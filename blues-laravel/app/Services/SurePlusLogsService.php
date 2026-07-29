<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\{Http, Log, Cache};

class SurePlusLogsService
{
    private const BASE_URL        = 'https://surepluglogs.com/api/v1/accounts';
    private const PURCHASE_URL    = 'https://surepluglogs.com/api/v1/advanced/purchase-account';
    private const CACHE_TTL       = 300; // 5 minutes

    private function apiKey(): string
    {
        return Setting::get('sureplus_api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey());
    }

    private function http()
    {
        return Http::timeout(15)->acceptJson();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Products
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch all available products (catalog + manual) from the API.
     * Returns a flat array of normalised product objects, or [] on failure.
     * Results are cached for 5 minutes.
     */
    public function getProducts(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        return Cache::remember('sureplus_products', self::CACHE_TTL, function () {
            try {
                $response = $this->http()->get(self::BASE_URL . '/products', [
                    'key' => $this->apiKey(),
                ]);

                if ($response->successful()) {
                    $data    = $response->json();
                    $catalog = $data['data']['catalog'] ?? [];
                    $manual  = $data['data']['manual']  ?? [];

                    $products = array_merge(
                        array_map(fn($p) => $this->normaliseProduct($p, 'catalog'), $catalog),
                        array_map(fn($p) => $this->normaliseProduct($p, 'manual'),  $manual)
                    );

                    return array_values($products);
                }

                Log::warning('SurePlusLogs: products fetch failed', ['status' => $response->status(), 'body' => $response->body()]);
            } catch (\Throwable $e) {
                Log::error('SurePlusLogs: products exception', ['error' => $e->getMessage()]);
            }
            return [];
        });
    }

    /**
     * Search products by term (bypasses cache for fresh results).
     */
    public function searchProducts(string $search = '', ?int $categoryId = null): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $params = ['key' => $this->apiKey(), 'search' => $search];
        if ($categoryId !== null) {
            $params['category'] = $categoryId;
        }

        try {
            $response = $this->http()->get(self::BASE_URL . '/products', $params);

            if ($response->successful()) {
                $data    = $response->json();
                $catalog = $data['data']['catalog'] ?? [];
                $manual  = $data['data']['manual']  ?? [];

                return array_values(array_merge(
                    array_map(fn($p) => $this->normaliseProduct($p, 'catalog'), $catalog),
                    array_map(fn($p) => $this->normaliseProduct($p, 'manual'),  $manual)
                ));
            }
        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: searchProducts exception', ['error' => $e->getMessage()]);
        }
        return [];
    }

    /**
     * Normalise a raw API product into consistent scalar types for Blade.
     */
    private function normaliseProduct(array $p, string $type): array
    {
        return [
            'id'           => (int)    ($p['id']          ?? 0),
            'type'         => $type,   // 'catalog' or 'manual'
            'name'         => (string) ($p['title']        ?? $p['name'] ?? ''),
            'description'  => (string) ($p['description']  ?? ''),
            'image'        => (string) ($p['image']        ?? ''),
            'category'     => (string) ($p['category_id']  ?? ''),  // manual products have category_id
            'price'        => (float)  ($p['price']        ?? 0),
            'min_quantity' => (int)    ($p['min_quantity']  ?? 1),
            'max_quantity' => (int)    ($p['max_quantity']  ?? 1),
            'stock'        => (int)    ($p['stock']         ?? 0),
            'platform'     => '',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Purchase
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Purchase a product and return its credentials.
     *
     * Returns ['success' => true, 'credentials' => '...', 'order_id' => N] on success.
     * Returns ['success' => false, 'message' => '...']                      on failure.
     */
    public function createOrder(int $productId, int $quantity = 1, string $productType = 'catalog'): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'API not configured.'];
        }

        try {
            $payload = [
                'key'          => $this->apiKey(),
                'product_type' => $productType,
                'product_id'   => $productId,
                'quantity'     => $quantity,
            ];

            $response = $this->http()->post(self::PURCHASE_URL, $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (!($data['status'] ?? false)) {
                    $message = $data['message'] ?? 'Purchase failed.';
                    Log::warning('SurePlusLogs: purchase returned status=false', ['response' => $data]);
                    return ['success' => false, 'message' => $message];
                }

                // Bust the products cache so stock updates reflect quickly
                Cache::forget('sureplus_products');

                $accounts = $data['accounts'] ?? [];
                $orderId  = $data['order']['id'] ?? null;

                // Flatten credentials from all returned accounts into one string
                $credentialLines = array_map(
                    fn($acc) => $acc['credentials'] ?? '',
                    $accounts
                );
                $credentials = implode("\n---\n", array_filter($credentialLines));

                if ($credentials) {
                    return [
                        'success'     => true,
                        'credentials' => $credentials,
                        'order_id'    => $orderId,
                        'accounts'    => $accounts,
                    ];
                }

                Log::warning('SurePlusLogs: order succeeded but no credentials returned', ['response' => $data]);
                return ['success' => false, 'message' => 'Order placed but credentials were not returned. Contact support.'];
            }

            $status  = $response->status();
            $message = $response->json('message') ?? $response->json('error') ?? match ($status) {
                401    => 'Invalid API key.',
                404    => 'Product not found or inactive.',
                422    => 'Insufficient balance or quantity exceeds available stock.',
                502    => 'Vendor purchase failed. Please try again.',
                default => 'Order failed (HTTP ' . $status . ').',
            };

            Log::error('SurePlusLogs: order failed', ['status' => $status, 'body' => $response->body()]);
            return ['success' => false, 'message' => $message];

        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: order exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Could not reach the catalog API. Please try again.'];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stats / Orders
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch account statistics (total accounts, sold, unsold, total spent, total orders).
     */
    public function getStats(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->http()->get(self::BASE_URL . '/stats', ['key' => $this->apiKey()]);
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: stats exception', ['error' => $e->getMessage()]);
        }
        return null;
    }

    /**
     * Fetch accounts delivered for a specific order ID.
     */
    public function getAccountsByOrder(int $orderId): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = $this->http()->get(self::BASE_URL . '/order/' . $orderId, ['key' => $this->apiKey()]);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: getAccountsByOrder exception', ['error' => $e->getMessage()]);
        }
        return [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cache helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Flush the products cache (call after admin key change or successful purchase).
     */
    public function clearCache(): void
    {
        Cache::forget('sureplus_products');
    }
}
