<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Consultation.php';

require_login(1);

$consultations = Consultation::all();

$BASE = '../';
$ACTIVE = 'consultations';
$TITLE = 'Consultations';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>Care-plan assessments (<?= count($consultations) ?>)</h2>
  </div>

  <p class="lead-intro">
    Detailed intake submitted through the multi-step assessment that opens when a
    visitor chooses a care plan. Open a row to read every clinical answer.
  </p>

  <?php if (empty($consultations)): ?>
    <div class="empty">
      No assessments yet. When a visitor picks a care plan and completes the
      multi-step form on the website, their submission will appear here automatically.
    </div>
  <?php else: ?>
    <div class="table-scroll">
      <table class="leads-table">
        <thead>
          <tr>
            <th class="col-hide-md">Received</th>
            <th class="col-hide-sm">Plan</th>
            <th>Patient</th>
            <th class="col-hide-md">Child age</th>
            <th class="col-hide-sm">Contact</th>
            <th class="col-hide-md">City</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($consultations as $row): ?>
            <tr>
              <td class="nowrap col-hide-md">
                <?php
                  $ts = strtotime((string) $row['created_at']);
                  echo e($ts ? date('d M Y', $ts) : $row['created_at']);
                ?>
                <small class="lead-time"><?= $ts ? e(date('h:i A', $ts)) : '' ?></small>
              </td>
              <td class="col-hide-sm">
                <span class="badge badge-published"><?= e($row['plan_name'] ?: ($row['plan'] ?: '—')) ?></span>
                <?php if (!empty($row['amount'])): ?>
                  <small class="lead-time"><?= e($row['amount']) ?></small>
                <?php endif; ?>
              </td>
              <td><strong><?= e($row['patient_name']) ?></strong></td>
              <td class="col-hide-md"><?= $row['child_age'] !== null && $row['child_age'] !== '' ? e($row['child_age']) . ' yrs' : '<span class="muted">—</span>' ?></td>
              <td class="nowrap col-hide-sm">
                <a href="tel:<?= e($row['mobile']) ?>"><?= e($row['mobile']) ?></a>
                <?php if (!empty($row['email'])): ?>
                  <small class="lead-email"><a href="mailto:<?= e($row['email']) ?>"><?= e($row['email']) ?></a></small>
                <?php endif; ?>
              </td>
              <td class="col-hide-md"><?= $row['city'] !== null && $row['city'] !== '' ? e($row['city']) : '<span class="muted">—</span>' ?></td>
              <td class="nowrap">
                <a href="<?= e(au('consultations/view/' . (int) $row['id'])) ?>" class="btn btn-sm">View</a>
                <form method="post" action="<?= e(au('consultations/delete')) ?>" onsubmit="return confirm('Delete this assessment? This cannot be undone.');" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
