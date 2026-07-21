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
        // price_minor is in kobo (minor units) → divide by 100 for Naira
        $priceMinor = (float) ($p['price_minor'] ?? $p['price'] ?? $p['amount'] ?? 0);
        $price      = $priceMinor > 1000 ? $priceMinor / 100 : $priceMinor; // detect kobo vs naira

        // category and platform come as nested objects {"id":2,"name":"..."}
        $category = is_array($p['category'] ?? null)
            ? ($p['category']['name'] ?? '')
            : (string) ($p['category'] ?? '');

        $platform = is_array($p['platform'] ?? null)
            ? ($p['platform']['name'] ?? '')
            : (string) ($p['platform'] ?? '');

        // stock lives under available_stock in this API
        $stock = (int) ($p['available_stock'] ?? $p['stock'] ?? $p['quantity'] ?? 0);

        // description may be null
        $description = is_array($p['description'] ?? null)
            ? json_encode($p['description'], JSON_UNESCAPED_UNICODE)
            : (string) ($p['description'] ?? '');

        return [
            'id'          => (int) ($p['id'] ?? 0),
            'name'        => (string) ($p['name'] ?? $p['title'] ?? ''),
            'description' => $description,
            'category'    => $category,
            'platform'    => $platform,
            'price'       => $price,
            'stock'       => $stock,
        ];
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
     * Fetch live stock for a single product using the guide endpoint:
     *   GET /reseller/v1/products/{product_id}/stock
     *
     * Handles every realistic response shape:
     *   • Plain integer body:              5
     *   • Flat object:                     {"stock":5}  {"quantity":5}  {"stock_count":5}  etc.
     *   • Data-wrapped:                    {"data":{"stock":5}}
     *   • Success-wrapped:                 {"success":true,"stock":5}
     *
     * Returns null only on a network/HTTP failure (caller keeps the product-list fallback).
     */
    private function fetchStockForProduct(int $pid): ?int
    {
        try {
            $res = $this->http()
                ->timeout(10)
                ->get(self::BASE_URL . "/reseller/v1/products/{$pid}/stock");

            Log::info("SujanDepartment [stock/{$pid}] HTTP {$res->status()}: " . $res->body());

            if (!$res->successful()) {
                return null;
            }

            $body = trim($res->body());

            // ── Shape 1: plain integer body ────────────────────────────────
            if (is_numeric($body)) {
                return (int) $body;
            }

            $d = $res->json();

            // ── Shape 2: data-wrapped (this API returns {"data":{"product_id":N,"available_stock":N}})
            $payload = $d['data'] ?? $d;

            // ── Shape 3: scan all known stock key names ────────────────────
            foreach (['available_stock', 'stock', 'stock_count', 'quantity', 'qty', 'available', 'count', 'in_stock'] as $k) {
                if (isset($payload[$k]) && is_numeric($payload[$k])) {
                    return (int) $payload[$k];
                }
            }

            Log::warning("SujanDepartment [stock/{$pid}] 200 but no stock key found. Body: {$body}");
            return null;

        } catch (\Throwable $e) {
            Log::error("SujanDepartment [stock/{$pid}] exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get live stock for a single product (also warms the per-product cache).
     */
    public function getStock(int $productId): ?int
    {
        $stock = $this->fetchStockForProduct($productId);
        if ($stock !== null) {
            Cache::put("sujan_stock_{$productId}", $stock, 60);
        }
        return $stock;
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
