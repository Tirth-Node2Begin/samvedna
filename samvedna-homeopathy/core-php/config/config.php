<?php
/**
 * Samvedna Homeopathy — Core PHP backend configuration.
 *
 * Central place for database credentials, paths and app settings. Values fall
 * back to XAMPP defaults and match the Next.js app's DB (see ../.env.local),
 * so the PHP admin and the Next site share one database.
 *
 * To override for a specific machine without editing this file, create
 * core-php/config/config.local.php returning an associative array, e.g.:
 *   <?php return ['DB_PASS' => 'secret'];
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
// Root-absolute URL prefix for the admin. Using absolute paths (/admin/...) keeps
// links and redirects correct whether the admin is opened directly on the PHP
// server (localhost:8080/admin) or proxied through Next.js (localhost:3000/admin).
define('ADMIN_BASE', '/admin');
// Base URL under which the admin is reached. When the Next.js dev server proxies
// /admin -> PHP (see next.config.ts), links stay relative so this is mostly for
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
// APP_ENV=production in the environment.
if (getenv('APP_ENV') === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
