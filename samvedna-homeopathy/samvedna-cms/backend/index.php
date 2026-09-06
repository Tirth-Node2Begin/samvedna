<?php
/**
 * Health check for the CMS backend. The UI lives in the Next.js frontend; this
 * document root only serves /api.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$tables = 0;
$migrated = false;
try {
    $tables = (int) cms_scalar(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name LIKE ?',
        [CMS_DB_NAME, 'cms\_%']
    );
    $migrated = $tables > 0;
} catch (Throwable $e) {
    // cms_db() has already emitted a JSON error if the connection itself failed.
}

cms_json([
    'app'      => CMS_APP_NAME,
    'status'   => $migrated ? 'ready' : 'not_migrated',
    'demo'     => CMS_DEMO,
    'database' => CMS_DB_NAME,
    'tables'   => $tables,
    'api'      => '/api',
    'hint'     => $migrated
        ? 'Backend is ready. Start the frontend with: cd samvedna-cms/frontend && npm run dev'
        : 'Run: php samvedna-cms/backend/migrations/migrate.php --seed-demo',
]);
