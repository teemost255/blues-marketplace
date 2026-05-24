<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Setting, WalletTransaction};
use Illuminate\Support\Facades\DB;

class ReferralLeaderboardController extends Controller
{
    public function index()
    {
        $leaderboard = User::withCount('referrals')
            ->having('referrals_count', '>', 0)
            ->orderByDesc('referrals_count')
            ->limit(50)
            ->get()
            ->map(function ($user) {
                $bonus = WalletTransaction::where('user_id', $user->id)
                    ->where('type', 'referral_bonus')
                    ->sum('amount');
                return [
                    'user'           => $user,
                    'referral_count' => $user->referrals_count,
                    'total_bonus'    => $bonus,
                ];
            });

        $totalReferrals    = User::whereNotNull('referred_by')->count();
        $referralBonusRate = Setting::get('referral_bonus', '0');

        return view('admin.referrals', compact('leaderboard', 'totalReferrals', 'referralBonusRate'));
    }
}
