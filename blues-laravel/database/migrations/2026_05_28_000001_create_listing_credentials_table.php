<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('listing_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->text('details');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['listing_id', 'is_used']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('listing_credentials');
    }
};
