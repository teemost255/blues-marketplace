<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('virtual_number_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('external_order_id')->nullable()->index();
            $table->string('service');
            $table->string('country')->default('ng');
            $table->string('phone_number')->nullable();
            $table->string('sms_code')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->text('raw_response')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('virtual_number_orders');
    }
};
