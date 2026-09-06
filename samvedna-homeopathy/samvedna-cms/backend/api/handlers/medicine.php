<?php
/**
 * The medicine supply cycle: who needs medicine today, recording a delivery,
 * and adjusting a case's interval.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Medicine.php';
require_once __DIR__ . '/../../includes/models/Notification.php';
require_once __DIR__ . '/../../includes/models/CareCase.php';
require_once __DIR__ . '/../../includes/models/Patient.php';

/** The board: due today / overdue / upcoming / later, plus counts and recent deliveries. */
function medicine_index(): void
{
    cms_require_auth();
    Notification::generateDaily();

    $board = MedicineSupply::board();

    cms_json([
        'today'     => $board['today'],
        'dueToday'  => $board['dueToday'],
        'overdue'   => $board['overdue'],
        'upcoming'  => $board['upcoming'],
        'later'     => $board['later'],
        'recent'    => MedicineDelivery::recent(10),
        'counts'    => [
            'dueToday'          => count($board['dueToday']),
            'overdue'           => count($board['overdue']),
            'upcoming'          => count($board['upcoming']),
            'outOfStock'        => MedicineSupply::countOutOfStock(),
            'deliveredThisMonth' => MedicineSupply::countDeliveredThisMonth(),
        ],
        'defaultDeferDays' => MedicineSupply::DEFAULT_OUT_OF_STOCK_DEFER,
    ]);
}

/** One case's cycle + delivery history — the Medicine tab on the patient page. */
function medicine_show(int $caseId): void
{
    cms_require_auth();

    $case = CareCase::find($caseId);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }

    $supply = MedicineSupply::forCase($caseId);

    cms_json([
        'supply'  => $supply ? MedicineSupply::shape($supply) : null,
        'history' => MedicineDelivery::forCase($caseId),
        'case'    => CareCase::shape($case),
        'patient' => Patient::shape(Patient::find((int) $case['patient_id']) ?? []),
    ]);
}

/**
 * Record a delivery. This is the one action the whole feature is built around:
 * it logs the delivery, moves `next_due_on` forward by the interval, closes the
 * day's notification, and returns the new due date so the UI can say
 * "Next due: 25 Aug" immediately.
 */
function medicine_deliver(int $caseId): void
{
    $user = cms_require_auth();

    $case = CareCase::find($caseId);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }
    if ($case['status'] !== 'active') {
        cms_error('conflict', 'Medicine can only be delivered on an active case.', 409);
    }

    $supply = MedicineSupply::forCase($caseId);
    if (!$supply) {
        // Older cases activated before this feature existed — start the cycle now.
        MedicineSupply::start($caseId, (int) $case['patient_id'], $case['start_date']);
        $supply = MedicineSupply::forCase($caseId);
    }

    $deliveredOn = cms_str('deliveredOn', cms_today());
    if (!cms_is_date($deliveredOn)) {
        cms_error('validation', 'Enter a valid delivery date.', 422, ['deliveredOn' => 'Use the format YYYY-MM-DD.']);
    }
    if (cms_days_between(cms_today(), $deliveredOn) > 0) {
        cms_error('validation', 'A delivery cannot be dated in the future.', 422, [
            'deliveredOn' => 'Pick today or an earlier date.',
        ]);
    }

    $result = MedicineSupply::deliver($caseId, [
        'deliveredOn' => $deliveredOn,
        'mode'        => cms_str('mode', 'courier'),
        'reference'   => cms_str('reference'),
        'notes'       => cms_str('notes'),
        'deliveredBy' => (int) $user['id'],
    ]);

    $closed = Notification::resolveMedicine($caseId);

    $patient = Patient::find((int) $case['patient_id']);
    cms_log(
        'medicine',
        $caseId,
        'delivered',
        sprintf(
            'Medicine delivered to %s on %s — next due %s',
            $patient['child_name'] ?? $case['code'],
            date('j M', strtotime($result['deliveredOn'])),
            date('j M', strtotime($result['nextDueOn']))
        )
    );

    cms_json([
        'delivery'  => $result,
        'supply'    => MedicineSupply::shape(MedicineSupply::forCase($caseId) ?? []),
        'history'   => MedicineDelivery::forCase($caseId, 10),
        'notificationsClosed' => $closed,
        'message'   => sprintf(
            'Delivered. Next supply due on %s.',
            date('j M Y', strtotime($result['nextDueOn']))
        ),
    ], 201);
}

/**
 * The medicine is not in stock.
 *
 * Nothing is delivered: the next due date slips by `deferDays` (5 by default),
 * the case is flagged out-of-stock, today's "deliver today" notice is retired,
 * and a notice goes up saying when the delivery will now happen. When that new
 * date arrives the ordinary morning notification fires again.
 */
function medicine_out_of_stock(int $caseId): void
{
    $user = cms_require_auth();

    $case = CareCase::find($caseId);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }
    if ($case['status'] !== 'active') {
        cms_error('conflict', 'Stock can only be recorded on an active case.', 409);
    }

    if (!MedicineSupply::forCase($caseId)) {
        // Cases activated before the medicine cycle existed.
        MedicineSupply::start($caseId, (int) $case['patient_id'], $case['start_date']);
    }

    $deferDays = cms_int('deferDays', MedicineSupply::DEFAULT_OUT_OF_STOCK_DEFER);
    if ($deferDays < 1 || $deferDays > 60) {
        cms_error('validation', 'The deferral must be between 1 and 60 days.', 422, [
            'deferDays' => 'Between 1 and 60 days.',
        ]);
    }

    $result = MedicineSupply::markOutOfStock($caseId, [
        'deferDays'  => $deferDays,
        'notes'      => cms_str('notes'),
        'recordedBy' => (int) $user['id'],
    ]);

    $patient = Patient::find((int) $case['patient_id']);
    $name    = $patient['child_name'] ?? $case['code'];

    // The deferred case is not actionable today; replace the due notice.
    Notification::supersedeMedicineDue($caseId);
    Notification::medicineOutOfStock(
        $caseId,
        (int) $case['patient_id'],
        $name,
        $result['nextDueOn'],
        $result['deferDays']
    );

    cms_log('medicine', $caseId, 'out_of_stock', sprintf(
        'Medicine out of stock for %s — delivery deferred %d days to %s',
        $name,
        $result['deferDays'],
        date('j M', strtotime($result['nextDueOn']))
    ));

    cms_json([
        'supply'  => MedicineSupply::shape(MedicineSupply::forCase($caseId) ?? []),
        'history' => MedicineDelivery::forCase($caseId, 10),
        'message' => sprintf(
            'Marked out of stock. %s\'s delivery moved to %s.',
            $name,
            date('j M Y', strtotime($result['nextDueOn']))
        ),
    ], 201);
}

/** Adjust the interval, move the next due date by hand, or pause the cycle. */
function medicine_update(int $caseId): void
{
    cms_require_role(['founder', 'senior_doctor', 'case_doctor', 'coordinator']);

    $case = CareCase::find($caseId);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }

    if (!MedicineSupply::forCase($caseId)) {
        MedicineSupply::start($caseId, (int) $case['patient_id'], $case['start_date']);
    }

    $body = cms_body();
    if (isset($body['nextDueOn']) && !cms_is_date((string) $body['nextDueOn'])) {
        cms_error('validation', 'Enter a valid date.', 422, ['nextDueOn' => 'Use the format YYYY-MM-DD.']);
    }
    if (isset($body['intervalDays']) && ((int) $body['intervalDays'] < 1 || (int) $body['intervalDays'] > 90)) {
        cms_error('validation', 'Interval must be between 1 and 90 days.', 422, [
            'intervalDays' => 'Between 1 and 90 days.',
        ]);
    }

    MedicineSupply::update($caseId, $body);

    $supply = MedicineSupply::forCase($caseId) ?? [];
    cms_log('medicine', $caseId, 'cycle_updated', sprintf(
        'Medicine cycle set to every %d days, next due %s',
        (int) ($supply['interval_days'] ?? 15),
        date('j M', strtotime($supply['next_due_on'] ?? cms_today()))
    ));

    cms_json(['supply' => MedicineSupply::shape($supply)]);
}
