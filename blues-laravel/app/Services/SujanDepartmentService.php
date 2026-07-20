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
     * Fetch all active catalog products (cached).
     * Returns array of product objects, or empty array on failure.
     */
    public function getProducts(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        return Cache::remember('sujan_products', self::CACHE_TTL, function () {
            try {
                $response = $this->http()->get(self::BASE_URL . '/reseller/v1/products');
                if ($response->successful()) {
                    $data = $response->json();
                    // API may return array directly or wrapped in a data key
                    return is_array($data) ? (isset($data['data']) ? $data['data'] : $data) : [];
                }
                Log::warning('SujanDepartment: products fetch failed', ['status' => $response->status()]);
            } catch (\Throwable $e) {
                Log::error('SujanDepartment: products exception', ['error' => $e->getMessage()]);
            }
            return [];
        });
    }

    /**
     * Get live stock for a single product.
     */
    public function getStock(int $productId): ?int
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->http()->get(self::BASE_URL . "/reseller/v1/products/{$productId}/stock");
            if ($response->successful()) {
                $data = $response->json();
                return (int) ($data['stock'] ?? $data['data']['stock'] ?? $data['count'] ?? 0);
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
     * Flush the products cache (call after admin key change).
     */
    public function clearCache(): void
    {
        Cache::forget('sujan_products');
    }
}
