<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Hero-SMS API (sms-activate compatible)
 * Endpoint: https://hero-sms.com/stubs/handler_api.php
 *
 * Flow for receiving an SMS code:
 *  1. getNumber(country, service)  → order_id + phone_number
 *  2. readyForSms(order_id)        → setStatus=1, activates SMS delivery
 *  3. checkSms(order_id)           → polls getStatus until STATUS_OK:code
 *  4. cancelOrder(order_id)        → setStatus=8 (if no code needed)
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
        ])->timeout(25);
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
        if (str_starts_with($body, '{') || str_starts_with($body, '[')) {
            $json = json_decode($body, true);
            if (is_array($json) && isset($json['title']) && $json['title'] !== 'OK') {
                return ['ok' => false, 'code' => $json['title'], 'detail' => $json['details'] ?? ''];
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
     * Returns list of countries as array of ['code'=>'N', 'name'=>'Country', 'iso'=>'xx'].
     * This endpoint is public — no api_key needed.
     */
    public function getCountries(): array
    {
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

        $countries = [];
        foreach ($json as $item) {
            if (!isset($item['id'])) continue;
            if (isset($item['visible']) && !$item['visible']) continue;
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
     * Returns services available for a given country (or all countries) with pricing.
     *
     * Hero-SMS getPrices response formats:
     *   With country:    {"country_id": {"service": {"count":N, "cost":X.XX}, ...}}
     *   Without country: {"service": {"country_id": {"count":N, "cost":X.XX}, ...}, ...}
     */
    public function getServices(?string $country = null): array
    {
        $hasCountry = ($country !== null && $country !== '');
        $params = ['action' => 'getPrices'];
        if ($hasCountry) {
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

        if (isset($json['title'])) {
            return ['success' => false, 'message' => $json['details'] ?? $json['title']];
        }

        // Build a flat map: service_code => ['count' => N, 'cost' => X.XX]
        $flat = [];

        if ($hasCountry) {
            // Hero-SMS may return one of two formats for country-specific requests:
            //   Format A (direct):  {"service": {"count":N, "cost":X.XX}, ...}
            //   Format B (wrapped): {"country_id": {"service": {"count":N, "cost":X.XX}, ...}}
            //
            // Detect by inspecting the first value:
            //   - If first value has a 'count' key → Format A (direct service map)
            //   - Otherwise → Format B (country wrapper, unwrap first)
            $firstValue = reset($json);
            if (!is_array($firstValue)) {
                return ['success' => false, 'message' => 'Unexpected services format.'];
            }
            $serviceMap = isset($firstValue['count']) ? $json : $firstValue;
            foreach ($serviceMap as $code => $info) {
                if (is_array($info) && isset($info['count'])) {
                    $flat[$code] = [
                        'count' => (int)($info['count'] ?? 0),
                        'cost'  => (float)($info['cost'] ?? 0),
                    ];
                }
            }
        } else {
            // Format: {"service": {"country_id": {"count":N, "cost":X.XX}, ...}, ...}
            // Flatten: sum counts across countries, take minimum cost
            foreach ($json as $code => $countryMap) {
                if (!is_array($countryMap)) continue;
                // If the value directly has count/cost it's a single-level response — handle it
                if (isset($countryMap['count'])) {
                    $flat[$code] = [
                        'count' => (int)($countryMap['count'] ?? 0),
                        'cost'  => (float)($countryMap['cost'] ?? 0),
                    ];
                    continue;
                }
                $totalCount = 0;
                $minCost    = null;
                foreach ($countryMap as $countryData) {
                    if (!is_array($countryData) || !isset($countryData['count'])) continue;
                    $totalCount += (int)($countryData['count'] ?? 0);
                    $cost = (float)($countryData['cost'] ?? 0);
                    if ($minCost === null || $cost < $minCost) $minCost = $cost;
                }
                if ($totalCount > 0) {
                    $flat[$code] = ['count' => $totalCount, 'cost' => $minCost ?? 0];
                }
            }
        }

        // Build output array with resolved names
        $services = [];
        foreach ($flat as $code => $info) {
            if ((int)($info['count'] ?? 0) <= 0) continue;
            $services[] = [
                'serviceId' => (string) $code,
                'name'      => self::resolveServiceName((string) $code),
                'count'     => (int) $info['count'],
                'cost'      => (float) $info['cost'],
            ];
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
            'wb'        => 'WhatsApp Business',
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
            'fc'        => 'Facebook Creator',
            'me'        => 'Messenger',
            'bl'        => 'Bigo Live',
            'lk'        => 'Likee',
            'ts'        => 'Threads',
            'cl2'       => 'Clubhouse',
            'imo'       => 'IMO',
            'hk'        => 'Hike',
            'kw'        => 'Kwai',
            'we'        => 'WeChat (alt)',

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
            'oz2'       => 'Ozon',
            'lm'        => 'Lazada',
            'fl'        => 'Flipkart',
            'wbx'       => 'Wildberries',
            'jd'        => 'JD.com',
            'tm2'       => 'Taobao',
            'ml'        => 'Mercadolibre',

            // ── Food / Grocery ───────────────────────────────────────
            'dd'        => 'DoorDash',
            'ue'        => 'Uber Eats',
            'gr'        => 'Grubhub',
            'if'        => 'ifood',
            'yk'        => 'Yandex Food',

            // ── Finance / Crypto / Banking ───────────────────────────
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
            'tr'        => 'Trust Wallet',
            'mm2'       => 'MetaMask',
            'ph'        => 'PhonePe',
            'gp'        => 'Google Pay',
            'ap2'       => 'Apple Pay',

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
            'cr'        => 'Crunchyroll',
            'pw'        => 'Peacock',

            // ── Gaming ───────────────────────────────────────────────
            'st'        => 'Steam',
            'ep'        => 'Epic Games',
            'ac'        => 'Activision',
            'ea'        => 'EA / Origin',
            'ub2'       => 'Ubisoft',
            'rg'        => 'Riot Games',
            'nv'        => 'NVIDIA',
            'gog'       => 'GOG',
            'ps'        => 'PlayStation',
            'xb'        => 'Xbox',
            'nx'        => 'Nintendo',
            'ro'        => 'Roblox',
            'mo'        => 'Mobile Legends',
            'px'        => 'Playrix',

            // ── Dating ───────────────────────────────────────────────
            'ti'        => 'Tinder',
            'bm'        => 'Bumble',
            'hg'        => 'Hinge',
            'ba'        => 'Badoo',
            'ml2'       => 'MeetMe',

            // ── Travel / Accommodation ───────────────────────────────
            'ab'        => 'Airbnb',
            'bkc'       => 'Booking.com',
            'ex'        => 'Expedia',
            'tz'        => 'Trivago',

            // ── Other Tech / AI / Productivity ──────────────────────
            'ch'        => 'ChatGPT / OpenAI',
            'cl'        => 'Claude / Anthropic',
            'gi'        => 'GitHub',
            'zo'        => 'Zoom',
            'sl'        => 'Slack',
            'dr'        => 'Dropbox',
            'nk'        => 'Nike',
            'ad'        => 'Adobe',
            'yax'       => 'Yandex',
            'ya'        => 'Yandex',
            'nw'        => 'Yahoo',
            'ao'        => 'AOL',
            'cf'        => 'Cloudflare',
            'tw2'       => 'Twilio',
            'nu'        => 'Nubank',
            'gg'        => 'GrabFood',
            'gx'        => 'Gojek',
            'ri'        => 'Rappi',
            'po'        => 'Postmates',
            'su'        => 'Swiggy',
            'zo2'       => 'Zomato',
            'ola'       => 'Ola',
            'zx'        => 'Lazada (alt)',
        ];

        $lower = strtolower(trim($code));
        return $map[$lower] ?? strtoupper($code);
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

        $errors = [
            'NO_NUMBERS'  => 'No numbers available for this service/country. Please try a different country.',
            'NO_BALANCE'  => 'Insufficient API account balance. Please top up the HeroSMS account.',
            'BAD_SERVICE' => 'Invalid service code. Please refresh the page and try again.',
            'BAD_COUNTRY' => 'Invalid country selected. Please refresh the page and try again.',
            'BAD_KEY'     => 'HeroSMS API key is invalid. Please update it in Admin → Settings.',
        ];

        $p    = $this->parsePlain($body);
        $code = $p['ok'] ? $p['code'] : ($p['code'] ?? 'UNKNOWN');
        if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: ($errors[$code] ?? $code)];

        Log::warning('HeroSms orderNumber unexpected: ' . $body);
        return ['success' => false, 'message' => $errors[$code] ?? 'Order failed: ' . $body];
    }

    /**
     * Request a new / re-send SMS code for an existing active order (setStatus=3).
     * Use when the user applied the number to the wrong service or the first
     * OTP expired without being used.
     * Returns ['success'=>true] or ['success'=>false, 'message'=>'...'].
     */
    public function requestResend(string $orderId): array
    {
        $r = $this->call(['action' => 'setStatus', 'id' => $orderId, 'status' => 3]);
        if (!$r['success']) return $r;

        $body = $r['body'];

        // Expected success responses
        if (str_starts_with($body, 'ACCESS_RETRY_GET') || str_starts_with($body, 'ACCESS_ACTIVATION')) {
            return ['success' => true];
        }

        // Some providers just echo OK or the same activation token
        if (in_array($body, ['OK', 'ACCESS_OK'])) {
            return ['success' => true];
        }

        $p = $this->parsePlain($body);

        if ($p['ok'] && in_array($p['code'], ['ACCESS_RETRY_GET', 'ACCESS_ACTIVATION', 'OK', 'ACCESS_OK'])) {
            return ['success' => true];
        }

        if (!$p['ok']) {
            return ['success' => false, 'message' => $p['detail'] ?: $p['code']];
        }

        // Unexpected but non-fatal — treat as success so the UI can still poll
        Log::warning('HeroSms requestResend unexpected response for ' . $orderId . ': ' . $body);
        return ['success' => true];
    }

    /**
     * Signal readiness to receive SMS (setStatus=1).
     * MUST be called immediately after orderNumber() — Hero-SMS will not send the
     * OTP until this activation signal is received.
     */
    public function readyForSms(string $orderId): void
    {
        try {
            $r = $this->call(['action' => 'setStatus', 'id' => $orderId, 'status' => 1]);
            Log::info('HeroSms readyForSms [' . $orderId . '] → ' . ($r['body'] ?? 'error'));
        } catch (\Exception $e) {
            Log::warning('HeroSms readyForSms failed for ' . $orderId . ': ' . $e->getMessage());
        }
    }

    /**
     * Check SMS status for an order.
     *
     * Possible plain-text responses:
     *   STATUS_WAIT_CODE          → waiting (1)
     *   STATUS_OK:123456          → code received (3)
     *   STATUS_CANCEL             → cancelled (6)
     *
     * Possible JSON responses:
     *   {"status":3,"code":"123456"}
     *   {"title":"BAD_KEY","details":"..."}
     */
    public function checkSms(string $orderId): array
    {
        $r = $this->call(['action' => 'getStatus', 'id' => $orderId]);
        if (!$r['success']) return $r;

        $body = $r['body'];

        // ── JSON response ──────────────────────────────────────────────────────
        if (str_starts_with($body, '{') || str_starts_with($body, '[')) {
            $json = json_decode($body, true);
            if (is_array($json)) {
                if (isset($json['title']) && $json['title'] !== 'OK') {
                    return ['success' => false, 'message' => $json['details'] ?? $json['title']];
                }
                $smsCode   = $json['sms'] ?? $json['code'] ?? $json['text'] ?? null;
                $rawStatus = $json['status'] ?? $json['statusCode'] ?? null;

                if ($smsCode) {
                    return ['success' => true, 'data' => ['status' => 3, 'sms' => (string)$smsCode, 'status_raw' => $body]];
                }
                if ($rawStatus === 6 || $rawStatus === 'STATUS_CANCEL') {
                    return ['success' => true, 'data' => ['status' => 6, 'sms' => null, 'status_raw' => $body]];
                }
                return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
            }
        }

        // ── Plain-text response ────────────────────────────────────────────────
        if (str_starts_with($body, 'STATUS_OK:')) {
            $code = trim(substr($body, 10));
            return ['success' => true, 'data' => ['status' => 3, 'sms' => $code, 'status_raw' => $body]];
        }

        if (str_starts_with($body, 'STATUS_CANCEL')) {
            return ['success' => true, 'data' => ['status' => 6, 'sms' => null, 'status_raw' => $body]];
        }

        // STATUS_WAIT_CODE / STATUS_WAIT_RESEND / STATUS_WAIT_RETRY
        if (str_starts_with($body, 'STATUS_WAIT') || str_starts_with($body, 'STATUS_WAIT_RETRY')) {
            return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
        }

        $p = $this->parsePlain($body);
        if (!$p['ok']) return ['success' => false, 'message' => $p['detail'] ?: $p['code']];

        return ['success' => true, 'data' => ['status' => 1, 'sms' => null, 'status_raw' => $body]];
    }

    /**
     * Cancel an order (setStatus=8).
     * HTTP 409 = already in a final state — treat as success.
     */
    public function cancelOrder(string $orderId): array
    {
        $r = $this->call(['action' => 'setStatus', 'id' => $orderId, 'status' => 8]);

        if (!$r['success']) {
            if (($r['http_status'] ?? 0) === 409) {
                Log::info('HeroSms cancelOrder: HTTP 409 for ' . $orderId . ' (already final)');
                return ['success' => true, 'data' => []];
            }
            return $r;
        }

        $body = $r['body'];

        if (str_starts_with($body, 'ACCESS_CANCEL') || str_starts_with($body, 'STATUS_CANCEL')) {
            return ['success' => true, 'data' => []];
        }

        $p = $this->parsePlain($body);

        if ($p['ok'] && in_array($p['code'], ['STATUS_CANCEL', 'ACCESS_CANCEL', 'STATUS_OK', 'ALREADY_CANCELLED'])) {
            return ['success' => true, 'data' => []];
        }

        if (!$p['ok']) {
            return ['success' => false, 'message' => $p['detail'] ?: 'Could not cancel the order. Please try again.'];
        }

        Log::warning('HeroSms cancelOrder unexpected for ' . $orderId . ': ' . $body);
        return ['success' => false, 'message' => 'Could not cancel the order. Please try again.'];
    }
}
