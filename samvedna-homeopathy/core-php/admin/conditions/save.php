<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Condition.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('conditions'));
}
verify_csrf();

$id     = req_int('id');
$isEdit = $id > 0;

$name = post('name');
if ($name === '') {
    flash('error', 'Name is required.');
    redirect($isEdit ? au('conditions/edit/' . $id) : au('conditions/new'));
}

$existing = $isEdit ? (Condition::find($id)['image'] ?? '') : '';
$uploadError = null;
$image = handle_image_upload('image', $existing, $uploadError);
if ($uploadError) {
    flash('error', $uploadError);
    redirect($isEdit ? au('conditions/edit/' . $id) : au('conditions/new'));
}
// No new file uploaded: the path field is authoritative, so clearing it removes
// the image and the card falls back to its icon.
if ($image === $existing) {
    $image = post('image_path');
}

$data = [
    'name'        => $name,
    'description' => post('description'),
    'image'       => $image,
    'alt'         => post('alt'),
    'span'        => post('span'),
    'sort_order'  => req_int('sort_order'),
    'status'      => post('status') === 'draft' ? 'draft' : 'published',
];

if ($isEdit) {
    Condition::update($id, $data);
    flash('success', 'Condition updated.');
} else {
    Condition::create($data);
    flash('success', 'Condition created.');
}

redirect(au('conditions'));
