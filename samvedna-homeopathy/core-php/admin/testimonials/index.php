<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/VideoTestimonial.php';

require_login(1);

$items = VideoTestimonial::all();

$BASE = '../';
$ACTIVE = 'testimonials';
$TITLE = 'Video Testimonials';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>All video testimonials (<?= count($items) ?>)</h2>
    <a class="btn btn-add" href="<?= e(au('testimonials/new')) ?>">New testimonial</a>
  </div>

  <?php if (empty($items)): ?>
    <div class="empty">No testimonials yet. <a href="<?= e(au('testimonials/new')) ?>">Add the first one</a>.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>Poster</th><th>Name / Condition</th><th>Location</th><th>YouTube</th><th>Order</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $v): ?>
          <tr>
            <td>
              <?php if (!empty($v['poster'])): ?>
                <img class="thumb" src="<?= e(image_url($v['poster'])) ?>" alt="">
              <?php else: ?>
                <div class="thumb"></div>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= e(au('testimonials/edit/' . (int) $v['id'])) ?>"><strong><?= e($v['name']) ?></strong></a>
              <div class="hint"><?= e($v['condition_label']) ?></div>
            </td>
            <td><?= e($v['location']) ?></td>
            <td><?= $v['youtube_id'] !== '' ? e($v['youtube_id']) : '<span class="hint">Coming soon</span>' ?></td>
            <td><?= (int) $v['sort_order'] ?></td>
            <td><span class="badge badge-<?= e($v['status']) ?>"><?= e($v['status']) ?></span></td>
            <td>
              <div class="actions">
                <a class="btn btn-secondary btn-sm" href="<?= e(au('testimonials/edit/' . (int) $v['id'])) ?>">Edit</a>
                <form method="post" action="<?= e(au('testimonials/delete')) ?>" onsubmit="return confirm('Delete this testimonial?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
