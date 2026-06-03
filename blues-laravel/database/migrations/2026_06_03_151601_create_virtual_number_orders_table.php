<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('virtual_number_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activation_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('service', 20);
            $table->string('service_name', 100);
            $table->integer('country')->default(0);
            $table->string('country_name', 100)->default('');
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('sms_code', 50)->nullable();
            $table->string('status', 20)->default('waiting');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('activation_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_number_orders');
    }
};
