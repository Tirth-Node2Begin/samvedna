<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

logout();
// After logging out, send the admin back to the public website homepage
// rather than the admin login screen.
redirect('/');
