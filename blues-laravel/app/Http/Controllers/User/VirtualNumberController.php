<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\VirtualNumberOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\GrizzlySmsService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VirtualNumberController extends Controller
{
    public function index()
    {
        $enabled              = Setting::get('virtual_number_enabled', '1') === '1';
        $grizzlySmsConfigured = (new GrizzlySmsService())->isConfigured();
        $configured           = $grizzlySmsConfigured;

        $orders       = VirtualNumberOrder::where('user_id', auth()->id())->latest()->paginate(10);
        $wallet       = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

        $commissionType  = Setting::get('vn_commission_type', 'flat');
        $commissionValue = (float) Setting::get('vn_commission_value', '0');
        $usdToNgn        = (float) Setting::get('usd_to_ngn_rate', '1600');

        $activeOrders  = $orders->getCollection()->filter(fn($o) => $o->status === 'active');
        $historyOrders = $orders->getCollection()->filter(fn($o) => $o->status !== 'active');

        return view('dashboard.virtual-numbers', compact(
            'enabled', 'configured', 'grizzlySmsConfigured',
            'orders', 'wallet', 'commissionType', 'commissionValue', 'usdToNgn',
            'activeOrders', 'historyOrders'
        ));
    }

    // ── Countries ─────────────────────────────────────────────────────────────

    public function getCountries(Request $request)
    {
        $svc = new GrizzlySmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'GrizzlySMS API not configured. Add your key in Settings.']);
        }
        $result = $svc->getCountries();
        return response()->json(['success' => true, 'data' => $result['data'], 'flow' => 'TWO_STEP']);
    }

    // ── Services ──────────────────────────────────────────────────────────────

    public function getServices(Request $request)
    {
        $country = $request->get('country');

        $svc = new GrizzlySmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'GrizzlySMS API not configured. Add your key in Settings.']);
        }
        if (!$country) {
            return response()->json(['success' => false, 'message' => 'Please select a country first.']);
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
        $provider = $request->input('provider', 'grizzlysms');

        $request->validate([
            'provider'     => 'required|string|in:grizzlysms',
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

        $wallet      = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);
        $apiCost     = (float)($request->price ?? 0);
        $commType    = Setting::get('vn_commission_type', 'flat');
        $commValue   = (float) Setting::get('vn_commission_value', '0');
        $commission  = $commType === 'percent' ? round($apiCost * $commValue / 100, 2) : $commValue;
        $cost        = round($apiCost + $commission, 2);

        if ($cost > 0 && $wallet->balance < $cost) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        return $this->orderGrizzlySms($request, $wallet, $cost);
    }

    private function orderGrizzlySms(Request $request, Wallet $wallet, float $cost)
    {
        $svc = new GrizzlySmsService();
        if (!$svc->isConfigured()) {
            return back()->with('error', 'GrizzlySMS is not configured yet.');
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
                    'description' => 'Virtual number (GrizzlySMS): ' . $serviceName,
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

        return $this->checkSmsGrizzly($order);
    }

    private function checkSmsGrizzly(VirtualNumberOrder $order)
    {
        $svc    = new GrizzlySmsService();
        $result = $svc->checkSms($order->external_order_id);

        if ($result['success']) {
            $data      = $result['data'];
            $sms       = $data['sms'] ?? null;
            $newStatus = match($data['status'] ?? 'pending') {
                'received'  => 'completed',
                'cancelled' => 'cancelled',
                default     => $order->status,
            };

            $order->update([
                'sms_code' => $sms ?: $order->sms_code,
                'status'   => $newStatus,
            ]);

            return response()->json([
                'success'  => true,
                'sms_code' => $order->fresh()->sms_code,
                'status'   => $order->fresh()->status,
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

        return $this->cancelGrizzly($order);
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

        return back()->with('error', 'Could not cancel: ' . $result['message']);
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
