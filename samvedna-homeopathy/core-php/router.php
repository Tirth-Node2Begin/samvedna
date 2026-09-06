<?php
/**
 * Front-controller / router for the PHP built-in server so the admin can use
 * clean, extension-less URLs:
 *
 *   /samvedna                       -> dashboard
 *   /samvedna/login | /samvedna/logout
 *   /samvedna/blogs                 -> list
 *   /samvedna/blogs/new             -> create form
 *   /samvedna/blogs/edit/{id}       -> edit form
 *   /samvedna/blogs/save   (POST)   -> insert/update
 *   /samvedna/blogs/delete (POST)   -> delete
 *
 * The admin URL prefix is ADMIN_BASE (/samvedna); the folder on disk is admin/.
 *   ...same for testimonials and doctors
 *
 * Start the server with this router:
 *   php -S 127.0.0.1:8000 -t core-php core-php/router.php   (from project root)
 *
 * Real files (CSS, uploads, /api/*.php, images) are served/executed as-is.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php'; // for ADMIN_BASE

$docroot = __DIR__;      // core-php
$base    = ADMIN_BASE;   // admin URL prefix, e.g. /samvedna (folder on disk is admin/)
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = urldecode($path);

// Basic traversal guard (dev server only, but be safe).
if (strpos($path, '..') !== false) {
    http_response_code(400);
    echo 'Bad request';
    return true;
}

$MIMES = [
    'webp' => 'image/webp', 'png' => 'image/png', 'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'svg' => 'image/svg+xml',
    'ico'  => 'image/x-icon', 'css' => 'text/css', 'js' => 'application/javascript',
    'woff2' => 'font/woff2', 'json' => 'application/json',
];

if (!function_exists('serve_static')) {
    function serve_static(string $file, array $mimes): void
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($mimes[$ext])) {
            header('Content-Type: ' . $mimes[$ext]);
        }
        readfile($file);
    }
}

// 1) Serve any real, existing file directly (CSS, images, uploads, /api/*.php).
if ($path !== '/' && is_file($docroot . $path)) {
    return false; // let the built-in server serve/execute it
}

// 1b) Admin static assets (e.g. /samvedna/assets/admin.css) live on disk under
// admin/, but are addressed under the ADMIN_BASE prefix. Map the prefix to the
// folder so the CSS/images load whether accessed directly or via the dev proxy.
if ($path !== '/' && strpos($path, $base . '/') === 0) {
    $assetFile = $docroot . '/admin' . substr($path, strlen($base));
    if (is_file($assetFile)) {
        serve_static($assetFile, $MIMES);
        return true;
    }
}

// Also serve Next.js public assets (e.g. /images/samvedna-logo.webp) when accessing PHP server directly on 8080
if ($path !== '/' && is_file($docroot . '/../public' . $path)) {
    $file = $docroot . '/../public' . $path;
    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'webp' => 'image/webp',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($file);
    return true;
}

function render_404(): bool {
    http_response_code(404);
    echo <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>404 Not Found - Samvedna Admin</title>
  <style>
    body {
      display: grid;
      min-height: 100vh;
      place-items: center;
      margin: 0;
      padding: 24px;
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.10), transparent 34%), #f8fafc;
      color: #0f172a;
      font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .box {
      width: min(100%, 420px);
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #ffffff;
      box-shadow: 0 18px 45px rgba(12, 79, 47, 0.08);
      padding: 36px;
      text-align: center;
    }
    img {
      width: 180px;
      height: auto;
      margin-bottom: 18px;
    }
    h1 {
      margin: 0 0 10px;
      font-size: 24px;
    }
    p {
      margin: 0;
      color: #64748b;
      line-height: 1.55;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-top: 24px;
      border-radius: 6px;
      background: #2563eb;
      color: #ffffff;
      font-weight: 700;
      padding: 12px 18px;
      text-decoration: none;
    }
    .btn:hover {
      background: #1d4ed8;
    }
  </style>
</head>
<body>
  <div class="box">
    <img src="/images/samvedna-logo.webp" alt="Samvedna Homeopathy">
    <h1>Page Not Found</h1>
    <p>The admin page or resource you requested could not be found.</p>
    <a href="/samvedna" class="btn">Return to Dashboard</a>
  </div>
</body>
</html>
HTML;
    return true;
}

// Root of the site -> send to the admin.
if ($path === '/' ) {
    header('Location: ' . $base);
    return true;
}

// Dashboard: /samvedna or /samvedna/
if ($path === $base || $path === $base . '/') {
    require $docroot . '/admin/index.php';
    return true;
}

// Everything else must live under the admin base (/samvedna/).
if (strpos($path, $base . '/') !== 0) {
    return render_404();
}

$route = trim(substr($path, strlen($base)), '/'); // e.g. "blogs/edit/3"
$seg   = $route === '' ? [] : explode('/', $route);

// Top-level admin actions.
$top = [
    'login'   => '/admin/login.php',
    'logout'  => '/admin/logout.php',
    'profile' => '/admin/profile.php',
];
if (count($seg) === 1 && isset($top[$seg[0]])) {
    require $docroot . $top[$seg[0]];
    return true;
}

// Resource routes.
$resources = ['blogs', 'testimonials', 'doctors', 'conditions', 'leads', 'consultations'];
if (isset($seg[0]) && in_array($seg[0], $resources, true)) {
    $dir    = $docroot . '/admin/' . $seg[0] . '/';
    $action = $seg[1] ?? 'index';

    switch ($action) {
        case 'index':
            require $dir . 'index.php';
            return true;
        case 'new':
            require $dir . 'form.php';
            return true;
        case 'edit':
            if (isset($seg[2]) && ctype_digit($seg[2])) {
                $_GET['id'] = $seg[2];
                $_REQUEST['id'] = $seg[2];
            }
            require $dir . 'form.php';
            return true;
        case 'view':
            // Read-only detail page (used by consultations, which are not editable).
            if (isset($seg[2]) && ctype_digit($seg[2])) {
                $_GET['id'] = $seg[2];
                $_REQUEST['id'] = $seg[2];
            }
            require $dir . 'view.php';
            return true;
        case 'save':
            require $dir . 'save.php';
            return true;
        case 'delete':
            require $dir . 'delete.php';
            return true;
    }
}

return render_404();
