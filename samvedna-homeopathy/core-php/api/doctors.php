<?php
/**
 * GET /api/doctors.php — published doctors, shaped for the Next.js `TeamMember`
 * type (with a nested `profile` object).
 */

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../includes/models/Doctor.php';

$rows = Doctor::all(true); // published only

$out = array_map(static function (array $d): array {
    return [
        'id'             => (int) $d['id'],
        'name'           => $d['name'],
        'title'          => $d['title'] ?? '',
        'credential'     => $d['credential'] ?? '',
        'image'          => public_asset_url($d['image'] ?? ''),
        'alt'            => $d['alt'] ?? '',
        'specialization' => $d['specialization'] ?? '',
        'experience'     => $d['experience'] ?? '',
        'summary'        => $d['summary'] ?? '',
        'profile'        => [
            'qualifications'  => json_col($d['qualifications']),
            'about'           => $d['about'] ?? '',
            'specializations' => json_col($d['specializations']),
            'treatments'      => json_col($d['treatments']),
            'certifications'  => json_col($d['certifications']),
            'awards'          => json_col($d['awards']),
            'languages'       => json_col($d['languages']),
            'consultation'    => $d['consultation'] ?? '',
        ],
    ];
}, $rows);

json_response($out);
