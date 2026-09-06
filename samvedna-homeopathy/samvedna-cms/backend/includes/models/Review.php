<?php
/**
 * The forced bi-monthly review template, its sign-offs and its tracked goals.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class Review
{
    /** Fields the flow marks mandatory. Order = the order they appear on screen. */
    public const MANDATORY = [
        'improvements'      => 'Improvements observed in the last cycle is required.',
        'stagnation'        => 'Areas of stagnation / concern is required.',
        'protocolDecision'  => 'A protocol decision is required.',
        'goals'             => 'At least one goal for the next cycle is required.',
        'therapyNotes'      => 'Therapy coordination notes are required.',
    ];

    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_reviews WHERE id = ? LIMIT 1', [$id]);
    }

    public static function forCase(int $caseId): array
    {
        return cms_all('SELECT * FROM cms_reviews WHERE case_id = ? ORDER BY cycle DESC, id DESC', [$caseId]);
    }

    public static function forCaseCycle(int $caseId, int $cycle): ?array
    {
        return cms_one('SELECT * FROM cms_reviews WHERE case_id = ? AND cycle = ? LIMIT 1', [$caseId, $cycle]);
    }

    /** Cross-case review queue. */
    public static function feed(array $filters = []): array
    {
        $sql = "SELECT r.*, c.code AS case_code, c.total_cycles, p.id AS patient_id, p.child_name,
                       p.code AS patient_code, pl.name AS plan_name, pl.requires_senior,
                       u.name AS author_name
                FROM cms_reviews r
                JOIN cms_cases c ON c.id = r.case_id
                JOIN cms_patients p ON p.id = c.patient_id
                JOIN cms_plans pl ON pl.id = c.plan_id
                LEFT JOIN cms_users u ON u.id = r.created_by
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND r.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['caseId'])) {
            $sql .= ' AND r.case_id = ?';
            $params[] = (int) $filters['caseId'];
        }
        if (!empty($filters['awaitingUserId'])) {
            $sql .= ' AND EXISTS (SELECT 1 FROM cms_review_signoffs s
                                  WHERE s.review_id = r.id AND s.user_id = ? AND s.status = \'pending\')';
            $params[] = (int) $filters['awaitingUserId'];
        }

        $sql .= ' ORDER BY FIELD(r.status, \'awaiting_signoff\', \'draft\', \'closed\'), r.review_date DESC, r.id DESC';
        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
        }

        return array_map(static function (array $r): array {
            $shaped = self::shape($r);
            $shaped['caseCode']    = $r['case_code'];
            $shaped['totalCycles'] = (int) $r['total_cycles'];
            $shaped['patientId']   = (int) $r['patient_id'];
            $shaped['patientName'] = $r['child_name'];
            $shaped['patientCode'] = $r['patient_code'];
            $shaped['planName']    = $r['plan_name'];
            $shaped['requiresSenior'] = (bool) $r['requires_senior'];
            $shaped['authorName']  = $r['author_name'] ?? '';
            return $shaped;
        }, cms_all($sql, $params));
    }

    public static function create(array $d): int
    {
        return cms_insert(
            'INSERT INTO cms_reviews
                (case_id, event_id, cycle, review_date, next_review_date, status, created_by, created_at, updated_at)
             VALUES (:case_id, :event_id, :cycle, :review_date, :next_review_date, :status, :created_by, NOW(), NOW())',
            [
                'case_id'          => $d['caseId'],
                'event_id'         => $d['eventId'] ?: null,
                'cycle'            => $d['cycle'],
                'review_date'      => $d['reviewDate'],
                'next_review_date' => $d['nextReviewDate'] ?: null,
                'status'           => $d['status'] ?? 'draft',
                'created_by'       => $d['createdBy'] ?: null,
            ]
        );
    }

    /** Save the template body. Does not change the review status. */
    public static function saveBody(int $id, array $d): void
    {
        cms_run(
            'UPDATE cms_reviews SET
                improvements = :improvements, stagnation = :stagnation,
                protocol_decision = :protocol_decision, protocol_rationale = :protocol_rationale,
                therapy_notes = :therapy_notes, adherence_percent = :adherence_percent,
                escalate = :escalate, escalate_note = :escalate_note,
                completeness = :completeness, updated_at = NOW()
             WHERE id = :id',
            [
                'improvements'       => $d['improvements'] ?? '',
                'stagnation'         => $d['stagnation'] ?? '',
                'protocol_decision'  => in_array($d['protocolDecision'] ?? '', ['continued', 'continued_adjusted', 'changed', 'paused'], true)
                    ? $d['protocolDecision'] : '',
                'protocol_rationale' => $d['protocolRationale'] ?? '',
                'therapy_notes'      => $d['therapyNotes'] ?? '',
                'adherence_percent'  => max(0, min(100, (int) ($d['adherencePercent'] ?? 0))),
                'escalate'           => !empty($d['escalate']) ? 1 : 0,
                'escalate_note'      => $d['escalateNote'] ?? '',
                'completeness'       => (int) ($d['completeness'] ?? 0),
                'id'                 => $id,
            ]
        );
    }

    /**
     * Validate the forced template. Mirrors the zod schema on the client so the
     * two can never disagree about what "complete" means.
     *
     * @return array{fields: array<string,string>, completeness: int}
     */
    public static function validate(array $d, array $goals): array
    {
        $fields = [];

        if (trim((string) ($d['improvements'] ?? '')) === '') {
            $fields['improvements'] = self::MANDATORY['improvements'];
        }
        if (trim((string) ($d['stagnation'] ?? '')) === '') {
            $fields['stagnation'] = self::MANDATORY['stagnation'];
        }

        $decision = (string) ($d['protocolDecision'] ?? '');
        if ($decision === '') {
            $fields['protocolDecision'] = self::MANDATORY['protocolDecision'];
        } elseif ($decision !== 'continued' && trim((string) ($d['protocolRationale'] ?? '')) === '') {
            // The flow's hard rule: any deviation from "continued as-is" must be
            // justified in writing before the review can close.
            $fields['protocolRationale'] = 'Rationale is mandatory when the protocol is adjusted, changed or paused.';
        }

        $realGoals = array_values(array_filter($goals, static fn($g) => trim((string) ($g['title'] ?? '')) !== ''));
        if (count($realGoals) === 0) {
            $fields['goals'] = self::MANDATORY['goals'];
        }
        if (trim((string) ($d['therapyNotes'] ?? '')) === '') {
            $fields['therapyNotes'] = self::MANDATORY['therapyNotes'];
        }

        // Completeness is scored per slot, not per message: a missing rationale
        // fails the same "protocol" slot as a missing decision, so the score stays
        // a clean n/5 however the protocol half was broken.
        $slots = [
            'improvements' => isset($fields['improvements']),
            'stagnation'   => isset($fields['stagnation']),
            'protocol'     => isset($fields['protocolDecision']) || isset($fields['protocolRationale']),
            'goals'        => isset($fields['goals']),
            'therapyNotes' => isset($fields['therapyNotes']),
        ];
        $failed = count(array_filter($slots));
        $completeness = (int) round(((count($slots) - $failed) / count($slots)) * 100);

        return ['fields' => $fields, 'completeness' => $completeness];
    }

    public static function setStatus(int $id, string $status): void
    {
        cms_run('UPDATE cms_reviews SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
    }

    public static function close(int $id, bool $onTime, float $satisfaction): void
    {
        cms_run(
            "UPDATE cms_reviews SET status = 'closed', closed_at = NOW(), closed_on_time = ?,
                    parent_satisfaction = ?, updated_at = NOW()
             WHERE id = ?",
            [$onTime ? 1 : 0, $satisfaction, $id]
        );
    }

    public static function shape(array $r): array
    {
        return [
            'id'                 => (int) $r['id'],
            'caseId'             => (int) $r['case_id'],
            'eventId'            => $r['event_id'] ? (int) $r['event_id'] : null,
            'cycle'              => (int) $r['cycle'],
            'reviewDate'         => $r['review_date'],
            'improvements'       => $r['improvements'] ?? '',
            'stagnation'         => $r['stagnation'] ?? '',
            'protocolDecision'   => $r['protocol_decision'] ?? '',
            'protocolRationale'  => $r['protocol_rationale'] ?? '',
            'therapyNotes'       => $r['therapy_notes'] ?? '',
            'adherencePercent'   => (int) $r['adherence_percent'],
            'escalate'           => (bool) $r['escalate'],
            'escalateNote'       => $r['escalate_note'] ?? '',
            'nextReviewDate'     => $r['next_review_date'],
            'status'             => $r['status'],
            'completeness'       => (int) $r['completeness'],
            'closedOnTime'       => $r['closed_on_time'] === null ? null : (bool) $r['closed_on_time'],
            'parentSatisfaction' => $r['parent_satisfaction'] !== null ? (float) $r['parent_satisfaction'] : null,
            'closedAt'           => $r['closed_at'],
            'createdBy'          => $r['created_by'] ? (int) $r['created_by'] : null,
            'createdAt'          => $r['created_at'],
        ];
    }
}

class ReviewSignoff
{
    /** Seed the sign-off rows a plan demands for a review. */
    public static function seed(int $reviewId, array $case, array $plan): void
    {
        $rows = [['case_doctor', $case['case_doctor_id']]];
        if (!empty($plan['requiresSenior'])) {
            $rows[] = ['senior_doctor', $case['senior_doctor_id']];
        }
        if (!empty($plan['requiresFounder'])) {
            $rows[] = ['founder', $case['founder_id']];
        }

        foreach ($rows as [$role, $userId]) {
            cms_run(
                'INSERT INTO cms_review_signoffs (review_id, user_id, role, status)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)',
                [$reviewId, $userId ?: null, $role, 'pending']
            );
        }
    }

    public static function forReview(int $reviewId): array
    {
        $rows = cms_all(
            'SELECT s.*, u.name AS user_name FROM cms_review_signoffs s
             LEFT JOIN cms_users u ON u.id = s.user_id
             WHERE s.review_id = ?
             ORDER BY FIELD(s.role, \'case_doctor\', \'senior_doctor\', \'founder\')',
            [$reviewId]
        );
        return array_map([self::class, 'shape'], $rows);
    }

    public static function forReviews(array $reviewIds): array
    {
        $reviewIds = array_values(array_unique(array_map('intval', $reviewIds)));
        if (!$reviewIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));
        $rows = cms_all(
            "SELECT s.*, u.name AS user_name FROM cms_review_signoffs s
             LEFT JOIN cms_users u ON u.id = s.user_id
             WHERE s.review_id IN ($placeholders)
             ORDER BY s.review_id, FIELD(s.role, 'case_doctor', 'senior_doctor', 'founder')",
            $reviewIds
        );

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['review_id']][] = self::shape($row);
        }
        return $out;
    }

    private static function shape(array $s): array
    {
        return [
            'id'        => (int) $s['id'],
            'role'      => $s['role'],
            'roleLabel' => cms_role_label($s['role']),
            'userId'    => $s['user_id'] ? (int) $s['user_id'] : null,
            'userName'  => $s['user_name'] ?? 'Unassigned',
            'status'    => $s['status'],
            'comment'   => $s['comment'] ?? '',
            'signedAt'  => $s['signed_at'],
        ];
    }

    public static function sign(int $reviewId, int $userId, string $comment = ''): bool
    {
        $affected = cms_run(
            "UPDATE cms_review_signoffs SET status = 'signed', comment = ?, signed_at = NOW()
             WHERE review_id = ? AND user_id = ? AND status = 'pending'",
            [$comment, $reviewId, $userId]
        );
        return $affected > 0;
    }

    public static function allSigned(int $reviewId): bool
    {
        return (int) cms_scalar(
            "SELECT COUNT(*) FROM cms_review_signoffs WHERE review_id = ? AND status <> 'signed'",
            [$reviewId]
        ) === 0;
    }
}

class ReviewGoal
{
    public static function forReview(int $reviewId): array
    {
        return array_map(
            [self::class, 'shape'],
            cms_all('SELECT * FROM cms_review_goals WHERE review_id = ? ORDER BY sort_order, id', [$reviewId])
        );
    }

    public static function forReviews(array $reviewIds): array
    {
        $reviewIds = array_values(array_unique(array_map('intval', $reviewIds)));
        if (!$reviewIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));
        $rows = cms_all(
            "SELECT * FROM cms_review_goals
             WHERE review_id IN ($placeholders)
             ORDER BY review_id, sort_order, id",
            $reviewIds
        );

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['review_id']][] = self::shape($row);
        }
        return $out;
    }

    public static function forCycle(int $caseId, int $cycle): array
    {
        return array_map(
            [self::class, 'shape'],
            cms_all('SELECT * FROM cms_review_goals WHERE case_id = ? AND cycle = ? ORDER BY sort_order, id', [$caseId, $cycle])
        );
    }

    /** Replace the goal set for a review (the form posts the whole list). */
    public static function replace(int $reviewId, int $caseId, int $cycle, array $goals): void
    {
        cms_run('DELETE FROM cms_review_goals WHERE review_id = ?', [$reviewId]);
        $order = 0;
        foreach ($goals as $g) {
            $title = trim((string) ($g['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            cms_run(
                'INSERT INTO cms_review_goals (case_id, review_id, cycle, title, metric, status, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    $caseId,
                    $reviewId,
                    $cycle,
                    mb_substr($title, 0, 255),
                    mb_substr(trim((string) ($g['metric'] ?? '')), 0, 191),
                    in_array($g['status'] ?? '', ['pending', 'progressing', 'achieved', 'missed'], true) ? $g['status'] : 'pending',
                    $order++,
                ]
            );
        }
    }

    public static function setStatus(int $id, string $status): void
    {
        cms_run('UPDATE cms_review_goals SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function shape(array $g): array
    {
        return [
            'id'     => (int) $g['id'],
            'caseId' => (int) $g['case_id'],
            'cycle'  => (int) $g['cycle'],
            'title'  => $g['title'],
            'metric' => $g['metric'] ?? '',
            'status' => $g['status'],
        ];
    }
}
