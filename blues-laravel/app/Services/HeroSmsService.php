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
            $status   = $response->status();

            // Log the raw response for debugging
            Log::debug('HeroSMS getPrices raw response', [
                'country'      => $country,
                'http_status'  => $status,
                'body_length'  => strlen($body),
                'body_preview' => substr($body, 0, 500),
            ]);

            if ($status !== 200 || empty($body)) {
                Log::warning('HeroSMS getPrices: bad HTTP status or empty body', [
                    'country' => $country, 'status' => $status,
                ]);
                return [];
            }

            // Try JSON first — most common format
            $data = $response->json();
            if (is_array($data) && !empty($data)) {
                $normalized = $this->normalizePrices($data, $country);
                Log::debug('HeroSMS getPrices normalized', [
                    'country' => $country,
                    'shape_count' => count($normalized),
                    'sample' => array_slice($normalized, 0, 3, true),
                ]);
                return $normalized;
            }

            // Non-JSON response (e.g. "BAD_ACTION", "ERROR_KEY", plain number)
            Log::warning('HeroSMS getPrices: non-JSON or empty JSON response', [
                'country' => $country,
                'body'    => $body,
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('HeroSMS getPrices error', ['country' => $country, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Return the raw body from a getPrices call — used by the admin debug endpoint.
     */
    public function getRawPricesResponse(int $country): array
    {
        try {
            $params = ['api_key' => $this->apiKey, 'action' => 'getPrices'];
            if ($country > 0) $params['country'] = $country;

            $response = Http::timeout(15)->get($this->baseUrl, $params);
            $body     = trim($response->body());

            return [
                'http_status'  => $response->status(),
                'body_length'  => strlen($body),
                'body_preview' => substr($body, 0, 2000),
                'is_json'      => is_array($response->json()),
                'json_keys'    => is_array($response->json()) ? array_slice(array_keys($response->json()), 0, 10) : [],
                'first_value'  => is_array($response->json()) ? json_encode(array_values($response->json())[0] ?? null) : null,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
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

        // Shape A: { "tg": {"cost": "0.25", "count": 100}, ... }
        // Keys are service codes (non-numeric), values have 'cost' directly (may be string or float)
        if (!is_numeric($firstKey) && is_array($firstValue) && array_key_exists('cost', $firstValue)) {
            $result = [];
            foreach ($data as $code => $info) {
                $cost = is_array($info) ? ($info['cost'] ?? null) : $info;
                if ($cost !== null && is_numeric($cost) && (float)$cost > 0) {
                    $result[(string) $code] = (float) $cost;
                }
            }
            return $result;
        }

        // Shape B: { "tg": {"0": {"cost":"0.25"}, "7": {"cost":"0.30"}}, ... }
        // Keys are service codes (non-numeric), values are country-keyed maps
        if (!is_numeric($firstKey) && is_array($firstValue) && !array_key_exists('cost', $firstValue)) {
            $result = [];
            foreach ($data as $code => $countryMap) {
                if (!is_array($countryMap)) continue;

                // Try exact country match first, then global (0), then minimum
                $cost = null;
                if (isset($countryMap[$country]) && is_array($countryMap[$country]) && isset($countryMap[$country]['cost'])) {
                    $cost = $countryMap[$country]['cost'];
                } elseif (isset($countryMap['0']) && is_array($countryMap['0']) && isset($countryMap['0']['cost'])) {
                    $cost = $countryMap['0']['cost'];
                } elseif (isset($countryMap[0]) && is_array($countryMap[0]) && isset($countryMap[0]['cost'])) {
                    $cost = $countryMap[0]['cost'];
                } else {
                    // Pick the minimum cost across all countries
                    $costs = [];
                    foreach ($countryMap as $v) {
                        if (is_array($v) && isset($v['cost']) && is_numeric($v['cost']) && (float)$v['cost'] > 0) {
                            $costs[] = (float) $v['cost'];
                        }
                    }
                    if (!empty($costs)) $cost = min($costs);
                }

                if ($cost !== null && is_numeric($cost) && (float)$cost > 0) {
                    $result[(string) $code] = (float) $cost;
                }
            }
            return $result;
        }

        // Shape C: { "0": {"cost":"0.25"}, "7": {"cost":"0.30"}, ... }
        // Keys are country IDs (numeric), values have 'cost' directly — single-service price response
        if (is_numeric($firstKey) && is_array($firstValue) && array_key_exists('cost', $firstValue)) {
            $target = $data[$country] ?? $data['0'] ?? $data[0] ?? array_values($data)[0] ?? null;
            if ($target && isset($target['cost']) && is_numeric($target['cost'])) {
                return ['*' => (float) $target['cost']];
            }
        }

        // Shape D (confirmed from live API):
        //   { "countryId": { "serviceCode": {"cost": 0.145, "count": 7650, ...} }, ... }
        // Outer keys = numeric country IDs, inner keys = service codes, leaf has 'cost'
        if (is_numeric($firstKey) && is_array($firstValue) && !array_key_exists('cost', $firstValue)) {
            // Confirm inner values look like service entries
            $firstInner = !empty($firstValue) ? array_values($firstValue)[0] : null;
            if (is_array($firstInner) && array_key_exists('cost', $firstInner)) {
                if ($country > 0) {
                    // Use prices for the exact requested country
                    $countryData = $data[$country] ?? $data[(string) $country] ?? null;
                    if ($countryData && is_array($countryData)) {
                        $result = [];
                        foreach ($countryData as $code => $info) {
                            if (is_array($info) && isset($info['cost']) && is_numeric($info['cost']) && (float)$info['cost'] > 0) {
                                $result[(string) $code] = (float) $info['cost'];
                            }
                        }
                        if (!empty($result)) return $result;
                    }
                }

                // country = 0 (all countries) OR country not found — take the minimum price
                // per service code across all countries in the response
                $result = [];
                foreach ($data as $cid => $services) {
                    if (!is_array($services)) continue;
                    foreach ($services as $code => $info) {
                        if (!is_array($info) || !isset($info['cost']) || !is_numeric($info['cost'])) continue;
                        $cost = (float) $info['cost'];
                        if ($cost <= 0) continue;
                        if (!isset($result[(string) $code]) || $cost < $result[(string) $code]) {
                            $result[(string) $code] = $cost;
                        }
                    }
                }
                return $result;
            }
        }

        Log::warning('HeroSMS getPrices: unrecognized response shape', [
            'first_key'   => $firstKey,
            'first_value' => json_encode(array_slice((array) $firstValue, 0, 2, true)),
        ]);
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

        Log::info('HeroSMS getStatus raw response', [
            'activation_id' => $activationId,
            'raw'           => $raw,
        ]);

        if (str_starts_with($raw, 'STATUS_OK:')) {
            $code = trim(substr($raw, strlen('STATUS_OK:')));
            return ['status' => 'received', 'code' => $code];
        }

        // Some HeroSMS deployments send "STATUS_OK" with the code appended after a space
        if (str_starts_with($raw, 'STATUS_OK ')) {
            $code = trim(substr($raw, strlen('STATUS_OK ')));
            if ($code !== '') {
                return ['status' => 'received', 'code' => $code];
            }
        }

        // Handle bare numeric code responses (some API clones return just the code)
        if (is_numeric(trim($raw)) && strlen(trim($raw)) >= 4) {
            return ['status' => 'received', 'code' => trim($raw)];
        }

        if ($raw === 'STATUS_WAIT_CODE' || $raw === 'STATUS_WAIT_RESEND') {
            return ['status' => 'waiting', 'code' => null];
        }
        if ($raw === 'STATUS_CANCEL' || $raw === 'WRONG_ACTIVATION_ID' || $raw === 'NO_ACTIVATION') {
            return ['status' => 'cancelled', 'code' => null];
        }

        Log::warning('HeroSMS getStatus: unrecognised response', [
            'activation_id' => $activationId,
            'raw'           => $raw,
        ]);
        return ['status' => 'unknown', 'code' => null, 'raw' => $raw];
    }

    public function setStatusReady(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 1]);
        return $raw === 'ACCESS_READY';
    }

    public function setStatusResend(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 3]);
        return in_array($raw, ['ACCESS_RETRY_GET', '1', 'ACCESS_READY']);
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
