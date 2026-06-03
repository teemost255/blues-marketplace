<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\{Http, Log};

class HeroSmsService
{
    private string $apiKey;
    private string $baseUrl = 'https://hero-sms.com/stubs/handler_api.php';

    public function __construct()
    {
        $this->apiKey = Setting::get('herosms_api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getBalance(): float
    {
        $raw = $this->call(['action' => 'getBalance']);
        if (str_starts_with($raw, 'ACCESS_BALANCE:')) {
            return (float) substr($raw, strlen('ACCESS_BALANCE:'));
        }
        return 0.0;
    }

    public function getCountries(): array
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl, [
                'api_key' => $this->apiKey,
                'action'  => 'getCountries',
            ]);
            $data = $response->json();
            if (!is_array($data)) return [];
            return $data;
        } catch (\Exception $e) {
            Log::error('HeroSMS getCountries error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getServicesForCountry(int $country): array
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl, [
                'api_key' => $this->apiKey,
                'action'  => 'getNumbersStatus',
                'country' => $country,
            ]);
            $data = $response->json();
            if (!is_array($data)) return [];
            return $data;
        } catch (\Exception $e) {
            Log::error('HeroSMS getNumbersStatus error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch prices for all services in a given country.
     * Returns [ 'service_code' => float_usd_price, ... ]
     * Falls back to empty array on failure.
     */
    public function getPricesForCountry(int $country): array
    {
        try {
            $params = [
                'api_key' => $this->apiKey,
                'action'  => 'getPrices',
            ];
            if ($country > 0) {
                $params['country'] = $country;
            }

            $response = Http::timeout(15)->get($this->baseUrl, $params);
            $body     = trim($response->body());

            // Try JSON first — most common format
            $data = $response->json();
            if (is_array($data)) {
                return $this->normalizePrices($data, $country);
            }

            // Fallback: plain numeric (single-service response like "0.25")
            if (is_numeric($body)) {
                return [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('HeroSMS getPrices error', ['country' => $country, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Normalize the price response into [ 'service_code' => float_price ]
     *
     * Known response shapes from HeroSMS getPrices:
     *
     *   Shape A — country-specific query (country > 0):
     *     { "tg": { "cost": 0.25, "count": 100 }, "wa": { ... }, ... }
     *
     *   Shape B — all-countries query (country = 0), service keys → country map:
     *     { "tg": { "0": { "cost": 0.25, "count": 100 }, "7": { "cost": 0.30 } }, ... }
     *
     *   Shape C — service-specific query, keys are country IDs:
     *     { "0": { "cost": 0.25, "count": 100 }, "7": { ... }, ... }
     */
    private function normalizePrices(array $data, int $country): array
    {
        if (empty($data)) return [];

        $firstKey   = array_key_first($data);
        $firstValue = $data[$firstKey] ?? null;

        // Shape A: keys are service codes (non-numeric), values have 'cost' directly
        if (!is_numeric($firstKey) && is_array($firstValue) && isset($firstValue['cost'])) {
            $result = [];
            foreach ($data as $code => $info) {
                $cost = is_array($info) ? ($info['cost'] ?? null) : $info;
                if ($cost !== null) {
                    $result[(string) $code] = (float) $cost;
                }
            }
            return $result;
        }

        // Shape B: keys are service codes (non-numeric), values are country-keyed maps
        // e.g. { "tg": { "0": {"cost":0.25}, "7": {"cost":0.30} } }
        if (!is_numeric($firstKey) && is_array($firstValue) && !isset($firstValue['cost'])) {
            $result = [];
            foreach ($data as $code => $countryMap) {
                if (!is_array($countryMap)) continue;
                // Prefer the requested country, fall back to global (0), then take min
                if (isset($countryMap[$country]['cost'])) {
                    $result[(string) $code] = (float) $countryMap[$country]['cost'];
                } elseif (isset($countryMap[0]['cost'])) {
                    $result[(string) $code] = (float) $countryMap[0]['cost'];
                } else {
                    // Pick the minimum price across all countries
                    $costs = array_filter(array_map(
                        fn($v) => is_array($v) ? ($v['cost'] ?? null) : null,
                        $countryMap
                    ), fn($c) => $c !== null);
                    if (!empty($costs)) {
                        $result[(string) $code] = (float) min($costs);
                    }
                }
            }
            return $result;
        }

        // Shape C: keys are country IDs (numeric), values have 'cost'
        if (is_numeric($firstKey) && is_array($firstValue) && isset($firstValue['cost'])) {
            $target = $data[$country] ?? $data[0] ?? null;
            if ($target && isset($target['cost'])) {
                return ['*' => (float) $target['cost']];
            }
        }

        return [];
    }

    /**
     * Get the NGN price for a specific service in a country.
     * Returns the full user-facing price in NGN (API cost × exchange rate + commission).
     */
    public static function calculateNgnPrice(float $usdCost): float
    {
        $exchangeRate    = (float) Setting::get('herosms_exchange_rate', '1600');
        $commissionType  = Setting::get('herosms_commission_type', 'flat');
        $commissionValue = (float) Setting::get('herosms_number_price', '200');

        $baseNgn = $usdCost * $exchangeRate;

        if ($commissionType === 'percentage') {
            $total = $baseNgn * (1 + $commissionValue / 100);
        } else {
            $total = $baseNgn + $commissionValue;
        }

        return ceil($total);
    }

    public function getNumber(string $service, int $country): array
    {
        $raw = $this->call([
            'action'  => 'getNumber',
            'service' => $service,
            'country' => $country,
        ]);

        if (str_starts_with($raw, 'ACCESS_NUMBER:')) {
            $parts = explode(':', $raw);
            return [
                'success'       => true,
                'activation_id' => $parts[1] ?? null,
                'phone_number'  => $parts[2] ?? null,
            ];
        }

        return ['success' => false, 'error' => $raw];
    }

    public function getStatus(string $activationId): array
    {
        $raw = $this->call([
            'action' => 'getStatus',
            'id'     => $activationId,
        ]);

        if (str_starts_with($raw, 'STATUS_OK:')) {
            return ['status' => 'received', 'code' => substr($raw, strlen('STATUS_OK:'))];
        }
        if ($raw === 'STATUS_WAIT_CODE') {
            return ['status' => 'waiting', 'code' => null];
        }
        if ($raw === 'STATUS_CANCEL') {
            return ['status' => 'cancelled', 'code' => null];
        }
        return ['status' => 'unknown', 'code' => null, 'raw' => $raw];
    }

    public function setStatusReady(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 1]);
        return $raw === 'ACCESS_READY';
    }

    public function setStatusComplete(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 6]);
        return in_array($raw, ['ACCESS_ACTIVATION', '1']);
    }

    public function setStatusCancel(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 8]);
        return in_array($raw, ['ACCESS_CANCEL', '1']);
    }

    private function call(array $params): string
    {
        try {
            $params['api_key'] = $this->apiKey;
            $response = Http::timeout(20)->get($this->baseUrl, $params);
            return trim($response->body());
        } catch (\Exception $e) {
            Log::error('HeroSMS API error', ['params' => $params, 'error' => $e->getMessage()]);
            return 'ERROR';
        }
    }
}
