<?php
/**
 * Auth + vocabulary endpoints.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/models/User.php';

function auth_login(): void
{
    $username = cms_str('username');
    $password = (string) cms_in('password', '');

    if ($username === '' || $password === '') {
        cms_error('validation', 'Enter a username and password.', 422, [
            'username' => $username === '' ? 'Username is required.' : '',
            'password' => $password === '' ? 'Password is required.' : '',
        ]);
    }

    $user = cms_attempt_login($username, $password);
    if (!$user) {
        cms_error('invalid_credentials', 'That username and password combination is not recognised.', 401);
    }

    cms_log('auth', $user['id'], 'login', $user['name'] . ' signed in');
    cms_json(['user' => $user]);
}

function auth_logout(): void
{
    $user = cms_user();
    if ($user) {
        cms_log('auth', (int) $user['id'], 'logout', $user['name'] . ' signed out');
    }
    cms_logout();
    cms_json(['ok' => true]);
}

function auth_me(): void
{
    $user = cms_user();
    if (!$user) {
        cms_json(['user' => null], 200);
    }
    cms_json(['user' => cms_public_user($user), 'demo' => CMS_DEMO]);
}

/**
 * Everything the client needs to render dropdowns and checklists without
 * hard-coding clinical vocabulary in two places.
 */
function meta_index(): void
{
    cms_json([
        'demo'              => CMS_DEMO,
        'conditions'        => cms_condition_types(),
        'severities'        => [
            ['key' => 'mild', 'label' => 'Mild'],
            ['key' => 'moderate', 'label' => 'Moderate'],
            ['key' => 'severe', 'label' => 'Severe'],
        ],
        'markers'           => cms_marker_catalogue(),
        'markerRatings'     => cms_marker_ratings(),
        'protocolDecisions' => cms_protocol_decisions(),
        'escalationReasons' => cms_escalation_reasons(),
        'roles'             => array_map(
            static fn(string $r): array => ['key' => $r, 'label' => cms_role_label($r)],
            ['founder', 'senior_doctor', 'case_doctor', 'coordinator']
        ),
        'patientStatuses'   => ['draft', 'active', 'on_hold', 'completed', 'discharged'],
        'demoAccounts'      => CMS_DEMO ? [
            ['username' => 'founder',    'role' => 'Founder',       'password' => 'samvedna123'],
            ['username' => 'senior',     'role' => 'Senior Doctor', 'password' => 'samvedna123'],
            ['username' => 'casedoctor', 'role' => 'Case Doctor',   'password' => 'samvedna123'],
            ['username' => 'coordinator', 'role' => 'Coordinator',  'password' => 'samvedna123'],
        ] : [],
    ]);
}
