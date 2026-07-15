<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Blog.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('blogs'));
}
verify_csrf();

$id     = req_int('id');
$isEdit = $id > 0;

$title    = post('title');
$category = post('category');

// Validation.
$errors = [];
if ($title === '')    { $errors[] = 'Title is required.'; }
if ($category === '') { $errors[] = 'Category is required.'; }

if ($errors) {
    flash('error', implode(' ', $errors));
    redirect($isEdit ? au('blogs/edit/' . $id) : au('blogs/new'));
}

// Slug: use provided, else derive from title; ensure unique.
$slug = post('slug');
$slug = $slug !== '' ? slugify($slug) : slugify($title);
$slug = Blog::uniqueSlug($slug, $id);

// Image: uploaded file wins, else the manual path field, else existing.
$existing = $isEdit ? (Blog::find($id)['image'] ?? '') : '';
$uploadError = null;
$image = handle_image_upload('image', $existing, $uploadError);
if ($uploadError) {
    flash('error', $uploadError);
    redirect($isEdit ? au('blogs/edit/' . $id) : au('blogs/new'));
}
// If no new upload changed it, allow the manual path field to set/override.
if ($image === $existing) {
    $pathField = post('image_path');
    if ($pathField !== '') {
        $image = $pathField;
    }
}

$data = [
    'slug'         => $slug,
    'title'        => $title,
    'excerpt'      => post('excerpt'),
    'content'      => $_POST['content'] ?? '',
    'category'     => $category,
    'author'       => post('author'),
    'image'        => $image,
    'alt'          => post('alt'),
    'read_time'    => post('read_time'),
    'status'       => post('status') === 'published' ? 'published' : 'draft',
    'published_at' => post('published_at'),
];

if ($isEdit) {
    Blog::update($id, $data);
    flash('success', 'Blog post updated.');
} else {
    Blog::create($data);
    flash('success', 'Blog post created.');
}

redirect(au('blogs'));
