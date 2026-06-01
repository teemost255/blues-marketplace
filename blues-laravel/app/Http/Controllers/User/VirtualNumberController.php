<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\VirtualNumberOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\HeroSmsService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VirtualNumberController extends Controller
{
    public function index()
    {
        $enabled           = Setting::get('virtual_number_enabled', '1') === '1';
        $heroSmsConfigured = (new HeroSmsService())->isConfigured();
        $configured        = $heroSmsConfigured;

        $orders  = VirtualNumberOrder::where('user_id', auth()->id())->latest()->paginate(10);
        $wallet  = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

        $commissionType  = Setting::get('vn_commission_type', 'flat');
        $commissionValue = (float) Setting::get('vn_commission_value', '0');
        $usdToNgn        = (float) Setting::get('usd_to_ngn_rate', '1600');

        // Auto-graduate any 'received' orders older than 3 minutes to 'completed'
        VirtualNumberOrder::where('user_id', auth()->id())
            ->where('status', 'received')
            ->where('sms_received_at', '<', now()->subMinutes(3))
            ->update(['status' => 'completed']);

        // Re-fetch so the above change is reflected
        $orders = VirtualNumberOrder::where('user_id', auth()->id())->latest()->paginate(10);

        $activeOrders  = $orders->getCollection()->filter(fn($o) => in_array($o->status, ['active', 'received']));
        $historyOrders = $orders->getCollection()->filter(fn($o) => in_array($o->status, ['completed', 'cancelled']));

        return view('dashboard.virtual-numbers', compact(
            'enabled', 'configured', 'heroSmsConfigured',
            'orders', 'wallet', 'commissionType', 'commissionValue', 'usdToNgn',
            'activeOrders', 'historyOrders'
        ));
    }

    // ── Countries ─────────────────────────────────────────────────────────────

    public function getCountries(Request $request)
    {
        $cacheKey = 'vn.countries.1';

        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Virtual number service is not available. Please contact support.']);
        }
        $data = Cache::remember($cacheKey, 600, function () use ($svc) {
            $result = $svc->getCountries();
            return $result['success'] ? $result['data'] : null;
        });
        if ($data === null) {
            Cache::forget($cacheKey);
            return response()->json(['success' => false, 'message' => 'Could not fetch countries. Please try again.']);
        }
        return response()->json(['success' => true, 'data' => $data, 'flow' => 'TWO_STEP']);
    }

    // ── Services ──────────────────────────────────────────────────────────────

    public function getServices(Request $request)
    {
        $country  = (string) $request->get('country', '');

        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Virtual number service is not available. Please contact support.']);
        }
        $usdToNgn = (float) Setting::get('usd_to_ngn_rate', '1600');
        $cacheKey = 'vn.services.1.' . md5($country . '_' . $usdToNgn);
        $errorMsg = null;
        $data = Cache::remember($cacheKey, 300, function () use ($svc, $country, $usdToNgn, &$errorMsg) {
            $result = $svc->getServices($country);
            if (!$result['success']) {
                $errorMsg = $result['message'] ?? 'Could not fetch services.';
                return null;
            }
            return array_map(function ($s) use ($usdToNgn) {
                $s['cost_ngn'] = round(($s['cost'] ?? 0) * $usdToNgn, 2);
                return $s;
            }, $result['data']);
        });
        if ($data === null) {
            Cache::forget($cacheKey);
            return response()->json(['success' => false, 'message' => $errorMsg ?? 'Could not fetch services. Please try again.']);
        }
        return response()->json(['success' => true, 'data' => $data]);
    }

    // ── Order ─────────────────────────────────────────────────────────────────

    public function order(Request $request)
    {
        $request->validate([
            'provider'     => 'required|string|in:herosms',
            'service_id'   => 'required|string',
            'country'      => 'nullable|string',
            'price'        => 'nullable|numeric|min:0',
            'service_name' => 'nullable|string',
        ]);

        $enabled = Setting::get('virtual_number_enabled', '1') === '1';
        if (!$enabled) {
            return back()->with('error', 'Virtual numbers are currently unavailable.');
        }

        $wallet     = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);
        $apiCost    = (float)($request->price ?? 0);
        $commType   = Setting::get('vn_commission_type', 'flat');
        $commValue  = (float) Setting::get('vn_commission_value', '0');
        $commission = $commType === 'percent' ? round($apiCost * $commValue / 100, 2) : $commValue;
        $cost       = round($apiCost + $commission, 2);

        if ($cost > 0 && $wallet->balance < $cost) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        return $this->orderHeroSms($request, $wallet, $cost);
    }

    private function orderHeroSms(Request $request, Wallet $wallet, float $cost)
    {
        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return back()->with('error', 'Virtual number service is currently unavailable. Please try again later.');
        }

        $orderCountry = $request->country ?? '';
        $orderService = $request->service_id;
        \Illuminate\Support\Facades\Log::info('HeroSMS order attempt | user#' . auth()->id() . ' service=' . $orderService . ' country=' . $orderCountry);

        $result = $svc->orderNumber($orderCountry, $orderService);
        if (!$result['success']) {
            \Illuminate\Support\Facades\Log::warning('HeroSMS order failed | user#' . auth()->id() . ' service=' . $orderService . ' country=' . $orderCountry . ' | ' . ($result['message'] ?? 'unknown'));
            return back()->with('error', $result['message']);
        }

        $data        = $result['data'];
        $serviceName = $request->service_name ?? $request->service_id;
        $externalId  = (string)($data['order_id'] ?? '');
        $phoneNumber = ltrim(trim((string)($data['number'] ?? '')), '+');

        DB::transaction(function () use ($data, $request, $cost, $wallet, $serviceName, $externalId, $phoneNumber) {
            $order = VirtualNumberOrder::create([
                'user_id'           => auth()->id(),
                'provider'          => 'herosms',
                'external_order_id' => $externalId,
                'service'           => $serviceName,
                'country'           => $request->country ?? '',
                'phone_number'      => $phoneNumber ?: null,
                'cost'              => $cost,
                'status'            => 'active',
                'raw_response'      => json_encode($data),
            ]);

            if ($cost > 0) {
                $wallet->decrement('balance', $cost);
                WalletTransaction::create([
                    'user_id'     => auth()->id(),
                    'type'        => 'withdrawal',
                    'amount'      => $cost,
                    'description' => 'Virtual number: ' . $serviceName,
                    'reference'   => 'VN-' . $order->id . '-' . uniqid('-', true),
                ]);
            }
        });

        // Signal Hero-SMS that we are ready to receive SMS.
        // This MUST happen after getNumber or Hero-SMS will never deliver the OTP.
        if ($externalId) {
            $svc->readyForSms($externalId);
        }

        ReferralService::markPurchased(auth()->user()->fresh());
        return back()->with('success', 'Virtual number ordered! Check your active orders below.');
    }

    // ── Resend / Request new code ─────────────────────────────────────────────

    public function resend(string $orderId)
    {
        $order = VirtualNumberOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'received'])
            ->firstOrFail();

        if (!$order->external_order_id) {
            return response()->json(['success' => false, 'message' => 'No external order ID found.']);
        }

        $svc    = new HeroSmsService();
        $result = $svc->requestResend($order->external_order_id);

        if ($result['success']) {
            // Reset the SMS code and status back to active so the UI starts waiting again
            $order->update([
                'sms_code'        => null,
                'status'          => 'active',
                'sms_received_at' => null,
            ]);
            return response()->json(['success' => true, 'message' => 'New code requested. Waiting for SMS…']);
        }

        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Could not request a new code. Please try again.']);
    }

    // ── Check SMS ─────────────────────────────────────────────────────────────

    public function checkSms(string $orderId)
    {
        $order = VirtualNumberOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Already in a final state — return current DB values without hitting the API
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success'         => true,
                'sms_code'        => $order->sms_code,
                'status'          => $order->status,
                'sms_received_at' => $order->sms_received_at?->toIso8601String(),
            ]);
        }

        if (!$order->external_order_id) {
            return response()->json(['success' => false, 'message' => 'No external order ID.']);
        }

        $svc    = new HeroSmsService();
        $result = $svc->checkSms($order->external_order_id);

        \Illuminate\Support\Facades\Log::info('checkSms order#' . $order->id . ' result: ' . json_encode($result));

        if ($result['success']) {
            $data   = $result['data'];
            $sms    = isset($data['sms']) ? trim((string) $data['sms']) : null;
            $status = $data['status'] ?? 1;

            if ($sms === '') $sms = null;

            $newStatus = match($status) {
                3 => 'received',
                6 => 'cancelled',
                default => $order->status,
            };

            $updates = [
                'sms_code' => $sms ?: $order->sms_code,
                'status'   => $newStatus,
            ];

            if ($newStatus === 'received' && !$order->sms_received_at) {
                $updates['sms_received_at'] = now();
            }

            if ($newStatus === 'received' && $order->sms_received_at && $order->sms_received_at->lt(now()->subMinutes(3))) {
                $updates['status'] = 'completed';
            }

            $order->update($updates);
            $order->refresh();

            return response()->json([
                'success'         => true,
                'sms_code'        => $order->sms_code,
                'status'          => $order->status,
                'sms_received_at' => $order->sms_received_at?->toIso8601String(),
            ]);
        }

        \Illuminate\Support\Facades\Log::warning('checkSms order#' . $order->id . ' failed: ' . ($result['message'] ?? 'unknown'));
        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function cancel(string $orderId)
    {
        $order = VirtualNumberOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'received'])
            ->firstOrFail();

        // If SMS was already received, just dismiss the order — no API cancel, no refund
        if ($order->status === 'received') {
            $order->update(['status' => 'cancelled']);
            return back()->with('success', 'Order dismissed.');
        }

        $svc    = new HeroSmsService();
        $result = ['success' => true, 'data' => []];

        if ($order->external_order_id) {
            $result = $svc->cancelOrder($order->external_order_id);
        }

        if ($result['success']) {
            $order->update(['status' => 'cancelled']);
            if ($order->cost > 0) {
                $this->processRefund($order);
            }
            return back()->with('success', 'Order cancelled and wallet refunded.');
        }

        return back()->with('error', $result['message'] ?? 'Could not cancel order.');
    }

    private function processRefund(VirtualNumberOrder $order): void
    {
        $wallet = Wallet::where('user_id', $order->user_id)->first();
        if ($wallet) {
            $wallet->increment('balance', $order->cost);
            WalletTransaction::create([
                'user_id'     => $order->user_id,
                'type'        => 'refund',
                'amount'      => $order->cost,
                'description' => 'Refund: cancelled virtual number #' . $order->id,
                'reference'   => 'REFUND-VN-' . $order->id . '-' . uniqid('', true),
            ]);
        }
    }
}
