<?php
/**
 * Shared helper functions: escaping, CSRF, flash messages, slugs, uploads, JSON.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

/** Escape a value for safe HTML output. */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Start the session once. */
function boot_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

/** Redirect and stop execution. Prefer absolute paths (e.g. au('blogs/index.php')). */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Build a root-absolute admin URL, e.g. au('blogs/form.php') => /admin/blogs/form.php.
 * Absolute paths keep links working both directly and behind the Next.js proxy.
 */
function au(string $path = ''): string
{
    return ADMIN_BASE . '/' . ltrim($path, '/');
}

/* ------------------------------------------------------------------ CSRF -- */

function csrf_token(): string
{
    boot_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verify a submitted token; aborts on failure. */
function verify_csrf(): void
{
    boot_session();
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('Invalid or expired form token. Please go back and try again.');
    }
}

/* ---------------------------------------------------------------- Flash --- */

function flash(string $type, string $message): void
{
    boot_session();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Return and clear queued flash messages. */
function take_flashes(): array
{
    boot_session();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* ----------------------------------------------------------------- Misc --- */

/** Read a trimmed string from POST. */
function post(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

/** Read an int from GET/POST. */
function req_int(string $key, int $default = 0): int
{
    $value = $_REQUEST[$key] ?? $default;
    return (int) $value;
}

/** Convert a title into a URL slug. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

/**
 * Turn a textarea (one item per line) into a clean array, and vice-versa.
 */
function lines_to_array(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
    $items = array_map('trim', $lines);
    return array_values(array_filter($items, static fn($v) => $v !== ''));
}

function array_to_lines($value): string
{
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        $value = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($value)) {
        return '';
    }
    return implode("\n", $value);
}

/** Decode a JSON column into an array (safe). */
function json_col($value): array
{
    if (is_array($value)) {
        return $value;
    }
    if (is_string($value) && $value !== '') {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
    return [];
}

/* --------------------------------------------------------------- Uploads -- */

/**
 * Handle an optional image upload from $_FILES[$field].
 *
 * @param string      $field   The form field name.
 * @param string|null $current Existing stored path (returned when no new file).
 * @param string|null $error   Populated with a message on failure.
 * @return string|null Stored web path (e.g. /uploads/abc.webp) or $current.
 */
function handle_image_upload(string $field, ?string $current, ?string &$error): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $current; // nothing uploaded — keep existing
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed (error code ' . (int) $file['error'] . ').';
        return $current;
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        $error = 'Image is too large. Maximum size is ' . (int) (MAX_UPLOAD_BYTES / 1024 / 1024) . ' MB.';
        return $current;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = $GLOBALS['ALLOWED_IMAGE_TYPES'];

    if (!isset($allowed[$mime])) {
        $error = 'Unsupported image type. Please upload a JPG, PNG, WEBP or GIF.';
        return $current;
    }

    if (!is_dir(UPLOAD_DIR)) {
        @mkdir(UPLOAD_DIR, 0775, true);
    }

    $ext      = $allowed[$mime];
    $filename = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
    $dest     = UPLOAD_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $error = 'Could not save the uploaded file.';
        return $current;
    }

    return UPLOAD_URL_PATH . '/' . $filename;
}

/** Resolve a stored image path to a URL usable from the admin pages. */
function image_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    // Uploaded files live under the PHP doc root; served images from Next.js
    // /public start with /images. Both are fine as-is when the admin is proxied.
    return $path;
}

/** Send a JSON response and exit (used by the public API). */
function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: public, max-age=60');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
