<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add the missing sms_received_at column
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('virtual_number_orders', 'sms_received_at')) {
                $table->timestamp('sms_received_at')->nullable()->after('sms_code');
            }
        });

        // Replace the status check constraint to include 'received'
        DB::statement("ALTER TABLE virtual_number_orders DROP CONSTRAINT IF EXISTS virtual_number_orders_status_check");
        DB::statement("ALTER TABLE virtual_number_orders ADD CONSTRAINT virtual_number_orders_status_check CHECK (status IN ('pending','active','received','completed','cancelled','failed'))");
    }

    public function down(): void
    {
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            $table->dropColumn('sms_received_at');
        });

        DB::statement("ALTER TABLE virtual_number_orders DROP CONSTRAINT IF EXISTS virtual_number_orders_status_check");
        DB::statement("ALTER TABLE virtual_number_orders ADD CONSTRAINT virtual_number_orders_status_check CHECK (status IN ('pending','active','completed','cancelled','failed'))");
    }
};
