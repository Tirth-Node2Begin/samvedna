<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Doctor.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('doctors'));
}
verify_csrf();

$id     = req_int('id');
$isEdit = $id > 0;

$name = post('name');
if ($name === '') {
    flash('error', 'Name is required.');
    redirect($isEdit ? au('doctors/edit/' . $id) : au('doctors/new'));
}

$existing = $isEdit ? (Doctor::find($id)['image'] ?? '') : '';
$uploadError = null;
$image = handle_image_upload('image', $existing, $uploadError);
if ($uploadError) {
    flash('error', $uploadError);
    redirect($isEdit ? au('doctors/edit/' . $id) : au('doctors/new'));
}
if ($image === $existing) {
    $pathField = post('image_path');
    if ($pathField !== '') {
        $image = $pathField;
    }
}

$data = [
    'name'            => $name,
    'title'           => post('title'),
    'credential'      => post('credential'),
    'image'           => $image,
    'alt'             => post('alt'),
    'specialization'  => post('specialization'),
    'experience'      => post('experience'),
    'summary'         => post('summary'),
    'about'           => post('about'),
    'qualifications'  => lines_to_array(post('qualifications')),
    'specializations' => lines_to_array(post('specializations')),
    'treatments'      => lines_to_array(post('treatments')),
    'certifications'  => lines_to_array(post('certifications')),
    'awards'          => lines_to_array(post('awards')),
    'languages'       => lines_to_array(post('languages')),
    'consultation'    => post('consultation'),
    'sort_order'      => req_int('sort_order'),
    'status'          => post('status') === 'draft' ? 'draft' : 'published',
];

if ($isEdit) {
    Doctor::update($id, $data);
    flash('success', 'Doctor profile updated.');
} else {
    Doctor::create($data);
    flash('success', 'Doctor profile created.');
}

redirect(au('doctors'));
