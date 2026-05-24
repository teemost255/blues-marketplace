<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Purchase::with(['listing', 'review'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);
        return view('dashboard.orders', compact('orders'));
    }
}
