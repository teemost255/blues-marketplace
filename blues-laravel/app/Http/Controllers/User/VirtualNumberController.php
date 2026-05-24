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
        $enabled    = Setting::get('virtual_number_enabled', '1') === '1';
        $configured = (new LogsplugService())->isConfigured();

        $countries = [];
        $services  = [];
        $apiError  = null;

        if ($enabled && $configured) {
            $svc = new LogsplugService();

            $selectedServer  = $request->get('server', 'server2');
            $selectedCountry = $request->get('country');

            if ($selectedServer === 'server2') {
                $cResult = $svc->getCountries('server2');
                if ($cResult['success']) {
                    $countries = $cResult['data']['data'] ?? [];
                } else {
                    $apiError = $cResult['message'];
                }

                if (!$selectedCountry && !empty($countries)) {
                    $selectedCountry = (string)($countries[0]['id'] ?? '');
                }

                if ($selectedCountry) {
                    $sResult = $svc->getServices('server2', $selectedCountry);
                    if ($sResult['success']) {
                        $services = $sResult['data']['data'] ?? [];
                    } elseif (!$apiError) {
                        $apiError = $sResult['message'];
                    }
                }
            } else {
                $sResult = $svc->getServices('server1');
                if ($sResult['success']) {
                    $services = $sResult['data']['data'] ?? [];
                } else {
                    $apiError = $sResult['message'];
                }
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
            'server'     => 'required|string|in:server1,server2',
            'service_id' => 'required|string',
            'country'    => 'nullable|string',
            'price'      => 'nullable|numeric|min:0',
            'service_name' => 'nullable|string',
        ]);

        $enabled = Setting::get('virtual_number_enabled', '1') === '1';
        $svc     = new LogsplugService();

        if (!$enabled || !$svc->isConfigured()) {
            return back()->with('error', 'Virtual numbers are currently unavailable.');
        }

        $wallet = Wallet::firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);
        $cost   = (float)($request->price ?? 0);

        if ($cost > 0 && $wallet->balance < $cost) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        $rentResult = $svc->rentNumber(
            $request->server,
            $request->service_id,
            $request->country ?? ''
        );

        if (!$rentResult['success']) {
            return back()->with('error', 'Could not get a number: ' . $rentResult['message']);
        }

        $data        = $rentResult['data']['data'] ?? $rentResult['data'];
        $serviceName = $request->service_name ?? $request->service_id;

        DB::transaction(function () use ($data, $request, $cost, $wallet, $serviceName) {
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
                WalletTransaction::create([
                    'user_id'     => auth()->id(),
                    'type'        => 'debit',
                    'amount'      => $cost,
                    'description' => 'Virtual number: ' . $serviceName,
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
        $result = $svc->getOtp($order->external_order_id);

        if ($result['success']) {
            $data   = $result['data']['data'] ?? $result['data'];
            $codes  = $data['code'] ?? [];
            $sms    = is_array($codes) ? implode(', ', $codes) : (string)$codes;
            $status = strtolower((string)($data['status'] ?? ''));

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
                        'type'        => 'credit',
                        'amount'      => $order->cost,
                        'description' => 'Refund: cancelled virtual number #' . $order->id,
                        'status'      => 'completed',
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
