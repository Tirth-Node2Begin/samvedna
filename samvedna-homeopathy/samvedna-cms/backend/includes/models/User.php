<?php
/**
 * Staff and doctors.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class User
{
    public static function all(?string $role = null): array
    {
        $sql = 'SELECT * FROM cms_users WHERE status = ?';
        $params = ['active'];
        if ($role) {
            $sql .= ' AND role = ?';
            $params[] = $role;
        }
        $sql .= " ORDER BY FIELD(role,'founder','senior_doctor','case_doctor','coordinator'), name";
        return cms_all($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_users WHERE id = ? LIMIT 1', [$id]);
    }

    public static function findByUsername(string $username): ?array
    {
        return cms_one('SELECT * FROM cms_users WHERE username = ? LIMIT 1', [$username]);
    }

    /** Public shape + caseload counts, for the team screen. */
    public static function withCaseload(): array
    {
        $rows = self::all();
        $ids = array_map(static fn(array $u): int => (int) $u['id'], $rows);

        if (!$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $caseloads = self::keyedCounts(cms_all(
            "SELECT u.id, COUNT(c.id) AS n
             FROM cms_users u
             LEFT JOIN cms_cases c
               ON c.status = 'active'
              AND (c.case_doctor_id = u.id OR c.senior_doctor_id = u.id OR c.founder_id = u.id)
             WHERE u.id IN ($placeholders)
             GROUP BY u.id",
            $ids
        ));

        $openEscalations = self::keyedCounts(cms_all(
            "SELECT u.id, COUNT(e.id) AS n
             FROM cms_users u
             LEFT JOIN cms_cases c
               ON c.case_doctor_id = u.id OR c.senior_doctor_id = u.id
             LEFT JOIN cms_escalations e
               ON e.case_id = c.id AND e.status IN ('open','in_progress')
             WHERE u.id IN ($placeholders)
             GROUP BY u.id",
            $ids
        ));

        $pendingSignoffs = self::keyedCounts(cms_all(
            "SELECT user_id AS id, COUNT(*) AS n
             FROM cms_review_signoffs
             WHERE status = 'pending' AND user_id IN ($placeholders)
             GROUP BY user_id",
            $ids
        ));

        $scorecards = [];
        foreach (cms_all(
            "SELECT created_by AS id,
                    COUNT(*) AS total,
                    COALESCE(AVG(completeness), 0) AS completeness,
                    COALESCE(AVG(closed_on_time), 0) AS on_time,
                    COALESCE(AVG(parent_satisfaction), 0) AS satisfaction
             FROM cms_reviews
             WHERE created_by IN ($placeholders) AND status = 'closed'
             GROUP BY created_by",
            $ids
        ) as $row) {
            $scorecards[(int) $row['id']] = [
                'reviewsClosed' => (int) $row['total'],
                'completeness'  => (int) round((float) $row['completeness']),
                'onTimeRate'    => (int) round(((float) $row['on_time']) * 100),
                'satisfaction'  => round((float) $row['satisfaction'], 1),
            ];
        }

        return array_map(static function (array $u) use ($caseloads, $openEscalations, $pendingSignoffs, $scorecards): array {
            $pub = cms_public_user($u);
            $id = (int) $u['id'];
            $pub['caseload'] = $caseloads[$id] ?? 0;
            $pub['openEscalations'] = $openEscalations[$id] ?? 0;
            $pub['pendingSignoffs'] = $pendingSignoffs[$id] ?? 0;
            $pub['scorecard'] = $scorecards[$id] ?? [
                'reviewsClosed' => 0,
                'completeness'  => 0,
                'onTimeRate'    => 0,
                'satisfaction'  => 0.0,
            ];
            return $pub;
        }, $rows);
    }

    private static function keyedCounts(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['id']] = (int) $row['n'];
        }
        return $out;
    }

    /**
     * Doctor scorecard: averaged CMS completeness, on-time closure rate and
     * parent satisfaction across every review the doctor authored.
     */
    public static function scorecard(int $userId): array
    {
        $row = cms_one(
            "SELECT COUNT(*) AS total,
                    COALESCE(AVG(completeness), 0) AS completeness,
                    COALESCE(AVG(closed_on_time), 0) AS on_time,
                    COALESCE(AVG(parent_satisfaction), 0) AS satisfaction
             FROM cms_reviews
             WHERE created_by = ? AND status = 'closed'",
            [$userId]
        ) ?: [];

        return [
            'reviewsClosed' => (int) ($row['total'] ?? 0),
            'completeness'  => (int) round((float) ($row['completeness'] ?? 0)),
            'onTimeRate'    => (int) round(((float) ($row['on_time'] ?? 0)) * 100),
            'satisfaction'  => round((float) ($row['satisfaction'] ?? 0), 1),
        ];
    }

    public static function create(array $d): int
    {
        return cms_insert(
            'INSERT INTO cms_users (username, name, email, phone, title, password_hash, role, status, created_at, updated_at)
             VALUES (:username, :name, :email, :phone, :title, :hash, :role, :status, NOW(), NOW())',
            [
                'username' => $d['username'],
                'name'     => $d['name'],
                'email'    => $d['email'] ?? null,
                'phone'    => $d['phone'] ?? null,
                'title'    => $d['title'] ?? null,
                'hash'     => password_hash($d['password'], PASSWORD_DEFAULT),
                'role'     => $d['role'] ?? 'case_doctor',
                'status'   => $d['status'] ?? 'active',
            ]
        );
    }

    public static function update(int $id, array $d): void
    {
        cms_run(
            'UPDATE cms_users SET name = :name, email = :email, phone = :phone,
                    title = :title, role = :role, status = :status, updated_at = NOW()
             WHERE id = :id',
            [
                'name'   => $d['name'],
                'email'  => $d['email'] ?? null,
                'phone'  => $d['phone'] ?? null,
                'title'  => $d['title'] ?? null,
                'role'   => $d['role'] ?? 'case_doctor',
                'status' => $d['status'] ?? 'active',
                'id'     => $id,
            ]
        );
    }
}
