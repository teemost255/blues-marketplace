<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            // PVADeals service IDs are 24-character Mongo IDs.
            $table->string('service', 64)->change();

            // PVADeals webhooks deliver the complete SMS message, not only a short code.
            $table->text('sms_code')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('virtual_number_orders', function (Blueprint $table) {
            $table->string('service', 20)->change();
            $table->string('sms_code', 50)->nullable()->change();
        });
    }
};
