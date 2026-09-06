-- ============================================================================
-- Samvedna Homeopathy — doctor profile corrections (2026-08-05)
--
-- Brings the SERVER database in line with the agreed medical-team wording. The
-- doctors table is admin-managed, so these were only ever changed on the local
-- development database — the live site still shows the old values until this
-- runs.
--
-- Changes:
--   * "Consultant" titles become "Doctor"        (Dr. Yakshika, Dr. Shiva)
--   * Dr. Krishna Thakor experience  12+ -> 7+ Years
--   * Dr. Yakshika       experience   6+ -> 5+ Years
--   * Dr. Shiva          experience   already 5+ Years (restated so the file is
--                                     safe to re-run and self-documenting)
--   * Two supporting text fields on Dr. Krishna Thakor that still said
--     "consultant" are reworded to "senior doctor".
--
-- HOW TO RUN (cPanel):
--   phpMyAdmin -> select the site database (DB_NAME from your .env)
--              -> "SQL" tab -> paste this whole file -> Go.
--
-- Safe to run more than once: every statement sets an absolute value and is
-- matched by name, so re-running changes nothing. No rows are inserted or
-- deleted and no schema is altered.
--
-- NOTE: these are content edits. The client can make the same changes by hand
-- at /samvedna -> Doctors -> Edit. This file is just the faster, exact route.
-- ============================================================================

-- Dr. Krishna Thakor — senior doctor, 7+ years -------------------------------
UPDATE `doctors`
SET `experience` = '7+ Years',
    `credential` = 'BHMS | Senior doctor consultation, long-term follow-up, and family guidance',
    `alt`        = 'Dr. Krishna Thakor, senior doctor for child neurodevelopment care',
    `updated_at` = NOW()
WHERE `name` = 'Dr. Krishna Thakor';

-- Dr. Yakshika — Doctor, 5+ years --------------------------------------------
UPDATE `doctors`
SET `title`      = 'Doctor',
    `experience` = '5+ Years',
    `updated_at` = NOW()
WHERE `name` = 'Dr. Yakshika';

-- Dr. Shiva — Doctor, 5+ years -----------------------------------------------
UPDATE `doctors`
SET `title`      = 'Doctor',
    `experience` = '5+ Years',
    `updated_at` = NOW()
WHERE `name` = 'Dr. Shiva';

-- Check ----------------------------------------------------------------------
-- Expect: Krishna Thakor / Senior Doctor / 7+ Years
--         Yakshika       / Doctor        / 5+ Years
--         Shiva          / Doctor        / 5+ Years
SELECT `id`, `name`, `title`, `experience`, `status`
FROM `doctors`
ORDER BY `sort_order`, `id`;
