<?php
/**
 * Migration + seed runner.
 *
 * Usage (from the Next.js project root, with XAMPP MySQL running):
 *   php core-php/migrations/migrate.php
 *
 * It will:
 *   1. Create the database if it does not exist.
 *   2. Apply migrations/schema.sql.
 *   3. Seed a default admin (only if none exists).
 *   4. Seed sample blogs / testimonials / doctors — ONLY with --seed-demo.
 *
 * The demo content is opt-in because blogs, doctors and video testimonials are
 * admin-managed and the website renders those sections only when rows exist.
 * Seeding them by default would put invented practitioners and invented parent
 * stories on a live medical site. For local development:
 *
 *   php core-php/migrations/migrate.php --seed-demo
 *
 * Safe to run multiple times.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$isCli = (PHP_SAPI === 'cli');
$seedDemo = ($isCli && in_array('--seed-demo', $argv ?? [], true))
    || (!$isCli && isset($_GET['seed-demo']));
$nl = $isCli ? "\n" : "<br>\n";
function out(string $m): void
{
    global $nl;
    echo $m . $nl;
    if (function_exists('flush')) {
        @flush();
    }
}

// --- 1. Connect to the server (no database) and create it if missing. ------
try {
    $serverDsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $server = new PDO($serverDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    out('ERROR: Could not connect to MySQL server: ' . $e->getMessage());
    out('Is XAMPP MySQL/MariaDB running? Check DB_HOST/DB_USER/DB_PASS in core-php/config/config.php.');
    exit(1);
}

$dbName = DB_NAME;
$server->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
out("Database `{$dbName}` ready.");

// --- 2. Apply schema.sql --------------------------------------------------
$server->exec("USE `{$dbName}`");
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    out('ERROR: Could not read schema.sql');
    exit(1);
}

// Split on semicolons at end-of-statement (schema has no procedures/strings with ;).
$statements = array_filter(array_map('trim', explode(';', $schema)), static fn($s) => $s !== '');
foreach ($statements as $sql) {
    // Skip pure comment blocks.
    if (preg_match('/^(--|\s)*$/', $sql)) {
        continue;
    }
    $server->exec($sql);
}
out('Schema applied (' . count($statements) . ' statements).');

// From here use the configured PDO connection.
require_once __DIR__ . '/../includes/db.php';
$pdo = db();

// --- 3. Seed default admin ------------------------------------------------
$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($adminCount === 0) {
    $username = 'admin';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO admins (username, name, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$username, 'Administrator', $hash]);
    out("Seeded default admin -> username: {$username}  password: {$password}");
    out('*** IMPORTANT: change this password after first login. ***');
} else {
    out("Admins already present ({$adminCount}) — skipping admin seed.");
}

// --- 4. Demo content ------------------------------------------------------
// Opt-in only: an unseeded install shows no blog, team or parent-stories
// section at all, which is correct until the client enters real content.
if (!$seedDemo) {
    out('Skipping demo content (pass --seed-demo to load sample blogs/testimonials/doctors).');
}

// --- 4a. Seed blogs -------------------------------------------------------
$blogCount = (int) $pdo->query('SELECT COUNT(*) FROM blogs')->fetchColumn();
if ($seedDemo && $blogCount === 0) {
    $blogs = [
        ['understanding-autism-first-guide', "Understanding Autism Spectrum Disorder: A Parent's First Guide", 'What the early signs mean, how assessment works, and the calm first steps families can take.', '/images/samvedna-auditorium.webp', 'Samvedna Homeopathy consultation space', 'Autism', '2026-05-12', '6 min read'],
        ['homeopathy-and-speech-delay', 'How Homeopathy Supports Children With Speech Delay', 'A look at individualized care that works alongside speech therapy and daily home routines.', '/images/assistant-doctor-cabin.webp', 'Doctor consultation cabin at Samvedna Homeopathy', 'Speech', '2026-04-28', '5 min read'],
        ['adhd-daily-routines', 'ADHD and Daily Routines: Practical Tips for Families', 'Small, consistent routines that help with attention, sleep and emotional regulation at home.', '/images/dr-krunal-kosada.webp', 'Dr. Krunal Kosada', 'ADHD', '2026-04-09', '4 min read'],
        ['first-consultation-what-to-expect', 'What to Expect in Your First Consultation', 'From sharing reports to building the first care plan — a clear walkthrough of the visit.', '/images/samvedna-associate-portrait.webp', 'Samvedna Homeopathy consultant', 'Care Process', '2026-03-22', '5 min read'],
        ['nutrition-and-sleep-foundations', 'Nutrition and Sleep: Foundations for Developmental Care', 'Why steady sleep and nutrition matter, and gentle changes parents can begin this week.', '/images/dr-yakshika.jpg', 'Dr. Yakshika, consultant at Samvedna Homeopathy', 'Wellbeing', '2026-03-05', '6 min read'],
        ['tracking-progress-follow-ups', 'Tracking Progress: Why Follow-ups Matter', 'How continuous follow-ups turn small observations into a plan that keeps improving.', '/images/member-3.jpg', 'Samvedna Homeopathy medical team member', 'Follow-up', '2026-02-18', '4 min read'],
    ];
    $stmt = $pdo->prepare(
        'INSERT INTO blogs (slug, title, excerpt, content, category, author, image, alt, read_time, status, published_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "published", ?, NOW(), NOW())'
    );
    foreach ($blogs as $b) {
        $content = '<p>' . $b[2] . '</p><p>Replace this placeholder body with the full article from the admin panel.</p>';
        $stmt->execute([$b[0], $b[1], $b[2], $content, $b[5], 'Samvedna Team', $b[3], $b[4], $b[7], $b[6]]);
    }
    out('Seeded ' . count($blogs) . ' blog posts.');
} elseif ($seedDemo) {
    out("Blogs already present ({$blogCount}) — skipping.");
}

// --- 4b. Seed video testimonials -----------------------------------------
$vtCount = (int) $pdo->query('SELECT COUNT(*) FROM video_testimonials')->fetchColumn();
if ($seedDemo && $vtCount === 0) {
    $items = [
        ['Parent family', 'Autism support', 'Canada', '', '/images/assistant-doctor-cabin.webp', "Parent sharing their child's autism care journey", '2:10'],
        ['Parent family', 'Speech delay support', 'India', '', '/images/samvedna-associate-portrait.webp', 'Parent describing speech delay progress', '1:48'],
        ['Parent family', 'Developmental delay support', 'India', '', '/images/dr-yakshika.jpg', 'Parent talking about developmental delay care', '2:35'],
        ['Parent family', 'ADHD support', 'India', '', '/images/member-3.jpg', 'Parent sharing their ADHD support experience', '1:55'],
        ['Parent family', 'Learning support', 'India', '', '/images/samvedna-auditorium.webp', 'Parent reflecting on continuous follow-up care', '2:22'],
        ['Parent family', 'Behavioral support', 'United Kingdom', '', '/images/dr-krunal-kosada.webp', 'Parent thanking the care team', '1:40'],
    ];
    $stmt = $pdo->prepare(
        'INSERT INTO video_testimonials (name, condition_label, location, youtube_id, poster, alt, duration, sort_order, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, "published", NOW(), NOW())'
    );
    foreach ($items as $i => $v) {
        $stmt->execute([$v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6], $i]);
    }
    out('Seeded ' . count($items) . ' video testimonials.');
} elseif ($seedDemo) {
    out("Video testimonials already present ({$vtCount}) — skipping.");
}

// --- 4c. Seed doctors -----------------------------------------------------
$docCount = (int) $pdo->query('SELECT COUNT(*) FROM doctors')->fetchColumn();
if ($seedDemo && $docCount === 0) {
    $j = static fn(array $a): string => json_encode($a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $doctors = [
        [
            'name' => 'Dr. Krishna Thakor', 'title' => 'Senior Consultant',
            'credential' => 'BHMS | Case coordination, long-term follow-up, and family guidance',
            'image' => '/images/samvedna-associate-portrait.webp',
            'alt' => 'Dr. Krishna Thakor, consultant for child neurodevelopment care',
            'specialization' => 'Case Coordination & Follow-up Care', 'experience' => '12+ Years',
            'summary' => 'Coordinates long-term care and follow-ups so families stay supported between consultations.',
            'about' => 'Dr. Krishna Thakor focuses on case coordination and long-term follow-up, helping families stay supported between visits with clear next steps and steady communication.',
            'qualifications' => $j(['BHMS']),
            'specializations' => $j(['Long-term case coordination', 'Follow-up and progress monitoring', 'Family guidance']),
            'treatments' => $j(['Follow-up consultations', 'Care-plan monitoring', 'Parent communication and support']),
            'certifications' => $j([]), 'awards' => $j([]),
            'languages' => $j(['English', 'Hindi', 'Gujarati']),
            'consultation' => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
        ],
        [
            'name' => 'Dr. Yakshika', 'title' => 'Consultant',
            'credential' => 'BHMS | Case coordination and family guidance',
            'image' => '/images/dr-yakshika.jpg',
            'alt' => 'Dr. Yakshika, consultant for child neurodevelopment care',
            'specialization' => 'Developmental Care & Family Guidance', 'experience' => '6+ Years',
            'summary' => 'Supports developmental care and guides parents through assessments and daily routines.',
            'about' => "Dr. Yakshika supports children's developmental care and works closely with parents through assessments, routines and ongoing guidance.",
            'qualifications' => $j(['BHMS']),
            'specializations' => $j(['Developmental care', 'Assessment support', 'Family guidance']),
            'treatments' => $j(['Developmental assessments', 'Supportive homeopathic care', 'Parent guidance']),
            'certifications' => $j([]), 'awards' => $j([]),
            'languages' => $j(['English', 'Hindi', 'Gujarati']),
            'consultation' => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
        ],
        [
            'name' => 'Medical Team Member', 'title' => 'Consultant',
            'credential' => 'BHMS | Case coordination and family guidance',
            'image' => '/images/member-3.jpg',
            'alt' => 'Medical Team Member, consultant for child neurodevelopment care',
            'specialization' => 'Consultation & Coordination', 'experience' => '5+ Years',
            'summary' => 'Assists with consultations, coordination and day-to-day family support across cases.',
            'about' => 'A consultant on the Samvedna team supporting consultations, coordination and day-to-day family guidance across cases.',
            'qualifications' => $j(['BHMS']),
            'specializations' => $j(['Consultation support', 'Case coordination', 'Family guidance']),
            'treatments' => $j(['Supportive consultations', 'Care coordination', 'Parent guidance']),
            'certifications' => $j([]), 'awards' => $j([]),
            'languages' => $j(['English', 'Hindi', 'Gujarati']),
            'consultation' => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
        ],
    ];
    $stmt = $pdo->prepare(
        'INSERT INTO doctors
            (name, title, credential, image, alt, specialization, experience, summary, about,
             qualifications, specializations, treatments, certifications, awards, languages,
             consultation, sort_order, status, created_at, updated_at)
         VALUES
            (:name, :title, :credential, :image, :alt, :specialization, :experience, :summary, :about,
             :qualifications, :specializations, :treatments, :certifications, :awards, :languages,
             :consultation, :sort_order, "published", NOW(), NOW())'
    );
    foreach ($doctors as $i => $d) {
        $d['sort_order'] = $i;
        $stmt->execute($d);
    }
    out('Seeded ' . count($doctors) . ' doctors.');
} elseif ($seedDemo) {
    out("Doctors already present ({$docCount}) — skipping.");
}

// --- 4d. Seed conditions --------------------------------------------------
// Not gated behind --seed-demo: these seven are the real, existing homepage
// content that used to live in constants/conditions.ts. Without them the
// "Conditions we support" section would render empty after the move to the DB.
// Images are left blank on purpose — the card falls back to its built-in icon
// until the client uploads one from the admin.
$condCount = (int) $pdo->query('SELECT COUNT(*) FROM conditions')->fetchColumn();
if ($condCount === 0) {
    $conditions = [
        ['Autism Spectrum Disorder Support', 'Individualized support for communication, social interaction, sensory needs, behavior, sleep, and family routines.', 'featured'],
        ['ADHD Support', 'Care focused on attention, hyperactivity, impulsivity, sleep, emotional regulation, and learning readiness.', 'standard'],
        ['Learning Disability Support', 'Guidance for children struggling with reading, writing, processing, classroom readiness, and confidence.', 'compact'],
        ['Speech Delay Support', 'Support for expressive speech, understanding, non-verbal communication, and connection alongside therapies.', 'compact'],
        ['Developmental Delay Support', 'Structured care for children whose milestones, regulation, and everyday developmental progress need support.', 'standard'],
        ['Genetic Disorders Support', 'Individualized supportive care for children with genetic and syndrome-related developmental challenges.', 'standard'],
        ['Neurological Disorders Support', 'Homeopathic support for pediatric neurological and neurodevelopmental concerns with careful monitoring.', 'standard'],
    ];
    $stmt = $pdo->prepare(
        'INSERT INTO conditions (name, description, image, alt, span, sort_order, status, created_at, updated_at)
         VALUES (?, ?, "", "", ?, ?, "published", NOW(), NOW())'
    );
    foreach ($conditions as $i => $c) {
        $stmt->execute([$c[0], $c[1], $c[2], $i]);
    }
    out('Seeded ' . count($conditions) . ' conditions.');
} else {
    out("Conditions already present ({$condCount}) — skipping.");
}

// --- Ensure uploads dir exists -------------------------------------------
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0775, true);
    out('Created uploads directory.');
}

out('');
out('Migration complete. Start the PHP server with:');
out('  php -S localhost:8080 -t core-php core-php/router.php');
out('Then open the admin at http://localhost:8080/samvedna  (or /samvedna via the Next dev server).');
