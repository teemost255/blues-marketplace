<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Profile, Wallet};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $referrerId   = null;
        $referralCode = session('referral_code');
        if ($referralCode) {
            $referrerProfile = \App\Models\Profile::where('referral_code', $referralCode)->first();
            if ($referrerProfile) {
                $referrerId = $referrerProfile->user_id;
            }
        }

        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'email_notifications' => true,
            'referred_by'         => $referrerId,
            'referral_deposited'  => false,
            'referral_purchased'  => false,
            'referral_bonus_paid' => false,
            'last_login_at'       => now(),
            'last_login_ip'       => $request->ip(),
        ]);

        Profile::create([
            'user_id'       => $user->id,
            'display_name'  => $request->name,
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        Wallet::create(['user_id' => $user->id, 'balance' => 0]);

        session()->forget('referral_code');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')->with('success',
            $referrerId
                ? 'Welcome! You joined via a referral link. Fund your wallet and make a purchase to activate your referrer\'s bonus.'
                : 'Welcome to BluesMarketplace!'
        );
    }
}
