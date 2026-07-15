<?php
/**
 * Shared admin header + sidebar.
 *
 * Pages must set before including:
 *   $BASE   - relative path to the /admin root (e.g. '' or '../')
 *   $ACTIVE - active nav key: dashboard|blogs|testimonials|doctors
 *   $TITLE  - page title
 * and must have already called require_login().
 */

declare(strict_types=1);

$ACTIVE = $ACTIVE ?? '';
$TITLE  = $TITLE  ?? 'Dashboard';
$admin  = current_admin();

$nav = [
    'dashboard'    => ['label' => 'Dashboard',          'href' => ADMIN_BASE,         'icon' => 'dashboard'],
    'blogs'        => ['label' => 'Blogs',              'href' => au('blogs'),        'icon' => 'blogs'],
    'testimonials' => ['label' => 'Video Testimonials', 'href' => au('testimonials'), 'icon' => 'video'],
    'doctors'      => ['label' => 'Doctors',            'href' => au('doctors'),      'icon' => 'doctors'],
];

if (!function_exists('admin_nav_icon')) {
    function admin_nav_icon(string $name): string
    {
        $icons = [
            'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
            'blogs' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>',
            'video' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3Z"/></svg>',
            'doctors' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M19 8v6"/><path d="M16 11h6"/></svg>',
        ];

        return $icons[$name] ?? $icons['dashboard'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($TITLE) ?> - <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(au('assets/admin.css')) ?>">
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">
      <img src="/images/samvedna-logo.webp" alt="Samvedna Homeopathy" class="brand-logo">
      <div class="brand-meta">
        <span class="brand-badge">Admin</span>
        <span class="brand-note">Core PHP CMS</span>
      </div>
    </div>
    <nav>
      <?php foreach ($nav as $key => $navItem): ?>
        <a href="<?= e($navItem['href']) ?>" class="<?= $ACTIVE === $key ? 'active' : '' ?>">
          <span class="nav-icon"><?= admin_nav_icon($navItem['icon']) ?></span>
          <span><?= e($navItem['label']) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="foot">v1.0 - Samvedna Homeopathy</div>
  </aside>

  <div class="main">
    <div class="topbar">
      <h1><?= e($TITLE) ?></h1>
      <div class="user">
        Signed in as <strong><?= e($admin['name'] ?? 'Admin') ?></strong>
        <a href="<?= e(au('logout')) ?>">Log out</a>
      </div>
    </div>
    <div class="content">
      <?php foreach (take_flashes() as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
