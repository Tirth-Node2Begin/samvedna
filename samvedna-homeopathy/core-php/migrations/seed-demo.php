<?php
/**
 * Demo-content seeder for blogs + faculty (doctors).
 *
 * Idempotent: upserts by blog slug / doctor name, so re-running refreshes the
 * demo rows in place instead of duplicating them. Also clears obvious gibberish
 * test rows so the demo reads cleanly.
 *
 * Run from the project root:
 *   php core-php/migrations/seed-demo.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/models/Blog.php';
require_once __DIR__ . '/../includes/models/Doctor.php';

// ---------------------------------------------------------------------------
// Blog posts — full HTML bodies (BlogArticle renders `content` as HTML).
// ---------------------------------------------------------------------------
$blogs = [
    [
        'slug'      => 'understanding-autism-first-guide',
        'title'     => "Understanding Autism Spectrum Disorder: A Parent's First Guide",
        'excerpt'   => 'What the early signs mean, how assessment works, and the calm first steps families can take.',
        'category'  => 'Autism',
        'author'    => 'Dr. Krunal Kosada',
        'image'     => '/images/samvedna-auditorium.webp',
        'alt'       => 'Samvedna Homeopathy consultation space',
        'read_time' => '6 min read',
        'published' => '2026-05-12',
        'content'   => <<<HTML
<p>Receiving the words "autism spectrum disorder" for the first time can feel overwhelming. This guide is here to slow things down and give you a calm, clear starting point.</p>
<h2>What the early signs actually mean</h2>
<p>Every child develops at their own pace, but a few patterns are worth noticing early: limited eye contact, delayed speech, repetitive movements, or strong reactions to sounds and textures. On their own, none of these confirm anything — together, over time, they are simply a signal to seek a proper assessment.</p>
<h2>How assessment works</h2>
<p>A good evaluation is never a single test. It combines developmental history, structured observation, and conversations with you about how your child plays, communicates, and responds at home. The goal is understanding, not labelling.</p>
<h2>The first calm steps</h2>
<ul>
<li>Write down what you observe — dates and specific examples help clinicians more than worry.</li>
<li>Bring any school or paediatric reports to the first consultation.</li>
<li>Focus on one routine at a time rather than changing everything at once.</li>
</ul>
<p>At Samvedna, our approach pairs individualized homeopathic care with steady family guidance, so you are never navigating this alone.</p>
HTML,
    ],
    [
        'slug'      => 'homeopathy-and-speech-delay',
        'title'     => 'How Homeopathy Supports Children With Speech Delay',
        'excerpt'   => 'A look at individualized care that works alongside speech therapy and daily home routines.',
        'category'  => 'Speech',
        'author'    => 'Dr. Krunal Kosada',
        'image'     => '/images/assistant-doctor-cabin.webp',
        'alt'       => 'Doctor consultation cabin at Samvedna Homeopathy',
        'read_time' => '5 min read',
        'published' => '2026-04-28',
        'content'   => <<<HTML
<p>Speech delay is one of the most common concerns parents bring to us — and one where consistent, individualized support makes a real difference.</p>
<h2>Homeopathy works with therapy, not instead of it</h2>
<p>We see the best outcomes when homeopathic care runs alongside speech therapy and daily practice at home. Each supports the other: therapy builds the skill, and individualized care supports the child's overall development and receptiveness.</p>
<h2>Why "individualized" matters</h2>
<p>Two children with the same delay rarely need the same plan. We look at temperament, sleep, focus and sensory profile before recommending anything, so the care fits the child rather than the diagnosis.</p>
<h2>What families can do at home</h2>
<ul>
<li>Narrate everyday actions out loud to model language naturally.</li>
<li>Give a few extra seconds for your child to respond before prompting.</li>
<li>Read together daily, even for just five minutes.</li>
</ul>
<p>Progress here is measured in small, steady wins — and we track them together at every follow-up.</p>
HTML,
    ],
    [
        'slug'      => 'adhd-daily-routines',
        'title'     => 'ADHD and Daily Routines: Practical Tips for Families',
        'excerpt'   => 'Small, consistent routines that help with attention, sleep and emotional regulation at home.',
        'category'  => 'ADHD',
        'author'    => 'Dr. Krunal Kosada',
        'image'     => '/images/dr-krunal-kosada.webp',
        'alt'       => 'Dr. Krunal Kosada',
        'read_time' => '4 min read',
        'published' => '2026-04-09',
        'content'   => <<<HTML
<p>For children with ADHD, structure is one of the kindest things a home can offer. Predictable routines reduce the number of decisions a child has to make, freeing up attention for the things that matter.</p>
<h2>Start with the anchors of the day</h2>
<p>Wake-up, meals, homework and bedtime are the anchors. Keep them at roughly the same time each day, and the hours in between become far easier to manage.</p>
<h2>Make routines visible</h2>
<p>A simple picture chart or checklist turns "get ready for school" into small, do-able steps. Children with ADHD respond well to seeing progress rather than being told it.</p>
<h2>Protect sleep</h2>
<ul>
<li>Wind down screens at least an hour before bed.</li>
<li>Keep a consistent bedtime, even on weekends.</li>
<li>Use a calm, repeatable pre-sleep routine.</li>
</ul>
<p>Alongside these routines, individualized homeopathic care can support focus and emotional regulation — always tailored to the child in front of us.</p>
HTML,
    ],
    [
        'slug'      => 'first-consultation-what-to-expect',
        'title'     => 'What to Expect in Your First Consultation',
        'excerpt'   => 'From sharing reports to building the first care plan — a clear walkthrough of the visit.',
        'category'  => 'Care Process',
        'author'    => 'Dr. Krunal Kosada',
        'image'     => '/images/samvedna-associate-portrait.webp',
        'alt'       => 'Samvedna Homeopathy consultant',
        'read_time' => '5 min read',
        'published' => '2026-03-22',
        'content'   => <<<HTML
<p>Knowing what happens in the first visit takes away a lot of the anxiety. Here is exactly how we spend that time together.</p>
<h2>We listen first</h2>
<p>The first consultation is unhurried. We ask about your child's history, development, routines, sleep, diet and temperament — the full picture, not just the presenting concern.</p>
<h2>Bring what you have</h2>
<ul>
<li>Any previous medical or developmental reports.</li>
<li>School observations, if available.</li>
<li>A short list of your own concerns and questions.</li>
</ul>
<h2>Building the first plan</h2>
<p>By the end of the visit you will leave with a clear, individualized starting plan and realistic expectations for the weeks ahead. Nothing is rushed, and every step is explained.</p>
<p>Care is a partnership — the first consultation is where that partnership begins.</p>
HTML,
    ],
    [
        'slug'      => 'nutrition-and-sleep-foundations',
        'title'     => 'Nutrition and Sleep: Foundations for Developmental Care',
        'excerpt'   => 'Why steady sleep and nutrition matter, and gentle changes parents can begin this week.',
        'category'  => 'Wellbeing',
        'author'    => 'Dr. Krunal Kosada',
        'image'     => '/images/dr-yakshika.jpg',
        'alt'       => 'Dr. Yakshika, consultant at Samvedna Homeopathy',
        'read_time' => '6 min read',
        'published' => '2026-03-05',
        'content'   => <<<HTML
<p>Before any specialised care, two foundations quietly shape a child's progress: how well they sleep and how well they eat. Get these steadier, and everything else becomes easier.</p>
<h2>Sleep sets the tone</h2>
<p>Consistent, sufficient sleep supports attention, mood and learning. A regular bedtime and a calm wind-down routine often bring visible improvements within a couple of weeks.</p>
<h2>Nutrition that supports development</h2>
<p>You do not need a dramatic overhaul. Steady meals, more whole foods, and fewer sudden sugar spikes give the body and brain a more even foundation to work from.</p>
<h2>Gentle changes to begin this week</h2>
<ul>
<li>Move bedtime 15 minutes earlier and keep it fixed.</li>
<li>Add one whole-food swap to a daily meal.</li>
<li>Keep water within easy reach through the day.</li>
</ul>
<p>Small, sustainable changes beat perfect plans that never last — that is the philosophy we bring to every care plan.</p>
HTML,
    ],
    [
        'slug'      => 'tracking-progress-follow-ups',
        'title'     => 'Tracking Progress: Why Follow-ups Matter',
        'excerpt'   => 'How continuous follow-ups turn small observations into a plan that keeps improving.',
        'category'  => 'Follow-up',
        'author'    => 'Dr. Krunal Kosada',
        'image'     => '/images/member-3.jpg',
        'alt'       => 'Samvedna Homeopathy medical team member',
        'read_time' => '4 min read',
        'published' => '2026-02-18',
        'content'   => <<<HTML
<p>The first consultation starts the journey, but follow-ups are where real, lasting progress is shaped.</p>
<h2>Small observations, big value</h2>
<p>Between visits, the little things you notice — a new word, calmer mornings, better sleep — are exactly the data we use to fine-tune the plan. Nothing is too small to mention.</p>
<h2>Care that adapts</h2>
<p>Children change, and their care should change with them. Regular follow-ups let us adjust the plan as your child grows, rather than waiting for a problem to reappear.</p>
<h2>Staying supported between visits</h2>
<ul>
<li>Keep a simple note of wins and concerns as they happen.</li>
<li>Share school or therapy feedback at each review.</li>
<li>Reach out between visits if something feels off — you are not on your own.</li>
</ul>
<p>Continuity is the quiet ingredient behind steady, meaningful improvement.</p>
HTML,
    ],
];

// ---------------------------------------------------------------------------
// Faculty / doctors — full profiles.
// ---------------------------------------------------------------------------
$doctors = [
    [
        'name'            => 'Dr. Krunal Kosada',
        'title'           => 'Founder & Lead Consultant',
        'credential'      => 'BHMS, MD (Hom.) | Neurodevelopmental & neurological disorders',
        'image'           => '/images/dr-krunal-kosada.webp',
        'alt'             => 'Dr. Krunal Kosada, Founder & Lead Consultant at Samvedna Homeopathy',
        'specialization'  => 'Autism, ADHD & Neurodevelopmental Care',
        'experience'      => '20+ Years',
        'summary'         => 'Internationally recognised homeopath specialising in autism, ADHD and neurodevelopmental care for children.',
        'about'           => "Dr. Krunal Kosada leads Samvedna Homeopathy with over two decades of clinical experience in neurodevelopmental and neurological disorders. He has presented at international congresses across Europe and the USA and is known for a patient, individualized approach that treats the child, not just the diagnosis.",
        'qualifications'  => ['BHMS', 'MD (Homeopathy)'],
        'specializations' => ['Autism Spectrum Disorder', 'ADHD & attention concerns', 'Speech and developmental delay', 'Neurological disorders'],
        'treatments'      => ['Individualized homeopathic care', 'Long-term developmental planning', 'Family guidance and counselling'],
        'certifications'  => ['President, HMAI Surat Unit', 'Member, Liga Medicorum Homoeopathica Internationalis (LMHI)'],
        'awards'          => [
            'Guest Speaker, Hellenic Homeopathic Medical Society (Greece)',
            'Key Presenter, 3rd International AYUSH Exhibition & Conference (Dubai)',
            'International Congress Presenter — LMHI Istanbul, Spain & Utrecht; JAHC, San Antonio, USA',
        ],
        'languages'       => ['English', 'Hindi', 'Gujarati'],
        'consultation'    => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
        'sort_order'      => 0,
    ],
    [
        'name'            => 'Dr. Krishna Thakor',
        'title'           => 'Senior Consultant',
        'credential'      => 'BHMS | Case coordination, long-term follow-up, and family guidance',
        'image'           => '/images/samvedna-associate-portrait.webp',
        'alt'             => 'Dr. Krishna Thakor, consultant for child neurodevelopment care',
        'specialization'  => 'Case Coordination & Follow-up Care',
        'experience'      => '12+ Years',
        'summary'         => 'Coordinates long-term care and follow-ups so families stay supported between consultations.',
        'about'           => 'Dr. Krishna Thakor focuses on case coordination and long-term follow-up, helping families stay supported between visits with clear next steps and steady communication.',
        'qualifications'  => ['BHMS'],
        'specializations' => ['Long-term case coordination', 'Follow-up and progress monitoring', 'Family guidance'],
        'treatments'      => ['Follow-up consultations', 'Care-plan monitoring', 'Parent communication and support'],
        'certifications'  => [],
        'awards'          => [],
        'languages'       => ['English', 'Hindi', 'Gujarati'],
        'consultation'    => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
        'sort_order'      => 1,
    ],
    [
        'name'            => 'Dr. Yakshika',
        'title'           => 'Consultant',
        'credential'      => 'BHMS | Developmental care and family guidance',
        'image'           => '/images/dr-yakshika.jpg',
        'alt'             => 'Dr. Yakshika, consultant for child neurodevelopment care',
        'specialization'  => 'Developmental Care & Family Guidance',
        'experience'      => '6+ Years',
        'summary'         => 'Supports developmental care and guides parents through assessments and daily routines.',
        'about'           => "Dr. Yakshika supports children's developmental care and works closely with parents through assessments, routines and ongoing guidance.",
        'qualifications'  => ['BHMS'],
        'specializations' => ['Developmental care', 'Assessment support', 'Family guidance'],
        'treatments'      => ['Developmental assessments', 'Supportive homeopathic care', 'Parent guidance'],
        'certifications'  => [],
        'awards'          => [],
        'languages'       => ['English', 'Hindi', 'Gujarati'],
        'consultation'    => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
        'sort_order'      => 2,
    ],
    [
        'name'            => 'Dr. Meera Patel',
        'title'           => 'Consultant',
        'credential'      => 'BHMS | Consultation and case coordination',
        'image'           => '/images/member-3.jpg',
        'alt'             => 'Dr. Meera Patel, consultant for child neurodevelopment care',
        'specialization'  => 'Consultation & Coordination',
        'experience'      => '5+ Years',
        'summary'         => 'Assists with consultations, coordination and day-to-day family support across cases.',
        'about'           => 'Dr. Meera Patel supports consultations, coordination and day-to-day family guidance across cases on the Samvedna team.',
        'qualifications'  => ['BHMS'],
        'specializations' => ['Consultation support', 'Case coordination', 'Family guidance'],
        'treatments'      => ['Supportive consultations', 'Care coordination', 'Parent guidance'],
        'certifications'  => [],
        'awards'          => [],
        'languages'       => ['English', 'Hindi', 'Gujarati'],
        'consultation'    => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
        'sort_order'      => 3,
    ],
];

// ---------------------------------------------------------------------------
// Upsert helpers.
// ---------------------------------------------------------------------------
function find_blog_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT id FROM blogs WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function find_doctor_by_name(string $name): ?array
{
    $stmt = db()->prepare('SELECT id FROM doctors WHERE name = ? LIMIT 1');
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    return $row ?: null;
}

$blogCreated = $blogUpdated = 0;
foreach ($blogs as $b) {
    $data = [
        'slug'         => $b['slug'],
        'title'        => $b['title'],
        'excerpt'      => $b['excerpt'],
        'content'      => $b['content'],
        'category'     => $b['category'],
        'author'       => $b['author'],
        'image'        => $b['image'],
        'alt'          => $b['alt'],
        'read_time'    => $b['read_time'],
        'status'       => 'published',
        'published_at' => $b['published'],
    ];
    $existing = find_blog_by_slug($b['slug']);
    if ($existing) {
        Blog::update((int) $existing['id'], $data);
        $blogUpdated++;
        echo "  updated blog: {$b['slug']}\n";
    } else {
        Blog::create($data);
        $blogCreated++;
        echo "  created blog: {$b['slug']}\n";
    }
}

$docCreated = $docUpdated = 0;
foreach ($doctors as $d) {
    $data = array_merge($d, ['status' => 'published']);
    $existing = find_doctor_by_name($d['name']);
    if ($existing) {
        Doctor::update((int) $existing['id'], $data);
        $docUpdated++;
        echo "  updated doctor: {$d['name']}\n";
    } else {
        Doctor::create($data);
        $docCreated++;
        echo "  created doctor: {$d['name']}\n";
    }
}

// ---------------------------------------------------------------------------
// Clear obvious gibberish test rows so the demo reads cleanly.
// ---------------------------------------------------------------------------
$junkBlogs   = db()->exec("DELETE FROM blogs WHERE slug = 'abc'");
$junkDoctors = db()->exec("DELETE FROM doctors WHERE name IN ('yashank') OR title = 'abc'");

echo "\nDone.\n";
echo "Blogs:   {$blogCreated} created, {$blogUpdated} updated, {$junkBlogs} junk removed.\n";
echo "Doctors: {$docCreated} created, {$docUpdated} updated, {$junkDoctors} junk removed.\n";
