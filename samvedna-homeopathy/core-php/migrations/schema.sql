-- ---------------------------------------------------------------------------
-- Samvedna Homeopathy — Core PHP admin schema
-- ---------------------------------------------------------------------------
-- Run via: php core-php/migrations/migrate.php
-- (the script creates the database if missing, then applies this file).
-- ---------------------------------------------------------------------------

-- Admin users for the CMS.
CREATE TABLE IF NOT EXISTS `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(64)  NOT NULL,
  `name`          VARCHAR(128) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog posts.
CREATE TABLE IF NOT EXISTS `blogs` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`         VARCHAR(200) NOT NULL,
  `title`        VARCHAR(255) NOT NULL,
  `excerpt`      TEXT         NULL,
  `content`      LONGTEXT     NULL,
  `category`     VARCHAR(100) NOT NULL DEFAULT 'General',
  `author`       VARCHAR(128) NULL,
  `image`        VARCHAR(255) NULL,
  `alt`          VARCHAR(255) NULL,
  `read_time`    VARCHAR(32)  NULL,
  `status`       ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` DATE         NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parent video testimonials.
CREATE TABLE IF NOT EXISTS `video_testimonials` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(128) NOT NULL,
  `condition_label` VARCHAR(128) NOT NULL,
  `location`        VARCHAR(128) NULL,
  `youtube_id`      VARCHAR(64)  NULL,
  `poster`          VARCHAR(255) NULL,
  `alt`             VARCHAR(255) NULL,
  `duration`        VARCHAR(16)  NULL,
  `sort_order`      INT          NOT NULL DEFAULT 0,
  `status`          ENUM('draft','published') NOT NULL DEFAULT 'published',
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Doctor / team member profiles.
CREATE TABLE IF NOT EXISTS `doctors` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(128) NOT NULL,
  `title`           VARCHAR(128) NULL,
  `credential`      VARCHAR(255) NULL,
  `image`           VARCHAR(255) NULL,
  `alt`             VARCHAR(255) NULL,
  `specialization`  VARCHAR(255) NULL,
  `experience`      VARCHAR(64)  NULL,
  `summary`         TEXT         NULL,
  `about`           TEXT         NULL,
  `qualifications`  TEXT         NULL,   -- JSON array
  `specializations` TEXT         NULL,   -- JSON array
  `treatments`      TEXT         NULL,   -- JSON array
  `certifications`  TEXT         NULL,   -- JSON array
  `awards`          TEXT         NULL,   -- JSON array
  `languages`       TEXT         NULL,   -- JSON array
  `consultation`    TEXT         NULL,
  `sort_order`      INT          NOT NULL DEFAULT 0,
  `status`          ENUM('draft','published') NOT NULL DEFAULT 'published',
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conditions supported (the "Conditions we support" bento grid on the homepage).
-- `span` drives the card size/emphasis in that grid. `image` is optional and the
-- card falls back to its built-in icon when empty.
CREATE TABLE IF NOT EXISTS `conditions` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(191) NOT NULL,
  `description` TEXT         NULL,
  `image`       VARCHAR(255) NULL,
  `alt`         VARCHAR(255) NULL,
  `span`        ENUM('featured','standard','compact') NOT NULL DEFAULT 'standard',
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `status`      ENUM('draft','published') NOT NULL DEFAULT 'published',
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consultation submissions (shared with the Next.js app / WordPress plugin).
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `parent_name`    VARCHAR(255) NOT NULL,
  `child_age`      INT          NOT NULL,
  `condition_type` VARCHAR(255) NOT NULL,
  `country`        VARCHAR(255) NOT NULL,
  `phone`          VARCHAR(32)  NOT NULL,
  `email`          VARCHAR(255) NOT NULL,
  `message`        TEXT         NULL,
  `preferred_time` VARCHAR(32)  NOT NULL,
  `source`         VARCHAR(32)  NOT NULL DEFAULT 'website',
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detailed care-plan assessment intakes (the multi-step wizard that opens from a
-- care-plan card). General-information fields are stored as real columns for the
-- admin list, and every clinical answer lives in the `answers` JSON blob.
CREATE TABLE IF NOT EXISTS `consultations` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `plan`         VARCHAR(64)  NOT NULL DEFAULT '',
  `plan_name`    VARCHAR(128) NULL,
  `amount`       VARCHAR(32)  NULL,
  `patient_name` VARCHAR(255) NOT NULL,
  `father_name`  VARCHAR(255) NULL,
  `mobile`       VARCHAR(32)  NOT NULL,
  `alt_phone`    VARCHAR(32)  NULL,
  `address`      TEXT         NULL,
  `city`         VARCHAR(128) NULL,
  `state`        VARCHAR(128) NULL,
  `zip`          VARCHAR(32)  NULL,
  `email`        VARCHAR(255) NULL,
  `remarks`      TEXT         NULL,
  `child_age`    VARCHAR(32)  NULL,
  `answers`      LONGTEXT     NULL,
  `source`       VARCHAR(32)  NOT NULL DEFAULT 'website',
  `status`       VARCHAR(32)  NOT NULL DEFAULT 'new',
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
