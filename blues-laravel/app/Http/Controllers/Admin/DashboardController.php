<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{User, Listing, Purchase, SupportTicket, WalletTransaction};

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'    => User::count(),
            'total_listings' => Listing::count(),
            'total_purchases'=> Purchase::count(),
            'open_tickets'   => SupportTicket::where('status', 'open')->count(),
            'total_revenue'  => Purchase::where('status', 'completed')->sum('amount'),
            'recent_purchases' => Purchase::with(['user', 'listing'])->latest()->limit(10)->get(),
        ];
        return view('admin.dashboard', compact('stats'));
    }
}
