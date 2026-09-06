-- ============================================================================
-- Samvedna Homeopathy — conditions migration (2026-08-05)
--
-- Adds the `conditions` table behind the homepage "Conditions we support" grid,
-- which used to be hardcoded in the front-end and is now editable (copy, image,
-- card size, order, draft/published) from the admin panel at /samvedna.
--
-- HOW TO RUN (cPanel):
--   1. Upload the new live/ folder first, so /api/conditions.php and the admin
--      pages exist. The API tolerates this table being absent, so the site keeps
--      working in between — the section is simply hidden.
--   2. cPanel -> phpMyAdmin -> select the site database (the DB_NAME value in
--      your .env) -> "SQL" tab -> paste this whole file -> Go.
--
-- Safe to run more than once: the table is only created if missing, and the
-- seven seed rows are only inserted when the table is empty.
-- Nothing here drops or alters existing tables.
-- ============================================================================

-- 1. The table ---------------------------------------------------------------
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

-- 2. The seven existing conditions -------------------------------------------
-- These are the real cards the site has always shown, so they are seeded rather
-- than left to the client to retype. `image` is intentionally blank: each card
-- falls back to its built-in icon until a photo is uploaded from the admin.
--
-- The SELECT ... WHERE NOT EXISTS guard makes the insert a no-op if the table
-- already has rows, so re-running this file never creates duplicates.
INSERT INTO `conditions` (`name`, `description`, `image`, `alt`, `span`, `sort_order`, `status`)
SELECT * FROM (
  SELECT
    'Autism Spectrum Disorder Support' AS a,
    'Individualized support for communication, social interaction, sensory needs, behavior, sleep, and family routines.' AS b,
    '' AS c, '' AS d, 'featured' AS e, 0 AS f, 'published' AS g
  UNION ALL SELECT
    'ADHD Support',
    'Care focused on attention, hyperactivity, impulsivity, sleep, emotional regulation, and learning readiness.',
    '', '', 'standard', 1, 'published'
  UNION ALL SELECT
    'Learning Disability Support',
    'Guidance for children struggling with reading, writing, processing, classroom readiness, and confidence.',
    '', '', 'compact', 2, 'published'
  UNION ALL SELECT
    'Speech Delay Support',
    'Support for expressive speech, understanding, non-verbal communication, and connection alongside therapies.',
    '', '', 'compact', 3, 'published'
  UNION ALL SELECT
    'Developmental Delay Support',
    'Structured care for children whose milestones, regulation, and everyday developmental progress need support.',
    '', '', 'standard', 4, 'published'
  UNION ALL SELECT
    'Genetic Disorders Support',
    'Individualized supportive care for children with genetic and syndrome-related developmental challenges.',
    '', '', 'standard', 5, 'published'
  UNION ALL SELECT
    'Neurological Disorders Support',
    'Homeopathic support for pediatric neurological and neurodevelopmental concerns with careful monitoring.',
    '', '', 'standard', 6, 'published'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `conditions`);

-- 3. Check -------------------------------------------------------------------
-- Expect 7 rows, all 'published'.
SELECT `id`, `name`, `span`, `sort_order`, `status` FROM `conditions` ORDER BY `sort_order`;
