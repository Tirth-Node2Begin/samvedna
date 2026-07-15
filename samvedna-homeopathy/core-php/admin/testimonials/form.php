<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/VideoTestimonial.php';

require_login(1);

$id = req_int('id');
$isEdit = $id > 0;
$item = $isEdit ? VideoTestimonial::find($id) : null;

if ($isEdit && !$item) {
    flash('error', 'Testimonial not found.');
    redirect(au('testimonials'));
}

$item = $item ?? [
    'id' => 0, 'name' => 'Parent family', 'condition_label' => '', 'location' => '',
    'youtube_id' => '', 'poster' => '', 'alt' => '', 'duration' => '',
    'sort_order' => 0, 'status' => 'published',
];

$BASE = '../';
$ACTIVE = 'testimonials';
$TITLE = $isEdit ? 'Edit testimonial' : 'New testimonial';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head"><h2><?= $isEdit ? 'Edit testimonial' : 'Add testimonial' ?></h2>
    <a class="btn btn-secondary btn-sm btn-back" href="<?= e(au('testimonials')) ?>">Back to list</a>
  </div>
  <div class="card-body">
    <form method="post" action="<?= e(au('testimonials/save')) ?>" enctype="multipart/form-data" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

      <div class="form-group">
        <label for="name">Name <span class="req">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($item['name']) ?>" required>
      </div>

      <div class="form-group">
        <label for="condition_label">Condition <span class="req">*</span></label>
        <input type="text" id="condition_label" name="condition_label" value="<?= e($item['condition_label']) ?>" placeholder="e.g. Autism support" required>
      </div>

      <div class="form-group">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?= e($item['location']) ?>" placeholder="e.g. India">
      </div>

      <div class="form-group">
        <label for="youtube_id">YouTube ID <small>(after watch?v=)</small></label>
        <input type="text" id="youtube_id" name="youtube_id" value="<?= e($item['youtube_id']) ?>" placeholder="dQw4w9WgXcQ (leave blank for 'coming soon')">
      </div>

      <div class="form-group">
        <label for="duration">Duration</label>
        <input type="text" id="duration" name="duration" value="<?= e($item['duration']) ?>" placeholder="e.g. 2:14">
      </div>

      <div class="form-group">
        <label for="sort_order">Sort order</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= (int) $item['sort_order'] ?>">
      </div>

      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="published" <?= $item['status'] === 'published' ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= $item['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>

      <div class="form-group">
        <label for="poster">Poster / thumbnail <small>(max 4MB)</small></label>
        <input type="file" id="poster" name="poster" accept="image/*">
        <?php if (!empty($item['poster'])): ?>
          <div class="preview">
            <img src="<?= e(image_url($item['poster'])) ?>" alt="current poster">
            <div class="hint">Current: <?= e($item['poster']) ?></div>
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="poster_path">Or poster path</label>
        <input type="text" id="poster_path" name="poster_path" value="<?= e($item['poster']) ?>" placeholder="/images/example.webp">
        <span class="hint">Used only when no file is uploaded.</span>
      </div>

      <div class="form-group full">
        <label for="alt">Poster alt text</label>
        <input type="text" id="alt" name="alt" value="<?= e($item['alt']) ?>" placeholder="Describe the video/poster for accessibility">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn"><?= $isEdit ? 'Update' : 'Create' ?></button>
        <a href="<?= e(au('testimonials')) ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
