<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VirtualNumberOrder;
use Illuminate\Http\Request;

class VirtualNumberOrdersController extends Controller
{
    public function index(Request $request)
    {
        $query = VirtualNumberOrder::with('user');

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'ilike', '%'.$request->search.'%')
                ->orWhere('email', 'ilike', '%'.$request->search.'%'))
            ->orWhere('phone_number', 'ilike', '%'.$request->search.'%')
            ->orWhere('service', 'ilike', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service')) {
            $query->where('service', 'ilike', '%'.$request->service.'%');
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
}
