<?php
/**
 * Doctor model — CRUD helpers for the `doctors` table.
 *
 * List-type profile fields (qualifications, specializations, treatments,
 * certifications, awards, languages) are stored as JSON text columns.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class Doctor
{
    /** @return array<int,array> */
    public static function all(bool $publishedOnly = false): array
    {
        $sql = 'SELECT * FROM doctors';
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published'";
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        return db()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM doctors WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO doctors
                (name, title, credential, image, alt, specialization, experience, summary, about,
                 qualifications, specializations, treatments, certifications, awards, languages,
                 consultation, sort_order, status, created_at, updated_at)
             VALUES
                (:name, :title, :credential, :image, :alt, :specialization, :experience, :summary, :about,
                 :qualifications, :specializations, :treatments, :certifications, :awards, :languages,
                 :consultation, :sort_order, :status, NOW(), NOW())'
        );
        $stmt->execute(self::bind($d));
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $params = self::bind($d);
        $params['id'] = $id;
        $stmt = db()->prepare(
            'UPDATE doctors SET
                name = :name, title = :title, credential = :credential, image = :image, alt = :alt,
                specialization = :specialization, experience = :experience, summary = :summary, about = :about,
                qualifications = :qualifications, specializations = :specializations, treatments = :treatments,
                certifications = :certifications, awards = :awards, languages = :languages,
                consultation = :consultation, sort_order = :sort_order, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM doctors WHERE id = ?')->execute([$id]);
    }

    private static function bind(array $d): array
    {
        $asJson = static function ($v): string {
            $arr = is_array($v) ? array_values($v) : [];
            return json_encode($arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        };

        return [
            'name'            => $d['name'],
            'title'           => $d['title'] ?? '',
            'credential'      => $d['credential'] ?? '',
            'image'           => $d['image'] ?? '',
            'alt'             => $d['alt'] ?? '',
            'specialization'  => $d['specialization'] ?? '',
            'experience'      => $d['experience'] ?? '',
            'summary'         => $d['summary'] ?? '',
            'about'           => $d['about'] ?? '',
            'qualifications'  => $asJson($d['qualifications'] ?? []),
            'specializations' => $asJson($d['specializations'] ?? []),
            'treatments'      => $asJson($d['treatments'] ?? []),
            'certifications'  => $asJson($d['certifications'] ?? []),
            'awards'          => $asJson($d['awards'] ?? []),
            'languages'       => $asJson($d['languages'] ?? []),
            'consultation'    => $d['consultation'] ?? '',
            'sort_order'      => (int) ($d['sort_order'] ?? 0),
            'status'          => $d['status'] ?? 'draft',
        ];
    }
}
