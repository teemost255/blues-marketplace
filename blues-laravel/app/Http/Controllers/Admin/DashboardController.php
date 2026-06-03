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
            'wallet_volume'    => WalletTransaction::whereIn('type', ['deposit', 'admin_credit', 'referral_bonus'])->sum('amount'),
            'recent_purchases' => Purchase::with(['user', 'listing'])->latest()->take(8)->get(),
            'recent_users'     => User::latest()->take(5)->get(),
            'new_users_today'  => User::whereDate('created_at', today())->count(),
            'new_users_week'   => User::where('created_at', '>=', now()->subDays(7))->count(),
            'revenue_today'    => Purchase::where('status', 'completed')->whereDate('created_at', today())->sum('amount'),
            'revenue_week'     => Purchase::where('status', 'completed')->where('created_at', '>=', now()->subDays(7))->sum('amount'),
            'qualified_referrals' => User::where('referral_bonus_paid', true)->count(),
            'pending_referrals'   => User::whereNotNull('referred_by')->where('referral_bonus_paid', false)->count(),
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

        return view('admin.dashboard', compact(
            'stats', 'chartLabels', 'chartRevenue', 'chartOrders'
        ));
    }
}
