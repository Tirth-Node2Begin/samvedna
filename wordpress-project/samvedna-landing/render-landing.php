<?php
/**
 * Full-page renderer for the Samvedna Landing page template.
 *
 * Loaded via the plugin's `template_include` filter instead of the theme's page
 * template, so this ONE page uses the Samvedna header, sections, and footer while
 * every other page keeps rendering through the site's own theme.
 *
 * @package Samvedna_Landing
 */

defined( 'ABSPATH' ) || exit;

// Header: <!doctype>, <head> (wp_head), navbar, opens <main>.
svl_part( 'header' );

// The 14 landing sections, in the same order as the original front page.
$svl_sections = array(
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

foreach ( $svl_sections as $svl_section ) {
	svl_part( 'template-parts/sections/' . $svl_section );
}

// Footer: closes <main>, footer markup, popup + video modal, wp_footer().
svl_part( 'footer' );
