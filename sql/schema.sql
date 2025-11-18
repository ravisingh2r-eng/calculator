-- ============================================================================
-- Calculator Hub - Database Schema
-- ============================================================================
--
-- MySQL 5.7+ / MariaDB 10.2+
-- Character Set: utf8mb4 (emoji support)
-- Collation: utf8mb4_unicode_ci
--
-- Run this script in phpMyAdmin:
-- 1. Create a new database (e.g., calculator_db)
-- 2. Select the database
-- 3. Go to SQL tab
-- 4. Paste and execute this script
--
-- @author  Your Name
-- @version 1.0.0
-- ============================================================================

-- Set character set and collation
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- TABLE: calculator_categories
-- ============================================================================
-- Stores calculator categories (Finance, Health, Math, etc.)
-- Each calculator belongs to one category

DROP TABLE IF EXISTS `calculator_categories`;

CREATE TABLE `calculator_categories` (
    -- Primary Key
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Category Information
    `name` VARCHAR(100) NOT NULL COMMENT 'Category display name',
    `slug` VARCHAR(100) NOT NULL COMMENT 'URL-friendly identifier',
    `icon` VARCHAR(50) DEFAULT NULL COMMENT 'Emoji or icon class',
    `short_desc` VARCHAR(255) DEFAULT NULL COMMENT 'Brief description for cards',

    -- Display Settings
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Display order (lower = first)',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=visible, 0=hidden',

    -- Timestamps
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_slug` (`slug`),
    KEY `idx_categories_active_order` (`is_active`, `sort_order`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Calculator categories';


-- ============================================================================
-- TABLE: calculators
-- ============================================================================
-- Main table storing all calculator information
-- Includes SEO fields, content, and settings

DROP TABLE IF EXISTS `calculators`;

CREATE TABLE `calculators` (
    -- Primary Key
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Foreign Key
    `category_id` INT UNSIGNED NOT NULL COMMENT 'Reference to calculator_categories',

    -- Basic Information
    `name` VARCHAR(150) NOT NULL COMMENT 'Calculator display name',
    `slug` VARCHAR(150) NOT NULL COMMENT 'URL-friendly identifier',
    `short_desc` VARCHAR(255) DEFAULT NULL COMMENT 'Brief description for cards',

    -- SEO Fields
    `meta_title` VARCHAR(70) DEFAULT NULL COMMENT 'SEO title tag (max 60-70 chars)',
    `meta_desc` VARCHAR(160) DEFAULT NULL COMMENT 'SEO meta description (max 155-160 chars)',
    `h1_title` VARCHAR(150) DEFAULT NULL COMMENT 'Main heading on page',
    `primary_keyword` VARCHAR(100) DEFAULT NULL COMMENT 'Main SEO keyword',
    `secondary_keywords` VARCHAR(500) DEFAULT NULL COMMENT 'Comma-separated secondary keywords',

    -- Content
    `intro_html` TEXT DEFAULT NULL COMMENT 'Introduction text above calculator',
    `content_html` MEDIUMTEXT DEFAULT NULL COMMENT 'Main content/instructions below calculator',
    `faq_json` JSON DEFAULT NULL COMMENT 'FAQ data for schema markup',
    `schema_json` JSON DEFAULT NULL COMMENT 'Additional structured data',

    -- Technical Settings
    `js_file` VARCHAR(100) DEFAULT NULL COMMENT 'JavaScript filename (e.g., bmi.js)',
    `ad_layout` VARCHAR(50) DEFAULT 'default' COMMENT 'Ad placement template',
    `disable_ads` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=no ads on this page',

    -- Visibility Settings
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=published, 0=draft',
    `is_indexed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=allow search engine indexing',
    `show_in_sitemap` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=include in XML sitemap',

    -- Timestamps
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_calculators_slug` (`slug`),
    KEY `idx_calculators_category` (`category_id`),
    KEY `idx_calculators_active` (`is_active`),
    KEY `idx_calculators_sitemap` (`show_in_sitemap`, `is_active`),

    -- Foreign Key Constraint
    CONSTRAINT `fk_calculators_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `calculator_categories` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Calculator pages and SEO data';


-- ============================================================================
-- TABLE: users
-- ============================================================================
-- Admin users for backend panel
-- Supports multiple roles (admin, editor, viewer)

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    -- Primary Key
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- User Information
    `name` VARCHAR(100) NOT NULL COMMENT 'Full name',
    `email` VARCHAR(255) NOT NULL COMMENT 'Login email (unique)',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',

    -- Role & Permissions
    `role` ENUM('admin', 'editor', 'viewer') NOT NULL DEFAULT 'viewer' COMMENT 'User role',

    -- Account Status
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=can login, 0=disabled',
    `last_login` TIMESTAMP NULL DEFAULT NULL COMMENT 'Last successful login',

    -- Timestamps
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin panel users';


-- ============================================================================
-- TABLE: calculator_stats
-- ============================================================================
-- Analytics tracking for calculator usage
-- Stores events like page views, calculations, etc.

DROP TABLE IF EXISTS `calculator_stats`;

CREATE TABLE `calculator_stats` (
    -- Primary Key
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Foreign Key
    `calculator_id` INT UNSIGNED NOT NULL COMMENT 'Reference to calculators table',

    -- Event Information
    `event_type` ENUM('view', 'calculate', 'share', 'print') NOT NULL DEFAULT 'view' COMMENT 'Type of event',

    -- Request Information (anonymized)
    `user_agent` VARCHAR(500) DEFAULT NULL COMMENT 'Browser user agent string',
    `ip_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 hash of IP (privacy)',
    `referrer` VARCHAR(500) DEFAULT NULL COMMENT 'HTTP referrer URL',
    `country_code` CHAR(2) DEFAULT NULL COMMENT 'ISO country code (optional)',

    -- Timestamp
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Constraints
    PRIMARY KEY (`id`),
    KEY `idx_stats_calculator` (`calculator_id`),
    KEY `idx_stats_event` (`event_type`),
    KEY `idx_stats_date` (`created_at`),
    KEY `idx_stats_calculator_date` (`calculator_id`, `created_at`),

    -- Foreign Key Constraint
    CONSTRAINT `fk_stats_calculator`
        FOREIGN KEY (`calculator_id`)
        REFERENCES `calculators` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Calculator usage analytics';


-- ============================================================================
-- TABLE: settings (Optional - Site Settings)
-- ============================================================================
-- Key-value store for site configuration
-- Useful for storing AdSense IDs, analytics codes, etc.

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
    -- Primary Key
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Setting Data
    `setting_key` VARCHAR(100) NOT NULL COMMENT 'Setting identifier',
    `setting_value` TEXT DEFAULT NULL COMMENT 'Setting value',
    `setting_group` VARCHAR(50) DEFAULT 'general' COMMENT 'Group for organization',

    -- Metadata
    `description` VARCHAR(255) DEFAULT NULL COMMENT 'Human-readable description',

    -- Timestamps
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`),
    KEY `idx_settings_group` (`setting_group`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Site configuration settings';


-- ============================================================================
-- Re-enable foreign key checks
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================================
-- INDEXES EXPLANATION
-- ============================================================================
--
-- uk_* = Unique Key (prevents duplicates)
-- idx_* = Index (improves query performance)
-- fk_* = Foreign Key (maintains referential integrity)
--
-- Query optimizations:
-- - Get active categories sorted: idx_categories_active_order
-- - Get calculators by category: idx_calculators_category
-- - Generate sitemap: idx_calculators_sitemap
-- - Analytics by calculator+date: idx_stats_calculator_date
--
-- ============================================================================


-- ============================================================================
-- SAMPLE QUERIES (for reference)
-- ============================================================================

-- Get all active categories ordered:
-- SELECT * FROM calculator_categories WHERE is_active = 1 ORDER BY sort_order;

-- Get calculators in a category:
-- SELECT * FROM calculators WHERE category_id = 1 AND is_active = 1;

-- Get calculator by slug:
-- SELECT c.*, cat.name AS category_name
-- FROM calculators c
-- JOIN calculator_categories cat ON c.category_id = cat.id
-- WHERE c.slug = 'bmi' AND c.is_active = 1;

-- Count calculators per category:
-- SELECT cat.name, COUNT(c.id) AS calculator_count
-- FROM calculator_categories cat
-- LEFT JOIN calculators c ON cat.id = c.category_id AND c.is_active = 1
-- GROUP BY cat.id;

-- Get popular calculators (by views in last 30 days):
-- SELECT c.name, c.slug, COUNT(s.id) AS view_count
-- FROM calculators c
-- JOIN calculator_stats s ON c.id = s.calculator_id
-- WHERE s.event_type = 'view' AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
-- GROUP BY c.id
-- ORDER BY view_count DESC
-- LIMIT 10;

-- Get sitemap data:
-- SELECT slug, updated_at FROM calculators WHERE is_active = 1 AND show_in_sitemap = 1;


-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
