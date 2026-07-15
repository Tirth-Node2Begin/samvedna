<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Blog.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('blogs'));
}
verify_csrf();

$id = req_int('id');
if ($id > 0 && Blog::find($id)) {
    Blog::delete($id);
    flash('success', 'Blog post deleted.');
} else {
    flash('error', 'Blog post not found.');
}

redirect(au('blogs'));
