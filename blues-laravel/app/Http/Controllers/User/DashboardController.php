<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Purchase, Notification, Wallet};
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user          = Auth::user();
        $wallet        = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $recentOrders  = Purchase::with('listing')->where('user_id', $user->id)->latest()->limit(5)->get();
        $unreadCount   = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $totalSpent    = Purchase::where('user_id', $user->id)->where('status', 'completed')->sum('amount');
        $orderCount    = Purchase::where('user_id', $user->id)->count();

        return view('dashboard.index', compact('wallet', 'recentOrders', 'unreadCount', 'totalSpent', 'orderCount'));
    }
}
