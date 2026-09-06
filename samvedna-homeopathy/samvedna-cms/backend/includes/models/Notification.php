<?php
/**
 * In-app notifications.
 *
 * There is no cron in the demo, so "the morning notification" is produced the
 * first time anyone touches the API each day: generateDaily() scans the medicine
 * cycles and inserts one notice per case whose supply is due or overdue. The
 * unique key on (type, case_id, due_date) makes that scan idempotent, so it is
 * safe to call on every request.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class Notification
{
    public const MEDICINE_DUE          = 'medicine_due';
    public const MEDICINE_OUT_OF_STOCK = 'medicine_out_of_stock';

    /**
     * Retired. The practice runs many patients at once, and a second, separate
     * "N days ago" notice per late case buried the one list that matters —
     * who to send medicine to today. Kept only so legacy rows can be swept up.
     */
    private const LEGACY_MEDICINE_OVERDUE = 'medicine_overdue';

    /** Every medicine-related type, for resolve/cleanup queries. */
    private const MEDICINE_TYPES = [
        self::MEDICINE_DUE,
        self::MEDICINE_OUT_OF_STOCK,
        self::LEGACY_MEDICINE_OVERDUE,
    ];

    /**
     * Generate the morning list: one notice per patient whose medicine is due.
     *
     * Exactly one notice per (case, due date) — created the day the supply falls
     * due and left open until someone acts on it. A case that is still waiting
     * three days later does NOT accumulate three notices; it keeps the one it
     * already has, so the bell always reads as "these are the patients to send
     * medicine to", never as a backlog of chase-ups.
     *
     * Cheap enough to run on every request; the unique key makes it idempotent
     * and the static flag stops it repeating within a single request.
     */
    public static function generateDaily(): int
    {
        static $ranThisRequest = false;
        if ($ranThisRequest) {
            return 0;
        }
        $ranThisRequest = true;

        // Sweep away notices from the retired overdue type so an upgraded
        // database does not keep showing them.
        cms_run(
            "UPDATE cms_notifications SET status = 'dismissed'
             WHERE type = ? AND status IN ('unread','read')",
            [self::LEGACY_MEDICINE_OVERDUE]
        );

        $today   = cms_today();
        $created = 0;

        $due = cms_all(
            "SELECT s.case_id, s.patient_id, s.next_due_on, s.last_delivered_on, s.interval_days,
                    s.stock_status, p.child_name
             FROM cms_medicine_supply s
             JOIN cms_cases c ON c.id = s.case_id
             JOIN cms_patients p ON p.id = s.patient_id
             WHERE c.status = 'active' AND s.status = 'active' AND s.next_due_on <= ?",
            [$today]
        );

        foreach ($due as $row) {
            $wasOutOfStock = ($row['stock_status'] ?? 'in_stock') === 'out_of_stock';

            $created += self::upsert([
                'type'      => self::MEDICINE_DUE,
                'caseId'    => (int) $row['case_id'],
                'patientId' => (int) $row['patient_id'],
                'dueDate'   => $row['next_due_on'],
                'title'     => 'Deliver medicine today — ' . $row['child_name'],
                'body'      => $wasOutOfStock
                    ? sprintf(
                        'Deferred supply is due. Confirm stock, then send the %d-day supply.',
                        (int) $row['interval_days']
                    )
                    : sprintf(
                        'Send the %d-day supply. Last delivered %s.',
                        (int) $row['interval_days'],
                        $row['last_delivered_on']
                            ? date('j M', strtotime($row['last_delivered_on']))
                            : 'at plan start'
                    ),
                'href'      => '/medicine?case=' . (int) $row['case_id'],
            ]);
        }

        return $created;
    }

    /**
     * Raise the out-of-stock notice. Keyed on the NEW due date, so deferring
     * twice on different days produces two distinct notices rather than
     * silently colliding on the unique key.
     */
    public static function medicineOutOfStock(
        int $caseId,
        int $patientId,
        string $patientName,
        string $nextDueOn,
        int $deferDays
    ): int {
        return self::upsert([
            'type'      => self::MEDICINE_OUT_OF_STOCK,
            'caseId'    => $caseId,
            'patientId' => $patientId,
            'dueDate'   => $nextDueOn,
            'title'     => 'Out of stock — ' . $patientName,
            'body'      => sprintf(
                'Medicine unavailable. Delivery moved to %s, %d day%s from today.',
                date('j M Y', strtotime($nextDueOn)),
                $deferDays,
                $deferDays === 1 ? '' : 's'
            ),
            'href'      => '/medicine?case=' . $caseId,
        ]);
    }

    /**
     * A deferred case is no longer actionable today, so its open "deliver today"
     * notice is retired — the out-of-stock notice replaces it.
     */
    public static function supersedeMedicineDue(int $caseId): int
    {
        return cms_run(
            "UPDATE cms_notifications SET status = 'dismissed'
             WHERE case_id = ? AND type IN (?, ?) AND status IN ('unread','read')",
            [$caseId, self::MEDICINE_DUE, self::LEGACY_MEDICINE_OVERDUE]
        );
    }

    /** Insert if absent. Returns 1 when a row was created, 0 when it already existed. */
    private static function upsert(array $n): int
    {
        return cms_run(
            'INSERT IGNORE INTO cms_notifications
                (type, case_id, patient_id, title, body, href, due_date, status, created_at)
             VALUES (:type, :case_id, :patient_id, :title, :body, :href, :due_date, :status, NOW())',
            [
                'type'       => $n['type'],
                'case_id'    => $n['caseId'],
                'patient_id' => $n['patientId'],
                'title'      => mb_substr($n['title'], 0, 191),
                'body'       => mb_substr((string) ($n['body'] ?? ''), 0, 255),
                'href'       => $n['href'] ?? null,
                'due_date'   => $n['dueDate'],
                'status'     => 'unread',
            ]
        );
    }

    /** Listing, unread first, newest first within each status. */
    public static function all(array $filters = []): array
    {
        $sql = 'SELECT n.*, p.child_name, p.code AS patient_code
                FROM cms_notifications n
                LEFT JOIN cms_patients p ON p.id = n.patient_id
                WHERE 1 = 1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND n.status = ?';
            $params[] = $filters['status'];
        } elseif (!empty($filters['openOnly'])) {
            $sql .= " AND n.status IN ('unread','read')";
        }
        if (!empty($filters['type'])) {
            $sql .= ' AND n.type = ?';
            $params[] = $filters['type'];
        }

        $sql .= " ORDER BY FIELD(n.status,'unread','read','done','dismissed'), n.created_at DESC, n.id DESC";
        $sql .= ' LIMIT ' . (int) ($filters['limit'] ?? 100);

        return array_map([self::class, 'shape'], cms_all($sql, $params));
    }

    public static function counts(): array
    {
        $rows = cms_all('SELECT status, COUNT(*) AS n FROM cms_notifications GROUP BY status');
        $out = ['unread' => 0, 'read' => 0, 'done' => 0, 'dismissed' => 0];
        foreach ($rows as $r) {
            $out[$r['status']] = (int) $r['n'];
        }
        $out['open'] = $out['unread'] + $out['read'];
        return $out;
    }

    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_notifications WHERE id = ? LIMIT 1', [$id]);
    }

    public static function markRead(int $id): void
    {
        cms_run(
            "UPDATE cms_notifications SET status = 'read', read_at = NOW()
             WHERE id = ? AND status = 'unread'",
            [$id]
        );
    }

    public static function markAllRead(): int
    {
        return cms_run("UPDATE cms_notifications SET status = 'read', read_at = NOW() WHERE status = 'unread'");
    }

    public static function dismiss(int $id): void
    {
        cms_run("UPDATE cms_notifications SET status = 'dismissed' WHERE id = ? AND status IN ('unread','read')", [$id]);
    }

    /**
     * Close every open medicine notice for a case — called when a delivery is
     * recorded. This includes the out-of-stock notice: the medicine arrived, so
     * the shortage is resolved too.
     */
    public static function resolveMedicine(int $caseId): int
    {
        $types = self::MEDICINE_TYPES;
        $slots = implode(',', array_fill(0, count($types), '?'));

        return cms_run(
            "UPDATE cms_notifications SET status = 'done', done_at = NOW()
             WHERE case_id = ? AND type IN ({$slots}) AND status IN ('unread','read')",
            array_merge([$caseId], $types)
        );
    }

    public static function shape(array $n): array
    {
        return [
            'id'          => (int) $n['id'],
            'type'        => $n['type'],
            'caseId'      => $n['case_id'] ? (int) $n['case_id'] : null,
            'patientId'   => $n['patient_id'] ? (int) $n['patient_id'] : null,
            'patientName' => $n['child_name'] ?? null,
            'patientCode' => $n['patient_code'] ?? null,
            'title'       => $n['title'],
            'body'        => $n['body'] ?? '',
            'href'        => $n['href'] ?? null,
            'dueDate'     => $n['due_date'],
            'status'      => $n['status'],
            'isMedicine'  => in_array($n['type'], self::MEDICINE_TYPES, true),
            // Only a "deliver today" notice offers the Mark-delivered shortcut;
            // an out-of-stock notice is information, not a task for today.
            'isActionable' => $n['type'] === self::MEDICINE_DUE,
            'isOutOfStock' => $n['type'] === self::MEDICINE_OUT_OF_STOCK,
            'createdAt'   => $n['created_at'],
            'readAt'      => $n['read_at'],
            'doneAt'      => $n['done_at'],
        ];
    }
}
