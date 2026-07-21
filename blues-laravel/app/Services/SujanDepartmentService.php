<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\{Http, Log, Cache};

class SujanDepartmentService
{
    private const BASE_URL = 'https://api.sujandepartment.com';
    private const CACHE_TTL = 300; // 5 minutes

    private function apiKey(): string
    {
        return Setting::get('sujan_api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey());
    }

    private function http()
    {
        return Http::withToken($this->apiKey())
            ->timeout(15)
            ->acceptJson();
    }

    /**
     * Fetch all active catalog products enriched with live stock.
     * Products are cached for 5 min; stock counts use a shorter 60-second cache.
     * Returns array of product objects, or empty array on failure.
     */
    public function getProducts(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        // Fetch the product list (5-min cache for structure)
        $products = Cache::remember('sujan_products', self::CACHE_TTL, function () {
            try {
                $response = $this->http()->get(self::BASE_URL . '/reseller/v1/products');
                if ($response->successful()) {
                    $data = $response->json();
                    return is_array($data) ? (isset($data['data']) ? $data['data'] : $data) : [];
                }
                Log::warning('SujanDepartment: products fetch failed', ['status' => $response->status()]);
            } catch (\Throwable $e) {
                Log::error('SujanDepartment: products exception', ['error' => $e->getMessage()]);
            }
            return [];
        });

        if (empty($products)) {
            return [];
        }

        // Enrich each product with a live stock count (1-min cache per product)
        return $this->enrichWithStock($products);
    }

    /**
     * Fetch live stock for each product in parallel and merge into the product array.
     */
    private function enrichWithStock(array $products): array
    {
        if (!$this->isConfigured()) {
            return $products;
        }

        $key    = $this->apiKey();
        $ids    = array_column($products, 'id');

        // Build per-product stock cache keys
        $stockMap = [];
        $missing  = [];
        foreach ($ids as $id) {
            $cached = Cache::get("sujan_stock_{$id}");
            if ($cached !== null) {
                $stockMap[$id] = (int) $cached;
            } else {
                $missing[] = $id;
            }
        }

        // Parallel HTTP requests for any IDs not in cache
        if (!empty($missing)) {
            try {
                $responses = \Illuminate\Support\Facades\Http::pool(function ($pool) use ($missing, $key) {
                    foreach ($missing as $pid) {
                        $pool->as((string) $pid)
                             ->withToken($key)
                             ->timeout(10)
                             ->acceptJson()
                             ->get(self::BASE_URL . "/reseller/v1/products/{$pid}/stock");
                    }
                });

                foreach ($missing as $pid) {
                    $res = $responses[(string) $pid] ?? null;
                    if ($res && $res->successful()) {
                        $d = $res->json();
                        $stock = (int) ($d['stock'] ?? $d['data']['stock'] ?? $d['count'] ?? $d['quantity'] ?? 0);
                    } else {
                        // Fallback: use stock already on the product object
                        $stock = null;
                    }

                    if ($stock !== null) {
                        Cache::put("sujan_stock_{$pid}", $stock, 60); // 1-minute cache
                        $stockMap[$pid] = $stock;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('SujanDepartment: parallel stock fetch exception', ['error' => $e->getMessage()]);
            }
        }

        // Merge live stock into products; keep product's own stock value as fallback
        return array_map(function ($product) use ($stockMap) {
            $pid = (int) ($product['id'] ?? 0);
            if (isset($stockMap[$pid])) {
                $product['stock'] = $stockMap[$pid];
            }
            return $product;
        }, $products);
    }

    /**
     * Get live stock for a single product (also warms the per-product cache).
     */
    public function getStock(int $productId): ?int
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->http()->get(self::BASE_URL . "/reseller/v1/products/{$productId}/stock");
            if ($response->successful()) {
                $data  = $response->json();
                $stock = (int) ($data['stock'] ?? $data['data']['stock'] ?? $data['count'] ?? $data['quantity'] ?? 0);
                Cache::put("sujan_stock_{$productId}", $stock, 60);
                return $stock;
            }
        } catch (\Throwable $e) {
            Log::error('SujanDepartment: stock exception', ['product_id' => $productId, 'error' => $e->getMessage()]);
        }
        return null;
    }

    /**
     * Get reseller wallet balance.
     */
    public function getBalance(): ?float
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->http()->get(self::BASE_URL . '/reseller/v1/balance');
            if ($response->successful()) {
                $data = $response->json();
                return (float) ($data['balance'] ?? $data['data']['balance'] ?? 0);
            }
        } catch (\Throwable $e) {
            Log::error('SujanDepartment: balance exception', ['error' => $e->getMessage()]);
        }
        return null;
    }

    /**
     * Place an order and return the credentials.
     * Returns ['success' => true, 'credentials' => '...'] on success.
     * Returns ['success' => false, 'message' => '...'] on failure.
     */
    public function createOrder(int $productId, int $quantity = 1): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'API not configured.'];
        }

        try {
            $response = $this->http()->post(self::BASE_URL . '/reseller/v1/orders', [
                'product_id' => $productId,
                'quantity'   => $quantity,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Unwrap data key if present
                $payload = isset($data['data']) ? $data['data'] : $data;
                $credentials = $payload['credentials'] ?? $payload['account'] ?? $payload['details'] ?? null;

                if ($credentials) {
                    // Bust the products cache so stock updates reflect quickly
                    Cache::forget('sujan_products');
                    return ['success' => true, 'credentials' => $credentials, 'order_id' => $payload['id'] ?? $payload['order_id'] ?? null];
                }

                Log::warning('SujanDepartment: order succeeded but no credentials', ['response' => $data]);
                return ['success' => false, 'message' => 'Order placed but credentials were not returned. Contact support.'];
            }

            $error = $response->json('message') ?? $response->json('error') ?? 'Order failed.';
            Log::error('SujanDepartment: order failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'message' => $error];

        } catch (\Throwable $e) {
            Log::error('SujanDepartment: order exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Could not reach the catalog API. Please try again.'];
        }
    }

    /**
     * Flush the products and stock caches (call after admin key change or purchase).
     */
    public function clearCache(?array $productIds = null): void
    {
        Cache::forget('sujan_products');
        if ($productIds) {
            foreach ($productIds as $pid) {
                Cache::forget("sujan_stock_{$pid}");
            }
        }
    }
}
