<?php
/**
 * Follow-up touchpoints and the adherence checks recorded against them.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class ScheduleEvent
{
    public const TYPE_LABELS = [
        'activation'     => 'Plan activation & baseline',
        'adherence'      => 'Adherence check',
        'review'         => 'Bi-monthly formal review',
        'founder_review' => 'Founder review',
    ];

    /** Adherence checks are non-consult touchpoints — the UI badges them differently. */
    public static function isConsult(string $type): bool
    {
        return $type !== 'adherence';
    }

    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_schedule_events WHERE id = ? LIMIT 1', [$id]);
    }

    public static function forCase(int $caseId, ?int $cycle = null): array
    {
        $sql = 'SELECT * FROM cms_schedule_events WHERE case_id = ?';
        $params = [$caseId];
        if ($cycle !== null) {
            $sql .= ' AND cycle = ?';
            $params[] = $cycle;
        }
        $sql .= ' ORDER BY due_date, FIELD(type,\'activation\',\'adherence\',\'review\',\'founder_review\'), id';
        return array_map([self::class, 'shape'], cms_all($sql, $params));
    }

    /** Cross-case schedule feed for the global Schedule screen and the dashboard. */
    public static function feed(array $filters = []): array
    {
        $sql = "SELECT e.*, c.code AS case_code, p.id AS patient_id, p.child_name, p.code AS patient_code,
                       u.name AS owner_name
                FROM cms_schedule_events e
                JOIN cms_cases c ON c.id = e.case_id
                JOIN cms_patients p ON p.id = c.patient_id
                LEFT JOIN cms_users u ON u.id = e.owner_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND e.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $sql .= ' AND e.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND e.due_date >= ?';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND e.due_date <= ?';
            $params[] = $filters['to'];
        }
        if (!empty($filters['ownerId'])) {
            $sql .= ' AND e.owner_id = ?';
            $params[] = (int) $filters['ownerId'];
        }
        if (!empty($filters['pendingOnly'])) {
            $sql .= " AND e.status IN ('scheduled','upcoming','missed')";
        }

        $sql .= ' ORDER BY e.due_date ASC, e.id ASC';
        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
        }

        return array_map(static function (array $e): array {
            $shaped = self::shape($e);
            $shaped['caseCode']     = $e['case_code'];
            $shaped['patientId']    = (int) $e['patient_id'];
            $shaped['patientName']  = $e['child_name'];
            $shaped['patientCode']  = $e['patient_code'];
            $shaped['ownerName']    = $e['owner_name'] ?? '';
            return $shaped;
        }, cms_all($sql, $params));
    }

    public static function create(array $d): int
    {
        return cms_insert(
            'INSERT INTO cms_schedule_events
                (case_id, cycle, type, title, due_date, original_due_date, status, owner_id, notes, created_at, updated_at)
             VALUES (:case_id, :cycle, :type, :title, :due_date, :due_date2, :status, :owner_id, :notes, NOW(), NOW())',
            [
                'case_id'  => $d['caseId'],
                'cycle'    => $d['cycle'],
                'type'     => $d['type'],
                'title'    => $d['title'],
                'due_date' => $d['dueDate'],
                'due_date2' => $d['dueDate'],
                'status'   => $d['status'] ?? 'scheduled',
                'owner_id' => $d['ownerId'] ?: null,
                'notes'    => $d['notes'] ?? null,
            ]
        );
    }

    public static function complete(int $id, string $notes = ''): void
    {
        cms_run(
            "UPDATE cms_schedule_events
             SET status = 'done', completed_at = NOW(), notes = COALESCE(NULLIF(?, ''), notes), updated_at = NOW()
             WHERE id = ?",
            [$notes, $id]
        );
    }

    public static function reschedule(int $id, string $newDate): void
    {
        cms_run(
            "UPDATE cms_schedule_events SET due_date = ?, status = 'rescheduled', updated_at = NOW() WHERE id = ?",
            [$newDate, $id]
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        cms_run('UPDATE cms_schedule_events SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
    }

    /**
     * Roll statuses forward against today's date: anything due within the next
     * 14 days becomes `upcoming`, anything past due becomes `missed`. Called on
     * every read of a case or the dashboard, so the demo always looks alive
     * without a cron job.
     */
    public static function refreshStatuses(?int $caseId = null): void
    {
        $where = $caseId ? ' AND case_id = ' . (int) $caseId : '';
        cms_run(
            "UPDATE cms_schedule_events
             SET status = 'upcoming', updated_at = NOW()
             WHERE status = 'scheduled' AND due_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
               AND due_date >= CURDATE()" . $where
        );
        cms_run(
            "UPDATE cms_schedule_events
             SET status = 'missed', updated_at = NOW()
             WHERE status IN ('scheduled','upcoming') AND due_date < CURDATE()" . $where
        );
    }

    public static function shape(array $e): array
    {
        $daysAway = cms_days_between(cms_today(), $e['due_date']);
        return [
            'id'              => (int) $e['id'],
            'caseId'          => (int) $e['case_id'],
            'cycle'           => (int) $e['cycle'],
            'type'            => $e['type'],
            'typeLabel'       => self::TYPE_LABELS[$e['type']] ?? $e['type'],
            'isConsult'       => self::isConsult($e['type']),
            'title'           => $e['title'],
            'dueDate'         => $e['due_date'],
            'originalDueDate' => $e['original_due_date'],
            'status'          => $e['status'],
            'ownerId'         => $e['owner_id'] ? (int) $e['owner_id'] : null,
            'notes'           => $e['notes'] ?? '',
            'completedAt'     => $e['completed_at'],
            'daysAway'        => $daysAway,
            'isOverdue'       => $daysAway < 0 && $e['status'] !== 'done',
        ];
    }
}

class AdherenceCheck
{
    public static function forEvent(int $eventId): ?array
    {
        $row = cms_one('SELECT * FROM cms_adherence_checks WHERE event_id = ? ORDER BY id DESC LIMIT 1', [$eventId]);
        return $row ? self::shape($row) : null;
    }

    public static function forCase(int $caseId): array
    {
        return array_map(
            [self::class, 'shape'],
            cms_all('SELECT * FROM cms_adherence_checks WHERE case_id = ? ORDER BY recorded_at DESC', [$caseId])
        );
    }

    public static function record(array $d): int
    {
        return cms_insert(
            'INSERT INTO cms_adherence_checks
                (event_id, case_id, cycle, medicine_compliance, refill_status, parent_concern_flag,
                 parent_note, recorded_by, recorded_at)
             VALUES (:event_id, :case_id, :cycle, :compliance, :refill, :flag, :note, :by, NOW())',
            [
                'event_id'   => $d['eventId'],
                'case_id'    => $d['caseId'],
                'cycle'      => $d['cycle'],
                'compliance' => in_array($d['compliance'] ?? '', ['full', 'partial', 'none'], true) ? $d['compliance'] : 'full',
                'refill'     => in_array($d['refill'] ?? '', ['stocked', 'due', 'requested', 'lapsed'], true) ? $d['refill'] : 'stocked',
                'flag'       => !empty($d['parentConcern']) ? 1 : 0,
                'note'       => $d['parentNote'] ?? '',
                'by'         => $d['recordedBy'] ?: null,
            ]
        );
    }

    /** Average compliance for a cycle as a percentage, used by the review + dashboard. */
    public static function cyclePercent(int $caseId, int $cycle): int
    {
        $rows = cms_all(
            'SELECT medicine_compliance FROM cms_adherence_checks WHERE case_id = ? AND cycle = ?',
            [$caseId, $cycle]
        );
        if (!$rows) {
            return 0;
        }
        $weights = ['full' => 100, 'partial' => 55, 'none' => 0];
        $sum = 0;
        foreach ($rows as $r) {
            $sum += $weights[$r['medicine_compliance']] ?? 0;
        }
        return (int) round($sum / count($rows));
    }

    public static function shape(array $a): array
    {
        return [
            'id'          => (int) $a['id'],
            'eventId'     => (int) $a['event_id'],
            'caseId'      => (int) $a['case_id'],
            'cycle'       => (int) $a['cycle'],
            'compliance'  => $a['medicine_compliance'],
            'refill'      => $a['refill_status'],
            'parentConcern' => (bool) $a['parent_concern_flag'],
            'parentNote'  => $a['parent_note'] ?? '',
            'recordedAt'  => $a['recorded_at'],
        ];
    }
}
