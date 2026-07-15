<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Blog.php';

require_login(1);

$id = req_int('id');
$isEdit = $id > 0;
$blog = $isEdit ? Blog::find($id) : null;

if ($isEdit && !$blog) {
    flash('error', 'Blog post not found.');
    redirect(au('blogs'));
}

// Default values for a new post.
$blog = $blog ?? [
    'id' => 0, 'slug' => '', 'title' => '', 'excerpt' => '', 'content' => '',
    'category' => '', 'author' => '', 'image' => '', 'alt' => '',
    'read_time' => '', 'status' => 'draft', 'published_at' => date('Y-m-d'),
];

$BASE = '../';
$ACTIVE = 'blogs';
$TITLE = $isEdit ? 'Edit blog' : 'New blog';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head"><h2><?= $isEdit ? 'Edit blog post' : 'Create blog post' ?></h2>
    <a class="btn btn-secondary btn-sm btn-back" href="<?= e(au('blogs')) ?>">Back to list</a>
  </div>
  <div class="card-body">
    <form method="post" action="<?= e(au('blogs/save')) ?>" enctype="multipart/form-data" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $blog['id'] ?>">

      <div class="form-group full">
        <label for="title">Title <span class="req">*</span></label>
        <input type="text" id="title" name="title" value="<?= e($blog['title']) ?>" required>
      </div>

      <div class="form-group">
        <label for="slug">Slug <small>(leave blank to auto-generate)</small></label>
        <input type="text" id="slug" name="slug" value="<?= e($blog['slug']) ?>" placeholder="auto from title">
      </div>

      <div class="form-group">
        <label for="category">Category <span class="req">*</span></label>
        <input type="text" id="category" name="category" value="<?= e($blog['category']) ?>" placeholder="e.g. Autism" required>
      </div>

      <div class="form-group full">
        <label for="excerpt">Excerpt <small>(short summary shown on cards)</small></label>
        <textarea id="excerpt" name="excerpt"><?= e($blog['excerpt']) ?></textarea>
      </div>

      <div class="form-group full">
        <label for="content">Content <small>(full article - HTML allowed)</small></label>
        <textarea id="content" name="content" style="min-height:220px;"><?= e($blog['content']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="author">Author</label>
        <input type="text" id="author" name="author" value="<?= e($blog['author']) ?>" placeholder="Samvedna Team">
      </div>

      <div class="form-group">
        <label for="read_time">Read time</label>
        <input type="text" id="read_time" name="read_time" value="<?= e($blog['read_time']) ?>" placeholder="e.g. 5 min read">
      </div>

      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="draft" <?= $blog['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="published" <?= $blog['status'] === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
      </div>

      <div class="form-group">
        <label for="published_at">Publish date</label>
        <input type="date" id="published_at" name="published_at" value="<?= e($blog['published_at']) ?>">
      </div>

      <div class="form-group">
        <label for="image">Featured image <small>(JPG/PNG/WEBP/GIF, max 4MB)</small></label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if (!empty($blog['image'])): ?>
          <div class="preview">
            <img src="<?= e(image_url($blog['image'])) ?>" alt="current image">
            <div class="hint">Current: <?= e($blog['image']) ?> - upload a new file to replace it.</div>
          </div>
        <?php else: ?>
          <span class="hint">Or reference an existing image path in the field below.</span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="image_path">Or image path</label>
        <input type="text" id="image_path" name="image_path" value="<?= e($blog['image']) ?>" placeholder="/images/example.webp">
        <span class="hint">Used only when no file is uploaded.</span>
      </div>

      <div class="form-group full">
        <label for="alt">Image alt text</label>
        <input type="text" id="alt" name="alt" value="<?= e($blog['alt']) ?>" placeholder="Describe the image for accessibility">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn"><?= $isEdit ? 'Update post' : 'Create post' ?></button>
        <a href="<?= e(au('blogs')) ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
