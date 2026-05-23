<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Wallet, WalletTransaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user         = Auth::user();
        $wallet       = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $transactions = WalletTransaction::where('user_id', $user->id)->latest()->paginate(20);
        return view('dashboard.wallet', compact('wallet', 'transactions'));
    }

    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1|max:10000']);

        $user   = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $wallet->increment('balance', $request->amount);

        WalletTransaction::create([
            'user_id'     => $user->id,
            'amount'      => $request->amount,
            'type'        => 'deposit',
            'reference'   => 'DEP-'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'description' => 'Wallet top-up',
        ]);

        return back()->with('success', '$'.number_format($request->amount, 2).' added to your wallet.');
    }
}
