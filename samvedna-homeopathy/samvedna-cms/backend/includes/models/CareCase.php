<?php
/**
 * A patient on a plan. Named CareCase because `Case` is a reserved word in PHP.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/Plan.php';

class CareCase
{
    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_cases WHERE id = ? LIMIT 1', [$id]);
    }

    /** The live case for a patient (active or paused), or null. */
    public static function activeForPatient(int $patientId): ?array
    {
        return cms_one(
            "SELECT * FROM cms_cases WHERE patient_id = ? AND status IN ('active','paused')
             ORDER BY id DESC LIMIT 1",
            [$patientId]
        );
    }

    public static function anyForPatient(int $patientId): ?array
    {
        return cms_one('SELECT * FROM cms_cases WHERE patient_id = ? ORDER BY id DESC LIMIT 1', [$patientId]);
    }

    public static function create(array $d): int
    {
        $code = cms_next_code('cms_cases', 'CASE');
        return cms_insert(
            'INSERT INTO cms_cases
                (code, patient_id, plan_id, case_doctor_id, senior_doctor_id, founder_id,
                 start_date, end_date, total_cycles, current_cycle, status, activated_by, activated_at,
                 created_at, updated_at)
             VALUES
                (:code, :patient_id, :plan_id, :case_doctor_id, :senior_doctor_id, :founder_id,
                 :start_date, :end_date, :total_cycles, 1, :status, :activated_by, NOW(), NOW(), NOW())',
            [
                'code'             => $code,
                'patient_id'       => $d['patientId'],
                'plan_id'          => $d['planId'],
                'case_doctor_id'   => $d['caseDoctorId'] ?: null,
                'senior_doctor_id' => $d['seniorDoctorId'] ?: null,
                'founder_id'       => $d['founderId'] ?: null,
                'start_date'       => $d['startDate'],
                'end_date'         => $d['endDate'],
                'total_cycles'     => $d['totalCycles'],
                'status'           => $d['status'] ?? 'active',
                'activated_by'     => $d['activatedBy'] ?: null,
            ]
        );
    }

    public static function advanceCycle(int $id): void
    {
        // Never run past the plan length: the last review closes the case instead
        // of pushing current_cycle beyond total_cycles.
        cms_run(
            "UPDATE cms_cases
             SET current_cycle = LEAST(current_cycle + 1, total_cycles),
                 status = CASE WHEN current_cycle + 1 > total_cycles THEN 'completed' ELSE status END,
                 updated_at = NOW()
             WHERE id = ?",
            [$id]
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        cms_run('UPDATE cms_cases SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
    }

    public static function assignRoles(int $id, ?int $caseDoctor, ?int $seniorDoctor, ?int $founder): void
    {
        cms_run(
            'UPDATE cms_cases SET case_doctor_id = ?, senior_doctor_id = ?, founder_id = ?, updated_at = NOW()
             WHERE id = ?',
            [$caseDoctor ?: null, $seniorDoctor ?: null, $founder ?: null, $id]
        );
    }

    /** Case + plan + assigned people, in API shape. */
    public static function shape(array $c): array
    {
        $plan = Plan::find((int) $c['plan_id']);
        $names = cms_one(
            'SELECT
                (SELECT name FROM cms_users WHERE id = ?) AS case_doctor,
                (SELECT name FROM cms_users WHERE id = ?) AS senior_doctor,
                (SELECT name FROM cms_users WHERE id = ?) AS founder',
            [$c['case_doctor_id'], $c['senior_doctor_id'], $c['founder_id']]
        ) ?: [];

        $elapsed = max(0, cms_days_between($c['start_date'], cms_today()));
        $total   = max(1, cms_days_between($c['start_date'], $c['end_date']));

        return [
            'id'             => (int) $c['id'],
            'code'           => $c['code'],
            'patientId'      => (int) $c['patient_id'],
            'planId'         => (int) $c['plan_id'],
            'plan'           => $plan,
            'caseDoctorId'   => $c['case_doctor_id'] ? (int) $c['case_doctor_id'] : null,
            'seniorDoctorId' => $c['senior_doctor_id'] ? (int) $c['senior_doctor_id'] : null,
            'founderId'      => $c['founder_id'] ? (int) $c['founder_id'] : null,
            'caseDoctorName'   => $names['case_doctor'] ?? '',
            'seniorDoctorName' => $names['senior_doctor'] ?? '',
            'founderName'      => $names['founder'] ?? '',
            'startDate'      => $c['start_date'],
            'endDate'        => $c['end_date'],
            'totalCycles'    => (int) $c['total_cycles'],
            'currentCycle'   => (int) $c['current_cycle'],
            'cycleLabel'     => 'Cycle ' . (int) $c['current_cycle'] . ' of ' . (int) $c['total_cycles'],
            'status'         => $c['status'],
            'activatedAt'    => $c['activated_at'],
            'elapsedPercent' => min(100, (int) round(($elapsed / $total) * 100)),
            'daysRemaining'  => max(0, cms_days_between(cms_today(), $c['end_date'])),
        ];
    }

    /** Per-cycle windows and completion, for the schedule overview bars. */
    public static function cycles(array $c): array
    {
        $plan = Plan::find((int) $c['plan_id']);
        $interval = $plan['reviewIntervalDays'] ?? 60;
        $out = [];
        $eventCounts = [];

        foreach (cms_all(
            "SELECT cycle,
                    COUNT(*) AS total_events,
                    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done_events
             FROM cms_schedule_events
             WHERE case_id = ?
             GROUP BY cycle",
            [$c['id']]
        ) as $row) {
            $eventCounts[(int) $row['cycle']] = [
                'total' => (int) $row['total_events'],
                'done'  => (int) $row['done_events'],
            ];
        }

        for ($i = 1; $i <= (int) $c['total_cycles']; $i++) {
            $start = cms_add_days($c['start_date'], $interval * ($i - 1));
            $end   = cms_add_days($c['start_date'], $interval * $i);
            $counts = $eventCounts[$i] ?? ['total' => 0, 'done' => 0];
            $totalEvents = $counts['total'];
            $doneEvents = $counts['done'];

            $out[] = [
                'cycle'    => $i,
                'start'    => $start,
                'end'      => $end,
                'label'    => date('M j', strtotime($start)) . ' – ' . date('M j, Y', strtotime($end)),
                'events'   => $totalEvents,
                'done'     => $doneEvents,
                'percent'  => $totalEvents > 0 ? (int) round(($doneEvents / $totalEvents) * 100) : 0,
                'isCurrent' => $i === (int) $c['current_cycle'],
            ];
        }

        return $out;
    }
}
