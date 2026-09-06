<?php
/**
 * PDO connection (singleton) for the CMS backend.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function cms_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        CMS_DB_HOST,
        CMS_DB_PORT,
        CMS_DB_NAME,
        CMS_DB_CHARSET
    );

    $started = microtime(true);

    try {
        $pdo = new PDO($dsn, CMS_DB_USER, CMS_DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // The API always answers JSON, so a connection failure has to as well —
        // otherwise the frontend sees an HTML page and reports a parse error.
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error'   => 'db_unavailable',
            'message' => 'Database connection failed: ' . $e->getMessage(),
            'hint'    => 'Start MySQL, then run: php samvedna-cms/backend/migrations/migrate.php --seed-demo',
        ]);
        exit;
    }

    cms_db_metric('connect_ms', (microtime(true) - $started) * 1000);
    return $pdo;
}

function cms_db_metric(string $key, float $value): void
{
    if (!isset($GLOBALS['cms_db_metrics'])) {
        $GLOBALS['cms_db_metrics'] = ['queries' => 0, 'query_ms' => 0.0, 'connect_ms' => 0.0];
    }

    if ($key === 'queries') {
        $GLOBALS['cms_db_metrics']['queries'] += (int) $value;
        return;
    }

    $GLOBALS['cms_db_metrics'][$key] = (float) ($GLOBALS['cms_db_metrics'][$key] ?? 0) + $value;
}

function cms_db_metrics(): array
{
    return $GLOBALS['cms_db_metrics'] ?? ['queries' => 0, 'query_ms' => 0.0, 'connect_ms' => 0.0];
}

function cms_db_record_query(float $started): void
{
    cms_db_metric('queries', 1);
    cms_db_metric('query_ms', (microtime(true) - $started) * 1000);
}

/** Fetch all rows for a query. */
function cms_all(string $sql, array $params = []): array
{
    $pdo = cms_db();
    $started = microtime(true);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    cms_db_record_query($started);
    return $stmt->fetchAll();
}

/** Fetch a single row, or null. */
function cms_one(string $sql, array $params = []): ?array
{
    $pdo = cms_db();
    $started = microtime(true);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    cms_db_record_query($started);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/** Run a statement and return the affected row count. */
function cms_run(string $sql, array $params = []): int
{
    $pdo = cms_db();
    $started = microtime(true);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    cms_db_record_query($started);
    return $stmt->rowCount();
}

/** Insert and return the new id. */
function cms_insert(string $sql, array $params = []): int
{
    cms_run($sql, $params);
    return (int) cms_db()->lastInsertId();
}

/** Single scalar value from the first column of the first row. */
function cms_scalar(string $sql, array $params = [], $default = 0)
{
    $pdo = cms_db();
    $started = microtime(true);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    cms_db_record_query($started);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : $value;
}
