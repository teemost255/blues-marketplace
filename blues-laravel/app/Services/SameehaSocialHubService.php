<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\{Cache, Http, Log};

class SameehaSocialHubService
{
    private const BASE_URL = 'https://sameehasocialhub.com/api/v1';
    private const PRODUCTS_CACHE_KEY = 'sameeha_products';
    private const CATEGORIES_CACHE_KEY = 'sameeha_categories';
    private const PRODUCTS_CACHE_TTL = 300;
    private const CATEGORIES_CACHE_TTL = 3600;
    private const MAX_PRODUCT_PAGES = 100;

    private function apiKey(): string
    {
        return (string) Setting::get('sameeha_api_key', '');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    private function http()
    {
        return Http::timeout(20)
            ->acceptJson()
            ->withToken($this->apiKey());
    }

    /**
     * Return a map of provider category IDs to names.
     */
    public function getCategories(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        return Cache::remember(self::CATEGORIES_CACHE_KEY, self::CATEGORIES_CACHE_TTL, function () {
            try {
                $response = $this->http()->get(self::BASE_URL . '/categories');
                if (!$response->successful()) {
                    Log::warning('Sameeha categories request failed', ['status' => $response->status()]);
                    return [];
                }

                $categories = $response->json('results');
                if (!is_array($categories)) {
                    Log::warning('Sameeha categories response has no results array');
                    return [];
                }

                $map = [];
                foreach ($categories as $category) {
                    if (!is_array($category) || !isset($category['id'], $category['name'])) {
                        continue;
                    }
                    $map[(string) $category['id']] = (string) $category['name'];
                }

                return $map;
            } catch (\Throwable $e) {
                Log::error('Sameeha categories request error', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Return all provider products, normalized for the marketplace.
     * Results are cached for five minutes and paged until the API returns an empty page.
     */
    public function getProducts(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        return Cache::remember(self::PRODUCTS_CACHE_KEY, self::PRODUCTS_CACHE_TTL, function () {
            $categoryMap = $this->getCategories();
            $products = [];
            $seenPages = [];
            $reportedCount = 0;

            try {
                for ($page = 1; $page <= self::MAX_PRODUCT_PAGES; $page++) {
                    $response = $this->http()->get(self::BASE_URL . '/products', ['page' => $page]);
                    if (!$response->successful()) {
                        Log::warning('Sameeha products request failed', [
                            'page' => $page,
                            'status' => $response->status(),
                        ]);
                        return [];
                    }

                    $payload = $response->json();
                    $results = is_array($payload) ? ($payload['results'] ?? null) : null;
                    if (!is_array($results)) {
                        Log::warning('Sameeha products response has no results array', ['page' => $page]);
                        return [];
                    }

                    if ($page === 1) {
                        $reportedCount = (int) ($payload['count'] ?? 0);
                    }
                    if ($results === []) {
                        break;
                    }

                    $signature = hash('sha256', json_encode($results) ?: '');
                    if (isset($seenPages[$signature])) {
                        Log::warning('Sameeha product pagination repeated a page', ['page' => $page]);
                        break;
                    }
                    $seenPages[$signature] = true;

                    foreach ($results as $product) {
                        if (!is_array($product)) {
                            continue;
                        }
                        $normalized = $this->normalizeProduct($product, $categoryMap);
                        if ($normalized['id'] > 0) {
                            $products[(string) $normalized['id']] = $normalized;
                        }
                    }

                    if ($reportedCount > 0 && count($products) >= $reportedCount) {
                        break;
                    }

                    if ($page === self::MAX_PRODUCT_PAGES) {
                        Log::warning('Sameeha product pagination reached its safety limit');
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Sameeha products request error', ['error' => $e->getMessage()]);
                return [];
            }

            return array_values($products);
        });
    }

    private function normalizeProduct(array $product, array $categoryMap): array
    {
        $categoryId = (string) ($product['category_id'] ?? '');

        return [
            'id'          => (int) ($product['id'] ?? 0),
            'type'        => 'catalog',
            'name'        => (string) ($product['name'] ?? ''),
            'description' => '',
            'image'       => '',
            'category'    => $categoryMap[$categoryId] ?? ($categoryId !== '' ? $categoryId : 'Other'),
            'price'       => (float) ($product['price'] ?? 0),
            'stock'       => max(0, (int) ($product['stock'] ?? 0)),
            'platform'    => '',
            'currency'    => 'NGN',
        ];
    }

    /**
     * Purchase product keys. Sameeha returns the keys once in the purchase response.
     *
     * @return array{success: bool, requires_review?: bool, credentials?: string, order_id?: int|string|null, charge?: string|null, http_status?: int|null, response_fields?: array, message?: string}
     */
    public function createOrder(int $productId, int $quantity = 1): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Sameeha API key is not configured.'];
        }

        try {
            $response = $this->http()->post(self::BASE_URL . '/buy', [
                'product' => $productId,
                'quantity' => $quantity,
            ]);
            $payload = $response->json();
            $status = $response->status();
            $payload = is_array($payload) ? $payload : [];
            $responseFields = array_keys($payload);
            $dataFields = is_array($payload['data'] ?? null) ? array_keys($payload['data']) : [];
            $orderId = $this->responseValue($payload, ['order_id', 'id']);
            $charge = $this->responseValue($payload, ['charge', 'amount', 'cost']);

            if (!$response->successful()) {
                $error = (string) ($payload['error'] ?? '');
                $message = match ($status) {
                    401 => 'The catalog provider rejected its API key. Please contact support.',
                    402 => 'The supplier wallet has insufficient funds. Please try again later.',
                    404 => 'This product is no longer available.',
                    409 => 'This product is out of stock. Please choose another product.',
                    429 => 'The catalog provider is busy. Please try again shortly.',
                    default => (string) ($payload['detail'] ?? 'The catalog provider could not complete this purchase.'),
                };
                $requiresReview = $status >= 500 || in_array($status, [408, 425], true) || $status < 400;

                Log::warning('Sameeha product purchase failed', [
                    'product_id' => $productId,
                    'status' => $status,
                    'error' => $error,
                    'requires_review' => $requiresReview,
                    'response_fields' => $responseFields,
                ]);
                return [
                    'success' => false,
                    'requires_review' => $requiresReview,
                    'message' => $requiresReview
                        ? 'The provider response is unclear. This order has been held for review; please do not purchase it again yet.'
                        : $message,
                    'http_status' => $status,
                    'response_fields' => $responseFields,
                    'order_id' => $orderId,
                    'charge' => is_scalar($charge) ? (string) $charge : null,
                ];
            }

            try {
                Cache::forget(self::PRODUCTS_CACHE_KEY);
            } catch (\Throwable $e) {
                // Cache invalidation must not hide keys from a successful purchase.
                Log::warning('Sameeha product cache could not be cleared after purchase', [
                    'product_id' => $productId,
                    'error' => $e->getMessage(),
                ]);
            }

            // A successful HTTP response may mean Sameeha charged the account, even
            // when its payload is malformed or uses a nested credentials field.
            $credentials = $this->extractCredentials($payload);
            if (count($credentials) < $quantity) {
                Log::error('Sameeha purchase returned fewer keys than requested', [
                    'product_id' => $productId,
                    'requested_quantity' => $quantity,
                    'returned_quantity' => count($credentials),
                    'status' => $status,
                    'response_fields' => $responseFields,
                    'data_fields' => $dataFields,
                ]);
                return [
                    'success' => false,
                    'requires_review' => true,
                    'message' => 'The provider may have processed this order but did not return usable credentials. It has been held for review; please do not purchase it again yet.',
                    'http_status' => $status,
                    'response_fields' => $responseFields,
                    'order_id' => $orderId,
                    'charge' => is_scalar($charge) ? (string) $charge : null,
                    'credentials' => implode("\n", $credentials),
                ];
            }

            return [
                'success' => true,
                'credentials' => implode("\n", $credentials),
                'order_id' => $orderId,
                'charge' => is_scalar($charge) ? (string) $charge : null,
                'http_status' => $status,
            ];
        } catch (\Throwable $e) {
            Log::error('Sameeha product purchase request error', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'requires_review' => true,
                'message' => 'The provider response is unclear. This order has been held for review; please do not purchase it again yet.',
            ];
        }
    }

    /**
     * Accept the documented top-level key list plus common nested/string response
     * shapes without ever writing credential values to the application log.
     */
    private function extractCredentials(array $payload): array
    {
        $containers = [$payload];
        foreach (['data', 'result', 'order'] as $container) {
            if (is_array($payload[$container] ?? null)) {
                $containers[] = $payload[$container];
            }
        }

        foreach ($containers as $container) {
            foreach (['keys', 'credentials', 'key', 'credential', 'account'] as $field) {
                if (!array_key_exists($field, $container)) {
                    continue;
                }

                $credentials = $this->normalizeCredentialValue($container[$field]);
                if ($credentials !== []) {
                    return $credentials;
                }
            }
        }

        return [];
    }

    private function normalizeCredentialValue(mixed $value): array
    {
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? [] : [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        if (array_is_list($value)) {
            $credentials = [];
            foreach ($value as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $credentials[] = trim($item);
                } elseif (is_array($item)) {
                    $formatted = $this->formatCredentialRecord($item);
                    if ($formatted !== '') {
                        $credentials[] = $formatted;
                    }
                }
            }
            return $credentials;
        }

        foreach (['keys', 'credentials', 'key', 'credential', 'value'] as $field) {
            if (array_key_exists($field, $value)) {
                $credentials = $this->normalizeCredentialValue($value[$field]);
                if ($credentials !== []) {
                    return $credentials;
                }
            }
        }

        $formatted = $this->formatCredentialRecord($value);
        return $formatted === '' ? [] : [$formatted];
    }

    private function formatCredentialRecord(array $record): string
    {
        $lines = [];
        foreach ($record as $label => $value) {
            if (is_scalar($value) && trim((string) $value) !== '') {
                $lines[] = is_string($label)
                    ? $label . ': ' . trim((string) $value)
                    : trim((string) $value);
            }
        }

        return implode("\n", $lines);
    }

    private function responseValue(array $payload, array $fields): mixed
    {
        foreach ([$payload, is_array($payload['data'] ?? null) ? $payload['data'] : []] as $container) {
            foreach ($fields as $field) {
                if (isset($container[$field]) && is_scalar($container[$field])) {
                    return $container[$field];
                }
            }
        }

        return null;
    }

    /**
     * Return the provider wallet balance and currency, or null if unavailable.
     *
     * @return array{balance: float, currency: string}|null
     */
    public function getBalance(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->http()->get(self::BASE_URL . '/balance');
            $balance = $response->json('balance');
            if (!$response->successful() || !is_numeric($balance)) {
                Log::warning('Sameeha balance request failed', ['status' => $response->status()]);
                return null;
            }

            return [
                'balance' => (float) $balance,
                'currency' => (string) ($response->json('currency') ?? 'NGN'),
            ];
        } catch (\Throwable $e) {
            Log::error('Sameeha balance request error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function clearCache(): void
    {
        Cache::forget(self::PRODUCTS_CACHE_KEY);
        Cache::forget(self::CATEGORIES_CACHE_KEY);
    }
}
