<?php
/**
 * Session-based admin authentication.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/** Attempt to log in with username/password. Returns true on success. */
function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, username, name, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    boot_session();
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'       => (int) $admin['id'],
        'username' => $admin['username'],
        'name'     => $admin['name'],
    ];

    return true;
}

function logout(): void
{
    boot_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function current_admin(): ?array
{
    boot_session();
    return $_SESSION['admin'] ?? null;
}

function is_logged_in(): bool
{
    return current_admin() !== null;
}

/**
 * Guard for admin pages. Redirects to the login page when not authenticated.
 * The $depth parameter is kept for backward compatibility but unused — links
 * are now root-absolute so they work behind the Next.js proxy too.
 */
function require_login(int $depth = 0): void
{
    if (is_logged_in()) {
        return;
    }
    redirect(au('login'));
}
