<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Consultation.php';

require_login(1);

$id  = req_int('id');
$row = $id > 0 ? Consultation::find($id) : null;

if (!$row) {
    flash('error', 'Assessment not found.');
    redirect(au('consultations'));
}

// The clinical answers are stored as a self-describing list of sections:
//   [ { title, items: [ { label, value } ] } ]
$sections = json_col($row['answers'] ?? '');

// General-information fields shown as their own block (stored as real columns).
$general = [
    'Patient name'    => $row['patient_name'],
    'Father name'     => $row['father_name'],
    'Mobile'          => $row['mobile'],
    'Alternate phone' => $row['alt_phone'],
    'Email'           => $row['email'],
    'Child age'       => $row['child_age'] !== null && $row['child_age'] !== '' ? $row['child_age'] . ' yrs' : '',
    'Address'         => $row['address'],
    'City'            => $row['city'],
    'State'           => $row['state'],
    'Zip'             => $row['zip'],
    'Remarks'         => $row['remarks'],
];

$ts = strtotime((string) $row['created_at']);

$BASE = '../';
$ACTIVE = 'consultations';
$TITLE = 'Assessment · ' . ($row['patient_name'] ?: '#' . $row['id']);
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>
      <?= e($row['patient_name'] ?: ('Assessment #' . $row['id'])) ?>
      <?php if (!empty($row['plan_name']) || !empty($row['plan'])): ?>
        <span class="badge badge-published"><?= e($row['plan_name'] ?: $row['plan']) ?><?= !empty($row['amount']) ? ' · ' . e($row['amount']) : '' ?></span>
      <?php endif; ?>
    </h2>
    <a href="<?= e(au('consultations')) ?>" class="btn btn-sm">&larr; Back to list</a>
  </div>

  <p class="lead-intro">
    Received <?= e($ts ? date('d M Y · h:i A', $ts) : (string) $row['created_at']) ?>.
  </p>

  <h3 class="detail-heading">General information</h3>
  <div class="detail-grid">
    <?php foreach ($general as $label => $value): ?>
      <div class="detail-item">
        <span class="detail-label"><?= e($label) ?></span>
        <span class="detail-value"><?= $value !== null && $value !== '' ? nl2br(e($value)) : '<span class="muted">—</span>' ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($sections)): ?>
    <?php foreach ($sections as $section): ?>
      <?php
        $title = is_array($section) ? ($section['title'] ?? '') : '';
        $items = is_array($section) && isset($section['items']) && is_array($section['items']) ? $section['items'] : [];
        if (empty($items)) {
            continue;
        }
      ?>
      <h3 class="detail-heading"><?= e($title) ?></h3>
      <div class="detail-grid">
        <?php foreach ($items as $item): ?>
          <?php
            $label = is_array($item) ? (string) ($item['label'] ?? '') : '';
            $value = is_array($item) ? (string) ($item['value'] ?? '') : '';
          ?>
          <div class="detail-item">
            <span class="detail-label"><?= e($label) ?></span>
            <span class="detail-value"><?= $value !== '' ? nl2br(e($value)) : '<span class="muted">—</span>' ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty">No clinical answers were recorded for this submission.</div>
  <?php endif; ?>

  <div class="detail-actions">
    <form method="post" action="<?= e(au('consultations/delete')) ?>" onsubmit="return confirm('Delete this assessment? This cannot be undone.');">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
      <button type="submit" class="btn btn-danger">Delete assessment</button>
    </form>
  </div>
</div>

<style>
  .detail-heading { margin: 26px 0 12px; font-size: 15px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
  .detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px 24px; }
  .detail-item { display: flex; flex-direction: column; gap: 3px; }
  .detail-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
  .detail-value { font-size: 14px; color: #0f172a; }
  .detail-actions { margin-top: 28px; }
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
