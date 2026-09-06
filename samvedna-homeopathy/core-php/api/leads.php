<?php
/**
 * POST /api/leads.php — public consultation-form endpoint.
 *
 * The site is a static export, so there is no Node server and no server action:
 * ConsultationForm.tsx posts here directly. Validation mirrors
 * lib/schemas/consultation.ts, because client-side zod is a UX affordance, not a
 * security boundary — anything reaching this file is untrusted.
 *
 * Responses:
 *   201 {ok:true}
 *   400 {ok:false, message, fieldErrors:{field:[msg]}}
 *   405 / 429 / 500 {ok:false, message}
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/models/Lead.php';

// This endpoint is same-origin (static export and PHP share one docroot), so it
// does not use the permissive CORS in _bootstrap.php.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/** Emit JSON and stop. Deliberately not helpers.php json_response(), which sets
 *  a 60s public cache — wrong for a write endpoint. */
function lead_json(array $payload, int $status): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    lead_json(['ok' => false, 'message' => 'Method not allowed.'], 405);
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
function field(array $src, string $key): string
{
    $v = $src[$key] ?? '';
    return is_scalar($v) ? trim((string) $v) : '';
}

// Honeypot: a hidden field real users never fill. Answer 201 so bots see success
// and do not retry with a different strategy.
if (field($input, 'company') !== '') {
    lead_json(['ok' => true], 201);
}

$allowedConditions = [
    'Autism Spectrum Disorder Support',
    'ADHD Support',
    'Learning Disability Support',
    'Speech Delay Support',
    'Developmental Delay Support',
    'Genetic Disorders Support',
    'Neurological Disorders Support',
];
$allowedTimes   = ['morning', 'afternoon', 'evening'];
$allowedSources = ['website', 'popup'];

$parentName    = field($input, 'parentName');
$childAgeRaw   = field($input, 'childAge');
$condition     = field($input, 'condition');
$country       = field($input, 'country');
$phone         = field($input, 'phone');
$email         = field($input, 'email');
$message       = field($input, 'message');
$preferredTime = field($input, 'preferredTime');
$source        = field($input, 'source');
if (!in_array($source, $allowedSources, true)) {
    $source = 'website';
}

$errors = [];

if (mb_strlen($parentName) < 2) {
    $errors['parentName'][] = "Enter the parent's full name.";
} elseif (mb_strlen($parentName) > 255) {
    $errors['parentName'][] = 'Name is too long.';
}

if ($childAgeRaw === '' || !is_numeric($childAgeRaw)) {
    $errors['childAge'][] = 'Enter the child age.';
} else {
    $childAge = (int) $childAgeRaw;
    if ($childAge < 0) {
        $errors['childAge'][] = 'Age cannot be negative.';
    } elseif ($childAge > 18) {
        $errors['childAge'][] = 'Please enter an age between 0 and 18.';
    }
}

if (!in_array($condition, $allowedConditions, true)) {
    $errors['condition'][] = 'Choose the primary concern.';
}

if (mb_strlen($country) < 2) {
    $errors['country'][] = 'Enter your country.';
} elseif (mb_strlen($country) > 255) {
    $errors['country'][] = 'Country is too long.';
}

if (!preg_match('/^\+?[0-9]{8,15}$/', $phone)) {
    $errors['phone'][] = 'Use 8 to 15 digits, with optional country code.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
    $errors['email'][] = 'Enter a valid email address.';
}

if (mb_strlen($message) > 500) {
    $errors['message'][] = 'Keep the message under 500 characters.';
}

if (!in_array($preferredTime, $allowedTimes, true)) {
    $errors['preferredTime'][] = 'Choose a preferred consultation time.';
}

if ($errors) {
    lead_json([
        'ok'          => false,
        'message'     => 'Please correct the highlighted fields.',
        'fieldErrors' => $errors,
    ], 400);
}

try {
    // Flood control: the same phone/email may not submit more than 3 times in 10
    // minutes. Cheap, and enough to blunt a form-spam script.
    if (Lead::recentCountByPhoneOrEmail($phone, $email, 600) >= 3) {
        lead_json([
            'ok'      => false,
            'message' => 'We already have your recent request. Our team will contact you shortly.',
        ], 429);
    }

    Lead::create([
        'parent_name'    => $parentName,
        'child_age'      => (int) $childAgeRaw,
        'condition_type' => $condition,
        'country'        => $country,
        'phone'          => $phone,
        'email'          => $email,
        'message'        => $message,
        'preferred_time' => $preferredTime,
        'source'         => $source,
    ]);
} catch (Throwable $e) {
    error_log('[leads] insert failed: ' . $e->getMessage());
    lead_json([
        'ok'      => false,
        'message' => 'Unable to save your request right now. Please try again or call us.',
    ], 500);
}

lead_json(['ok' => true], 201);
