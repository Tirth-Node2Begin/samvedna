<?php
/**
 * Samvedna CMS — Core PHP backend configuration.
 *
 * Mirrors the configuration conventions already used by core-php/ so both
 * backends can be deployed the same way. Sources, highest precedence first:
 *   1. backend/config/config.local.php (machine-specific PHP override)
 *   2. backend/.env (KEY=VALUE, shipped with the deploy)
 *   3. real environment variables
 *   4. the XAMPP-friendly defaults below
 */

declare(strict_types=1);

$overrides = [];
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $loaded = require $localFile;
    if (is_array($loaded)) {
        $overrides = $loaded;
    }
}

// PHP has no built-in dotenv; parse the simple KEY=VALUE format here. Blank
// lines and #-comments are skipped, surrounding quotes stripped.
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

/** Override value -> environment variable -> default. */
function cms_cfg(string $key, string $default, array $overrides): string
{
    if (array_key_exists($key, $overrides)) {
        return (string) $overrides[$key];
    }
    $env = getenv($key);
    return ($env !== false && $env !== '') ? $env : $default;
}

// --- Database -------------------------------------------------------------
// Defaults to the same database the marketing site uses; every CMS table is
// prefixed `cms_` so the two coexist. Point DB_NAME elsewhere to separate them.
//
// Use TCP loopback by default. On Windows/XAMPP, PDO MySQL connections to
// `localhost` can spend ~2s resolving/choosing a transport per request.
define('CMS_DB_HOST', cms_cfg('DB_HOST', '127.0.0.1', $overrides));
define('CMS_DB_PORT', cms_cfg('DB_PORT', '3306', $overrides));
define('CMS_DB_NAME', cms_cfg('DB_NAME', 'samvedna_homeopathy', $overrides));
define('CMS_DB_USER', cms_cfg('DB_USER', 'root', $overrides));
define('CMS_DB_PASS', cms_cfg('DB_PASS', '', $overrides));
define('CMS_DB_CHARSET', 'utf8mb4');

// --- Paths ----------------------------------------------------------------
define('CMS_ROOT', dirname(__DIR__));
define('CMS_UPLOAD_DIR', CMS_ROOT . '/uploads');
define('CMS_UPLOAD_URL', '/uploads');

// --- App ------------------------------------------------------------------
define('CMS_APP_NAME', 'Samvedna CMS');

// Demo mode keeps the seeded walkthrough safe to hand to a client: the login
// screen advertises the demo accounts and destructive actions are soft. Set
// CMS_DEMO=0 in .env for a real deployment.
define('CMS_DEMO', cms_cfg('CMS_DEMO', '1', $overrides) === '1');

// Comma-separated origins allowed to send credentialed requests. Only needed
// when the Next.js app is NOT proxying /api (see frontend/next.config.ts).
define('CMS_CORS_ORIGINS', cms_cfg('CMS_CORS_ORIGINS', 'http://localhost:3001,http://127.0.0.1:3001', $overrides));

$appEnv = cms_cfg('APP_ENV', 'development', $overrides);
define('CMS_APP_ENV', $appEnv);

date_default_timezone_set('Asia/Kolkata');

if ($appEnv === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
