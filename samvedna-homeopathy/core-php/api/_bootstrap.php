<?php
/**
 * Shared bootstrap for public API endpoints.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Basic CORS for GET requests (public, read-only data).
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/**
 * Resolve a stored image path to a URL the Next.js frontend can load.
 * Both /images/... (Next /public) and /uploads/... (proxied to PHP via the
 * `/uploads/:path*` rewrite in next.config.ts) are returned as root-relative
 * paths, so they are same-origin and work with next/image without extra config.
 */
function public_asset_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    // Already a full URL? leave as-is.
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    // Ensure a leading slash for stored relative paths.
    return '/' . ltrim($path, '/');
}
