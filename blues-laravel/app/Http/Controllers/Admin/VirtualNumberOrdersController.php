<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VirtualNumberOrder;
use App\Services\LogsplugService;
use App\Services\HeroSmsService;
use App\Services\FiveSimService;
use Illuminate\Http\Request;

class VirtualNumberOrdersController extends Controller
{
    public function index(Request $request)
    {
        $query = VirtualNumberOrder::with('user');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function($q) use ($term) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term))
                ->orWhere('phone_number', 'like', $term)
                ->orWhere('service', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service')) {
            $query->where('service', 'like', '%' . $request->service . '%');
        }

        $orders = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'     => VirtualNumberOrder::count(),
            'active'    => VirtualNumberOrder::where('status', 'active')->count(),
            'completed' => VirtualNumberOrder::where('status', 'completed')->count(),
            'cancelled' => VirtualNumberOrder::where('status', 'cancelled')->count(),
            'revenue'   => VirtualNumberOrder::where('status', '!=', 'cancelled')->sum('cost'),
        ];

        return view('admin.virtual-numbers', compact('orders', 'stats'));
    }

    public function updateStatus(Request $request, VirtualNumberOrder $order)
    {
        $request->validate(['status' => 'required|in:pending,active,completed,cancelled,failed']);
        $order->update(['status' => $request->status]);
        return back()->with('success', 'Order status updated.');
    }

    public function destroy(VirtualNumberOrder $order)
    {
        $order->delete();
        return back()->with('success', 'Order deleted.');
    }

    public function logsplugBalance()
    {
        $svc = new LogsplugService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'API not configured. Add your Logsplug key in Settings.']);
        }
        $result = $svc->getBalance();
        if ($result['success']) {
            $balance = $result['data']['data']['balance'] ?? ($result['data']['balance'] ?? null);
            return response()->json(['success' => true, 'balance' => $balance]);
        }
        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Could not fetch balance.']);
    }

    public function heroSmsBalance()
    {
        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Hero-SMS API not configured. Add your key in Settings.']);
        }
        $result = $svc->getBalance();
        if ($result['success']) {
            $balance = $result['data']['balance'] ?? null;
            return response()->json(['success' => true, 'balance' => $balance]);
        }
        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Could not fetch balance.']);
    }

    public function fiveSimBalance()
    {
        $svc = new FiveSimService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => '5SIM API not configured. Add your key in Settings.']);
        }
        $result = $svc->getBalance();
        if ($result['success']) {
            $balance = $result['data']['balance_usd'] ?? null;
            return response()->json(['success' => true, 'balance' => $balance]);
        }
        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Could not fetch balance.']);
    }

    public function exportCsv(Request $request)
    {
        $query = VirtualNumberOrder::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="vn-orders-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User', 'Email', 'Service', 'Country', 'Phone Number', 'Cost (NGN)', 'Status', 'SMS Code', 'Date']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->user?->name ?? '—',
                    $order->user?->email ?? '—',
                    $order->service,
                    $order->country,
                    $order->phone_number,
                    number_format($order->cost, 2),
                    $order->status,
                    $order->sms_code ?? '',
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
