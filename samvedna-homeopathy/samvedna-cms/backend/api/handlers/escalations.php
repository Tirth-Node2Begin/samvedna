<?php
/**
 * Escalation board: raising, advancing up the chain, and resolving inside the
 * 7-day SLA.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Escalation.php';
require_once __DIR__ . '/../../includes/models/CareCase.php';
require_once __DIR__ . '/../../includes/models/Patient.php';
require_once __DIR__ . '/../../includes/models/Review.php';

function escalations_index(): void
{
    cms_require_auth();

    $escalations = Escalation::feed([
        'status'   => cms_query('status'),
        'level'    => cms_query('level'),
        'openOnly' => cms_query('open') === '1',
        'limit'    => (int) (cms_query('limit') ?: 200),
    ]);

    cms_json([
        'escalations' => $escalations,
        'reasons'     => cms_escalation_reasons(),
        'counts'      => [
            'open'       => count(array_filter($escalations, static fn($e) => $e['status'] === 'open')),
            'inProgress' => count(array_filter($escalations, static fn($e) => $e['status'] === 'in_progress')),
            'resolved'   => count(array_filter($escalations, static fn($e) => $e['status'] === 'resolved')),
            'overdue'    => count(array_filter($escalations, static fn($e) => $e['isOverdue'])),
        ],
    ]);
}

function escalations_show(int $id): void
{
    cms_require_auth();

    $escalation = Escalation::find($id);
    if (!$escalation) {
        cms_error('not_found', 'Escalation not found.', 404);
    }

    $case    = CareCase::find((int) $escalation['case_id']);
    $patient = $case ? Patient::find((int) $case['patient_id']) : null;
    $raisedBy = $escalation['raised_by']
        ? cms_one('SELECT name FROM cms_users WHERE id = ?', [$escalation['raised_by']])
        : null;

    cms_json([
        'escalation' => Escalation::shape($escalation) + ['raisedByName' => $raisedBy['name'] ?? 'System'],
        'steps'      => EscalationStep::forEscalation($id),
        'reasons'    => cms_escalation_reasons(),
        'case'       => $case ? CareCase::shape($case) : null,
        'patient'    => $patient ? Patient::shape($patient) : null,
        'review'     => $escalation['review_id'] ? Review::shape(Review::find((int) $escalation['review_id']) ?? []) : null,
    ]);
}

function escalations_store(): void
{
    $user = cms_require_role(['founder', 'senior_doctor', 'case_doctor']);

    $caseId = cms_int('caseId');
    $case = CareCase::find($caseId);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }

    $reasons = cms_arr('reasons');
    $valid = array_column(cms_escalation_reasons(), 'key');
    $reasons = array_values(array_intersect($reasons, $valid));

    $notes = cms_str('notes');
    $fields = [];
    if (!$reasons) {
        $fields['reasons'] = 'Select at least one reason for the escalation.';
    }
    if ($notes === '') {
        $fields['notes'] = 'Clinical notes for the escalation are mandatory.';
    }
    if ($fields) {
        cms_error('validation', 'Complete the escalation before raising it.', 422, $fields);
    }

    $id = Escalation::create([
        'caseId'         => $caseId,
        'reviewId'       => cms_int('reviewId'),
        'raisedBy'       => (int) $user['id'],
        'reasons'        => $reasons,
        'notes'          => $notes,
        'seniorDoctorId' => $case['senior_doctor_id'] ? (int) $case['senior_doctor_id'] : null,
        'founderId'      => $case['founder_id'] ? (int) $case['founder_id'] : null,
    ]);

    // Mirror the decision onto the review that triggered it, so the review screen
    // shows the escalation instead of a stale "No — progress on track".
    $reviewId = cms_int('reviewId');
    if ($reviewId > 0) {
        cms_run('UPDATE cms_reviews SET escalate = 1, escalate_note = ? WHERE id = ?', [$notes, $reviewId]);
    }

    cms_log('escalation', $id, 'raised', 'Escalation raised on ' . $case['code']);

    cms_json([
        'escalation' => Escalation::shape(Escalation::find($id) ?? []),
        'steps'      => EscalationStep::forEscalation($id),
    ], 201);
}

/** Push the chain from Senior Doctor to Founder. */
function escalations_advance(int $id): void
{
    cms_require_role(['founder', 'senior_doctor']);

    $escalation = Escalation::find($id);
    if (!$escalation) {
        cms_error('not_found', 'Escalation not found.', 404);
    }
    if ($escalation['status'] === 'resolved') {
        cms_error('conflict', 'This escalation is already resolved.', 409);
    }
    if ($escalation['level'] === 'founder') {
        cms_error('conflict', 'This escalation is already with the Founder.', 409);
    }

    Escalation::advance($id);
    cms_log('escalation', $id, 'advanced', $escalation['code'] . ' escalated to the Founder');

    cms_json([
        'escalation' => Escalation::shape(Escalation::find($id) ?? []),
        'steps'      => EscalationStep::forEscalation($id),
    ]);
}

function escalations_resolve(int $id): void
{
    $user = cms_require_role(['founder', 'senior_doctor']);

    $escalation = Escalation::find($id);
    if (!$escalation) {
        cms_error('not_found', 'Escalation not found.', 404);
    }
    if ($escalation['status'] === 'resolved') {
        cms_error('conflict', 'This escalation is already resolved.', 409);
    }

    $notes = cms_str('resolutionNotes');
    if ($notes === '') {
        cms_error('validation', 'Resolution notes are required to close an escalation.', 422, [
            'resolutionNotes' => 'Describe how the escalation was resolved.',
        ]);
    }

    Escalation::resolve($id, $notes, (int) $user['id']);

    $shaped = Escalation::shape(Escalation::find($id) ?? []);
    cms_log('escalation', $id, 'resolved', $escalation['code'] . ' resolved' . ($shaped['daysLeft'] < 0 ? ' (past SLA)' : ' within SLA'));

    cms_json(['escalation' => $shaped, 'steps' => EscalationStep::forEscalation($id)]);
}
