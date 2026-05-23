<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('wallet');
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('email', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }
        if ($request->status) $query->where('status', $request->status);
        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => 'active',
        ]);
        Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        return back()->with('success', "User {$user->name} created successfully.");
    }

    public function updateStatus(Request $request, User $user)
    {
        $status = $request->status;
        if (!in_array($status, ['active', 'suspended', 'banned'])) {
            return back()->with('error', 'Invalid status.');
        }
        $user->update(['status' => $status]);
        $labels = ['active' => 'activated', 'suspended' => 'suspended', 'banned' => 'banned'];
        return back()->with('success', "User {$user->name} has been {$labels[$status]}.");
    }

    public function walletAdjust(Request $request, User $user)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'type'        => 'required|in:fund,deduct',
            'description' => 'nullable|string|max:255',
        ]);

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $amount = (float) $request->amount;

        if ($request->type === 'deduct' && $wallet->balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance to deduct.');
        }

        if ($request->type === 'fund') {
            $wallet->increment('balance', $amount);
        } else {
            $wallet->decrement('balance', $amount);
        }

        WalletTransaction::create([
            'user_id'     => $user->id,
            'amount'      => $request->type === 'fund' ? $amount : -$amount,
            'type'        => $request->type === 'fund' ? 'credit' : 'debit',
            'reference'   => 'ADMIN-' . strtoupper(uniqid()),
            'description' => $request->description ?: ($request->type === 'fund' ? 'Admin wallet funding' : 'Admin wallet deduction'),
        ]);

        $action = $request->type === 'fund' ? 'funded' : 'deducted from';
        return back()->with('success', "₦" . number_format($amount, 2) . " {$action} {$user->name}'s wallet.");
    }

    public function impersonate(User $user)
    {
        session(['impersonate_user_id' => $user->id, 'impersonate_user_name' => $user->name]);
        return redirect()->route('admin.impersonate.dashboard', $user);
    }

    public function impersonateDashboard(User $user)
    {
        $wallet    = $user->wallet;
        $orders    = $user->purchases()->with('listing')->latest()->take(10)->get();
        $tickets   = $user->tickets()->latest()->take(5)->get();
        $wishlist  = $user->wishlists()->with('listing')->latest()->take(10)->get();
        $notifs    = $user->notifications()->latest()->take(10)->get();
        return view('admin.user-dashboard', compact('user', 'wallet', 'orders', 'tickets', 'wishlist', 'notifs'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
