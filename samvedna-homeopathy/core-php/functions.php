<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function start_app_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('samvedna_admin');
        session_start();
    }
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// --- Database ---

function db_connect(): mysqli
{
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            error_log('DB connection failed: ' . $conn->connect_error);
            throw new RuntimeException('Database connection failed.');
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}

// --- CSRF ---

function csrf_token(): string
{
    start_app_session();

    if (empty($_SESSION[CSRF_SESSION_KEY])) {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION[CSRF_SESSION_KEY];
}

function csrf_is_valid(): bool
{
    start_app_session();

    $postedToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    $sessionToken = isset($_SESSION[CSRF_SESSION_KEY]) ? (string) $_SESSION[CSRF_SESSION_KEY] : '';

    return $postedToken !== '' && $sessionToken !== '' && hash_equals($sessionToken, $postedToken);
}

// --- Form helpers ---

function post_value(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function normalize_inquiry_from_post(): array
{
    return [
        'parent_name' => post_value('parent_name'),
        'child_age' => post_value('child_age'),
        'condition' => post_value('condition'),
        'country' => post_value('country'),
        'phone' => post_value('phone'),
        'email' => post_value('email'),
        'preferred_time' => post_value('preferred_time'),
        'message' => post_value('message'),
    ];
}

function validate_inquiry(array $data): array
{
    $errors = [];

    if (strlen($data['parent_name']) < 2) {
        $errors['parent_name'] = 'Enter the parent name.';
    }

    if ($data['child_age'] === '' || !is_numeric($data['child_age'])) {
        $errors['child_age'] = 'Enter the child age.';
    } else {
        $age = (int) $data['child_age'];
        if ($age < 0 || $age > 18) {
            $errors['child_age'] = 'Enter an age between 0 and 18.';
        }
    }

    if (!in_array($data['condition'], CONDITION_OPTIONS, true)) {
        $errors['condition'] = 'Choose a condition.';
    }

    if (strlen($data['country']) < 2) {
        $errors['country'] = 'Enter the country.';
    }

    if (!preg_match('/^\+?[0-9]{8,15}$/', $data['phone'])) {
        $errors['phone'] = 'Use 8 to 15 digits with optional country code.';
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if (!in_array($data['preferred_time'], PREFERRED_TIME_OPTIONS, true)) {
        $errors['preferred_time'] = 'Choose a preferred time.';
    }

    if (strlen($data['message']) > 500) {
        $errors['message'] = 'Keep the message under 500 characters.';
    }

    return $errors;
}

// --- Inquiry CRUD (MySQL) ---

function save_inquiry(array $data): void
{
    $db = db_connect();

    $stmt = $db->prepare(
        'INSERT INTO inquiries (parent_name, child_age, `condition`, country, phone, email, preferred_time, message, ip_address, user_agent, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $childAge = (int) $data['child_age'];

    $stmt->bind_param(
        'sissssssss',
        $data['parent_name'],
        $childAge,
        $data['condition'],
        $data['country'],
        $data['phone'],
        $data['email'],
        $data['preferred_time'],
        $data['message'],
        $ipAddress,
        $userAgent
    );

    $stmt->execute();
    $stmt->close();
}

function read_inquiries(): array
{
    $db = db_connect();

    $result = $db->query(
        'SELECT * FROM inquiries ORDER BY created_at DESC'
    );

    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $result->free();
    return $rows;
}

// --- Admin Auth ---

function admin_is_logged_in(): bool
{
    start_app_session();

    return !empty($_SESSION['samvedna_admin_logged_in']);
}

function login_admin(string $username, string $password): bool
{
    start_app_session();

    if (!hash_equals(ADMIN_USERNAME, $username)) {
        return false;
    }

    if (!password_verify($password, ADMIN_PASSWORD_HASH)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['samvedna_admin_logged_in'] = true;
    $_SESSION['samvedna_admin_username'] = $username;

    return true;
}

function logout_admin(): void
{
    start_app_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}

function require_admin(): void
{
    if (!admin_is_logged_in()) {
        redirect_to('/admin/');
    }
}

function formatted_date(string $isoDate): string
{
    try {
        $date = new DateTime($isoDate);
        $date->setTimezone(new DateTimeZone('Asia/Kolkata'));
        return $date->format('d M Y, h:i A');
    } catch (Exception $exception) {
        return $isoDate;
    }
}

function export_inquiries_csv(array $inquiries): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=samvedna-inquiries.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'Date',
        'Parent Name',
        'Child Age',
        'Condition',
        'Country',
        'Phone',
        'Email',
        'Preferred Time',
        'Message',
    ]);

    foreach ($inquiries as $inquiry) {
        fputcsv($output, [
            formatted_date((string) ($inquiry['created_at'] ?? '')),
            $inquiry['parent_name'] ?? '',
            $inquiry['child_age'] ?? '',
            $inquiry['condition'] ?? '',
            $inquiry['country'] ?? '',
            $inquiry['phone'] ?? '',
            $inquiry['email'] ?? '',
            $inquiry['preferred_time'] ?? '',
            $inquiry['message'] ?? '',
        ]);
    }

    fclose($output);
    exit;
}
