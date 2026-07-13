<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme support flags.
 */
function samvedna_setup() {
	load_theme_textdomain( 'samvedna', SAMVEDNA_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 50,
		'width'       => 157,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'samvedna' ),
		'footer'  => __( 'Footer Menu', 'samvedna' ),
	) );

	// Card / thumbnail crops used across the site.
	add_image_size( 'samvedna-card', 800, 600, true );      // Doctor / blog cards (4:3).
	add_image_size( 'samvedna-blog', 1024, 640, true );     // Blog feature (16:10).
	add_image_size( 'samvedna-portrait', 640, 800, true );  // Doctor portrait (4:5).
}
add_action( 'after_setup_theme', 'samvedna_setup' );

/**
 * Content width.
 */
function samvedna_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'samvedna_content_width', 0 );

/**
 * Register the sidebar used on the blog / archive templates.
 */
function samvedna_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'samvedna' ),
		'id'            => 'sidebar-blog',
		'description'   => __( 'Appears on blog, archive, and single post pages.', 'samvedna' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s rounded-card border border-border bg-white p-6">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="font-display text-lg font-semibold text-text mb-4">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'samvedna_widgets_init' );

/**
 * Add the `js` class to <html> as early as possible so the scroll-reveal CSS
 * only hides content for JS-capable browsers (progressive enhancement).
 */
function samvedna_html_js_class() {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
}
add_action( 'wp_head', 'samvedna_html_js_class', 0 );
