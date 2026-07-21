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
                    $raw  = is_array($data) ? (isset($data['data']) ? $data['data'] : $data) : [];
                    // Normalise every product so all scalar fields are strings/numbers
                    return array_values(array_map([$this, 'normaliseProduct'], $raw));
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
     * Flatten a raw API product into safe scalar types.
     * Any field that arrives as an array is JSON-encoded so Blade never sees a non-string.
     */
    private function normaliseProduct(array $p): array
    {
        $scalar = static function ($v): string {
            if (is_null($v))  return '';
            if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
            return (string) $v;
        };

        return [
            'id'          => (int)   ($p['id']          ?? 0),
            'name'        => $scalar($p['name']         ?? $p['title']       ?? ''),
            'description' => $scalar($p['description']  ?? $p['desc']        ?? null),
            'category'    => $scalar($p['category']     ?? $p['type']        ?? null),
            'price'       => (float) ($p['price']       ?? $p['amount']      ?? 0),
            'stock'       => (int)   ($p['stock']       ?? $p['quantity']    ?? 0),
            // preserve any other keys the rest of the code might read
        ] + array_map(static function ($v) use ($scalar) {
            return is_array($v) ? $scalar($v) : $v;
        }, $p);
    }

    /**
     * Fetch live stock for each product sequentially and merge into the product array.
     * Results are cached per-product for 60 s to avoid hammering the API on every page load.
     */
    private function enrichWithStock(array $products): array
    {
        if (!$this->isConfigured()) {
            return $products;
        }

        // Separate already-cached products from those that need a fresh call
        $stockMap = [];
        $missing  = [];
        foreach ($products as $product) {
            $pid    = (int) ($product['id'] ?? 0);
            $cached = Cache::get("sujan_stock_{$pid}");
            if ($cached !== null) {
                $stockMap[$pid] = (int) $cached;
            } else {
                $missing[] = $pid;
            }
        }

        // Sequential calls for any not in cache
        foreach ($missing as $pid) {
            $stock = $this->fetchStockForProduct($pid);
            if ($stock !== null) {
                Cache::put("sujan_stock_{$pid}", $stock, 60);
                $stockMap[$pid] = $stock;
            }
        }

        // Merge: if we got a live value, use it; otherwise keep whatever normaliseProduct extracted
        return array_map(function (array $product) use ($stockMap): array {
            $pid = (int) ($product['id'] ?? 0);
            if (isset($stockMap[$pid])) {
                $product['stock'] = $stockMap[$pid];
            }
            return $product;
        }, $products);
    }

    /**
     * Fetch live stock for a single product ID from the API.
     * Tries the dedicated stock endpoint first; falls back to re-fetching the product detail.
     * Returns null if neither succeeds (caller keeps the normalised fallback value).
     */
    private function fetchStockForProduct(int $pid): ?int
    {
        // ── Attempt 1: dedicated stock endpoint ──────────────────────────────
        try {
            $res = $this->http()->get(self::BASE_URL . "/reseller/v1/products/{$pid}/stock");
            Log::debug("SujanDepartment stock/{$pid}: HTTP {$res->status()} — " . substr($res->body(), 0, 200));

            if ($res->successful()) {
                $d = $res->json();
                // Handle both flat {"stock":N} and wrapped {"data":{"stock":N}} responses
                $stock = $d['stock']
                    ?? $d['quantity']
                    ?? $d['available']
                    ?? $d['count']
                    ?? ($d['data']['stock']    ?? null)
                    ?? ($d['data']['quantity'] ?? null);

                if ($stock !== null) {
                    return (int) $stock;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("SujanDepartment stock/{$pid} exception: " . $e->getMessage());
        }

        // ── Attempt 2: re-fetch the individual product (some APIs embed stock here) ──
        try {
            $res = $this->http()->get(self::BASE_URL . "/reseller/v1/products/{$pid}");
            Log::debug("SujanDepartment product/{$pid}: HTTP {$res->status()} — " . substr($res->body(), 0, 200));

            if ($res->successful()) {
                $d       = $res->json();
                $payload = $d['data'] ?? $d;
                $stock   = $payload['stock'] ?? $payload['quantity'] ?? $payload['available'] ?? null;

                if ($stock !== null) {
                    return (int) $stock;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("SujanDepartment product/{$pid} exception: " . $e->getMessage());
        }

        return null;
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
