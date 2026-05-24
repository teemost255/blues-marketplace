<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('listings', function (Blueprint $table) {
            $table->text('login_details')->nullable()->after('description');
        });
    }
    public function down(): void {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('login_details');
        });
    }
};
