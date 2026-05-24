<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\VirtualNumberOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LogsplugService;
use App\Services\SmsPoolService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VirtualNumberController extends Controller
{
    public function index()
    {
        $enabled           = Setting::get('virtual_number_enabled', '1') === '1';
        $logsplugConfigured = (new LogsplugService())->isConfigured();
        $smsPoolConfigured  = (new SmsPoolService())->isConfigured();
        $configured         = $logsplugConfigured || $smsPoolConfigured;

        $orders       = VirtualNumberOrder::where('user_id', auth()->id())->latest()->paginate(10);
        $wallet       = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

        $commissionType  = Setting::get('vn_commission_type', 'flat');
        $commissionValue = (float) Setting::get('vn_commission_value', '0');

        $activeOrders  = $orders->getCollection()->filter(fn($o) => $o->status === 'active');
        $historyOrders = $orders->getCollection()->filter(fn($o) => $o->status !== 'active');

        return view('dashboard.virtual-numbers', compact(
            'enabled', 'configured', 'logsplugConfigured', 'smsPoolConfigured',
            'orders', 'wallet', 'commissionType', 'commissionValue',
            'activeOrders', 'historyOrders'
        ));
    }

    // ── Countries ─────────────────────────────────────────────────────────────

    public function getCountries(Request $request)
    {
        $server   = $request->get('server', 'server2');
        $provider = $request->get('provider', 'logsplug');

        if ($provider === 'smspool') {
            $svc = new SmsPoolService();
            if (!$svc->isConfigured()) {
                return response()->json(['success' => false, 'message' => 'SMSPool API not configured.']);
            }
            $result = $svc->getCountries();
            if ($result['success']) {
                return response()->json(['success' => true, 'data' => $result['data'], 'flow' => 'TWO_STEP']);
            }
            return response()->json(['success' => false, 'message' => $result['message']]);
        }

        // Logsplug
        $svc = new LogsplugService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'API not configured.']);
        }

        if ($server === 'server1') {
            return response()->json(['success' => true, 'data' => [], 'flow' => 'ONE_STEP']);
        }

        $result = $svc->getCountries($server);
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data'    => $result['data']['data'] ?? [],
                'flow'    => 'TWO_STEP',
            ]);
        }

        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    // ── Services ──────────────────────────────────────────────────────────────

    public function getServices(Request $request)
    {
        $server   = $request->get('server', 'server2');
        $country  = $request->get('country');
        $provider = $request->get('provider', 'logsplug');

        if ($provider === 'smspool') {
            $svc = new SmsPoolService();
            if (!$svc->isConfigured()) {
                return response()->json(['success' => false, 'message' => 'SMSPool API not configured.']);
            }
            $result = $svc->getServices($country ?: null);
            if ($result['success']) {
                return response()->json(['success' => true, 'data' => $result['data']]);
            }
            return response()->json(['success' => false, 'message' => $result['message']]);
        }

        // Logsplug
        $svc = new LogsplugService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'API not configured.']);
        }

        $result = $svc->getServices($server, $country ?: null);
        if ($result['success']) {
            return response()->json(['success' => true, 'data' => $result['data']['data'] ?? []]);
        }

        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    // ── Order ─────────────────────────────────────────────────────────────────

    public function order(Request $request)
    {
        $provider = $request->input('provider', 'logsplug');

        $request->validate([
            'provider'     => 'required|string|in:logsplug,smspool',
            'server'       => 'nullable|string|in:server1,server2',
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

        if ($provider === 'smspool') {
            return $this->orderSmsPool($request, $wallet, $apiCost, $commission, $cost);
        }

        return $this->orderLogsplug($request, $wallet, $apiCost, $commission, $cost);
    }

    private function orderLogsplug(Request $request, Wallet $wallet, float $apiCost, float $commission, float $cost)
    {
        $svc = new LogsplugService();
        if (!$svc->isConfigured()) {
            return back()->with('error', 'Virtual numbers are currently unavailable.');
        }

        $rentResult = $svc->rentNumber($request->input('server', 'server2'), $request->service_id, $request->country ?? '');
        if (!$rentResult['success']) {
            return back()->with('error', 'Could not get a number: ' . $rentResult['message']);
        }

        $data        = $rentResult['data']['data'] ?? $rentResult['data'];
        $serviceName = $request->service_name ?? $request->service_id;

        DB::transaction(function () use ($data, $request, $cost, $wallet, $serviceName) {
            $order = VirtualNumberOrder::create([
                'user_id'           => auth()->id(),
                'provider'          => 'logsplug',
                'external_order_id' => (string)($data['id'] ?? ''),
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
                    'description' => 'Virtual number (Logsplug): ' . $serviceName,
                    'reference'   => 'VN-' . $order->id . '-' . time(),
                ]);
            }
        });

        ReferralService::markPurchased(auth()->user()->fresh());
        return back()->with('success', 'Virtual number ordered successfully! Check your active orders below.');
    }

    private function orderSmsPool(Request $request, Wallet $wallet, float $apiCost, float $commission, float $cost)
    {
        $svc = new SmsPoolService();
        if (!$svc->isConfigured()) {
            return back()->with('error', 'SMSPool is not configured yet.');
        }

        $result = $svc->orderNumber($request->country ?? '', $request->service_id);
        if (!$result['success']) {
            return back()->with('error', 'Could not get a number: ' . $result['message']);
        }

        $data        = $result['data'];
        $serviceName = $request->service_name ?? $request->service_id;

        DB::transaction(function () use ($data, $request, $cost, $wallet, $serviceName) {
            $order = VirtualNumberOrder::create([
                'user_id'           => auth()->id(),
                'provider'          => 'smspool',
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
                    'description' => 'Virtual number (SMSPool): ' . $serviceName,
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

        if ($order->provider === 'smspool') {
            return $this->checkSmsSmsPool($order);
        }

        return $this->checkSmsLogsplug($order);
    }

    private function checkSmsLogsplug(VirtualNumberOrder $order)
    {
        $svc    = new LogsplugService();
        $result = $svc->getOtp($order->external_order_id);

        if ($result['success']) {
            $data      = $result['data']['data'] ?? $result['data'];
            $codes     = $data['code'] ?? [];
            $sms       = is_array($codes) ? implode(', ', $codes) : (string)$codes;
            $status    = strtolower((string)($data['status'] ?? ''));

            $newStatus = match($status) {
                'received', 'completed', 'success' => 'completed',
                'cancelled', 'canceled'             => 'cancelled',
                default                             => $order->status,
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

    private function checkSmsSmsPool(VirtualNumberOrder $order)
    {
        $svc    = new SmsPoolService();
        $result = $svc->checkSms($order->external_order_id);

        if ($result['success']) {
            $data   = $result['data'];
            // SMSPool status: 1=pending, 2=in-use, 3=completed, 6=refunded/cancelled
            $status = (int)($data['status'] ?? 1);
            $sms    = $data['sms'] ?? null;

            $newStatus = match($status) {
                3       => 'completed',
                6       => 'cancelled',
                default => $order->status,
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

        if ($order->provider === 'smspool') {
            return $this->cancelSmsPool($order);
        }

        return $this->cancelLogsplug($order);
    }

    private function cancelLogsplug(VirtualNumberOrder $order)
    {
        $svc    = new LogsplugService();
        $result = ['success' => true];

        if ($order->external_order_id) {
            $result = $svc->cancelRental($order->external_order_id);
        }

        if ($result['success']) {
            $order->update(['status' => 'cancelled']);
            $refunded = $result['data']['data']['refunded'] ?? false;

            if ($order->cost > 0 && $refunded) {
                $this->processRefund($order);
            }

            return back()->with('success', $refunded
                ? 'Order cancelled and wallet refunded.'
                : 'Order cancelled. (No refund — SMS was already received.)');
        }

        return back()->with('error', 'Could not cancel: ' . $result['message']);
    }

    private function cancelSmsPool(VirtualNumberOrder $order)
    {
        $svc    = new SmsPoolService();
        $result = ['success' => true, 'data' => []];

        if ($order->external_order_id) {
            $result = $svc->cancelOrder($order->external_order_id);
        }

        if ($result['success']) {
            $order->update(['status' => 'cancelled']);
            // SMSPool refunds automatically on cancel
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
