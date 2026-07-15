<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

boot_session();

// Already logged in? Go to dashboard.
if (is_logged_in()) {
    redirect(ADMIN_BASE);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = post('username');
    $password = post('password');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (attempt_login($username, $password)) {
        flash('success', 'Welcome back, ' . (current_admin()['name'] ?? 'Admin') . '.');
        redirect(ADMIN_BASE);
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Portal Login - <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(au('assets/admin.css')) ?>">
</head>
<body class="login-body">
  <div class="login-wrap">
    <div class="login-shell">
      <section class="login-brand-panel" aria-label="Samvedna admin portal">
        <img src="/images/samvedna-logo.webp" alt="Samvedna Homeopathy" class="login-brand-logo">
        <span class="portal-badge">Admin Portal</span>
        <h1>Clinic content dashboard</h1>
        <p>Manage the Samvedna public experience with a calm workspace for articles, parent stories, doctors, and consultation records.</p>

        <div class="login-metrics" aria-label="Admin sections">
          <div class="login-metric"><strong>Blogs</strong><span>Articles</span></div>
          <div class="login-metric"><strong>Stories</strong><span>Testimonials</span></div>
          <div class="login-metric"><strong>Doctors</strong><span>Profiles</span></div>
        </div>

        <div class="login-panel-note">
          <span class="note-icon">
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm3.36 6.18a.75.75 0 0 0-1.06-1.06L9 10.44 7.7 9.14a.75.75 0 0 0-1.06 1.06l1.83 1.83c.3.3.77.3 1.06 0l3.83-3.85Z" clip-rule="evenodd" />
            </svg>
          </span>
          <span>Protected access for Samvedna CMS administrators.</span>
        </div>
      </section>

      <div class="login-container">
        <form class="login-card" method="post" action="<?= e(au('login')) ?>">
          <div class="login-header">
            <span class="portal-badge">Secure Login</span>
            <h2>Welcome back</h2>
            <p class="welcome-text">Sign in to continue managing clinical content and patient inquiry records.</p>
          </div>

          <?php if ($error): ?>
            <div class="flash flash-error">
              <svg class="flash-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
              </svg>
              <span><?= e($error) ?></span>
            </div>
          <?php endif; ?>

          <?= csrf_field() ?>
          
          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
              </svg>
              <input type="text" id="username" name="username" value="<?= e(post('username')) ?>" placeholder="Enter your username" required autocomplete="username">
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
              </svg>
              <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>
          </div>

          <button type="submit" class="btn btn-login">
            <span>Sign In to Dashboard</span>
            <svg class="btn-arrow" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
          </button>
        </form>
        
        <div class="login-footer">
          <p>&copy; <?= date('Y') ?> Samvedna Homeopathy Clinic. All rights reserved.</p>
          <div class="security-note">
            <svg viewBox="0 0 20 20" fill="currentColor" class="lock-icon">
              <path fill-rule="evenodd" d="M9.661 2.237a.531.531 0 01.678 0 11.947 11.947 0 007.078 2.749.5.5 0 01.479.425c.069.52.104 1.05.104 1.59 0 5.162-3.26 9.563-7.834 11.256a.48.48 0 01-.332 0C5.26 16.564 2 12.163 2 7c0-.538.035-1.069.104-1.589a.5.5 0 01.48-.425 11.947 11.947 0 007.077-2.749zM10 4.308a10.887 10.887 0 01-5.91 2.302c-.047.45-.09.914-.09 1.39 0 4.41 2.766 8.193 6 9.682 3.234-1.489 6-5.272 6-9.682 0-.476-.043-.94-.09-1.39a10.887 10.887 0 01-5.91-2.302z" clip-rule="evenodd" />
            </svg>
            <span>Encrypted admin session</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
