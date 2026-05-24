<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LogsplugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VirtualNumberController extends Controller
{
    public function index()
    {
        $enabled    = Setting::get('virtual_number_enabled', '1') === '1';
        $configured = (new LogsplugService())->isConfigured();
        $orders     = \App\Models\VirtualNumberOrder::where('user_id', auth()->id())->latest()->paginate(10);
        $wallet     = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

        $commissionType  = Setting::get('vn_commission_type', 'flat');
        $commissionValue = (float) Setting::get('vn_commission_value', '0');

        return view('dashboard.virtual-numbers', compact('enabled', 'configured', 'orders', 'wallet', 'commissionType', 'commissionValue'));
    }

    public function getCountries(Request $request)
    {
        $server = $request->get('server', 'server2');
        $svc    = new LogsplugService();

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

    public function getServices(Request $request)
    {
        $server  = $request->get('server', 'server2');
        $country = $request->get('country');
        $svc     = new LogsplugService();

        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'API not configured.']);
        }

        $result = $svc->getServices($server, $country ?: null);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data'    => $result['data']['data'] ?? [],
            ]);
        }

        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    public function order(Request $request)
    {
        $request->validate([
            'server'       => 'required|string|in:server1,server2',
            'service_id'   => 'required|string',
            'country'      => 'nullable|string',
            'price'        => 'nullable|numeric|min:0',
            'service_name' => 'nullable|string',
        ]);

        $enabled = Setting::get('virtual_number_enabled', '1') === '1';
        $svc     = new LogsplugService();

        if (!$enabled || !$svc->isConfigured()) {
            return back()->with('error', 'Virtual numbers are currently unavailable.');
        }

        $wallet = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);
        $apiCost        = (float)($request->price ?? 0);
        $commType       = Setting::get('vn_commission_type', 'flat');
        $commValue      = (float) Setting::get('vn_commission_value', '0');
        $commission     = $commType === 'percent' ? round($apiCost * $commValue / 100, 2) : $commValue;
        $cost           = round($apiCost + $commission, 2);

        if ($cost > 0 && $wallet->balance < $cost) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        $rentResult = $svc->rentNumber($request->input('server'), $request->service_id, $request->country ?? '');

        if (!$rentResult['success']) {
            return back()->with('error', 'Could not get a number: ' . $rentResult['message']);
        }

        $data        = $rentResult['data']['data'] ?? $rentResult['data'];
        $serviceName = $request->service_name ?? $request->service_id;

        DB::transaction(function () use ($data, $request, $apiCost, $commission, $cost, $wallet, $serviceName) {
            $order = \App\Models\VirtualNumberOrder::create([
                'user_id'           => auth()->id(),
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
                $desc = 'Virtual number: ' . $serviceName;
                if ($commission > 0) {
                    $desc .= ' (API: ₦' . number_format($apiCost, 2) . ' + fee: ₦' . number_format($commission, 2) . ')';
                }
                WalletTransaction::create([
                    'user_id'     => auth()->id(),
                    'type'        => 'withdrawal',
                    'amount'      => $cost,
                    'description' => $desc,
                    'reference'   => 'VN-' . $order->id . '-' . time(),
                ]);
            }
        });

        return back()->with('success', 'Virtual number ordered successfully! Check your active orders below.');
    }

    public function checkSms(string $orderId)
    {
        $order = \App\Models\VirtualNumberOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$order->external_order_id) {
            return response()->json(['success' => false, 'message' => 'No external order ID.']);
        }

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

    public function cancel(string $orderId)
    {
        $order = \App\Models\VirtualNumberOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        $svc    = new LogsplugService();
        $result = ['success' => true];

        if ($order->external_order_id) {
            $result = $svc->cancelRental($order->external_order_id);
        }

        if ($result['success']) {
            $order->update(['status' => 'cancelled']);
            $refunded = $result['data']['data']['refunded'] ?? false;

            if ($order->cost > 0 && $refunded) {
                $wallet = Wallet::where('user_id', auth()->id())->first();
                if ($wallet) {
                    $wallet->increment('balance', $order->cost);
                    WalletTransaction::create([
                        'user_id'     => auth()->id(),
                        'type'        => 'refund',
                        'amount'      => $order->cost,
                        'description' => 'Refund: cancelled virtual number #' . $order->id,
                        'reference'   => 'REFUND-VN-' . $order->id . '-' . time(),
                    ]);
                }
            }

            return back()->with('success', $refunded
                ? 'Order cancelled and wallet refunded.'
                : 'Order cancelled. (No refund — SMS was already received.)');
        }

        return back()->with('error', 'Could not cancel: ' . $result['message']);
    }
}
