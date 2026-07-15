<?php
/**
 * GET /api/testimonials.php — published video testimonials, shaped for the
 * Next.js `VideoTestimonial` type.
 */

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../includes/models/VideoTestimonial.php';

$rows = VideoTestimonial::all(true); // published only

$out = array_map(static function (array $v): array {
    return [
        'id'        => (int) $v['id'],
        'youtubeId' => $v['youtube_id'] ?? '',
        'poster'    => public_asset_url($v['poster'] ?? ''),
        'alt'       => $v['alt'] ?? '',
        'name'      => $v['name'] ?? '',
        'condition' => $v['condition_label'] ?? '',
        'location'  => $v['location'] ?? '',
        'duration'  => $v['duration'] ?? '',
    ];
}, $rows);

json_response($out);
