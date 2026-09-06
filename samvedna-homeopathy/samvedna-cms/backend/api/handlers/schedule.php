<?php
/**
 * The follow-up schedule: completing touchpoints, the +/-7-day reschedule guard,
 * adherence capture, and opening the review a review-event points at.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/ScheduleEvent.php';
require_once __DIR__ . '/../../includes/models/CareCase.php';
require_once __DIR__ . '/../../includes/models/Plan.php';
require_once __DIR__ . '/../../includes/models/Review.php';
require_once __DIR__ . '/../../includes/services/ScheduleGenerator.php';

function schedule_index(): void
{
    cms_require_auth();
    ScheduleEvent::refreshStatuses();

    $events = ScheduleEvent::feed([
        'status'      => cms_query('status'),
        'type'        => cms_query('type'),
        'from'        => cms_query('from'),
        'to'          => cms_query('to'),
        'ownerId'     => cms_query('ownerId'),
        'pendingOnly' => cms_query('pending') === '1',
        'limit'       => (int) (cms_query('limit') ?: 300),
    ]);

    cms_json([
        'events' => $events,
        'counts' => [
            'overdue'  => count(array_filter($events, static fn($e) => $e['isOverdue'])),
            'upcoming' => count(array_filter($events, static fn($e) => $e['status'] === 'upcoming')),
            'done'     => count(array_filter($events, static fn($e) => $e['status'] === 'done')),
        ],
    ]);
}

function schedule_complete(int $id): void
{
    cms_require_auth();

    $event = ScheduleEvent::find($id);
    if (!$event) {
        cms_error('not_found', 'Touchpoint not found.', 404);
    }
    if ($event['status'] === 'done') {
        cms_error('conflict', 'This touchpoint is already marked done.', 409);
    }

    ScheduleEvent::complete($id, cms_str('notes'));
    cms_log('schedule', $id, 'completed', $event['title'] . ' marked done');

    cms_json(['event' => ScheduleEvent::shape(ScheduleEvent::find($id) ?? [])]);
}

/**
 * Reschedule inside the plan's window. This is the enforcement half of the
 * "Reschedule allowed ±7 days only" lock the flow shows on step 2.
 */
function schedule_reschedule(int $id): void
{
    cms_require_auth();

    $event = ScheduleEvent::find($id);
    if (!$event) {
        cms_error('not_found', 'Touchpoint not found.', 404);
    }

    $case = CareCase::find((int) $event['case_id']);
    $plan = $case ? Plan::find((int) $case['plan_id']) : null;
    $window = $plan['rescheduleWindowDays'] ?? 7;

    $newDate = cms_str('dueDate');
    $check = ScheduleGenerator::canReschedule($event, $newDate, $window);
    if (!$check['ok']) {
        cms_error('reschedule_blocked', $check['message'], 422, ['dueDate' => $check['message']]);
    }

    ScheduleEvent::reschedule($id, $newDate);
    cms_log('schedule', $id, 'rescheduled', sprintf(
        '%s moved to %s (%+d days from plan date)',
        $event['title'],
        date('M j, Y', strtotime($newDate)),
        $check['drift']
    ));

    cms_json([
        'event'  => ScheduleEvent::shape(ScheduleEvent::find($id) ?? []),
        'drift'  => $check['drift'],
        'window' => $window,
    ]);
}

/** Record a non-consult adherence check against its touchpoint. */
function schedule_adherence(int $id): void
{
    cms_require_auth();
    $user = cms_user();

    $event = ScheduleEvent::find($id);
    if (!$event) {
        cms_error('not_found', 'Touchpoint not found.', 404);
    }
    if ($event['type'] !== 'adherence') {
        cms_error('conflict', 'Adherence data can only be recorded against an adherence check.', 409);
    }

    AdherenceCheck::record([
        'eventId'       => $id,
        'caseId'        => (int) $event['case_id'],
        'cycle'         => (int) $event['cycle'],
        'compliance'    => cms_str('compliance', 'full'),
        'refill'        => cms_str('refill', 'stocked'),
        'parentConcern' => cms_bool('parentConcern'),
        'parentNote'    => cms_str('parentNote'),
        'recordedBy'    => $user ? (int) $user['id'] : null,
    ]);

    ScheduleEvent::complete($id, cms_str('parentNote'));
    cms_log('schedule', $id, 'adherence', 'Adherence check recorded for cycle ' . $event['cycle']);

    cms_json([
        'event'     => ScheduleEvent::shape(ScheduleEvent::find($id) ?? []),
        'adherence' => AdherenceCheck::forEvent($id),
        'cyclePercent' => AdherenceCheck::cyclePercent((int) $event['case_id'], (int) $event['cycle']),
    ]);
}

/**
 * Open (or create) the review that a review touchpoint represents. Creating it
 * here rather than at activation keeps the review list free of empty shells for
 * cycles the case has not reached yet.
 */
function schedule_open_review(int $id): void
{
    $user = cms_require_role(['founder', 'senior_doctor', 'case_doctor']);

    $event = ScheduleEvent::find($id);
    if (!$event) {
        cms_error('not_found', 'Touchpoint not found.', 404);
    }
    if (!in_array($event['type'], ['review', 'founder_review'], true)) {
        cms_error('conflict', 'Only a formal review touchpoint opens a review.', 409);
    }

    $case = CareCase::find((int) $event['case_id']);
    if (!$case) {
        cms_error('not_found', 'Case not found.', 404);
    }
    $plan = Plan::find((int) $case['plan_id']) ?? [];

    $existing = Review::forCaseCycle((int) $event['case_id'], (int) $event['cycle']);
    if ($existing) {
        cms_json(['review' => Review::shape($existing), 'created' => false]);
    }

    $interval = (int) ($plan['reviewIntervalDays'] ?? 60);
    $reviewId = Review::create([
        'caseId'         => (int) $event['case_id'],
        'eventId'        => $id,
        'cycle'          => (int) $event['cycle'],
        'reviewDate'     => $event['due_date'],
        // Auto-computed and locked in the UI — the flow shows this as a read-only field.
        'nextReviewDate' => cms_add_days($event['due_date'], $interval),
        'status'         => 'draft',
        'createdBy'      => (int) $user['id'],
    ]);

    ReviewSignoff::seed($reviewId, $case, $plan);
    cms_log('review', $reviewId, 'opened', 'Opened cycle ' . $event['cycle'] . ' review');

    cms_json(['review' => Review::shape(Review::find($reviewId) ?? []), 'created' => true], 201);
}
