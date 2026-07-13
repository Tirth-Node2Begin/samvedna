<?php
/**
 * Front page — the single long landing page (ports app/page.tsx).
 *
 * Sections render in the same order as the Next.js project. The footer and the
 * timed consultation popup live in footer.php so they appear site-wide.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sections = array(
	'hero',
	'doctor-intro',
	'doctor-scroll-avatar',
	'trust-bar',
	'conditions',
	'doctor-achievements',
	'why-families-trust',
	'treatment-journey',
	'medical-team',
	'international-reach',
	'video-testimonials',
	'blogs',
	'pricing',
	'final-cta',
	'faq',
);

foreach ( $sections as $section ) {
	get_template_part( 'template-parts/sections/' . $section );
}

get_footer();
