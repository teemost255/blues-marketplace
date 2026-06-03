<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{VirtualNumberOrder, Setting};
use App\Services\HeroSmsService;
use Illuminate\Http\Request;

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
}
