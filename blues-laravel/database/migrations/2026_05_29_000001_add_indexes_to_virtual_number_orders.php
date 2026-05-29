<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            $table->index(['user_id', 'status'],     'vno_user_status_idx');
            $table->index(['status', 'created_at'],  'vno_status_created_idx');
        });
    }
    public function down(): void {
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            $table->dropIndex('vno_user_status_idx');
            $table->dropIndex('vno_status_created_idx');
        });
    }
};
