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
        // ── Messaging ────────────────────────────────────────────────────────
        'tg'  => 'Telegram',
        'wa'  => 'WhatsApp',
        'wv'  => 'WhatsApp Business',
        'wb'  => 'WhatsApp Business',
        'wp'  => 'WhatsApp',
        'vi'  => 'Viber',
        'we'  => 'WeChat',
        'si'  => 'Signal',
        'ka'  => 'KakaoTalk',
        'li'  => 'Line',
        'za'  => 'Zalo',
        'pt'  => 'Potato Chat',
        'im'  => 'ICQ / IM',
        'sk'  => 'Skype',
        'vc'  => 'Voxer',
        'kk'  => 'KakaoTalk',
        'bi'  => 'Bigo Live',
        'cl'  => 'Clubhouse',

        // ── Social Media ─────────────────────────────────────────────────────
        'fb'  => 'Facebook',
        'ig'  => 'Instagram',
        'tw'  => 'Twitter / X',
        'tk'  => 'TikTok',
        'sn'  => 'Snapchat',
        'ln'  => 'LinkedIn',
        'vk'  => 'VKontakte',
        'ok'  => 'Odnoklassniki',
        'ma'  => 'Mail.ru',
        'yo'  => 'YouTube',
        'pi'  => 'Pinterest',
        'rd'  => 'Reddit',
        'tm'  => 'Tumblr',
        'tt'  => 'Twitch',
        'dr'  => 'Discord',
        'qu'  => 'Quora',
        'nk'  => 'Nextdoor',
        'pr'  => 'Parler',
        'lo'  => 'Lolz Guru',
        'lk'  => 'Likee',
        'kw'  => 'Kwai',
        'hk'  => 'Hike',
        'ms'  => 'Mastodon',
        'xi'  => 'Xing',
        'bw'  => 'BeReal',
        'wm'  => 'Weibo',
        'dt'  => 'Douyin',
        'md'  => 'MeWe',

        // ── Google / Microsoft / Apple ────────────────────────────────────────
        'go'  => 'Google',
        'mm'  => 'Microsoft',
        'ap'  => 'Apple ID',
        'gg'  => 'Gmail',
        'gp'  => 'ChatGPT / OpenAI',
        'az'  => 'Azure',
        'of'  => 'Microsoft Office',

        // ── E-Commerce & Marketplaces ─────────────────────────────────────────
        'am'  => 'Amazon',
        'ae'  => 'AliExpress',
        'ab'  => 'Alibaba',
        'eb'  => 'eBay',
        'ew'  => 'eBay',
        'sh'  => 'Shopee',
        'la'  => 'Lazada',
        'oz'  => 'OLX',
        'av'  => 'Avito',
        'et'  => 'Etsy',
        'wb2' => 'Wildberries',
        'mx'  => 'Mercado Livre',
        'ft'  => 'Fiverr',
        'up'  => 'Upwork',
        'pp'  => 'PayPal',
        'fl'  => 'Freelancer',
        'ro'  => 'Rozetka',

        // ── Crypto & Finance ─────────────────────────────────────────────────
        'bn'  => 'Binance',
        'cb'  => 'Coinbase',
        'ku'  => 'KuCoin',
        'hu'  => 'HTX (Huobi)',
        'by'  => 'Bybit',
        'mc'  => 'MEXC',
        'cr'  => 'Crypto.com',
        'gt'  => 'Gate.io',
        'ok2' => 'OKX',
        'bx'  => 'BitGet',
        'qi'  => 'Qiwi',
        'sb'  => 'Sberbank',
        'wb3' => 'WebMoney',
        'pm'  => 'Perfect Money',
        'pa'  => 'Patreon',
        'sy'  => 'Skrill',
        'ny'  => 'Neteller',
        'ct'  => 'CoinTracker',

        // ── Gaming ────────────────────────────────────────────────────────────
        'st'  => 'Steam',
        'ep'  => 'Epic Games',
        'ri'  => 'Riot Games',
        'nt'  => 'Nintendo',
        'xb'  => 'Xbox',
        'ps'  => 'PlayStation',
        'rk'  => 'Rockstar Games',
        'ba'  => 'Battle.net',
        'mo'  => 'Mobile Legends',
        'pk'  => 'PUBG Mobile',
        'fr'  => 'Free Fire',
        'gc'  => 'Genshin Impact',

        // ── Streaming & Entertainment ─────────────────────────────────────────
        'nf'  => 'Netflix',
        'sp'  => 'Spotify',
        'di'  => 'Disney+',
        'ht'  => 'HBO Max',
        'hlu' => 'Hulu',
        'dc'  => 'Deezer',
        'ap2' => 'Apple Music',
        'pr2' => 'Prime Video',
        'ti'  => 'Tidal',

        // ── Food & Delivery ───────────────────────────────────────────────────
        'ub'  => 'Uber',
        'ue'  => 'Uber Eats',
        'dl'  => 'DoorDash',
        'if'  => 'inDriver / BlaBlaCar',
        'gr'  => 'Grab',
        'gf'  => 'Glovo',
        'yf'  => 'Yandex Food',
        'bl'  => 'Bolt',
        'lt'  => 'Lyft',

        // ── Travel & Hospitality ─────────────────────────────────────────────
        'at'  => 'Airbnb',
        'bo'  => 'Booking.com',
        'bk'  => 'Booking.com',
        'ag'  => 'Agoda',
        'ek'  => 'Expedia',
        'tr'  => 'TripAdvisor',

        // ── Dating ────────────────────────────────────────────────────────────
        'tf'  => 'Tinder',
        'bm'  => 'Bumble',
        'hg'  => 'Hinge',
        'oi'  => 'OkCupid',
        'gd'  => 'Grindr',
        'ba2' => 'Badoo',
        'pe'  => 'Pof',
        'mu'  => 'MuMu',

        // ── Other / Misc ─────────────────────────────────────────────────────
        'ya'  => 'Yandex',
        'ym'  => 'Yandex Market',
        'goo' => 'Google Voice',
        'tw2' => 'Twilio',
        'ot'  => 'Other',
        'any' => 'Any Service',
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
     * Fetches ALL services + prices from the API.
     * If $countryCode is provided, returns only that country's services.
     * If empty, fetches every country and returns everything.
     * [{serviceId, name, count, cost_usd, cost_ngn, country_code, country_name}]
     */
    public function getServices(string $countryCode = ''): array
    {
        try {
            $params = ['action' => 'getPrices'];
            if ($countryCode !== '') {
                $params['country'] = $countryCode;
            }

            $resp = $this->request($params);
            $data = json_decode($resp, true);

            if (!is_array($data) || empty($data)) {
                return ['success' => false, 'message' => 'No services available. Please try again.'];
            }

            // Build a lookup: numeric code (string) → country name
            $countryLookup = [];
            foreach (self::COUNTRIES as $c) {
                $countryLookup[(string)$c['code']] = $c['name'];
            }

            // If a specific country was requested, narrow data to that key
            if ($countryCode !== '') {
                $countryInt  = (int) $countryCode;
                $narrowed    = $data[$countryInt] ?? $data[$countryCode] ?? null;
                if (!$narrowed && count($data) === 1) {
                    $narrowed = reset($data);
                }
                if (!$narrowed || !is_array($narrowed)) {
                    return ['success' => false, 'message' => 'No services available for the selected country.'];
                }
                // Re-wrap to use the unified loop below
                $data = [$countryCode => $narrowed];
            }

            $services = [];
            foreach ($data as $codeKey => $countryServices) {
                if (!is_array($countryServices)) continue;

                $codeStr     = (string)$codeKey;
                $countryName = $countryLookup[$codeStr] ?? ('Country ' . $codeStr);

                foreach ($countryServices as $serviceCode => $priceInfo) {
                    if (!is_array($priceInfo)) continue;

                    $count    = (int)($priceInfo['count'] ?? 0);
                    $priceUsd = (float)($priceInfo['cost'] ?? 0);
                    $name     = self::SERVICE_NAMES[$serviceCode]
                                ?? ucwords(str_replace(['_', '-'], ' ', (string)$serviceCode));

                    $services[] = [
                        'serviceId'    => (string)$serviceCode,
                        'name'         => $name,
                        'count'        => $count,
                        'cost_usd'     => $priceUsd,
                        'cost_ngn'     => $this->usdToNgn($priceUsd),
                        'country_code' => $codeStr,
                        'country_name' => $countryName,
                    ];
                }
            }

            if (empty($services)) {
                return ['success' => false, 'message' => 'No services found.'];
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
