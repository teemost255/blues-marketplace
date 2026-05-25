<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bank_transfer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 20)->default('wallet_topup'); // wallet_topup | marketplace
            $table->foreignId('listing_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('reference', 100)->unique();
            $table->string('status', 20)->default('pending'); // pending | confirmed | rejected
            $table->text('admin_note')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bank_transfer_payments');
    }
};
