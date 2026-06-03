<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Wallet, WalletTransaction, Notification, Setting, VirtualNumberOrder};
use App\Services\HeroSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log};

class VirtualNumberController extends Controller
{
    private array $serviceNames = [
        'tg' => 'Telegram',  'wa' => 'WhatsApp',  'go' => 'Google',
        'fb' => 'Facebook',  'ig' => 'Instagram',  'tw' => 'Twitter / X',
        'tk' => 'TikTok',    'ds' => 'Discord',    'am' => 'Amazon',
        'ma' => 'Microsoft', 'ap' => 'Apple',      'ya' => 'Yahoo',
        'li' => 'LinkedIn',  'ub' => 'Uber',       'nf' => 'Netflix',
        'vi' => 'Viber',     'si' => 'Signal',     'mm' => 'Mail.ru',
        'vk' => 'VKontakte', 'ok' => 'OK.ru',
    ];

    public function index()
    {
        $user    = Auth::user();
        $wallet  = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $enabled = Setting::get('herosms_enabled', '0') === '1';

        $orders = VirtualNumberOrder::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('dashboard.virtual-numbers', compact('wallet', 'enabled', 'orders'));
    }

    public function getCountries()
    {
        $sms = new HeroSmsService();
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
        $request->validate(['country' => 'nullable|integer']);
        $sms = new HeroSmsService();

        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Service not configured.'], 503);
        }

        $country = (int) ($request->country ?? 0);

        // Fetch availability counts and prices in parallel (both needed)
        $rawCounts = $sms->getServicesForCountry($country);
        $rawPrices = $sms->getPricesForCountry($country);

        $services = [];

        foreach ($rawCounts as $code => $count) {
            if ((int) $count <= 0) continue;

            // Look up API USD price for this service
            $apiUsdPrice = $rawPrices[$code] ?? $rawPrices['*'] ?? null;
            $priceNgn    = null;

            if ($apiUsdPrice !== null) {
                $priceNgn = HeroSmsService::calculateNgnPrice((float) $apiUsdPrice);
            } else {
                // Fallback: use the stored flat commission as minimum price
                $priceNgn = (float) Setting::get('herosms_number_price', '200');
            }

            $services[] = [
                'code'     => $code,
                'name'     => $this->serviceNames[$code] ?? strtoupper($code),
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

        return response()->json(['services' => $services]);
    }

    public function order(Request $request)
    {
        $request->validate([
            'service' => 'required|string|max:10',
            'country' => 'required|integer|min:1',
        ]);

        if (Setting::get('herosms_enabled', '0') !== '1') {
            return response()->json(['error' => 'Virtual numbers are not available right now.'], 503);
        }

        $sms = new HeroSmsService();
        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Service not configured. Contact support.'], 503);
        }

        // Fetch live API price for this service+country
        $country     = (int) $request->country;
        $rawPrices   = $sms->getPricesForCountry($country);
        $apiUsdPrice = $rawPrices[$request->service] ?? $rawPrices['*'] ?? null;

        if ($apiUsdPrice !== null) {
            $price = HeroSmsService::calculateNgnPrice((float) $apiUsdPrice);
        } else {
            // Fallback to flat commission setting when API price unavailable
            $price = (float) Setting::get('herosms_number_price', '200');
        }

        $user   = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ($wallet->balance < $price) {
            return response()->json([
                'error' => 'Insufficient wallet balance. You need ₦' . number_format($price, 2) . '. Please top up first.',
            ], 422);
        }

        // Check active rental limit
        $maxActive  = (int) Setting::get('herosms_max_active', '3');
        $activeCount = VirtualNumberOrder::where('user_id', $user->id)
            ->whereIn('status', ['waiting', 'received'])
            ->count();

        if ($activeCount >= $maxActive) {
            return response()->json([
                'error' => "You have reached the maximum of {$maxActive} active rentals. Complete or cancel existing ones first.",
            ], 422);
        }

        $result = $sms->getNumber($request->service, $country);

        if (!$result['success']) {
            $msg = match($result['error'] ?? '') {
                'NO_NUMBERS'  => 'No numbers available for this service/country combination.',
                'NO_BALANCE'  => 'Service temporarily unavailable. Try again later.',
                'BAD_SERVICE' => 'Invalid service selected.',
                default       => 'Could not get a number. Please try another service or country.',
            };
            return response()->json(['error' => $msg], 422);
        }

        $sms->setStatusReady($result['activation_id']);

        $serviceName = $this->serviceNames[$request->service] ?? strtoupper($request->service);
        $expiryMins  = (int) Setting::get('herosms_expiry_minutes', '20');

        try {
            $order = DB::transaction(function () use (
                $user, $wallet, $price, $result, $request,
                $serviceName, $expiryMins, $country, $apiUsdPrice
            ) {
                // Use lockForUpdate to prevent race conditions on the wallet row
                Wallet::where('id', $wallet->id)->lockForUpdate()->first();
                $wallet->decrement('balance', round((float) $price, 2));

                WalletTransaction::create([
                    'user_id'     => $user->id,
                    'amount'      => -round((float) $price, 2),
                    'type'        => 'purchase',
                    'reference'   => 'VN-' . strtoupper(uniqid()),
                    'description' => "Virtual number: {$serviceName}" . ($apiUsdPrice !== null ? " (\${$apiUsdPrice})" : ''),
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
                    'expires_at'    => now()->addMinutes($expiryMins),
                ]);

                return $order;
            });
        } catch (\Throwable $e) {
            // DB transaction rolled back — cancel the number with HeroSMS so they don't charge us
            try { $sms->setStatusCancel($result['activation_id']); } catch (\Throwable) {}
            Log::error('VirtualNumber order DB failure', [
                'user_id'       => $user->id,
                'activation_id' => $result['activation_id'] ?? null,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Order could not be saved. Your balance has not been charged. Please try again.',
                'debug' => $e->getMessage(),
            ], 500);
        }

        // Notification is intentionally OUTSIDE the transaction — a notification
        // failure must never roll back a successfully saved order.
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

    /**
     * HeroSMS push webhook — called by HeroSMS when an SMS code arrives.
     * No CSRF, no auth. Secured by a secret token in the URL query string.
     * Expected params: activation_id, code  (or sms_code / message)
     */
    public function webhook(Request $request)
    {
        // Verify secret token to reject spoofed requests
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
            // Unknown or already closed — acknowledge silently
            return response()->json(['ok' => true]);
        }

        $order->update(['status' => 'received', 'sms_code' => $smsCode]);

        // Notify the user
        Notification::create([
            'user_id' => $order->user_id,
            'title'   => 'SMS Code Received',
            'message' => "Your {$order->service_name} verification code: {$smsCode}",
            'type'    => 'success',
        ]);

        Log::info('HeroSMS webhook: code received', [
            'activation_id' => $activationId,
            'order_id'      => $order->id,
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

        $sms = new HeroSmsService();
        $ok  = $sms->setStatusResend($order->activation_id);

        Log::info('VirtualNumber resend', [
            'order_id'      => $order->id,
            'activation_id' => $order->activation_id,
            'ok'            => $ok,
        ]);

        return response()->json(['success' => true, 'sent' => $ok]);
    }

    public function checkStatus(Request $request, VirtualNumberOrder $order)
    {
        if ($order->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (!in_array($order->status, ['waiting', 'received'])) {
            return response()->json(['status' => $order->status, 'code' => $order->sms_code]);
        }

        $sms    = new HeroSmsService();
        $result = $sms->getStatus((string) $order->activation_id);

        Log::info('VirtualNumber checkStatus', [
            'order_id'      => $order->id,
            'activation_id' => $order->activation_id,
            'api_result'    => $result,
        ]);

        if ($result['status'] === 'received' && !empty($result['code'])) {
            $order->update(['status' => 'received', 'sms_code' => $result['code']]);
        } elseif ($result['status'] === 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        $order->refresh();

        return response()->json([
            'status'     => $order->status,
            'code'       => $order->sms_code,
            'api_status' => $result['status'],
            'api_raw'    => $result['raw'] ?? null,
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

        $sms = new HeroSmsService();
        $sms->setStatusComplete($order->activation_id);

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

        $sms = new HeroSmsService();
        $sms->setStatusCancel($order->activation_id);

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
}
