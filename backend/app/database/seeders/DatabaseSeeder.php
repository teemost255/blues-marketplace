<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed listing categories
        $cats = ['Facebook', 'Instagram', 'TikTok', '2nd Numbers'];
        foreach ($cats as $c) {
            \App\Models\ListingCategory::firstOrCreate(['name' => $c]);
        }

        // Seed some listings
        \App\Models\Listing::factory()->count(8)->create();
    }
}
