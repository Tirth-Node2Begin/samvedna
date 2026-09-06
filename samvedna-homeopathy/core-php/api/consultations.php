<?php
/**
 * POST /api/consultations.php — public care-plan assessment endpoint.
 *
 * Receives the multi-step wizard (components/consultation/AssessmentWizard.tsx).
 * The site is a static export, so there is no Node server and no server action:
 * the wizard posts here directly. Client-side validation is a UX affordance, not
 * a security boundary — anything reaching this file is untrusted.
 *
 * The general-information fields are validated and stored as columns; every
 * clinical answer is passed through in an `answers` object and stored as JSON.
 *
 * Responses:
 *   201 {ok:true}
 *   400 {ok:false, message, fieldErrors:{field:[msg]}}
 *   405 / 429 / 500 {ok:false, message}
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/models/Consultation.php';

// Same-origin (static export and PHP share one docroot), so no permissive CORS.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/** Emit JSON and stop. Not helpers.php json_response(), which sets a 60s public
 *  cache — wrong for a write endpoint. */
function consultation_json(array $payload, int $status): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    consultation_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

// Accept both JSON and classic form encoding.
$raw = file_get_contents('php://input') ?: '';
$input = [];
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $decoded = json_decode($raw, true);
    $input = is_array($decoded) ? $decoded : [];
} else {
    $input = $_POST;
}

/** Trimmed string for a key, always a string. */
function cfield(array $src, string $key): string
{
    $v = $src[$key] ?? '';
    return is_scalar($v) ? trim((string) $v) : '';
}

// Honeypot: a hidden field real users never fill. Answer 201 so bots see success
// and do not retry with a different strategy.
if (cfield($input, 'company') !== '') {
    consultation_json(['ok' => true], 201);
}

$allowedSources = ['website', 'popup'];

$plan        = cfield($input, 'plan');
$planName    = cfield($input, 'planName');
$amount      = cfield($input, 'amount');
$patientName = cfield($input, 'patientName');
$fatherName  = cfield($input, 'fatherName');
$mobile      = cfield($input, 'mobile');
$altPhone    = cfield($input, 'altPhone');
$address     = cfield($input, 'address');
$city        = cfield($input, 'city');
$state       = cfield($input, 'state');
$zip         = cfield($input, 'zip');
$email       = cfield($input, 'email');
$remarks     = cfield($input, 'remarks');
$childAge    = cfield($input, 'childAge');

$source = cfield($input, 'source');
if (!in_array($source, $allowedSources, true)) {
    $source = 'website';
}

// The clinical answers arrive as an object keyed by question id.
$answers = $input['answers'] ?? [];
if (!is_array($answers)) {
    $answers = [];
}

$errors = [];

if (mb_strlen($patientName) < 2) {
    $errors['patientName'][] = "Enter the patient's name.";
} elseif (mb_strlen($patientName) > 255) {
    $errors['patientName'][] = 'Name is too long.';
}

if (!preg_match('/^\+?[0-9]{8,15}$/', $mobile)) {
    $errors['mobile'][] = 'Use 8 to 15 digits, with optional country code.';
}

// Email is optional here, but must be valid when provided.
if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255)) {
    $errors['email'][] = 'Enter a valid email address.';
}

if ($errors) {
    consultation_json([
        'ok'          => false,
        'message'     => 'Please correct the highlighted fields.',
        'fieldErrors' => $errors,
    ], 400);
}

try {
    // Flood control: the same mobile/email may not submit more than 3 times in
    // 10 minutes. Cheap, and enough to blunt a form-spam script.
    if (Consultation::recentCountByMobileOrEmail($mobile, $email, 600) >= 3) {
        consultation_json([
            'ok'      => false,
            'message' => 'We already have your recent submission. Our team will contact you shortly.',
        ], 429);
    }

    Consultation::create([
        'plan'         => $plan,
        'plan_name'    => $planName,
        'amount'       => $amount,
        'patient_name' => $patientName,
        'father_name'  => $fatherName,
        'mobile'       => $mobile,
        'alt_phone'    => $altPhone,
        'address'      => $address,
        'city'         => $city,
        'state'        => $state,
        'zip'          => $zip,
        'email'        => $email,
        'remarks'      => $remarks,
        'child_age'    => $childAge,
        'answers'      => $answers,
        'source'       => $source,
    ]);
} catch (Throwable $e) {
    error_log('[consultations] insert failed: ' . $e->getMessage());
    consultation_json([
        'ok'      => false,
        'message' => 'Unable to save your submission right now. Please try again or call us.',
    ], 500);
}

consultation_json(['ok' => true], 201);
