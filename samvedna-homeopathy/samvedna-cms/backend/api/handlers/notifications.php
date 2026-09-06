<?php
/**
 * In-app notifications: the bell, the full list, and the read/dismiss actions.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Notification.php';
require_once __DIR__ . '/../../includes/models/Medicine.php';

/** The bell payload and the /notifications page. Generates today's notices first. */
function notifications_index(): void
{
    cms_require_auth();
    Notification::generateDaily();

    $status = cms_query('status');
    $items  = Notification::all([
        'status'   => $status,
        'openOnly' => $status === '' && cms_query('all') !== '1',
        'type'     => cms_query('type'),
        'limit'    => (int) (cms_query('limit') ?: 100),
    ]);

    cms_json([
        'notifications' => $items,
        'counts'        => Notification::counts(),
        'medicine'      => [
            'dueToday' => MedicineSupply::countDueToday(),
            'overdue'  => MedicineSupply::countOverdue(),
        ],
        'today'         => cms_today(),
    ]);
}

/**
 * Lightweight poll for the header bell — counts only, no rows. Also the
 * endpoint a real cron would hit each morning to pre-generate the day's
 * notices before anyone signs in.
 */
function notifications_summary(): void
{
    cms_require_auth();
    $generated = Notification::generateDaily();

    cms_json([
        'counts'    => Notification::counts(),
        'medicine'  => [
            'dueToday' => MedicineSupply::countDueToday(),
            'overdue'  => MedicineSupply::countOverdue(),
        ],
        'generated' => $generated,
        'today'     => cms_today(),
    ]);
}

function notifications_read(int $id): void
{
    cms_require_auth();
    if (!Notification::find($id)) {
        cms_error('not_found', 'Notification not found.', 404);
    }
    Notification::markRead($id);
    cms_json(['ok' => true, 'counts' => Notification::counts()]);
}

function notifications_read_all(): void
{
    cms_require_auth();
    $n = Notification::markAllRead();
    cms_json(['ok' => true, 'marked' => $n, 'counts' => Notification::counts()]);
}

function notifications_dismiss(int $id): void
{
    cms_require_auth();
    if (!Notification::find($id)) {
        cms_error('not_found', 'Notification not found.', 404);
    }
    Notification::dismiss($id);
    cms_json(['ok' => true, 'counts' => Notification::counts()]);
}
