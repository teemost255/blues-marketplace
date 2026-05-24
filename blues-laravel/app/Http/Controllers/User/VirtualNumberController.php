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
    public function index(Request $request)
    {
        $enabled = Setting::get('virtual_number_enabled', '1') === '1';
        $configured = (new LogsplugService())->isConfigured();

        $countries = [];
        $services  = [];
        $apiError  = null;

        if ($enabled && $configured) {
            $svc = new LogsplugService();

            $cResult = $svc->getCountries();
            if ($cResult['success']) {
                $countries = $cResult['data']['data'] ?? $cResult['data'] ?? [];
            } else {
                $apiError = $cResult['message'];
            }

            $selectedCountry = $request->get('country', 'ng');
            $sResult = $svc->getServices($selectedCountry);
            if ($sResult['success']) {
                $services = $sResult['data']['data'] ?? $sResult['data'] ?? [];
            }
        }

        $orders = \App\Models\VirtualNumberOrder::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        $wallet = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

        return view('dashboard.virtual-numbers', compact(
            'enabled', 'configured', 'countries', 'services', 'orders', 'wallet', 'apiError'
        ));
    }

    public function order(Request $request)
    {
        $request->validate([
            'service' => 'required|string',
            'country' => 'required|string',
        ]);

        $enabled    = Setting::get('virtual_number_enabled', '1') === '1';
        $svc        = new LogsplugService();

        if (!$enabled || !$svc->isConfigured()) {
            return back()->with('error', 'Virtual numbers are currently unavailable.');
        }

        $wallet = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

        $priceResult = $svc->getServicePrice($request->service, $request->country);
        $cost = 0;
        if ($priceResult['success']) {
            $cost = (float)($priceResult['data']['price'] ?? $priceResult['data']['cost'] ?? 0);
        }

        if ($wallet->balance < $cost && $cost > 0) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        $orderResult = $svc->orderNumber($request->service, $request->country);

        if (!$orderResult['success']) {
            return back()->with('error', 'Could not get a number: ' . $orderResult['message']);
        }

        $data = $orderResult['data'];

        DB::transaction(function () use ($data, $request, $cost, $wallet) {
            $order = \App\Models\VirtualNumberOrder::create([
                'user_id'           => auth()->id(),
                'external_order_id' => $data['id'] ?? $data['order_id'] ?? null,
                'service'           => $request->service,
                'country'           => $request->country,
                'phone_number'      => $data['number'] ?? $data['phone'] ?? null,
                'cost'              => $cost,
                'status'            => 'active',
                'raw_response'      => json_encode($data),
            ]);

            if ($cost > 0) {
                $wallet->decrement('balance', $cost);
                WalletTransaction::create([
                    'user_id'     => auth()->id(),
                    'type'        => 'debit',
                    'amount'      => $cost,
                    'description' => 'Virtual number: ' . strtoupper($request->service) . ' (' . strtoupper($request->country) . ')',
                    'status'      => 'completed',
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
        $result = $svc->getOrderStatus($order->external_order_id);

        if ($result['success']) {
            $data = $result['data'];
            $sms  = $data['sms'] ?? $data['code'] ?? $data['otp'] ?? null;
            $status = $data['status'] ?? null;

            $newStatus = match(strtolower((string)$status)) {
                'completed', 'success', 'received' => 'completed',
                'cancelled', 'canceled'             => 'cancelled',
                default                             => $order->status,
            };

            $order->update([
                'sms_code' => $sms ?? $order->sms_code,
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
            $result = $svc->cancelOrder($order->external_order_id);
        }

        if ($result['success']) {
            $order->update(['status' => 'cancelled']);

            if ($order->cost > 0) {
                $wallet = Wallet::where('user_id', auth()->id())->first();
                if ($wallet) {
                    $wallet->increment('balance', $order->cost);
                    WalletTransaction::create([
                        'user_id'     => auth()->id(),
                        'type'        => 'credit',
                        'amount'      => $order->cost,
                        'description' => 'Refund: cancelled virtual number #' . $order->id,
                        'status'      => 'completed',
                        'reference'   => 'REFUND-VN-' . $order->id . '-' . time(),
                    ]);
                }
            }

            return back()->with('success', 'Order cancelled and wallet refunded.');
        }

        return back()->with('error', 'Could not cancel: ' . $result['message']);
    }
}
