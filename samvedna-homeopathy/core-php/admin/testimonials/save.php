<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/VideoTestimonial.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('testimonials'));
}
verify_csrf();

$id     = req_int('id');
$isEdit = $id > 0;

$name      = post('name');
$condition = post('condition_label');

$errors = [];
if ($name === '')      { $errors[] = 'Name is required.'; }
if ($condition === '') { $errors[] = 'Condition is required.'; }

if ($errors) {
    flash('error', implode(' ', $errors));
    redirect($isEdit ? au('testimonials/edit/' . $id) : au('testimonials/new'));
}

$existing = $isEdit ? (VideoTestimonial::find($id)['poster'] ?? '') : '';
$uploadError = null;
$poster = handle_image_upload('poster', $existing, $uploadError);
if ($uploadError) {
    flash('error', $uploadError);
    redirect($isEdit ? au('testimonials/edit/' . $id) : au('testimonials/new'));
}
if ($poster === $existing) {
    $pathField = post('poster_path');
    if ($pathField !== '') {
        $poster = $pathField;
    }
}

// Accept either a raw ID or a full YouTube URL.
$youtube = post('youtube_id');
if ($youtube !== '' && preg_match('~(?:youtu\.be/|v=|/embed/)([A-Za-z0-9_-]{6,})~', $youtube, $m)) {
    $youtube = $m[1];
}

$data = [
    'name'            => $name,
    'condition_label' => $condition,
    'location'        => post('location'),
    'youtube_id'      => $youtube,
    'poster'          => $poster,
    'alt'             => post('alt'),
    'duration'        => post('duration'),
    'sort_order'      => req_int('sort_order'),
    'status'          => post('status') === 'draft' ? 'draft' : 'published',
];

if ($isEdit) {
    VideoTestimonial::update($id, $data);
    flash('success', 'Testimonial updated.');
} else {
    VideoTestimonial::create($data);
    flash('success', 'Testimonial created.');
}

redirect(au('testimonials'));
