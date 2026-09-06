<?php
/**
 * Shared helpers for the JSON API: responses, input parsing, dates, codes.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

/* ------------------------------------------------------------- responses -- */

/** Send JSON and stop. */
function cms_json($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    if (defined('CMS_APP_ENV') && CMS_APP_ENV !== 'production') {
        $requestStart = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $totalMs = (microtime(true) - (float) $requestStart) * 1000;
        $db = function_exists('cms_db_metrics') ? cms_db_metrics() : ['queries' => 0, 'query_ms' => 0.0, 'connect_ms' => 0.0];

        header(sprintf(
            'Server-Timing: app;dur=%.1f, db;dur=%.1f, db_connect;dur=%.1f',
            $totalMs,
            (float) ($db['query_ms'] ?? 0),
            (float) ($db['connect_ms'] ?? 0)
        ));
        header('X-CMS-Query-Count: ' . (int) ($db['queries'] ?? 0));
    }
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Structured error. `fields` carries per-field messages so the React forms can
 * highlight exactly the mandatory boxes the server rejected — the client-side
 * zod rules and these server rules are deliberately the same set.
 */
function cms_error(string $code, string $message, int $status = 400, array $fields = []): void
{
    $payload = ['error' => $code, 'message' => $message];
    if ($fields) {
        $payload['fields'] = $fields;
    }
    cms_json($payload, $status);
}

/* ----------------------------------------------------------------- input -- */

/** Decoded JSON request body (falls back to form-encoded POST). */
function cms_body(): array
{
    static $body = null;
    if ($body !== null) {
        return $body;
    }
    $raw = file_get_contents('php://input') ?: '';
    if ($raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $body = $decoded;
        }
    }
    return $body = (is_array($_POST) ? $_POST : []);
}

function cms_in(string $key, $default = null)
{
    $body = cms_body();
    return array_key_exists($key, $body) ? $body[$key] : $default;
}

/** Trimmed string from the body. */
function cms_str(string $key, string $default = ''): string
{
    $value = cms_in($key, $default);
    if (is_scalar($value)) {
        return trim((string) $value);
    }
    return $default;
}

function cms_int(string $key, int $default = 0): int
{
    $value = cms_in($key, $default);
    return is_numeric($value) ? (int) $value : $default;
}

function cms_bool(string $key, bool $default = false): bool
{
    $value = cms_in($key, $default);
    if (is_bool($value)) {
        return $value;
    }
    if (is_numeric($value)) {
        return (int) $value === 1;
    }
    if (is_string($value)) {
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }
    return $default;
}

/** Array from the body (accepts a JSON array or a newline-separated string). */
function cms_arr(string $key): array
{
    $value = cms_in($key, []);
    if (is_array($value)) {
        return array_values($value);
    }
    if (is_string($value) && $value !== '') {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        return array_values(array_filter(array_map('trim', $lines), static fn($v) => $v !== ''));
    }
    return [];
}

/** Query-string value. */
function cms_query(string $key, string $default = ''): string
{
    $value = $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

/* ------------------------------------------------------------------ json -- */

/** Decode a JSON text column into an array, tolerating nulls and bad data. */
function cms_json_col($value): array
{
    if (is_array($value)) {
        return $value;
    }
    if (is_string($value) && $value !== '') {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
    return [];
}

/** Encode for a JSON text column. */
function cms_json_put($value): string
{
    return json_encode(array_values(is_array($value) ? $value : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
}

/** Encode an associative structure (object shape preserved). */
function cms_json_obj($value): string
{
    return json_encode(is_array($value) ? $value : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
}

/* ----------------------------------------------------------------- dates -- */

function cms_today(): string
{
    return date('Y-m-d');
}

/**
 * Add days to a Y-m-d date. The sign is written out explicitly: strtotime reads
 * "+-60 days" as a *forward* jump, which silently turns every backdated demo
 * case into a future one.
 */
function cms_add_days(string $date, int $days): string
{
    $sign = $days < 0 ? '-' : '+';
    return date('Y-m-d', (int) strtotime($date . ' ' . $sign . abs($days) . ' days'));
}

/** Add months to a Y-m-d date (same sign handling as cms_add_days). */
function cms_add_months(string $date, int $months): string
{
    $sign = $months < 0 ? '-' : '+';
    return date('Y-m-d', (int) strtotime($date . ' ' . $sign . abs($months) . ' months'));
}

/** Whole days between two Y-m-d dates (b - a). Negative when b is earlier. */
function cms_days_between(string $a, string $b): int
{
    $from = new DateTimeImmutable($a);
    $to   = new DateTimeImmutable($b);
    return (int) $from->diff($to)->format('%r%a');
}

/** Age in years from a date of birth, or null. */
function cms_age(?string $dob): ?int
{
    if (!$dob) {
        return null;
    }
    try {
        return (new DateTimeImmutable($dob))->diff(new DateTimeImmutable('today'))->y;
    } catch (Exception $e) {
        return null;
    }
}

/** Validate a Y-m-d string. */
function cms_is_date(string $value): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
}

/* ----------------------------------------------------------------- codes -- */

/**
 * Next sequential code for a table, e.g. cms_next_code('cms_patients', 'SAM').
 * Reads the current max id rather than counting rows so deletions never
 * produce a duplicate code.
 */
function cms_next_code(string $table, string $prefix): string
{
    $allowed = ['cms_patients', 'cms_cases', 'cms_escalations'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unknown table for code generation.');
    }
    $max = (int) cms_scalar("SELECT COALESCE(MAX(id), 0) FROM `{$table}`");
    return $prefix . '-' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
}

/* ------------------------------------------------------------ vocabulary -- */

/**
 * Baseline functional markers. Order matters: it is the order shown at intake
 * and the order the progress dashboard charts them in.
 */
function cms_marker_catalogue(): array
{
    return [
        ['key' => 'eye_contact',        'label' => 'Eye contact'],
        ['key' => 'social_reciprocity', 'label' => 'Social reciprocity'],
        ['key' => 'repetitive',         'label' => 'Repetitive behaviours'],
        ['key' => 'verbal',             'label' => 'Verbal communication'],
        ['key' => 'sensory',            'label' => 'Sensory response'],
        ['key' => 'sleep',              'label' => 'Sleep pattern'],
        ['key' => 'fine_motor',         'label' => 'Fine motor skills'],
        ['key' => 'attention',          'label' => 'Attention span'],
        ['key' => 'self_help',          'label' => 'Self-help skills'],
    ];
}

/** Ratings a marker can take, weakest first (drives the progress bar width). */
function cms_marker_ratings(): array
{
    return [
        ['key' => 'absent',   'label' => 'Absent',   'score' => 10],
        ['key' => 'minimal',  'label' => 'Minimal',  'score' => 25],
        ['key' => 'poor',     'label' => 'Poor',     'score' => 35],
        ['key' => 'frequent', 'label' => 'Frequent', 'score' => 35],
        ['key' => 'emerging', 'label' => 'Emerging', 'score' => 50],
        ['key' => 'moderate', 'label' => 'Moderate', 'score' => 60],
        ['key' => 'good',     'label' => 'Good',     'score' => 80],
        ['key' => 'typical',  'label' => 'Age-typical', 'score' => 95],
    ];
}

function cms_rating_score(string $rating): int
{
    foreach (cms_marker_ratings() as $r) {
        if ($r['key'] === $rating) {
            return (int) $r['score'];
        }
    }
    return 0;
}

/** Escalation reasons, exactly as the flow checklist lists them. */
function cms_escalation_reasons(): array
{
    return [
        ['key' => 'no_movement',      'label' => 'No measurable movement across 2 consecutive cycles'],
        ['key' => 'mixed_signals',    'label' => 'Mixed clinical signals — protocol innovation needed'],
        ['key' => 'comorbidity',      'label' => 'Multi-comorbidity complexity'],
        ['key' => 'parent_anxiety',   'label' => 'Parent anxiety unresolved despite structured counselling'],
        ['key' => 'adverse_reaction', 'label' => 'Adverse reaction / safety concern'],
        ['key' => 'plan_change',      'label' => 'Parent requesting plan change or refund'],
    ];
}

function cms_condition_types(): array
{
    return [
        'Autism Spectrum Disorder',
        'ADHD',
        'Speech Delay',
        'Global Developmental Delay',
        'Learning Disability',
        'Cerebral Palsy',
        'Sensory Processing Disorder',
        'Behavioural Disorder',
        'Other',
    ];
}

function cms_protocol_decisions(): array
{
    return [
        ['key' => 'continued',          'label' => 'Continued as-is'],
        ['key' => 'continued_adjusted', 'label' => 'Continued with minor dosage adjustment'],
        ['key' => 'changed',            'label' => 'Protocol changed'],
        ['key' => 'paused',             'label' => 'Protocol paused'],
    ];
}

/** Human label for a role key. */
function cms_role_label(string $role): string
{
    return [
        'founder'       => 'Founder',
        'senior_doctor' => 'Senior Doctor',
        'case_doctor'   => 'Case Doctor',
        'coordinator'   => 'Coordinator',
    ][$role] ?? $role;
}
