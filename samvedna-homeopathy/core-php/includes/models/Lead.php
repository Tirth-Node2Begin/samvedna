<?php
/**
 * Lead model — the `inquiries` table.
 *
 * Both the public contact form (source = "website") and the auto-popup form
 * (source = "popup") write here through POST /api/leads.php. The site is a
 * static export, so PHP owns the write path; the read helpers tolerate the
 * table not existing yet (fresh install, before the first submission).
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class Lead
{
    /** Column list — `condition` is reserved in MySQL, hence condition_type. */
    private const COLS =
        'id, parent_name, child_age, condition_type, country, phone, email, message, preferred_time, source, created_at';

    private const CREATE_TABLE_SQL = <<<'SQL'
        CREATE TABLE IF NOT EXISTS inquiries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            parent_name VARCHAR(255) NOT NULL,
            child_age INT NOT NULL,
            condition_type VARCHAR(255) NOT NULL,
            country VARCHAR(255) NOT NULL,
            phone VARCHAR(32) NOT NULL,
            email VARCHAR(255) NOT NULL,
            message TEXT NULL,
            preferred_time VARCHAR(32) NOT NULL,
            source VARCHAR(32) NOT NULL DEFAULT 'website',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL;

    /** Create the table on first use so a fresh deploy needs no manual step. */
    public static function ensureSchema(): void
    {
        db()->exec(self::CREATE_TABLE_SQL);
    }

    /**
     * Insert one inquiry. Caller is responsible for validation.
     *
     * @param array<string,mixed> $data Keys match the column names below.
     * @return int New row id.
     */
    public static function create(array $data): int
    {
        self::ensureSchema();

        $stmt = db()->prepare(
            'INSERT INTO inquiries
                (parent_name, child_age, condition_type, country, phone, email, message, preferred_time, source)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['parent_name'],
            $data['child_age'],
            $data['condition_type'],
            $data['country'],
            $data['phone'],
            $data['email'],
            $data['message'] !== '' ? $data['message'] : null,
            $data['preferred_time'],
            $data['source'],
        ]);

        return (int) db()->lastInsertId();
    }

    /**
     * How many rows this IP has inserted in the last $seconds — crude flood
     * control for a public, unauthenticated endpoint.
     */
    public static function recentCountByPhoneOrEmail(string $phone, string $email, int $seconds): int
    {
        try {
            $stmt = db()->prepare(
                'SELECT COUNT(*) FROM inquiries
                 WHERE (phone = ? OR email = ?) AND created_at > (NOW() - INTERVAL ? SECOND)'
            );
            $stmt->execute([$phone, $email, $seconds]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * All leads, newest first. Optionally filter by source ("website"|"popup").
     *
     * @return array<int,array>
     */
    public static function all(?string $source = null): array
    {
        try {
            if ($source !== null && $source !== '') {
                $stmt = db()->prepare(
                    'SELECT ' . self::COLS . ' FROM inquiries WHERE source = ? ORDER BY created_at DESC, id DESC'
                );
                $stmt->execute([$source]);
                return $stmt->fetchAll();
            }

            return db()
                ->query('SELECT ' . self::COLS . ' FROM inquiries ORDER BY created_at DESC, id DESC')
                ->fetchAll();
        } catch (PDOException $e) {
            // Table not created yet (no submissions so far) — treat as empty.
            return [];
        }
    }

    public static function find(int $id): ?array
    {
        try {
            $stmt = db()->prepare('SELECT ' . self::COLS . ' FROM inquiries WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function delete(int $id): void
    {
        try {
            $stmt = db()->prepare('DELETE FROM inquiries WHERE id = ?');
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Ignore — nothing to delete if the table is absent.
        }
    }

    /**
     * Count leads, optionally by source.
     */
    public static function count(?string $source = null): int
    {
        try {
            if ($source !== null && $source !== '') {
                $stmt = db()->prepare('SELECT COUNT(*) FROM inquiries WHERE source = ?');
                $stmt->execute([$source]);
                return (int) $stmt->fetchColumn();
            }
            return (int) db()->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
