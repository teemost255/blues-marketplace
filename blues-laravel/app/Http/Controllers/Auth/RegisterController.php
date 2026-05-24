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
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $referrerId = null;
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
        ]);

        Profile::create([
            'user_id'       => $user->id,
            'display_name'  => $request->name,
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 0]);

        $bonusAmount = (float) \App\Models\Setting::get('referral_bonus', '0');

        if ($referrerId && $bonusAmount > 0) {
            $referrerWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $referrerId],
                ['balance' => 0]
            );
            $referrerWallet->increment('balance', $bonusAmount);

            \App\Models\WalletTransaction::create([
                'user_id'     => $referrerId,
                'amount'      => $bonusAmount,
                'type'        => 'referral_bonus',
                'reference'   => 'REF-' . $user->id,
                'description' => 'Referral bonus: ' . $request->name . ' joined using your link',
            ]);

            \App\Models\Notification::create([
                'user_id' => $referrerId,
                'title'   => 'Referral Bonus Earned!',
                'message' => $request->name . ' joined using your referral link. $' . number_format($bonusAmount, 2) . ' has been added to your wallet.',
                'type'    => 'success',
            ]);
        }

        session()->forget('referral_code');

        Auth::login($user);
        $request->session()->regenerate();

        $welcomeMsg = 'Welcome to BluesMarketplace!';
        if ($referrerId) {
            $welcomeMsg = 'Welcome! You joined via a referral link.';
        }

        return redirect()->route('dashboard.index')->with('success', $welcomeMsg);
    }
}
