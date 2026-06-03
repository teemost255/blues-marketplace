<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Wallet, WalletTransaction, Notification, Setting, VirtualNumberOrder};
use App\Services\HeroSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};

class VirtualNumberController extends Controller
{
    private array $serviceNames = [
        'tg' => 'Telegram',  'wa' => 'WhatsApp',  'go' => 'Google',
        'fb' => 'Facebook',  'ig' => 'Instagram',  'tw' => 'Twitter / X',
        'tk' => 'TikTok',    'ds' => 'Discord',    'am' => 'Amazon',
        'ma' => 'Microsoft', 'ap' => 'Apple',      'ya' => 'Yahoo',
        'li' => 'LinkedIn',  'ub' => 'Uber',       'nf' => 'Netflix',
        'vi' => 'Viber',     'si' => 'Signal',      'mm' => 'Mail.ru',
        'vk' => 'VKontakte', 'ok' => 'OK.ru',
    ];

    public function index()
    {
        $user    = Auth::user();
        $wallet  = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $enabled = Setting::get('herosms_enabled', '0') === '1';
        $price   = (float) Setting::get('herosms_number_price', '200');

        $orders = VirtualNumberOrder::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('dashboard.virtual-numbers', compact('wallet', 'enabled', 'price', 'orders'));
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
        $sms     = new HeroSmsService();

        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Service not configured.'], 503);
        }

        $country  = (int) ($request->country ?? 0);
        $raw      = $sms->getServicesForCountry($country);
        $services = [];

        foreach ($raw as $code => $count) {
            if ((int) $count > 0) {
                $services[] = [
                    'code'  => $code,
                    'name'  => $this->serviceNames[$code] ?? strtoupper($code),
                    'count' => (int) $count,
                ];
            }
        }

        $sort = $request->input('sort', 'az');
        if ($sort === 'az') {
            usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));
        } elseif ($sort === 'za') {
            usort($services, fn($a, $b) => strcmp($b['name'], $a['name']));
        } else {
            usort($services, fn($a, $b) => $b['count'] <=> $a['count']);
        }

        return response()->json(['services' => $services]);
    }

    public function order(Request $request)
    {
        $request->validate([
            'service' => 'required|string|max:10',
            'country' => 'required|integer',
        ]);

        if (Setting::get('herosms_enabled', '0') !== '1') {
            return response()->json(['error' => 'Virtual numbers are not available right now.'], 503);
        }

        $price = (float) Setting::get('herosms_number_price', '200');
        $user  = Auth::user();

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ($wallet->balance < $price) {
            return response()->json(['error' => 'Insufficient wallet balance. Please top up first.'], 422);
        }

        $sms = new HeroSmsService();
        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Service not configured. Contact support.'], 503);
        }

        $result = $sms->getNumber($request->service, (int) $request->country);

        if (!$result['success']) {
            $msg = match($result['error'] ?? '') {
                'NO_NUMBERS'   => 'No numbers available for this service/country combination.',
                'NO_BALANCE'   => 'Service temporarily unavailable. Try again later.',
                'BAD_SERVICE'  => 'Invalid service selected.',
                default        => 'Could not get a number. Please try another service or country.',
            };
            return response()->json(['error' => $msg], 422);
        }

        $sms->setStatusReady($result['activation_id']);

        $wallet->decrement('balance', $price);

        WalletTransaction::create([
            'user_id'     => $user->id,
            'amount'      => -$price,
            'type'        => 'virtual_number',
            'reference'   => 'VN-' . strtoupper(uniqid()),
            'description' => 'Virtual number: ' . ($this->serviceNames[$request->service] ?? $request->service),
        ]);

        $serviceName = $this->serviceNames[$request->service] ?? strtoupper($request->service);

        $order = VirtualNumberOrder::create([
            'user_id'       => $user->id,
            'activation_id' => $result['activation_id'],
            'phone_number'  => $result['phone_number'],
            'service'       => $request->service,
            'service_name'  => $serviceName,
            'country'       => (int) $request->country,
            'country_name'  => $request->input('country_name', ''),
            'cost'          => $price,
            'status'        => 'waiting',
            'expires_at'    => now()->addMinutes(20),
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Virtual Number Assigned',
            'message' => "Your {$serviceName} number is ready: {$result['phone_number']}",
            'type'    => 'info',
        ]);

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

    public function checkStatus(Request $request, VirtualNumberOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (!in_array($order->status, ['waiting', 'received'])) {
            return response()->json(['status' => $order->status, 'code' => $order->sms_code]);
        }

        $sms    = new HeroSmsService();
        $result = $sms->getStatus($order->activation_id);

        if ($result['status'] === 'received' && $result['code']) {
            $order->update(['status' => 'received', 'sms_code' => $result['code']]);
        } elseif ($result['status'] === 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json([
            'status' => $order->fresh()->status,
            'code'   => $order->fresh()->sms_code,
        ]);
    }

    public function complete(Request $request, VirtualNumberOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
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
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (!in_array($order->status, ['waiting'])) {
            return response()->json(['error' => 'This order cannot be cancelled.'], 422);
        }

        $sms = new HeroSmsService();
        $sms->setStatusCancel($order->activation_id);

        $order->update(['status' => 'cancelled']);

        $refundPct  = (float) Setting::get('herosms_cancel_refund_pct', '50');
        $refundAmt  = round($order->cost * ($refundPct / 100), 2);

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
