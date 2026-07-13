<?php
/**
 * Plugin Name:       Samvedna Core
 * Plugin URI:        https://samvednahomeopathy.com
 * Description:       Custom post types, taxonomies, ACF field groups, the consultation form handler (storage + email + admin screen), and a one-click content seeder for the Samvedna Homeopathy theme.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            Samvedna Homeopathy
 * License:           GPL-2.0-or-later
 * Text Domain:       samvedna-core
 *
 * @package Samvedna_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'SAMVEDNA_CORE_VERSION', '1.0.0' );
define( 'SAMVEDNA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAMVEDNA_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'SAMVEDNA_INQUIRIES_TABLE', 'samvedna_inquiries' );

require_once SAMVEDNA_CORE_DIR . 'includes/post-types.php';
require_once SAMVEDNA_CORE_DIR . 'includes/acf-fields.php';
require_once SAMVEDNA_CORE_DIR . 'includes/consultation-form.php';
require_once SAMVEDNA_CORE_DIR . 'includes/admin-inquiries.php';
require_once SAMVEDNA_CORE_DIR . 'includes/seeder.php';

/**
 * Full table name for stored inquiries.
 *
 * @return string
 */
function samvedna_inquiries_table() {
	global $wpdb;
	return $wpdb->prefix . SAMVEDNA_INQUIRIES_TABLE;
}

/**
 * Activation: register CPTs (so rewrite rules are correct), create the DB
 * table, seed default content once, then flush rewrite rules.
 */
function samvedna_core_activate() {
	samvedna_register_post_types();
	samvedna_register_taxonomies();
	samvedna_core_create_table();
	samvedna_core_seed_content();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'samvedna_core_activate' );

/**
 * Deactivation: flush rewrite rules.
 */
function samvedna_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'samvedna_core_deactivate' );

/**
 * Self-healing upgrade check (runs on every admin load, cheap once done).
 *
 * Ensures the DB table exists and rewrite rules are flushed even if the plugin
 * was first activated before this version was in place.
 */
function samvedna_core_maybe_upgrade() {
	if ( get_option( 'samvedna_db_version' ) !== SAMVEDNA_CORE_VERSION ) {
		samvedna_core_create_table();
		update_option( 'samvedna_db_version', SAMVEDNA_CORE_VERSION );
		update_option( 'samvedna_flush_needed', '1' );
	}
	if ( get_option( 'samvedna_flush_needed' ) ) {
		flush_rewrite_rules();
		delete_option( 'samvedna_flush_needed' );
	}
}
add_action( 'admin_init', 'samvedna_core_maybe_upgrade' );

/**
 * Create the inquiries table (idempotent).
 */
function samvedna_core_create_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table           = samvedna_inquiries_table();
	$charset_collate = $wpdb->get_charset_collate();

	// `condition` is a reserved word in MySQL, so the column is `condition_type`.
	$sql = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		parent_name VARCHAR(255) NOT NULL,
		child_age TINYINT UNSIGNED NOT NULL,
		condition_type VARCHAR(255) NOT NULL,
		country VARCHAR(255) NOT NULL,
		phone VARCHAR(32) NOT NULL,
		email VARCHAR(255) NOT NULL,
		message TEXT NULL,
		preferred_time VARCHAR(32) NOT NULL,
		source VARCHAR(32) NOT NULL DEFAULT 'website',
		ip_address VARCHAR(45) NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY created_at (created_at)
	) {$charset_collate};";

	dbDelta( $sql );
}

/**
 * Admin notice prompting installation of recommended companion plugins.
 */
function samvedna_core_admin_notices() {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) ) {
		echo '<div class="notice notice-info is-dismissible"><p>';
		echo wp_kses_post( __( '<strong>Samvedna Core:</strong> Install and activate <strong>Advanced Custom Fields</strong> to edit hero text, contact details, and section content from the admin. The site still works without it using the bundled defaults.', 'samvedna-core' ) );
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'samvedna_core_admin_notices' );
