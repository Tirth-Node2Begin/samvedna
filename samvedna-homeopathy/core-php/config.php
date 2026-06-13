<?php
declare(strict_types=1);

const APP_NAME = 'Samvedna Homeopathy';
const ADMIN_USERNAME = 'admin';

// Default password: Samvedna@2026
// Replace this hash before uploading to a live server.
const ADMIN_PASSWORD_HASH = '$2y$10$1gYfmWoC7j6pJPJtFu075umgREMmP8oh5hpIJLdqNLe4DLSaiIdrK';

// --- MySQL Database ---
const DB_HOST = 'localhost';
const DB_NAME = 'samvedna_homeopathy';
const DB_USER = 'root';
const DB_PASS = '';

const CSRF_SESSION_KEY = 'samvedna_csrf_token';

const CONDITION_OPTIONS = [
    'Autism Spectrum Disorder Support',
    'ADHD Support',
    'Learning Disability Support',
    'Speech Delay Support',
    'Developmental Delay Support',
    'Genetic Disorders Support',
    'Neurological Disorders Support',
];

const PREFERRED_TIME_OPTIONS = [
    'morning',
    'afternoon',
    'evening',
];
