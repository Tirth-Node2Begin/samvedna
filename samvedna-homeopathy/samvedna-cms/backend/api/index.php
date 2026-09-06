<?php
/**
 * Samvedna CMS — JSON API front controller.
 *
 * One entry point, one explicit route table. Routes are matched as
 * METHOD + segment pattern, where `#` stands for a numeric id, e.g.
 *
 *   GET  /api/patients/12          -> patients_show(12)
 *   POST /api/reviews/8/signoff    -> reviews_signoff(8)
 *
 * router.php (dev) and the production .htaccess both funnel /api/* here.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

/* ------------------------------------------------------------------ CORS -- */
// Normally the Next.js dev server proxies /api so everything is same-origin and
// none of this matters. It only kicks in when the frontend is served from its
// own origin, in which case credentials require an explicit origin echo.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $allowed = array_map('trim', explode(',', CMS_CORS_ORIGINS));
    if (in_array($origin, $allowed, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
    }
}
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* --------------------------------------------------------------- handlers -- */

require_once __DIR__ . '/handlers/auth.php';
require_once __DIR__ . '/handlers/dashboard.php';
require_once __DIR__ . '/handlers/patients.php';
require_once __DIR__ . '/handlers/plans.php';
require_once __DIR__ . '/handlers/cases.php';
require_once __DIR__ . '/handlers/schedule.php';
require_once __DIR__ . '/handlers/reviews.php';
require_once __DIR__ . '/handlers/escalations.php';
require_once __DIR__ . '/handlers/progress.php';
require_once __DIR__ . '/handlers/team.php';
require_once __DIR__ . '/handlers/medicine.php';
require_once __DIR__ . '/handlers/notifications.php';

/* ----------------------------------------------------------------- route -- */

/** The path after /api, as segments. */
function cms_route_segments(): array
{
    // PATH_INFO when running under Apache; ?_route= when router.php rewrites it.
    $path = $_GET['_route'] ?? ($_SERVER['PATH_INFO'] ?? '');
    if ($path === '') {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $path = preg_replace('#^.*?/api#', '', $uri) ?? '';
    }
    $path = trim(urldecode($path), '/');
    return $path === '' ? [] : explode('/', $path);
}

/**
 * Route table. Keys are "METHOD /pattern"; `#` matches a run of digits and is
 * passed to the handler as an int, in order.
 */
$routes = [
    'POST /auth/login'   => 'auth_login',
    'POST /auth/logout'  => 'auth_logout',
    'GET /auth/me'       => 'auth_me',

    'GET /meta'          => 'meta_index',
    'GET /dashboard'     => 'dashboard_index',

    'GET /patients'            => 'patients_index',
    'POST /patients'           => 'patients_store',
    'GET /patients/#'          => 'patients_show',
    'PUT /patients/#'          => 'patients_update',
    'DELETE /patients/#'       => 'patients_destroy',
    'PUT /patients/#/baseline' => 'patients_baseline',
    'POST /patients/#/activate' => 'patients_activate',

    'GET /plans'        => 'plans_index',
    'POST /plans'       => 'plans_store',
    'PUT /plans/#'      => 'plans_update',
    'DELETE /plans/#'   => 'plans_destroy',

    'GET /cases/#'          => 'cases_show',
    'POST /cases/#/roles'   => 'cases_roles',
    'POST /cases/#/status'  => 'cases_status',

    'GET /schedule'                => 'schedule_index',
    'POST /schedule/#/complete'    => 'schedule_complete',
    'POST /schedule/#/reschedule'  => 'schedule_reschedule',
    'POST /schedule/#/adherence'   => 'schedule_adherence',
    'POST /schedule/#/open-review' => 'schedule_open_review',

    'GET /reviews'           => 'reviews_index',
    'GET /reviews/#'         => 'reviews_show',
    'PUT /reviews/#'         => 'reviews_update',
    'POST /reviews/#/submit' => 'reviews_submit',
    'POST /reviews/#/signoff' => 'reviews_signoff',

    'GET /escalations'          => 'escalations_index',
    'POST /escalations'         => 'escalations_store',
    'GET /escalations/#'        => 'escalations_show',
    'POST /escalations/#/advance' => 'escalations_advance',
    'POST /escalations/#/resolve' => 'escalations_resolve',

    'GET /progress'          => 'progress_index',
    'GET /progress/#/#'      => 'progress_show',
    'POST /progress/#/share' => 'progress_share',

    'GET /medicine'                 => 'medicine_index',
    'GET /medicine/#'               => 'medicine_show',
    'POST /medicine/#/deliver'      => 'medicine_deliver',
    'POST /medicine/#/out-of-stock' => 'medicine_out_of_stock',
    'PUT /medicine/#'               => 'medicine_update',

    'GET /notifications'            => 'notifications_index',
    'GET /notifications/summary'    => 'notifications_summary',
    'POST /notifications/read-all'  => 'notifications_read_all',
    'POST /notifications/#/read'    => 'notifications_read',
    'POST /notifications/#/dismiss' => 'notifications_dismiss',

    'GET /team'      => 'team_index',
    'POST /team'     => 'team_store',
    'PUT /team/#'    => 'team_update',
    'GET /activity'  => 'activity_index',
];

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$segments = cms_route_segments();

// Browsers and simple fetch clients cannot always send PUT/DELETE; honour the
// standard override header/field so the client stays simple.
$override = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '');
if ($method === 'POST' && in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
    $method = $override;
}
if ($method === 'PATCH') {
    $method = 'PUT';
}

foreach ($routes as $pattern => $handler) {
    [$routeMethod, $routePath] = explode(' ', $pattern, 2);
    if ($routeMethod !== $method) {
        continue;
    }

    $parts = array_values(array_filter(explode('/', $routePath), static fn($p) => $p !== ''));
    if (count($parts) !== count($segments)) {
        continue;
    }

    $args = [];
    $matched = true;
    foreach ($parts as $i => $part) {
        if ($part === '#') {
            if (!ctype_digit($segments[$i])) {
                $matched = false;
                break;
            }
            $args[] = (int) $segments[$i];
        } elseif ($part !== $segments[$i]) {
            $matched = false;
            break;
        }
    }

    if ($matched && function_exists($handler)) {
        try {
            $handler(...$args);
        } catch (PDOException $e) {
            cms_error('db_error', 'Database error: ' . $e->getMessage(), 500);
        } catch (Throwable $e) {
            cms_error('server_error', $e->getMessage(), 500);
        }
        exit;
    }
}

cms_error('not_found', 'No API route matches ' . $method . ' /' . implode('/', $segments) . '.', 404);
