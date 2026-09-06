<?php
/**
 * The medicine supply cycle.
 *
 * Every active case carries one MedicineSupply row. Its `next_due_on` is the
 * single fact the whole feature hangs on: the morning notification fires when
 * it is today, the Medicine page groups cases by it, and recording a delivery
 * rewrites it to `delivered_on + interval_days`.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class MedicineSupply
{
    public const DEFAULT_INTERVAL = 15;

    /**
     * How far a delivery slips when the medicine is out of stock. The pharmacy
     * restocks on roughly this cadence, so the deferred date is a real promise
     * to the parent rather than a guess.
     */
    public const DEFAULT_OUT_OF_STOCK_DEFER = 5;

    public static function forCase(int $caseId): ?array
    {
        return cms_one('SELECT * FROM cms_medicine_supply WHERE case_id = ? LIMIT 1', [$caseId]);
    }

    /**
     * Create the cycle for a freshly activated case. The plan start date counts
     * as the first supply, so the first delivery reminder lands 15 days later.
     */
    public static function start(int $caseId, int $patientId, string $firstDeliveredOn, int $interval = self::DEFAULT_INTERVAL): int
    {
        $interval = max(1, $interval);
        return cms_insert(
            'INSERT INTO cms_medicine_supply
                (case_id, patient_id, interval_days, last_delivered_on, next_due_on, status, created_at, updated_at)
             VALUES (:case_id, :patient_id, :interval, :last, :next, :status, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                interval_days = VALUES(interval_days), last_delivered_on = VALUES(last_delivered_on),
                next_due_on = VALUES(next_due_on), status = VALUES(status), updated_at = NOW()',
            [
                'case_id'    => $caseId,
                'patient_id' => $patientId,
                'interval'   => $interval,
                'last'       => $firstDeliveredOn,
                'next'       => cms_add_days($firstDeliveredOn, $interval),
                'status'     => 'active',
            ]
        );
    }

    /**
     * Record a delivery: log it, then move the cycle on. Returns the new
     * next-due date so the caller can show it immediately.
     */
    public static function deliver(int $caseId, array $d): array
    {
        $supply = self::forCase($caseId);
        if (!$supply) {
            throw new RuntimeException('This case has no medicine cycle yet.');
        }

        $deliveredOn = $d['deliveredOn'] ?? cms_today();
        $interval    = (int) $supply['interval_days'];
        $nextDue     = cms_add_days($deliveredOn, $interval);

        $deliveryId = cms_insert(
            'INSERT INTO cms_medicine_deliveries
                (case_id, patient_id, kind, delivered_on, next_due_on, mode, reference, notes, delivered_by, created_at)
             VALUES (:case_id, :patient_id, :kind, :delivered_on, :next_due_on, :mode, :reference, :notes, :by, NOW())',
            [
                'case_id'      => $caseId,
                'patient_id'   => (int) $supply['patient_id'],
                'kind'         => 'delivered',
                'delivered_on' => $deliveredOn,
                'next_due_on'  => $nextDue,
                'mode'         => in_array($d['mode'] ?? '', ['courier', 'hand', 'pickup', 'other'], true) ? $d['mode'] : 'courier',
                'reference'    => mb_substr(trim((string) ($d['reference'] ?? '')), 0, 128),
                'notes'        => trim((string) ($d['notes'] ?? '')),
                'by'           => $d['deliveredBy'] ?: null,
            ]
        );

        // A real delivery always clears the out-of-stock flag — the medicine
        // demonstrably exists now.
        cms_run(
            "UPDATE cms_medicine_supply
             SET last_delivered_on = ?, next_due_on = ?,
                 stock_status = 'in_stock', out_of_stock_since = NULL, stock_note = NULL,
                 updated_at = NOW()
             WHERE case_id = ?",
            [$deliveredOn, $nextDue, $caseId]
        );

        return ['deliveryId' => $deliveryId, 'deliveredOn' => $deliveredOn, 'nextDueOn' => $nextDue];
    }

    /**
     * The medicine is not available. Nothing was delivered, so `last_delivered_on`
     * is untouched — only the next due date moves, by `deferDays` from today, and
     * the case stays flagged until a real delivery clears it.
     *
     * @return array{deferredOn:string, nextDueOn:string, deferDays:int}
     */
    public static function markOutOfStock(int $caseId, array $d): array
    {
        $supply = self::forCase($caseId);
        if (!$supply) {
            throw new RuntimeException('This case has no medicine cycle yet.');
        }

        $deferDays  = max(1, min(60, (int) ($d['deferDays'] ?? self::DEFAULT_OUT_OF_STOCK_DEFER)));
        $recordedOn = cms_today();
        $nextDue    = cms_add_days($recordedOn, $deferDays);
        $note       = mb_substr(trim((string) ($d['notes'] ?? '')), 0, 255);

        cms_insert(
            'INSERT INTO cms_medicine_deliveries
                (case_id, patient_id, kind, delivered_on, next_due_on, mode, reference, notes, delivered_by, created_at)
             VALUES (:case_id, :patient_id, :kind, :on, :next_due_on, :mode, :reference, :notes, :by, NOW())',
            [
                'case_id'     => $caseId,
                'patient_id'  => (int) $supply['patient_id'],
                'kind'        => 'deferred_out_of_stock',
                'on'          => $recordedOn,
                'next_due_on' => $nextDue,
                'mode'        => 'other',
                'reference'   => '',
                'notes'       => $note,
                'by'          => $d['recordedBy'] ?: null,
            ]
        );

        cms_run(
            "UPDATE cms_medicine_supply
             SET next_due_on = ?, stock_status = 'out_of_stock',
                 out_of_stock_since = COALESCE(out_of_stock_since, ?), stock_note = ?,
                 updated_at = NOW()
             WHERE case_id = ?",
            [$nextDue, $recordedOn, $note, $caseId]
        );

        return ['deferredOn' => $recordedOn, 'nextDueOn' => $nextDue, 'deferDays' => $deferDays];
    }

    /** Change the interval and/or manually move the next due date. */
    public static function update(int $caseId, array $d): void
    {
        $supply = self::forCase($caseId);
        if (!$supply) {
            throw new RuntimeException('This case has no medicine cycle yet.');
        }

        $interval = isset($d['intervalDays']) ? max(1, min(90, (int) $d['intervalDays'])) : (int) $supply['interval_days'];
        $nextDue  = (isset($d['nextDueOn']) && cms_is_date((string) $d['nextDueOn']))
            ? (string) $d['nextDueOn']
            : $supply['next_due_on'];
        $status   = in_array($d['status'] ?? '', ['active', 'paused'], true) ? $d['status'] : $supply['status'];

        cms_run(
            'UPDATE cms_medicine_supply
             SET interval_days = ?, next_due_on = ?, status = ?, notes = ?, updated_at = NOW()
             WHERE case_id = ?',
            [$interval, $nextDue, $status, trim((string) ($d['notes'] ?? $supply['notes'] ?? '')), $caseId]
        );
    }

    /**
     * The board: every active supply joined to its patient, bucketed by how
     * far `next_due_on` is from today. Ordered soonest first.
     */
    public static function board(): array
    {
        $rows = cms_all(
            "SELECT s.*, c.code AS case_code, c.status AS case_status,
                    p.child_name, p.code AS patient_code, p.guardian_name, p.phone, p.city,
                    u.name AS case_doctor_name
             FROM cms_medicine_supply s
             JOIN cms_cases c ON c.id = s.case_id
             JOIN cms_patients p ON p.id = s.patient_id
             LEFT JOIN cms_users u ON u.id = c.case_doctor_id
             WHERE c.status = 'active' AND s.status = 'active'
             ORDER BY s.next_due_on ASC, p.child_name ASC"
        );

        $today = cms_today();
        $out = ['dueToday' => [], 'overdue' => [], 'upcoming' => [], 'later' => []];

        foreach ($rows as $row) {
            $shaped = self::shape($row);
            $days   = $shaped['daysUntilDue'];

            if ($days === 0) {
                $out['dueToday'][] = $shaped;
            } elseif ($days < 0) {
                $out['overdue'][] = $shaped;
            } elseif ($days <= 7) {
                $out['upcoming'][] = $shaped;
            } else {
                $out['later'][] = $shaped;
            }
        }

        // Overdue reads most-late first — that is who to chase.
        usort($out['overdue'], static fn($a, $b) => $a['daysUntilDue'] <=> $b['daysUntilDue']);

        $out['today'] = $today;
        return $out;
    }

    public static function countDueToday(): int
    {
        return (int) cms_scalar(
            "SELECT COUNT(*) FROM cms_medicine_supply s JOIN cms_cases c ON c.id = s.case_id
             WHERE c.status = 'active' AND s.status = 'active' AND s.next_due_on = CURDATE()"
        );
    }

    public static function countOverdue(): int
    {
        return (int) cms_scalar(
            "SELECT COUNT(*) FROM cms_medicine_supply s JOIN cms_cases c ON c.id = s.case_id
             WHERE c.status = 'active' AND s.status = 'active' AND s.next_due_on < CURDATE()"
        );
    }

    public static function countOutOfStock(): int
    {
        return (int) cms_scalar(
            "SELECT COUNT(*) FROM cms_medicine_supply s JOIN cms_cases c ON c.id = s.case_id
             WHERE c.status = 'active' AND s.status = 'active' AND s.stock_status = 'out_of_stock'"
        );
    }

    public static function countDeliveredThisMonth(): int
    {
        // Deferrals live in the same table but are not deliveries.
        return (int) cms_scalar(
            "SELECT COUNT(*) FROM cms_medicine_deliveries
             WHERE kind = 'delivered' AND delivered_on >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
    }

    public static function shape(array $s): array
    {
        $days = cms_days_between(cms_today(), $s['next_due_on']);

        if ($days === 0) {
            $state = 'due_today';
            $label = 'Due today';
        } elseif ($days < 0) {
            $state = 'overdue';
            $label = abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' overdue';
        } elseif ($days === 1) {
            $state = 'upcoming';
            $label = 'Due tomorrow';
        } else {
            $state = 'upcoming';
            $label = 'Due in ' . $days . ' days';
        }

        $outOfStock = ($s['stock_status'] ?? 'in_stock') === 'out_of_stock';

        return [
            'id'              => (int) $s['id'],
            'caseId'          => (int) $s['case_id'],
            'patientId'       => (int) $s['patient_id'],
            'intervalDays'    => (int) $s['interval_days'],
            'lastDeliveredOn' => $s['last_delivered_on'],
            'nextDueOn'       => $s['next_due_on'],
            'daysUntilDue'    => $days,
            'state'           => $state,
            'stateLabel'      => $label,
            'status'          => $s['status'],
            'stockStatus'     => $outOfStock ? 'out_of_stock' : 'in_stock',
            'isOutOfStock'    => $outOfStock,
            'outOfStockSince' => $s['out_of_stock_since'] ?? null,
            'stockNote'       => $s['stock_note'] ?? '',
            'notes'           => $s['notes'] ?? '',
            // Present when joined via board().
            'caseCode'        => $s['case_code'] ?? null,
            'patientName'     => $s['child_name'] ?? null,
            'patientCode'     => $s['patient_code'] ?? null,
            'guardianName'    => $s['guardian_name'] ?? null,
            'phone'           => $s['phone'] ?? null,
            'city'            => $s['city'] ?? null,
            'caseDoctorName'  => $s['case_doctor_name'] ?? null,
        ];
    }
}

class MedicineDelivery
{
    public static function forCase(int $caseId, int $limit = 50): array
    {
        $rows = cms_all(
            'SELECT d.*, u.name AS delivered_by_name
             FROM cms_medicine_deliveries d
             LEFT JOIN cms_users u ON u.id = d.delivered_by
             WHERE d.case_id = ?
             ORDER BY d.delivered_on DESC, d.id DESC
             LIMIT ' . (int) $limit,
            [$caseId]
        );
        return array_map([self::class, 'shape'], $rows);
    }

    /** Recent deliveries across every case, for the Medicine page footer. */
    public static function recent(int $limit = 12): array
    {
        $rows = cms_all(
            'SELECT d.*, u.name AS delivered_by_name, p.child_name, p.code AS patient_code
             FROM cms_medicine_deliveries d
             LEFT JOIN cms_users u ON u.id = d.delivered_by
             JOIN cms_patients p ON p.id = d.patient_id
             ORDER BY d.delivered_on DESC, d.id DESC
             LIMIT ' . (int) $limit
        );
        return array_map(static function (array $r): array {
            $shaped = self::shape($r);
            $shaped['patientName'] = $r['child_name'];
            $shaped['patientCode'] = $r['patient_code'];
            return $shaped;
        }, $rows);
    }

    public static function shape(array $d): array
    {
        $kind = $d['kind'] ?? 'delivered';
        $deferred = $kind === 'deferred_out_of_stock';

        return [
            'id'              => (int) $d['id'],
            'caseId'          => (int) $d['case_id'],
            'patientId'       => (int) $d['patient_id'],
            'kind'            => $kind,
            'isDeferral'      => $deferred,
            'kindLabel'       => $deferred ? 'Out of stock — deferred' : 'Delivered',
            'deliveredOn'     => $d['delivered_on'],
            'nextDueOn'       => $d['next_due_on'],
            'mode'            => $d['mode'],
            // A deferral has no delivery mode; showing "Other" would read as noise.
            'modeLabel'       => $deferred ? '' : ([
                'courier' => 'Courier',
                'hand'    => 'Hand delivery',
                'pickup'  => 'Clinic pickup',
                'other'   => 'Other',
            ][$d['mode']] ?? $d['mode']),
            'reference'       => $d['reference'] ?? '',
            'notes'           => $d['notes'] ?? '',
            'deliveredByName' => $d['delivered_by_name'] ?? '',
            'createdAt'       => $d['created_at'],
        ];
    }
}
