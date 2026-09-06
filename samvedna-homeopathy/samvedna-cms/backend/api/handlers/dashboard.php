<?php
/**
 * The command dashboard: what needs attention today, across every case.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/ScheduleEvent.php';
require_once __DIR__ . '/../../includes/models/Review.php';
require_once __DIR__ . '/../../includes/models/Escalation.php';
require_once __DIR__ . '/../../includes/models/User.php';
require_once __DIR__ . '/../../includes/models/Medicine.php';
require_once __DIR__ . '/../../includes/models/Notification.php';

function dashboard_index(): void
{
    $user = cms_require_auth();
    ScheduleEvent::refreshStatuses();
    Notification::generateDaily();

    $medicineBoard = MedicineSupply::board();

    $activeCases = (int) cms_scalar("SELECT COUNT(*) FROM cms_cases WHERE status = 'active'");
    $draftIntakes = (int) cms_scalar("SELECT COUNT(*) FROM cms_patients WHERE status = 'draft'");

    $reviewsDue = (int) cms_scalar(
        "SELECT COUNT(*) FROM cms_schedule_events
         WHERE type IN ('review','founder_review') AND status IN ('upcoming','missed')
           AND due_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)"
    );
    $awaitingSignoff = (int) cms_scalar("SELECT COUNT(*) FROM cms_reviews WHERE status = 'awaiting_signoff'");
    $pendingForMe = (int) cms_scalar(
        "SELECT COUNT(*) FROM cms_review_signoffs WHERE user_id = ? AND status = 'pending'",
        [$user['id']]
    );

    $openEscalations = (int) cms_scalar("SELECT COUNT(*) FROM cms_escalations WHERE status IN ('open','in_progress')");
    $overdueEscalations = (int) cms_scalar(
        "SELECT COUNT(*) FROM cms_escalations WHERE status IN ('open','in_progress') AND due_date < CURDATE()"
    );
    $overdueTouchpoints = (int) cms_scalar("SELECT COUNT(*) FROM cms_schedule_events WHERE status = 'missed'");

    // Average medicine adherence across every check recorded in the last 90 days.
    $adherenceRows = cms_all(
        "SELECT medicine_compliance FROM cms_adherence_checks
         WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)"
    );
    $weights = ['full' => 100, 'partial' => 55, 'none' => 0];
    $adherence = 0;
    if ($adherenceRows) {
        $sum = 0;
        foreach ($adherenceRows as $r) {
            $sum += $weights[$r['medicine_compliance']] ?? 0;
        }
        $adherence = (int) round($sum / count($adherenceRows));
    }

    $completeness = (int) round((float) cms_scalar(
        "SELECT COALESCE(AVG(completeness), 0) FROM cms_reviews WHERE status = 'closed'",
        [],
        0
    ));

    $trendCounts = cms_all(
        "SELECT overall_trend, COUNT(*) AS n FROM cms_progress_reports GROUP BY overall_trend"
    );
    $trends = ['improving' => 0, 'stable' => 0, 'declining' => 0];
    foreach ($trendCounts as $t) {
        $trends[$t['overall_trend']] = (int) $t['n'];
    }

    cms_json([
        'metrics' => [
            'activeCases'        => $activeCases,
            'draftIntakes'       => $draftIntakes,
            'reviewsDue'         => $reviewsDue,
            'awaitingSignoff'    => $awaitingSignoff,
            'pendingForMe'       => $pendingForMe,
            'openEscalations'    => $openEscalations,
            'overdueEscalations' => $overdueEscalations,
            'overdueTouchpoints' => $overdueTouchpoints,
            'adherence'          => $adherence,
            'cmsCompleteness'    => $completeness,
            'medicineDueToday'   => count($medicineBoard['dueToday']),
            'medicineOverdue'    => count($medicineBoard['overdue']),
            'unreadNotifications' => Notification::counts()['unread'],
        ],
        'medicine'    => [
            'dueToday' => $medicineBoard['dueToday'],
            'overdue'  => $medicineBoard['overdue'],
        ],
        'trends'      => $trends,
        'upcoming'    => ScheduleEvent::feed(['pendingOnly' => true, 'limit' => 8]),
        'reviewQueue' => Review::feed(['status' => 'awaiting_signoff', 'limit' => 6]),
        'escalations' => Escalation::feed(['openOnly' => true, 'limit' => 6]),
        'activity'    => array_map(static fn(array $a): array => [
            'id'        => (int) $a['id'],
            'actorName' => $a['actor_name'] ?? 'System',
            'action'    => $a['action'],
            'summary'   => $a['summary'] ?? '',
            'createdAt' => $a['created_at'],
        ], cms_all('SELECT * FROM cms_activity_log ORDER BY id DESC LIMIT 10')),
        'scorecard'   => User::scorecard((int) $user['id']),
    ]);
}
