<?php
/**
 * GET /api/blogs.php — published blog posts, shaped for the Next.js `BlogPost` type.
 */

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../includes/models/Blog.php';

$rows = Blog::all(true); // published only

$out = array_map(static function (array $b): array {
    $date = $b['published_at'] ? date('M j, Y', strtotime($b['published_at'])) : '';
    return [
        'id'       => (int) $b['id'],
        'slug'     => $b['slug'],
        'title'    => $b['title'],
        'excerpt'  => $b['excerpt'] ?? '',
        'content'  => $b['content'] ?? '',
        'image'    => public_asset_url($b['image'] ?? ''),
        'alt'      => $b['alt'] ?? '',
        'category' => $b['category'] ?? '',
        'date'     => $date,
        'readTime' => $b['read_time'] ?? '',
        'href'     => '/blog/' . $b['slug'],
    ];
}, $rows);

json_response($out);
