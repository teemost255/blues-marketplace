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
                return ['success' => false, 'http_status' => $status, 'body' => $body, 'message' => 'Service request failed (HTTP ' . $status . ').'];
            }

            return ['success' => true, 'http_status' => $status, 'body' => $body];
        } catch (\Exception $e) {
            Log::error('HeroSmsService error [' . ($params['action'] ?? '?') . ']: ' . $e->getMessage());
            return ['success' => false, 'http_status' => 0, 'body' => '', 'message' => 'Service temporarily unavailable. Please try again.'];
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
            return ['success' => false, 'message' => 'Service temporarily unavailable. Please try again.'];
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
        foreach ($json as $code => $info) {
            if (is_array($info) && isset($info['count']) && (int)($info['count']) > 0) {
                $services[] = [
                    'serviceId' => $code,
                    'name'      => self::resolveServiceName($code),
                    'count'     => (int) $info['count'],
                    'cost'      => (float) ($info['cost'] ?? 0),
                ];
            }
        }

        usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));

        return ['success' => true, 'data' => $services];
    }

    /**
     * Maps Hero-SMS / sms-activate short service codes to human-readable names.
     * Falls back to uppercase of the code if not found.
     */
    public static function resolveServiceName(string $code): string
    {
        static $map = [
            // ── Messaging & Social ──────────────────────────────────
            'wa'        => 'WhatsApp',
            'tg'        => 'Telegram',
            'vi'        => 'Viber',
            'fb'        => 'Facebook',
            'ig'        => 'Instagram',
            'tw'        => 'Twitter / X',
            'tk'        => 'TikTok',
            'sc'        => 'Snapchat',
            'dc'        => 'Discord',
            'li'        => 'LinkedIn',
            'yt'        => 'YouTube',
            'rd'        => 'Reddit',
            'pi'        => 'Pinterest',
            'tt'        => 'Twitch',
            'sk'        => 'Skype',
            'im'        => 'IMO',
            'ln'        => 'Line',
            'za'        => 'Zalo',
            'wc'        => 'WeChat',
            'sg'        => 'Signal',
            'kk'        => 'KakaoTalk',
            'tb'        => 'Tumblr',
            'ki'        => 'Kik',
            'vk'        => 'VKontakte',
            'ok'        => 'Odnoklassniki',
            'mm'        => 'MoMo',
            'tm'        => 'Teams',
            'ws'        => 'WeChat (alt)',
            'fc'        => 'Facebook Creator',
            'me'        => 'Messenger',
            'bl'        => 'Bigo Live',
            'lk'        => 'Likee',

            // ── Google / Microsoft / Apple ───────────────────────────
            'go'        => 'Google',
            'gv'        => 'Gmail / Google Voice',
            'ms'        => 'Microsoft',
            'ot'        => 'Outlook',
            'ap'        => 'Apple / iCloud',

            // ── Ride / Delivery ──────────────────────────────────────
            'ub'        => 'Uber',
            'lt'        => 'Lyft',
            'bk'        => 'Bolt',
            'gf'        => 'Glovo',
            'rf'        => 'Rappi',
            'ip'        => 'inDriver',

            // ── Shopping / eCommerce ─────────────────────────────────
            'am'        => 'Amazon',
            'eb'        => 'eBay',
            'al'        => 'AliExpress',
            'sh'        => 'Shopee',
            'oz'        => 'OLX',
            'av'        => 'Avito',
            'wb'        => 'Wildberries',
            'oz2'       => 'Ozon',
            'lm'        => 'Lazada',
            'fl'        => 'Flipkart',

            // ── Food / Grocery ───────────────────────────────────────
            'dd'        => 'DoorDash',
            'ue'        => 'Uber Eats',
            'gr'        => 'Grubhub',

            // ── Finance / Crypto ─────────────────────────────────────
            'pp'        => 'PayPal',
            'bn'        => 'Binance',
            'cb'        => 'Coinbase',
            'bb'        => 'Bybit',
            'ku'        => 'KuCoin',
            'kp'        => 'Kraken',
            'ht'        => 'HTX (Huobi)',
            'mx'        => 'MEXC',
            'ok2'       => 'OKX',
            'gate'      => 'Gate.io',
            'bt'        => 'Bitget',
            'wbt'       => 'WhiteBIT',
            'pm'        => 'PerfectMoney',
            'qw'        => 'Qiwi',
            'ym'        => 'Yoomoney',
            'wb2'       => 'WebMoney',
            'mn'        => 'MoneyGram',
            'wu'        => 'Western Union',
            'cas'       => 'Cash App',
            'vmo'       => 'Venmo',
            'zl'        => 'Zelle',
            'rvt'       => 'Revolut',
            'nf'        => 'Neteller',
            'sk2'       => 'Skrill',

            // ── Streaming / Entertainment ────────────────────────────
            'nf2'       => 'Netflix',
            'sp'        => 'Spotify',
            'di'        => 'Disney+',
            'hp'        => 'HBO / Max',
            'hu'        => 'Hulu',
            'pr'        => 'Paramount+',
            'pv'        => 'Prime Video',
            'an'        => 'Apple TV+',
            'dt'        => 'DAZN',

            // ── Gaming ───────────────────────────────────────────────
            'st'        => 'Steam',
            'ep'        => 'Epic Games',
            'ac'        => 'Activision',
            'ea'        => 'EA / Origin',
            'ub2'       => 'Ubisoft',
            'rg'        => 'Riot Games',
            'nv'        => 'NVIDIA',
            'gog'       => 'GOG',

            // ── Dating ───────────────────────────────────────────────
            'ti'        => 'Tinder',
            'bm'        => 'Bumble',
            'hg'        => 'Hinge',
            'ba'        => 'Badoo',
            'ml'        => 'MeetMe',

            // ── Travel / Accommodation ───────────────────────────────
            'ab'        => 'Airbnb',
            'bkc'       => 'Booking.com',
            'ex'        => 'Expedia',
            'tz'        => 'Trivago',

            // ── Other Tech / AI ──────────────────────────────────────
            'ch'        => 'ChatGPT / OpenAI',
            'cl'        => 'Claude / Anthropic',
            'gi'        => 'GitHub',
            'zo'        => 'Zoom',
            'sl'        => 'Slack',
            'dr'        => 'Dropbox',
            'nk'        => 'Nike',
            'ad'        => 'Adobe',
            'yax'       => 'Yandex',
        ];

        $lower = strtolower(trim($code));
        return $map[$lower] ?? ucfirst($code);
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
            'NO_NUMBERS'  => 'No numbers available for this service/country combination. Please try a different country.',
            'NO_BALANCE'  => 'Server 1 account has insufficient balance. Please top up the Server 1 provider account.',
            'BAD_SERVICE' => 'Invalid service selected. Please reload the page and try again.',
            'BAD_COUNTRY' => 'Invalid country selected. Please reload the page and try again.',
            'BAD_KEY'     => 'Server 1 API key is invalid. Please update it in Admin → Settings.',
        ];

        $p = $this->parsePlain($body);
        if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: ($errors[$p['code']] ?? $p['code'])];

        $code = $p['code'];
        Log::warning('HeroSms orderNumber unexpected response: ' . $body);
        return ['success' => false, 'message' => $errors[$code] ?? ('Order failed: ' . $body)];
    }

    /**
     * Signal to Hero-SMS that the number has been received and is ready for SMS.
     * Must be called immediately after getNumber — without this, Hero-SMS never
     * triggers the activation and the OTP is never sent.
     */
    public function readyForSms(string $orderId): void
    {
        try {
            $r = $this->call(['action' => 'setStatus', 'id' => $orderId, 'status' => 1]);
            Log::info('HeroSms readyForSms [' . $orderId . '] response: ' . ($r['body'] ?? 'error'));
        } catch (\Exception $e) {
            Log::warning('HeroSms readyForSms failed for order ' . $orderId . ': ' . $e->getMessage());
        }
    }

    /**
     * Check SMS status for an order.
     * Returns ['success'=>true,'data'=>['status_raw'=>'...','sms'=>'...','status'=>1|3|6]]
     *
     * Hero-SMS may respond with plain text (SMS-Activate style) OR JSON.
     * Plain text:  STATUS_WAIT_CODE | STATUS_OK:123456 | STATUS_CANCEL
     * JSON:        {"status":3,"code":"123456"} or {"ok":true,"sms":"123456"}
     */
    public function checkSms(string $orderId): array
    {
        $r = $this->call(['action' => 'getStatus', 'id' => $orderId]);
        if (!$r['success']) return $r;

        $body = $r['body'];

        // ── JSON response ────────────────────────────────────────────────────
        if (str_starts_with($body, '{') || str_starts_with($body, '[')) {
            $json = json_decode($body, true);
            if (is_array($json)) {
                // Error JSON: {"title":"BAD_KEY","details":"..."}
                if (isset($json['title']) && $json['title'] !== 'OK') {
                    return ['success' => false, 'message' => $json['details'] ?? $json['title']];
                }
                // Code present in JSON
                $smsCode = $json['sms'] ?? $json['code'] ?? $json['text'] ?? null;
                $rawStatus = $json['status'] ?? $json['statusCode'] ?? null;

                if ($smsCode) {
                    return ['success' => true, 'data' => ['status' => 3, 'sms' => (string)$smsCode, 'status_raw' => $body]];
                }
                if ($rawStatus === 6 || $rawStatus === 'STATUS_CANCEL') {
                    return ['success' => true, 'data' => ['status' => 6, 'sms' => null, 'status_raw' => $body]];
                }
                // Any other JSON = still waiting
                return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
            }
        }

        // ── Plain-text response (standard SMS-Activate format) ────────────────
        // STATUS_OK:CODE123        → received (3)
        if (str_starts_with($body, 'STATUS_OK:')) {
            $code = trim(substr($body, 10));
            return ['success' => true, 'data' => ['status' => 3, 'sms' => $code, 'status_raw' => $body]];
        }

        // STATUS_CANCEL            → cancelled (6)
        if (str_starts_with($body, 'STATUS_CANCEL')) {
            return ['success' => true, 'data' => ['status' => 6, 'sms' => null, 'status_raw' => $body]];
        }

        // STATUS_WAIT_CODE / STATUS_WAIT_RESEND / STATUS_WAIT_RETRY → pending (1)
        if (str_starts_with($body, 'STATUS_WAIT') || str_starts_with($body, 'STATUS_WAIT_RETRY')) {
            return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
        }

        $p = $this->parsePlain($body);
        if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: $p['code']];

        return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
    }

    /**
     * Cancel an order (setStatus with status=8).
     * HTTP 409 means the order is already in a final state — treat as success.
     */
    public function cancelOrder(string $orderId): array
    {
        $r = $this->call(['action' => 'setStatus', 'id' => $orderId, 'status' => 8]);

        // 409 Conflict = order already used/cancelled on the provider side — safe to mark cancelled locally
        if (!$r['success']) {
            if (($r['http_status'] ?? 0) === 409) {
                Log::info('HeroSms cancelOrder: HTTP 409 for order ' . $orderId . ' (already in final state, treating as success)');
                return ['success' => true, 'data' => []];
            }
            return $r;
        }

        $body = $r['body'];

        // Standard success responses
        if (str_starts_with($body, 'ACCESS_CANCEL') || str_starts_with($body, 'STATUS_CANCEL')) {
            return ['success' => true, 'data' => []];
        }

        $p = $this->parsePlain($body);

        // "Already cancelled / completed" codes → treat as success
        if ($p['ok'] && in_array($p['code'], ['STATUS_CANCEL', 'ACCESS_CANCEL', 'STATUS_OK', 'ALREADY_CANCELLED'])) {
            return ['success' => true, 'data' => []];
        }

        if (!$p['ok']) {
            return ['success' => false, 'message' => $p['detail'] ?: 'Could not cancel the order. Please try again.'];
        }

        // Unrecognised response — log it but don't leak internals to the user
        Log::warning('HeroSms cancelOrder unexpected response for order ' . $orderId . ': ' . $body);
        return ['success' => false, 'message' => 'Could not cancel the order. Please try again.'];
    }
}
