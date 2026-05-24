<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('referral_deposited')->default(false)->after('referred_by');
            $table->boolean('referral_purchased')->default(false)->after('referral_deposited');
            $table->boolean('referral_bonus_paid')->default(false)->after('referral_purchased');
        });

        // Mark all existing referred users as already paid (old system paid on signup)
        DB::table('users')->whereNotNull('referred_by')->update(['referral_bonus_paid' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_deposited', 'referral_purchased', 'referral_bonus_paid']);
        });
    }
};
