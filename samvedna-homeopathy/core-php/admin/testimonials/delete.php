<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/VideoTestimonial.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('testimonials'));
}
verify_csrf();

$id = req_int('id');
if ($id > 0 && VideoTestimonial::find($id)) {
    VideoTestimonial::delete($id);
    flash('success', 'Testimonial deleted.');
} else {
    flash('error', 'Testimonial not found.');
}

redirect(au('testimonials'));
