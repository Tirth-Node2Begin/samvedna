<?php
/**
 * Front controller for the PHP built-in server, so the API has clean URLs:
 *
 *   php -S 127.0.0.1:8001 -t samvedna-cms/backend samvedna-cms/backend/router.php
 *
 * Everything under /api is handed to api/index.php with the remaining path in
 * $_GET['_route']; real files (uploads, images) are served as-is.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

$docroot = __DIR__;
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = urldecode($path);

if (strpos($path, '..') !== false) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'bad_request']);
    return true;
}

// API routes.
if ($path === '/api' || strpos($path, '/api/') === 0) {
    $_GET['_route'] = substr($path, 4); // keep everything after "/api"
    require $docroot . '/api/index.php';
    return true;
}

// Uploaded files and any other real asset.
if ($path !== '/' && is_file($docroot . $path)) {
    return false; // let the built-in server handle it
}

// Health check / landing.
if ($path === '/' || $path === '/index.php') {
    require $docroot . '/index.php';
    return true;
}

http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'error'   => 'not_found',
    'message' => 'Samvedna CMS backend: only /api/* is served here.',
]);
return true;
