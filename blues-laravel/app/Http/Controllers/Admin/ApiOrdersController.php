<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Notification, Purchase, Wallet, WalletTransaction};
use App\Services\SameehaSocialHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiOrdersController extends Controller
{
    public function index(Request $request)
    {
        $provider = app(SameehaSocialHubService::class);
        $configured = $provider->isConfigured();
        $providerBalance = $configured ? $provider->getBalance() : null;

        $purchases = Purchase::with('user')->where('source', 'api')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $orders = $purchases->getCollection()->map(function (Purchase $purchase) {
            $delivery = $this->deliveryData($purchase);

            return [
                'id' => $purchase->id,
                'product_name' => $purchase->api_product_name ?? $delivery['product'] ?? 'Catalog Product',
                'buyer_email' => $purchase->user?->email ?? '—',
                'source' => 'sameeha',
                'quantity' => $delivery['quantity'] ?? 1,
                'unit_price' => (float) $purchase->amount,
                'total_price' => (float) $purchase->amount,
                'status' => $purchase->status,
                'created_at' => $purchase->created_at,
            ];
        })->values()->all();

        $accounts = $purchases->getCollection()->map(function (Purchase $purchase) {
            $delivery = $this->deliveryData($purchase);

            return [
                'id' => $purchase->id,
                'product_label' => $purchase->api_product_name ?? $delivery['product'] ?? 'Catalog Product',
                'buyer_email' => $purchase->user?->email ?? '—',
                'order_id' => $delivery['provider_order_id'] ?? $delivery['order_id'] ?? $purchase->id,
                'status' => $purchase->status,
                'is_sold' => $purchase->status === 'completed' && !empty($delivery['credentials']),
                'order_total' => (float) $purchase->amount,
                'created_at' => $purchase->created_at,
                'credentials' => $delivery['credentials'] ?? '',
            ];
        })->values()->all();

        $pagination = [
            'total' => $purchases->total(),
            'current_page' => $purchases->currentPage(),
            'last_page' => $purchases->lastPage(),
            'has_more' => $purchases->hasMorePages(),
        ];
        $orders = ['data' => $orders, 'pagination' => $pagination];
        $accounts = ['data' => $accounts, 'pagination' => $pagination];
        $stats = null;
        $page = $purchases->currentPage();
        $perPage = $purchases->perPage();

        return view('admin.api-orders', compact(
            'stats', 'orders', 'accounts', 'configured', 'providerBalance', 'page', 'perPage'
        ));
    }

    public function resolve(Request $request, int $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:deliver,refund',
            'credentials' => 'required_if:action,deliver|nullable|string|max:10000',
        ]);

        if ($validated['action'] === 'deliver' && trim((string) ($validated['credentials'] ?? '')) === '') {
            return back()->withErrors(['credentials' => 'Enter the credentials recovered from the supplier.'])->withInput();
        }

        $resolved = DB::transaction(function () use ($validated, $id): bool {
            $purchase = Purchase::where('source', 'api')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($purchase->status !== 'pending') {
                return false;
            }

            $delivery = $this->deliveryData($purchase);
            $delivery['resolved_at'] = now()->toIso8601String();

            if ($validated['action'] === 'deliver') {
                $delivery['fulfillment_state'] = 'delivered';
                $delivery['manual_resolution'] = 'credentials_recovered_from_supplier';
                $delivery['credentials'] = trim($validated['credentials']);

                $purchase->update([
                    'status' => 'completed',
                    'delivery_data' => json_encode($delivery),
                ]);

                Notification::create([
                    'user_id' => $purchase->user_id,
                    'title' => 'Purchase Credentials Ready',
                    'message' => 'Credentials for "' . ($purchase->api_product_name ?? 'your API order') . '" are now available in My Orders.',
                    'type' => 'success',
                ]);

                return true;
            }

            $wallet = Wallet::where('user_id', $purchase->user_id)->lockForUpdate()->first()
                ?? Wallet::create(['user_id' => $purchase->user_id, 'balance' => 0]);
            $wallet->increment('balance', $purchase->amount);

            WalletTransaction::create([
                'user_id' => $purchase->user_id,
                'amount' => $purchase->amount,
                'type' => 'refund',
                'reference' => 'REFUND-API-' . $purchase->id . '-' . Str::uuid(),
                'description' => 'Admin refund: ' . ($purchase->api_product_name ?? 'API order'),
            ]);

            $delivery['fulfillment_state'] = 'refunded';
            $delivery['manual_resolution'] = 'refunded_after_supplier_check';
            $purchase->update([
                'status' => 'refunded',
                'delivery_data' => json_encode($delivery),
            ]);

            Notification::create([
                'user_id' => $purchase->user_id,
                'title' => 'API Order Refunded',
                'message' => 'Your wallet has been refunded for "' . ($purchase->api_product_name ?? 'your API order') . '".',
                'type' => 'warning',
            ]);

            return true;
        });

        if (!$resolved) {
            return back()->with('error', 'This order was already resolved and cannot be changed again.');
        }

        return back()->with(
            'success',
            $validated['action'] === 'deliver'
                ? 'Credentials saved and made available to the buyer.'
                : 'The pending order was refunded to the buyer’s wallet.'
        );
    }

    public function accountDetails(int $id)
    {
        $purchase = Purchase::where('source', 'api')->find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Account not found.'], 404);
        }

        $delivery = $this->deliveryData($purchase);
        return response()->json([
            'id' => $purchase->id,
            'credentials' => $delivery['credentials'] ?? '',
        ]);
    }

    public function orderAccounts(int $orderId)
    {
        $purchase = Purchase::where('source', 'api')->find($orderId);
        if (!$purchase) {
            return response()->json([]);
        }

        $delivery = $this->deliveryData($purchase);
        return response()->json([[
            'id' => $purchase->id,
            'credentials' => $delivery['credentials'] ?? '',
        ]]);
    }

    private function deliveryData(Purchase $purchase): array
    {
        $decoded = json_decode((string) $purchase->delivery_data, true);
        return is_array($decoded) ? $decoded : [];
    }
}
