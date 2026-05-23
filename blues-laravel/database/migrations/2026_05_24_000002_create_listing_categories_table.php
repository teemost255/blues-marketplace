<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('listing_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        DB::table('listing_categories')->insert([
            ['name' => 'Facebook',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Instagram',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TikTok',      'created_at' => now(), 'updated_at' => now()],
            ['name' => '2nd Numbers', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    public function down(): void { Schema::dropIfExists('listing_categories'); }
};
