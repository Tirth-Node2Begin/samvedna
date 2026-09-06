<?php
/**
 * Admin profile: update account details (display name / username) and change
 * the sign-in password. GET renders the two forms; POST processes whichever
 * form was submitted (distinguished by a hidden `form` field) and redirects
 * back with a flash message (post/redirect/get).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

require_login();

$adminId = (int) (current_admin()['id'] ?? 0);

// Always work from a fresh DB record (the session only caches id/name/username).
$stmt = db()->prepare('SELECT id, username, name, password_hash, created_at FROM admins WHERE id = ? LIMIT 1');
$stmt->execute([$adminId]);
$me = $stmt->fetch();

if (!$me) {
    // The signed-in admin no longer exists — force a clean re-login.
    logout();
    redirect(au('login'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form = post('form');

    if ($form === 'details') {
        $name     = post('name');
        $username = post('username');

        if ($name === '' || $username === '') {
            flash('error', 'Display name and username are both required.');
        } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username)) {
            flash('error', 'Username must be 3–64 characters: letters, numbers, dot, dash or underscore.');
        } else {
            $check = db()->prepare('SELECT id FROM admins WHERE username = ? AND id <> ? LIMIT 1');
            $check->execute([$username, $adminId]);
            if ($check->fetch()) {
                flash('error', 'That username is already taken. Please choose another.');
            } else {
                $upd = db()->prepare('UPDATE admins SET name = ?, username = ? WHERE id = ?');
                $upd->execute([$name, $username, $adminId]);
                $_SESSION['admin']['name']     = $name;
                $_SESSION['admin']['username'] = $username;
                flash('success', 'Account details updated.');
            }
        }
        redirect(au('profile'));
    }

    if ($form === 'password') {
        $current = post('current_password');
        $new     = post('new_password');
        $confirm = post('confirm_password');

        if ($current === '' || $new === '' || $confirm === '') {
            flash('error', 'Please fill in all three password fields.');
        } elseif (!password_verify($current, (string) $me['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
        } elseif (strlen($new) < 8) {
            flash('error', 'The new password must be at least 8 characters long.');
        } elseif ($new !== $confirm) {
            flash('error', 'The new password and its confirmation do not match.');
        } elseif ($new === $current) {
            flash('error', 'The new password must be different from your current one.');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd  = db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $upd->execute([$hash, $adminId]);
            flash('success', 'Password changed successfully.');
        }
        redirect(au('profile'));
    }

    redirect(au('profile'));
}

$memberSince = strtotime((string) $me['created_at']);

$BASE   = '';
$ACTIVE = 'profile';
$TITLE  = 'Profile';
require __DIR__ . '/partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>Account details</h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= e(au('profile')) ?>" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="form" value="details">

      <div class="form-group">
        <label for="name">Display name <span class="req">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($me['name']) ?>" required maxlength="128">
      </div>

      <div class="form-group">
        <label for="username">Username <span class="req">*</span></label>
        <input type="text" id="username" name="username" value="<?= e($me['username']) ?>" required maxlength="64" autocomplete="username">
        <span class="hint">Used to sign in. Letters, numbers, dot, dash or underscore.</span>
      </div>

      <div class="form-group full">
        <span class="hint">Administrator account<?= $memberSince ? ' · member since ' . e(date('d M Y', $memberSince)) : '' ?>.</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn">Save details</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <h2>Change password</h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= e(au('profile')) ?>" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="form" value="password">

      <div class="form-group full">
        <label for="current_password">Current password <span class="req">*</span></label>
        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
      </div>

      <div class="form-group">
        <label for="new_password">New password <span class="req">*</span></label>
        <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm new password <span class="req">*</span></label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
      </div>

      <div class="form-group full">
        <span class="hint">Use at least 8 characters. You will stay signed in after changing it.</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn">Update password</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
