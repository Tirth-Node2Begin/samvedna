<?php
/**
 * Builds the follow-up schedule from a plan, and polices the reschedule window.
 *
 * Step 3 of the flow: "Schedule auto-generated based on the plan. Formal reviews
 * locked at 60-day intervals. Adherence checks are non-consult touchpoints."
 * Nothing in here reads a user-supplied interval — every cadence comes from the
 * plan row, which is what makes the lock real rather than cosmetic.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../models/ScheduleEvent.php';

class ScheduleGenerator
{
    /**
     * Generate every touchpoint for a freshly activated case.
     *
     * @return int Number of events created.
     */
    public static function build(int $caseId, array $plan, string $startDate, array $roles): int
    {
        $reviewInterval    = max(7, (int) $plan['reviewIntervalDays']);
        $adherenceInterval = (int) $plan['adherenceIntervalDays'];
        $cycles            = self::cycleCount($plan);
        $created           = 0;

        // Day 0 — activation and baseline, owned by the Case Doctor.
        ScheduleEvent::create([
            'caseId'  => $caseId,
            'cycle'   => 1,
            'type'    => 'activation',
            'title'   => 'Plan activation & baseline',
            'dueDate' => $startDate,
            'status'  => 'done',
            'ownerId' => $roles['caseDoctorId'] ?? null,
            'notes'   => 'Baseline captured at intake.',
        ]);
        $created++;

        for ($cycle = 1; $cycle <= $cycles; $cycle++) {
            $cycleStart = cms_add_days($startDate, $reviewInterval * ($cycle - 1));
            $cycleEnd   = cms_add_days($startDate, $reviewInterval * $cycle);

            // Adherence checks inside the cycle. They stop short of the cycle end
            // so a check never lands on the same day as the formal review.
            if ($adherenceInterval > 0) {
                $offset = $adherenceInterval;
                while ($offset < $reviewInterval) {
                    ScheduleEvent::create([
                        'caseId'  => $caseId,
                        'cycle'   => $cycle,
                        'type'    => 'adherence',
                        'title'   => 'Adherence check',
                        'dueDate' => cms_add_days($cycleStart, $offset),
                        'status'  => 'scheduled',
                        'ownerId' => $roles['caseDoctorId'] ?? null,
                        'notes'   => 'Non-consult · medicine compliance, refill status, parent concern flag',
                    ]);
                    $created++;
                    $offset += $adherenceInterval;
                }
            }

            // The formal review that closes the cycle.
            ScheduleEvent::create([
                'caseId'  => $caseId,
                'cycle'   => $cycle,
                'type'    => 'review',
                'title'   => self::reviewTitle($reviewInterval),
                'dueDate' => $cycleEnd,
                'status'  => 'scheduled',
                'ownerId' => $roles['caseDoctorId'] ?? null,
                'notes'   => !empty($plan['requiresSenior'])
                    ? 'Case Doctor + Senior Doctor · structured review'
                    : 'Case Doctor · structured review',
            ]);
            $created++;
        }

        // Premium adds a founder review on its own cadence, independent of cycles.
        if (!empty($plan['requiresFounder']) && (int) $plan['founderIntervalDays'] > 0) {
            $founderInterval = (int) $plan['founderIntervalDays'];
            $totalDays = $reviewInterval * $cycles;
            for ($offset = $founderInterval; $offset <= $totalDays; $offset += $founderInterval) {
                ScheduleEvent::create([
                    'caseId'  => $caseId,
                    'cycle'   => (int) ceil($offset / $reviewInterval),
                    'type'    => 'founder_review',
                    'title'   => 'Founder review',
                    'dueDate' => cms_add_days($startDate, $offset),
                    'status'  => 'scheduled',
                    'ownerId' => $roles['founderId'] ?? null,
                    'notes'   => 'Quarterly founder oversight (Premium plan)',
                ]);
                $created++;
            }
        }

        ScheduleEvent::refreshStatuses($caseId);
        return $created;
    }

    /** How many formal review cycles the plan runs. */
    public static function cycleCount(array $plan): int
    {
        $byDuration = (int) floor(((int) $plan['durationMonths'] * 30) / max(7, (int) $plan['reviewIntervalDays']));
        return max(1, min((int) $plan['reviewCount'], max(1, $byDuration)));
    }

    /** "Bi-monthly formal review" only reads right at a 60-day cadence. */
    private static function reviewTitle(int $intervalDays): string
    {
        if ($intervalDays === 60) {
            return 'Bi-monthly formal review';
        }
        if ($intervalDays === 30) {
            return 'Monthly formal review';
        }
        return $intervalDays . '-day formal review';
    }

    /**
     * The +/- N-day reschedule guard. Measured against original_due_date, never
     * against the current due date — otherwise a chain of small moves would walk
     * an appointment arbitrarily far from where the plan put it.
     *
     * @return array{ok: bool, message: string, drift: int}
     */
    public static function canReschedule(array $event, string $newDate, int $windowDays): array
    {
        if (!cms_is_date($newDate)) {
            return ['ok' => false, 'message' => 'Provide a valid date (YYYY-MM-DD).', 'drift' => 0];
        }
        if ($event['status'] === 'done') {
            return ['ok' => false, 'message' => 'This touchpoint is already completed.', 'drift' => 0];
        }

        $drift = cms_days_between($event['original_due_date'], $newDate);
        if (abs($drift) > $windowDays) {
            return [
                'ok'      => false,
                'drift'   => $drift,
                'message' => sprintf(
                    'Follow-up frequency is locked by the plan. This touchpoint can move at most ±%d days from %s — the date you picked is %d days out.',
                    $windowDays,
                    date('M j, Y', strtotime($event['original_due_date'])),
                    $drift
                ),
            ];
        }

        return ['ok' => true, 'message' => '', 'drift' => $drift];
    }
}
