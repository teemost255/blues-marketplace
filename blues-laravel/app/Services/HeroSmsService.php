<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Hero-SMS API (sms-activate compatible style)
 * Endpoint: https://hero-sms.com/stubs/handler_api.php
 */
class HeroSmsService
{
    private string $apiKey;
    private string $baseUrl = 'https://hero-sms.com/stubs/handler_api.php';

    public function __construct()
    {
        $this->apiKey = trim(Setting::get('herosms_api_key', ''));
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    // ── HTTP helpers ───────────────────────────────────────────────────────────

    private function client()
    {
        return Http::withOptions([
            'curl' => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1],
        ])->withHeaders([
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept'          => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->timeout(20);
    }

    private function call(array $params): array
    {
        $params['api_key'] = $this->apiKey;
        try {
            $response = $this->client()->get($this->baseUrl, $params);
            $status   = $response->status();
            $body     = trim($response->body());

            Log::info('HeroSms [' . ($params['action'] ?? '?') . '] HTTP ' . $status . ' | ' . substr($body, 0, 300));

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Hero-SMS request failed (HTTP ' . $status . ').'];
            }

            return ['success' => true, 'body' => $body];
        } catch (\Exception $e) {
            Log::error('HeroSmsService error [' . ($params['action'] ?? '?') . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach Hero-SMS. Check your connection.'];
        }
    }

    /** Parse a plain-text sms-activate style response, e.g. ACCESS_NUMBER:id:phone */
    private function parsePlain(string $body): array
    {
        // JSON error response: {"title":"BAD_KEY","details":"..."}
        if (str_starts_with($body, '{') || str_starts_with($body, '[')) {
            $json = json_decode($body, true);
            if (is_array($json)) {
                if (isset($json['title']) && $json['title'] !== 'OK') {
                    return ['ok' => false, 'code' => $json['title'], 'detail' => $json['details'] ?? ''];
                }
            }
        }

        $parts = explode(':', $body, 3);
        return ['ok' => true, 'code' => $parts[0], 'parts' => $parts];
    }

    // ── Public API methods ─────────────────────────────────────────────────────

    /** Returns ['success'=>true,'data'=>['balance'=>X.XX]] */
    public function getBalance(): array
    {
        $r = $this->call(['action' => 'getBalance']);
        if (!$r['success']) return $r;

        $body = $r['body'];
        // Successful: "ACCESS_BALANCE:12.34"
        if (str_starts_with($body, 'ACCESS_BALANCE:')) {
            return ['success' => true, 'data' => ['balance' => (float) substr($body, 15)]];
        }

        $p = $this->parsePlain($body);
        if (!$p['ok']) {
            return ['success' => false, 'message' => $p['detail'] ?: $p['code']];
        }

        return ['success' => false, 'message' => 'Unexpected balance response: ' . $body];
    }

    /**
     * Returns list of countries as array of ['id'=>N, 'name'=>'Country'].
     * This endpoint is public — no auth required.
     */
    public function getCountries(): array
    {
        // Public endpoint — omit api_key to avoid bad-key errors on unconfigured keys
        try {
            $response = $this->client()->get($this->baseUrl, ['action' => 'getCountries']);
            $body = trim($response->body());
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Could not reach Hero-SMS.'];
        }

        $json = json_decode($body, true);
        if (!is_array($json)) {
            return ['success' => false, 'message' => 'Unexpected countries response.'];
        }

        // Convert {"1":{"id":1,"eng":"Ukraine","iso":"ua",...},...} → flat array
        // Must return 'code' key (string) so the frontend country selector works correctly.
        $countries = [];
        foreach ($json as $item) {
            if (!isset($item['id']) || !($item['visible'] ?? 1)) continue;
            $countries[] = [
                'code' => (string) $item['id'],
                'name' => $item['eng'] ?? $item['rus'] ?? 'Country ' . $item['id'],
                'iso'  => strtolower($item['iso'] ?? ''),
            ];
        }

        usort($countries, fn($a, $b) => strcmp($a['name'], $b['name']));

        return ['success' => true, 'data' => $countries];
    }

    /**
     * Returns services available for a given country with pricing.
     * Response: {"service_name":{"count":N,"cost":X.XX},...}
     */
    public function getServices(?string $country = null): array
    {
        $params = ['action' => 'getPrices'];
        if ($country !== null && $country !== '') {
            $params['country'] = $country;
        }

        $r = $this->call($params);
        if (!$r['success']) return $r;

        $json = json_decode($r['body'], true);
        if (!is_array($json)) {
            $p = $this->parsePlain($r['body']);
            if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: $p['code']];
            return ['success' => false, 'message' => 'Unexpected services response.'];
        }

        // If error JSON
        if (isset($json['title'])) {
            return ['success' => false, 'message' => $json['details'] ?? $json['title']];
        }

        // When a country is specified, Hero-SMS wraps response as:
        // {"countryId": {"service": {"count":N,"cost":X.XX,"physicalCount":N}, ...}}
        // When no country, it's: {"service": {"countryId": {"count":N,...}, ...}}
        // Detect the wrapped format by checking if the first value is itself a map of maps.
        $firstValue = reset($json);
        if (is_array($firstValue) && !isset($firstValue['count']) && !isset($firstValue['cost'])) {
            // Unwrap one level — use the first (and usually only) country block
            $json = $firstValue;
        }

        // Convert to array of service objects
        $services = [];
        foreach ($json as $name => $info) {
            if (is_array($info) && isset($info['count']) && (int)($info['count']) > 0) {
                $services[] = [
                    'serviceId' => $name,
                    'name'      => $name,
                    'count'     => (int) $info['count'],
                    'cost'      => (float) ($info['cost'] ?? 0),
                ];
            }
        }

        usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));

        return ['success' => true, 'data' => $services];
    }

    /**
     * Order a number.
     * Returns ['success'=>true,'data'=>['order_id'=>'...','number'=>'...']]
     */
    public function orderNumber(string $country, string $service): array
    {
        $r = $this->call([
            'action'  => 'getNumber',
            'country' => $country,
            'service' => $service,
        ]);
        if (!$r['success']) return $r;

        $body = $r['body'];

        // Success: "ACCESS_NUMBER:12345:79001234567"
        if (str_starts_with($body, 'ACCESS_NUMBER:')) {
            $parts = explode(':', $body, 3);
            return ['success' => true, 'data' => [
                'order_id' => $parts[1] ?? '',
                'number'   => $parts[2] ?? '',
            ]];
        }

        // Known error strings
        $errors = [
            'NO_NUMBERS'  => 'No numbers available for this service/country.',
            'NO_BALANCE'  => 'Insufficient Hero-SMS account balance.',
            'BAD_SERVICE' => 'Invalid service selected.',
            'BAD_COUNTRY' => 'Invalid country selected.',
            'BAD_KEY'     => 'Hero-SMS API key is invalid.',
        ];

        $p = $this->parsePlain($body);
        if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: ($errors[$p['code']] ?? $p['code'])];

        $code = $p['code'];
        return ['success' => false, 'message' => $errors[$code] ?? ('Order failed: ' . $body)];
    }

    /**
     * Check SMS status for an order.
     * Returns ['success'=>true,'data'=>['status_raw'=>'...','sms'=>'...','status'=>1|3|6]]
     */
    public function checkSms(string $orderId): array
    {
        $r = $this->call(['action' => 'getStatus', 'id' => $orderId]);
        if (!$r['success']) return $r;

        $body = $r['body'];

        // STATUS_WAIT_CODE         → pending (1)
        // STATUS_WAIT_RESEND       → pending (1)
        // STATUS_WAIT_CODE:X       → pending (1)
        // STATUS_OK:CODE123        → completed (3)
        // STATUS_CANCEL            → cancelled (6)

        if (str_starts_with($body, 'STATUS_OK:')) {
            $code = substr($body, 10);
            return ['success' => true, 'data' => ['status' => 3, 'sms' => $code, 'status_raw' => $body]];
        }

        if (str_starts_with($body, 'STATUS_CANCEL')) {
            return ['success' => true, 'data' => ['status' => 6, 'sms' => null, 'status_raw' => $body]];
        }

        if (str_starts_with($body, 'STATUS_WAIT')) {
            return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
        }

        $p = $this->parsePlain($body);
        if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: $p['code']];

        return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
    }

    /**
     * Cancel an order (setStatus with status=8).
     */
    public function cancelOrder(string $orderId): array
    {
        $r = $this->call(['action' => 'setStatus', 'id' => $orderId, 'status' => 8]);
        if (!$r['success']) return $r;

        $body = $r['body'];

        if (str_starts_with($body, 'ACCESS_CANCEL')) {
            return ['success' => true, 'data' => []];
        }

        $p = $this->parsePlain($body);
        if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: $p['code']];

        return ['success' => false, 'message' => 'Cancel failed: ' . $body];
    }
}
