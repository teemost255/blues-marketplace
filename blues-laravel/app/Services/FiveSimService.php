<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 5SIM API Integration
 * Docs: https://5sim.net/api
 * Base: https://5sim.net/v1
 */
class FiveSimService
{
    private string $apiKey;
    private string $baseUrl = 'https://5sim.net/v1';
    private float  $usdToNgn;

    public function __construct()
    {
        $this->apiKey   = trim(Setting::get('fivesim_api_key', ''));
        $this->usdToNgn = (float) Setting::get('usd_to_ngn_rate', '1600');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function usdToNgn(float $usd): float
    {
        return round($usd * $this->usdToNgn, 2);
    }

    private function authClient()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
        ])->timeout(20);
    }

    private function guestClient()
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
        ])->timeout(20);
    }

    public function getBalance(): array
    {
        try {
            $response = $this->authClient()->get($this->baseUrl . '/user/profile');
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'HTTP ' . $response->status()];
            }
            $data = $response->json();
            $balanceUsd = (float)($data['balance'] ?? 0);
            return ['success' => true, 'data' => [
                'balance_usd' => $balanceUsd,
                'balance_ngn' => $this->usdToNgn($balanceUsd),
                'balance'     => $balanceUsd,
            ]];
        } catch (\Exception $e) {
            Log::error('5SIM getBalance: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach 5SIM API.'];
        }
    }

    /**
     * Returns list of countries.
     * [{code, name}]
     */
    public function getCountries(): array
    {
        try {
            $response = $this->guestClient()->get($this->baseUrl . '/guest/countries');
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'HTTP ' . $response->status()];
            }
            $data = $response->json();
            if (!is_array($data)) {
                return ['success' => false, 'message' => 'Unexpected countries response.'];
            }

            $countries = [];
            foreach ($data as $code => $info) {
                if (!is_array($info)) continue;
                // API returns text_en for English name; iso is {"af":1} object
                $name = $info['text_en'] ?? ucwords(str_replace('and', '& ', $code));
                $isoObj = $info['iso'] ?? [];
                $iso = is_array($isoObj) ? (string)(array_key_first($isoObj) ?? '') : '';
                $countries[] = [
                    'code' => $code,
                    'name' => $name,
                    'iso'  => $iso,
                ];
            }

            usort($countries, fn($a, $b) => strcmp($a['name'], $b['name']));
            return ['success' => true, 'data' => $countries];
        } catch (\Exception $e) {
            Log::error('5SIM getCountries: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach 5SIM API.'];
        }
    }

    /**
     * Returns services (products) for a country.
     * [{serviceId, name, count, cost_usd, cost_ngn}]
     */
    public function getServices(string $country): array
    {
        try {
            $url      = $this->baseUrl . '/guest/products/' . urlencode($country) . '/any';
            $response = $this->guestClient()->get($url);
            Log::info('5SIM getServices [' . $country . '] HTTP ' . $response->status());

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'HTTP ' . $response->status()];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return ['success' => false, 'message' => 'Unexpected services response.'];
            }

            $services = [];
            foreach ($data as $product => $info) {
                $qty = (int)($info['Qty'] ?? 0);
                if ($qty <= 0) continue;
                $priceUsd = (float)($info['Price'] ?? 0);
                $services[] = [
                    'serviceId' => $product,
                    'name'      => ucwords(str_replace('_', ' ', $product)),
                    'count'     => $qty,
                    'cost_usd'  => $priceUsd,
                    'cost_ngn'  => $this->usdToNgn($priceUsd),
                ];
            }

            usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));
            return ['success' => true, 'data' => $services];
        } catch (\Exception $e) {
            Log::error('5SIM getServices: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach 5SIM API.'];
        }
    }

    /**
     * Order a number.
     * Returns ['success'=>true, 'data'=>['order_id'=>'...','number'=>'...','cost_usd'=>X,'cost_ngn'=>X]]
     */
    public function orderNumber(string $country, string $product): array
    {
        try {
            $url      = $this->baseUrl . '/user/buy/activation/' . urlencode($country) . '/any/' . urlencode($product);
            $response = $this->authClient()->get($url);
            Log::info('5SIM orderNumber [' . $country . '/' . $product . '] HTTP ' . $response->status() . ' | ' . substr($response->body(), 0, 200));

            if (!$response->successful()) {
                $msg = $response->json('message') ?? ('HTTP ' . $response->status());
                return ['success' => false, 'message' => 'Order failed: ' . $msg];
            }

            $data     = $response->json();
            $priceUsd = (float)($data['price'] ?? 0);

            return ['success' => true, 'data' => [
                'order_id' => (string)($data['id'] ?? ''),
                'number'   => ltrim($data['phone'] ?? '', '+'),
                'cost_usd' => $priceUsd,
                'cost_ngn' => $this->usdToNgn($priceUsd),
            ]];
        } catch (\Exception $e) {
            Log::error('5SIM orderNumber: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach 5SIM API.'];
        }
    }

    /**
     * Check SMS for an order.
     * status: pending|received|canceled|timeout|banned|finished
     */
    public function checkSms(string $orderId): array
    {
        try {
            $response = $this->authClient()->get($this->baseUrl . '/user/check/' . $orderId);
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'HTTP ' . $response->status()];
            }

            $data   = $response->json();
            $status = strtolower($data['status'] ?? 'pending');
            $sms    = collect($data['sms'] ?? [])->pluck('code')->filter()->implode(', ');

            $normalized = match($status) {
                'received', 'finished' => 'completed',
                'canceled', 'timeout', 'banned' => 'cancelled',
                default => 'pending',
            };

            return ['success' => true, 'data' => [
                'status'     => $normalized,
                'status_raw' => $status,
                'sms'        => $sms ?: null,
            ]];
        } catch (\Exception $e) {
            Log::error('5SIM checkSms: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach 5SIM API.'];
        }
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(string $orderId): array
    {
        try {
            $response = $this->authClient()->get($this->baseUrl . '/user/cancel/' . $orderId);
            if ($response->successful()) {
                return ['success' => true, 'data' => []];
            }
            $msg = $response->json('message') ?? ('HTTP ' . $response->status());
            return ['success' => false, 'message' => $msg];
        } catch (\Exception $e) {
            Log::error('5SIM cancelOrder: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach 5SIM API.'];
        }
    }
}
