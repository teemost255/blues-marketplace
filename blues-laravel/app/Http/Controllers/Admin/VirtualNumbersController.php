<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{VirtualNumberOrder, Setting, Wallet, WalletTransaction};
use App\Services\{HeroSmsService, PvaDealsSmsService};
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
            'pending'   => VirtualNumberOrder::where('status', 'pending')->count(),
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

        if ($order->provider === 'server3' && $order->status === 'pending') {
            return response()->json([
                'error' => 'This PVADeals purchase is unconfirmed. Check the provider request history before issuing a refund.',
            ], 422);
        }

        if ($order->provider === 'server3' && $order->status === 'received') {
            return response()->json([
                'error' => 'PVADeals has already delivered an SMS for this order. Review the provider request before refunding.',
            ], 422);
        }

        if ($order->provider === 'server3' && $order->status === 'waiting') {
            if (!$order->activation_id) {
                return response()->json(['error' => 'The provider order ID is missing; reconcile this order first.'], 422);
            }

            try {
                $providerRequest = (new PvaDealsSmsService())->getRequest((string) $order->activation_id);
                if (strtoupper((string) ($providerRequest['status'] ?? '')) !== 'FLAGGED'
                    && !(new PvaDealsSmsService())->flagNumber((string) $order->activation_id)) {
                    return response()->json(['error' => 'PVADeals did not confirm that this number can be flagged.'], 422);
                }
            } catch (\Throwable $e) {
                Log::warning('Admin PVADeals refund could not confirm provider cancellation.', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'error' => 'Provider cancellation was not confirmed. No refund was issued.',
                ], 422);
            }
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
            if ($order->provider !== 'server3') {
                try {
                    $sms = new HeroSmsService();
                    if ($sms->isConfigured() && $order->activation_id) {
                        $sms->setStatusCancel($order->activation_id);
                    }
                } catch (\Throwable) {}
                }

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
