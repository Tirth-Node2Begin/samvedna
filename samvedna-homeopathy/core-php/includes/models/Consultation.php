<?php
/**
 * Consultation model — the `consultations` table.
 *
 * Backs the multi-step care-plan assessment wizard (components/consultation/*).
 * A visitor picks a plan, fills the clinical intake step by step, and submits
 * through POST /api/consultations.php. The general-information fields are stored
 * as real columns so the admin list stays readable; every clinical answer is
 * kept in the `answers` JSON blob. Read helpers tolerate the table not existing
 * yet (fresh install, before the first submission).
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class Consultation
{
    private const COLS =
        'id, plan, plan_name, amount, patient_name, father_name, mobile, alt_phone, address, city, state, zip, email, remarks, child_age, answers, source, status, created_at';

    private const CREATE_TABLE_SQL = <<<'SQL'
        CREATE TABLE IF NOT EXISTS consultations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plan VARCHAR(64) NOT NULL DEFAULT '',
            plan_name VARCHAR(128) NULL,
            amount VARCHAR(32) NULL,
            patient_name VARCHAR(255) NOT NULL,
            father_name VARCHAR(255) NULL,
            mobile VARCHAR(32) NOT NULL,
            alt_phone VARCHAR(32) NULL,
            address TEXT NULL,
            city VARCHAR(128) NULL,
            state VARCHAR(128) NULL,
            zip VARCHAR(32) NULL,
            email VARCHAR(255) NULL,
            remarks TEXT NULL,
            child_age VARCHAR(32) NULL,
            answers LONGTEXT NULL,
            source VARCHAR(32) NOT NULL DEFAULT 'website',
            status VARCHAR(32) NOT NULL DEFAULT 'new',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

    /** Create the table on first use so a fresh deploy needs no manual step. */
    public static function ensureSchema(): void
    {
        db()->exec(self::CREATE_TABLE_SQL);
    }

    /**
     * Insert one consultation. Caller is responsible for validation.
     *
     * The `answers` key may be an array (encoded here) or a pre-encoded JSON
     * string. Empty optional strings are stored as NULL.
     *
     * @param array<string,mixed> $data Keys match the column names below.
     * @return int New row id.
     */
    public static function create(array $data): int
    {
        self::ensureSchema();

        $answers = $data['answers'] ?? [];
        if (is_array($answers)) {
            $answers = json_encode($answers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $nullable = static fn($v) => (is_string($v) && trim($v) !== '') ? $v : ($v === 0 || $v === '0' ? $v : null);

        $stmt = db()->prepare(
            'INSERT INTO consultations
                (plan, plan_name, amount, patient_name, father_name, mobile, alt_phone,
                 address, city, state, zip, email, remarks, child_age, answers, source, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (string) ($data['plan'] ?? ''),
            $nullable($data['plan_name'] ?? ''),
            $nullable($data['amount'] ?? ''),
            (string) ($data['patient_name'] ?? ''),
            $nullable($data['father_name'] ?? ''),
            (string) ($data['mobile'] ?? ''),
            $nullable($data['alt_phone'] ?? ''),
            $nullable($data['address'] ?? ''),
            $nullable($data['city'] ?? ''),
            $nullable($data['state'] ?? ''),
            $nullable($data['zip'] ?? ''),
            $nullable($data['email'] ?? ''),
            $nullable($data['remarks'] ?? ''),
            $nullable($data['child_age'] ?? ''),
            $answers !== '' ? $answers : null,
            (string) ($data['source'] ?? 'website'),
            (string) ($data['status'] ?? 'new'),
        ]);

        return (int) db()->lastInsertId();
    }

    /**
     * How many rows this mobile/email submitted in the last $seconds — crude
     * flood control for a public, unauthenticated endpoint.
     */
    public static function recentCountByMobileOrEmail(string $mobile, string $email, int $seconds): int
    {
        try {
            $stmt = db()->prepare(
                'SELECT COUNT(*) FROM consultations
                 WHERE (mobile = ? OR (email <> \'\' AND email = ?)) AND created_at > (NOW() - INTERVAL ? SECOND)'
            );
            $stmt->execute([$mobile, $email, $seconds]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * All consultations, newest first.
     *
     * @return array<int,array>
     */
    public static function all(): array
    {
        try {
            return db()
                ->query('SELECT ' . self::COLS . ' FROM consultations ORDER BY created_at DESC, id DESC')
                ->fetchAll();
        } catch (PDOException $e) {
            // Table not created yet (no submissions so far) — treat as empty.
            return [];
        }
    }

    public static function find(int $id): ?array
    {
        try {
            $stmt = db()->prepare('SELECT ' . self::COLS . ' FROM consultations WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function delete(int $id): void
    {
        try {
            $stmt = db()->prepare('DELETE FROM consultations WHERE id = ?');
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Ignore — nothing to delete if the table is absent.
        }
    }

    public static function count(): int
    {
        try {
            return (int) db()->query('SELECT COUNT(*) FROM consultations')->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
