<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            $table->string('provider')->default('logsplug')->after('user_id');
        });
    }
    public function down(): void {
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }
};
