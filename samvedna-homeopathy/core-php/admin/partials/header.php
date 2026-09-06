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
    'conditions'   => ['label' => 'Conditions',         'href' => au('conditions'),   'icon' => 'conditions'],
    'leads'        => ['label' => 'Leads',              'href' => au('leads'),        'icon' => 'leads'],
    'consultations'=> ['label' => 'Consultations',      'href' => au('consultations'),'icon' => 'consultations'],
];

if (!function_exists('admin_nav_icon')) {
    function admin_nav_icon(string $name): string
    {
        $icons = [
            'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
            'blogs' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>',
            'video' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3Z"/></svg>',
            'doctors' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M19 8v6"/><path d="M16 11h6"/></svg>',
            'conditions' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/><path d="M9 12h1.5l1-2 1.5 4 1-2H15"/></svg>',
            'leads' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
            'consultations' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>',
            'profile' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>',
            'logout' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>',
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
  <aside class="sidebar" id="adminSidebar">
    <div class="brand">
      <img src="/images/samvedna-logo.webp" alt="Samvedna Homeopathy" class="brand-logo">
      <div class="brand-meta">
        <span class="brand-badge">Admin</span>
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

    <div class="sidebar-account">
      <a href="<?= e(au('profile')) ?>" class="<?= $ACTIVE === 'profile' ? 'active' : '' ?>">
        <span class="nav-icon"><?= admin_nav_icon('profile') ?></span>
        <span>Profile</span>
      </a>
      <a href="<?= e(au('logout')) ?>" class="nav-logout">
        <span class="nav-icon"><?= admin_nav_icon('logout') ?></span>
        <span>Log out</span>
      </a>
    </div>

    <div class="foot">v1.0 - Samvedna Homeopathy</div>
  </aside>

  <div class="sidebar-backdrop" data-nav-backdrop></div>

  <div class="main">
    <div class="topbar">
      <button type="button" class="nav-toggle" data-nav-toggle aria-label="Open menu" aria-expanded="false" aria-controls="adminSidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
      <h1><?= e($TITLE) ?></h1>
      <div class="user">Signed in as <strong><?= e($admin['name'] ?? 'Admin') ?></strong></div>
    </div>
    <div class="content">
      <?php foreach (take_flashes() as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
