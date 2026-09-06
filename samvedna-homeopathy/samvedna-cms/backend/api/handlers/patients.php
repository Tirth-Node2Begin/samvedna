<?php
/**
 * Patient register, the mandatory baseline, and case activation.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Patient.php';
require_once __DIR__ . '/../../includes/models/CareCase.php';
require_once __DIR__ . '/../../includes/models/Plan.php';
require_once __DIR__ . '/../../includes/models/ScheduleEvent.php';
require_once __DIR__ . '/../../includes/models/Review.php';
require_once __DIR__ . '/../../includes/models/Escalation.php';
require_once __DIR__ . '/../../includes/models/ProgressReport.php';
require_once __DIR__ . '/../../includes/services/ScheduleGenerator.php';
require_once __DIR__ . '/../../includes/models/Medicine.php';

function patients_index(): void
{
    cms_require_auth();

    $patients = Patient::all([
        'search'    => cms_query('search'),
        'status'    => cms_query('status'),
        'condition' => cms_query('condition'),
        'severity'  => cms_query('severity'),
        'doctorId'  => cms_query('doctorId'),
    ]);

    cms_json([
        'patients' => $patients,
        'counts'   => [
            'total'  => count($patients),
            'active' => count(array_filter($patients, static fn($p) => $p['status'] === 'active')),
            'draft'  => count(array_filter($patients, static fn($p) => $p['status'] === 'draft')),
        ],
    ]);
}

function patients_store(): void
{
    $user = cms_require_auth();

    $childName = cms_str('childName');
    if ($childName === '') {
        cms_error('validation', 'The child name is required.', 422, ['childName' => 'Child name is required.']);
    }

    $body = cms_body();
    $id = Patient::create($body, (int) $user['id']);

    // The baseline is always created alongside the patient, even when empty, so
    // the intake screen has a row to write into and the completeness verdict is
    // meaningful from the first save.
    Baseline::save($id, [
        'conditionType'      => $body['conditionType'] ?? '',
        'severity'           => $body['severity'] ?? '',
        'diagnosisAge'       => $body['diagnosisAge'] ?? '',
        'medicalHistory'     => $body['medicalHistory'] ?? '',
        'therapyInvolvement' => $body['therapyInvolvement'] ?? '',
        'concerns'           => $body['concerns'] ?? [],
        'markers'            => $body['markers'] ?? [],
    ]);

    $patient = Patient::find($id);
    cms_log('patient', $id, 'created', 'Registered ' . $childName);

    cms_json([
        'patient'  => Patient::shape($patient ?? []),
        'baseline' => Baseline::shape(Baseline::forPatient($id) ?? []),
        'verdict'  => Baseline::verdictFor(Baseline::forPatient($id)),
    ], 201);
}

/** Full case workspace payload: patient, baseline, case, schedule, reviews, escalations, progress. */
function patients_show(int $id): void
{
    cms_require_auth();

    $patient = Patient::find($id);
    if (!$patient) {
        cms_error('not_found', 'Patient not found.', 404);
    }

    $baselineRow = Baseline::forPatient($id);
    $case = CareCase::anyForPatient($id);

    $payload = [
        'patient'  => Patient::shape($patient),
        'baseline' => Baseline::shape($baselineRow ?? []),
        'verdict'  => Baseline::verdictFor($baselineRow),
        'case'     => null,
        'schedule' => [],
        'cycles'   => [],
        'reviews'  => [],
        'escalations' => [],
        'progress' => [],
        'adherence' => [],
        'medicine' => ['supply' => null, 'history' => []],
    ];

    if ($case) {
        $caseId = (int) $case['id'];
        ScheduleEvent::refreshStatuses($caseId);

        $payload['case']        = CareCase::shape($case);
        $payload['schedule']    = ScheduleEvent::forCase($caseId);
        $payload['cycles']      = CareCase::cycles($case);
        $payload['adherence']   = AdherenceCheck::forCase($caseId);
        $payload['escalations'] = Escalation::forCase($caseId);
        $payload['progress']    = ProgressReport::forCase($caseId);
        $supplyRow = MedicineSupply::forCase($caseId);
        $payload['medicine']    = [
            'supply'  => $supplyRow ? MedicineSupply::shape($supplyRow) : null,
            'history' => MedicineDelivery::forCase($caseId, 10),
        ];
        $reviewRows = Review::forCase($caseId);
        $reviewIds = array_map(static fn(array $r): int => (int) $r['id'], $reviewRows);
        $signoffsByReview = ReviewSignoff::forReviews($reviewIds);
        $goalsByReview = ReviewGoal::forReviews($reviewIds);
        $payload['reviews']     = array_map(static function (array $r) use ($signoffsByReview, $goalsByReview): array {
            $shaped = Review::shape($r);
            $shaped['signoffs'] = $signoffsByReview[(int) $r['id']] ?? [];
            $shaped['goals']    = $goalsByReview[(int) $r['id']] ?? [];
            return $shaped;
        }, $reviewRows);
    }

    cms_json($payload);
}

function patients_update(int $id): void
{
    cms_require_auth();

    $patient = Patient::find($id);
    if (!$patient) {
        cms_error('not_found', 'Patient not found.', 404);
    }

    $body = cms_body();
    $body['status'] = $body['status'] ?? $patient['status'];
    if (trim((string) ($body['childName'] ?? '')) === '') {
        cms_error('validation', 'The child name is required.', 422, ['childName' => 'Child name is required.']);
    }

    Patient::update($id, $body);
    cms_log('patient', $id, 'updated', 'Updated profile for ' . $body['childName']);

    cms_json(['patient' => Patient::shape(Patient::find($id) ?? [])]);
}

function patients_destroy(int $id): void
{
    cms_require_role(['founder', 'senior_doctor']);

    $patient = Patient::find($id);
    if (!$patient) {
        cms_error('not_found', 'Patient not found.', 404);
    }
    if ($patient['status'] !== 'draft' || CareCase::anyForPatient($id)) {
        cms_error(
            'conflict',
            'Only draft patients with no case history can be deleted. Discharge the patient instead.',
            409
        );
    }

    Patient::delete($id);
    cms_log('patient', $id, 'deleted', 'Deleted draft ' . $patient['child_name']);
    cms_json(['ok' => true]);
}

/** Save the mandatory baseline and return the fresh completeness verdict. */
function patients_baseline(int $id): void
{
    cms_require_auth();

    if (!Patient::find($id)) {
        cms_error('not_found', 'Patient not found.', 404);
    }

    $baseline = Baseline::save($id, cms_body());
    $verdict  = Baseline::verdictFor(Baseline::forPatient($id));

    cms_log('baseline', $id, 'saved', $verdict['complete'] ? 'Baseline completed' : 'Baseline saved as draft');

    cms_json(['baseline' => $baseline, 'verdict' => $verdict]);
}

/**
 * Step 2 of the flow: confirm a plan, assign roles, activate the case and
 * generate the whole follow-up schedule in one transaction.
 */
function patients_activate(int $id): void
{
    $user = cms_require_role(['founder', 'senior_doctor', 'case_doctor', 'coordinator']);

    $patient = Patient::find($id);
    if (!$patient) {
        cms_error('not_found', 'Patient not found.', 404);
    }
    if (CareCase::activeForPatient($id)) {
        cms_error('conflict', 'This patient already has an active care plan.', 409);
    }

    // --- The activation gate -------------------------------------------------
    $baselineRow = Baseline::forPatient($id);
    $verdict = Baseline::verdictFor($baselineRow);
    if (!$verdict['complete']) {
        cms_error(
            'baseline_incomplete',
            'Case activation blocked — complete all baseline fields to proceed.',
            409,
            $verdict['missing']
        );
    }

    // --- Plan ----------------------------------------------------------------
    $plan = Plan::find(cms_int('planId'));
    if (!$plan) {
        cms_error('validation', 'Select a care plan.', 422, ['planId' => 'A care plan is required.']);
    }

    // --- Roles ---------------------------------------------------------------
    $caseDoctorId   = cms_int('caseDoctorId');
    $seniorDoctorId = cms_int('seniorDoctorId');
    $founderId      = cms_int('founderId');

    $roleErrors = [];
    if ($caseDoctorId <= 0) {
        $roleErrors['caseDoctorId'] = 'A Case Doctor (primary) must be assigned.';
    }
    if ($plan['requiresSenior'] && $seniorDoctorId <= 0) {
        $roleErrors['seniorDoctorId'] = 'The ' . $plan['name'] . ' plan requires a Senior Doctor (secondary).';
    }
    if ($plan['requiresFounder'] && $founderId <= 0) {
        $roleErrors['founderId'] = 'The ' . $plan['name'] . ' plan includes founder review — assign the founder.';
    }
    if ($roleErrors) {
        cms_error('validation', 'Doctor role assignment is mandatory.', 422, $roleErrors);
    }

    // --- Dates ---------------------------------------------------------------
    $startDate = cms_str('startDate', cms_today());
    if (!cms_is_date($startDate)) {
        cms_error('validation', 'Provide a valid plan start date.', 422, ['startDate' => 'Use the format YYYY-MM-DD.']);
    }
    $cycles  = ScheduleGenerator::cycleCount($plan);
    $endDate = cms_add_months($startDate, (int) $plan['durationMonths']);

    // --- Create + generate ---------------------------------------------------
    $pdo = cms_db();
    $pdo->beginTransaction();
    try {
        $caseId = CareCase::create([
            'patientId'      => $id,
            'planId'         => (int) $plan['id'],
            'caseDoctorId'   => $caseDoctorId,
            'seniorDoctorId' => $seniorDoctorId,
            'founderId'      => $founderId,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'totalCycles'    => $cycles,
            'activatedBy'    => (int) $user['id'],
        ]);

        ScheduleGenerator::build($caseId, $plan, $startDate, [
            'caseDoctorId' => $caseDoctorId,
            'founderId'    => $founderId,
        ]);

        // The first medicine goes out with the plan; the 15-day cycle runs from there.
        MedicineSupply::start($caseId, $id, $startDate);

        Patient::setStatus($id, 'active');
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    cms_log('case', $caseId, 'activated', $patient['child_name'] . ' activated on the ' . $plan['name'] . ' plan');

    $case = CareCase::find($caseId);
    cms_json([
        'case'     => CareCase::shape($case ?? []),
        'schedule' => ScheduleEvent::forCase($caseId),
        'cycles'   => CareCase::cycles($case ?? []),
    ], 201);
}
