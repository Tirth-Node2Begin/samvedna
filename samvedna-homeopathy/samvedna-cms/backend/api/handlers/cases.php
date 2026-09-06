<?php
/**
 * Case-level reads and role/status changes.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/CareCase.php';
require_once __DIR__ . '/../../includes/models/Patient.php';
require_once __DIR__ . '/../../includes/models/ScheduleEvent.php';
require_once __DIR__ . '/../../includes/models/Review.php';
require_once __DIR__ . '/../../includes/models/Escalation.php';
require_once __DIR__ . '/../../includes/models/ProgressReport.php';

function cases_show(int $id): void
{
    cms_require_auth();

    $case = CareCase::find($id);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }

    ScheduleEvent::refreshStatuses($id);
    $patient = Patient::find((int) $case['patient_id']);

    cms_json([
        'case'        => CareCase::shape($case),
        'patient'     => Patient::shape($patient ?? []),
        'baseline'    => Baseline::shape(Baseline::forPatient((int) $case['patient_id']) ?? []),
        'schedule'    => ScheduleEvent::forCase($id),
        'cycles'      => CareCase::cycles($case),
        'adherence'   => AdherenceCheck::forCase($id),
        'reviews'     => array_map(static function (array $r): array {
            $shaped = Review::shape($r);
            $shaped['signoffs'] = ReviewSignoff::forReview((int) $r['id']);
            $shaped['goals']    = ReviewGoal::forReview((int) $r['id']);
            return $shaped;
        }, Review::forCase($id)),
        'escalations' => Escalation::forCase($id),
        'progress'    => ProgressReport::forCase($id),
    ]);
}

function cases_roles(int $id): void
{
    cms_require_role(['founder', 'senior_doctor']);

    $case = CareCase::find($id);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }

    $caseDoctorId = cms_int('caseDoctorId');
    if ($caseDoctorId <= 0) {
        cms_error('validation', 'A Case Doctor must remain assigned.', 422, [
            'caseDoctorId' => 'Case Doctor (primary) is mandatory.',
        ]);
    }

    CareCase::assignRoles($id, $caseDoctorId, cms_int('seniorDoctorId') ?: null, cms_int('founderId') ?: null);
    cms_log('case', $id, 'roles_updated', 'Reassigned doctors on ' . $case['code']);

    cms_json(['case' => CareCase::shape(CareCase::find($id) ?? [])]);
}

function cases_status(int $id): void
{
    cms_require_role(['founder', 'senior_doctor']);

    $case = CareCase::find($id);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }

    $status = cms_str('status');
    if (!in_array($status, ['active', 'paused', 'completed', 'closed'], true)) {
        cms_error('validation', 'Unknown case status.', 422, ['status' => 'Pick active, paused, completed or closed.']);
    }

    CareCase::setStatus($id, $status);

    // Keep the patient record in step so the register does not show an active
    // patient whose only case is closed.
    $patientStatus = [
        'active'    => 'active',
        'paused'    => 'on_hold',
        'completed' => 'completed',
        'closed'    => 'discharged',
    ][$status];
    Patient::setStatus((int) $case['patient_id'], $patientStatus);

    cms_log('case', $id, 'status', $case['code'] . ' set to ' . $status);
    cms_json(['case' => CareCase::shape(CareCase::find($id) ?? [])]);
}
