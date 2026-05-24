<?php

namespace Database\Seeders;

use App\Models\Listing;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $listings = [
            // Social Media
            [
                'title'       => 'Verified Instagram Account (5K Followers)',
                'description' => 'Aged Instagram account with 5,000 real followers. Niche: lifestyle. Includes full account access, original email, and 2FA removed. Ready for immediate use.',
                'category'    => 'social-media',
                'price'       => 8500,
                'stock'       => 5,
                'featured'    => true,
                'image_url'   => 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=800&q=80',
            ],
            [
                'title'       => 'Facebook Aged Account (2018 Creation)',
                'description' => 'Genuine Facebook account created in 2018 with profile history. Friends: 200+. No restrictions. Perfect for marketplace or ads use.',
                'category'    => 'social-media',
                'price'       => 4500,
                'stock'       => 10,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1508672019048-805c876b67e2?w=800&q=80',
            ],
            [
                'title'       => 'Twitter/X Account (Verified Blue)',
                'description' => 'Twitter/X account with blue checkmark subscription active. 1,200 followers, aged 2020. Full credentials provided.',
                'category'    => 'social-media',
                'price'       => 12000,
                'stock'       => 3,
                'featured'    => true,
                'image_url'   => 'https://images.unsplash.com/photo-1611605698335-8adc22224671?w=800&q=80',
            ],
            [
                'title'       => 'TikTok Account (10K Followers)',
                'description' => 'TikTok account with 10,000 followers in the entertainment niche. High engagement rate. Full access provided.',
                'category'    => 'social-media',
                'price'       => 15000,
                'stock'       => 2,
                'featured'    => true,
                'image_url'   => 'https://images.unsplash.com/photo-1614680376739-414d95ff43df?w=800&q=80',
            ],

            // Email Accounts
            [
                'title'       => 'Gmail Account Bundle (5 Accounts)',
                'description' => 'Five aged Gmail accounts (2019–2021), each with recovery options set up. Not phone-verified. Delivered within 30 minutes.',
                'category'    => 'email-accounts',
                'price'       => 3500,
                'stock'       => 20,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1596526131083-e8c633c948d2?w=800&q=80',
            ],
            [
                'title'       => 'Outlook Account (Aged 2020)',
                'description' => 'Single aged Outlook account with full access. Clean history, no bans. Original credentials provided.',
                'category'    => 'email-accounts',
                'price'       => 1500,
                'stock'       => 15,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1684369175809-f9642bd5e5c4?w=800&q=80',
            ],
            [
                'title'       => 'Yahoo Mail Account (Phone Verified)',
                'description' => 'Phone-verified Yahoo Mail account. Aged 2019. Ideal for account creation and verification tasks.',
                'category'    => 'email-accounts',
                'price'       => 2000,
                'stock'       => 8,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=800&q=80',
            ],

            // Streaming
            [
                'title'       => 'Netflix Premium Account (1 Month)',
                'description' => 'Netflix Premium 4K account with 1 month validity. 4 screens. Instant delivery. Replacement guarantee within the subscription period.',
                'category'    => 'streaming',
                'price'       => 5000,
                'stock'       => 25,
                'featured'    => true,
                'image_url'   => 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=800&q=80',
            ],
            [
                'title'       => 'Spotify Premium (3 Months)',
                'description' => 'Spotify Premium individual plan with 3 months of access. Ad-free, offline listening, unlimited skips. Instant delivery.',
                'category'    => 'streaming',
                'price'       => 4200,
                'stock'       => 30,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&q=80',
            ],
            [
                'title'       => 'Disney+ Account (6 Months)',
                'description' => 'Disney+ account with 6 months validity. Access to all Disney, Marvel, Star Wars, and National Geographic content.',
                'category'    => 'streaming',
                'price'       => 6500,
                'stock'       => 12,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1618556450991-2f1af64e8191?w=800&q=80',
            ],

            // Gaming
            [
                'title'       => 'Steam Account (50+ Games Library)',
                'description' => 'Steam account with 50+ games including popular AAA titles. 2,000+ hours playtime. Full credentials. No VAC bans.',
                'category'    => 'gaming',
                'price'       => 22000,
                'stock'       => 2,
                'featured'    => true,
                'image_url'   => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800&q=80',
            ],
            [
                'title'       => 'Valorant Account (Gold Rank)',
                'description' => 'Valorant account ranked Gold in the current season. Full access with 15+ agents unlocked. NA server.',
                'category'    => 'gaming',
                'price'       => 18000,
                'stock'       => 4,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=800&q=80',
            ],
            [
                'title'       => 'PUBG Mobile Account (Platinum Tier)',
                'description' => 'PUBG Mobile account at Platinum tier with rare gun skins and outfits. Level 60+. Full account access.',
                'category'    => 'gaming',
                'price'       => 9500,
                'stock'       => 6,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1560419015-7c427e8ae5ba?w=800&q=80',
            ],

            // VPN & Privacy
            [
                'title'       => 'NordVPN Account (1 Year)',
                'description' => 'NordVPN premium account with 1 year of access. Connect up to 6 devices. 5,000+ servers in 60 countries. Instant delivery.',
                'category'    => 'vpn-privacy',
                'price'       => 11000,
                'stock'       => 18,
                'featured'    => true,
                'image_url'   => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=800&q=80',
            ],
            [
                'title'       => 'ExpressVPN Account (6 Months)',
                'description' => 'ExpressVPN account with 6 months validity. Fast speeds, 94 countries, and 24/7 support. Up to 5 devices.',
                'category'    => 'vpn-privacy',
                'price'       => 8000,
                'stock'       => 10,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=800&q=80',
            ],

            // Shopping
            [
                'title'       => 'Amazon Account (Prime Member)',
                'description' => 'Amazon account with active Prime membership. Clean purchase history. US-based account. Includes access to Prime Video.',
                'category'    => 'shopping',
                'price'       => 7500,
                'stock'       => 7,
                'featured'    => true,
                'image_url'   => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&q=80',
            ],
            [
                'title'       => 'eBay Seller Account (100% Feedback)',
                'description' => 'eBay seller account with 100% positive feedback and 50+ completed transactions. US account. Ready to list.',
                'category'    => 'shopping',
                'price'       => 14000,
                'stock'       => 3,
                'featured'    => false,
                'image_url'   => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=800&q=80',
            ],
        ];

        foreach ($listings as $data) {
            Listing::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
