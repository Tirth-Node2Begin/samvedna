<?php
/**
 * Care plan catalogue CRUD.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/Plan.php';
require_once __DIR__ . '/../../includes/services/ScheduleGenerator.php';

function plans_index(): void
{
    cms_require_auth();

    $plans = Plan::all(cms_query('all') !== '1');

    // Attach live usage so the catalogue screen can warn before an edit lands on
    // cases that are already running.
    $planIds = array_map(static fn(array $p): int => (int) $p['id'], $plans);
    $activeCases = [];
    if ($planIds) {
        $placeholders = implode(',', array_fill(0, count($planIds), '?'));
        foreach (cms_all(
            "SELECT plan_id, COUNT(*) AS n
             FROM cms_cases
             WHERE status = 'active' AND plan_id IN ($placeholders)
             GROUP BY plan_id",
            $planIds
        ) as $row) {
            $activeCases[(int) $row['plan_id']] = (int) $row['n'];
        }
    }

    $plans = array_map(static function (array $p) use ($activeCases): array {
        $p['activeCases'] = $activeCases[(int) $p['id']] ?? 0;
        $p['cycles'] = ScheduleGenerator::cycleCount($p);
        return $p;
    }, $plans);

    cms_json(['plans' => $plans]);
}

function plans_store(): void
{
    cms_require_role(['founder']);

    $body = cms_body();
    $fields = [];
    if (trim((string) ($body['name'] ?? '')) === '') {
        $fields['name'] = 'Plan name is required.';
    }
    $code = trim((string) ($body['code'] ?? ''));
    if ($code === '') {
        $fields['code'] = 'A short code is required (e.g. standard).';
    } elseif (Plan::findByCode($code)) {
        $fields['code'] = 'That plan code is already in use.';
    }
    if ($fields) {
        cms_error('validation', 'Check the plan details.', 422, $fields);
    }

    $id = Plan::create($body);
    cms_log('plan', $id, 'created', 'Created plan ' . $body['name']);
    cms_json(['plan' => Plan::find($id)], 201);
}

function plans_update(int $id): void
{
    cms_require_role(['founder']);

    $existing = Plan::find($id);
    if (!$existing) {
        cms_error('not_found', 'Plan not found.', 404);
    }

    $body = cms_body();
    $body['code'] = $body['code'] ?? $existing['code'];

    $clash = Plan::findByCode((string) $body['code']);
    if ($clash && (int) $clash['id'] !== $id) {
        cms_error('validation', 'That plan code is already in use.', 422, ['code' => 'Code already in use.']);
    }

    Plan::update($id, $body);
    cms_log('plan', $id, 'updated', 'Updated plan ' . ($body['name'] ?? $existing['name']));
    cms_json(['plan' => Plan::find($id)]);
}

function plans_destroy(int $id): void
{
    cms_require_role(['founder']);

    if (!Plan::find($id)) {
        cms_error('not_found', 'Plan not found.', 404);
    }

    // Archive rather than delete — running cases still point here.
    Plan::archive($id);
    cms_log('plan', $id, 'archived', 'Archived a care plan');
    cms_json(['ok' => true, 'archived' => true]);
}
