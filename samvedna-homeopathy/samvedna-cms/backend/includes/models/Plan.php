<?php
/**
 * Care plan catalogue. Plan rows are the single source of truth for follow-up
 * cadence — ScheduleGenerator reads the intervals from here, never from input.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class Plan
{
    public static function all(bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM cms_plans';
        if ($activeOnly) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= ' ORDER BY sort_order, price';
        return array_map([self::class, 'shape'], cms_all($sql));
    }

    public static function find(int $id): ?array
    {
        $row = cms_one('SELECT * FROM cms_plans WHERE id = ? LIMIT 1', [$id]);
        return $row ? self::shape($row) : null;
    }

    public static function findByCode(string $code): ?array
    {
        $row = cms_one('SELECT * FROM cms_plans WHERE code = ? LIMIT 1', [$code]);
        return $row ? self::shape($row) : null;
    }

    /** Raw DB row -> API shape (camelCase, decoded JSON, derived labels). */
    public static function shape(array $p): array
    {
        $months = (int) $p['duration_months'];
        return [
            'id'                     => (int) $p['id'],
            'code'                   => $p['code'],
            'name'                   => $p['name'],
            'tagline'                => $p['tagline'] ?? '',
            'price'                  => (int) $p['price'],
            'currency'               => $p['currency'],
            'priceLabel'             => ($p['currency'] === 'INR' ? '₹' : '$') . number_format((float) $p['price']),
            'durationMonths'         => $months,
            'durationLabel'          => $months . ' month' . ($months === 1 ? '' : 's'),
            'reviewCount'            => (int) $p['review_count'],
            'reviewIntervalDays'     => (int) $p['review_interval_days'],
            'adherenceIntervalDays'  => (int) $p['adherence_interval_days'],
            'rescheduleWindowDays'   => (int) $p['reschedule_window_days'],
            'requiresSenior'         => (bool) $p['requires_senior'],
            'requiresFounder'        => (bool) $p['requires_founder'],
            'founderIntervalDays'    => (int) $p['founder_interval_days'],
            'clinicalCover'          => $p['clinical_cover'] ?? '',
            'features'               => cms_json_col($p['features'] ?? ''),
            'highlight'              => (bool) $p['highlight'],
            'sortOrder'              => (int) $p['sort_order'],
            'status'                 => $p['status'],
            'cadenceLabel'           => sprintf(
                'Formal review every %d days · adherence check every %d days · reschedule ±%d days',
                (int) $p['review_interval_days'],
                (int) $p['adherence_interval_days'],
                (int) $p['reschedule_window_days']
            ),
        ];
    }

    public static function create(array $d): int
    {
        return cms_insert(
            'INSERT INTO cms_plans
                (code, name, tagline, price, currency, duration_months, review_count,
                 review_interval_days, adherence_interval_days, reschedule_window_days,
                 requires_senior, requires_founder, founder_interval_days, clinical_cover,
                 features, highlight, sort_order, status, created_at, updated_at)
             VALUES
                (:code, :name, :tagline, :price, :currency, :duration_months, :review_count,
                 :review_interval_days, :adherence_interval_days, :reschedule_window_days,
                 :requires_senior, :requires_founder, :founder_interval_days, :clinical_cover,
                 :features, :highlight, :sort_order, :status, NOW(), NOW())',
            self::bind($d)
        );
    }

    public static function update(int $id, array $d): void
    {
        $params = self::bind($d);
        $params['id'] = $id;
        cms_run(
            'UPDATE cms_plans SET
                code = :code, name = :name, tagline = :tagline, price = :price, currency = :currency,
                duration_months = :duration_months, review_count = :review_count,
                review_interval_days = :review_interval_days,
                adherence_interval_days = :adherence_interval_days,
                reschedule_window_days = :reschedule_window_days,
                requires_senior = :requires_senior, requires_founder = :requires_founder,
                founder_interval_days = :founder_interval_days, clinical_cover = :clinical_cover,
                features = :features, highlight = :highlight, sort_order = :sort_order,
                status = :status, updated_at = NOW()
             WHERE id = :id',
            $params
        );
    }

    public static function archive(int $id): void
    {
        // Plans are never hard-deleted: live cases point at them and the case
        // history has to stay readable.
        cms_run("UPDATE cms_plans SET status = 'archived', updated_at = NOW() WHERE id = ?", [$id]);
    }

    private static function bind(array $d): array
    {
        return [
            'code'                   => $d['code'],
            'name'                   => $d['name'],
            'tagline'                => $d['tagline'] ?? '',
            'price'                  => (int) ($d['price'] ?? 0),
            'currency'               => $d['currency'] ?? 'INR',
            'duration_months'        => max(1, (int) ($d['durationMonths'] ?? 6)),
            'review_count'           => max(1, (int) ($d['reviewCount'] ?? 3)),
            'review_interval_days'   => max(7, (int) ($d['reviewIntervalDays'] ?? 60)),
            'adherence_interval_days' => max(0, (int) ($d['adherenceIntervalDays'] ?? 15)),
            'reschedule_window_days' => max(0, (int) ($d['rescheduleWindowDays'] ?? 7)),
            'requires_senior'        => !empty($d['requiresSenior']) ? 1 : 0,
            'requires_founder'       => !empty($d['requiresFounder']) ? 1 : 0,
            'founder_interval_days'  => max(0, (int) ($d['founderIntervalDays'] ?? 0)),
            'clinical_cover'         => $d['clinicalCover'] ?? '',
            'features'               => cms_json_put($d['features'] ?? []),
            'highlight'              => !empty($d['highlight']) ? 1 : 0,
            'sort_order'             => (int) ($d['sortOrder'] ?? 0),
            'status'                 => $d['status'] ?? 'active',
        ];
    }
}
