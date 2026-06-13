<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/functions.php';

// Allow requests from Next.js dev server
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON body.']);
    exit;
}

$data = [
    'parent_name'    => trim((string) ($input['parentName'] ?? '')),
    'child_age'      => (string) ($input['childAge'] ?? ''),
    'condition'      => trim((string) ($input['condition'] ?? '')),
    'country'        => trim((string) ($input['country'] ?? '')),
    'phone'          => trim((string) ($input['phone'] ?? '')),
    'email'          => trim((string) ($input['email'] ?? '')),
    'preferred_time' => trim((string) ($input['preferredTime'] ?? 'morning')),
    'message'        => trim((string) ($input['message'] ?? '')),
];

$errors = validate_inquiry($data);

if ($errors !== []) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Validation failed.', 'errors' => $errors]);
    exit;
}

try {
    save_inquiry($data);
    echo json_encode(['status' => 'success', 'message' => 'Inquiry saved successfully.']);
} catch (Throwable $e) {
    error_log('save_inquiry error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save inquiry. Please try again.']);
}
