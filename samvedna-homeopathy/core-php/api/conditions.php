<?php
/**
 * GET /api/conditions.php — published conditions, shaped for the Next.js
 * `Condition` type used by the homepage "Conditions we support" grid.
 */

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../includes/models/Condition.php';

$rows = Condition::all(true); // published only

$out = array_map(static function (array $c): array {
    return [
        'id'          => (int) $c['id'],
        'name'        => $c['name'],
        'description' => $c['description'] ?? '',
        'image'       => public_asset_url($c['image'] ?? ''),
        'alt'         => $c['alt'] ?? '',
        'span'        => $c['span'] ?? 'standard',
    ];
}, $rows);

json_response($out);
