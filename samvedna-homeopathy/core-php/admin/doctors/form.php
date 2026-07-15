<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Doctor.php';

require_login(1);

$id = req_int('id');
$isEdit = $id > 0;
$doc = $isEdit ? Doctor::find($id) : null;

if ($isEdit && !$doc) {
    flash('error', 'Doctor not found.');
    redirect(au('doctors'));
}

$doc = $doc ?? [
    'id' => 0, 'name' => '', 'title' => '', 'credential' => '', 'image' => '', 'alt' => '',
    'specialization' => '', 'experience' => '', 'summary' => '', 'about' => '',
    'qualifications' => '[]', 'specializations' => '[]', 'treatments' => '[]',
    'certifications' => '[]', 'awards' => '[]', 'languages' => '[]',
    'consultation' => '', 'sort_order' => 0, 'status' => 'published',
];

$BASE = '../';
$ACTIVE = 'doctors';
$TITLE = $isEdit ? 'Edit doctor' : 'New doctor';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head"><h2><?= $isEdit ? 'Edit doctor profile' : 'Add doctor profile' ?></h2>
    <a class="btn btn-secondary btn-sm btn-back" href="<?= e(au('doctors')) ?>">Back to list</a>
  </div>
  <div class="card-body">
    <form method="post" action="<?= e(au('doctors/save')) ?>" enctype="multipart/form-data" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>">

      <div class="form-group">
        <label for="name">Name <span class="req">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($doc['name']) ?>" required>
      </div>

      <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($doc['title']) ?>" placeholder="e.g. Senior Consultant">
      </div>

      <div class="form-group full">
        <label for="credential">Credential line</label>
        <input type="text" id="credential" name="credential" value="<?= e($doc['credential']) ?>" placeholder="e.g. BHMS | Case coordination and family guidance">
      </div>

      <div class="form-group">
        <label for="specialization">Specialization</label>
        <input type="text" id="specialization" name="specialization" value="<?= e($doc['specialization']) ?>" placeholder="e.g. Developmental Care">
      </div>

      <div class="form-group">
        <label for="experience">Experience</label>
        <input type="text" id="experience" name="experience" value="<?= e($doc['experience']) ?>" placeholder="e.g. 12+ Years">
      </div>

      <div class="form-group full">
        <label for="summary">Card summary <small>(short text shown on the card)</small></label>
        <textarea id="summary" name="summary"><?= e($doc['summary']) ?></textarea>
      </div>

      <div class="form-group full">
        <label for="about">About <small>(detailed bio in the profile dialog)</small></label>
        <textarea id="about" name="about"><?= e($doc['about']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="qualifications">Qualifications <small>(one per line)</small></label>
        <textarea id="qualifications" name="qualifications" placeholder="BHMS&#10;FCAH"><?= e(array_to_lines($doc['qualifications'])) ?></textarea>
      </div>

      <div class="form-group">
        <label for="languages">Languages <small>(one per line)</small></label>
        <textarea id="languages" name="languages" placeholder="English&#10;Hindi&#10;Gujarati"><?= e(array_to_lines($doc['languages'])) ?></textarea>
      </div>

      <div class="form-group">
        <label for="specializations">Specializations <small>(one per line)</small></label>
        <textarea id="specializations" name="specializations"><?= e(array_to_lines($doc['specializations'])) ?></textarea>
      </div>

      <div class="form-group">
        <label for="treatments">Treatments <small>(one per line)</small></label>
        <textarea id="treatments" name="treatments"><?= e(array_to_lines($doc['treatments'])) ?></textarea>
      </div>

      <div class="form-group">
        <label for="certifications">Certifications <small>(one per line, optional)</small></label>
        <textarea id="certifications" name="certifications"><?= e(array_to_lines($doc['certifications'])) ?></textarea>
      </div>

      <div class="form-group">
        <label for="awards">Awards <small>(one per line, optional)</small></label>
        <textarea id="awards" name="awards"><?= e(array_to_lines($doc['awards'])) ?></textarea>
      </div>

      <div class="form-group full">
        <label for="consultation">Consultation details</label>
        <input type="text" id="consultation" name="consultation" value="<?= e($doc['consultation']) ?>" placeholder="Online & in-clinic - Mon-Sat, 10am-7pm - By appointment.">
      </div>

      <div class="form-group">
        <label for="image">Photo <small>(max 4MB)</small></label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if (!empty($doc['image'])): ?>
          <div class="preview">
            <img src="<?= e(image_url($doc['image'])) ?>" alt="current photo">
            <div class="hint">Current: <?= e($doc['image']) ?></div>
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="image_path">Or photo path</label>
        <input type="text" id="image_path" name="image_path" value="<?= e($doc['image']) ?>" placeholder="/images/example.webp">
        <span class="hint">Used only when no file is uploaded.</span>
      </div>

      <div class="form-group full">
        <label for="alt">Photo alt text</label>
        <input type="text" id="alt" name="alt" value="<?= e($doc['alt']) ?>" placeholder="Describe the photo for accessibility">
      </div>

      <!-- Sort order is managed automatically; preserved on edit via this hidden field. -->
      <input type="hidden" name="sort_order" value="<?= (int) $doc['sort_order'] ?>">

      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="published" <?= $doc['status'] === 'published' ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= $doc['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn"><?= $isEdit ? 'Update profile' : 'Create profile' ?></button>
        <a href="<?= e(au('doctors')) ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
