<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Doctor.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('doctors'));
}
verify_csrf();

$id = req_int('id');
if ($id > 0 && Doctor::find($id)) {
    Doctor::delete($id);
    flash('success', 'Doctor profile deleted.');
} else {
    flash('error', 'Doctor not found.');
}

redirect(au('doctors'));
