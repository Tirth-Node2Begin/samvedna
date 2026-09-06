-- ---------------------------------------------------------------------------
-- Samvedna CMS — clinical case management schema
-- ---------------------------------------------------------------------------
-- Run via: php samvedna-cms/backend/migrations/migrate.php [--seed-demo]
--
-- Every table is prefixed `cms_` so this can share a MySQL database with the
-- marketing site's core-php tables (blogs, doctors, inquiries…) without collision.
-- ---------------------------------------------------------------------------

-- Staff and doctors. `role` drives navigation, route guards and the sign-off /
-- escalation chains, so it is an enum rather than a free string.
CREATE TABLE IF NOT EXISTS `cms_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(64)  NOT NULL,
  `name`          VARCHAR(128) NOT NULL,
  `email`         VARCHAR(191) NULL,
  `phone`         VARCHAR(32)  NULL,
  `title`         VARCHAR(128) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('founder','senior_doctor','case_doctor','coordinator') NOT NULL DEFAULT 'case_doctor',
  `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_username` (`username`),
  KEY `idx_cms_user_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Care plan catalogue. The cadence columns are the single source of truth for
-- the auto-generated schedule — the UI never lets a user type an interval.
CREATE TABLE IF NOT EXISTS `cms_plans` (
  `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`                   VARCHAR(32)  NOT NULL,
  `name`                   VARCHAR(128) NOT NULL,
  `tagline`                VARCHAR(191) NULL,
  `price`                  INT UNSIGNED NOT NULL DEFAULT 0,
  `currency`               VARCHAR(8)   NOT NULL DEFAULT 'INR',
  `duration_months`        TINYINT UNSIGNED NOT NULL DEFAULT 6,
  `review_count`           TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `review_interval_days`   SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  `adherence_interval_days` SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  `reschedule_window_days` TINYINT UNSIGNED NOT NULL DEFAULT 7,
  `requires_senior`        TINYINT(1)   NOT NULL DEFAULT 0,
  `requires_founder`       TINYINT(1)   NOT NULL DEFAULT 0,
  `founder_interval_days`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `clinical_cover`         VARCHAR(191) NULL,
  `features`               TEXT         NULL,  -- JSON array of strings
  `highlight`              TINYINT(1)   NOT NULL DEFAULT 0,
  `sort_order`             INT          NOT NULL DEFAULT 0,
  `status`                 ENUM('active','archived') NOT NULL DEFAULT 'active',
  `created_at`             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_plan_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The child + guardian. Stays in `draft` until the baseline is complete and a
-- plan is confirmed; `activate` flips it to `active` and creates the case.
CREATE TABLE IF NOT EXISTS `cms_patients` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`             VARCHAR(24)  NOT NULL,
  `child_name`       VARCHAR(191) NOT NULL,
  `dob`              DATE         NULL,
  `gender`           ENUM('male','female','other','unspecified') NOT NULL DEFAULT 'unspecified',
  `guardian_name`    VARCHAR(191) NULL,
  `guardian_relation` VARCHAR(64) NULL,
  `phone`            VARCHAR(32)  NULL,
  `alt_phone`        VARCHAR(32)  NULL,
  `email`            VARCHAR(191) NULL,
  `city`             VARCHAR(128) NULL,
  `state`            VARCHAR(128) NULL,
  `country`          VARCHAR(128) NULL DEFAULT 'India',
  `referral_source`  VARCHAR(128) NULL,
  `notes`            TEXT         NULL,
  `status`           ENUM('draft','active','on_hold','completed','discharged') NOT NULL DEFAULT 'draft',
  `created_by`       INT UNSIGNED NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_patient_code` (`code`),
  KEY `idx_cms_patient_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The mandatory baseline, 1:1 with a patient. `is_complete` is computed on every
-- write by Baseline::evaluate() and is what blocks or unblocks case activation.
CREATE TABLE IF NOT EXISTS `cms_baselines` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id`          INT UNSIGNED NOT NULL,
  `condition_type`      VARCHAR(128) NULL,
  `severity`            ENUM('','mild','moderate','severe') NOT NULL DEFAULT '',
  `diagnosis_age`       VARCHAR(64)  NULL,
  `medical_history`     TEXT         NULL,
  `therapy_involvement` TEXT         NULL,
  `concerns`            TEXT         NULL,  -- JSON array, exactly 3 entries
  `markers`             TEXT         NULL,  -- JSON array of {key,label,rating}
  `is_complete`         TINYINT(1)   NOT NULL DEFAULT 0,
  `completed_at`        DATETIME     NULL,
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_baseline_patient` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A patient on a plan, with its assigned doctors and cycle counter.
CREATE TABLE IF NOT EXISTS `cms_cases` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`             VARCHAR(24)  NOT NULL,
  `patient_id`       INT UNSIGNED NOT NULL,
  `plan_id`          INT UNSIGNED NOT NULL,
  `case_doctor_id`   INT UNSIGNED NULL,
  `senior_doctor_id` INT UNSIGNED NULL,
  `founder_id`       INT UNSIGNED NULL,
  `start_date`       DATE         NOT NULL,
  `end_date`         DATE         NOT NULL,
  `total_cycles`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `current_cycle`    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status`           ENUM('active','paused','completed','closed') NOT NULL DEFAULT 'active',
  `activated_by`     INT UNSIGNED NULL,
  `activated_at`     DATETIME     NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_case_code` (`code`),
  KEY `idx_cms_case_patient` (`patient_id`),
  KEY `idx_cms_case_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Every touchpoint on the timeline. `original_due_date` is never overwritten so
-- the plan's +/- reschedule window can always be measured against it.
CREATE TABLE IF NOT EXISTS `cms_schedule_events` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id`           INT UNSIGNED NOT NULL,
  `cycle`             TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `type`              ENUM('activation','adherence','review','founder_review') NOT NULL,
  `title`             VARCHAR(191) NOT NULL,
  `due_date`          DATE         NOT NULL,
  `original_due_date` DATE         NOT NULL,
  `status`            ENUM('scheduled','upcoming','done','missed','rescheduled') NOT NULL DEFAULT 'scheduled',
  `owner_id`          INT UNSIGNED NULL,
  `notes`             TEXT         NULL,
  `completed_at`      DATETIME     NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cms_event_case` (`case_id`),
  KEY `idx_cms_event_due` (`due_date`),
  KEY `idx_cms_event_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- What a non-consult adherence touchpoint actually captured.
CREATE TABLE IF NOT EXISTS `cms_adherence_checks` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`            INT UNSIGNED NOT NULL,
  `case_id`             INT UNSIGNED NOT NULL,
  `cycle`               TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `medicine_compliance` ENUM('full','partial','none') NOT NULL DEFAULT 'full',
  `refill_status`       ENUM('stocked','due','requested','lapsed') NOT NULL DEFAULT 'stocked',
  `parent_concern_flag` TINYINT(1)   NOT NULL DEFAULT 0,
  `parent_note`         TEXT         NULL,
  `recorded_by`         INT UNSIGNED NULL,
  `recorded_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cms_adherence_case` (`case_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The forced bi-monthly review template.
CREATE TABLE IF NOT EXISTS `cms_reviews` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id`            INT UNSIGNED NOT NULL,
  `event_id`           INT UNSIGNED NULL,
  `cycle`              TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `review_date`        DATE         NOT NULL,
  `improvements`       TEXT         NULL,
  `stagnation`         TEXT         NULL,
  `protocol_decision`  ENUM('','continued','continued_adjusted','changed','paused') NOT NULL DEFAULT '',
  `protocol_rationale` TEXT         NULL,
  `therapy_notes`      TEXT         NULL,
  `adherence_percent`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `escalate`           TINYINT(1)   NOT NULL DEFAULT 0,
  `escalate_note`      TEXT         NULL,
  `next_review_date`   DATE         NULL,
  `status`             ENUM('draft','awaiting_signoff','closed') NOT NULL DEFAULT 'draft',
  `completeness`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `closed_on_time`     TINYINT(1)   NULL,
  `parent_satisfaction` DECIMAL(2,1) NULL,
  `created_by`         INT UNSIGNED NULL,
  `closed_at`          DATETIME     NULL,
  `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cms_review_case` (`case_id`),
  KEY `idx_cms_review_status` (`status`),
  KEY `idx_cms_review_date` (`review_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per role that must sign a review. Seeded when the review is created.
CREATE TABLE IF NOT EXISTS `cms_review_signoffs` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` INT UNSIGNED NOT NULL,
  `user_id`   INT UNSIGNED NULL,
  `role`      ENUM('case_doctor','senior_doctor','founder') NOT NULL,
  `status`    ENUM('pending','signed','rejected') NOT NULL DEFAULT 'pending',
  `comment`   TEXT         NULL,
  `signed_at` DATETIME     NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_signoff` (`review_id`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goals are rows, not prose, so the next cycle can score them.
CREATE TABLE IF NOT EXISTS `cms_review_goals` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id`    INT UNSIGNED NOT NULL,
  `review_id`  INT UNSIGNED NULL,
  `cycle`      TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `title`      VARCHAR(255) NOT NULL,
  `metric`     VARCHAR(191) NULL,
  `status`     ENUM('pending','progressing','achieved','missed') NOT NULL DEFAULT 'pending',
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cms_goal_case` (`case_id`, `cycle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Escalations carry a hard 7-day SLA (`due_date` = raised_at + plan window).
CREATE TABLE IF NOT EXISTS `cms_escalations` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`             VARCHAR(24)  NOT NULL,
  `case_id`          INT UNSIGNED NOT NULL,
  `review_id`        INT UNSIGNED NULL,
  `raised_by`        INT UNSIGNED NULL,
  `reasons`          TEXT         NULL,  -- JSON array of reason keys
  `notes`            TEXT         NULL,
  `level`            ENUM('senior','founder') NOT NULL DEFAULT 'senior',
  `status`           ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `raised_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date`         DATE         NOT NULL,
  `resolution_notes` TEXT         NULL,
  `resolved_by`      INT UNSIGNED NULL,
  `resolved_at`      DATETIME     NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_escalation_code` (`code`),
  KEY `idx_cms_escalation_case` (`case_id`),
  KEY `idx_cms_escalation_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The Senior -> Founder approval chain, one row per step.
CREATE TABLE IF NOT EXISTS `cms_escalation_steps` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `escalation_id` INT UNSIGNED NOT NULL,
  `user_id`       INT UNSIGNED NULL,
  `role`          ENUM('senior_doctor','founder') NOT NULL,
  `status`        ENUM('standby','reviewing','approved','resolved') NOT NULL DEFAULT 'standby',
  `note`          TEXT         NULL,
  `acted_at`      DATETIME     NULL,
  `sort_order`    INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_cms_step_escalation` (`escalation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auto-generated at the close of each review cycle. One per case + cycle.
CREATE TABLE IF NOT EXISTS `cms_progress_reports` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id`           INT UNSIGNED NOT NULL,
  `review_id`         INT UNSIGNED NULL,
  `cycle`             TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `period_start`      DATE         NOT NULL,
  `period_end`        DATE         NOT NULL,
  `overall_trend`     ENUM('improving','stable','declining') NOT NULL DEFAULT 'stable',
  `goals_total`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `goals_progressing` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `adherence_percent` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `areas`             TEXT         NULL,  -- JSON [{label,score,trend}]
  `objectives`        TEXT         NULL,  -- JSON [{title,metric,done}]
  `scorecard`         TEXT         NULL,  -- JSON {completeness,closure,satisfaction,doctor}
  `shared_at`         DATETIME     NULL,
  `shared_channel`    VARCHAR(32)  NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_progress_cycle` (`case_id`, `cycle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only audit trail.
CREATE TABLE IF NOT EXISTS `cms_activity_log` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NULL,
  `actor_name`  VARCHAR(128) NULL,
  `entity_type` VARCHAR(48)  NOT NULL,
  `entity_id`   INT UNSIGNED NULL,
  `action`      VARCHAR(48)  NOT NULL,
  `summary`     VARCHAR(255) NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cms_activity_entity` (`entity_type`, `entity_id`),
  KEY `idx_cms_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Medicine supply cycle + notifications
-- ---------------------------------------------------------------------------

-- One row per case: the 15-day medicine cycle. `next_due_on` is the single
-- source of truth for "who needs medicine today"; every delivery rewrites it.
CREATE TABLE IF NOT EXISTS `cms_medicine_supply` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id`           INT UNSIGNED NOT NULL,
  `patient_id`        INT UNSIGNED NOT NULL,
  `interval_days`     SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  `last_delivered_on` DATE         NULL,
  `next_due_on`       DATE         NOT NULL,
  `status`            ENUM('active','paused') NOT NULL DEFAULT 'active',
  -- Stock: when the pharmacy cannot supply, the delivery is deferred rather
  -- than marked done. `next_due_on` is pushed forward and the case stays flagged
  -- until a real delivery clears it.
  `stock_status`       ENUM('in_stock','out_of_stock') NOT NULL DEFAULT 'in_stock',
  `out_of_stock_since` DATE         NULL,
  `stock_note`         VARCHAR(255) NULL,
  `notes`             TEXT         NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_supply_case` (`case_id`),
  KEY `idx_cms_supply_due` (`next_due_on`),
  KEY `idx_cms_supply_patient` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only supply timeline. `next_due_on` is captured at the moment of the
-- event so the history reads correctly even if the interval changes later.
-- `kind` distinguishes a real delivery from an out-of-stock deferral, so the
-- patient's timeline shows both in order.
CREATE TABLE IF NOT EXISTS `cms_medicine_deliveries` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id`      INT UNSIGNED NOT NULL,
  `patient_id`   INT UNSIGNED NOT NULL,
  `kind`         ENUM('delivered','deferred_out_of_stock') NOT NULL DEFAULT 'delivered',
  `delivered_on` DATE         NOT NULL,
  `next_due_on`  DATE         NOT NULL,
  `mode`         ENUM('courier','hand','pickup','other') NOT NULL DEFAULT 'courier',
  `reference`    VARCHAR(128) NULL,
  `notes`        TEXT         NULL,
  `delivered_by` INT UNSIGNED NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cms_delivery_case` (`case_id`, `delivered_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- In-app notifications. Generated lazily on the first request of each day
-- (Notification::generateDaily), so no cron is needed for the demo; the unique
-- key makes generation idempotent — one notice per type, case and due date.
CREATE TABLE IF NOT EXISTS `cms_notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`       VARCHAR(48)  NOT NULL,
  `case_id`    INT UNSIGNED NULL,
  `patient_id` INT UNSIGNED NULL,
  `title`      VARCHAR(191) NOT NULL,
  `body`       VARCHAR(255) NULL,
  `href`       VARCHAR(191) NULL,
  `due_date`   DATE         NULL,
  `status`     ENUM('unread','read','done','dismissed') NOT NULL DEFAULT 'unread',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at`    DATETIME     NULL,
  `done_at`    DATETIME     NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_notification` (`type`, `case_id`, `due_date`),
  KEY `idx_cms_notification_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
