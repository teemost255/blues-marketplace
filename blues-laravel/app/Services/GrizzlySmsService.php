<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GrizzlySMS API Integration
 * Docs: https://grizzlysms.com/docs
 * Base: https://api.grizzlysms.com/stubs/handler_api.php
 * All requests are HTTP GET; responses are plain-text strings (not JSON).
 */
class GrizzlySmsService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.grizzlysms.com/stubs/handler_api.php';
    private float  $usdToNgn;

    // Numeric country codes (SMS-Activate / GrizzlySMS compatible)
    private const COUNTRIES = [
        ['code' => '12',  'name' => 'USA',              'iso' => 'us'],
        ['code' => '22',  'name' => 'India',             'iso' => 'in'],
        ['code' => '73',  'name' => 'Brazil',            'iso' => 'br'],
        ['code' => '36',  'name' => 'Canada',            'iso' => 'ca'],
        ['code' => '16',  'name' => 'United Kingdom',    'iso' => 'gb'],
        ['code' => '43',  'name' => 'Germany',           'iso' => 'de'],
        ['code' => '78',  'name' => 'France',            'iso' => 'fr'],
        ['code' => '86',  'name' => 'Italy',             'iso' => 'it'],
        ['code' => '56',  'name' => 'Spain',             'iso' => 'es'],
        ['code' => '48',  'name' => 'Netherlands',       'iso' => 'nl'],
        ['code' => '46',  'name' => 'Sweden',            'iso' => 'se'],
        ['code' => '15',  'name' => 'Poland',            'iso' => 'pl'],
        ['code' => '82',  'name' => 'Belgium',           'iso' => 'be'],
        ['code' => '50',  'name' => 'Austria',           'iso' => 'at'],
        ['code' => '63',  'name' => 'Czech Republic',    'iso' => 'cz'],
        ['code' => '32',  'name' => 'Romania',           'iso' => 'ro'],
        ['code' => '83',  'name' => 'Bulgaria',          'iso' => 'bg'],
        ['code' => '84',  'name' => 'Hungary',           'iso' => 'hu'],
        ['code' => '23',  'name' => 'Ireland',           'iso' => 'ie'],
        ['code' => '0',   'name' => 'Russia',            'iso' => 'ru'],
        ['code' => '1',   'name' => 'Ukraine',           'iso' => 'ua'],
        ['code' => '51',  'name' => 'Belarus',           'iso' => 'by'],
        ['code' => '2',   'name' => 'Kazakhstan',        'iso' => 'kz'],
        ['code' => '40',  'name' => 'Uzbekistan',        'iso' => 'uz'],
        ['code' => '11',  'name' => 'Kyrgyzstan',        'iso' => 'kg'],
        ['code' => '62',  'name' => 'Turkey',            'iso' => 'tr'],
        ['code' => '6',   'name' => 'Indonesia',         'iso' => 'id'],
        ['code' => '4',   'name' => 'Philippines',       'iso' => 'ph'],
        ['code' => '10',  'name' => 'Vietnam',           'iso' => 'vn'],
        ['code' => '7',   'name' => 'Malaysia',          'iso' => 'my'],
        ['code' => '52',  'name' => 'Thailand',          'iso' => 'th'],
        ['code' => '3',   'name' => 'China',             'iso' => 'cn'],
        ['code' => '14',  'name' => 'Hong Kong',         'iso' => 'hk'],
        ['code' => '55',  'name' => 'Taiwan',            'iso' => 'tw'],
        ['code' => '60',  'name' => 'Bangladesh',        'iso' => 'bd'],
        ['code' => '66',  'name' => 'Pakistan',          'iso' => 'pk'],
        ['code' => '81',  'name' => 'Nepal',             'iso' => 'np'],
        ['code' => '64',  'name' => 'Sri Lanka',         'iso' => 'lk'],
        ['code' => '5',   'name' => 'Myanmar',           'iso' => 'mm'],
        ['code' => '24',  'name' => 'Cambodia',          'iso' => 'kh'],
        ['code' => '72',  'name' => 'Mongolia',          'iso' => 'mn'],
        ['code' => '53',  'name' => 'Saudi Arabia',      'iso' => 'sa'],
        ['code' => '94',  'name' => 'UAE',               'iso' => 'ae'],
        ['code' => '47',  'name' => 'Iraq',              'iso' => 'iq'],
        ['code' => '13',  'name' => 'Israel',            'iso' => 'il'],
        ['code' => '57',  'name' => 'Iran',              'iso' => 'ir'],
        ['code' => '21',  'name' => 'Egypt',             'iso' => 'eg'],
        ['code' => '19',  'name' => 'Nigeria',           'iso' => 'ng'],
        ['code' => '38',  'name' => 'Ghana',             'iso' => 'gh'],
        ['code' => '8',   'name' => 'Kenya',             'iso' => 'ke'],
        ['code' => '31',  'name' => 'South Africa',      'iso' => 'za'],
        ['code' => '37',  'name' => 'Morocco',           'iso' => 'ma'],
        ['code' => '41',  'name' => 'Cameroon',          'iso' => 'cm'],
        ['code' => '75',  'name' => 'Uganda',            'iso' => 'ug'],
        ['code' => '9',   'name' => 'Tanzania',          'iso' => 'tz'],
        ['code' => '58',  'name' => 'Algeria',           'iso' => 'dz'],
        ['code' => '89',  'name' => 'Tunisia',           'iso' => 'tn'],
        ['code' => '61',  'name' => 'Senegal',           'iso' => 'sn'],
        ['code' => '69',  'name' => 'Mali',              'iso' => 'ml'],
        ['code' => '39',  'name' => 'Argentina',         'iso' => 'ar'],
        ['code' => '54',  'name' => 'Mexico',            'iso' => 'mx'],
        ['code' => '33',  'name' => 'Colombia',          'iso' => 'co'],
        ['code' => '65',  'name' => 'Peru',              'iso' => 'pe'],
        ['code' => '91',  'name' => 'Bolivia',           'iso' => 'bo'],
        ['code' => '70',  'name' => 'Venezuela',         'iso' => 've'],
    ];

    // Short service codes → human-readable display names
    private const SERVICE_NAMES = [
        'go'  => 'Google',
        'tg'  => 'Telegram',
        'wa'  => 'WhatsApp',
        'wv'  => 'WhatsApp',
        'wb'  => 'WhatsApp',
        'wp'  => 'WhatsApp',
        'fb'  => 'Facebook',
        'tw'  => 'Twitter / X',
        'ig'  => 'Instagram',
        'tk'  => 'TikTok',
        'am'  => 'Amazon',
        'vi'  => 'Viber',
        'ln'  => 'LinkedIn',
        'ub'  => 'Uber',
        'mm'  => 'Microsoft',
        'ya'  => 'Yandex',
        'vk'  => 'VKontakte',
        'we'  => 'WeChat',
        'nf'  => 'Netflix',
        'dr'  => 'Discord',
        'sn'  => 'Snapchat',
        'ma'  => 'Mail.ru',
        'ok'  => 'Odnoklassniki',
        'av'  => 'Avito',
        'ot'  => 'Other',
    ];

    public function __construct()
    {
        $this->apiKey   = trim(Setting::get('grizzlysms_api_key', ''));
        $this->usdToNgn = (float) Setting::get('usd_to_ngn_rate', '1600');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    private function usdToNgn(float $usd): float
    {
        return round($usd * $this->usdToNgn, 2);
    }

    /** All API calls are plain GET; response is a plain-text string. */
    private function request(array $params): string
    {
        $params['api_key'] = $this->apiKey;
        $response = Http::timeout(20)->get($this->baseUrl, $params);
        Log::info('GrizzlySMS [' . ($params['action'] ?? '?') . '] HTTP ' . $response->status() . ' | ' . substr($response->body(), 0, 200));
        return trim($response->body());
    }

    // ── Balance ────────────────────────────────────────────────────────────────

    public function getBalance(): array
    {
        try {
            $resp = $this->request(['action' => 'getBalance']);
            // Response: "ACCESS_BALANCE:12.50"
            if (str_starts_with($resp, 'ACCESS_BALANCE:')) {
                $bal = (float) explode(':', $resp)[1];
                return ['success' => true, 'data' => [
                    'balance_usd' => $bal,
                    'balance_ngn' => $this->usdToNgn($bal),
                    'balance'     => $bal,
                ]];
            }
            return ['success' => false, 'message' => $resp];
        } catch (\Exception $e) {
            Log::error('GrizzlySMS getBalance: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Service temporarily unavailable. Please try again.'];
        }
    }

    // ── Countries ─────────────────────────────────────────────────────────────

    /** Returns hardcoded country list with numeric codes for GrizzlySMS. */
    public function getCountries(): array
    {
        $sorted = self::COUNTRIES;
        usort($sorted, fn($a, $b) => strcmp($a['name'], $b['name']));
        return ['success' => true, 'data' => $sorted];
    }

    // ── Services ──────────────────────────────────────────────────────────────

    /**
     * Fetches available services + prices for a given numeric country code.
     * Uses getPrices action; response is JSON.
     * [{serviceId, name, count, cost_usd, cost_ngn}]
     */
    public function getServices(string $countryCode): array
    {
        try {
            // API returns: {countryCode: {serviceCode: {count, cost, retry}}}
            // Fetch with country param so the response is smaller/faster.
            $resp = $this->request(['action' => 'getPrices', 'country' => $countryCode]);
            $data = json_decode($resp, true);

            if (!is_array($data) || empty($data)) {
                return ['success' => false, 'message' => 'No services available. Please try again.'];
            }

            // The outer key is the country code (as integer after json_decode).
            $countryInt      = (int) $countryCode;
            $countryServices = $data[$countryInt] ?? $data[$countryCode] ?? null;

            // If still not found, try any single key (some responses wrap in one country key)
            if (!$countryServices && count($data) === 1) {
                $countryServices = reset($data);
            }

            if (!$countryServices || !is_array($countryServices)) {
                return ['success' => false, 'message' => 'No services available for the selected country.'];
            }

            $services = [];
            foreach ($countryServices as $serviceCode => $priceInfo) {
                if (!is_array($priceInfo)) continue;

                $count    = (int)($priceInfo['count'] ?? 0);
                $priceUsd = (float)($priceInfo['cost'] ?? 0);

                if ($count <= 0) continue;

                $name = self::SERVICE_NAMES[$serviceCode] ?? ucwords(str_replace('_', ' ', (string)$serviceCode));

                $services[] = [
                    'serviceId' => (string)$serviceCode,
                    'name'      => $name,
                    'count'     => $count,
                    'cost_usd'  => $priceUsd,
                    'cost_ngn'  => $this->usdToNgn($priceUsd),
                ];
            }

            if (empty($services)) {
                return ['success' => false, 'message' => 'No services available for the selected country.'];
            }

            usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));
            return ['success' => true, 'data' => $services];
        } catch (\Exception $e) {
            Log::error('GrizzlySMS getServices: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Service temporarily unavailable. Please try again.'];
        }
    }

    // ── Order ─────────────────────────────────────────────────────────────────

    /**
     * Orders a virtual number.
     * Returns ['success'=>true, 'data'=>['order_id','number']]
     */
    public function orderNumber(string $countryCode, string $serviceCode): array
    {
        try {
            $resp = $this->request([
                'action'  => 'getNumber',
                'service' => $serviceCode,
                'country' => $countryCode,
            ]);
            Log::info('GrizzlySMS orderNumber [' . $countryCode . '/' . $serviceCode . '] response: ' . $resp);

            // Response: "ACCESS_NUMBER:38496653:66846426435"
            if (str_starts_with($resp, 'ACCESS_NUMBER:')) {
                $parts = explode(':', $resp, 3);
                return ['success' => true, 'data' => [
                    'order_id' => $parts[1] ?? '',
                    'number'   => $parts[2] ?? '',
                ]];
            }

            $msg = match($resp) {
                'NO_NUMBERS' => 'Out of stock. Please try again later.',
                'NO_BALANCE' => 'Out of stock. Please try again later.',
                'BAD_KEY'    => 'Service configuration error. Please contact support.',
                'BAD_SERVICE'=> 'Out of stock. Please try again later.',
                'BAD_COUNTRY'=> 'This country is not supported.',
                default      => 'Could not get a number. Please try again.',
            };
            return ['success' => false, 'message' => $msg, 'raw' => $resp];
        } catch (\Exception $e) {
            Log::error('GrizzlySMS orderNumber: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Out of stock. Please try again later.'];
        }
    }

    // ── Check SMS ─────────────────────────────────────────────────────────────

    /**
     * Checks activation status / SMS code.
     * status: pending|received|cancelled
     */
    public function checkSms(string $orderId): array
    {
        try {
            $resp = $this->request(['action' => 'getStatus', 'id' => (int) $orderId]);

            if (str_starts_with($resp, 'STATUS_OK:')) {
                $code = substr($resp, strlen('STATUS_OK:'));
                return ['success' => true, 'data' => ['status' => 'received', 'sms' => $code]];
            }
            if (str_starts_with($resp, 'STATUS_WAIT_RETRY:')) {
                $code = substr($resp, strlen('STATUS_WAIT_RETRY:'));
                return ['success' => true, 'data' => ['status' => 'pending', 'sms' => $code ?: null]];
            }
            if ($resp === 'STATUS_CANCEL') {
                return ['success' => true, 'data' => ['status' => 'cancelled', 'sms' => null]];
            }
            // API-level errors — treat as pending so the UI can keep polling
            if (in_array($resp, ['BAD_KEY', 'BAD_ACTION', 'NO_ACTIVATION', 'WRONG_ACTIVATION_ID'])) {
                Log::warning('GrizzlySMS checkSms error response: ' . $resp . ' for order ' . $orderId);
                return ['success' => false, 'message' => 'Activation error: ' . $resp];
            }
            // STATUS_WAIT_CODE, STATUS_WAIT_RESEND, or any other waiting state
            return ['success' => true, 'data' => ['status' => 'pending', 'sms' => null]];
        } catch (\Exception $e) {
            Log::error('GrizzlySMS checkSms: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach GrizzlySMS API.'];
        }
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function cancelOrder(string $orderId): array
    {
        try {
            // Status 8 = cancel and return money (SMS-Activate compatible standard)
            $resp = $this->request(['action' => 'setStatus', 'id' => $orderId, 'status' => 8]);
            if ($resp === 'ACCESS_CANCEL') {
                return ['success' => true, 'message' => 'Order cancelled successfully.'];
            }
            $msg = match($resp) {
                'BAD_KEY'        => 'Invalid GrizzlySMS API key.',
                'BAD_ACTION'     => 'Invalid action.',
                'NO_ACTIVATION'  => 'Activation not found.',
                'BAD_STATUS'     => 'Cannot cancel at this stage.',
                default          => 'Cancel response: ' . $resp,
            };
            // If already cancelled or completed, treat as success
            if (in_array($resp, ['ACCESS_CANCEL', 'STATUS_CANCEL', 'STATUS_OK'])) {
                return ['success' => true, 'message' => $resp];
            }
            return ['success' => false, 'message' => $msg];
        } catch (\Exception $e) {
            Log::error('GrizzlySMS cancelOrder: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach GrizzlySMS API.'];
        }
    }
}
