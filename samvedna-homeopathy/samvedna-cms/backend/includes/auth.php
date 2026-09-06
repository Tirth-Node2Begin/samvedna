<?php
/**
 * Session authentication and role guards for the CMS API.
 *
 * The Next.js dev server proxies /api to this backend, so the session cookie is
 * same-origin and no token juggling is needed on the client.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function cms_boot_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('samvedna_cms');
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_path'     => '/',
        ]);
    }
}

/** Attempt a login. Returns the public user row on success, null on failure. */
function cms_attempt_login(string $username, string $password): ?array
{
    $user = cms_one(
        'SELECT * FROM cms_users WHERE username = ? AND status = ? LIMIT 1',
        [$username, 'active']
    );

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }

    cms_boot_session();
    session_regenerate_id(true);
    $_SESSION['cms_user_id'] = (int) $user['id'];

    return cms_public_user($user);
}

function cms_logout(): void
{
    cms_boot_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** The signed-in user row, or null. */
function cms_user(): ?array
{
    static $cached = null;
    static $resolved = false;

    if ($resolved) {
        return $cached;
    }
    $resolved = true;

    cms_boot_session();
    $id = (int) ($_SESSION['cms_user_id'] ?? 0);
    if ($id <= 0) {
        return $cached = null;
    }

    return $cached = cms_one('SELECT * FROM cms_users WHERE id = ? AND status = ? LIMIT 1', [$id, 'active']);
}

/** Strip the password hash before a user row crosses the wire. */
function cms_public_user(?array $user): ?array
{
    if (!$user) {
        return null;
    }
    return [
        'id'        => (int) $user['id'],
        'username'  => $user['username'],
        'name'      => $user['name'],
        'email'     => $user['email'] ?? '',
        'phone'     => $user['phone'] ?? '',
        'title'     => $user['title'] ?? '',
        'role'      => $user['role'],
        'roleLabel' => cms_role_label($user['role']),
        'status'    => $user['status'],
    ];
}

/** Guard: 401 unless signed in. Returns the user row. */
function cms_require_auth(): array
{
    $user = cms_user();
    if (!$user) {
        cms_error('unauthenticated', 'Please sign in to continue.', 401);
    }
    return $user;
}

/** Guard: 403 unless the signed-in user holds one of $roles. */
function cms_require_role(array $roles): array
{
    $user = cms_require_auth();
    if (!in_array($user['role'], $roles, true)) {
        cms_error(
            'forbidden',
            'Your role (' . cms_role_label($user['role']) . ') cannot perform this action.',
            403
        );
    }
    return $user;
}

/** True when the user may act clinically (write reviews, escalate). */
function cms_is_clinical(?array $user): bool
{
    return $user !== null && in_array($user['role'], ['founder', 'senior_doctor', 'case_doctor'], true);
}

/** Append an entry to the audit trail. Never throws — logging must not break a write. */
function cms_log(string $entityType, ?int $entityId, string $action, string $summary = ''): void
{
    try {
        $user = cms_user();
        cms_run(
            'INSERT INTO cms_activity_log (user_id, actor_name, entity_type, entity_id, action, summary, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [
                $user ? (int) $user['id'] : null,
                $user['name'] ?? 'System',
                $entityType,
                $entityId,
                $action,
                mb_substr($summary, 0, 255),
            ]
        );
    } catch (Throwable $e) {
        // Swallow — the audit log is secondary to the operation that triggered it.
    }
}
