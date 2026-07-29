<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\{Http, Log, Cache};

class SurePlusLogsService
{
    private const BASE_URL        = 'https://surepluglogs.com/api/v1/accounts';
    private const CATEGORIES_URL  = 'https://surepluglogs.com/api/v1/accounts/categories';
    private const PURCHASE_URL    = 'https://surepluglogs.com/api/v1/advanced/purchase-account';
    private const CACHE_TTL       = 300;  // 5 minutes
    private const CAT_CACHE_TTL   = 3600; // 1 hour

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
     * Fetch all API categories and return a map of [ id => name ].
     * Results are cached for 1 hour.
     */
    public function getCategories(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        return Cache::remember('sureplus_categories', self::CAT_CACHE_TTL, function () {
            try {
                $response = $this->http()->get(self::CATEGORIES_URL, [
                    'key' => $this->apiKey(),
                ]);

                if ($response->successful()) {
                    $data = $response->json('data') ?? $response->json() ?? [];
                    // Normalise into [ id => name ]
                    $map = [];
                    foreach ((array) $data as $cat) {
                        $id   = $cat['id']    ?? null;
                        $name = $cat['name']  ?? $cat['title'] ?? null;
                        if ($id !== null && $name) {
                            $map[(string) $id] = (string) $name;
                        }
                    }
                    return $map;
                }

                Log::warning('SurePlusLogs: categories fetch failed', ['status' => $response->status()]);
            } catch (\Throwable $e) {
                Log::error('SurePlusLogs: getCategories exception', ['error' => $e->getMessage()]);
            }
            return [];
        });
    }

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
            $categoryMap = $this->getCategories();
            try {
                $response = $this->http()->get(self::BASE_URL . '/products', [
                    'key' => $this->apiKey(),
                ]);

                if ($response->successful()) {
                    $data    = $response->json();
                    $catalog = $data['data']['catalog'] ?? [];
                    $manual  = $data['data']['manual']  ?? [];

                    $products = array_merge(
                        array_map(fn($p) => $this->normaliseProduct($p, 'catalog', $categoryMap), $catalog),
                        array_map(fn($p) => $this->normaliseProduct($p, 'manual',  $categoryMap), $manual)
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

        $categoryMap = $this->getCategories();
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
                    array_map(fn($p) => $this->normaliseProduct($p, 'catalog', $categoryMap), $catalog),
                    array_map(fn($p) => $this->normaliseProduct($p, 'manual',  $categoryMap), $manual)
                ));
            }
        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: searchProducts exception', ['error' => $e->getMessage()]);
        }
        return [];
    }

    /**
     * Normalise a raw API product into consistent scalar types for Blade.
     *
     * Category resolution order:
     *   1. 'category' field  (string name — catalog products)
     *   2. 'category_name'   (some API versions)
     *   3. 'category_id' mapped through $categoryMap  (manual products)
     *   4. raw 'category_id' string as a last resort
     */
    private function normaliseProduct(array $p, string $type, array $categoryMap = []): array
    {
        // Try to resolve a human-readable category name
        $categoryName = (string) ($p['category'] ?? $p['category_name'] ?? '');
        if ($categoryName === '') {
            $categoryId = (string) ($p['category_id'] ?? '');
            $categoryName = $categoryMap[$categoryId] ?? $categoryId;
        }

        return [
            'id'           => (int)    ($p['id']           ?? 0),
            'type'         => $type,   // 'catalog' or 'manual'
            'name'         => (string) ($p['title']         ?? $p['name'] ?? ''),
            'description'  => (string) ($p['description']   ?? ''),
            'image'        => (string) ($p['image']         ?? ''),
            'category'     => $categoryName,
            'price'        => (float)  ($p['price']         ?? 0),
            'min_quantity' => (int)    ($p['min_quantity']   ?? 1),
            'max_quantity' => (int)    ($p['max_quantity']   ?? 1),
            'stock'        => (int)    ($p['stock']          ?? 0),
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
    // Orders
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * List all orders (paginated).
     * Returns ['data' => [...], 'pagination' => [...]] or [] on failure.
     */
    public function getOrders(int $page = 1, int $perPage = 20): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = $this->http()->get(self::BASE_URL . '/orders', [
                'key'      => $this->apiKey(),
                'page'     => $page,
                'per_page' => $perPage,
            ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('SurePlusLogs: getOrders failed', ['status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: getOrders exception', ['error' => $e->getMessage()]);
        }
        return [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Accounts
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * List all delivered accounts (paginated).
     * Returns the full API response array or [] on failure.
     */
    public function getAccounts(int $page = 1, int $perPage = 20): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = $this->http()->get(self::BASE_URL, [
                'key'      => $this->apiKey(),
                'page'     => $page,
                'per_page' => $perPage,
            ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('SurePlusLogs: getAccounts failed', ['status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: getAccounts exception', ['error' => $e->getMessage()]);
        }
        return [];
    }

    /**
     * Get a single account's details by ID.
     * Returns the account data array or null on failure/not found.
     */
    public function getAccountDetails(int $id): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->http()->get(self::BASE_URL . '/' . $id, [
                'key' => $this->apiKey(),
            ]);

            if ($response->successful()) {
                return $response->json('data');
            }

            if ($response->status() === 404) {
                return null;
            }

            Log::warning('SurePlusLogs: getAccountDetails failed', ['id' => $id, 'status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::error('SurePlusLogs: getAccountDetails exception', ['error' => $e->getMessage()]);
        }
        return null;
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
