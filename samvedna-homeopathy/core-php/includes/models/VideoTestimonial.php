<?php
/**
 * VideoTestimonial model — CRUD helpers for the `video_testimonials` table.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class VideoTestimonial
{
    /** @return array<int,array> */
    public static function all(bool $publishedOnly = false): array
    {
        $sql = 'SELECT * FROM video_testimonials';
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published'";
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        return db()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM video_testimonials WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO video_testimonials
                (name, condition_label, location, youtube_id, poster, alt, duration, sort_order, status, created_at, updated_at)
             VALUES
                (:name, :condition_label, :location, :youtube_id, :poster, :alt, :duration, :sort_order, :status, NOW(), NOW())'
        );
        $stmt->execute(self::bind($d));
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $params = self::bind($d);
        $params['id'] = $id;
        $stmt = db()->prepare(
            'UPDATE video_testimonials SET
                name = :name, condition_label = :condition_label, location = :location,
                youtube_id = :youtube_id, poster = :poster, alt = :alt, duration = :duration,
                sort_order = :sort_order, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM video_testimonials WHERE id = ?')->execute([$id]);
    }

    private static function bind(array $d): array
    {
        return [
            'name'            => $d['name'],
            'condition_label' => $d['condition_label'],
            'location'        => $d['location'] ?? '',
            'youtube_id'      => $d['youtube_id'] ?? '',
            'poster'          => $d['poster'] ?? '',
            'alt'             => $d['alt'] ?? '',
            'duration'        => $d['duration'] ?? '',
            'sort_order'      => (int) ($d['sort_order'] ?? 0),
            'status'          => $d['status'] ?? 'draft',
        ];
    }
}
