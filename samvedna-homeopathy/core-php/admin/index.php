<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models/Blog.php';
require_once __DIR__ . '/../includes/models/VideoTestimonial.php';
require_once __DIR__ . '/../includes/models/Doctor.php';

require_login(0);

$blogCount    = count(Blog::all());
$vtCount      = count(VideoTestimonial::all());
$docCount     = count(Doctor::all());
$inquiryCount = (int) db()->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();

$recentBlogs = array_slice(Blog::all(), 0, 5);
$adminName = current_admin()['name'] ?? 'Administrator';

if (!function_exists('dashboard_stat_icon')) {
    function dashboard_stat_icon(string $name): string
    {
        $icons = [
            'blogs' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path d="M8 7h8"/><path d="M8 11h7"/></svg>',
            'video' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3Z"/></svg>',
            'doctor' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M19 8v6"/><path d="M16 11h6"/></svg>',
            'inquiry' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>',
            'spark' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 3 9.8 8.8 4 11l5.8 2.2L12 19l2.2-5.8L20 11l-5.8-2.2Z"/></svg>',
        ];

        return $icons[$name] ?? $icons['spark'];
    }
}

$dashboardStats = [
    [
        'key' => 'blogs',
        'icon' => 'blogs',
        'value' => $blogCount,
        'label' => 'Blog posts',
        'detail' => 'Articles available for the landing page and blog library.',
        'href' => au('blogs'),
    ],
    [
        'key' => 'video',
        'icon' => 'video',
        'value' => $vtCount,
        'label' => 'Video testimonials',
        'detail' => 'Parent story videos and coming-soon testimonial entries.',
        'href' => au('testimonials'),
    ],
    [
        'key' => 'doctor',
        'icon' => 'doctor',
        'value' => $docCount,
        'label' => 'Doctors',
        'detail' => 'Profiles used across the medical team sections.',
        'href' => au('doctors'),
    ],
    [
        'key' => 'inquiry',
        'icon' => 'inquiry',
        'value' => $inquiryCount,
        'label' => 'Consultation inquiries',
        'detail' => 'Submitted consultation requests stored in the CMS.',
        'href' => ADMIN_BASE,
    ],
];

$BASE = '';
$ACTIVE = 'dashboard';
$TITLE = 'Dashboard';
require __DIR__ . '/partials/header.php';
?>

<section class="dashboard-hero">
  <div>
    <span class="section-eyebrow">Samvedna control center</span>
    <h2>Welcome back, <?= e($adminName) ?></h2>
    <p>Review your clinic content, keep public sections fresh, and manage updates for blogs, parent stories, and doctor profiles.</p>
  </div>
  <div class="dashboard-hero-actions">
    <a class="btn btn-add" href="<?= e(au('blogs/new')) ?>">New blog</a>
    <a class="btn btn-secondary btn-add" href="<?= e(au('testimonials/new')) ?>">New testimonial</a>
    <a class="btn btn-secondary btn-add" href="<?= e(au('doctors/new')) ?>">New doctor</a>
  </div>
</section>

<div class="stats">
  <?php foreach ($dashboardStats as $stat): ?>
    <a class="stat stat-<?= e($stat['key']) ?>" href="<?= e($stat['href']) ?>">
      <div class="stat-top">
        <span class="stat-icon"><?= dashboard_stat_icon($stat['icon']) ?></span>
        <span class="stat-chip">CMS</span>
      </div>
      <div class="n"><?= (int) $stat['value'] ?></div>
      <div class="l"><?= e($stat['label']) ?></div>
      <p class="stat-detail"><?= e($stat['detail']) ?></p>
    </a>
  <?php endforeach; ?>
</div>

<div class="dashboard-grid">
  <div class="card dashboard-main-card">
    <div class="card-head">
      <h2>Recent blog posts</h2>
      <a class="btn btn-sm btn-add" href="<?= e(au('blogs/new')) ?>">New blog</a>
    </div>
    <?php if (empty($recentBlogs)): ?>
      <div class="empty">No blog posts yet. <a href="<?= e(au('blogs/new')) ?>">Create the first one</a>.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th></tr></thead>
        <tbody>
          <?php foreach ($recentBlogs as $b): ?>
            <tr>
              <td><a href="<?= e(au('blogs/edit/' . (int) $b['id'])) ?>"><?= e($b['title']) ?></a></td>
              <td><?= e($b['category']) ?></td>
              <td><span class="badge badge-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
              <td><?= e($b['published_at'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <aside class="dashboard-side">
    <div class="card dashboard-mini-card">
      <div class="card-head compact">
        <h2>Quick actions</h2>
      </div>
      <div class="quick-actions">
        <a href="<?= e(au('blogs/new')) ?>" class="quick-action">
          <span class="quick-action-mark"><?= dashboard_stat_icon('blogs') ?></span>
          <span><strong>Create article</strong><small>Add a new blog post</small></span>
        </a>
        <a href="<?= e(au('testimonials/new')) ?>" class="quick-action">
          <span class="quick-action-mark"><?= dashboard_stat_icon('video') ?></span>
          <span><strong>Add story</strong><small>Upload testimonial details</small></span>
        </a>
        <a href="<?= e(au('doctors/new')) ?>" class="quick-action">
          <span class="quick-action-mark"><?= dashboard_stat_icon('doctor') ?></span>
          <span><strong>Add doctor</strong><small>Create a profile card</small></span>
        </a>
      </div>
    </div>

    <div class="card dashboard-mini-card">
      <div class="card-head compact">
        <h2>Content snapshot</h2>
      </div>
      <div class="snapshot-list">
        <div class="snapshot-row"><span>Total records</span><strong><?= $blogCount + $vtCount + $docCount ?></strong></div>
        <div class="snapshot-row"><span>Public sections</span><strong>3</strong></div>
        <div class="snapshot-row"><span>Last checked</span><strong><?= e(date('d M Y')) ?></strong></div>
      </div>
    </div>
  </aside>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
