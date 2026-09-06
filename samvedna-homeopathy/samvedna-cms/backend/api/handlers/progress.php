<?php
/**
 * The auto-generated progress dashboard, and sharing it with the parent.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/ProgressReport.php';
require_once __DIR__ . '/../../includes/models/CareCase.php';
require_once __DIR__ . '/../../includes/models/Patient.php';
require_once __DIR__ . '/../../includes/models/Review.php';
require_once __DIR__ . '/../../includes/services/ProgressBuilder.php';

/** Every generated report, newest first — the practice-wide progress board. */
function progress_index(): void
{
    cms_require_auth();

    $sql = "SELECT r.*, c.code AS case_code, p.id AS patient_id, p.child_name, p.code AS patient_code,
                   u.name AS doctor_name
            FROM cms_progress_reports r
            JOIN cms_cases c ON c.id = r.case_id
            JOIN cms_patients p ON p.id = c.patient_id
            LEFT JOIN cms_users u ON u.id = c.case_doctor_id
            WHERE 1 = 1";
    $params = [];

    if (($trend = cms_query('trend')) !== '') {
        $sql .= ' AND r.overall_trend = ?';
        $params[] = $trend;
    }
    if (cms_query('shared') === '0') {
        $sql .= ' AND r.shared_at IS NULL';
    } elseif (cms_query('shared') === '1') {
        $sql .= ' AND r.shared_at IS NOT NULL';
    }

    $sql .= ' ORDER BY r.created_at DESC, r.id DESC LIMIT 200';

    $reports = array_map(static function (array $r): array {
        $shaped = ProgressReport::shape($r);
        $shaped['caseCode']    = $r['case_code'];
        $shaped['patientId']   = (int) $r['patient_id'];
        $shaped['patientName'] = $r['child_name'];
        $shaped['patientCode'] = $r['patient_code'];
        $shaped['doctorName']  = $r['doctor_name'] ?? '';
        return $shaped;
    }, cms_all($sql, $params));

    cms_json([
        'reports' => $reports,
        'counts'  => [
            'total'    => count($reports),
            'shared'   => count(array_filter($reports, static fn($r) => $r['sharedAt'] !== null)),
            'unshared' => count(array_filter($reports, static fn($r) => $r['sharedAt'] === null)),
        ],
    ]);
}

function progress_show(int $caseId, int $cycle): void
{
    cms_require_auth();

    $case = CareCase::find($caseId);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }

    $report = ProgressReport::forCycle($caseId, $cycle);

    // Regenerate on demand when the review for this cycle is closed but the
    // report is missing — e.g. after a schema reset or a re-seed.
    if (!$report) {
        $review = Review::forCaseCycle($caseId, $cycle);
        if ($review && $review['status'] === 'closed') {
            ProgressBuilder::build($case, $review);
            $report = ProgressReport::forCycle($caseId, $cycle);
        }
    }

    if (!$report) {
        cms_error(
            'not_generated',
            'No progress report yet for cycle ' . $cycle . '. It is generated automatically when that cycle review closes.',
            404
        );
    }

    $patient = Patient::find((int) $case['patient_id']);
    $review  = $report['review_id'] ? Review::find((int) $report['review_id']) : null;

    // The next cycle's objectives are this cycle's goals carried forward.
    $nextObjectives = ReviewGoal::forCycle($caseId, $cycle);

    cms_json([
        'report'   => ProgressReport::shape($report),
        'case'     => CareCase::shape($case),
        'patient'  => $patient ? Patient::shape($patient) : null,
        'review'   => $review ? Review::shape($review) : null,
        'objectives' => $nextObjectives,
        'history'  => ProgressReport::forCase($caseId),
        'nextCycle' => min((int) $case['total_cycles'], $cycle + 1),
    ]);
}

function progress_share(int $id): void
{
    cms_require_auth();

    $report = ProgressReport::find($id);
    if (!$report) {
        cms_error('not_found', 'Progress report not found.', 404);
    }

    $channel = cms_str('channel', 'whatsapp');
    ProgressReport::share($id, $channel);

    cms_log('progress', $id, 'shared', 'Cycle ' . $report['cycle'] . ' report shared via ' . $channel);

    cms_json(['report' => ProgressReport::shape(ProgressReport::find($id) ?? [])]);
}
