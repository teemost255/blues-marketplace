-- ============================================================
-- Blues Marketplace — MySQL Schema Dump
-- Generated: 2026-05-26
-- Source: SQLite (converted to MySQL syntax)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------
-- Table: admin_audit_log
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_audit_log` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `admin_id`    BIGINT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(255) NOT NULL,
  `target_type` VARCHAR(255) DEFAULT NULL,
  `target_id`   BIGINT UNSIGNED DEFAULT NULL,
  `details`     TEXT DEFAULT NULL,
  `ip_address`  VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: admins_users
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins_users` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email`        VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(255) DEFAULT NULL,
  `avatar_url`   VARCHAR(255) DEFAULT NULL,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  `last_login`   DATETIME DEFAULT NULL,
  `created_at`   DATETIME DEFAULT NULL,
  `updated_at`   DATETIME DEFAULT NULL,
  `role`         VARCHAR(255) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: announcements
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `announcements` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title`            VARCHAR(255) NOT NULL,
  `message`          TEXT NOT NULL,
  `type`             VARCHAR(255) NOT NULL DEFAULT 'info',
  `sent_by`          BIGINT UNSIGNED DEFAULT NULL,
  `email_sent`       TINYINT(1) NOT NULL DEFAULT 0,
  `recipients_count` INT NOT NULL DEFAULT 0,
  `created_at`       DATETIME DEFAULT NULL,
  `updated_at`       DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: bank_transfer_payments
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bank_transfer_payments` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`           BIGINT UNSIGNED NOT NULL,
  `type`              VARCHAR(255) NOT NULL DEFAULT 'wallet_topup',
  `listing_id`        BIGINT UNSIGNED DEFAULT NULL,
  `purchase_id`       BIGINT UNSIGNED DEFAULT NULL,
  `amount`            DECIMAL(15,2) NOT NULL,
  `reference`         VARCHAR(255) NOT NULL UNIQUE,
  `status`            VARCHAR(255) NOT NULL DEFAULT 'pending',
  `admin_note`        TEXT DEFAULT NULL,
  `confirmed_at`      DATETIME DEFAULT NULL,
  `created_at`        DATETIME DEFAULT NULL,
  `updated_at`        DATETIME DEFAULT NULL,
  `user_confirmed_at` DATETIME DEFAULT NULL,
  INDEX `idx_bank_transfer_user` (`user_id`),
  INDEX `idx_bank_transfer_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: cache
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cache` (
  `key`        VARCHAR(255) NOT NULL PRIMARY KEY,
  `value`      MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: cache_locks
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key`        VARCHAR(255) NOT NULL PRIMARY KEY,
  `owner`      VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: failed_jobs
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uuid`       VARCHAR(255) NOT NULL UNIQUE,
  `connection` TEXT NOT NULL,
  `queue`      TEXT NOT NULL,
  `payload`    LONGTEXT NOT NULL,
  `exception`  LONGTEXT NOT NULL,
  `failed_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: job_batches
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id`             VARCHAR(255) NOT NULL PRIMARY KEY,
  `name`           VARCHAR(255) NOT NULL,
  `total_jobs`     INT NOT NULL,
  `pending_jobs`   INT NOT NULL,
  `failed_jobs`    INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options`        MEDIUMTEXT DEFAULT NULL,
  `cancelled_at`   INT DEFAULT NULL,
  `created_at`     INT NOT NULL,
  `finished_at`    INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: jobs
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `queue`        VARCHAR(255) NOT NULL,
  `payload`      LONGTEXT NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at`   INT UNSIGNED NOT NULL,
  INDEX `idx_jobs_queue` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: listing_categories
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `listing_categories` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(255) NOT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  `slug`        VARCHAR(255) DEFAULT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `icon`        VARCHAR(255) DEFAULT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `image_path`  VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: listing_reviews
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `listing_reviews` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `listing_id`  BIGINT UNSIGNED NOT NULL,
  `purchase_id` BIGINT UNSIGNED NOT NULL,
  `rating`      TINYINT NOT NULL,
  `comment`     TEXT DEFAULT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  UNIQUE KEY `uq_review_purchase` (`purchase_id`),
  INDEX `idx_review_listing` (`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: listings
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `listings` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title`         VARCHAR(255) NOT NULL,
  `description`   TEXT DEFAULT NULL,
  `category`      VARCHAR(255) DEFAULT NULL,
  `price`         DECIMAL(15,2) NOT NULL DEFAULT 0,
  `stock`         INT NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `image_url`     VARCHAR(255) DEFAULT NULL,
  `created_at`    DATETIME DEFAULT NULL,
  `updated_at`    DATETIME DEFAULT NULL,
  `image_path`    VARCHAR(255) DEFAULT NULL,
  `featured`      TINYINT(1) NOT NULL DEFAULT 0,
  `login_details` TEXT DEFAULT NULL,
  INDEX `idx_listings_category` (`category`),
  INDEX `idx_listings_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: migrations
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: notifications
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `title`      VARCHAR(255) NOT NULL,
  `message`    TEXT NOT NULL,
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `type`       VARCHAR(255) NOT NULL DEFAULT 'info',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  INDEX `idx_notifications_user` (`user_id`),
  INDEX `idx_notifications_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: password_reset_tokens
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email`      VARCHAR(255) NOT NULL PRIMARY KEY,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: profiles
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `profiles` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`      BIGINT UNSIGNED NOT NULL UNIQUE,
  `username`     VARCHAR(255) DEFAULT NULL UNIQUE,
  `display_name` VARCHAR(255) DEFAULT NULL,
  `avatar_url`   VARCHAR(255) DEFAULT NULL,
  `status`       VARCHAR(255) NOT NULL DEFAULT 'active',
  `referral_code` VARCHAR(255) DEFAULT NULL UNIQUE,
  `created_at`   DATETIME DEFAULT NULL,
  `updated_at`   DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: purchases
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `purchases` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `listing_id`    BIGINT UNSIGNED NOT NULL,
  `amount`        DECIMAL(15,2) NOT NULL,
  `status`        VARCHAR(255) NOT NULL DEFAULT 'pending',
  `delivery_data` TEXT DEFAULT NULL,
  `created_at`    DATETIME DEFAULT NULL,
  `updated_at`    DATETIME DEFAULT NULL,
  INDEX `idx_purchases_user` (`user_id`),
  INDEX `idx_purchases_listing` (`listing_id`),
  INDEX `idx_purchases_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: sessions
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            VARCHAR(255) NOT NULL PRIMARY KEY,
  `user_id`       BIGINT UNSIGNED DEFAULT NULL,
  `ip_address`    VARCHAR(45) DEFAULT NULL,
  `user_agent`    TEXT DEFAULT NULL,
  `payload`       LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  INDEX `idx_sessions_user` (`user_id`),
  INDEX `idx_sessions_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: settings
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key`        VARCHAR(255) NOT NULL UNIQUE,
  `value`      TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: support_tickets
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `subject`     VARCHAR(255) NOT NULL,
  `message`     TEXT NOT NULL,
  `admin_reply` TEXT DEFAULT NULL,
  `status`      VARCHAR(255) NOT NULL DEFAULT 'open',
  `priority`    VARCHAR(255) NOT NULL DEFAULT 'medium',
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  INDEX `idx_tickets_user` (`user_id`),
  INDEX `idx_tickets_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: users
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`                 VARCHAR(255) NOT NULL,
  `email`                VARCHAR(255) NOT NULL UNIQUE,
  `email_verified_at`    DATETIME DEFAULT NULL,
  `password`             VARCHAR(255) NOT NULL,
  `remember_token`       VARCHAR(100) DEFAULT NULL,
  `created_at`           DATETIME DEFAULT NULL,
  `updated_at`           DATETIME DEFAULT NULL,
  `status`               VARCHAR(255) NOT NULL DEFAULT 'active',
  `email_notifications`  TINYINT(1) NOT NULL DEFAULT 1,
  `referred_by`          BIGINT UNSIGNED DEFAULT NULL,
  `referral_deposited`   TINYINT(1) NOT NULL DEFAULT 0,
  `referral_purchased`   TINYINT(1) NOT NULL DEFAULT 0,
  `referral_bonus_paid`  TINYINT(1) NOT NULL DEFAULT 0,
  `last_login_at`        DATETIME DEFAULT NULL,
  `last_login_ip`        VARCHAR(45) DEFAULT NULL,
  INDEX `idx_users_status` (`status`),
  INDEX `idx_users_referred` (`referred_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: virtual_number_orders
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `virtual_number_orders` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`           BIGINT UNSIGNED NOT NULL,
  `external_order_id` VARCHAR(255) DEFAULT NULL,
  `service`           VARCHAR(255) NOT NULL,
  `country`           VARCHAR(20) NOT NULL DEFAULT 'ng',
  `phone_number`      VARCHAR(50) DEFAULT NULL,
  `sms_code`          VARCHAR(255) DEFAULT NULL,
  `cost`              DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status`            VARCHAR(255) NOT NULL DEFAULT 'pending',
  `raw_response`      TEXT DEFAULT NULL,
  `created_at`        DATETIME DEFAULT NULL,
  `updated_at`        DATETIME DEFAULT NULL,
  `provider`          VARCHAR(255) NOT NULL DEFAULT 'logsplug',
  INDEX `idx_vno_user` (`user_id`),
  INDEX `idx_vno_status` (`status`),
  INDEX `idx_vno_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: wallet_transactions
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `amount`      DECIMAL(15,2) NOT NULL,
  `type`        VARCHAR(255) NOT NULL,
  `reference`   VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  INDEX `idx_wt_user` (`user_id`),
  INDEX `idx_wt_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: wallets
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wallets` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`    BIGINT UNSIGNED NOT NULL UNIQUE,
  `balance`    DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: wishlists
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `listing_id` BIGINT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  UNIQUE KEY `uq_wishlist` (`user_id`, `listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Foreign Key Constraints
-- -----------------------------------------------------------
ALTER TABLE `bank_transfer_payments`
  ADD CONSTRAINT `fk_btp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `listing_reviews`
  ADD CONSTRAINT `fk_lr_user`     FOREIGN KEY (`user_id`)     REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lr_listing`  FOREIGN KEY (`listing_id`)  REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lr_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchase_user`    FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_purchase_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

ALTER TABLE `support_tickets`
  ADD CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `virtual_number_orders`
  ADD CONSTRAINT `fk_vno_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `fk_wt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `wishlists`
  ADD CONSTRAINT `fk_wishlist_user`    FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================
-- End of dump — 26 tables total
-- ============================================================
