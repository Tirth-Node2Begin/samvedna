-- ---------------------------------------------------------------------------
-- Samvedna Homeopathy — consultation submissions table
-- ---------------------------------------------------------------------------
-- This table is created automatically when the "Samvedna Core" plugin is
-- activated (via dbDelta). It is provided here for reference and for manual
-- setup if needed. Replace the `wp_` prefix below with your site's actual
-- table prefix (see $table_prefix in wp-config.php).
--
-- `condition` is a reserved word in MySQL, so the column is `condition_type`.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `wp_samvedna_inquiries` (
  `id`             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_name`    VARCHAR(255)        NOT NULL,
  `child_age`      TINYINT UNSIGNED    NOT NULL,
  `condition_type` VARCHAR(255)        NOT NULL,
  `country`        VARCHAR(255)        NOT NULL,
  `phone`          VARCHAR(32)         NOT NULL,
  `email`          VARCHAR(255)        NOT NULL,
  `message`        TEXT                NULL,
  `preferred_time` VARCHAR(32)         NOT NULL,
  `source`         VARCHAR(32)         NOT NULL DEFAULT 'website',
  `ip_address`     VARCHAR(45)         NULL,
  `created_at`     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
