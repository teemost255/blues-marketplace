<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Models\Profile;
use App\Models\User;

class ReferralPageController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);

        if (!$profile->referral_code) {
            $profile->update(['referral_code' => strtoupper(substr(md5($user->id . $user->email), 0, 8))]);
        }

        $referralCount = User::where('referred_by', $user->id)->count();

        $earnings = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'referral_bonus')
            ->latest()
            ->get();

        $totalEarned = $earnings->sum('amount');

        $referrals = User::where('referred_by', $user->id)
            ->with('profile')
            ->latest()
            ->get();

        $tier1Bonus     = (float) Setting::get('referral_bonus', '0');
        $tier2Bonus     = (float) Setting::get('referral_bonus_tier2', '0');
        $tier3Bonus     = (float) Setting::get('referral_bonus_tier3', '0');
        $tier2Threshold = (int)   Setting::get('referral_bonus_tier2_threshold', '6');
        $tier3Threshold = (int)   Setting::get('referral_bonus_tier3_threshold', '16');

        $currentTier   = $referralCount >= $tier3Threshold ? 3 : ($referralCount >= $tier2Threshold ? 2 : 1);
        $currentBonus  = $currentTier === 3 ? $tier3Bonus : ($currentTier === 2 ? $tier2Bonus : $tier1Bonus);

        $nextThreshold = null;
        $nextBonus     = null;
        $progressPct   = 100;

        if ($currentTier === 1 && $tier2Threshold > 1) {
            $nextThreshold = $tier2Threshold;
            $nextBonus     = $tier2Bonus;
            $progressPct   = min(99, round(($referralCount / $tier2Threshold) * 100));
        } elseif ($currentTier === 2) {
            $nextThreshold = $tier3Threshold;
            $nextBonus     = $tier3Bonus;
            $progressPct   = min(99, round((($referralCount - $tier2Threshold) / ($tier3Threshold - $tier2Threshold)) * 100));
        }

        $milestones = [
            ['label' => 'Bronze', 'threshold' => 1,             'bonus' => $tier1Bonus, 'color' => 'amber',  'tier' => 1],
            ['label' => 'Silver', 'threshold' => $tier2Threshold,'bonus' => $tier2Bonus,'color' => 'slate',  'tier' => 2],
            ['label' => 'Gold',   'threshold' => $tier3Threshold,'bonus' => $tier3Bonus,'color' => 'yellow', 'tier' => 3],
        ];

        $bonusRate = $currentBonus;

        return view('dashboard.referrals', compact(
            'user', 'profile', 'referralCount', 'earnings', 'totalEarned',
            'bonusRate', 'referrals', 'milestones', 'currentTier',
            'currentBonus', 'nextThreshold', 'nextBonus', 'progressPct'
        ));
    }
}
