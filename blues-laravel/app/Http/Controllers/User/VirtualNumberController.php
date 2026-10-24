<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Wallet, WalletTransaction, Notification, Setting, VirtualNumberOrder};
use App\Services\{HeroSmsService, GrizzlySmsService, PvaDealsSmsService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log};

class VirtualNumberController extends Controller
{
    private array $serviceNames = [
        // ── Messaging & Social ──────────────────────────────────────────
        'tg'   => 'Telegram',
        'wa'   => 'WhatsApp',
        'wb'   => 'WhatsApp Business',   // GrizzlySMS code for WA Business
        'wba'  => 'WhatsApp Business',
        'fb'   => 'Facebook',
        'ig'   => 'Instagram',
        'tw'   => 'Twitter / X',
        'tk'   => 'TikTok',
        'ds'   => 'Discord',
        'vi'   => 'Viber',
        'si'   => 'Signal',
        'sg'   => 'Signal',              // alternate code
        'vk'   => 'VKontakte',
        'ok'   => 'Odnoklassniki',
        'mm'   => 'Mail.ru',
        'ln'   => 'LINE',
        'kk'   => 'KakaoTalk',
        'wc'   => 'WeChat',
        'ws'   => 'WeChat',              // alternate code
        'sk'   => 'Skype',
        'sc'   => 'Snapchat',
        'br'   => 'BeReal',
        'th'   => 'Threads',
        'bs'   => 'Bluesky',
        'tm'   => 'Tumblr',
        'tt'   => 'Twitch',
        'rd'   => 'Reddit',
        'pt'   => 'Pinterest',
        'yt'   => 'YouTube',
        'sp'   => 'Spotify',
        'zl'   => 'Zalo',
        'im'   => 'ICQ',
        'ic'   => 'ICQ New',
        'bc'   => 'BChat',
        'mt'   => 'MeWe',
        'tx'   => 'TextNow',
        'ch'   => 'Clubhouse',
        'ms'   => 'Mastodon',
        'li'   => 'LinkedIn',
        'lnk'  => 'LinkedIn',
        'pi'   => 'Pinterest',
        'tum'  => 'Tumblr',
        'twi'  => 'Twitch',
        'me'   => 'MeWe',
        'mx2'  => 'MX Takatak',
        'koo'  => 'Koo',
        'sha'  => 'ShareChat',
        'ro'   => 'Roposo',

        // ── Tech, Productivity & AI ──────────────────────────────────────
        'go'   => 'Google',
        'ap'   => 'Apple',
        'ma'   => 'Microsoft',
        'ya'   => 'Yahoo',
        'gi'   => 'GitHub',
        'dr'   => 'Dropbox',
        'zm'   => 'Zoom',
        'sl'   => 'Slack',
        'do'   => 'DigitalOcean',
        'gd'   => 'GoDaddy',
        'wp'   => 'WordPress',
        'sf'   => 'Salesforce',
        'hb'   => 'HubSpot',
        'at'   => 'Atlassian',
        'op'   => 'OpenAI / ChatGPT',
        'ai'   => 'ChatGPT',
        'no'   => 'Notion',
        'fi'   => 'Figma',
        'cn'   => 'Canva',
        'cl'   => 'Claude / Anthropic',
        'ge'   => 'Gemini / Google AI',
        'tw3'  => 'Twilio',
        'gv'   => 'Google Voice',
        'pm'   => 'ProtonMail',
        'tu'   => 'Tutanota',
        'ze'   => 'Zendesk',
        'fr'   => 'Freshdesk',
        'ab'   => 'Adobe',

        // ── Shopping & Marketplaces ──────────────────────────────────────
        'am'   => 'Amazon',
        'eb'   => 'eBay',
        'et'   => 'Etsy',
        'sh'   => 'Shopify',
        'al'   => 'AliExpress',
        'oz'   => 'Ozon',
        'wbr'  => 'Wildberries',         // Wildberries (moved from wb to avoid WA Business conflict)
        'av'   => 'Avito',
        'ol'   => 'OLX',
        'lz'   => 'Lazada',
        'wm'   => 'Walmart',
        'nk'   => 'Nike',
        'za'   => 'Zara',
        'ji'   => 'Jumia',
        'kf'   => 'Konga',
        'te'   => 'Temu',
        'sh2'  => 'SHEIN',
        'ali'  => 'Alibaba',
        'fl'   => 'Flipkart',
        'mnt'  => 'Myntra',
        'mer'  => 'Mercado Libre',
        'tok'  => 'Tokopedia',
        'sho'  => 'Shopee',

        // ── Finance & Crypto ─────────────────────────────────────────────
        'pp'   => 'PayPal',
        'bn'   => 'Binance',
        'by'   => 'Bybit',
        'cb'   => 'Coinbase',
        'kc'   => 'KuCoin',
        'ok2'  => 'OKX',
        'okx'  => 'OKX',
        'ft'   => 'FTX',
        'wx'   => 'Wise',
        'rv'   => 'Revolut',
        'ck'   => 'Cash App',
        'vn'   => 'Venmo',
        'zl2'  => 'Zelle',
        'sy'   => 'Stripe',
        'wr'   => 'Wirex',
        'kr'   => 'Kraken',
        'gm'   => 'Gemini Exchange',
        'nx'   => 'Nexo',
        'cy'   => 'Crypto.com',
        'gf'   => 'Gate.io',
        'hu2'  => 'Huobi / HTX',
        'htx'  => 'Huobi / HTX',
        'mx'   => 'MEXC',
        'bit'  => 'Bitget',
        'bfx'  => 'Bitfinex',
        'tr2'  => 'Trust Wallet',
        'mm2'  => 'MetaMask',
        'ps2'  => 'Paystack',
        'fw'   => 'Flutterwave',
        'bv'   => 'Bvnk',
        'cp'   => 'CoinsPaid',
        'ph'   => 'Payhip',
        'nv'   => 'Nuvei',

        // ── Ride & Food Delivery ─────────────────────────────────────────
        'ub'   => 'Uber',
        'lf'   => 'Lyft',
        'gp'   => 'Grab',
        'bl'   => 'Bolt',
        'dd'   => 'DoorDash',
        'ue'   => 'Uber Eats',
        'jt'   => 'Just Eat',
        'dk'   => 'Deliveroo',
        'rf'   => 'Rappi',
        'in'   => 'InDrive',
        'gl'   => 'Glovo',
        'sw'   => 'Swiggy',
        'zp'   => 'Zomato',
        'cv'   => 'Careem',
        'ol2'  => 'Ola',

        // ── Travel & Accommodation ───────────────────────────────────────
        'af'   => 'Airbnb',
        'bk'   => 'Booking.com',
        'ex'   => 'Expedia',
        'ag'   => 'Agoda',
        'tr'   => 'TripAdvisor',
        'ct'   => 'Ctrip / Trip.com',
        'mk'   => 'MakeMyTrip',

        // ── Streaming & Entertainment ────────────────────────────────────
        'nf'   => 'Netflix',
        'di'   => 'Disney+',
        'hx'   => 'HBO Max',
        'hu'   => 'Hulu',
        'pr'   => 'Paramount+',
        'ae'   => 'Apple TV+',
        'az'   => 'Amazon Prime',
        'dz'   => 'Deezer',
        'td'   => 'TIDAL',
        'pw'   => 'Peacock',
        'fu'   => 'Fubo',
        'cr'   => 'Crunchyroll',
        'yt2'  => 'YouTube Premium',
        'sou'  => 'SoundCloud',
        'dan'  => 'Deezer',

        // ── Dating ───────────────────────────────────────────────────────
        'tn'   => 'Tinder',
        'bm'   => 'Bumble',
        'bt'   => 'Badoo',
        'gr'   => 'Grindr',
        'hg'   => 'Hinge',
        'mo'   => 'MocoSpace',
        'zk'   => 'Zoosk',
        'mch'  => 'Match',
        'ok3'  => 'OkCupid',
        'pof'  => 'Plenty of Fish',

        // ── Gaming ───────────────────────────────────────────────────────
        'st'   => 'Steam',
        'ep'   => 'Epic Games',
        'rk'   => 'Rockstar Games',
        'ac'   => 'Activision',
        'ea'   => 'EA Games',
        'nx2'  => 'Nintendo',
        'ps'   => 'PlayStation',
        'xb'   => 'Xbox',
        'rz'   => 'Razer',
        'va'   => 'Valorant',
        'ml'   => 'Mobile Legends',
        'ffc'  => 'Free Fire',
        'coc'  => 'Clash of Clans',
        'ry'   => 'Riot Games',
        'bg'   => 'BGMI / PUBG',
        'pu'   => 'PUBG',
        'ro2'  => 'Roblox',
        'min'  => 'Minecraft',
        'fn'   => 'Fortnite',
        'lol'  => 'League of Legends',

        // ── Security & VPN ───────────────────────────────────────────────
        'ks'   => 'Kaspersky',
        'nt'   => 'Norton',
        'bt2'  => 'Bitdefender',
        'mc'   => 'McAfee',
        'nr'   => 'NordVPN',
        'ev'   => 'ExpressVPN',
        'sn'   => 'Surfshark',

        // ── Other / Catch-all ────────────────────────────────────────────
        'li2'  => 'LinkedIn',
        'ld'   => 'Letgo',
        'ta'   => 'Tango',
        'ft2'  => 'Footprint',
        'tw2'  => 'Twilio',
        'ot'   => 'Other / Any',
        'any'  => 'Any Service',
        'other'=> 'Other',
        'more' => 'More Services',
    ];

    /**
     * Return the correct SMS service instance for a given provider identifier.
     */
    private function getProviderService(string $provider): HeroSmsService|GrizzlySmsService|PvaDealsSmsService
    {
        if ($provider === 'server1') {
            return new GrizzlySmsService();
        }
        if ($provider === 'server3') {
            return new PvaDealsSmsService();
        }
        return new HeroSmsService();
    }

    public function index()
    {
        $user    = Auth::user();
        $wallet  = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $enabled = Setting::get('herosms_enabled', '0') === '1'
                || Setting::get('grizzly_enabled', '0') === '1'
                || Setting::get('pvadeals_enabled', '0') === '1';

        $orders = VirtualNumberOrder::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('dashboard.virtual-numbers', compact('wallet', 'enabled', 'orders'));
    }

    public function getCountries(Request $request)
    {
        if ((int) $request->query('server', 0) === 3) {
            if (Setting::get('pvadeals_enabled', '0') !== '1') {
                return response()->json(['countries' => []], 503);
            }

            $sms = new PvaDealsSmsService();
            if (!$sms->isConfigured()) {
                return response()->json(['countries' => []], 503);
            }

            try {
                $countries = [];
                foreach ($sms->getServices() as $service) {
                    if (!is_array($service) || (int) ($service['STRstock'] ?? 0) <= 0
                        || (float) ($service['STRprice'] ?? 0) <= 0) {
                        continue;
                    }

                    $countryCode = PvaDealsSmsService::countryCode($service['country'] ?? null);
                    if ($countryCode === '') {
                        continue;
                    }

                    $countries[$countryCode] = [
                        'id' => $countryCode,
                        'name' => PvaDealsSmsService::countryName($countryCode, $service['country'] ?? null),
                    ];
                }

                $countries = array_values($countries);
                usort($countries, fn ($a, $b) => strcmp($a['name'], $b['name']));

                return response()->json(['countries' => $countries]);
            } catch (\Throwable $e) {
                Log::warning('PVADeals country catalog unavailable.', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Could not load Server 3 countries.'], 503);
            }
        }

        // Prefer server1; fall back to server2
        $sms = new GrizzlySmsService();
        if (!$sms->isConfigured() || Setting::get('grizzly_enabled', '0') !== '1') {
            $sms = new HeroSmsService();
        }

        if (!$sms->isConfigured()) {
            return response()->json(['countries' => []]);
        }

        $raw       = $sms->getCountries();
        $countries = [];
        foreach ($raw as $item) {
            $id   = $item['id']   ?? $item['iso'] ?? null;
            $name = $item['name'] ?? $item['eng']  ?? null;
            if ($id !== null && $name) {
                $countries[] = ['id' => (int) $id, 'name' => $name];
            }
        }
        usort($countries, fn($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json(['countries' => $countries]);
    }

    public function getServices(Request $request)
    {
        $serverPref = (int) $request->input('server', 0); // 1 = force server1, 2 = force server2, 0 = auto
        $request->validate([
            'country' => $serverPref === 3 ? 'nullable|string|max:100' : 'nullable|integer',
            'sort' => 'nullable|in:az,za,count,price_asc,price_desc',
        ]);

        if ($serverPref === 3) {
            return $this->getPvaDealsServices($request);
        }

        if ($serverPref === 2) {
            $sms = new HeroSmsService();
            if (!$sms->isConfigured() || Setting::get('herosms_enabled', '0') !== '1') {
                return response()->json(['error' => 'Service not available.'], 503);
            }
        } elseif ($serverPref === 1) {
            $sms = new GrizzlySmsService();
            if (!$sms->isConfigured() || Setting::get('grizzly_enabled', '0') !== '1') {
                return response()->json(['error' => 'Service not available.'], 503);
            }
        } else {
            // Auto: prefer server1, fall back to server2
            $sms = new GrizzlySmsService();
            if (!$sms->isConfigured() || Setting::get('grizzly_enabled', '0') !== '1') {
                $sms = new HeroSmsService();
            }
        }

        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Service not available.'], 503);
        }

        $country = (int) ($request->country ?? 0);

        // Fetch counts and prices in parallel for Server 1 (GrizzlySMS) or fall back to sequential for Server 2
        if ($sms instanceof GrizzlySmsService) {
            $parallel   = $sms->getServicesAndPrices($country);
            $rawCounts  = $parallel['counts'];
            $rawPrices  = $parallel['prices'];
        } else {
            $rawCounts = $sms->getServicesForCountry($country);
            $rawPrices = $sms->getPricesForCountry($country);
        }

        $services = [];

        foreach ($rawCounts as $code => $count) {
            if ((int) $count <= 0) continue;

            $apiUsdPrice = $rawPrices[$code] ?? $rawPrices['*'] ?? null;
            $priceNgn    = null;

            if ($apiUsdPrice !== null) {
                $priceNgn = HeroSmsService::calculateNgnPrice((float) $apiUsdPrice);
            } else {
                $priceNgn = (float) Setting::get('herosms_number_price', '200');
            }

            $services[] = [
                'code'     => $code,
                'name'     => $this->serviceNames[$code] ?? ucwords(str_replace(['-','_'], ' ', $code)),
                'count'    => (int) $count,
                'price'    => $priceNgn,
                'usd_cost' => $apiUsdPrice,
            ];
        }

        $sort = $request->input('sort', 'az');
        if ($sort === 'az') {
            usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));
        } elseif ($sort === 'za') {
            usort($services, fn($a, $b) => strcmp($b['name'], $a['name']));
        } elseif ($sort === 'count') {
            usort($services, fn($a, $b) => $b['count'] <=> $a['count']);
        } elseif ($sort === 'price_asc') {
            usort($services, fn($a, $b) => ($a['price'] ?? 999999) <=> ($b['price'] ?? 999999));
        } elseif ($sort === 'price_desc') {
            usort($services, fn($a, $b) => ($b['price'] ?? 0) <=> ($a['price'] ?? 0));
        }

        // Supplemental WhatsApp country
        $suppCountryId   = (int) Setting::get('herosms_supplemental_wa_country_id', '0');
        $suppCountryName = trim(Setting::get('herosms_supplemental_wa_country_name', ''));

        if ($suppCountryId > 0 && $suppCountryName !== '' && $suppCountryId !== $country) {
            try {
                $suppCounts = $sms->getServicesForCountry($suppCountryId);
                $suppPrices = $sms->getPricesForCountry($suppCountryId);
                $waCount    = isset($suppCounts['wa']) ? (int) $suppCounts['wa'] : 0;

                if ($waCount > 0) {
                    $apiUsd    = $suppPrices['wa'] ?? null;
                    $priceNgn  = $apiUsd !== null
                        ? HeroSmsService::calculateNgnPrice((float) $apiUsd)
                        : (float) Setting::get('herosms_number_price', '200');

                    $services[] = [
                        'code'                      => 'wa',
                        'name'                      => 'WhatsApp',
                        'count'                     => $waCount,
                        'price'                     => $priceNgn,
                        'usd_cost'                  => $apiUsd,
                        'supplemental'              => true,
                        'supplemental_country_id'   => $suppCountryId,
                        'supplemental_country_name' => $suppCountryName,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('getServices: supplemental WA fetch failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['services' => $services]);
    }

    private function getPvaDealsServices(Request $request)
    {
        if (Setting::get('pvadeals_enabled', '0') !== '1') {
            return response()->json(['error' => 'Server 3 is not available.'], 503);
        }

        $sms = new PvaDealsSmsService();
        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Server 3 is not configured.'], 503);
        }

        try {
            $selectedCountry = strtoupper(trim((string) $request->query('country', '0')));
            $services = [];

            foreach ($sms->getServices() as $item) {
                if (!is_array($item) || empty($item['_id']) || empty($item['name'])) {
                    continue;
                }

                $stock = (int) ($item['STRstock'] ?? 0);
                $usdCost = $item['STRprice'] ?? null;
                if ($stock <= 0 || !is_numeric($usdCost) || (float) $usdCost <= 0) {
                    continue;
                }

                $providerCountry = (string) ($item['country'] ?? '');
                $countryCode = PvaDealsSmsService::countryCode($providerCountry);
                if ($countryCode === '') {
                    continue;
                }
                if ($selectedCountry !== '' && $selectedCountry !== '0' && $selectedCountry !== $countryCode) {
                    continue;
                }

                $services[] = [
                    'code' => (string) $item['_id'],
                    'name' => mb_substr((string) $item['name'], 0, 100),
                    'count' => $stock,
                    'price' => HeroSmsService::calculateNgnPrice((float) $usdCost),
                    'usd_cost' => (float) $usdCost,
                    'country_code' => $countryCode,
                    'country_name' => PvaDealsSmsService::countryName($countryCode, $providerCountry),
                ];
            }

            $sort = $request->input('sort', 'az');
            if ($sort === 'az') {
                usort($services, fn ($a, $b) => strcmp($a['name'], $b['name']));
            } elseif ($sort === 'za') {
                usort($services, fn ($a, $b) => strcmp($b['name'], $a['name']));
            } elseif ($sort === 'count') {
                usort($services, fn ($a, $b) => $b['count'] <=> $a['count']);
            } elseif ($sort === 'price_asc') {
                usort($services, fn ($a, $b) => $a['price'] <=> $b['price']);
            } elseif ($sort === 'price_desc') {
                usort($services, fn ($a, $b) => $b['price'] <=> $a['price']);
            }

            return response()->json(['services' => $services]);
        } catch (\Throwable $e) {
            Log::warning('PVADeals service catalog unavailable.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Could not load Server 3 services.'], 503);
        }
    }

    public function order(Request $request)
    {
        if ((int) $request->input('server_pref', 0) === 3) {
            $request->validate([
                'service' => 'required|string|max:64',
                'server_pref' => 'required|integer|in:3',
                'country_name' => 'nullable|string|max:100',
            ]);

            return $this->orderPvaDeals($request);
        }

        $request->validate([
            'service'    => 'required|string|max:10',
            'country'    => 'required|integer|min:1',
            'server_pref' => 'nullable|integer|in:1,2',
        ]);

        $server1Enabled = Setting::get('grizzly_enabled', '0') === '1';
        $server2Enabled = Setting::get('herosms_enabled', '0') === '1';

        if (!$server1Enabled && !$server2Enabled) {
            return response()->json(['error' => 'Virtual numbers are not available right now.'], 503);
        }

        $serverPref = (int) $request->input('server_pref', 0); // 1 = prefer s1, 2 = prefer s2, 0 = auto
        $country    = (int) $request->country;

        // Determine price from whichever provider is active
        $priceSms = $this->getPriceFromActiveProvider($request->service, $country);
        $price    = $priceSms ?? (float) Setting::get('herosms_number_price', '200');

        $user   = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ($wallet->balance < $price) {
            return response()->json([
                'error' => 'Insufficient wallet balance. You need ₦' . number_format($price, 2) . '. Please top up first.',
            ], 422);
        }

        $maxActive   = (int) Setting::get('herosms_max_active', '3');
        $activeCount = VirtualNumberOrder::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'waiting', 'received'])
            ->count();

        if ($activeCount >= $maxActive) {
            return response()->json([
                'error' => "You have reached the maximum of {$maxActive} active rentals. Complete or cancel existing ones first.",
            ], 422);
        }

        $result   = null;
        $provider = null;

        // If user explicitly chose Server 2, try it first (then fallback to Server 1)
        if ($serverPref === 2) {
            if ($server2Enabled) {
                $s2 = new HeroSmsService();
                if ($s2->isConfigured()) {
                    $result = $s2->getNumber($request->service, $country);
                    if ($result['success']) {
                        $provider = 'server2';
                    }
                }
            }
            if (!$provider && $server1Enabled) {
                $s1 = new GrizzlySmsService();
                if ($s1->isConfigured()) {
                    $result = $s1->getNumber($request->service, $country);
                    if ($result['success']) {
                        $provider = 'server1';
                        $s1->setStatusReady($result['activation_id']);
                    }
                }
            }
        } else {
            // Default / Server 1 preferred: try Server 1 first, then Server 2
            if ($server1Enabled) {
                $s1 = new GrizzlySmsService();
                if ($s1->isConfigured()) {
                    $result = $s1->getNumber($request->service, $country);
                    if ($result['success']) {
                        $provider = 'server1';
                        $s1->setStatusReady($result['activation_id']);
                    } else {
                        Log::info('VN order: Server1 failed, trying Server2', [
                            'service' => $request->service,
                            'error'   => $result['error'] ?? 'unknown',
                        ]);
                    }
                }
            }

            if (!$provider && $server2Enabled) {
                $s2 = new HeroSmsService();
                if ($s2->isConfigured()) {
                    $result = $s2->getNumber($request->service, $country);
                    if ($result['success']) {
                        $provider = 'server2';
                        $s2->setStatusReady($result['activation_id']);
                    }
                }
            }
        } // end else (serverPref !== 2)

        if (!$provider || !$result || !$result['success']) {
            $apiError = $result['error'] ?? 'UNAVAILABLE';
            $msg = match($apiError) {
                'NO_NUMBERS'  => 'No numbers available for this service/country. Please try another.',
                'BAD_SERVICE' => 'Invalid service selected.',
                'NO_BALANCE'  => 'Service temporarily unavailable. Try again later.',
                default       => 'Could not get a number. Please try another service or country.',
            };
            return response()->json(['error' => $msg], 422);
        }

        $serviceName = $this->serviceNames[$request->service] ?? ucwords(str_replace(['-','_'], ' ', $request->service));
        $expiryMins  = (int) Setting::get('herosms_expiry_minutes', '20');

        try {
            $order = DB::transaction(function () use (
                $user, $wallet, $price, $result, $request,
                $serviceName, $expiryMins, $country, $provider
            ) {
                // Acquire row lock and use the freshly-locked instance for the decrement
                $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();
                $lockedWallet->decrement('balance', round((float) $price, 2));

                WalletTransaction::create([
                    'user_id'     => $user->id,
                    'amount'      => -round((float) $price, 2),
                    'type'        => 'purchase',
                    'reference'   => 'VN-' . strtoupper(uniqid()),
                    'description' => "Virtual number: {$serviceName}",
                ]);

                $order = VirtualNumberOrder::create([
                    'user_id'       => $user->id,
                    'activation_id' => (string) ($result['activation_id'] ?? ''),
                    'phone_number'  => (string) ($result['phone_number'] ?? ''),
                    'service'       => (string) $request->service,
                    'service_name'  => mb_substr((string) $serviceName, 0, 100),
                    'country'       => (int) $country,
                    'country_name'  => mb_substr((string) $request->input('country_name', ''), 0, 100),
                    'cost'          => round((float) $price, 2),
                    'status'        => 'waiting',
                    'provider'      => $provider,
                    'expires_at'    => now()->addMinutes($expiryMins),
                ]);

                return $order;
            });
        } catch (\Throwable $e) {
            // Roll back the number on the provider
            try {
                $svc = $this->getProviderService($provider);
                $svc->setStatusCancel($result['activation_id']);
            } catch (\Throwable) {}

            Log::error('VirtualNumber order DB failure', [
                'user_id'  => $user->id,
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Order could not be saved. Your balance has not been charged. Please try again.',
                'debug' => $e->getMessage(),
            ], 500);
        }

        try {
            Notification::create([
                'user_id' => $user->id,
                'title'   => 'Virtual Number Assigned',
                'message' => "Your {$serviceName} number is ready: {$result['phone_number']}",
                'type'    => 'info',
            ]);
        } catch (\Throwable $e) {
            Log::warning('VirtualNumber: notification create failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'order'   => [
                'id'           => $order->id,
                'phone_number' => $order->phone_number,
                'service_name' => $order->service_name,
                'status'       => $order->status,
                'expires_at'   => $order->expires_at?->toIso8601String(),
            ],
        ]);
    }

    private function orderPvaDeals(Request $request)
    {
        if (Setting::get('pvadeals_enabled', '0') !== '1') {
            return response()->json(['error' => 'Server 3 is not available right now.'], 503);
        }

        $sms = new PvaDealsSmsService();
        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Server 3 is not configured.'], 503);
        }

        try {
            $service = $sms->findService((string) $request->input('service'));
        } catch (\Throwable $e) {
            Log::warning('PVADeals catalog lookup failed before purchase.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Could not verify this service. Please try again shortly.'], 503);
        }

        if (!$service || !is_numeric($service['STRprice'] ?? null)
            || (float) $service['STRprice'] <= 0
            || (int) ($service['STRstock'] ?? 0) <= 0) {
            return response()->json(['error' => 'This service is no longer available. Please choose another.'], 422);
        }

        $serviceId = (string) $service['_id'];
        $countryCode = PvaDealsSmsService::countryCode($service['country'] ?? null);
        $countryName = PvaDealsSmsService::countryName($countryCode, $service['country'] ?? null);
        if ($countryCode === ''
            || ($request->filled('country_name')
                && PvaDealsSmsService::countryCode($request->input('country_name')) !== $countryCode)) {
            return response()->json(['error' => 'The selected country does not match this service. Refresh and try again.'], 422);
        }

        $price = HeroSmsService::calculateNgnPrice((float) $service['STRprice']);
        $serviceName = mb_substr((string) ($service['name'] ?? 'Virtual number'), 0, 100);
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ((float) $wallet->balance < $price) {
            return response()->json([
                'error' => 'Insufficient wallet balance. You need ₦' . number_format($price, 2) . '. Please top up first.',
            ], 422);
        }

        $maxActive = (int) Setting::get('herosms_max_active', '3');
        $activeCount = VirtualNumberOrder::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'waiting', 'received'])
            ->count();
        if ($activeCount >= $maxActive) {
            return response()->json([
                'error' => "You have reached the maximum of {$maxActive} active rentals. Complete or cancel existing ones first.",
            ], 422);
        }

        // Reserve the customer's wallet before the upstream call. If the
        // provider response is ambiguous, this pending record prevents a
        // duplicate purchase or an automatic refund of a possibly charged order.
        try {
            $order = DB::transaction(function () use ($user, $wallet, $price, $serviceId, $serviceName, $countryName) {
                $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();
                if ((float) $lockedWallet->balance < $price) {
                    throw new \DomainException('Insufficient wallet balance.');
                }

                $lockedWallet->decrement('balance', round($price, 2));
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => -round($price, 2),
                    'type' => 'purchase',
                    'reference' => 'VN-S3-' . strtoupper(bin2hex(random_bytes(8))),
                    'description' => "Virtual number: {$serviceName}",
                ]);

                return VirtualNumberOrder::create([
                    'user_id' => $user->id,
                    'activation_id' => null,
                    'phone_number' => null,
                    'service' => $serviceId,
                    'service_name' => $serviceName,
                    'country' => 0,
                    'country_name' => mb_substr($countryName, 0, 100),
                    'cost' => round($price, 2),
                    'status' => 'pending',
                    'provider' => 'server3',
                    'expires_at' => null,
                ]);
            });
        } catch (\DomainException) {
            return response()->json([
                'error' => 'Insufficient wallet balance. Please top up and try again.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Could not reserve wallet for PVADeals order.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'The order could not be started. Please try again.'], 500);
        }

        $purchase = $sms->purchaseNumber($serviceId);
        if (!$purchase['success']) {
            if ($purchase['uncertain']) {
                Log::warning('PVADeals order outcome requires review; wallet reservation retained.', [
                    'order_id' => $order->id,
                    'http_status' => $purchase['http_status'] ?? null,
                    'error' => $purchase['error'] ?? 'Unconfirmed provider response',
                ]);

                return response()->json([
                    'error' => 'The provider has not confirmed this order yet. Your wallet amount is temporarily reserved. Do not place it again; contact support with order #' . $order->id . '.',
                    'pending_confirmation' => true,
                    'order_id' => $order->id,
                ], 503);
            }

            try {
                $this->releasePvaDealsReservation($order);
            } catch (\Throwable $e) {
                Log::error('Could not release a failed PVADeals order reservation.', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'error' => 'The provider rejected this order, but the wallet release needs review. Contact support with order #' . $order->id . '.',
                    'pending_confirmation' => true,
                    'order_id' => $order->id,
                ], 500);
            }

            $status = (int) ($purchase['http_status'] ?? 0);
            $message = match ($status) {
                401 => 'Server 3 API credentials were rejected. Please contact support.',
                403 => 'The provider account cannot make this purchase right now.',
                404, 422 => 'No number is available for this service right now. Please try another service.',
                429 => 'Server 3 is busy. Please wait a moment and try again.',
                503 => 'Server 3 is temporarily unavailable. Please try again shortly.',
                default => 'Could not get a number. Please try another service.',
            };

            return response()->json(['error' => $message], 422);
        }

        $providerRequest = $purchase['request'];
        $phoneNumber = ltrim((string) $providerRequest['number'], '+');
        try {
            $expiresAt = !empty($providerRequest['endTime'])
                ? \Illuminate\Support\Carbon::parse($providerRequest['endTime'])
                : now()->addMinutes(20);

            $order->update([
                'activation_id' => (string) $providerRequest['_id'],
                'phone_number' => $phoneNumber,
                'service_name' => mb_substr((string) ($providerRequest['serviceName'] ?? $serviceName), 0, 100),
                'country_name' => mb_substr($countryName, 0, 100),
                'status' => 'waiting',
                'expires_at' => $expiresAt,
            ]);
        } catch (\Throwable $e) {
            Log::critical('PVADeals number purchased but local order could not be finalized.', [
                'order_id' => $order->id,
                'provider_request_id' => $providerRequest['_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'The number was purchased, but the order needs review. Do not place it again; contact support with order #' . $order->id . '.',
                'pending_confirmation' => true,
                'order_id' => $order->id,
            ], 500);
        }

        try {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Virtual Number Assigned',
                'message' => "Your {$serviceName} number is ready: +{$phoneNumber}",
                'type' => 'info',
            ]);
        } catch (\Throwable $e) {
            Log::warning('PVADeals order notification could not be created.', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'phone_number' => $phoneNumber,
                'service_name' => $serviceName,
                'status' => 'waiting',
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ]);
    }

    private function releasePvaDealsReservation(VirtualNumberOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = VirtualNumberOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== 'pending') {
                return;
            }

            $wallet = Wallet::where('user_id', $lockedOrder->user_id)->lockForUpdate()->firstOrFail();
            $refund = round((float) $lockedOrder->cost, 2);
            $wallet->increment('balance', $refund);

            WalletTransaction::create([
                'user_id' => $lockedOrder->user_id,
                'amount' => $refund,
                'type' => 'refund',
                'reference' => 'VN-S3-REF-' . $lockedOrder->id,
                'description' => 'Release of uncompleted PVADeals virtual number reservation',
            ]);

            $lockedOrder->update(['status' => 'cancelled']);
        });
    }

    /**
     * Generic VN webhook — called by any provider when an SMS code arrives.
     * Secured by a secret token in the query string.
     */
    public function webhook(Request $request)
    {
        $secret  = Setting::get('herosms_webhook_secret', '');
        $tokenIn = $request->query('token', '');

        if ($secret && !hash_equals($secret, $tokenIn)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $activationId = $request->input('activation_id') ?? $request->input('id');
        $smsCode      = $request->input('code')
                     ?? $request->input('sms_code')
                     ?? $request->input('message');

        if (!$activationId || !$smsCode) {
            return response()->json(['error' => 'Missing parameters'], 422);
        }

        $order = VirtualNumberOrder::where('activation_id', $activationId)
            ->whereIn('status', ['waiting', 'received'])
            ->first();

        if (!$order) {
            return response()->json(['ok' => true]);
        }

        $order->update(['status' => 'received', 'sms_code' => $smsCode]);

        try {
            Notification::create([
                'user_id' => $order->user_id,
                'title'   => 'SMS Code Received',
                'message' => "Your {$order->service_name} verification code: {$smsCode}",
                'type'    => 'success',
            ]);
        } catch (\Throwable) {}

        Log::info('VN webhook: code received', [
            'activation_id' => $activationId,
            'order_id'      => $order->id,
            'provider'      => $order->provider,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Server1-specific webhook (same logic, different secret setting).
     */
    public function webhookServer1(Request $request)
    {
        $secret  = Setting::get('grizzly_webhook_secret', '');
        $tokenIn = $request->query('token', '');

        if ($secret && !hash_equals($secret, $tokenIn)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $activationId = $request->input('activation_id') ?? $request->input('id');
        $smsCode      = $request->input('code')
                     ?? $request->input('sms_code')
                     ?? $request->input('message');

        if (!$activationId || !$smsCode) {
            return response()->json(['error' => 'Missing parameters'], 422);
        }

        $order = VirtualNumberOrder::where('activation_id', $activationId)
            ->whereIn('status', ['waiting', 'received'])
            ->first();

        if (!$order) {
            return response()->json(['ok' => true]);
        }

        $order->update(['status' => 'received', 'sms_code' => $smsCode]);

        try {
            Notification::create([
                'user_id' => $order->user_id,
                'title'   => 'SMS Code Received',
                'message' => "Your {$order->service_name} verification code: {$smsCode}",
                'type'    => 'success',
            ]);
        } catch (\Throwable) {}

        Log::info('VN server1 webhook: code received', [
            'activation_id' => $activationId,
            'order_id'      => $order->id,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * PVADeals sends the full SMS body using requestId/message fields.
     */
    public function webhookServer3(Request $request)
    {
        $secret = Setting::get('pvadeals_webhook_secret', '');
        $tokenIn = (string) $request->query('token', '');
        if (!$secret || !hash_equals($secret, $tokenIn)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $event = $request->input('event');
        if (!in_array($event, ['number_purchased', 'sms_received'], true)) {
            return response()->json(['ok' => true]);
        }

        $requestId = $request->input('requestId');
        $serviceId = $request->input('serviceId');
        $number = $request->input('number');
        $timestamp = $request->input('timestamp');
        $message = $request->input('message');
        if (!is_string($requestId) || $requestId === ''
            || !is_string($serviceId) || $serviceId === ''
            || !is_string($number) || $number === ''
            || !is_string($timestamp) || $timestamp === ''
            || ($event === 'sms_received'
                && (!is_string($message) || trim($message) === '' || mb_strlen($message) > 5000))) {
            return response()->json(['error' => 'Invalid webhook payload'], 422);
        }

        try {
            $eventAt = \Illuminate\Support\Carbon::parse($timestamp);
        } catch (\Throwable) {
            return response()->json(['error' => 'Invalid webhook timestamp'], 422);
        }

        $order = VirtualNumberOrder::where('provider', 'server3')
            ->where('activation_id', $requestId)
            ->first();

        if (!$order) {
            // A provider webhook can beat the local purchase response after
            // a timeout. Link it only if one pending order matches uniquely.
            $matches = VirtualNumberOrder::where('provider', 'server3')
                ->where('status', 'pending')
                ->where('service', $serviceId)
                ->whereBetween('created_at', [
                    $eventAt->copy()->subSeconds(120),
                    $eventAt->copy()->addSeconds(120),
                ])
                ->get();

            if ($matches->count() !== 1) {
                return response()->json(['error' => 'Order is not uniquely available yet'], 503);
            }

            $order = $matches->first();
        }

        if (in_array($order->status, ['cancelled', 'expired', 'completed'], true)) {
            return response()->json(['ok' => true]);
        }

        $updates = [
            'activation_id' => $requestId,
            'phone_number' => ltrim($number, '+'),
        ];
        if ($event === 'number_purchased') {
            if ($order->status === 'pending') {
                $updates['status'] = 'waiting';
                $updates['expires_at'] = $eventAt->copy()->addMinutes(20);
            }
        } else {
            $updates['status'] = 'received';
            $updates['sms_code'] = trim($message);
        }
        $order->update($updates);

        if ($event === 'sms_received') {
            try {
                Notification::create([
                    'user_id' => $order->user_id,
                    'title' => 'SMS Code Received',
                    'message' => mb_substr("Your {$order->service_name} verification message has arrived.", 0, 250),
                    'type' => 'success',
                ]);
            } catch (\Throwable) {}
        }

        Log::info('PVADeals webhook processed.', [
            'order_id' => $order->id,
            'request_id' => $requestId,
            'event' => $event,
        ]);

        return response()->json(['ok' => true]);
    }

    public function resend(Request $request, VirtualNumberOrder $order)
    {
        if ($order->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'waiting') {
            return response()->json(['error' => 'Can only resend on waiting orders.'], 422);
        }

        if ($order->provider === 'server3') {
            return response()->json(['error' => 'PVADeals does not support resending an SMS for this rental.'], 422);
        }

        $sms = $this->getProviderService($order->provider ?? 'server2');
        $ok  = $sms->setStatusResend($order->activation_id);

        return response()->json(['success' => true, 'sent' => $ok]);
    }

    public function checkStatus(Request $request, VirtualNumberOrder $order)
    {
        if ($order->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($order->provider === 'server3') {
            if ($order->status === 'waiting' && $order->expires_at && $order->expires_at->isPast()) {
                $order->update(['status' => 'expired']);
                $order->refresh();
            }

            return response()->json([
                'status' => $order->status,
                'code' => $order->sms_code,
                'phone_number' => $order->phone_number,
            ]);
        }

        if (!in_array($order->status, ['waiting', 'received'])) {
            return response()->json(['status' => $order->status, 'code' => $order->sms_code]);
        }

        $sms    = $this->getProviderService($order->provider ?? 'server2');
        $result = $sms->getStatus((string) $order->activation_id);

        if ($result['status'] === 'received' && !empty($result['code'])) {
            $order->update(['status' => 'received', 'sms_code' => $result['code']]);
        } elseif ($result['status'] === 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        $order->refresh();

        return response()->json([
            'status' => $order->status,
            'code'   => $order->sms_code,
        ]);
    }

    public function complete(Request $request, VirtualNumberOrder $order)
    {
        if ($order->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (!in_array($order->status, ['waiting', 'received'])) {
            return response()->json(['error' => 'This order cannot be completed.'], 422);
        }

        if ($order->provider !== 'server3') {
            $sms = $this->getProviderService($order->provider ?? 'server2');
            $sms->setStatusComplete($order->activation_id);
        }

        $order->update(['status' => 'completed']);

        return response()->json(['success' => true]);
    }

    public function cancel(Request $request, VirtualNumberOrder $order)
    {
        if ($order->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (!in_array($order->status, ['waiting'])) {
            return response()->json(['error' => 'This order cannot be cancelled.'], 422);
        }

        if ($order->provider === 'server3') {
            if (!$order->activation_id) {
                return response()->json([
                    'error' => 'This purchase is still being confirmed. Contact support before cancelling.',
                ], 422);
            }

            try {
                $cancelled = (new PvaDealsSmsService())->flagNumber((string) $order->activation_id);
                if (!$cancelled) {
                    return response()->json(['error' => 'PVADeals did not confirm that this number can be cancelled.'], 422);
                }
            } catch (\Throwable $e) {
                Log::warning('PVADeals rejected or could not confirm a cancellation.', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'error' => 'The provider did not confirm cancellation. No refund was issued; please try again or contact support.',
                ], 422);
            }
        } else {
            $sms = $this->getProviderService($order->provider ?? 'server2');
            $sms->setStatusCancel($order->activation_id);
        }

        $order->update(['status' => 'cancelled']);

        $refundPct = (float) Setting::get('herosms_cancel_refund_pct', '50');
        $refundAmt = round($order->cost * ($refundPct / 100), 2);

        if ($refundAmt > 0) {
            $wallet = Wallet::firstOrCreate(['user_id' => $order->user_id], ['balance' => 0]);
            $wallet->increment('balance', $refundAmt);

            WalletTransaction::create([
                'user_id'     => $order->user_id,
                'amount'      => $refundAmt,
                'type'        => 'refund',
                'reference'   => 'VN-REF-' . strtoupper(uniqid()),
                'description' => "Refund ({$refundPct}%) for cancelled virtual number",
            ]);
        }

        return response()->json([
            'success'    => true,
            'refunded'   => $refundAmt,
            'refund_pct' => $refundPct,
        ]);
    }

    /**
     * Fetch the NGN price for a service+country from whichever provider is active.
     */
    private function getPriceFromActiveProvider(string $service, int $country): ?float
    {
        $sms = null;

        if (Setting::get('grizzly_enabled', '0') === '1') {
            $s1 = new GrizzlySmsService();
            if ($s1->isConfigured()) $sms = $s1;
        }

        if (!$sms && Setting::get('herosms_enabled', '0') === '1') {
            $s2 = new HeroSmsService();
            if ($s2->isConfigured()) $sms = $s2;
        }

        if (!$sms) return null;

        $rawPrices   = $sms->getPricesForCountry($country);
        $apiUsdPrice = $rawPrices[$service] ?? $rawPrices['*'] ?? null;

        if ($apiUsdPrice !== null) {
            return HeroSmsService::calculateNgnPrice((float) $apiUsdPrice);
        }

        return null;
    }
}
