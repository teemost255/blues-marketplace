<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\{Http, Log};

class GrizzlySmsService
{
    private string $apiKey;
    private string $baseUrl = 'https://grizzlysms.com/stubs/handler_api.php';

    public function __construct()
    {
        $this->apiKey = Setting::get('grizzly_api_key', '');
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
            Log::error('SMS service getCountries error', ['error' => $e->getMessage()]);
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

            // Normalize response shapes into { code => count }.
            // GrizzlySMS appends a country suffix to service codes:
            //   "tg_0" => 150  (country 0)   Strip "_N" to get "tg".
            // Shape A (flat):   { "tg_0": 150, "wa_0": 200 }
            // Shape B (object): { "tg_0": {"count": 150, ...}, ... }
            $normalized = [];
            foreach ($data as $rawCode => $value) {
                // Strip trailing _<digits> country suffix (e.g. "tg_0" → "tg")
                $code = preg_replace('/_\d+$/', '', (string) $rawCode);

                if (is_array($value)) {
                    $count = (int) ($value['count'] ?? $value['qty'] ?? 0);
                } else {
                    $count = (int) $value;
                }
                if ($count > 0) {
                    // Keep the highest count if the same service appears twice
                    $existing = $normalized[$code] ?? 0;
                    $normalized[$code] = max($existing, $count);
                }
            }
            return $normalized;
        } catch (\Exception $e) {
            Log::error('SMS service getNumbersStatus error', ['error' => $e->getMessage()]);
            return [];
        }
    }

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

            if ($status !== 200 || empty($body)) {
                return [];
            }

            $data = $response->json();
            if (is_array($data) && !empty($data)) {
                return $this->normalizePrices($data, $country);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('SMS service getPrices error', ['country' => $country, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function normalizePrices(array $data, int $country): array
    {
        if (empty($data)) return [];

        $firstKey   = array_key_first($data);
        $firstValue = $data[$firstKey] ?? null;

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

        if (!is_numeric($firstKey) && is_array($firstValue) && !array_key_exists('cost', $firstValue)) {
            $result = [];
            foreach ($data as $code => $countryMap) {
                if (!is_array($countryMap)) continue;
                $cost = null;
                if (isset($countryMap[$country]) && is_array($countryMap[$country]) && isset($countryMap[$country]['cost'])) {
                    $cost = $countryMap[$country]['cost'];
                } elseif (isset($countryMap['0']) && is_array($countryMap['0']) && isset($countryMap['0']['cost'])) {
                    $cost = $countryMap['0']['cost'];
                } elseif (isset($countryMap[0]) && is_array($countryMap[0]) && isset($countryMap[0]['cost'])) {
                    $cost = $countryMap[0]['cost'];
                } else {
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

        if (is_numeric($firstKey) && is_array($firstValue) && array_key_exists('cost', $firstValue)) {
            $target = $data[$country] ?? $data['0'] ?? $data[0] ?? array_values($data)[0] ?? null;
            if ($target && isset($target['cost']) && is_numeric($target['cost'])) {
                return ['*' => (float) $target['cost']];
            }
        }

        if (is_numeric($firstKey) && is_array($firstValue) && !array_key_exists('cost', $firstValue)) {
            $firstInner = !empty($firstValue) ? array_values($firstValue)[0] : null;
            if (is_array($firstInner) && array_key_exists('cost', $firstInner)) {
                if ($country > 0) {
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

        return [];
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

        Log::info('SMS service getStatus raw response', [
            'activation_id' => $activationId,
            'raw'           => $raw,
        ]);

        if (str_starts_with($raw, 'STATUS_OK:')) {
            $code = trim(substr($raw, strlen('STATUS_OK:')));
            return ['status' => 'received', 'code' => $code];
        }

        if (str_starts_with($raw, 'STATUS_OK ')) {
            $code = trim(substr($raw, strlen('STATUS_OK ')));
            if ($code !== '') {
                return ['status' => 'received', 'code' => $code];
            }
        }

        if (is_numeric(trim($raw)) && strlen(trim($raw)) >= 4) {
            return ['status' => 'received', 'code' => trim($raw)];
        }

        if ($raw === 'STATUS_WAIT_CODE' || $raw === 'STATUS_WAIT_RESEND') {
            return ['status' => 'waiting', 'code' => null];
        }
        if ($raw === 'STATUS_CANCEL' || $raw === 'WRONG_ACTIVATION_ID' || $raw === 'NO_ACTIVATION') {
            return ['status' => 'cancelled', 'code' => null];
        }

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
            Log::error('SMS provider API error', ['params' => array_diff_key($params, ['api_key' => '']), 'error' => $e->getMessage()]);
            return 'ERROR';
        }
    }
}
