<?php
/**
 * Baked-in default content.
 *
 * Mirrors the original Next.js `constants/*` and `lib/utils` data so the theme
 * renders pixel-perfect out of the box — before any ACF fields or custom posts
 * are created. Templates read these via the helpers in inc/helpers.php and fall
 * back here whenever a dynamic source is empty.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact details (lib/utils.ts `contact`).
 */
function samvedna_default_contact() {
	return array(
		'phone_primary'   => '+91-78748-76777',
		'phone_secondary' => '+91-98986-48777',
		'phone_href'      => 'tel:+917874876777',
		'whatsapp_href'   => 'https://wa.me/917874876777',
		'email'           => 'samvedna.helpdesk@gmail.com',
		'address'         => '261, The Galleria Shopping Hub, Sanjeevkumar Auditorium Road, Pal, Surat, Gujarat 395009, India',
		'hours'           => 'Monday to Saturday, 10 am to 7 pm',
	);
}

/**
 * Primary navigation (constants/site.ts `navItems`).
 */
function samvedna_default_nav_items() {
	return array(
		array( 'label' => 'HOME', 'href' => '#home' ),
		array( 'label' => 'CONDITIONS', 'href' => '#conditions' ),
		array( 'label' => 'ABOUT DOCTOR', 'href' => '#doctors' ),
		array( 'label' => 'CARE PROCESS', 'href' => '#journey' ),
		array( 'label' => 'PARENT STORIES', 'href' => '#testimonials' ),
		array( 'label' => 'FAQ', 'href' => '#faq' ),
		array( 'label' => 'CONTACT', 'href' => '#consultation' ),
	);
}

/**
 * Social links (constants/site.ts `socialLinks`).
 */
function samvedna_default_social_links() {
	return array(
		array( 'label' => 'Instagram', 'href' => 'https://www.instagram.com/samvedna_homeopathy/' ),
		array( 'label' => 'YouTube', 'href' => 'https://www.youtube.com/user/drkrunalkosada' ),
		array( 'label' => 'Facebook', 'href' => 'https://www.facebook.com/61559641756389/' ),
		array( 'label' => 'LinkedIn', 'href' => 'https://in.linkedin.com/in/dr-krunal-kosada-7459511b' ),
	);
}

/**
 * Trust metrics shown in the trust bar (constants/site.ts `trustMetrics`).
 */
function samvedna_default_trust_metrics() {
	return array(
		array( 'label' => 'Clinical experience', 'value' => '20+ years' ),
		array( 'label' => 'Patients treated', 'value' => '10,000+' ),
		array( 'label' => 'Autism cases managed', 'value' => '1,000+' ),
		array( 'label' => 'Online consultations', 'value' => 'Worldwide' ),
	);
}

/**
 * "Why families trust Samvedna" reasons (constants/site.ts `trustReasons`).
 */
function samvedna_default_trust_reasons() {
	return array(
		array( 'title' => 'Personalized Treatment Plans', 'description' => "Every plan begins with the child's developmental history, behavior, speech, sensory responses, health, and parent observations." ),
		array( 'title' => 'Continuous Follow-Ups', 'description' => 'Reviews track communication, attention, sleep, behavior, learning readiness, and practical changes parents notice at home.' ),
		array( 'title' => 'Autism & ADHD Support', 'description' => 'Focused homeopathic care for autism, ADHD, speech delay, learning concerns, and related developmental challenges.' ),
		array( 'title' => 'Parent Guidance at Every Step', 'description' => 'Parents receive clear next steps, report guidance, and steady communication so the plan feels easier to follow.' ),
		array( 'title' => 'Worldwide Online Consultations', 'description' => 'Families in India and abroad can begin care through structured online consultations and follow-up support.' ),
		array( 'title' => 'Ethical Developmental Care', 'description' => 'No fixed promises or cure claims; the focus is individualized support, careful monitoring, and child-centered progress.' ),
	);
}

/**
 * Treatment journey steps (constants/site.ts `journeySteps`).
 */
function samvedna_default_journey_steps() {
	return array(
		array( 'title' => 'Parent Intake', 'description' => "Parents share the child's diagnosis, milestones, daily challenges, therapy history, reports, and current concerns." ),
		array( 'title' => 'Detailed Assessment', 'description' => 'The clinical team studies developmental, behavioral, emotional, sensory, speech, sleep, and health patterns.' ),
		array( 'title' => 'Personalized Plan', 'description' => "A homeopathic care plan is selected around the child's individual presentation, not only the diagnostic label." ),
		array( 'title' => 'Medicine & Guidance', 'description' => 'Parents receive medicine instructions, practical guidance, and coordination for local or distance care.' ),
		array( 'title' => 'Continuous Follow-Ups', 'description' => 'Follow-ups review response, concerns, parent observations, and changes seen at home, therapy, or school.' ),
		array( 'title' => 'Progress Refinement', 'description' => 'The plan is adjusted as communication, attention, sleep, behavior, learning readiness, and routines evolve.' ),
	);
}

/**
 * Reach countries (constants/site.ts `reachCountries`).
 */
function samvedna_default_reach_countries() {
	return array( 'India', 'UAE', 'UK', 'USA', 'Canada', 'Australia', 'Singapore', 'Germany', 'New Zealand', 'South Africa', 'Kuwait' );
}

/**
 * Condition list used by the form select (constants/conditions.ts `conditionList`).
 */
function samvedna_condition_list() {
	return array(
		'Autism Spectrum Disorder Support',
		'ADHD Support',
		'Learning Disability Support',
		'Speech Delay Support',
		'Developmental Delay Support',
		'Genetic Disorders Support',
		'Neurological Disorders Support',
	);
}

/**
 * Conditions treated, with bento layout + icon metadata (constants/conditions.ts).
 *
 * `icon` keys map to the inline SVGs in samvedna_icon() (inc/template-tags.php).
 */
function samvedna_default_conditions() {
	return array(
		array( 'name' => 'Autism Spectrum Disorder Support', 'description' => 'Individualized support for communication, social interaction, sensory needs, behavior, sleep, and family routines.', 'icon' => 'brain', 'span' => 'md:col-span-2 lg:col-span-2', 'variant' => 'dark' ),
		array( 'name' => 'ADHD Support', 'description' => 'Care focused on attention, hyperactivity, impulsivity, sleep, emotional regulation, and learning readiness.', 'icon' => 'activity', 'span' => 'md:col-span-1 lg:col-span-1', 'variant' => 'light' ),
		array( 'name' => 'Learning Disability Support', 'description' => 'Guidance for children struggling with reading, writing, processing, classroom readiness, and confidence.', 'icon' => 'book-open', 'span' => 'md:col-span-1 lg:col-span-1', 'variant' => 'soft' ),
		array( 'name' => 'Speech Delay Support', 'description' => 'Support for expressive speech, understanding, non-verbal communication, and connection alongside therapies.', 'icon' => 'message-circle', 'span' => 'md:col-span-1 lg:col-span-1', 'variant' => 'soft' ),
		array( 'name' => 'Developmental Delay Support', 'description' => 'Structured care for children whose milestones, regulation, and everyday developmental progress need support.', 'icon' => 'sprout', 'span' => 'md:col-span-1 lg:col-span-1', 'variant' => 'light' ),
		array( 'name' => 'Genetic Disorders Support', 'description' => 'Individualized supportive care for children with genetic and syndrome-related developmental challenges.', 'icon' => 'sparkles', 'span' => 'md:col-span-1 lg:col-span-1', 'variant' => 'light' ),
		array( 'name' => 'Neurological Disorders Support', 'description' => 'Homeopathic support for pediatric neurological and neurodevelopmental concerns with careful monitoring.', 'icon' => 'heart-pulse', 'span' => 'md:col-span-1 lg:col-span-1', 'variant' => 'dark' ),
	);
}

/**
 * FAQ items (constants/faq.ts).
 */
function samvedna_default_faq() {
	return array(
		array( 'question' => 'Can homeopathy cure autism?', 'answer' => "Samvedna Homeopathy does not claim to cure Autism Spectrum Disorder. The aim is to support the child's overall development, including communication, behavior, emotional regulation, attention, sleep, and associated concerns. Every child responds differently, so outcomes are reviewed carefully over time." ),
		array( 'question' => 'Which child development concerns does Samvedna support?', 'answer' => "Samvedna provides homeopathic support for Autism Spectrum Disorder, ADHD, Learning Disability, Speech Delay, Developmental Delay, Genetic Disorders, and Neurological Disorders. Each plan is personalized after understanding the child's full developmental and health picture." ),
		array( 'question' => 'What age is best to start treatment for autism or ADHD?', 'answer' => 'Early intervention is always preferable because younger children have more developmental opportunity. Children at different ages may still benefit from individualized care, especially when the plan is consistent and reviewed regularly.' ),
		array( 'question' => 'Can my child continue speech therapy or occupational therapy?', 'answer' => 'Yes. Homeopathic care can generally be taken alongside speech therapy, occupational therapy, behavioral therapy, special education, physiotherapy, and conventional medical care. Samvedna encourages an integrated approach when it serves the child.' ),
		array( 'question' => 'How long does treatment take for neurodevelopmental disorders?', 'answer' => "Neurodevelopmental conditions usually require patience and consistent follow-up. Duration depends on the child's age, severity, associated concerns, prior therapies, and individual responsiveness. Progress is monitored through follow-up visits and parent observations." ),
		array( 'question' => 'What improvements can parents realistically expect?', 'answer' => 'Parents may seek support for eye contact, speech, attention, sleep, hyperactivity, irritability, digestion, social interaction, and learning readiness. Improvement varies from child to child, and no ethical medical practice can guarantee a fixed result.' ),
		array( 'question' => 'Are homeopathic medicines safe for children?', 'answer' => "When prescribed by a qualified homeopathic physician, homeopathic medicines are commonly used in pediatric practice. Samvedna evaluates the child's full health history before prescribing and reviews the response during follow-ups." ),
		array( 'question' => 'Do you help children who are non-verbal?', 'answer' => "Many families consult Samvedna for children with significant speech delay or non-verbal autism. Treatment focuses on the child's overall developmental functioning and associated concerns. The plan is individualized and reviewed alongside therapy progress where relevant." ),
		array( 'question' => 'Will my child need to stop existing medicines?', 'answer' => 'No prescribed medicine should be stopped without speaking to the treating physician. Homeopathic care is often planned alongside ongoing medical care, therapies, and school support.' ),
		array( 'question' => 'Do you provide online consultations outside India?', 'answer' => 'Yes. Samvedna provides tele-consultations for families in India and internationally. The process includes history taking, assessment, treatment planning, follow-up scheduling, and guidance for medicine dispatch where available.' ),
		array( 'question' => 'What should parents bring for the first consultation?', 'answer' => "Parents should bring developmental assessments, therapy reports, medical records, investigation reports, current medicine details, and short videos that show the child's communication, behavior, play, sleep, or daily routine. These details help the doctor understand the child more completely." ),
	);
}

/**
 * Doctor achievements with display metadata (constants/achievements.ts + DoctorAchievements meta).
 */
function samvedna_default_achievements() {
	return array(
		array( 'title' => 'Guest Speaker in Greece', 'event' => 'Hellenic Homeopathic Medical Society', 'description' => 'Invited on multiple occasions to share clinical experience in neurodevelopmental and neurological disorders through homeopathy.', 'icon' => 'trophy', 'location' => 'Greece', 'icon_bg' => 'bg-primary text-white shadow-md shadow-primary/20' ),
		array( 'title' => 'Key Presenter in Dubai', 'event' => '3rd International AYUSH Exhibition & Conference', 'description' => 'Represented Indian homeopathy on an international platform before a global healthcare audience.', 'icon' => 'mic', 'location' => 'Dubai', 'icon_bg' => 'bg-slate-800 text-white' ),
		array( 'title' => 'President, HMAI Surat Unit', 'event' => 'Homoeopathic Medical Association of India', 'description' => 'Leads regional academic initiatives, professional development, and awareness for homeopathic practice.', 'icon' => 'users', 'location' => 'India', 'icon_bg' => 'bg-slate-800 text-white' ),
		array( 'title' => 'Global Scientific Presenter', 'event' => 'LMHI Congresses, JAHC San Antonio, and other forums', 'description' => 'Presented scientific papers and clinical work across respected international homeopathic conferences.', 'icon' => 'globe', 'location' => 'Global', 'icon_bg' => 'bg-slate-800 text-white' ),
		array( 'title' => 'Key Speaker in Advanced Homeopathy', 'event' => '6th International Seminar of Advanced Homeopathic Studies, Greece', 'description' => 'Recognized internationally for clinical expertise among homeopathic physicians and researchers.', 'icon' => 'book-open', 'location' => 'Greece', 'icon_bg' => 'bg-slate-800 text-white' ),
	);
}

/**
 * Medical team members (constants/team.ts). Image keys resolve to assets/images/.
 */
function samvedna_default_team() {
	return array(
		array(
			'name'            => 'Dr. Krishna Thakor',
			'title'           => 'Senior Consultant',
			'image'           => 'samvedna-associate-portrait.webp',
			'alt'             => 'Dr. Krishna Thakor, consultant for child neurodevelopment care',
			'specialization'  => 'Case Coordination & Follow-up Care',
			'experience'      => '12+ Years',
			'summary'         => 'Coordinates long-term care and follow-ups so families stay supported between consultations.',
			'qualifications'  => array( 'BHMS' ),
			'about'           => 'Dr. Krishna Thakor focuses on case coordination and long-term follow-up, helping families stay supported between visits with clear next steps and steady communication.',
			'specializations' => array( 'Long-term case coordination', 'Follow-up and progress monitoring', 'Family guidance' ),
			'treatments'      => array( 'Follow-up consultations', 'Care-plan monitoring', 'Parent communication and support' ),
			'languages'       => array( 'English', 'Hindi', 'Gujarati' ),
			'consultation'    => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
		),
		array(
			'name'            => 'Dr. Yakshika',
			'title'           => 'Consultant',
			'image'           => 'dr-yakshika.jpg',
			'alt'             => 'Dr. Yakshika, consultant for child neurodevelopment care',
			'specialization'  => 'Developmental Care & Family Guidance',
			'experience'      => '6+ Years',
			'summary'         => 'Supports developmental care and guides parents through assessments and daily routines.',
			'qualifications'  => array( 'BHMS' ),
			'about'           => "Dr. Yakshika supports children's developmental care and works closely with parents through assessments, routines and ongoing guidance.",
			'specializations' => array( 'Developmental care', 'Assessment support', 'Family guidance' ),
			'treatments'      => array( 'Developmental assessments', 'Supportive homeopathic care', 'Parent guidance' ),
			'languages'       => array( 'English', 'Hindi', 'Gujarati' ),
			'consultation'    => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
		),
		array(
			'name'            => 'Medical Team Member',
			'title'           => 'Consultant',
			'image'           => 'member-3.jpg',
			'alt'             => 'Medical Team Member, consultant for child neurodevelopment care',
			'specialization'  => 'Consultation & Coordination',
			'experience'      => '5+ Years',
			'summary'         => 'Assists with consultations, coordination and day-to-day family support across cases.',
			'qualifications'  => array( 'BHMS' ),
			'about'           => 'A consultant on the Samvedna team supporting consultations, coordination and day-to-day family guidance across cases.',
			'specializations' => array( 'Consultation support', 'Case coordination', 'Family guidance' ),
			'treatments'      => array( 'Supportive consultations', 'Care coordination', 'Parent guidance' ),
			'languages'       => array( 'English', 'Hindi', 'Gujarati' ),
			'consultation'    => 'Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment.',
		),
	);
}

/**
 * Sample blog posts (constants/blogs.ts). Used only when no WP posts exist yet.
 */
function samvedna_default_blogs() {
	return array(
		array( 'slug' => 'understanding-autism-first-guide', 'title' => "Understanding Autism Spectrum Disorder: A Parent's First Guide", 'excerpt' => 'What the early signs mean, how assessment works, and the calm first steps families can take.', 'image' => 'samvedna-auditorium.webp', 'alt' => 'Samvedna Homeopathy consultation space', 'category' => 'Autism', 'date' => 'May 12, 2026', 'read_time' => '6 min read' ),
		array( 'slug' => 'homeopathy-and-speech-delay', 'title' => 'How Homeopathy Supports Children With Speech Delay', 'excerpt' => 'A look at individualized care that works alongside speech therapy and daily home routines.', 'image' => 'assistant-doctor-cabin.webp', 'alt' => 'Doctor consultation cabin at Samvedna Homeopathy', 'category' => 'Speech', 'date' => 'Apr 28, 2026', 'read_time' => '5 min read' ),
		array( 'slug' => 'adhd-daily-routines', 'title' => 'ADHD and Daily Routines: Practical Tips for Families', 'excerpt' => 'Small, consistent routines that help with attention, sleep and emotional regulation at home.', 'image' => 'dr-krunal-kosada.webp', 'alt' => 'Dr. Krunal Kosada', 'category' => 'ADHD', 'date' => 'Apr 9, 2026', 'read_time' => '4 min read' ),
		array( 'slug' => 'first-consultation-what-to-expect', 'title' => 'What to Expect in Your First Consultation', 'excerpt' => 'From sharing reports to building the first care plan — a clear walkthrough of the visit.', 'image' => 'samvedna-associate-portrait.webp', 'alt' => 'Samvedna Homeopathy consultant', 'category' => 'Care Process', 'date' => 'Mar 22, 2026', 'read_time' => '5 min read' ),
		array( 'slug' => 'nutrition-and-sleep-foundations', 'title' => 'Nutrition and Sleep: Foundations for Developmental Care', 'excerpt' => 'Why steady sleep and nutrition matter, and gentle changes parents can begin this week.', 'image' => 'dr-yakshika.jpg', 'alt' => 'Dr. Yakshika, consultant at Samvedna Homeopathy', 'category' => 'Wellbeing', 'date' => 'Mar 5, 2026', 'read_time' => '6 min read' ),
		array( 'slug' => 'tracking-progress-follow-ups', 'title' => 'Tracking Progress: Why Follow-ups Matter', 'excerpt' => 'How continuous follow-ups turn small observations into a plan that keeps improving.', 'image' => 'member-3.jpg', 'alt' => 'Samvedna Homeopathy medical team member', 'category' => 'Follow-up', 'date' => 'Feb 18, 2026', 'read_time' => '4 min read' ),
	);
}

/**
 * Parent video stories (constants/videoTestimonials.ts).
 */
function samvedna_default_video_testimonials() {
	return array(
		array( 'youtube_id' => '', 'poster' => 'assistant-doctor-cabin.webp', 'alt' => "Parent sharing their child's autism care journey", 'name' => 'Parent family', 'condition' => 'Autism support', 'location' => 'Canada', 'duration' => '2:10' ),
		array( 'youtube_id' => '', 'poster' => 'samvedna-associate-portrait.webp', 'alt' => 'Parent describing speech delay progress', 'name' => 'Parent family', 'condition' => 'Speech delay support', 'location' => 'India', 'duration' => '1:48' ),
		array( 'youtube_id' => '', 'poster' => 'dr-yakshika.jpg', 'alt' => 'Parent talking about developmental delay care', 'name' => 'Parent family', 'condition' => 'Developmental delay support', 'location' => 'India', 'duration' => '2:35' ),
		array( 'youtube_id' => '', 'poster' => 'member-3.jpg', 'alt' => 'Parent sharing their ADHD support experience', 'name' => 'Parent family', 'condition' => 'ADHD support', 'location' => 'India', 'duration' => '1:55' ),
		array( 'youtube_id' => '', 'poster' => 'samvedna-auditorium.webp', 'alt' => 'Parent reflecting on continuous follow-up care', 'name' => 'Parent family', 'condition' => 'Learning support', 'location' => 'India', 'duration' => '2:22' ),
		array( 'youtube_id' => '', 'poster' => 'dr-krunal-kosada.webp', 'alt' => 'Parent thanking the care team', 'name' => 'Parent family', 'condition' => 'Behavioral support', 'location' => 'United Kingdom', 'duration' => '1:40' ),
	);
}

/**
 * Care plans / pricing (components/sections/Pricing.tsx `plans`).
 */
function samvedna_default_plans() {
	return array(
		array(
			'name'     => 'Starter (Trial)',
			'tagline'  => 'Safe start to experience structured Samvedna care.',
			'duration' => '2 months',
			'price'    => '₹14,999',
			'features' => array( 'Medicines for 2 months', '1 Bi-Monthly Review (60-day review)', 'Written instructions for medicines & routines', 'WhatsApp support (≤48h response)' ),
			'popular'  => false,
			'cta'      => 'Start with 2 Months',
		),
		array(
			'name'     => 'Standard',
			'tagline'  => 'Balanced, evidence-led care with two-doctor oversight.',
			'duration' => '6 months',
			'price'    => '₹39,999',
			'features' => array( 'Medicines for 6 months', 'Every 2 months (3 sessions)', 'Case + Senior doctor oversight', 'Progress dashboard', 'Therapy coordination (1 call/cycle)', 'Priority slots', 'WhatsApp support (24–36h response)' ),
			'popular'  => true,
			'cta'      => 'Choose Standard Plan',
		),
		array(
			'name'     => 'Premium',
			'tagline'  => 'High-intensity supervision with founder review.',
			'duration' => '6 months',
			'price'    => '₹54,999',
			'features' => array( 'Medicines for 6 months', 'Monthly follow-ups', 'Case + Senior + Founder review', 'Same-day support', 'Custom tweaks', 'Founder Q&A webinar access' ),
			'popular'  => false,
			'cta'      => 'Apply for Premium',
		),
	);
}

/**
 * Hero stats (constants/site.ts `heroStats`).
 */
function samvedna_default_hero_stats() {
	return array(
		array( 'value' => '20+', 'label' => 'Years Experience', 'detail' => 'Focused child developmental care' ),
		array( 'value' => '10,000+', 'label' => 'Patients Treated', 'detail' => 'Across pediatric and family care' ),
		array( 'value' => '1,000+', 'label' => 'Autism Cases', 'detail' => 'Managed with ongoing follow-up' ),
		array( 'value' => 'Worldwide', 'label' => 'Online Consultations', 'detail' => 'For India and international families' ),
	);
}

/**
 * Orbiting countries on the international-reach world map (components/ui/WorldMap.tsx).
 */
function samvedna_default_map_planets() {
	return array(
		array( 'name' => 'India', 'code' => 'in', 'radius' => 140, 'angle' => 0, 'speed' => 30, 'direction' => 1 ),
		array( 'name' => 'UAE', 'code' => 'ae', 'radius' => 140, 'angle' => 180, 'speed' => 30, 'direction' => 1 ),
		array( 'name' => 'UK', 'code' => 'gb', 'radius' => 210, 'angle' => 60, 'speed' => 40, 'direction' => -1 ),
		array( 'name' => 'Singapore', 'code' => 'sg', 'radius' => 210, 'angle' => 180, 'speed' => 40, 'direction' => -1 ),
		array( 'name' => 'USA', 'code' => 'us', 'radius' => 210, 'angle' => 300, 'speed' => 40, 'direction' => -1 ),
		array( 'name' => 'Canada', 'code' => 'ca', 'radius' => 280, 'angle' => 0, 'speed' => 55, 'direction' => 1 ),
		array( 'name' => 'Australia', 'code' => 'au', 'radius' => 280, 'angle' => 120, 'speed' => 55, 'direction' => 1 ),
		array( 'name' => 'Germany', 'code' => 'de', 'radius' => 280, 'angle' => 240, 'speed' => 55, 'direction' => 1 ),
	);
}
