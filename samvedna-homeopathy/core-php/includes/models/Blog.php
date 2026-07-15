<?php
/**
 * Blog model — CRUD helpers for the `blogs` table.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class Blog
{
    /** @return array<int,array> */
    public static function all(bool $publishedOnly = false): array
    {
        $sql = 'SELECT * FROM blogs';
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published'";
        }
        $sql .= ' ORDER BY published_at DESC, id DESC';
        return db()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM blogs WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Ensure the slug is unique, appending -2, -3 … if needed. */
    public static function uniqueSlug(string $slug, int $ignoreId = 0): string
    {
        $base = $slug;
        $i = 1;
        while (true) {
            $stmt = db()->prepare('SELECT id FROM blogs WHERE slug = ? AND id <> ? LIMIT 1');
            $stmt->execute([$slug, $ignoreId]);
            if (!$stmt->fetch()) {
                return $slug;
            }
            $i++;
            $slug = $base . '-' . $i;
        }
    }

    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO blogs
                (slug, title, excerpt, content, category, author, image, alt, read_time, status, published_at, created_at, updated_at)
             VALUES
                (:slug, :title, :excerpt, :content, :category, :author, :image, :alt, :read_time, :status, :published_at, NOW(), NOW())'
        );
        $stmt->execute(self::bind($d));
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $params = self::bind($d);
        $params['id'] = $id;
        $stmt = db()->prepare(
            'UPDATE blogs SET
                slug = :slug, title = :title, excerpt = :excerpt, content = :content,
                category = :category, author = :author, image = :image, alt = :alt,
                read_time = :read_time, status = :status, published_at = :published_at,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM blogs WHERE id = ?')->execute([$id]);
    }

    private static function bind(array $d): array
    {
        return [
            'slug'         => $d['slug'],
            'title'        => $d['title'],
            'excerpt'      => $d['excerpt'],
            'content'      => $d['content'] ?? '',
            'category'     => $d['category'],
            'author'       => $d['author'] ?? '',
            'image'        => $d['image'] ?? '',
            'alt'          => $d['alt'] ?? '',
            'read_time'    => $d['read_time'] ?? '',
            'status'       => $d['status'] ?? 'draft',
            'published_at' => $d['published_at'] ?: null,
        ];
    }
}
