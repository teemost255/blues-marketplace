<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\{Cache, Http, Log};
use RuntimeException;
use Throwable;

class PvaDealsSmsService
{
    private const BASE_URL = 'https://prod-v3.pvadeals.com/v3/api';

    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = trim($apiKey ?? Setting::get('pvadeals_api_key', ''));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function getBalance(): float
    {
        $data = $this->request('GET', 'balance');
        if (!isset($data['credits']) || !is_numeric($data['credits'])) {
            throw new RuntimeException('PVADeals returned an invalid balance response.');
        }

        return (float) $data['credits'];
    }

    /**
     * Cache the catalog so browsing does not exceed PVADeals' 10/minute limit.
     */
    public function getServices(): array
    {
        $this->ensureConfigured();

        $cacheKey = 'pvadeals.services.' . hash('sha256', $this->apiKey);

        return Cache::remember($cacheKey, now()->addSeconds(60), function () {
            $data = $this->request('GET', 'services/all');
            if (!isset($data['services']) || !is_array($data['services'])) {
                throw new RuntimeException('PVADeals returned an invalid services response.');
            }

            return $data['services'];
        });
    }

    public function findService(string $serviceId): ?array
    {
        if (!preg_match('/^[a-f0-9]{24}$/i', $serviceId)) {
            return null;
        }

        foreach ($this->getServices() as $service) {
            if (is_array($service) && (string) ($service['_id'] ?? '') === $serviceId) {
                return $service;
            }
        }

        return null;
    }

    /**
     * Purchase a single short-term rental. Unclear transport or response
     * outcomes are marked uncertain so callers do not refund or retry blindly.
     */
    public function purchaseNumber(string $serviceId): array
    {
        try {
            $response = $this->send('POST', 'purchase', [
                'services' => [['serviceId' => $serviceId]],
            ]);
        } catch (Throwable $e) {
            Log::warning('PVADeals purchase connection failed; outcome is uncertain.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'uncertain' => true,
                'error' => 'The provider response could not be confirmed.',
            ];
        }

        $body = $response->json();
        if (!$response->successful() || !is_array($body) || ($body['success'] ?? false) !== true) {
            $message = is_array($body) ? ($body['message'] ?? null) : null;
            $message = is_string($message) && $message !== ''
                ? mb_substr($message, 0, 300)
                : 'PVADeals could not complete the purchase.';

            return [
                'success' => false,
                'uncertain' => $response->serverError() || $response->successful(),
                'error' => $message,
                'http_status' => $response->status(),
            ];
        }

        $request = $body['data']['requests'][0] ?? null;
        if (!is_array($request)
            || empty($request['_id'])
            || empty($request['number'])
            || !is_string($request['_id'])
            || !is_string($request['number'])) {
            Log::warning('PVADeals purchase returned success without a usable request record.', [
                'http_status' => $response->status(),
            ]);

            return [
                'success' => false,
                'uncertain' => true,
                'error' => 'The provider accepted the request but did not return its order details.',
            ];
        }

        return ['success' => true, 'request' => $request];
    }

    public function getRequest(string $requestId): array
    {
        return $this->request('GET', 'request/' . rawurlencode($requestId));
    }

    public function flagNumber(string $requestId): bool
    {
        $data = $this->request('POST', 'flag/' . rawurlencode($requestId));

        return $data === true;
    }

    public function setWebhook(string $url): array
    {
        return $this->request('POST', 'webhook', ['url' => $url]);
    }

    public static function countryCode(?string $country): string
    {
        $value = strtoupper(trim((string) $country));

        return match ($value) {
            'USA', 'UNITED STATES', 'UNITED STATES OF AMERICA' => 'US',
            'UK', 'UNITED KINGDOM', 'GREAT BRITAIN' => 'GB',
            default => $value,
        };
    }

    public static function countryName(string $countryCode, ?string $providerName = null): string
    {
        return match ($countryCode) {
            'US' => 'United States',
            'GB' => 'United Kingdom',
            default => trim((string) $providerName) !== '' ? trim((string) $providerName) : $countryCode,
        };
    }

    private function request(string $method, string $path, array $payload = []): array|bool
    {
        $response = $this->send($method, $path, $payload);
        $body = $response->json();

        if (!$response->successful() || !is_array($body) || ($body['success'] ?? false) !== true
            || !array_key_exists('data', $body)) {
            $message = is_array($body) ? ($body['message'] ?? null) : null;
            $message = is_string($message) && $message !== ''
                ? mb_substr($message, 0, 300)
                : "PVADeals request failed (HTTP {$response->status()}).";

            throw new RuntimeException($message);
        }

        return $body['data'];
    }

    private function send(string $method, string $path, array $payload = []): Response
    {
        $this->ensureConfigured();
        $http = Http::acceptJson()
            ->withToken($this->apiKey)
            ->timeout(20);
        $url = self::BASE_URL . '/' . ltrim($path, '/');

        return match (strtoupper($method)) {
            'GET' => $http->get($url, $payload),
            'POST' => $http->post($url, $payload),
            'DELETE' => $http->delete($url, $payload),
            default => throw new RuntimeException('Unsupported PVADeals request method.'),
        };
    }

    private function ensureConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('PVADeals API key is not configured.');
        }
    }
}
