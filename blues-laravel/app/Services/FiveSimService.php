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

    /** Keys that are metadata, not operator names */
    private const COUNTRY_META_KEYS = ['iso', 'prefix', 'text_en', 'text_ru'];

    /**
     * Returns list of countries with their available operators.
     * [{code, name, iso, operators:[]}]
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
                $name   = $info['text_en'] ?? ucwords(str_replace('and', '& ', $code));
                $isoObj = $info['iso'] ?? [];
                $iso    = is_array($isoObj) ? (string)(array_key_first($isoObj) ?? '') : '';
                // Operators are all keys that are not metadata
                $operators = array_values(array_filter(
                    array_keys($info),
                    fn($k) => !in_array($k, self::COUNTRY_META_KEYS)
                ));
                $countries[] = [
                    'code'      => $code,
                    'name'      => $name,
                    'iso'       => $iso,
                    'operators' => $operators,
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
     * Returns services for a country, fetching each operator in parallel.
     * Each service entry has an `operators` array with per-carrier qty+price.
     * [{serviceId, name, count, cost_usd, cost_ngn, operators:[{operator,qty,price_usd,price_ngn}]}]
     */
    public function getServices(string $country, array $operators = []): array
    {
        try {
            // Always include 'any' plus all real operators
            $toFetch = array_unique(array_merge($operators, ['any']));
            $baseUrl = $this->baseUrl;

            // Parallel HTTP requests — one per operator
            $responses = Http::pool(function ($pool) use ($baseUrl, $country, $toFetch) {
                $reqs = [];
                foreach ($toFetch as $op) {
                    $reqs[] = $pool->as($op)
                        ->withHeaders(['Accept' => 'application/json'])
                        ->timeout(15)
                        ->get("{$baseUrl}/guest/products/{$country}/{$op}");
                }
                return $reqs;
            });

            // Merge: product → [operator entries]
            $products = [];
            foreach ($toFetch as $op) {
                $res = $responses[$op] ?? null;
                if (!$res || !$res->successful()) continue;
                $data = $res->json();
                if (!is_array($data)) continue;

                foreach ($data as $product => $info) {
                    $qty = (int)($info['Qty'] ?? 0);
                    if ($qty <= 0) continue;
                    $priceUsd = (float)($info['Price'] ?? 0);

                    if (!isset($products[$product])) {
                        $products[$product] = [
                            'serviceId' => $product,
                            'name'      => ucwords(str_replace('_', ' ', $product)),
                            'operators' => [],
                        ];
                    }
                    $products[$product]['operators'][] = [
                        'operator'  => $op,
                        'qty'       => $qty,
                        'price_usd' => $priceUsd,
                        'price_ngn' => $this->usdToNgn($priceUsd),
                    ];
                }
            }

            // Build final list: sort operators by price, derive top-level cost from cheapest
            $services = [];
            foreach ($products as $info) {
                $ops = $info['operators'];
                // Sort by price ascending; put 'any' last (it auto-picks cheapest real carrier)
                usort($ops, function ($a, $b) {
                    if ($a['operator'] === 'any') return 1;
                    if ($b['operator'] === 'any') return -1;
                    return $a['price_usd'] <=> $b['price_usd'];
                });
                $cheapest = $ops[0] ?? null;
                $info['operators'] = $ops;
                $info['count']     = array_sum(array_column($ops, 'qty'));
                $info['cost_usd']  = $cheapest['price_usd'] ?? 0;
                $info['cost_ngn']  = $cheapest['price_ngn'] ?? 0;
                $services[] = $info;
            }

            usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));
            return ['success' => true, 'data' => $services];
        } catch (\Exception $e) {
            Log::error('5SIM getServices: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach 5SIM API.'];
        }
    }

    /**
     * Order a number from a specific operator (or 'any' for cheapest).
     * Returns ['success'=>true, 'data'=>['order_id'=>'...','number'=>'...','cost_usd'=>X,'cost_ngn'=>X]]
     */
    public function orderNumber(string $country, string $product, string $operator = 'any'): array
    {
        try {
            $op  = $operator ?: 'any';
            $url = $this->baseUrl . '/user/buy/activation/'
                . urlencode($country) . '/' . urlencode($op) . '/' . urlencode($product);
            $response = $this->authClient()->get($url);
            Log::info('5SIM orderNumber [' . $country . '/' . $op . '/' . $product . '] HTTP ' . $response->status() . ' | ' . substr($response->body(), 0, 200));

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
