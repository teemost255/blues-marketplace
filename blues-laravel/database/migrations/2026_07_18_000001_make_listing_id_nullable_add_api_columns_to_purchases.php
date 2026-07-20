<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop the existing foreign key so we can make the column nullable
            $table->dropForeign(['listing_id']);

            // Make listing_id nullable (API purchases have no local listing)
            $table->foreignId('listing_id')->nullable()->change();

            // Re-add the foreign key with nullOnDelete so deleting a listing doesn't cascade
            $table->foreign('listing_id')->references('id')->on('listings')->nullOnDelete();

            // Track where the purchase came from and store the API product details
            $table->string('source')->default('local')->after('status');       // 'local' | 'api'
            $table->unsignedInteger('api_product_id')->nullable()->after('source');
            $table->string('api_product_name')->nullable()->after('api_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
            $table->dropColumn(['source', 'api_product_id', 'api_product_name']);
            $table->foreignId('listing_id')->nullable(false)->change();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
        });
    }
};
