<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Lead.php';

require_login(1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(au('leads'));
}
verify_csrf();

$id = req_int('id');
if ($id > 0 && Lead::find($id)) {
    Lead::delete($id);
    flash('success', 'Lead deleted.');
} else {
    flash('error', 'Lead not found.');
}

// Preserve the source filter the admin was viewing, if any.
$source = $_POST['source'] ?? '';
$suffix = in_array($source, ['website', 'popup'], true) ? '?source=' . $source : '';

redirect(au('leads' . $suffix));
