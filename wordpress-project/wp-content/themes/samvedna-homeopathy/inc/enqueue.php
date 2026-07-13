<?php
/**
 * Front-end styles & scripts.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting version based on file mtime (falls back to theme version).
 *
 * @param string $rel_path Path relative to the theme root.
 * @return string
 */
function samvedna_asset_ver( $rel_path ) {
	$file = SAMVEDNA_DIR . '/' . ltrim( $rel_path, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : SAMVEDNA_VERSION;
}

/**
 * Enqueue front-end assets.
 */
function samvedna_enqueue_assets() {
	// --- Fonts (Google Fonts, matching the original Inter / Inter Tight / DM Sans) ---
	wp_enqueue_style(
		'samvedna-fonts',
		'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800;900&family=Inter+Tight:wght@500;600;700;800&display=swap',
		array(),
		null
	);

	// --- Compiled Tailwind stylesheet (the only theme stylesheet) ---
	wp_enqueue_style(
		'samvedna-app',
		SAMVEDNA_URI . '/assets/css/app.css',
		array( 'samvedna-fonts' ),
		samvedna_asset_ver( 'assets/css/app.css' )
	);

	// WordPress requires the registered theme stylesheet handle; load header only.
	wp_register_style( 'samvedna-style', get_stylesheet_uri(), array(), SAMVEDNA_VERSION );

	// --- Vendor scripts: GSAP + ScrollTrigger + Lenis (self-hosted, no CDN) ---
	wp_enqueue_script( 'gsap', SAMVEDNA_URI . '/assets/js/vendor/gsap.min.js', array(), '3.13.0', true );
	wp_enqueue_script( 'gsap-scrolltrigger', SAMVEDNA_URI . '/assets/js/vendor/ScrollTrigger.min.js', array( 'gsap' ), '3.13.0', true );
	wp_enqueue_script( 'lenis', SAMVEDNA_URI . '/assets/js/vendor/lenis.min.js', array(), '1.1.18', true );

	// --- Theme script ---
	wp_enqueue_script(
		'samvedna-main',
		SAMVEDNA_URI . '/assets/js/main.js',
		array( 'gsap', 'gsap-scrolltrigger', 'lenis' ),
		samvedna_asset_ver( 'assets/js/main.js' ),
		true
	);

	// Data passed to the consultation form handler.
	wp_localize_script( 'samvedna-main', 'samvednaData', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'samvedna_consultation' ),
		'action'      => 'samvedna_submit_consultation',
		'redirect'    => samvedna_form_redirect(),
		'popupDelay'  => (int) samvedna_option( 'popup_delay_ms', 12000 ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'samvedna_enqueue_assets' );

/**
 * Preconnect to Google Fonts for faster first paint.
 */
function samvedna_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'samvedna_resource_hints', 10, 2 );

/**
 * Resolve the post-submit redirect configuration for the consultation form.
 *
 * @return array{url:?string,external:bool,delayMs:int}
 */
function samvedna_form_redirect() {
	$mode     = samvedna_option( 'form_redirect_mode', 'thankYou' );
	$delay    = (int) samvedna_option( 'form_redirect_delay_ms', 2200 );
	$appt     = samvedna_option( 'appointment_url', 'https://autismhomeohelp.com/online-consulting/' );
	$whatsapp = samvedna_contact( 'whatsapp_href', 'https://wa.me/917874876777' );
	$custom   = samvedna_option( 'form_redirect_url', '' );

	switch ( $mode ) {
		case 'none':
			return array( 'url' => null, 'external' => false, 'delayMs' => $delay );
		case 'appointment':
			return array( 'url' => $appt, 'external' => true, 'delayMs' => $delay );
		case 'whatsapp':
			return array( 'url' => $whatsapp, 'external' => true, 'delayMs' => $delay );
		case 'custom':
			return array( 'url' => $custom ? $custom : null, 'external' => true, 'delayMs' => $delay );
		case 'thankYou':
		default:
			$page = get_page_by_path( 'thank-you' );
			$url  = $page ? get_permalink( $page ) : home_url( '/thank-you/' );
			return array( 'url' => $url, 'external' => false, 'delayMs' => $delay );
	}
}
