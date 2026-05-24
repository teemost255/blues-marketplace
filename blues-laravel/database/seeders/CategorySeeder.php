<?php

namespace Database\Seeders;

use App\Models\ListingCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Social Media',
                'description' => 'Verified social media accounts including Facebook, Instagram, Twitter/X, TikTok and more.',
                'icon'        => '📱',
            ],
            [
                'name'        => 'Email Accounts',
                'description' => 'Aged and verified email accounts from Gmail, Outlook, Yahoo and other providers.',
                'icon'        => '📧',
            ],
            [
                'name'        => 'Streaming',
                'description' => 'Premium streaming service accounts for Netflix, Spotify, Disney+ and more.',
                'icon'        => '🎬',
            ],
            [
                'name'        => 'Gaming',
                'description' => 'Gaming accounts, in-game currencies, and game keys for popular titles.',
                'icon'        => '🎮',
            ],
            [
                'name'        => 'VPN & Privacy',
                'description' => 'VPN subscriptions and privacy tool accounts to keep you secure online.',
                'icon'        => '🔒',
            ],
            [
                'name'        => 'Shopping',
                'description' => 'E-commerce accounts for Amazon, eBay, and other major shopping platforms.',
                'icon'        => '🛒',
            ],
        ];

        foreach ($categories as $cat) {
            ListingCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, [
                    'slug'      => Str::slug($cat['name']),
                    'is_active' => true,
                ])
            );
        }
    }
}
