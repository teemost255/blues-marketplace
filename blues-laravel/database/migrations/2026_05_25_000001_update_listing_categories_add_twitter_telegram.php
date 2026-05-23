<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Rename '2nd Numbers' to 'Virtual Numbers'
        DB::table('listing_categories')->where('name', '2nd Numbers')->update(['name' => 'Virtual Numbers']);

        // Add Twitter and Telegram if they don't exist
        foreach (['Twitter', 'Telegram'] as $cat) {
            if (!DB::table('listing_categories')->where('name', $cat)->exists()) {
                DB::table('listing_categories')->insert(['name' => $cat, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void {
        DB::table('listing_categories')->where('name', 'Virtual Numbers')->update(['name' => '2nd Numbers']);
        DB::table('listing_categories')->whereIn('name', ['Twitter', 'Telegram'])->delete();
    }
};
