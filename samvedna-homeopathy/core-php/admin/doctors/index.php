<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Doctor.php';

require_login(1);

$doctors = Doctor::all();

$BASE = '../';
$ACTIVE = 'doctors';
$TITLE = 'Doctors';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>All doctors (<?= count($doctors) ?>)</h2>
    <a class="btn btn-add" href="<?= e(au('doctors/new')) ?>">New doctor</a>
  </div>

  <?php if (empty($doctors)): ?>
    <div class="empty">No doctors yet. <a href="<?= e(au('doctors/new')) ?>">Add the first profile</a>.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table>
      <thead>
        <tr><th class="col-hide-md">Photo</th><th>Name</th><th class="col-hide-sm">Title</th><th class="col-hide-md">Specialization</th><th class="col-hide-sm">Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($doctors as $d): ?>
          <tr>
            <td class="col-hide-md">
              <?php if (!empty($d['image'])): ?>
                <img class="thumb" src="<?= e(image_url($d['image'])) ?>" alt="">
              <?php else: ?>
                <div class="thumb"></div>
              <?php endif; ?>
            </td>
            <td><a href="<?= e(au('doctors/edit/' . (int) $d['id'])) ?>"><strong><?= e($d['name']) ?></strong></a></td>
            <td class="col-hide-sm"><?= e($d['title']) ?></td>
            <td class="col-hide-md"><?= e($d['specialization']) ?></td>
            <td class="col-hide-sm"><span class="badge badge-<?= e($d['status']) ?>"><?= e($d['status']) ?></span></td>
            <td>
              <div class="actions">
                <a class="btn btn-secondary btn-sm" href="<?= e(au('doctors/edit/' . (int) $d['id'])) ?>">Edit</a>
                <form method="post" action="<?= e(au('doctors/delete')) ?>" onsubmit="return confirm('Delete this doctor profile?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
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
