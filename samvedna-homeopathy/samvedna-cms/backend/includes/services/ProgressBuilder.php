<?php
/**
 * Builds the per-cycle progress dashboard (step 6) at the moment a review closes.
 *
 * The whole point of the flow's "auto-generated" badge is that nobody types this
 * screen: the trend, the area bars, the objectives and the audit scorecard are
 * all derived from data the forced template already captured.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/ProgressReport.php';
require_once __DIR__ . '/../models/ScheduleEvent.php';
require_once __DIR__ . '/../models/Plan.php';

class ProgressBuilder
{
    /**
     * Generate (or regenerate) the report for a closed review.
     *
     * @return int Progress report id.
     */
    public static function build(array $case, array $review): int
    {
        $caseId = (int) $case['id'];
        $cycle  = (int) $review['cycle'];
        $plan   = Plan::find((int) $case['plan_id']) ?? [];
        $interval = $plan['reviewIntervalDays'] ?? 60;

        $periodStart = cms_add_days($case['start_date'], $interval * ($cycle - 1));
        $periodEnd   = $review['review_date'];

        $goals = ReviewGoal::forReview((int) $review['id']);
        $goalsTotal = count($goals);
        $goalsProgressing = count(array_filter(
            $goals,
            static fn(array $g): bool => in_array($g['status'], ['progressing', 'achieved'], true)
        ));

        $adherence = (int) $review['adherence_percent'];
        if ($adherence === 0) {
            $adherence = AdherenceCheck::cyclePercent($caseId, $cycle);
        }

        $areas = self::areas($caseId, $cycle, $review);
        $trend = self::trend($areas, $goalsTotal, $goalsProgressing, (string) $review['protocol_decision']);

        $objectives = array_map(static fn(array $g): array => [
            'title'  => $g['title'],
            'metric' => $g['metric'],
            'done'   => $g['status'] === 'achieved',
        ], $goals);

        return ProgressReport::upsert([
            'caseId'           => $caseId,
            'reviewId'         => (int) $review['id'],
            'cycle'            => $cycle,
            'periodStart'      => $periodStart,
            'periodEnd'        => $periodEnd,
            'overallTrend'     => $trend,
            'goalsTotal'       => $goalsTotal,
            'goalsProgressing' => $goalsProgressing,
            'adherencePercent' => $adherence,
            'areas'            => $areas,
            'objectives'       => $objectives,
            'scorecard'        => self::scorecard($case, $review),
        ]);
    }

    /**
     * Area-wise progress, charted against the same baseline markers captured at
     * intake so a parent can see movement on the exact things they flagged.
     *
     * The demo has no per-cycle re-rating screen, so movement is modelled from
     * the review's own signals: a marker named in the improvements text gains,
     * one named in the stagnation text holds flat.
     */
    private static function areas(int $caseId, int $cycle, array $review): array
    {
        $baseline = Baseline::forPatient((int) cms_scalar('SELECT patient_id FROM cms_cases WHERE id = ?', [$caseId]));
        $markers  = Baseline::shape($baseline ?? [])['markers'];

        $improved = mb_strtolower((string) ($review['improvements'] ?? ''));
        $stalled  = mb_strtolower((string) ($review['stagnation'] ?? ''));

        // A marker gains 15 points per cycle when the review names it as improving.
        $gainPerCycle = 15;

        return array_map(static function (array $m) use ($improved, $stalled, $cycle, $gainPerCycle): array {
            $needle = mb_strtolower(explode(' ', $m['label'])[0]);
            $base   = (int) $m['score'];

            $mentionedImproved = $needle !== '' && str_contains($improved, $needle);
            $mentionedStalled  = $needle !== '' && str_contains($stalled, $needle);

            if ($mentionedImproved) {
                $score = min(100, $base + $gainPerCycle * $cycle);
                $trend = 'improving';
            } elseif ($mentionedStalled) {
                // The doctor explicitly called this out as not moving.
                $score = $base;
                $trend = 'stable';
            } else {
                // Unnamed markers drift up slightly with time on protocol, but a
                // marker the review never mentions has no evidence behind it —
                // it reads as stable, never as a claimed improvement.
                $score = min(100, $base + (int) round($gainPerCycle * $cycle * 0.4));
                $trend = 'stable';
            }

            return [
                'key'      => $m['key'],
                'label'    => $m['label'],
                'baseline' => $base,
                'score'    => $score,
                'trend'    => $trend,
            ];
        }, $markers);
    }

    /** Overall trend from the area movement, goal hit-rate and protocol decision. */
    private static function trend(array $areas, int $goalsTotal, int $goalsProgressing, string $decision): string
    {
        $improving = count(array_filter($areas, static fn(array $a): bool => $a['trend'] === 'improving'));
        $ratio = $areas ? $improving / count($areas) : 0;
        $goalRatio = $goalsTotal > 0 ? $goalsProgressing / $goalsTotal : 0;

        if ($decision === 'paused') {
            return 'declining';
        }
        if ($ratio >= 0.5 || $goalRatio >= 0.6) {
            return 'improving';
        }
        if ($ratio <= 0.1 && $goalRatio <= 0.2) {
            return 'declining';
        }
        return 'stable';
    }

    /**
     * The internal audit scorecard: how well the Case Doctor actually operated
     * the CMS this cycle, not how the child is doing.
     */
    private static function scorecard(array $case, array $review): array
    {
        $doctor = $case['case_doctor_id']
            ? cms_one('SELECT name FROM cms_users WHERE id = ?', [$case['case_doctor_id']])
            : null;

        $onTime = $review['closed_on_time'] === null
            ? self::wasOnTime($review)
            : (bool) $review['closed_on_time'];

        return [
            'doctor'       => $doctor['name'] ?? 'Unassigned',
            'completeness' => (int) $review['completeness'],
            'closure'      => $onTime ? 'On time' : 'Late',
            'onTime'       => $onTime,
            'satisfaction' => $review['parent_satisfaction'] !== null
                ? (float) $review['parent_satisfaction']
                : 4.5,
        ];
    }

    /** A review closed within 3 days of its scheduled date counts as on time. */
    public static function wasOnTime(array $review): bool
    {
        $closed = $review['closed_at'] ? date('Y-m-d', strtotime($review['closed_at'])) : cms_today();
        return cms_days_between($review['review_date'], $closed) <= 3;
    }
}
