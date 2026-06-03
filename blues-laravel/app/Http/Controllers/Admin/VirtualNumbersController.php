<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{VirtualNumberOrder, Setting, Wallet, WalletTransaction};
use App\Services\HeroSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VirtualNumbersController extends Controller
{
    public function index(Request $request)
    {
        $query = VirtualNumberOrder::with('user')->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('email', 'like', '%' . $request->search . '%')
                ->orWhere('name', 'like', '%' . $request->search . '%'));
        }

        $orders = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => VirtualNumberOrder::count(),
            'waiting'   => VirtualNumberOrder::where('status', 'waiting')->count(),
            'completed' => VirtualNumberOrder::where('status', 'completed')->count(),
            'revenue'   => VirtualNumberOrder::whereIn('status', ['waiting', 'received', 'completed'])->sum('cost'),
        ];

        $sms     = new HeroSmsService();
        $balance = $sms->isConfigured() ? $sms->getBalance() : null;

        return view('admin.virtual-numbers', compact('orders', 'stats', 'balance'));
    }

    /**
     * Refund the order cost to the user's wallet and mark as cancelled.
     */
    public function refund(VirtualNumberOrder $order)
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json(['error' => 'This order cannot be refunded (status: ' . $order->status . ').'], 422);
        }

        try {
            DB::transaction(function () use ($order) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $order->user_id],
                    ['balance' => 0]
                );
                $wallet->increment('balance', round((float) $order->cost, 2));

                WalletTransaction::create([
                    'user_id'     => $order->user_id,
                    'amount'      => round((float) $order->cost, 2),
                    'type'        => 'refund',
                    'reference'   => 'REFUND-VN-' . $order->id,
                    'description' => 'Admin refund: Virtual number ' . $order->service_name,
                ]);

                $order->update(['status' => 'cancelled']);
            });

            // Cancel at provider (best-effort)
            try {
                $sms = new HeroSmsService();
                if ($sms->isConfigured() && $order->activation_id) {
                    $sms->setStatusCancel($order->activation_id);
                }
            } catch (\Throwable) {}

            Log::info('Admin refunded VN order', ['order_id' => $order->id, 'amount' => $order->cost]);
            return response()->json(['success' => true, 'message' => '₦' . number_format($order->cost, 2) . ' refunded to user wallet.']);
        } catch (\Throwable $e) {
            Log::error('Admin VN refund failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Refund failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Manually update an order's status (complete / cancel / reset to waiting).
     */
    public function updateStatus(VirtualNumberOrder $order, Request $request)
    {
        $request->validate(['status' => 'required|in:waiting,received,completed,cancelled,expired']);
        $order->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Order status updated to ' . $request->status . '.']);
    }
}
