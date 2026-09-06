<?php
/**
 * Team, roles, doctor scorecards and the audit log.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/User.php';

function team_index(): void
{
    cms_require_auth();

    cms_json([
        'team'  => User::withCaseload(),
        'roles' => array_map(
            static fn(string $r): array => ['key' => $r, 'label' => cms_role_label($r)],
            ['founder', 'senior_doctor', 'case_doctor', 'coordinator']
        ),
    ]);
}

function team_store(): void
{
    cms_require_role(['founder']);

    $body = cms_body();
    $fields = [];
    $username = trim((string) ($body['username'] ?? ''));
    $password = (string) ($body['password'] ?? '');

    if (trim((string) ($body['name'] ?? '')) === '') {
        $fields['name'] = 'Name is required.';
    }
    if ($username === '') {
        $fields['username'] = 'Username is required.';
    } elseif (User::findByUsername($username)) {
        $fields['username'] = 'That username is taken.';
    }
    if (strlen($password) < 8) {
        $fields['password'] = 'Use at least 8 characters.';
    }
    if (!in_array($body['role'] ?? '', ['founder', 'senior_doctor', 'case_doctor', 'coordinator'], true)) {
        $fields['role'] = 'Pick a role.';
    }
    if ($fields) {
        cms_error('validation', 'Check the team member details.', 422, $fields);
    }

    $id = User::create($body);
    cms_log('user', $id, 'created', 'Added ' . $body['name'] . ' as ' . cms_role_label($body['role']));

    cms_json(['user' => cms_public_user(User::find($id))], 201);
}

function team_update(int $id): void
{
    cms_require_role(['founder']);

    $user = User::find($id);
    if (!$user) {
        cms_error('not_found', 'Team member not found.', 404);
    }

    $body = cms_body();
    if (trim((string) ($body['name'] ?? '')) === '') {
        cms_error('validation', 'Name is required.', 422, ['name' => 'Name is required.']);
    }
    if (!in_array($body['role'] ?? '', ['founder', 'senior_doctor', 'case_doctor', 'coordinator'], true)) {
        cms_error('validation', 'Pick a valid role.', 422, ['role' => 'Pick a role.']);
    }

    User::update($id, $body);
    cms_log('user', $id, 'updated', 'Updated ' . $body['name']);

    cms_json(['user' => cms_public_user(User::find($id))]);
}

function activity_index(): void
{
    cms_require_auth();

    $limit  = max(1, min(200, (int) (cms_query('limit') ?: 60)));
    $entity = cms_query('entityType');

    $sql = 'SELECT * FROM cms_activity_log WHERE 1 = 1';
    $params = [];
    if ($entity !== '') {
        $sql .= ' AND entity_type = ?';
        $params[] = $entity;
    }
    $sql .= ' ORDER BY created_at DESC, id DESC LIMIT ' . $limit;

    $rows = cms_all($sql, $params);

    cms_json([
        'activity' => array_map(static fn(array $a): array => [
            'id'         => (int) $a['id'],
            'actorName'  => $a['actor_name'] ?? 'System',
            'entityType' => $a['entity_type'],
            'entityId'   => $a['entity_id'] ? (int) $a['entity_id'] : null,
            'action'     => $a['action'],
            'summary'    => $a['summary'] ?? '',
            'createdAt'  => $a['created_at'],
        ], $rows),
    ]);
}
