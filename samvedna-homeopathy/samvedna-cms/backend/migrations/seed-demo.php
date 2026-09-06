<?php
/**
 * Demo content for the client walkthrough.
 *
 * Every patient here exists to make one screen of the approved page flow real,
 * and the dates are anchored to *today* rather than hard-coded, so the demo is
 * always live no matter when it is shown:
 *
 *   Aarav Mehta    Standard, day 60  -> cycle-1 review sitting in "awaiting Senior sign-off" (steps 1-4)
 *   Ishaan Verma   Standard, day 130 -> cycle 1 closed with a generated progress dashboard (step 6)
 *   Diya Kulkarni  Premium,  day 40  -> an open escalation inside its 7-day SLA (step 5)
 *   Kabir Shah     draft             -> an incomplete baseline, activation blocked (step 1 lock)
 *   Aanya Nair     Starter,  day 20  -> a straightforward young case
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/models/User.php';
require_once __DIR__ . '/../includes/models/Plan.php';
require_once __DIR__ . '/../includes/models/Patient.php';
require_once __DIR__ . '/../includes/models/CareCase.php';
require_once __DIR__ . '/../includes/models/ScheduleEvent.php';
require_once __DIR__ . '/../includes/models/Review.php';
require_once __DIR__ . '/../includes/models/Escalation.php';
require_once __DIR__ . '/../includes/models/ProgressReport.php';
require_once __DIR__ . '/../includes/services/ScheduleGenerator.php';
require_once __DIR__ . '/../includes/services/ProgressBuilder.php';
require_once __DIR__ . '/../includes/models/Medicine.php';
require_once __DIR__ . '/../includes/models/Notification.php';

function cms_seed_demo(PDO $pdo): void
{
    out('');
    out('Seeding demo walkthrough…');

    if ((int) cms_scalar('SELECT COUNT(*) FROM cms_patients') > 0) {
        out('  Patients already exist — skipping demo seed. Use --fresh to rebuild.');
        return;
    }

    $staff = [];
    foreach (cms_all('SELECT id, username FROM cms_users') as $u) {
        $staff[$u['username']] = (int) $u['id'];
    }

    $founder = $staff['founder'] ?? null;
    $senior  = $staff['senior'] ?? null;
    $anjali  = $staff['casedoctor'] ?? null;
    $meera   = $staff['casedoctor2'] ?? null;

    $starter  = Plan::findByCode('starter');
    $standard = Plan::findByCode('standard');
    $premium  = Plan::findByCode('premium');

    /* ---------------------------------------------------------- Aarav ----- */
    // The flow's own case. Day 60: the cycle-1 review is written, the Case Doctor
    // has signed, and the Senior Doctor's sign-off is the thing standing between
    // it and the progress dashboard.
    $aarav = cms_seed_patient([
        'childName' => 'Aarav Mehta', 'dob' => cms_add_months(cms_today(), -78),
        'gender' => 'male', 'guardianName' => 'Priya Mehta', 'guardianRelation' => 'Mother',
        'phone' => '+91 98765 43210', 'email' => 'priya.mehta@example.com',
        'city' => 'Ahmedabad', 'state' => 'Gujarat', 'referralSource' => 'Google search',
    ], [
        'conditionType' => 'Autism Spectrum Disorder',
        'severity' => 'moderate',
        'diagnosisAge' => '3 years 2 months',
        'medicalHistory' => 'Full-term birth. Recurrent ear infections until age 4. No seizure history.',
        'therapyInvolvement' => 'Speech therapy (2×/week), Occupational therapy (1×/week)',
        'concerns' => ['Limited verbal communication', 'Sensory meltdowns in public', 'Sleep disturbances'],
        'markers' => [
            'eye_contact' => 'minimal', 'social_reciprocity' => 'poor', 'repetitive' => 'frequent',
            'verbal' => 'minimal', 'sensory' => 'poor', 'sleep' => 'poor',
            'fine_motor' => 'emerging', 'attention' => 'poor', 'self_help' => 'emerging',
        ],
    ], $anjali);

    $aaravCase = cms_seed_case($aarav, $standard, cms_add_days(cms_today(), -60), [
        'caseDoctorId' => $anjali, 'seniorDoctorId' => $senior, 'founderId' => $founder,
    ]);
    cms_seed_complete_past_events($aaravCase, 'full');

    $aaravReview = cms_seed_review($aaravCase, 1, [
        'improvements'      => 'Better eye contact during meals and structured play. Meltdown frequency down from 4/week to 2/week. Responds to name in 6 of 10 attempts, up from 2.',
        'stagnation'        => 'Verbal output still limited to single words. Sleep cycle remains inconsistent — settles past midnight on 4 of 7 nights.',
        'protocolDecision'  => 'continued_adjusted',
        'protocolRationale' => 'Increase Carcinosin 200 frequency from fortnightly to weekly — the response after week 5 was clearly positive and the sensory picture supports a higher repetition.',
        'therapyNotes'      => 'Spoke with the speech therapist — aligning on a verbal prompting strategy and reducing prompt dependence. OT to add a 20-minute evening sensory diet to support sleep onset.',
        'adherencePercent'  => 100,
        'goals' => [
            ['title' => 'Improve verbal initiation', 'metric' => '5+ self-initiated phrases per day', 'status' => 'progressing'],
            ['title' => 'Consistent 8-hour sleep', 'metric' => '5 of 7 nights', 'status' => 'progressing'],
            ['title' => 'Reduce school meltdowns', 'metric' => 'Zero meltdowns in the final 2 weeks', 'status' => 'pending'],
        ],
    ], $anjali, 'awaiting_signoff');

    // The Case Doctor has signed; the Senior Doctor has not. That pending row is
    // what renders the "Review cannot be closed until Senior Doctor signs off" lock.
    ReviewSignoff::sign($aaravReview, (int) $anjali, 'Submitted by author');
    out('  Aarav Mehta — Standard plan, cycle 1 review awaiting Senior Doctor sign-off.');

    /* --------------------------------------------------------- Ishaan ----- */
    // Far enough along that cycle 1 is closed and its dashboard exists.
    $ishaan = cms_seed_patient([
        'childName' => 'Ishaan Verma', 'dob' => cms_add_months(cms_today(), -92),
        'gender' => 'male', 'guardianName' => 'Rohit Verma', 'guardianRelation' => 'Father',
        'phone' => '+91 99201 55678', 'email' => 'rohit.verma@example.com',
        'city' => 'Pune', 'state' => 'Maharashtra', 'referralSource' => 'Parent referral',
    ], [
        'conditionType' => 'ADHD',
        'severity' => 'moderate',
        'diagnosisAge' => '5 years',
        'medicalHistory' => 'No significant illness. Family history of attention difficulties.',
        'therapyInvolvement' => 'Behavioural therapy (1×/week), school shadow support',
        'concerns' => ['Cannot sit through a class period', 'Impulsive with siblings', 'Homework refusal'],
        'markers' => [
            'eye_contact' => 'good', 'social_reciprocity' => 'moderate', 'repetitive' => 'minimal',
            'verbal' => 'good', 'sensory' => 'moderate', 'sleep' => 'moderate',
            'fine_motor' => 'moderate', 'attention' => 'poor', 'self_help' => 'moderate',
        ],
    ], $meera);

    $ishaanCase = cms_seed_case($ishaan, $standard, cms_add_days(cms_today(), -130), [
        'caseDoctorId' => $meera, 'seniorDoctorId' => $senior, 'founderId' => $founder,
    ]);
    cms_seed_complete_past_events($ishaanCase, 'full');

    $ishaanReview = cms_seed_review($ishaanCase, 1, [
        'improvements'      => 'Attention span in structured tasks up from 4 to 11 minutes. Sitting through a full 30-minute class period on 3 of 5 school days.',
        'stagnation'        => 'Impulsivity with siblings unchanged. Homework refusal persists on days with a late school finish.',
        'protocolDecision'  => 'continued',
        'protocolRationale' => '',
        'therapyNotes'      => 'Behavioural therapist reports better carry-over at home. Agreed to keep the same reward schedule for one more cycle before adjusting.',
        'adherencePercent'  => 92,
        'goals' => [
            ['title' => 'Sustain 20 minutes of seated work', 'metric' => '4 of 5 school days', 'status' => 'achieved'],
            ['title' => 'Complete homework without refusal', 'metric' => '4 of 5 weekdays', 'status' => 'progressing'],
            ['title' => 'Reduce sibling conflict incidents', 'metric' => 'Under 2 per week', 'status' => 'pending'],
        ],
    ], $meera, 'awaiting_signoff');

    // Close it properly through the same path the API uses, so the progress
    // report and the cycle advance are generated rather than hand-written.
    ReviewSignoff::sign($ishaanReview, (int) $meera, 'Submitted by author');
    ReviewSignoff::sign($ishaanReview, (int) $senior, 'Reviewed — agree with continuing the current protocol.');
    cms_seed_close_review($ishaanReview);
    out('  Ishaan Verma — cycle 1 closed, progress dashboard generated.');

    /* ----------------------------------------------------------- Diya ----- */
    $diya = cms_seed_patient([
        'childName' => 'Diya Kulkarni', 'dob' => cms_add_months(cms_today(), -66),
        'gender' => 'female', 'guardianName' => 'Sneha Kulkarni', 'guardianRelation' => 'Mother',
        'phone' => '+91 98220 11223', 'email' => 'sneha.k@example.com',
        'city' => 'Nagpur', 'state' => 'Maharashtra', 'referralSource' => 'Instagram',
    ], [
        'conditionType' => 'Global Developmental Delay',
        'severity' => 'severe',
        'diagnosisAge' => '2 years 6 months',
        'medicalHistory' => 'Pre-term at 33 weeks, NICU for 18 days. Hypotonia noted at 8 months.',
        'therapyInvolvement' => 'Physiotherapy (3×/week), Speech therapy (2×/week), Special education (daily)',
        'concerns' => ['No functional speech', 'Frequent choking while eating', 'Poor sitting balance'],
        'markers' => [
            'eye_contact' => 'minimal', 'social_reciprocity' => 'minimal', 'repetitive' => 'moderate',
            'verbal' => 'absent', 'sensory' => 'poor', 'sleep' => 'moderate',
            'fine_motor' => 'absent', 'attention' => 'minimal', 'self_help' => 'absent',
        ],
    ], $anjali);

    $diyaCase = cms_seed_case($diya, $premium, cms_add_days(cms_today(), -40), [
        'caseDoctorId' => $anjali, 'seniorDoctorId' => $senior, 'founderId' => $founder,
    ]);
    cms_seed_complete_past_events($diyaCase, 'partial');

    $escalationId = Escalation::create([
        'caseId'   => $diyaCase,
        'reviewId' => null,
        'raisedBy' => $anjali,
        'reasons'  => ['mixed_signals', 'parent_anxiety'],
        'notes'    => 'Parent reporting regression in feeding tolerance over the last three weeks while motor milestones continue to improve. Needs reassessment of constitution before cycle 2 begins, and a structured counselling session for the mother.',
        'seniorDoctorId' => $senior,
        'founderId'      => $founder,
    ]);
    Escalation::setStatus($escalationId, 'in_progress');
    out('  Diya Kulkarni — Premium plan with an open escalation inside its 7-day SLA.');

    /* ---------------------------------------------------------- Kabir ----- */
    // Deliberately incomplete: no severity, only two concerns, four markers
    // unrated. Opening this patient shows the activation lock exactly as the
    // flow describes it.
    cms_seed_patient([
        'childName' => 'Kabir Shah', 'dob' => cms_add_months(cms_today(), -54),
        'gender' => 'male', 'guardianName' => 'Ritu Shah', 'guardianRelation' => 'Mother',
        'phone' => '+91 90333 78901', 'email' => 'ritu.shah@example.com',
        'city' => 'Surat', 'state' => 'Gujarat', 'referralSource' => 'Walk-in',
    ], [
        'conditionType' => 'Speech Delay',
        'severity' => '',
        'diagnosisAge' => '',
        'medicalHistory' => '',
        'therapyInvolvement' => '',
        'concerns' => ['Says fewer than 10 words', 'Does not respond to name'],
        'markers' => [
            'eye_contact' => 'moderate', 'social_reciprocity' => 'moderate',
            'repetitive' => 'minimal', 'verbal' => 'minimal', 'sensory' => 'good',
        ],
    ], $anjali);
    out('  Kabir Shah — draft intake, baseline incomplete (activation blocked).');

    /* ---------------------------------------------------------- Aanya ----- */
    $aanya = cms_seed_patient([
        'childName' => 'Aanya Nair', 'dob' => cms_add_months(cms_today(), -47),
        'gender' => 'female', 'guardianName' => 'Deepak Nair', 'guardianRelation' => 'Father',
        'phone' => '+91 97400 22110', 'email' => 'deepak.nair@example.com',
        'city' => 'Kochi', 'state' => 'Kerala', 'referralSource' => 'YouTube',
    ], [
        'conditionType' => 'Sensory Processing Disorder',
        'severity' => 'mild',
        'diagnosisAge' => '3 years 8 months',
        'medicalHistory' => 'Unremarkable. Mild eczema managed topically.',
        'therapyInvolvement' => 'Occupational therapy (2×/week)',
        'concerns' => ['Distressed by loud sounds', 'Refuses most food textures', 'Struggles with clothing tags'],
        'markers' => [
            'eye_contact' => 'good', 'social_reciprocity' => 'good', 'repetitive' => 'minimal',
            'verbal' => 'good', 'sensory' => 'poor', 'sleep' => 'good',
            'fine_motor' => 'good', 'attention' => 'moderate', 'self_help' => 'moderate',
        ],
    ], $meera);

    $aanyaCase = cms_seed_case($aanya, $starter, cms_add_days(cms_today(), -20), [
        'caseDoctorId' => $meera, 'seniorDoctorId' => null, 'founderId' => null,
    ]);
    cms_seed_complete_past_events($aanyaCase, 'full');
    out('  Aanya Nair — Starter plan, first cycle underway.');

    /* ------------------------------------------------------- medicine ----- */
    // Each case is placed at a different point in its 15-day cycle so the
    // Medicine page shows every state at once: Aarav is due TODAY (the morning
    // notification fires), Diya is two days overdue, Ishaan is due in three
    // days, Aanya has a fresh supply.
    cms_seed_medicine($aaravCase,  $anjali, cms_add_days(cms_today(), -15), 3);
    cms_seed_medicine($diyaCase,   $anjali, cms_add_days(cms_today(), -17), 1);
    cms_seed_medicine($ishaanCase, $meera,  cms_add_days(cms_today(), -12), 7);
    cms_seed_medicine($aanyaCase,  $meera,  cms_add_days(cms_today(), -5),  1);

    // Diya's remedy is unavailable: nothing ships, the date slips 5 days, and a
    // notice explains why. Run this BEFORE generateDaily so her "deliver today"
    // notice is superseded rather than created and then retired.
    $diyaDefer = MedicineSupply::markOutOfStock($diyaCase, [
        'deferDays'  => MedicineSupply::DEFAULT_OUT_OF_STOCK_DEFER,
        'notes'      => 'Carcinosin 200 out of stock with the supplier; restock expected mid-week.',
        'recordedBy' => $anjali,
    ]);
    Notification::medicineOutOfStock(
        $diyaCase,
        (int) cms_scalar('SELECT patient_id FROM cms_cases WHERE id = ?', [$diyaCase]),
        'Diya Kulkarni',
        $diyaDefer['nextDueOn'],
        $diyaDefer['deferDays']
    );
    Notification::generateDaily();
    out('  Medicine cycles seeded — Aarav due today, Diya out of stock (+5 days), Ishaan due in 3.');

    /* ------------------------------------------------------- activity ----- */
    cms_seed_activity();

    ScheduleEvent::refreshStatuses();
    out('Demo seed complete.');
}

/* -------------------------------------------------------------- helpers -- */

/** Create a patient plus baseline. Returns the patient id. */
function cms_seed_patient(array $profile, array $baseline, ?int $createdBy): int
{
    $id = Patient::create($profile + ['status' => 'draft'], $createdBy);

    Baseline::save($id, [
        'conditionType'      => $baseline['conditionType'],
        'severity'           => $baseline['severity'],
        'diagnosisAge'       => $baseline['diagnosisAge'],
        'medicalHistory'     => $baseline['medicalHistory'],
        'therapyInvolvement' => $baseline['therapyInvolvement'],
        'concerns'           => $baseline['concerns'],
        'markers'            => array_map(
            static fn(string $key, string $rating): array => ['key' => $key, 'rating' => $rating],
            array_keys($baseline['markers']),
            array_values($baseline['markers'])
        ),
    ]);

    return $id;
}

/** Activate a case and build its schedule. Returns the case id. */
function cms_seed_case(int $patientId, array $plan, string $startDate, array $roles): int
{
    $cycles = ScheduleGenerator::cycleCount($plan);

    $caseId = CareCase::create([
        'patientId'      => $patientId,
        'planId'         => (int) $plan['id'],
        'caseDoctorId'   => $roles['caseDoctorId'] ?? null,
        'seniorDoctorId' => $roles['seniorDoctorId'] ?? null,
        'founderId'      => $roles['founderId'] ?? null,
        'startDate'      => $startDate,
        'endDate'        => cms_add_months($startDate, (int) $plan['durationMonths']),
        'totalCycles'    => $cycles,
        'activatedBy'    => $roles['caseDoctorId'] ?? null,
    ]);

    ScheduleGenerator::build($caseId, $plan, $startDate, $roles);
    Patient::setStatus($patientId, 'active');

    return $caseId;
}

/**
 * Mark every adherence touchpoint that is already in the past as done, with a
 * recorded compliance level — otherwise a freshly seeded case looks like a month
 * of missed appointments.
 */
function cms_seed_complete_past_events(int $caseId, string $compliance): void
{
    $case = CareCase::find($caseId);
    $events = cms_all(
        "SELECT * FROM cms_schedule_events
         WHERE case_id = ? AND type = 'adherence' AND due_date < CURDATE()
         ORDER BY due_date",
        [$caseId]
    );

    foreach ($events as $i => $event) {
        AdherenceCheck::record([
            'eventId'       => (int) $event['id'],
            'caseId'        => $caseId,
            'cycle'         => (int) $event['cycle'],
            // Vary it a little so the dashboard average is not a flat 100%.
            'compliance'    => ($i > 0 && $i % 4 === 0) ? 'partial' : $compliance,
            'refill'        => ($i % 3 === 0) ? 'due' : 'stocked',
            'parentConcern' => $i % 5 === 0,
            'parentNote'    => $i % 5 === 0
                ? 'Parent flagged a disturbed week of sleep; reassured and asked to log bedtimes.'
                : 'Medicine compliance confirmed. No new concerns.',
            'recordedBy'    => $case['case_doctor_id'] ? (int) $case['case_doctor_id'] : null,
        ]);
        ScheduleEvent::complete((int) $event['id'], 'Non-consult check completed.');
    }
}

/** Create and fill a cycle review. Returns the review id. */
function cms_seed_review(int $caseId, int $cycle, array $body, ?int $author, string $status): int
{
    $case = CareCase::find($caseId);
    $plan = Plan::find((int) $case['plan_id']) ?? [];

    $event = cms_one(
        "SELECT * FROM cms_schedule_events WHERE case_id = ? AND cycle = ? AND type = 'review' LIMIT 1",
        [$caseId, $cycle]
    );
    $reviewDate = $event['due_date'] ?? cms_today();

    $reviewId = Review::create([
        'caseId'         => $caseId,
        'eventId'        => $event ? (int) $event['id'] : null,
        'cycle'          => $cycle,
        'reviewDate'     => $reviewDate,
        'nextReviewDate' => cms_add_days($reviewDate, (int) ($plan['reviewIntervalDays'] ?? 60)),
        'status'         => $status,
        'createdBy'      => $author,
    ]);

    $body['completeness'] = 100;
    Review::saveBody($reviewId, $body);
    ReviewGoal::replace($reviewId, $caseId, $cycle, $body['goals'] ?? []);
    ReviewSignoff::seed($reviewId, $case, $plan);

    return $reviewId;
}

/** Close a fully signed review the same way the API does. */
function cms_seed_close_review(int $reviewId): void
{
    $review = Review::find($reviewId);
    $case   = CareCase::find((int) $review['case_id']);

    Review::close($reviewId, true, 4.8);
    $review = Review::find($reviewId);

    ProgressBuilder::build($case, $review);

    if ($review['event_id']) {
        ScheduleEvent::complete((int) $review['event_id'], 'Formal review closed.');
    }
    CareCase::advanceCycle((int) $case['id']);
}

/**
 * Seed a medicine cycle with a realistic delivery history: `$priorDeliveries`
 * fortnightly deliveries ending on `$lastDeliveredOn`, so the history tab has
 * rows and the next-due date lands where the demo needs it.
 */
function cms_seed_medicine(int $caseId, ?int $by, string $lastDeliveredOn, int $priorDeliveries): void
{
    $case = CareCase::find($caseId);
    $interval = MedicineSupply::DEFAULT_INTERVAL;

    // Walk backwards so the log reads oldest -> newest and each entry's
    // next_due_on is the following entry's delivered_on.
    for ($i = $priorDeliveries - 1; $i >= 0; $i--) {
        $deliveredOn = cms_add_days($lastDeliveredOn, -$interval * $i);
        cms_run(
            'INSERT INTO cms_medicine_deliveries
                (case_id, patient_id, delivered_on, next_due_on, mode, reference, notes, delivered_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $caseId,
                (int) $case['patient_id'],
                $deliveredOn,
                cms_add_days($deliveredOn, $interval),
                $i % 2 === 0 ? 'courier' : 'hand',
                $i % 2 === 0 ? 'DTDC-' . str_pad((string) (48210 + $caseId * 7 + $i), 6, '0', STR_PAD_LEFT) : '',
                $i === 0 ? 'Fortnightly supply. Parent confirmed receipt.' : '',
                $by,
                $deliveredOn . ' 10:30:00',
            ]
        );
    }

    MedicineSupply::start($caseId, (int) $case['patient_id'], $lastDeliveredOn, $interval);
}

/** A short audit trail so the activity feed is not empty on first load. */
function cms_seed_activity(): void
{
    $entries = [
        ['Dr. Anjali Rao', 'patient', 'created', 'Registered Aarav Mehta'],
        ['Dr. Anjali Rao', 'case', 'activated', 'Aarav Mehta activated on the Standard plan'],
        ['Dr. Meera Iyer', 'case', 'activated', 'Ishaan Verma activated on the Standard plan'],
        ['Dr. Meera Iyer', 'review', 'submitted', 'Cycle 1 review awaiting sign-off'],
        ['Dr. Suresh Nair', 'review', 'signed', 'Senior Doctor signed off cycle 1'],
        ['Dr. Meera Iyer', 'review', 'closed', 'Cycle 1 review closed'],
        ['Dr. Anjali Rao', 'escalation', 'raised', 'Escalation raised on Diya Kulkarni'],
        ['Dr. Anjali Rao', 'review', 'submitted', 'Cycle 1 review awaiting sign-off'],
    ];

    foreach ($entries as $i => [$actor, $entity, $action, $summary]) {
        cms_run(
            'INSERT INTO cms_activity_log (user_id, actor_name, entity_type, entity_id, action, summary, created_at)
             VALUES (NULL, ?, ?, NULL, ?, ?, DATE_SUB(NOW(), INTERVAL ? HOUR))',
            [$actor, $entity, $action, $summary, (count($entries) - $i) * 7]
        );
    }
}
