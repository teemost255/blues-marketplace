<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{User, Listing, Purchase, SupportTicket, WalletTransaction};

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
        return view('admin.dashboard', compact('stats'));
    }
}
