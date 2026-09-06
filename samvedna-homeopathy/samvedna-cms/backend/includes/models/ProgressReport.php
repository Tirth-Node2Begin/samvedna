<?php
/**
 * The auto-generated per-cycle progress report (step 6 of the flow).
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class ProgressReport
{
    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_progress_reports WHERE id = ? LIMIT 1', [$id]);
    }

    public static function forCycle(int $caseId, int $cycle): ?array
    {
        return cms_one('SELECT * FROM cms_progress_reports WHERE case_id = ? AND cycle = ? LIMIT 1', [$caseId, $cycle]);
    }

    public static function forCase(int $caseId): array
    {
        return array_map(
            [self::class, 'shape'],
            cms_all('SELECT * FROM cms_progress_reports WHERE case_id = ? ORDER BY cycle DESC', [$caseId])
        );
    }

    /** Insert or replace the report for a cycle. */
    public static function upsert(array $d): int
    {
        $existing = self::forCycle((int) $d['caseId'], (int) $d['cycle']);
        $params = [
            'case_id'           => $d['caseId'],
            'review_id'         => $d['reviewId'] ?: null,
            'cycle'             => $d['cycle'],
            'period_start'      => $d['periodStart'],
            'period_end'        => $d['periodEnd'],
            'overall_trend'     => $d['overallTrend'],
            'goals_total'       => $d['goalsTotal'],
            'goals_progressing' => $d['goalsProgressing'],
            'adherence_percent' => $d['adherencePercent'],
            'areas'             => cms_json_put($d['areas'] ?? []),
            'objectives'        => cms_json_put($d['objectives'] ?? []),
            'scorecard'         => cms_json_obj($d['scorecard'] ?? []),
        ];

        if ($existing) {
            $params['id'] = (int) $existing['id'];
            cms_run(
                'UPDATE cms_progress_reports SET
                    review_id = :review_id, period_start = :period_start, period_end = :period_end,
                    overall_trend = :overall_trend, goals_total = :goals_total,
                    goals_progressing = :goals_progressing, adherence_percent = :adherence_percent,
                    areas = :areas, objectives = :objectives, scorecard = :scorecard
                 WHERE id = :id',
                $params
            );
            return (int) $existing['id'];
        }

        return cms_insert(
            'INSERT INTO cms_progress_reports
                (case_id, review_id, cycle, period_start, period_end, overall_trend,
                 goals_total, goals_progressing, adherence_percent, areas, objectives, scorecard, created_at)
             VALUES (:case_id, :review_id, :cycle, :period_start, :period_end, :overall_trend,
                     :goals_total, :goals_progressing, :adherence_percent, :areas, :objectives, :scorecard, NOW())',
            $params
        );
    }

    public static function share(int $id, string $channel): void
    {
        cms_run(
            'UPDATE cms_progress_reports SET shared_at = NOW(), shared_channel = ? WHERE id = ?',
            [in_array($channel, ['whatsapp', 'portal', 'email'], true) ? $channel : 'whatsapp', $id]
        );
    }

    public static function shape(array $p): array
    {
        return [
            'id'               => (int) $p['id'],
            'caseId'           => (int) $p['case_id'],
            'reviewId'         => $p['review_id'] ? (int) $p['review_id'] : null,
            'cycle'            => (int) $p['cycle'],
            'periodStart'      => $p['period_start'],
            'periodEnd'        => $p['period_end'],
            'periodLabel'      => date('M j', strtotime($p['period_start'])) . ' – ' . date('M j, Y', strtotime($p['period_end'])),
            'overallTrend'     => $p['overall_trend'],
            'goalsTotal'       => (int) $p['goals_total'],
            'goalsProgressing' => (int) $p['goals_progressing'],
            'adherencePercent' => (int) $p['adherence_percent'],
            'areas'            => cms_json_col($p['areas'] ?? ''),
            'objectives'       => cms_json_col($p['objectives'] ?? ''),
            'scorecard'        => cms_json_col($p['scorecard'] ?? ''),
            'sharedAt'         => $p['shared_at'],
            'sharedChannel'    => $p['shared_channel'] ?? '',
            'createdAt'        => $p['created_at'],
        ];
    }
}
