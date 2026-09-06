<?php
/**
 * Condition model — CRUD helpers for the `conditions` table.
 *
 * Backs the "Conditions we support" grid on the homepage. Unlike doctors, every
 * field here is plain scalar text, so there is no JSON packing to do.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class Condition
{
    /** Card sizes the homepage bento grid understands. */
    public const SPANS = ['featured', 'standard', 'compact'];

    /**
     * @return array<int,array>
     *
     * Reads tolerate the table not existing yet: on deploy the PHP files land
     * before the migration is run, and a fatal 500 from /api/conditions.php
     * during that window would take the homepage build/refresh down with it.
     * An empty list just hides the section until the table is created.
     */
    public static function all(bool $publishedOnly = false): array
    {
        $sql = 'SELECT * FROM conditions';
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published'";
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        try {
            return db()->query($sql)->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function find(int $id): ?array
    {
        try {
            $stmt = db()->prepare('SELECT * FROM conditions WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO conditions
                (name, description, image, alt, span, sort_order, status, created_at, updated_at)
             VALUES
                (:name, :description, :image, :alt, :span, :sort_order, :status, NOW(), NOW())'
        );
        $stmt->execute(self::bind($d));
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $params = self::bind($d);
        $params['id'] = $id;
        $stmt = db()->prepare(
            'UPDATE conditions SET
                name = :name, description = :description, image = :image, alt = :alt,
                span = :span, sort_order = :sort_order, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM conditions WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        try {
            return (int) db()->query('SELECT COUNT(*) FROM conditions')->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    private static function bind(array $d): array
    {
        $span = $d['span'] ?? 'standard';

        return [
            'name'        => $d['name'],
            'description' => $d['description'] ?? '',
            'image'       => $d['image'] ?? '',
            'alt'         => $d['alt'] ?? '',
            'span'        => in_array($span, self::SPANS, true) ? $span : 'standard',
            'sort_order'  => (int) ($d['sort_order'] ?? 0),
            'status'      => $d['status'] === 'draft' ? 'draft' : 'published',
        ];
    }
}
