<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Lead.php';

require_login(1);

// Active source filter: '' (all) | 'website' | 'popup'.
$filter = $_GET['source'] ?? '';
if (!in_array($filter, ['', 'website', 'popup'], true)) {
    $filter = '';
}

$leads = Lead::all($filter !== '' ? $filter : null);

$totalAll     = Lead::count();
$totalWebsite = Lead::count('website');
$totalPopup   = Lead::count('popup');

/** Human label + badge class for a source value. */
function lead_source_meta(string $source): array
{
    switch ($source) {
        case 'popup':
            return ['Popup form', 'badge-draft'];
        case 'website':
            return ['Contact page', 'badge-published'];
        default:
            return [ucfirst($source ?: 'Website'), 'badge-draft'];
    }
}

$tabs = [
    ''        => ['label' => 'All leads',     'count' => $totalAll],
    'website' => ['label' => 'Contact page',  'count' => $totalWebsite],
    'popup'   => ['label' => 'Popup form',    'count' => $totalPopup],
];

$BASE = '../';
$ACTIVE = 'leads';
$TITLE = 'Leads';
require __DIR__ . '/../partials/header.php';
?>

<div class="card">
  <div class="card-head">
    <h2>Consultation leads (<?= count($leads) ?>)</h2>
    <div class="lead-tabs">
      <?php foreach ($tabs as $key => $tab): ?>
        <a
          class="lead-tab <?= $filter === $key ? 'active' : '' ?>"
          href="<?= e(au('leads' . ($key !== '' ? '?source=' . $key : ''))) ?>"
        ><?= e($tab['label']) ?> <span class="lead-tab-count"><?= (int) $tab['count'] ?></span></a>
      <?php endforeach; ?>
    </div>
  </div>

  <p class="lead-intro">
    Every consultation request submitted through the website contact form and the
    auto-appearing popup lands here in real time.
  </p>

  <?php if (empty($leads)): ?>
    <div class="empty">
      No leads yet. When a visitor fills the contact form or the popup on the website,
      their request will appear here automatically.
    </div>
  <?php else: ?>
    <div class="table-scroll">
      <table class="leads-table">
        <thead>
          <tr>
            <th class="col-hide-md">Received</th>
            <th class="col-hide-sm">Source</th>
            <th>Parent</th>
            <th class="col-hide-md">Child age</th>
            <th class="col-hide-sm">Concern</th>
            <th class="col-hide-md">Country</th>
            <th>Contact</th>
            <th class="col-hide-md">Preferred time</th>
            <th class="col-hide-sm">Message</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leads as $lead): ?>
            <?php [$sourceLabel, $sourceBadge] = lead_source_meta((string) $lead['source']); ?>
            <tr>
              <td class="nowrap col-hide-md">
                <?php
                  $ts = strtotime((string) $lead['created_at']);
                  echo e($ts ? date('d M Y', $ts) : $lead['created_at']);
                ?>
                <small class="lead-time"><?= $ts ? e(date('h:i A', $ts)) : '' ?></small>
              </td>
              <td class="col-hide-sm"><span class="badge <?= e($sourceBadge) ?>"><?= e($sourceLabel) ?></span></td>
              <td><strong><?= e($lead['parent_name']) ?></strong></td>
              <td class="col-hide-md"><?= e($lead['child_age']) ?> yrs</td>
              <td class="col-hide-sm"><?= e($lead['condition_type']) ?></td>
              <td class="col-hide-md"><?= e($lead['country']) ?></td>
              <td class="nowrap">
                <a href="tel:<?= e($lead['phone']) ?>"><?= e($lead['phone']) ?></a>
                <small class="lead-email"><a href="mailto:<?= e($lead['email']) ?>"><?= e($lead['email']) ?></a></small>
              </td>
              <td class="col-hide-md"><?= e($lead['preferred_time']) ?></td>
              <td class="lead-message col-hide-sm"><?= $lead['message'] !== null && $lead['message'] !== '' ? e($lead['message']) : '<span class="muted">—</span>' ?></td>
              <td>
                <form method="post" action="<?= e(au('leads/delete')) ?>" onsubmit="return confirm('Delete this lead? This cannot be undone.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                  <?php if ($filter !== '') : ?>
                    <input type="hidden" name="source" value="<?= e($filter) ?>">
                  <?php endif; ?>
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
