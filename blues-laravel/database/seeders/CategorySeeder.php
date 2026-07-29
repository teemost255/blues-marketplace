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
                'name'        => 'Streaming',
                'description' => 'Premium streaming accounts — Netflix, Spotify, Disney+, Prime Video, Hulu, HBO Max, Apple TV+, Crunchyroll and more.',
                'icon'        => '🎬',
            ],
            [
                'name'        => 'Social Media',
                'description' => 'Verified social media accounts — Facebook, Instagram, Twitter/X, TikTok, Snapchat, LinkedIn, Pinterest and more.',
                'icon'        => '📱',
            ],
            [
                'name'        => 'Music',
                'description' => 'Premium music streaming accounts — Spotify, Apple Music, Tidal, Deezer, YouTube Music and more.',
                'icon'        => '🎵',
            ],
            [
                'name'        => 'Gaming',
                'description' => 'Gaming accounts, gift cards and in-game credits — Steam, PlayStation, Xbox, Roblox, PUBG, Call of Duty and more.',
                'icon'        => '🎮',
            ],
            [
                'name'        => 'Email Accounts',
                'description' => 'Aged and verified email accounts — Gmail, Outlook, Yahoo, iCloud and other providers.',
                'icon'        => '📧',
            ],
            [
                'name'        => 'VPN & Privacy',
                'description' => 'VPN and privacy tool subscriptions — NordVPN, ExpressVPN, Surfshark, CyberGhost and more.',
                'icon'        => '🔒',
            ],
            [
                'name'        => 'Education',
                'description' => 'Online learning platform accounts — Coursera, Udemy, Skillshare, LinkedIn Learning, Duolingo Plus and more.',
                'icon'        => '📚',
            ],
            [
                'name'        => 'Shopping',
                'description' => 'E-commerce accounts and gift cards — Amazon, eBay, Shein, AliExpress and major shopping platforms.',
                'icon'        => '🛒',
            ],
            [
                'name'        => 'Productivity',
                'description' => 'Software and productivity subscriptions — Microsoft 365, Adobe Creative Cloud, Canva Pro, Notion and more.',
                'icon'        => '💼',
            ],
            [
                'name'        => 'Dating',
                'description' => 'Premium dating app subscriptions — Tinder Gold, Bumble Premium, Badoo and more.',
                'icon'        => '❤️',
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
