<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Condition.php';

require_login(1);

$id = req_int('id');
$isEdit = $id > 0;
$cond = $isEdit ? Condition::find($id) : null;

if ($isEdit && !$cond) {
    flash('error', 'Condition not found.');
    redirect(au('conditions'));
}

$cond = $cond ?? [
    'id' => 0, 'name' => '', 'description' => '', 'image' => '', 'alt' => '',
    'span' => 'standard', 'sort_order' => 0, 'status' => 'published',
];

$BASE = '../';
$ACTIVE = 'conditions';
$TITLE = $isEdit ? 'Edit condition' : 'New condition';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head"><h2><?= $isEdit ? 'Edit condition' : 'Add condition' ?></h2>
    <a class="btn btn-secondary btn-sm btn-back" href="<?= e(au('conditions')) ?>">Back to list</a>
  </div>
  <div class="card-body">
    <form method="post" action="<?= e(au('conditions/save')) ?>" enctype="multipart/form-data" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $cond['id'] ?>">

      <div class="form-group">
        <label for="name">Name <span class="req">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($cond['name']) ?>" placeholder="e.g. ADHD Support" required>
      </div>

      <div class="form-group">
        <label for="span">Card size</label>
        <select id="span" name="span">
          <option value="featured" <?= $cond['span'] === 'featured' ? 'selected' : '' ?>>Featured (wide, highlighted)</option>
          <option value="standard" <?= $cond['span'] === 'standard' ? 'selected' : '' ?>>Standard</option>
          <option value="compact" <?= $cond['span'] === 'compact' ? 'selected' : '' ?>>Compact</option>
        </select>
        <span class="hint">Only the first card in the grid is rendered large.</span>
      </div>

      <div class="form-group full">
        <label for="description">Description <small>(1-2 lines shown on the card)</small></label>
        <textarea id="description" name="description"><?= e($cond['description']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="image">Image <small>(optional, max 4MB)</small></label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if (!empty($cond['image'])): ?>
          <div class="preview">
            <img src="<?= e(image_url($cond['image'])) ?>" alt="current image">
            <div class="hint">Current: <?= e($cond['image']) ?></div>
          </div>
        <?php endif; ?>
        <span class="hint">Leave empty to show the built-in icon instead.</span>
      </div>

      <div class="form-group">
        <label for="image_path">Or image path</label>
        <input type="text" id="image_path" name="image_path" value="<?= e($cond['image']) ?>" placeholder="/images/example.webp">
        <span class="hint">Used only when no file is uploaded. Clear it to remove the image.</span>
      </div>

      <div class="form-group full">
        <label for="alt">Image alt text</label>
        <input type="text" id="alt" name="alt" value="<?= e($cond['alt']) ?>" placeholder="Describe the image for accessibility">
      </div>

      <div class="form-group">
        <label for="sort_order">Sort order</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= (int) $cond['sort_order'] ?>">
        <span class="hint">Lower numbers appear first.</span>
      </div>

      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="published" <?= $cond['status'] === 'published' ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= $cond['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn"><?= $isEdit ? 'Update condition' : 'Create condition' ?></button>
        <a href="<?= e(au('conditions')) ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
