<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Listing, Purchase, SupportTicket, WalletTransaction, VirtualNumberOrder};
use App\Services\LogsplugService;
use App\Services\HeroSmsService;
use App\Services\FiveSimService;
use App\Services\GrizzlySmsService;
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
            'vn_total'         => VirtualNumberOrder::count(),
            'vn_active'        => VirtualNumberOrder::where('status', 'active')->count(),
            'vn_revenue'       => VirtualNumberOrder::where('status', '!=', 'cancelled')->sum('cost'),
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

        // Fetch Logsplug balance server-side
        $logsplugBalance = null;
        $logsplugError   = null;
        $logsplugSvc = new LogsplugService();
        if ($logsplugSvc->isConfigured()) {
            try {
                $result = $logsplugSvc->getBalance();
                if ($result['success']) {
                    $logsplugBalance = $result['data']['data']['balance'] ?? ($result['data']['balance'] ?? null);
                } else {
                    $logsplugError = $result['message'] ?? 'Could not fetch balance.';
                }
            } catch (\Throwable $e) {
                $logsplugError = 'Balance fetch failed. Check API connectivity.';
            }
        } else {
            $logsplugError = 'API not configured. Add your Logsplug key in Settings.';
        }

        // Fetch Hero-SMS balance server-side
        $heroSmsBalance = null;
        $heroSmsError   = null;
        $heroSmsSvc = new HeroSmsService();
        if ($heroSmsSvc->isConfigured()) {
            try {
                $result = $heroSmsSvc->getBalance();
                if ($result['success']) {
                    $heroSmsBalance = $result['data']['balance'] ?? null;
                } else {
                    $heroSmsError = $result['message'] ?? 'Could not fetch balance.';
                }
            } catch (\Throwable $e) {
                $heroSmsError = 'Balance fetch failed. Check API connectivity.';
            }
        } else {
            $heroSmsError = 'API not configured. Add your Hero-SMS key in Settings.';
        }

        // Fetch 5SIM balance server-side
        $fiveSimBalance = null;
        $fiveSimError   = null;
        $fiveSimSvc = new FiveSimService();
        if ($fiveSimSvc->isConfigured()) {
            try {
                $result = $fiveSimSvc->getBalance();
                if ($result['success']) {
                    $fiveSimBalance = $result['data']['balance_usd'] ?? null;
                } else {
                    $fiveSimError = $result['message'] ?? 'Could not fetch balance.';
                }
            } catch (\Throwable $e) {
                $fiveSimError = 'Balance fetch failed. Check API connectivity.';
            }
        } else {
            $fiveSimError = 'API not configured. Add your 5SIM key in Settings.';
        }

        // Fetch GrizzlySMS balance server-side
        $grizzlyBalance = null;
        $grizzlyError   = null;
        $grizzlySvc = new GrizzlySmsService();
        if ($grizzlySvc->isConfigured()) {
            try {
                $result = $grizzlySvc->getBalance();
                if ($result['success']) {
                    $grizzlyBalance = $result['data']['balance_usd'] ?? null;
                } else {
                    $grizzlyError = $result['message'] ?? 'Could not fetch balance.';
                }
            } catch (\Throwable $e) {
                $grizzlyError = 'Balance fetch failed. Check API connectivity.';
            }
        } else {
            $grizzlyError = 'API not configured. Add your GrizzlySMS key in Settings.';
        }

        return view('admin.dashboard', compact(
            'stats', 'chartLabels', 'chartRevenue', 'chartOrders',
            'logsplugBalance', 'logsplugError',
            'heroSmsBalance', 'heroSmsError',
            'fiveSimBalance', 'fiveSimError',
            'grizzlyBalance', 'grizzlyError'
        ));
    }
}
