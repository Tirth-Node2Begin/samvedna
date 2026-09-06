<?php
/**
 * The bi-monthly review: the forced template, its validation, and the sign-off
 * chain that closes it and triggers the progress dashboard.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Review.php';
require_once __DIR__ . '/../../includes/models/CareCase.php';
require_once __DIR__ . '/../../includes/models/Patient.php';
require_once __DIR__ . '/../../includes/models/Plan.php';
require_once __DIR__ . '/../../includes/models/ScheduleEvent.php';
require_once __DIR__ . '/../../includes/models/ProgressReport.php';
require_once __DIR__ . '/../../includes/services/ProgressBuilder.php';

function reviews_index(): void
{
    $user = cms_require_auth();

    $reviews = Review::feed([
        'status'         => cms_query('status'),
        'caseId'         => cms_query('caseId'),
        'awaitingUserId' => cms_query('mine') === '1' ? (int) $user['id'] : 0,
        'limit'          => (int) (cms_query('limit') ?: 200),
    ]);

    cms_json([
        'reviews' => $reviews,
        'counts'  => [
            'draft'    => count(array_filter($reviews, static fn($r) => $r['status'] === 'draft')),
            'awaiting' => count(array_filter($reviews, static fn($r) => $r['status'] === 'awaiting_signoff')),
            'closed'   => count(array_filter($reviews, static fn($r) => $r['status'] === 'closed')),
        ],
        'pendingForMe' => (int) cms_scalar(
            "SELECT COUNT(*) FROM cms_review_signoffs WHERE user_id = ? AND status = 'pending'",
            [$user['id']]
        ),
    ]);
}

/** Everything the review screen needs in one call. */
function reviews_show(int $id): void
{
    $user = cms_require_auth();

    $review = Review::find($id);
    if (!$review) {
        cms_error('not_found', 'Review not found.', 404);
    }

    $case    = CareCase::find((int) $review['case_id']);
    $patient = $case ? Patient::find((int) $case['patient_id']) : null;
    $plan    = $case ? Plan::find((int) $case['plan_id']) : null;
    $signoffs = ReviewSignoff::forReview($id);

    $mine = array_values(array_filter(
        $signoffs,
        static fn(array $s): bool => $s['userId'] === (int) $user['id'] && $s['status'] === 'pending'
    ));

    // The previous cycle's goals are what this review is scoring, so ship them
    // alongside — the screen shows them as a carry-forward checklist.
    $previousGoals = $review['cycle'] > 1
        ? ReviewGoal::forCycle((int) $review['case_id'], (int) $review['cycle'] - 1)
        : [];

    cms_json([
        'review'        => Review::shape($review),
        'goals'         => ReviewGoal::forReview($id),
        'previousGoals' => $previousGoals,
        'signoffs'      => $signoffs,
        'canSign'       => count($mine) > 0,
        'case'          => $case ? CareCase::shape($case) : null,
        'patient'       => $patient ? Patient::shape($patient) : null,
        'baseline'      => $case ? Baseline::shape(Baseline::forPatient((int) $case['patient_id']) ?? []) : null,
        'plan'          => $plan,
        'adherence'     => $case ? AdherenceCheck::cyclePercent((int) $case['id'], (int) $review['cycle']) : 0,
        'mandatory'     => Review::MANDATORY,
    ]);
}

/** Save the template as a draft. Validation is advisory here, blocking on submit. */
function reviews_update(int $id): void
{
    cms_require_role(['founder', 'senior_doctor', 'case_doctor']);

    $review = Review::find($id);
    if (!$review) {
        cms_error('not_found', 'Review not found.', 404);
    }
    if ($review['status'] === 'closed') {
        cms_error('conflict', 'A closed review cannot be edited.', 409);
    }

    $body  = cms_body();
    $goals = is_array($body['goals'] ?? null) ? $body['goals'] : [];
    $check = Review::validate($body, $goals);

    $body['completeness'] = $check['completeness'];
    Review::saveBody($id, $body);
    ReviewGoal::replace($id, (int) $review['case_id'], (int) $review['cycle'], $goals);

    cms_json([
        'review'       => Review::shape(Review::find($id) ?? []),
        'goals'        => ReviewGoal::forReview($id),
        'fields'       => $check['fields'],
        'completeness' => $check['completeness'],
    ]);
}

/**
 * Submit the review. Every mandatory field must be filled and — the rule the
 * flow is built around — a protocol change must carry a written rationale.
 * A plan that requires a Senior Doctor leaves the review in `awaiting_signoff`.
 */
function reviews_submit(int $id): void
{
    $user = cms_require_role(['founder', 'senior_doctor', 'case_doctor']);

    $review = Review::find($id);
    if (!$review) {
        cms_error('not_found', 'Review not found.', 404);
    }
    if ($review['status'] === 'closed') {
        cms_error('conflict', 'This review is already closed.', 409);
    }

    $case = CareCase::find((int) $review['case_id']);
    $plan = $case ? Plan::find((int) $case['plan_id']) : null;
    if (!$case || !$plan) {
        cms_error('not_found', 'Case or plan not found for this review.', 404);
    }

    $body  = cms_body();
    $goals = is_array($body['goals'] ?? null) ? $body['goals'] : [];
    $check = Review::validate($body, $goals);

    if ($check['fields']) {
        // Persist what they did type before rejecting, so nothing is lost.
        $body['completeness'] = $check['completeness'];
        Review::saveBody($id, $body);
        ReviewGoal::replace($id, (int) $review['case_id'], (int) $review['cycle'], $goals);

        cms_error(
            'validation',
            'This review cannot be submitted until every mandatory field is complete.',
            422,
            $check['fields']
        );
    }

    $body['completeness'] = 100;
    Review::saveBody($id, $body);
    ReviewGoal::replace($id, (int) $review['case_id'], (int) $review['cycle'], $goals);

    // The author's own sign-off lands with the submission.
    ReviewSignoff::seed($id, $case, $plan);
    ReviewSignoff::sign($id, (int) $user['id'], 'Submitted by author');

    if (ReviewSignoff::allSigned($id)) {
        $progressId = reviews_finalise($id);
        cms_log('review', $id, 'closed', 'Cycle ' . $review['cycle'] . ' review closed');
        cms_json([
            'review'     => Review::shape(Review::find($id) ?? []),
            'signoffs'   => ReviewSignoff::forReview($id),
            'closed'     => true,
            'progressId' => $progressId,
        ]);
    }

    Review::setStatus($id, 'awaiting_signoff');
    cms_log('review', $id, 'submitted', 'Cycle ' . $review['cycle'] . ' review awaiting sign-off');

    $pending = array_values(array_filter(
        ReviewSignoff::forReview($id),
        static fn(array $s): bool => $s['status'] === 'pending'
    ));

    cms_json([
        'review'   => Review::shape(Review::find($id) ?? []),
        'signoffs' => ReviewSignoff::forReview($id),
        'closed'   => false,
        'awaiting' => array_map(static fn(array $s): string => $s['roleLabel'], $pending),
        'message'  => 'Review cannot be closed until ' . implode(' and ', array_map(
            static fn(array $s): string => $s['roleLabel'],
            $pending
        )) . ' signs off.',
    ]);
}

/** Record one sign-off; the last one closes the review. */
function reviews_signoff(int $id): void
{
    $user = cms_require_role(['founder', 'senior_doctor', 'case_doctor']);

    $review = Review::find($id);
    if (!$review) {
        cms_error('not_found', 'Review not found.', 404);
    }
    if ($review['status'] === 'closed') {
        cms_error('conflict', 'This review is already closed.', 409);
    }
    if ($review['status'] !== 'awaiting_signoff') {
        cms_error('conflict', 'The review has not been submitted for sign-off yet.', 409);
    }

    if (!ReviewSignoff::sign($id, (int) $user['id'], cms_str('comment'))) {
        cms_error('forbidden', 'You do not have a pending sign-off on this review.', 403);
    }

    cms_log('review', $id, 'signed', cms_role_label($user['role']) . ' signed off cycle ' . $review['cycle']);

    if (!ReviewSignoff::allSigned($id)) {
        cms_json([
            'review'   => Review::shape(Review::find($id) ?? []),
            'signoffs' => ReviewSignoff::forReview($id),
            'closed'   => false,
        ]);
    }

    $progressId = reviews_finalise($id);
    cms_log('review', $id, 'closed', 'Cycle ' . $review['cycle'] . ' review closed');

    cms_json([
        'review'     => Review::shape(Review::find($id) ?? []),
        'signoffs'   => ReviewSignoff::forReview($id),
        'closed'     => true,
        'progressId' => $progressId,
    ]);
}

/**
 * Close the review and cascade: build the progress dashboard, tick the schedule
 * event done, advance the cycle. This is the "Submit and generate dashboard"
 * button in the flow, unpacked.
 *
 * @return int The progress report id.
 */
function reviews_finalise(int $id): int
{
    $review = Review::find($id);
    $case   = CareCase::find((int) $review['case_id']);

    $onTime = ProgressBuilder::wasOnTime($review);
    // Demo satisfaction: real deployments collect this from the parent feedback
    // form. Kept deterministic per review so the scorecard is stable on reload.
    $satisfaction = round(4.2 + (($id % 7) / 10), 1);

    Review::close($id, $onTime, $satisfaction);
    $review = Review::find($id);

    $progressId = ProgressBuilder::build($case, $review);

    if ($review['event_id']) {
        ScheduleEvent::complete((int) $review['event_id'], 'Formal review closed.');
    }
    CareCase::advanceCycle((int) $case['id']);

    return $progressId;
}
