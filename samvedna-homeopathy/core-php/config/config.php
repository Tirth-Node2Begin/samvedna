<?php
/**
 * Samvedna Homeopathy — Core PHP backend configuration.
 *
 * Central place for database credentials, paths and app settings. Values fall
 * back to XAMPP defaults and match the Next.js app's DB (see ../.env.local),
 * so the PHP admin and the Next site share one database.
 *
 * Configuration sources, highest precedence first:
 *   1. core-php/config/config.local.php (machine-specific PHP override)
 *   2. .env file next to the core-php root (on the live docroot: /.env,
 *      shipped by scripts/build-live.ps1 from core-php/.env.production)
 *   3. real environment variables (getenv)
 *   4. the defaults below (XAMPP root/no-password)
 */

declare(strict_types=1);

// Load optional local overrides.
$overrides = [];
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $loaded = require $localFile;
    if (is_array($loaded)) {
        $overrides = $loaded;
    }
}

// Load .env (deploy config). PHP has no built-in dotenv, so parse the simple
// KEY=VALUE format here: blank lines and #-comments skipped, optional single or
// double quotes around the value stripped. config.local.php keys win over .env.
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $len = strlen($value);
        if ($len >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[$len - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && !array_key_exists($key, $overrides)) {
            $overrides[$key] = $value;
        }
    }
}

/** Small helper: override value -> environment variable -> default. */
function sam_cfg(string $key, string $default, array $overrides): string
{
    if (array_key_exists($key, $overrides)) {
        return (string) $overrides[$key];
    }
    $env = getenv($key);
    return ($env !== false && $env !== '') ? $env : $default;
}

// --- Database ---
define('DB_HOST', sam_cfg('DB_HOST', 'localhost', $overrides));
define('DB_PORT', sam_cfg('DB_PORT', '3306', $overrides));
define('DB_NAME', sam_cfg('DB_NAME', 'samvedna_homeopathy', $overrides));
define('DB_USER', sam_cfg('DB_USER', 'root', $overrides));
define('DB_PASS', sam_cfg('DB_PASS', '', $overrides));
define('DB_CHARSET', 'utf8mb4');

// --- Paths ---
define('CORE_PATH', dirname(__DIR__));                 // .../core-php
define('UPLOAD_DIR', CORE_PATH . '/uploads');          // filesystem path for uploads
define('UPLOAD_URL_PATH', '/uploads');                 // URL path (served by php built-in server)

// --- App ---
define('APP_NAME', 'Samvedna Homeopathy Admin');
// Root-absolute URL prefix for the admin panel. The admin is branded as
// /samvedna: every link, form action and redirect the admin emits is built from
// this via au(). The on-disk folder is still admin/ — router.php (dev) and the
// live .htaccess map the /samvedna URL prefix onto it. Keep it consistent with
// the /samvedna routing in core-php/router.php and deploy/htaccess-site.conf.
define('ADMIN_BASE', '/samvedna');
// Base URL under which the admin is reached. When the Next.js dev server proxies
// /samvedna -> PHP (see next.config.ts), links stay relative so this is mostly for
// building absolute upload URLs in the API. Adjust if hosted elsewhere.
define('PUBLIC_BASE_URL', sam_cfg('PHP_ADMIN_URL', 'http://localhost:8080', $overrides));

// --- Uploads ---
define('MAX_UPLOAD_BYTES', 4 * 1024 * 1024); // 4 MB
$GLOBALS['ALLOWED_IMAGE_TYPES'] = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

// Timezone (India).
date_default_timezone_set('Asia/Kolkata');

// Show errors during development; silence display in production by setting
// APP_ENV=production in .env (or the environment).
if (sam_cfg('APP_ENV', '', $overrides) === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
