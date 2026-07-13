<?php
/**
 * Samvedna Homeopathy theme bootstrap.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SAMVEDNA_VERSION' ) ) {
	define( 'SAMVEDNA_VERSION', '1.0.0' );
}
if ( ! defined( 'SAMVEDNA_DIR' ) ) {
	define( 'SAMVEDNA_DIR', get_template_directory() );
}
if ( ! defined( 'SAMVEDNA_URI' ) ) {
	define( 'SAMVEDNA_URI', get_template_directory_uri() );
}

/**
 * Load theme modules.
 */
require_once SAMVEDNA_DIR . '/inc/setup.php';      // Theme supports, menus, image sizes.
require_once SAMVEDNA_DIR . '/inc/enqueue.php';    // Styles & scripts.
require_once SAMVEDNA_DIR . '/inc/defaults.php';   // Baked-in content (mirrors the Next.js constants).
require_once SAMVEDNA_DIR . '/inc/helpers.php';    // Field/content helpers with graceful fallbacks.
require_once SAMVEDNA_DIR . '/inc/content.php';    // Content resolvers (custom post types -> defaults).
require_once SAMVEDNA_DIR . '/inc/template-tags.php'; // Markup helpers (reveals, animated text, icons).
require_once SAMVEDNA_DIR . '/inc/seo.php';        // <head> meta + JSON-LD (skips if an SEO plugin is active).
