<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Condition.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('conditions'));
}
verify_csrf();

$id = req_int('id');
if ($id > 0 && Condition::find($id)) {
    Condition::delete($id);
    flash('success', 'Condition deleted.');
} else {
    flash('error', 'Condition not found.');
}

redirect(au('conditions'));
