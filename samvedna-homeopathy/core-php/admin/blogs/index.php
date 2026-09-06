<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Blog.php';

require_login(1);

$blogs = Blog::all();

$BASE = '../';
$ACTIVE = 'blogs';
$TITLE = 'Blogs';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>All blog posts (<?= count($blogs) ?>)</h2>
    <a class="btn btn-add" href="<?= e(au('blogs/new')) ?>">New blog</a>
  </div>

  <?php if (empty($blogs)): ?>
    <div class="empty">No blog posts yet. <a href="<?= e(au('blogs/new')) ?>">Create the first one</a>.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table>
      <thead>
        <tr><th class="col-hide-md">Image</th><th>Title</th><th class="col-hide-sm">Category</th><th class="col-hide-sm">Status</th><th class="col-hide-md">Published</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($blogs as $b): ?>
          <tr>
            <td class="col-hide-md">
              <?php if (!empty($b['image'])): ?>
                <img class="thumb" src="<?= e(image_url($b['image'])) ?>" alt="">
              <?php else: ?>
                <div class="thumb"></div>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= e(au('blogs/edit/' . (int) $b['id'])) ?>"><strong><?= e($b['title']) ?></strong></a>
              <div class="hint"><?= e($b['slug']) ?></div>
            </td>
            <td class="col-hide-sm"><?= e($b['category']) ?></td>
            <td class="col-hide-sm"><span class="badge badge-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
            <td class="col-hide-md"><?= e($b['published_at'] ?? '-') ?></td>
            <td>
              <div class="actions">
                <a class="btn btn-secondary btn-sm" href="<?= e(au('blogs/edit/' . (int) $b['id'])) ?>">Edit</a>
                <form method="post" action="<?= e(au('blogs/delete')) ?>" onsubmit="return confirm('Delete this blog post? This cannot be undone.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
