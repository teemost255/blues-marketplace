<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\SameehaSocialHubService;
use Illuminate\Http\Request;

class ApiOrdersController extends Controller
{
    public function index(Request $request)
    {
        $provider = app(SameehaSocialHubService::class);
        $configured = $provider->isConfigured();
        $providerBalance = $configured ? $provider->getBalance() : null;

        $purchases = Purchase::where('source', 'api')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $orders = $purchases->getCollection()->map(function (Purchase $purchase) {
            $delivery = $this->deliveryData($purchase);

            return [
                'id' => $purchase->id,
                'product_name' => $purchase->api_product_name ?? $delivery['product'] ?? 'Catalog Product',
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
