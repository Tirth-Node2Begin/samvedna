<?php
/**
 * Migration + seed runner for the Samvedna CMS backend.
 *
 * Usage (from the project root, with MySQL running):
 *   php samvedna-cms/backend/migrations/migrate.php              # schema + staff accounts + plans
 *   php samvedna-cms/backend/migrations/migrate.php --seed-demo  # + the demo case walkthrough
 *   php samvedna-cms/backend/migrations/migrate.php --fresh --seed-demo   # drop cms_* first
 *
 * Safe to run repeatedly: the schema is CREATE TABLE IF NOT EXISTS and the
 * seeders skip rows that already exist.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$isCli    = (PHP_SAPI === 'cli');
$argvList = $argv ?? [];
$seedDemo = ($isCli && in_array('--seed-demo', $argvList, true)) || (!$isCli && isset($_GET['seed-demo']));
$fresh    = ($isCli && in_array('--fresh', $argvList, true)) || (!$isCli && isset($_GET['fresh']));
$nl       = $isCli ? "\n" : "<br>\n";

function out(string $m): void
{
    global $nl;
    echo $m . $nl;
    @flush();
}

// --- 1. Connect and create the database if missing -------------------------
try {
    $serverDsn = sprintf('mysql:host=%s;port=%s;charset=%s', CMS_DB_HOST, CMS_DB_PORT, CMS_DB_CHARSET);
    $pdo = new PDO($serverDsn, CMS_DB_USER, CMS_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    out('ERROR: Could not connect to MySQL: ' . $e->getMessage());
    out('Is MySQL/MariaDB running? Check DB_* in samvedna-cms/backend/.env or config/config.php.');
    exit(1);
}

$dbName = CMS_DB_NAME;
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$dbName}`");
out("Database `{$dbName}` ready.");

// --- 1b. Optional clean slate ---------------------------------------------
if ($fresh) {
    out('--fresh: dropping cms_* tables…');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $pdo->query(
        "SELECT table_name FROM information_schema.tables
         WHERE table_schema = " . $pdo->quote($dbName) . " AND table_name LIKE 'cms\\_%'"
    )->fetchAll();
    foreach ($tables as $t) {
        $name = $t['table_name'] ?? $t['TABLE_NAME'];
        $pdo->exec("DROP TABLE IF EXISTS `{$name}`");
        out("  dropped {$name}");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

// --- 2. Apply the schema ---------------------------------------------------
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    out('ERROR: could not read schema.sql');
    exit(1);
}

// Split on semicolons at end of line — the schema has no stored routines, so a
// simple split is safe and avoids needing a real SQL parser.
$chunks = preg_split('/;\s*[\r\n]/', $schema) ?: [];
$applied = 0;
foreach ($chunks as $chunk) {
    // Each chunk arrives with its leading `--` documentation block attached.
    // Strip those lines first — a statement that merely *starts* with a comment
    // is still a statement, and skipping it silently leaves no tables behind.
    $lines = preg_split('/\r\n|\r|\n/', $chunk) ?: [];
    $lines = array_filter($lines, static fn(string $l): bool => !str_starts_with(trim($l), '--'));
    $sql = trim(rtrim(trim(implode("\n", $lines)), ';'));

    if ($sql === '') {
        continue;
    }
    try {
        $pdo->exec($sql);
        $applied++;
    } catch (PDOException $e) {
        out('ERROR applying statement: ' . $e->getMessage());
        out(substr($sql, 0, 160) . '…');
        exit(1);
    }
}
out('Schema applied (' . $applied . ' statements).');

// --- 2b. Additive column migrations ---------------------------------------
// schema.sql is CREATE TABLE IF NOT EXISTS, so a column added to a definition
// never reaches a database that already has that table. MySQL 8 has no
// `ADD COLUMN IF NOT EXISTS`, so check information_schema first. Each entry is
// safe to run repeatedly.
$columnMigrations = [
    ['cms_medicine_supply', 'stock_status', "ENUM('in_stock','out_of_stock') NOT NULL DEFAULT 'in_stock' AFTER `status`"],
    ['cms_medicine_supply', 'out_of_stock_since', 'DATE NULL AFTER `stock_status`'],
    ['cms_medicine_supply', 'stock_note', 'VARCHAR(255) NULL AFTER `out_of_stock_since`'],
    ['cms_medicine_deliveries', 'kind', "ENUM('delivered','deferred_out_of_stock') NOT NULL DEFAULT 'delivered' AFTER `patient_id`"],
];

$hasColumn = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = ? AND table_name = ? AND column_name = ?'
);
$tableExists = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?'
);

$addedColumns = 0;
foreach ($columnMigrations as [$table, $column, $definition]) {
    $tableExists->execute([$dbName, $table]);
    if ((int) $tableExists->fetchColumn() === 0) {
        continue;
    }
    $hasColumn->execute([$dbName, $table, $column]);
    if ((int) $hasColumn->fetchColumn() > 0) {
        continue;
    }
    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    out("  + {$table}.{$column}");
    $addedColumns++;
}
if ($addedColumns > 0) {
    out("Column migrations applied ({$addedColumns}).");
}

// --- 3. Seed staff accounts ------------------------------------------------
$staff = [
    ['founder',     'Dr. Krunal Kosada', 'Founder & Chief Homeopath',      'founder'],
    ['senior',      'Dr. Suresh Nair',   'Senior Consultant — Paediatrics', 'senior_doctor'],
    ['casedoctor',  'Dr. Anjali Rao',    'Case Doctor — Developmental Care', 'case_doctor'],
    ['casedoctor2', 'Dr. Meera Iyer',    'Case Doctor — Behavioural Care',   'case_doctor'],
    ['coordinator', 'Nisha Patel',       'Patient Care Coordinator',         'coordinator'],
];

$insertUser = $pdo->prepare(
    'INSERT INTO cms_users (username, name, email, title, password_hash, role, status, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
);
$findUser = $pdo->prepare('SELECT id FROM cms_users WHERE username = ? LIMIT 1');

foreach ($staff as [$username, $name, $title, $role]) {
    $findUser->execute([$username]);
    if ($findUser->fetch()) {
        continue;
    }
    $insertUser->execute([
        $username,
        $name,
        $username . '@samvedna.local',
        $title,
        password_hash('samvedna123', PASSWORD_DEFAULT),
        $role,
        'active',
    ]);
    out("Staff account created: {$username} ({$role})");
}

// --- 4. Seed the plan catalogue -------------------------------------------
// Prices, durations and cadences come straight from the approved page flow.
$plans = [
    [
        'code' => 'starter', 'name' => 'Starter', 'tagline' => 'Short assessment cycle',
        'price' => 14999, 'duration_months' => 2, 'review_count' => 1,
        'review_interval_days' => 60, 'adherence_interval_days' => 15, 'reschedule_window_days' => 7,
        'requires_senior' => 0, 'requires_founder' => 0, 'founder_interval_days' => 0,
        'clinical_cover' => 'Case Doctor only',
        'features' => ['2 months of care', '1 formal review', 'Adherence check every 15 days', 'Parent progress report at cycle close'],
        'highlight' => 0, 'sort_order' => 1,
    ],
    [
        'code' => 'standard', 'name' => 'Standard', 'tagline' => 'Most preferred',
        'price' => 39999, 'duration_months' => 6, 'review_count' => 3,
        'review_interval_days' => 60, 'adherence_interval_days' => 15, 'reschedule_window_days' => 7,
        'requires_senior' => 1, 'requires_founder' => 0, 'founder_interval_days' => 0,
        'clinical_cover' => 'Case Doctor + Senior Doctor',
        'features' => ['6 months of care', '3 bi-monthly formal reviews', 'Senior Doctor sign-off on every review', 'Adherence check every 15 days', 'Progress dashboard shared each cycle'],
        'highlight' => 1, 'sort_order' => 2,
    ],
    [
        'code' => 'premium', 'name' => 'Premium', 'tagline' => 'Founder oversight',
        'price' => 54999, 'duration_months' => 6, 'review_count' => 6,
        'review_interval_days' => 30, 'adherence_interval_days' => 15, 'reschedule_window_days' => 7,
        'requires_senior' => 1, 'requires_founder' => 1, 'founder_interval_days' => 90,
        'clinical_cover' => 'Case + Senior Doctor, quarterly Founder review',
        'features' => ['6 months of care', 'Monthly formal reviews', 'Quarterly Founder review', 'Senior Doctor sign-off on every review', 'Priority escalation handling'],
        'highlight' => 0, 'sort_order' => 3,
    ],
];

$findPlan = $pdo->prepare('SELECT id FROM cms_plans WHERE code = ? LIMIT 1');
$insertPlan = $pdo->prepare(
    'INSERT INTO cms_plans
        (code, name, tagline, price, currency, duration_months, review_count, review_interval_days,
         adherence_interval_days, reschedule_window_days, requires_senior, requires_founder,
         founder_interval_days, clinical_cover, features, highlight, sort_order, status, created_at, updated_at)
     VALUES (?, ?, ?, ?, "INR", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "active", NOW(), NOW())'
);

foreach ($plans as $p) {
    $findPlan->execute([$p['code']]);
    if ($findPlan->fetch()) {
        continue;
    }
    $insertPlan->execute([
        $p['code'], $p['name'], $p['tagline'], $p['price'], $p['duration_months'], $p['review_count'],
        $p['review_interval_days'], $p['adherence_interval_days'], $p['reschedule_window_days'],
        $p['requires_senior'], $p['requires_founder'], $p['founder_interval_days'], $p['clinical_cover'],
        json_encode($p['features'], JSON_UNESCAPED_UNICODE), $p['highlight'], $p['sort_order'],
    ]);
    out("Plan created: {$p['name']} (₹" . number_format($p['price']) . ')');
}

// --- 5. Demo content -------------------------------------------------------
if ($seedDemo) {
    require __DIR__ . '/seed-demo.php';
    cms_seed_demo($pdo);
} else {
    out('');
    out('Skipped demo content. Add --seed-demo to populate the walkthrough case.');
}

out('');
out('Done. Next:');
out('  php -S 127.0.0.1:8001 -t samvedna-cms/backend samvedna-cms/backend/router.php');
out('  cd samvedna-cms/frontend && npm install && npm run dev');
out('');
out('Sign in with founder / senior / casedoctor / coordinator — password samvedna123');
