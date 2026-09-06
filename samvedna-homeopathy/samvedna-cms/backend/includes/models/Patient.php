<?php
/**
 * Patients and their mandatory baseline.
 *
 * The baseline completeness rule lives here (Baseline::evaluate) and is the one
 * gate that blocks case activation — the API, the intake screen and the case
 * workspace all read the same verdict.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class Patient
{
    /** Listing with search + filters, joined to the active case for context. */
    public static function all(array $filters = []): array
    {
        $sql = "SELECT p.*,
                       b.condition_type, b.severity, b.is_complete,
                       c.id AS case_id, c.code AS case_code, c.current_cycle, c.total_cycles,
                       c.status AS case_status, c.start_date, c.end_date,
                       pl.name AS plan_name, pl.code AS plan_code,
                       cd.name AS case_doctor_name
                FROM cms_patients p
                LEFT JOIN cms_baselines b ON b.patient_id = p.id
                LEFT JOIN cms_cases c ON c.patient_id = p.id AND c.status IN ('active','paused')
                LEFT JOIN cms_plans pl ON pl.id = c.plan_id
                LEFT JOIN cms_users cd ON cd.id = c.case_doctor_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= ' AND (p.child_name LIKE ? OR p.guardian_name LIKE ? OR p.code LIKE ? OR p.phone LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['condition'])) {
            $sql .= ' AND b.condition_type = ?';
            $params[] = $filters['condition'];
        }
        if (!empty($filters['severity'])) {
            $sql .= ' AND b.severity = ?';
            $params[] = $filters['severity'];
        }
        if (!empty($filters['doctorId'])) {
            $sql .= ' AND c.case_doctor_id = ?';
            $params[] = (int) $filters['doctorId'];
        }

        $sql .= ' ORDER BY p.created_at DESC, p.id DESC';

        return array_map([self::class, 'shapeListRow'], cms_all($sql, $params));
    }

    public static function find(int $id): ?array
    {
        return cms_one('SELECT * FROM cms_patients WHERE id = ? LIMIT 1', [$id]);
    }

    public static function create(array $d, ?int $userId): int
    {
        $code = cms_next_code('cms_patients', 'SAM');
        return cms_insert(
            'INSERT INTO cms_patients
                (code, child_name, dob, gender, guardian_name, guardian_relation, phone, alt_phone,
                 email, city, state, country, referral_source, notes, status, created_by, created_at, updated_at)
             VALUES
                (:code, :child_name, :dob, :gender, :guardian_name, :guardian_relation, :phone, :alt_phone,
                 :email, :city, :state, :country, :referral_source, :notes, :status, :created_by, NOW(), NOW())',
            self::bind($d) + ['code' => $code, 'created_by' => $userId]
        );
    }

    public static function update(int $id, array $d): void
    {
        $params = self::bind($d);
        $params['id'] = $id;
        cms_run(
            'UPDATE cms_patients SET
                child_name = :child_name, dob = :dob, gender = :gender,
                guardian_name = :guardian_name, guardian_relation = :guardian_relation,
                phone = :phone, alt_phone = :alt_phone, email = :email,
                city = :city, state = :state, country = :country,
                referral_source = :referral_source, notes = :notes, status = :status,
                updated_at = NOW()
             WHERE id = :id',
            $params
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        cms_run('UPDATE cms_patients SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
    }

    public static function delete(int $id): void
    {
        // Only draft patients are deletable (guarded in the handler). Cascade the
        // baseline by hand — the schema stays FK-free so it can run on shared hosts
        // where InnoDB constraints are sometimes disabled.
        cms_run('DELETE FROM cms_baselines WHERE patient_id = ?', [$id]);
        cms_run('DELETE FROM cms_patients WHERE id = ?', [$id]);
    }

    /** Row -> API shape. */
    public static function shape(array $p): array
    {
        return [
            'id'               => (int) $p['id'],
            'code'             => $p['code'],
            'childName'        => $p['child_name'],
            'dob'              => $p['dob'],
            'age'              => cms_age($p['dob'] ?? null),
            'gender'           => $p['gender'],
            'guardianName'     => $p['guardian_name'] ?? '',
            'guardianRelation' => $p['guardian_relation'] ?? '',
            'phone'            => $p['phone'] ?? '',
            'altPhone'         => $p['alt_phone'] ?? '',
            'email'            => $p['email'] ?? '',
            'city'             => $p['city'] ?? '',
            'state'            => $p['state'] ?? '',
            'country'          => $p['country'] ?? '',
            'referralSource'   => $p['referral_source'] ?? '',
            'notes'            => $p['notes'] ?? '',
            'status'           => $p['status'],
            'createdAt'        => $p['created_at'] ?? null,
        ];
    }

    private static function shapeListRow(array $p): array
    {
        $shaped = self::shape($p);
        $shaped['condition']       = $p['condition_type'] ?? '';
        $shaped['severity']        = $p['severity'] ?? '';
        $shaped['baselineComplete'] = (bool) ($p['is_complete'] ?? 0);
        $shaped['caseId']          = $p['case_id'] ? (int) $p['case_id'] : null;
        $shaped['caseCode']        = $p['case_code'] ?? '';
        $shaped['caseStatus']      = $p['case_status'] ?? '';
        $shaped['planName']        = $p['plan_name'] ?? '';
        $shaped['planCode']        = $p['plan_code'] ?? '';
        $shaped['caseDoctorName']  = $p['case_doctor_name'] ?? '';
        $shaped['startDate']       = $p['start_date'] ?? null;
        $shaped['endDate']         = $p['end_date'] ?? null;
        $shaped['cycleLabel']      = $p['current_cycle']
            ? 'Cycle ' . (int) $p['current_cycle'] . ' of ' . (int) $p['total_cycles']
            : '';
        return $shaped;
    }

    private static function bind(array $d): array
    {
        $dob = $d['dob'] ?? '';
        return [
            'child_name'        => $d['childName'] ?? '',
            'dob'               => ($dob !== '' && cms_is_date($dob)) ? $dob : null,
            'gender'            => in_array($d['gender'] ?? '', ['male', 'female', 'other'], true) ? $d['gender'] : 'unspecified',
            'guardian_name'     => $d['guardianName'] ?? '',
            'guardian_relation' => $d['guardianRelation'] ?? '',
            'phone'             => $d['phone'] ?? '',
            'alt_phone'         => $d['altPhone'] ?? '',
            'email'             => $d['email'] ?? '',
            'city'              => $d['city'] ?? '',
            'state'             => $d['state'] ?? '',
            'country'           => $d['country'] ?? 'India',
            'referral_source'   => $d['referralSource'] ?? '',
            'notes'             => $d['notes'] ?? '',
            'status'            => in_array($d['status'] ?? '', ['draft', 'active', 'on_hold', 'completed', 'discharged'], true)
                ? $d['status'] : 'draft',
        ];
    }
}

class Baseline
{
    public static function forPatient(int $patientId): ?array
    {
        return cms_one('SELECT * FROM cms_baselines WHERE patient_id = ? LIMIT 1', [$patientId]);
    }

    /**
     * Insert or update the baseline, recomputing completeness on every write.
     * Returns the shaped baseline.
     */
    public static function save(int $patientId, array $d): array
    {
        $concerns = array_values(array_filter(
            array_map(static fn($c) => is_string($c) ? trim($c) : '', $d['concerns'] ?? []),
            static fn($c) => $c !== ''
        ));
        $markers = self::normaliseMarkers($d['markers'] ?? []);

        $payload = [
            'condition_type'      => trim((string) ($d['conditionType'] ?? '')),
            'severity'            => in_array($d['severity'] ?? '', ['mild', 'moderate', 'severe'], true) ? $d['severity'] : '',
            'diagnosis_age'       => trim((string) ($d['diagnosisAge'] ?? '')),
            'medical_history'     => trim((string) ($d['medicalHistory'] ?? '')),
            'therapy_involvement' => trim((string) ($d['therapyInvolvement'] ?? '')),
            'concerns'            => cms_json_put($concerns),
            'markers'             => cms_json_put($markers),
        ];

        $verdict = self::evaluate($payload['condition_type'], $payload['severity'], $payload['therapy_involvement'], $concerns, $markers);
        $payload['is_complete'] = $verdict['complete'] ? 1 : 0;

        $existing = self::forPatient($patientId);
        if ($existing) {
            $payload['id'] = (int) $existing['id'];
            cms_run(
                'UPDATE cms_baselines SET
                    condition_type = :condition_type, severity = :severity, diagnosis_age = :diagnosis_age,
                    medical_history = :medical_history, therapy_involvement = :therapy_involvement,
                    concerns = :concerns, markers = :markers, is_complete = :is_complete,
                    completed_at = CASE WHEN :is_complete2 = 1 AND completed_at IS NULL THEN NOW() ELSE completed_at END,
                    updated_at = NOW()
                 WHERE id = :id',
                $payload + ['is_complete2' => $payload['is_complete']]
            );
        } else {
            $payload['patient_id'] = $patientId;
            cms_insert(
                'INSERT INTO cms_baselines
                    (patient_id, condition_type, severity, diagnosis_age, medical_history,
                     therapy_involvement, concerns, markers, is_complete, completed_at, created_at, updated_at)
                 VALUES
                    (:patient_id, :condition_type, :severity, :diagnosis_age, :medical_history,
                     :therapy_involvement, :concerns, :markers, :is_complete,
                     CASE WHEN :is_complete2 = 1 THEN NOW() ELSE NULL END, NOW(), NOW())',
                $payload + ['is_complete2' => $payload['is_complete']]
            );
        }

        return self::shape(self::forPatient($patientId) ?? []);
    }

    /**
     * The activation gate. Every rule the flow marks "mandatory" is checked here
     * and returned as a per-field message list so the UI can point at the box.
     */
    public static function evaluate(
        string $conditionType,
        string $severity,
        string $therapy,
        array $concerns,
        array $markers
    ): array {
        $missing = [];

        if ($conditionType === '') {
            $missing['conditionType'] = 'Condition classification is required.';
        }
        if ($severity === '') {
            $missing['severity'] = 'Severity level is required.';
        }
        if ($therapy === '') {
            $missing['therapyInvolvement'] = 'Record current therapy involvement (write "None" if there is none).';
        }
        if (count($concerns) < 3) {
            $missing['concerns'] = 'All three parent concerns are required.';
        }

        $rated = 0;
        $byKey = [];
        foreach ($markers as $m) {
            if (!empty($m['rating'])) {
                $rated++;
                $byKey[$m['key']] = true;
            }
        }
        $catalogue = cms_marker_catalogue();
        if ($rated < count($catalogue)) {
            $unrated = array_values(array_filter(
                array_map(static fn($m) => isset($byKey[$m['key']]) ? null : $m['label'], $catalogue)
            ));
            $missing['markers'] = 'Rate every functional marker. Still open: ' . implode(', ', $unrated) . '.';
        }

        $totalChecks = 5;
        $done = $totalChecks - count($missing);

        return [
            'complete'   => count($missing) === 0,
            'missing'    => $missing,
            'percent'    => (int) round(($done / $totalChecks) * 100),
            'ratedCount' => $rated,
            'totalCount' => count($catalogue),
        ];
    }

    /** Re-run the verdict against a stored row. */
    public static function verdictFor(?array $row): array
    {
        if (!$row) {
            return [
                'complete' => false,
                'missing'  => ['conditionType' => 'Baseline has not been started.'],
                'percent'  => 0,
                'ratedCount' => 0,
                'totalCount' => count(cms_marker_catalogue()),
            ];
        }
        return self::evaluate(
            (string) ($row['condition_type'] ?? ''),
            (string) ($row['severity'] ?? ''),
            (string) ($row['therapy_involvement'] ?? ''),
            cms_json_col($row['concerns'] ?? ''),
            cms_json_col($row['markers'] ?? '')
        );
    }

    public static function shape(array $b): array
    {
        if (!$b) {
            return [
                'conditionType'      => '',
                'severity'           => '',
                'diagnosisAge'       => '',
                'medicalHistory'     => '',
                'therapyInvolvement' => '',
                'concerns'           => ['', '', ''],
                'markers'            => self::normaliseMarkers([]),
                'isComplete'         => false,
                'completedAt'        => null,
            ];
        }
        $concerns = cms_json_col($b['concerns'] ?? '');
        while (count($concerns) < 3) {
            $concerns[] = '';
        }
        return [
            'conditionType'      => $b['condition_type'] ?? '',
            'severity'           => $b['severity'] ?? '',
            'diagnosisAge'       => $b['diagnosis_age'] ?? '',
            'medicalHistory'     => $b['medical_history'] ?? '',
            'therapyInvolvement' => $b['therapy_involvement'] ?? '',
            'concerns'           => array_slice($concerns, 0, 3),
            'markers'            => self::normaliseMarkers(cms_json_col($b['markers'] ?? '')),
            'isComplete'         => (bool) ($b['is_complete'] ?? 0),
            'completedAt'        => $b['completed_at'] ?? null,
        ];
    }

    /**
     * Always return the full marker catalogue in catalogue order, merged with
     * whatever ratings exist. The intake checklist and the progress dashboard
     * then never have to worry about a marker being absent from the JSON.
     */
    public static function normaliseMarkers(array $input): array
    {
        $byKey = [];
        foreach ($input as $m) {
            if (is_array($m) && !empty($m['key'])) {
                $byKey[$m['key']] = (string) ($m['rating'] ?? '');
            }
        }
        return array_map(static function (array $m) use ($byKey): array {
            $rating = $byKey[$m['key']] ?? '';
            return [
                'key'    => $m['key'],
                'label'  => $m['label'],
                'rating' => $rating,
                'score'  => $rating !== '' ? cms_rating_score($rating) : 0,
            ];
        }, cms_marker_catalogue());
    }
}
