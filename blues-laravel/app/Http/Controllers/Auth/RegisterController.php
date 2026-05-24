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

    private function getMilestoneBonusAmount(int $currentReferralCount): float
    {
        $tier3Threshold = (int) \App\Models\Setting::get('referral_bonus_tier3_threshold', '16');
        $tier2Threshold = (int) \App\Models\Setting::get('referral_bonus_tier2_threshold', '6');

        $nextCount = $currentReferralCount + 1;

        if ($nextCount >= $tier3Threshold) {
            return (float) \App\Models\Setting::get('referral_bonus_tier3', '0');
        }
        if ($nextCount >= $tier2Threshold) {
            return (float) \App\Models\Setting::get('referral_bonus_tier2', '0');
        }
        return (float) \App\Models\Setting::get('referral_bonus', '0');
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
        ]);

        Profile::create([
            'user_id'       => $user->id,
            'display_name'  => $request->name,
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        Wallet::create(['user_id' => $user->id, 'balance' => 0]);

        if ($referrerId) {
            $currentCount = User::where('referred_by', $referrerId)->where('id', '!=', $user->id)->count();
            $bonusAmount  = $this->getMilestoneBonusAmount($currentCount);

            if ($bonusAmount > 0) {
                $referrerWallet = \App\Models\Wallet::firstOrCreate(
                    ['user_id' => $referrerId],
                    ['balance' => 0]
                );
                $referrerWallet->increment('balance', $bonusAmount);

                $tier = $this->getTierLabel($currentCount + 1);

                \App\Models\WalletTransaction::create([
                    'user_id'     => $referrerId,
                    'amount'      => $bonusAmount,
                    'type'        => 'referral_bonus',
                    'reference'   => 'REF-' . $user->id,
                    'description' => 'Referral bonus (' . $tier . '): ' . $request->name . ' joined using your link',
                    'status'      => 'completed',
                ]);

                \App\Models\Notification::create([
                    'user_id' => $referrerId,
                    'title'   => 'Referral Bonus Earned! ' . $tier,
                    'message' => $request->name . ' joined using your referral link. ₦' . number_format($bonusAmount, 2) . ' has been added to your wallet.',
                    'type'    => 'success',
                ]);
            }
        }

        session()->forget('referral_code');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')->with('success',
            $referrerId ? 'Welcome! You joined via a referral link.' : 'Welcome to BluesMarketplace!'
        );
    }

    private function getTierLabel(int $count): string
    {
        $tier3Threshold = (int) \App\Models\Setting::get('referral_bonus_tier3_threshold', '16');
        $tier2Threshold = (int) \App\Models\Setting::get('referral_bonus_tier2_threshold', '6');

        if ($count >= $tier3Threshold) return 'Gold';
        if ($count >= $tier2Threshold) return 'Silver';
        return 'Bronze';
    }
}
