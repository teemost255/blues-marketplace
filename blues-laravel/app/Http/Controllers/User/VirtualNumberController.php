<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\VirtualNumberOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\GrizzlySmsService;
use App\Services\HeroSmsService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VirtualNumberController extends Controller
{
    public function index()
    {
        $enabled              = Setting::get('virtual_number_enabled', '1') === '1';
        $grizzlySmsConfigured = (new GrizzlySmsService())->isConfigured();
        $heroSmsConfigured    = (new HeroSmsService())->isConfigured();
        $configured           = $grizzlySmsConfigured || $heroSmsConfigured;

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
            'enabled', 'configured', 'grizzlySmsConfigured', 'heroSmsConfigured',
            'orders', 'wallet', 'commissionType', 'commissionValue', 'usdToNgn',
            'activeOrders', 'historyOrders'
        ));
    }

    // ── Countries ─────────────────────────────────────────────────────────────

    public function getCountries(Request $request)
    {
        $server = $request->get('server', '2');

        if ($server === '1') {
            $svc = new HeroSmsService();
            if (!$svc->isConfigured()) {
                return response()->json(['success' => false, 'message' => 'Server 1 (HeroSMS) is not available. Please contact support.']);
            }
            $result = $svc->getCountries();
            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['message']]);
            }
            return response()->json(['success' => true, 'data' => $result['data'], 'flow' => 'TWO_STEP']);
        }

        // Default: GrizzlySMS (server 2)
        $svc = new GrizzlySmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Virtual number service is not available. Please contact support.']);
        }
        $result = $svc->getCountries();
        return response()->json(['success' => true, 'data' => $result['data'], 'flow' => 'TWO_STEP']);
    }

    // ── Services ──────────────────────────────────────────────────────────────

    public function getServices(Request $request)
    {
        $country = (string) $request->get('country', '');
        $server  = $request->get('server', '2');

        if ($server === '1') {
            $svc = new HeroSmsService();
            if (!$svc->isConfigured()) {
                return response()->json(['success' => false, 'message' => 'Server 1 (HeroSMS) is not available. Please contact support.']);
            }
            $result = $svc->getServices($country);
            if ($result['success']) {
                return response()->json(['success' => true, 'data' => $result['data']]);
            }
            return response()->json(['success' => false, 'message' => $result['message']]);
        }

        // Default: GrizzlySMS (server 2)
        $svc = new GrizzlySmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Virtual number service is not available. Please contact support.']);
        }
        $result = $svc->getServices($country);
        if ($result['success']) {
            return response()->json(['success' => true, 'data' => $result['data']]);
        }
        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    // ── Order ─────────────────────────────────────────────────────────────────

    public function order(Request $request)
    {
        $request->validate([
            'provider'     => 'required|string|in:grizzlysms,herosms',
            'server'       => 'nullable|string',
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

        $provider = $request->input('provider', 'grizzlysms');

        if ($provider === 'herosms') {
            return $this->orderHeroSms($request, $wallet, $cost);
        }

        return $this->orderGrizzlySms($request, $wallet, $cost);
    }

    private function orderHeroSms(Request $request, Wallet $wallet, float $cost)
    {
        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return back()->with('error', 'Server 1 (HeroSMS) is currently unavailable. Please try again later.');
        }

        $result = $svc->orderNumber($request->country ?? '', $request->service_id);
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        $data        = $result['data'];
        $serviceName = $request->service_name ?? $request->service_id;

        DB::transaction(function () use ($data, $request, $cost, $wallet, $serviceName) {
            $order = VirtualNumberOrder::create([
                'user_id'           => auth()->id(),
                'provider'          => 'herosms',
                'external_order_id' => (string)($data['order_id'] ?? ''),
                'service'           => $serviceName,
                'country'           => $request->country ?? '',
                'phone_number'      => $data['number'] ?? null,
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
                    'description' => 'Virtual number (S1): ' . $serviceName,
                    'reference'   => 'VN-' . $order->id . '-' . time(),
                ]);
            }
        });

        ReferralService::markPurchased(auth()->user()->fresh());
        return back()->with('success', 'Virtual number ordered successfully! Check your active orders below.');
    }

    private function orderGrizzlySms(Request $request, Wallet $wallet, float $cost)
    {
        $svc = new GrizzlySmsService();
        if (!$svc->isConfigured()) {
            return back()->with('error', 'Virtual number service is currently unavailable. Please try again later.');
        }

        $result = $svc->orderNumber($request->country ?? '', $request->service_id);
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        $data        = $result['data'];
        $serviceName = $request->service_name ?? $request->service_id;

        DB::transaction(function () use ($data, $request, $cost, $wallet, $serviceName) {
            $order = VirtualNumberOrder::create([
                'user_id'           => auth()->id(),
                'provider'          => 'grizzlysms',
                'external_order_id' => (string)($data['order_id'] ?? ''),
                'service'           => $serviceName,
                'country'           => $request->country ?? '',
                'phone_number'      => $data['number'] ?? null,
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
                    'reference'   => 'VN-' . $order->id . '-' . time(),
                ]);
            }
        });

        ReferralService::markPurchased(auth()->user()->fresh());
        return back()->with('success', 'Virtual number ordered successfully! Check your active orders below.');
    }


    // ── Check SMS ─────────────────────────────────────────────────────────────

    public function checkSms(string $orderId)
    {
        $order = VirtualNumberOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$order->external_order_id) {
            return response()->json(['success' => false, 'message' => 'No external order ID.']);
        }

        if ($order->provider === 'herosms') {
            return $this->checkSmsHero($order);
        }

        return $this->checkSmsGrizzly($order);
    }

    private function checkSmsHero(VirtualNumberOrder $order)
    {
        $svc    = new HeroSmsService();
        $result = $svc->checkSms($order->external_order_id);

        if ($result['success']) {
            $data   = $result['data'];
            $sms    = $data['sms'] ?? null;
            $status = $data['status'] ?? 1;

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

        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    private function checkSmsGrizzly(VirtualNumberOrder $order)
    {
        $svc    = new GrizzlySmsService();
        $result = $svc->checkSms($order->external_order_id);

        if ($result['success']) {
            $data      = $result['data'];
            $sms       = $data['sms'] ?? null;
            $newStatus = match($data['status'] ?? 'pending') {
                'received'  => 'received',
                'cancelled' => 'cancelled',
                default     => $order->status,
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
                'success'       => true,
                'sms_code'      => $order->sms_code,
                'status'        => $order->status,
                'sms_received_at' => $order->sms_received_at?->toIso8601String(),
            ]);
        }

        return response()->json(['success' => false, 'message' => $result['message']]);
    }


    // ── Cancel ────────────────────────────────────────────────────────────────

    public function cancel(string $orderId)
    {
        $order = VirtualNumberOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        if ($order->provider === 'herosms') {
            return $this->cancelHero($order);
        }

        return $this->cancelGrizzly($order);
    }

    private function cancelHero(VirtualNumberOrder $order)
    {
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

    private function cancelGrizzly(VirtualNumberOrder $order)
    {
        $svc    = new GrizzlySmsService();
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
                'reference'   => 'REFUND-VN-' . $order->id . '-' . time(),
            ]);
        }
    }
}
