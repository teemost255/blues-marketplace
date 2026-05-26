-- Blues Marketplace MySQL Dump
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- Generated: 2026-05-26T04:54:40.440Z
-- Includes all 25 tables + listing_categories.image_path
--
-- Import: mysql -u root -p your_db < blues_marketplace_mysql.sql

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `admin_audit_log`;
CREATE TABLE `admin_audit_log` (
    id BIGINT NOT NULL,
    admin_id BIGINT,
    action VARCHAR(255) NOT NULL,
    target_type VARCHAR(255),
    target_id BIGINT,
    details JSON,
    ip_address VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `admins_users`;
CREATE TABLE `admins_users` (
    id BIGINT NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(255),
    avatar_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1 NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    role VARCHAR(255) DEFAULT 'admin',
    PRIMARY KEY (id),
    UNIQUE KEY uk_admins_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
    id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(255) DEFAULT 'info',
    sent_by BIGINT,
    email_sent TINYINT(1) DEFAULT 0 NOT NULL,
    recipients_count INT DEFAULT 0 NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `bank_transfer_payments`;
CREATE TABLE `bank_transfer_payments` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    type VARCHAR(20) DEFAULT 'wallet_topup',
    listing_id BIGINT,
    purchase_id BIGINT,
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(100) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    admin_note TEXT,
    confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_bank_transfer_payments_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
    key VARCHAR(255) NOT NULL,
    value TEXT NOT NULL,
    expiration INT NOT NULL,
    PRIMARY KEY (key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
    key VARCHAR(255) NOT NULL,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    PRIMARY KEY (key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
    id BIGINT NOT NULL,
    uuid VARCHAR(255) NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_failed_jobs_uuid (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT,
    cancelled_at INT,
    created_at INT NOT NULL,
    finished_at INT,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
    id BIGINT NOT NULL,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts smallint NOT NULL,
    reserved_at INT,
    available_at INT NOT NULL,
    created_at INT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `listing_categories`;
CREATE TABLE `listing_categories` (
    id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    slug VARCHAR(255),
    description VARCHAR(255),
    icon VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1 NOT NULL,
    image_path VARCHAR(255),
    PRIMARY KEY (id),
    UNIQUE KEY uk_listing_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `listing_reviews`;
CREATE TABLE `listing_reviews` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    listing_id BIGINT NOT NULL,
    purchase_id BIGINT NOT NULL,
    rating smallint NOT NULL,
    comment TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_listing_reviews_user_id_purchase_id (user_id, purchase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `listings`;
CREATE TABLE `listings` (
    id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(255),
    price DECIMAL(10,2) DEFAULT '0',
    stock INT DEFAULT 0 NOT NULL,
    is_active TINYINT(1) DEFAULT 1 NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    image_path VARCHAR(255),
    featured TINYINT(1) DEFAULT 0 NOT NULL,
    login_details TEXT,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
    id INT NOT NULL,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0 NOT NULL,
    type VARCHAR(255) DEFAULT 'info',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    username VARCHAR(255),
    display_name VARCHAR(255),
    avatar_url VARCHAR(255),
    status VARCHAR(255) DEFAULT 'active',
    referral_code VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT profiles_status_check CHECK (((status)= ANY ((ARRAY['active', 'suspended', 'banned'])[]))),
    PRIMARY KEY (id),
    UNIQUE KEY uk_profiles_referral_code (referral_code),
    UNIQUE KEY uk_profiles_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    listing_id BIGINT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(255) DEFAULT 'pending',
    delivery_data TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT purchases_status_check CHECK (((status)= ANY ((ARRAY['pending', 'completed', 'refunded', 'disputed'])[]))),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
    id VARCHAR(255) NOT NULL,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    id BIGINT NOT NULL,
    key VARCHAR(255) NOT NULL,
    value TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_settings_key (key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    admin_reply TEXT,
    status VARCHAR(255) DEFAULT 'open',
    priority VARCHAR(255) DEFAULT 'medium',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT support_tickets_priority_check CHECK (((priority)= ANY ((ARRAY['low', 'medium', 'high'])[]))),
    CONSTRAINT support_tickets_status_check CHECK (((status)= ANY ((ARRAY['open', 'in_progress', 'resolved', 'closed'])[]))),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    status VARCHAR(255) DEFAULT 'active',
    email_notifications TINYINT(1) DEFAULT 1 NOT NULL,
    referred_by BIGINT,
    referral_deposited TINYINT(1) DEFAULT 0 NOT NULL,
    referral_purchased TINYINT(1) DEFAULT 0 NOT NULL,
    referral_bonus_paid TINYINT(1) DEFAULT 0 NOT NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `virtual_number_orders`;
CREATE TABLE `virtual_number_orders` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    external_order_id VARCHAR(255),
    service VARCHAR(255) NOT NULL,
    country VARCHAR(255) DEFAULT 'ng',
    phone_number VARCHAR(255),
    sms_code VARCHAR(255),
    cost DECIMAL(10,2) DEFAULT '0',
    status VARCHAR(255) DEFAULT 'pending',
    raw_response TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    provider VARCHAR(255) DEFAULT 'logsplug',
    CONSTRAINT virtual_number_orders_status_check CHECK (((status)= ANY ((ARRAY['pending', 'active', 'completed', 'cancelled', 'failed'])[]))),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `wallet_transactions`;
CREATE TABLE `wallet_transactions` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type VARCHAR(255) NOT NULL,
    reference VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT wallet_transactions_type_check CHECK (((type)= ANY ((ARRAY['deposit', 'withdrawal', 'purchase', 'refund', 'referral_bonus', 'admin_credit', 'admin_debit'])[]))),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `wallets`;
CREATE TABLE `wallets` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    balance DECIMAL(10,2) DEFAULT '0',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_wallets_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
    id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    listing_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_wishlists_user_id_listing_id (user_id, listing_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DATA --

INSERT INTO `listings` (id, title, description, category, price, stock, is_active, image_url, created_at, updated_at, image_path, featured, login_details) VALUES
('1', 'Verified Instagram Account (5K Followers)', 'Aged Instagram account with 5,000 real followers. Niche: lifestyle. Includes full account access, original email, and 2FA removed. Ready for immediate use.', 'social-media', '8500.00', '5', 't', 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 't', NULL),
('2', 'Facebook Aged Account (2018 Creation)', 'Genuine Facebook account created in 2018 with profile history. Friends: 200+. No restrictions. Perfect for marketplace or ads use.', 'social-media', '4500.00', '10', 't', 'https://images.unsplash.com/photo-1508672019048-805c876b67e2?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('3', 'Twitter/X Account (Verified Blue)', 'Twitter/X account with blue checkmark subscription active. 1,200 followers, aged 2020. Full credentials provided.', 'social-media', '12000.00', '3', 't', 'https://images.unsplash.com/photo-1611605698335-8adc22224671?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 't', NULL),
('4', 'TikTok Account (10K Followers)', 'TikTok account with 10,000 followers in the entertainment niche. High engagement rate. Full access provided.', 'social-media', '15000.00', '2', 't', 'https://images.unsplash.com/photo-1614680376739-414d95ff43df?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 't', NULL),
('5', 'Gmail Account Bundle (5 Accounts)', 'Five aged Gmail accounts (2019–2021), each with recovery options set up. Not phone-verified. Delivered within 30 minutes.', 'email-accounts', '3500.00', '20', 't', 'https://images.unsplash.com/photo-1596526131083-e8c633c948d2?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('6', 'Outlook Account (Aged 2020)', 'Single aged Outlook account with full access. Clean history, no bans. Original credentials provided.', 'email-accounts', '1500.00', '15', 't', 'https://images.unsplash.com/photo-1684369175809-f9642bd5e5c4?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('7', 'Yahoo Mail Account (Phone Verified)', 'Phone-verified Yahoo Mail account. Aged 2019. Ideal for account creation and verification tasks.', 'email-accounts', '2000.00', '8', 't', 'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('8', 'Netflix Premium Account (1 Month)', 'Netflix Premium 4K account with 1 month validity. 4 screens. Instant delivery. Replacement guarantee within the subscription period.', 'streaming', '5000.00', '25', 't', 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 't', NULL),
('9', 'Spotify Premium (3 Months)', 'Spotify Premium individual plan with 3 months of access. Ad-free, offline listening, unlimited skips. Instant delivery.', 'streaming', '4200.00', '30', 't', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('10', 'Disney+ Account (6 Months)', 'Disney+ account with 6 months validity. Access to all Disney, Marvel, Star Wars, and National Geographic content.', 'streaming', '6500.00', '12', 't', 'https://images.unsplash.com/photo-1618556450991-2f1af64e8191?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('11', 'Steam Account (50+ Games Library)', 'Steam account with 50+ games including popular AAA titles. 2,000+ hours playtime. Full credentials. No VAC bans.', 'gaming', '22000.00', '2', 't', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 't', NULL),
('12', 'Valorant Account (Gold Rank)', 'Valorant account ranked Gold in the current season. Full access with 15+ agents unlocked. NA server.', 'gaming', '18000.00', '4', 't', 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('13', 'PUBG Mobile Account (Platinum Tier)', 'PUBG Mobile account at Platinum tier with rare gun skins and outfits. Level 60+. Full account access.', 'gaming', '9500.00', '6', 't', 'https://images.unsplash.com/photo-1560419015-7c427e8ae5ba?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('14', 'NordVPN Account (1 Year)', 'NordVPN premium account with 1 year of access. Connect up to 6 devices. 5,000+ servers in 60 countries. Instant delivery.', 'vpn-privacy', '11000.00', '18', 't', 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 't', NULL),
('15', 'ExpressVPN Account (6 Months)', 'ExpressVPN account with 6 months validity. Fast speeds, 94 countries, and 24/7 support. Up to 5 devices.', 'vpn-privacy', '8000.00', '10', 't', 'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL),
('16', 'Amazon Account (Prime Member)', 'Amazon account with active Prime membership. Clean purchase history. US-based account. Includes access to Prime Video.', 'shopping', '7500.00', '7', 't', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 't', NULL),
('17', 'eBay Seller Account (100% Feedback)', 'eBay seller account with 100% positive feedback and 50+ completed transactions. US account. Ready to list.', 'shopping', '14000.00', '3', 't', 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=800&q=80', '2026-05-26 04:39:24', '2026-05-26 04:39:24', NULL, 'f', NULL);

INSERT INTO `listing_categories` (id, name, created_at, updated_at, slug, description, icon, is_active) VALUES
('1', 'Facebook', '2026-05-26 04:39:15', '2026-05-26 04:39:15', NULL, NULL, NULL, 't'),
('2', 'Instagram', '2026-05-26 04:39:15', '2026-05-26 04:39:15', NULL, NULL, NULL, 't'),
('3', 'TikTok', '2026-05-26 04:39:15', '2026-05-26 04:39:15', NULL, NULL, NULL, 't'),
('4', 'Virtual Numbers', '2026-05-26 04:39:15', '2026-05-26 04:39:15', NULL, NULL, NULL, 't'),
('5', 'Twitter', '2026-05-26 04:39:15', '2026-05-26 04:39:15', NULL, NULL, NULL, 't'),
('6', 'Telegram', '2026-05-26 04:39:15', '2026-05-26 04:39:15', NULL, NULL, NULL, 't'),
('7', 'Social Media', '2026-05-26 04:39:24', '2026-05-26 04:39:24', 'social-media', 'Verified social media accounts including Facebook, Instagram, Twitter/X, TikTok and more.', '📱', 't'),
('8', 'Email Accounts', '2026-05-26 04:39:24', '2026-05-26 04:39:24', 'email-accounts', 'Aged and verified email accounts from Gmail, Outlook, Yahoo and other providers.', '📧', 't'),
('9', 'Streaming', '2026-05-26 04:39:24', '2026-05-26 04:39:24', 'streaming', 'Premium streaming service accounts for Netflix, Spotify, Disney+ and more.', '🎬', 't'),
('10', 'Gaming', '2026-05-26 04:39:24', '2026-05-26 04:39:24', 'gaming', 'Gaming accounts, in-game currencies, and game keys for popular titles.', '🎮', 't'),
('11', 'VPN & Privacy', '2026-05-26 04:39:24', '2026-05-26 04:39:24', 'vpn-privacy', 'VPN subscriptions and privacy tool accounts to keep you secure online.', '🔒', 't'),
('12', 'Shopping', '2026-05-26 04:39:24', '2026-05-26 04:39:24', 'shopping', 'E-commerce accounts for Amazon, eBay, and other major shopping platforms.', '🛒', 't');

INSERT INTO `migrations` (id, migration, batch) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_05_23_170156_create_admins_users_table', '1'),
('5', '2026_05_23_170157_create_listings_table', '1'),
('6', '2026_05_23_170157_create_profiles_table', '1'),
('7', '2026_05_23_170158_create_purchases_table', '1'),
('8', '2026_05_23_170159_create_wallet_transactions_table', '1'),
('9', '2026_05_23_170159_create_wallets_table', '1'),
('10', '2026_05_23_170200_create_support_tickets_table', '1'),
('11', '2026_05_23_170201_create_notifications_table', '1'),
('12', '2026_05_23_170202_create_admin_audit_log_table', '1'),
('13', '2026_05_23_211537_add_status_to_users_table', '1'),
('14', '2026_05_23_211538_create_settings_table', '1'),
('15', '2026_05_23_211758_add_extra_fields_to_listings_table', '1'),
('16', '2026_05_23_215407_add_role_to_admins_users_table', '1'),
('17', '2026_05_23_215408_create_password_reset_tokens_table', '1'),
('18', '2026_05_24_000001_create_wishlists_table', '1'),
('19', '2026_05_24_000002_create_listing_categories_table', '1'),
('20', '2026_05_24_000003_add_extra_fields_to_listing_categories_table', '1'),
('21', '2026_05_24_070448_create_announcements_table', '1'),
('22', '2026_05_25_000001_update_listing_categories_add_twitter_telegram', '1'),
('23', '2026_05_25_000002_create_virtual_number_orders_table', '1'),
('24', '2026_05_25_000003_add_login_details_to_listings_table', '1'),
('25', '2026_05_25_100001_add_email_notifications_to_users_table', '1'),
('26', '2026_05_25_100002_add_referred_by_to_users_table', '1'),
('27', '2026_05_25_200001_create_listing_reviews_table', '1'),
('28', '2026_05_26_000001_add_referral_tracking_to_users', '1'),
('29', '2026_05_26_000002_add_login_tracking_to_users', '1'),
('30', '2026_05_26_100001_create_bank_transfer_payments_table', '1'),
('31', '2026_05_27_000001_add_provider_to_virtual_number_orders', '1');

INSERT INTO `sessions` (id, user_id, ip_address, user_agent, payload, last_activity) VALUES
('izqyczSDNQEOzGTm6QKvNnqEYXCGWJjPosCtkeGh', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUndlUW04V2ZlMHkwWUlVdTVBWG9NNlF3ZEVZTWJBaVhvUFJkVWlKQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Nzk6Imh0dHA6Ly81ZWIwY2ZiMy05MDA2LTRmM2UtYjY3ZS1lNDRlMDIyMjZiZTMtMDAtMmVmNzJ3bzZrNW02bi5qYW5ld2F5LnJlcGxpdC5kZXYiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', '1779770389'),
('JSgj87WYqeynYVwPJT9cG3Bjt2CNqgudIlP5yDt8', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNzlQdFRNWWYzZlhVemVJbVBYSWQ3UklXVnFtbFc1VGM0Q3lScVpLbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6NTAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1779770395');

SET FOREIGN_KEY_CHECKS=1;
