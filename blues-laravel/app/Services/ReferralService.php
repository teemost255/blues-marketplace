<?php
namespace App\Services;

use App\Models\{User, Wallet, WalletTransaction, Notification, Setting};

class ReferralService
{
    public static function markDeposited(User $user): void
    {
        if (!$user->referred_by || $user->referral_bonus_paid) return;
        if ($user->referral_deposited) return;

        $user->update(['referral_deposited' => true]);
        static::checkAndAward($user->fresh());
    }

    public static function markPurchased(User $user): void
    {
        if (!$user->referred_by || $user->referral_bonus_paid) return;
        if ($user->referral_purchased) return;

        $user->update(['referral_purchased' => true]);
        static::checkAndAward($user->fresh());
    }

    public static function checkAndAward(User $user): void
    {
        if (!$user->referred_by || $user->referral_bonus_paid) return;
        if (!$user->referral_deposited || !$user->referral_purchased) return;

        $referrerId   = $user->referred_by;
        $qualifiedCount = User::where('referred_by', $referrerId)
            ->where('referral_bonus_paid', true)
            ->count();

        $tier3Threshold = (int) Setting::get('referral_bonus_tier3_threshold', '16');
        $tier2Threshold = (int) Setting::get('referral_bonus_tier2_threshold', '6');
        $nextCount      = $qualifiedCount + 1;

        if ($nextCount >= $tier3Threshold) {
            $bonusAmount = (float) Setting::get('referral_bonus_tier3', '0');
            $tier        = 'Gold';
        } elseif ($nextCount >= $tier2Threshold) {
            $bonusAmount = (float) Setting::get('referral_bonus_tier2', '0');
            $tier        = 'Silver';
        } else {
            $bonusAmount = (float) Setting::get('referral_bonus', '0');
            $tier        = 'Bronze';
        }

        $user->update(['referral_bonus_paid' => true]);

        if ($bonusAmount > 0) {
            $wallet = Wallet::firstOrCreate(['user_id' => $referrerId], ['balance' => 0]);
            $wallet->increment('balance', $bonusAmount);

            WalletTransaction::create([
                'user_id'     => $referrerId,
                'amount'      => $bonusAmount,
                'type'        => 'referral_bonus',
                'reference'   => 'REF-' . $user->id . '-' . time(),
                'description' => 'Referral bonus (' . $tier . '): ' . $user->name . ' funded & made first purchase',
                'status'      => 'completed',
            ]);

            Notification::create([
                'user_id' => $referrerId,
                'title'   => 'Referral Bonus Earned! ' . $tier,
                'message' => $user->name . ' has funded their wallet and made a purchase via your referral link. ₦' . number_format($bonusAmount, 2) . ' added to your wallet.',
                'type'    => 'success',
            ]);
        }
    }
}
