<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{User, Listing, Purchase, SupportTicket, WalletTransaction};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'      => User::count(),
            'active_users'     => User::where('status', 'active')->count(),
            'banned_users'     => User::where('status', 'banned')->count(),
            'suspended_users'  => User::where('status', 'suspended')->count(),
            'total_listings'   => Listing::count(),
            'active_listings'  => Listing::where('is_active', true)->count(),
            'total_purchases'  => Purchase::count(),
            'total_revenue'    => Purchase::where('status', 'completed')->sum('amount'),
            'open_tickets'     => SupportTicket::where('status', 'open')->count(),
            'wallet_volume'    => WalletTransaction::where('type', 'credit')->sum('amount'),
            'recent_purchases' => Purchase::with(['user', 'listing'])->latest()->take(8)->get(),
            'recent_users'     => User::latest()->take(5)->get(),
        ];

        $start = Carbon::now()->subDays(29)->startOfDay();
        $rawRevenue = Purchase::where('status', 'completed')
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count")
            ->groupByRaw("DATE(created_at)")
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels  = [];
        $chartRevenue = [];
        $chartOrders  = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartLabels[]  = Carbon::parse($d)->format('M j');
            $chartRevenue[] = isset($rawRevenue[$d]) ? (float) $rawRevenue[$d]->total : 0;
            $chartOrders[]  = isset($rawRevenue[$d]) ? (int)   $rawRevenue[$d]->count : 0;
        }

        return view('admin.dashboard', compact('stats', 'chartLabels', 'chartRevenue', 'chartOrders'));
    }
}
