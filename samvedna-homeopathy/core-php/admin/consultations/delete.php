<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Consultation.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('consultations'));
}
verify_csrf();

$id = req_int('id');
if ($id > 0 && Consultation::find($id)) {
    Consultation::delete($id);
    flash('success', 'Assessment deleted.');
} else {
    flash('error', 'Assessment not found.');
}

redirect(au('consultations'));
