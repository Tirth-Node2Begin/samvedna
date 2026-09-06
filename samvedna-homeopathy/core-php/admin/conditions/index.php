<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Condition.php';

require_login(1);

$conditions = Condition::all();

$BASE = '../';
$ACTIVE = 'conditions';
$TITLE = 'Conditions';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>All conditions (<?= count($conditions) ?>)</h2>
    <a class="btn btn-add" href="<?= e(au('conditions/new')) ?>">New condition</a>
  </div>

  <?php if (empty($conditions)): ?>
    <div class="empty">No conditions yet. <a href="<?= e(au('conditions/new')) ?>">Add the first one</a>.</div>
  <?php else: ?>
    <div class="table-scroll">
    <table>
      <thead>
        <tr><th class="col-hide-md">Image</th><th>Name</th><th class="col-hide-md">Description</th><th class="col-hide-sm">Card size</th><th class="col-hide-sm">Order</th><th class="col-hide-sm">Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($conditions as $c): ?>
          <tr>
            <td class="col-hide-md">
              <?php if (!empty($c['image'])): ?>
                <img class="thumb" src="<?= e(image_url($c['image'])) ?>" alt="">
              <?php else: ?>
                <div class="thumb"></div>
              <?php endif; ?>
            </td>
            <td><a href="<?= e(au('conditions/edit/' . (int) $c['id'])) ?>"><strong><?= e($c['name']) ?></strong></a></td>
            <td class="col-hide-md"><?= e(mb_strimwidth((string) $c['description'], 0, 90, '…')) ?></td>
            <td class="col-hide-sm"><?= e($c['span']) ?></td>
            <td class="col-hide-sm"><?= (int) $c['sort_order'] ?></td>
            <td class="col-hide-sm"><span class="badge badge-<?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
            <td>
              <div class="actions">
                <a class="btn btn-secondary btn-sm" href="<?= e(au('conditions/edit/' . (int) $c['id'])) ?>">Edit</a>
                <form method="post" action="<?= e(au('conditions/delete')) ?>" onsubmit="return confirm('Delete this condition?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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
