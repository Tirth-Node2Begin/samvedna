<?php
/**
 * Escalations and the Senior -> Founder approval chain.
 *
 * The 7-day resolution window from the flow is enforced here: due_date is
 * derived at creation and never accepted from input.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class Escalation
{
    public const SLA_DAYS = 7;

    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_escalations WHERE id = ? LIMIT 1', [$id]);
    }

    public static function forCase(int $caseId): array
    {
        return array_map(
            [self::class, 'shape'],
            cms_all('SELECT * FROM cms_escalations WHERE case_id = ? ORDER BY raised_at DESC', [$caseId])
        );
    }

    /** Cross-case escalation board. */
    public static function feed(array $filters = []): array
    {
        $sql = "SELECT e.*, c.code AS case_code, p.id AS patient_id, p.child_name, p.code AS patient_code,
                       u.name AS raised_by_name
                FROM cms_escalations e
                JOIN cms_cases c ON c.id = e.case_id
                JOIN cms_patients p ON p.id = c.patient_id
                LEFT JOIN cms_users u ON u.id = e.raised_by
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND e.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['openOnly'])) {
            $sql .= " AND e.status IN ('open','in_progress')";
        }
        if (!empty($filters['level'])) {
            $sql .= ' AND e.level = ?';
            $params[] = $filters['level'];
        }

        $sql .= " ORDER BY FIELD(e.status,'open','in_progress','resolved','closed'), e.due_date ASC";
        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
        }

        return array_map(static function (array $e): array {
            $shaped = self::shape($e);
            $shaped['caseCode']     = $e['case_code'];
            $shaped['patientId']    = (int) $e['patient_id'];
            $shaped['patientName']  = $e['child_name'];
            $shaped['patientCode']  = $e['patient_code'];
            $shaped['raisedByName'] = $e['raised_by_name'] ?? '';
            return $shaped;
        }, cms_all($sql, $params));
    }

    public static function create(array $d): int
    {
        $code = cms_next_code('cms_escalations', 'ESC');
        $id = cms_insert(
            'INSERT INTO cms_escalations
                (code, case_id, review_id, raised_by, reasons, notes, level, status,
                 raised_at, due_date, created_at, updated_at)
             VALUES (:code, :case_id, :review_id, :raised_by, :reasons, :notes, :level, :status,
                     NOW(), :due_date, NOW(), NOW())',
            [
                'code'      => $code,
                'case_id'   => $d['caseId'],
                'review_id' => $d['reviewId'] ?: null,
                'raised_by' => $d['raisedBy'] ?: null,
                'reasons'   => cms_json_put($d['reasons'] ?? []),
                'notes'     => $d['notes'] ?? '',
                'level'     => 'senior',
                'status'    => 'open',
                // Locked to the SLA from the flow — the client cannot extend it.
                'due_date'  => cms_add_days(cms_today(), self::SLA_DAYS),
            ]
        );

        EscalationStep::seed($id, $d['seniorDoctorId'] ?? null, $d['founderId'] ?? null);
        return $id;
    }

    /** Move the chain on to the founder. */
    public static function advance(int $id): void
    {
        cms_run(
            "UPDATE cms_escalations SET level = 'founder', status = 'in_progress', updated_at = NOW() WHERE id = ?",
            [$id]
        );
        EscalationStep::activate($id, 'founder');
    }

    public static function setStatus(int $id, string $status): void
    {
        cms_run('UPDATE cms_escalations SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
    }

    public static function resolve(int $id, string $notes, ?int $userId): void
    {
        cms_run(
            "UPDATE cms_escalations SET status = 'resolved', resolution_notes = ?, resolved_by = ?,
                    resolved_at = NOW(), updated_at = NOW()
             WHERE id = ?",
            [$notes, $userId ?: null, $id]
        );
        cms_run(
            "UPDATE cms_escalation_steps SET status = 'resolved', acted_at = NOW()
             WHERE escalation_id = ? AND status <> 'resolved'",
            [$id]
        );
    }

    public static function shape(array $e): array
    {
        $reasonKeys = cms_json_col($e['reasons'] ?? '');
        $catalogue  = [];
        foreach (cms_escalation_reasons() as $r) {
            $catalogue[$r['key']] = $r['label'];
        }
        $daysLeft = cms_days_between(cms_today(), $e['due_date']);
        $open = in_array($e['status'], ['open', 'in_progress'], true);

        return [
            'id'              => (int) $e['id'],
            'code'            => $e['code'],
            'caseId'          => (int) $e['case_id'],
            'reviewId'        => $e['review_id'] ? (int) $e['review_id'] : null,
            'raisedBy'        => $e['raised_by'] ? (int) $e['raised_by'] : null,
            'reasons'         => $reasonKeys,
            'reasonLabels'    => array_values(array_map(
                static fn($k) => $catalogue[$k] ?? $k,
                $reasonKeys
            )),
            'notes'           => $e['notes'] ?? '',
            'level'           => $e['level'],
            'status'          => $e['status'],
            'raisedAt'        => $e['raised_at'],
            'dueDate'         => $e['due_date'],
            'slaDays'         => self::SLA_DAYS,
            'daysLeft'        => $daysLeft,
            'isOverdue'       => $open && $daysLeft < 0,
            'resolutionNotes' => $e['resolution_notes'] ?? '',
            'resolvedAt'      => $e['resolved_at'],
        ];
    }
}

class EscalationStep
{
    /** Build the two-step chain: Senior reviews first, Founder waits on standby. */
    public static function seed(int $escalationId, ?int $seniorId, ?int $founderId): void
    {
        cms_run(
            'INSERT INTO cms_escalation_steps (escalation_id, user_id, role, status, sort_order)
             VALUES (?, ?, ?, ?, ?)',
            [$escalationId, $seniorId ?: null, 'senior_doctor', 'reviewing', 0]
        );
        cms_run(
            'INSERT INTO cms_escalation_steps (escalation_id, user_id, role, status, sort_order)
             VALUES (?, ?, ?, ?, ?)',
            [$escalationId, $founderId ?: null, 'founder', 'standby', 1]
        );
    }

    public static function activate(int $escalationId, string $role): void
    {
        cms_run(
            "UPDATE cms_escalation_steps SET status = 'reviewing', acted_at = NOW()
             WHERE escalation_id = ? AND role = ?",
            [$escalationId, $role === 'founder' ? 'founder' : 'senior_doctor']
        );
        if ($role === 'founder') {
            cms_run(
                "UPDATE cms_escalation_steps SET status = 'approved', acted_at = NOW()
                 WHERE escalation_id = ? AND role = 'senior_doctor' AND status = 'reviewing'",
                [$escalationId]
            );
        }
    }

    public static function forEscalation(int $escalationId): array
    {
        $rows = cms_all(
            'SELECT s.*, u.name AS user_name FROM cms_escalation_steps s
             LEFT JOIN cms_users u ON u.id = s.user_id
             WHERE s.escalation_id = ? ORDER BY s.sort_order',
            [$escalationId]
        );
        return array_map(static fn(array $s): array => [
            'id'        => (int) $s['id'],
            'role'      => $s['role'],
            'roleLabel' => cms_role_label($s['role']),
            'userName'  => $s['user_name'] ?? 'Unassigned',
            'status'    => $s['status'],
            'note'      => $s['note'] ?? '',
            'actedAt'   => $s['acted_at'],
        ], $rows);
    }
}
