<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Models\Profile;

class ReferralPageController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);

        if (!$profile->referral_code) {
            $profile->update(['referral_code' => strtoupper(substr(md5($user->id . $user->email), 0, 8))]);
        }

        $referralCount = \App\Models\User::where('referred_by', $user->id)->count();

        $earnings = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'referral_bonus')
            ->latest()
            ->get();

        $totalEarned = $earnings->sum('amount');

        $bonusRate = (float) Setting::get('referral_bonus', 0);

        $referrals = \App\Models\User::where('referred_by', $user->id)
            ->with('profile')
            ->latest()
            ->get();

        return view('dashboard.referrals', compact(
            'user', 'profile', 'referralCount', 'earnings', 'totalEarned', 'bonusRate', 'referrals'
        ));
    }
}
