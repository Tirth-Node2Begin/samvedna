<?php
/**
 * Plugin Name:       Samvedna Landing
 * Plugin URI:        https://autismhomeohelp.com/
 * Description:       Adds the Samvedna Homeopathy landing page (custom header, all sections, animations, footer, and the consultation form) as a single selectable page template. It never changes the active theme and its styles/scripts load ONLY on the assigned page, so no other page on the site is affected.
 * Version:           1.0.16
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            Samvedna
 * License:           GPL-2.0-or-later
 * Text Domain:       samvedna-landing
 *
 * @package Samvedna_Landing
 */

defined( 'ABSPATH' ) || exit;

define( 'SVL_VERSION', '1.0.16' );
define( 'SVL_DIR', plugin_dir_path( __FILE__ ) );   // trailing slash
define( 'SVL_URL', plugin_dir_url( __FILE__ ) );    // trailing slash
define( 'SVL_TEMPLATE', 'samvedna-landing' );       // page-template key stored on the page

/*
 * The bundled render code (copied from the theme) expects these constants.
 * Point them at THIS plugin so images/assets resolve from here.
 */
if ( ! defined( 'SAMVEDNA_VERSION' ) ) { define( 'SAMVEDNA_VERSION', SVL_VERSION ); }
if ( ! defined( 'SAMVEDNA_DIR' ) )     { define( 'SAMVEDNA_DIR', untrailingslashit( SVL_DIR ) ); }
if ( ! defined( 'SAMVEDNA_URI' ) )     { define( 'SAMVEDNA_URI', untrailingslashit( SVL_URL ) ); }

/* PHP 8 polyfill so the bundle also survives an older PHP 7.x host. */
if ( ! function_exists( 'str_starts_with' ) ) {
	function str_starts_with( $haystack, $needle ) {
		return '' === $needle || 0 === strncmp( $haystack, $needle, strlen( $needle ) );
	}
}

/*
 * Load the bundled render helpers. Guarded so that if this site ever ALSO runs
 * the real Samvedna theme/plugin, we don't redeclare their functions.
 */
if ( ! function_exists( 'samvedna_option' ) ) {
	require_once SVL_DIR . 'inc/defaults.php';       // baked-in content
	require_once SVL_DIR . 'inc/helpers.php';        // field/content helpers (graceful without ACF)
	require_once SVL_DIR . 'inc/content.php';        // content resolvers -> defaults
	require_once SVL_DIR . 'inc/template-tags.php';  // icons, reveals, buttons
}
require_once SVL_DIR . 'inc/form.php';               // consultation form handler + storage (guarded inside)

/**
 * Plugin-local replacement for get_template_part() used inside the bundled files.
 * Mirrors WordPress behaviour: the partial receives $args in scope.
 *
 * @param string     $slug Relative slug, e.g. 'header' or 'template-parts/sections/hero'.
 * @param string|null $name Optional variant name.
 * @param array      $args Data passed to the partial.
 */
function svl_part( $slug, $name = null, $args = array() ) {
	$file = SVL_DIR . $slug . ( $name ? "-{$name}" : '' ) . '.php';
	if ( file_exists( $file ) ) {
		include $file; // $args is intentionally in scope for the partial.
	}
}

/**
 * Is the current request our landing page (a page using the Samvedna template)?
 *
 * @return bool
 */
function svl_is_landing() {
	if ( ! is_page() ) {
		return false;
	}
	$id = get_queried_object_id();
	return $id && get_page_template_slug( $id ) === SVL_TEMPLATE;
}

/**
 * Convert an INR price string (e.g. "₹14,999") to an approximate whole-dollar USD
 * amount for the pricing cards. This is the server-side fallback; the pricing
 * section also refines it to the live rate in JS. Adjust the rate with the
 * 'svl_usd_rate' filter (INR per 1 USD).
 *
 * @param string $inr Price string containing the rupee amount.
 * @return string Formatted USD number (e.g. "177"), or '' if no digits found.
 */
function svl_inr_to_usd( $inr ) {
	$digits = preg_replace( '/[^0-9]/', '', (string) $inr );
	if ( '' === $digits ) {
		return '';
	}
	$rate = (float) apply_filters( 'svl_usd_rate', 85 ); // INR per 1 USD.
	if ( $rate <= 0 ) {
		return '';
	}
	return number_format( (int) round( (int) $digits / $rate ) );
}

/**
 * Register "Samvedna Landing" in the page Template dropdown.
 */
add_filter( 'theme_page_templates', function ( $templates ) {
	$templates[ SVL_TEMPLATE ] = __( 'Samvedna Landing', 'samvedna-landing' );
	return $templates;
} );

/**
 * When a page uses our template, render it from the plugin instead of the theme.
 */
add_filter( 'template_include', function ( $template ) {
	if ( svl_is_landing() ) {
		return SVL_DIR . 'render-landing.php';
	}
	return $template;
} );

/**
 * Load the landing's CSS/JS — ONLY on the landing page, so other pages are untouched.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! svl_is_landing() ) {
		return;
	}

	// Fonts (Inter / Inter Tight / DM Sans — same as the design).
	wp_enqueue_style(
		'samvedna-fonts',
		'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800;900&family=Inter+Tight:wght@500;600;700;800&display=swap',
		array(),
		null
	);

	// Compiled Tailwind stylesheet.
	wp_enqueue_style( 'samvedna-app', SVL_URL . 'assets/css/app.css', array( 'samvedna-fonts' ), SVL_VERSION );

	// Vendor + theme scripts (self-hosted, in the footer).
	wp_enqueue_script( 'gsap', SVL_URL . 'assets/js/vendor/gsap.min.js', array(), '3.13.0', true );
	wp_enqueue_script( 'gsap-scrolltrigger', SVL_URL . 'assets/js/vendor/ScrollTrigger.min.js', array( 'gsap' ), '3.13.0', true );
	wp_enqueue_script( 'lenis', SVL_URL . 'assets/js/vendor/lenis.min.js', array(), '1.1.18', true );
	wp_enqueue_script( 'samvedna-main', SVL_URL . 'assets/js/main.js', array( 'gsap', 'gsap-scrolltrigger', 'lenis' ), SVL_VERSION, true );

	// Data for the AJAX consultation form. Redirect defaults to "stay + show success".
	wp_localize_script( 'samvedna-main', 'samvednaData', array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'samvedna_consultation' ),
		'action'     => 'samvedna_submit_consultation',
		'redirect'   => apply_filters( 'svl_form_redirect', array( 'url' => null, 'external' => false, 'delayMs' => 2200 ) ),
		'popupDelay' => (int) apply_filters( 'svl_popup_delay_ms', 12000 ),
	) );
}, 100 );

/**
 * Preconnect to Google Fonts on the landing page for faster first paint.
 */
add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && svl_is_landing() ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

/**
 * Best-effort isolation: drop the active theme's main stylesheet on the landing
 * page so it can't fight the Tailwind design. Extend via the 'svl_dequeue_styles'
 * filter if a specific theme/builder handle still bleeds through.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! svl_is_landing() ) {
		return;
	}
	$slug    = get_stylesheet();
	$parent  = get_template();
	$handles = array( $slug, $parent, $slug . '-style', $parent . '-style', $slug . '-theme', 'theme-style', 'style' );
	$handles = apply_filters( 'svl_dequeue_styles', $handles );
	foreach ( array_unique( array_filter( $handles ) ) as $h ) {
		wp_dequeue_style( $h );
	}
}, 999 );

/**
 * This template renders the WHOLE document from the plugin (there is no Elementor
 * content on the landing page), so any OTHER stylesheet enqueued site-wide — the
 * page builders (Elementor, ElementsKit, Jeg Elementor Kit), their kits, and the
 * active theme — can only fight the Tailwind design and win specificity battles.
 *
 * Drop every stylesheet except our own + fonts + the admin bar + the GTranslate
 * language switcher, so the landing page renders purely from the Samvedna CSS and
 * looks exactly like the design. Runs last so it also catches builder styles.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! svl_is_landing() ) {
		return;
	}

	$keep = apply_filters( 'svl_keep_styles', array(
		'samvedna-app',
		'samvedna-fonts',
		'admin-bar',
		'dashicons',
	) );

	global $wp_styles;
	if ( ! ( $wp_styles instanceof WP_Styles ) ) {
		return;
	}

	foreach ( (array) $wp_styles->queue as $handle ) {
		if ( in_array( $handle, $keep, true ) ) {
			continue;
		}
		// Keep the GTranslate language switcher styled.
		if ( false !== stripos( $handle, 'translate' ) || false !== stripos( $handle, 'gtranslate' ) || false !== stripos( $handle, 'gt_' ) ) {
			continue;
		}
		wp_dequeue_style( $handle );
	}
}, PHP_INT_MAX );

/**
 * Create the inquiries table on activation (so the consultation form can store leads).
 */
register_activation_hook( __FILE__, function () {
	if ( function_exists( 'svl_create_inquiries_table' ) ) {
		svl_create_inquiries_table();
	}
} );
