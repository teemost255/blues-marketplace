<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('listing_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('description')->nullable()->after('slug');
            $table->string('icon')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('icon');
        });
    }
    public function down(): void {
        Schema::table('listing_categories', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'icon', 'is_active']);
        });
    }
};
