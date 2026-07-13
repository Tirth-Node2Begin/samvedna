<?php
/**
 * Consultation form backend for the Samvedna Landing bundle.
 *
 * Validates the AJAX submission, stores it in a dedicated table, and emails the
 * site admin. All functions are guarded so this can coexist with the full
 * Samvedna Core plugin without redeclaration errors.
 *
 * @package Samvedna_Landing
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'samvedna_inquiries_table' ) ) {
	/**
	 * Full inquiries table name.
	 *
	 * @return string
	 */
	function samvedna_inquiries_table() {
		global $wpdb;
		return $wpdb->prefix . 'samvedna_inquiries';
	}
}

if ( ! function_exists( 'svl_create_inquiries_table' ) ) {
	/**
	 * Create the inquiries table (idempotent). Called on plugin activation.
	 */
	function svl_create_inquiries_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = samvedna_inquiries_table();
		$charset_collate = $wpdb->get_charset_collate();

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
}

/*
 * Self-heal: make sure the table exists even if the page is visited before the
 * activation hook ran (cheap once the option is set).
 */
add_action( 'admin_init', function () {
	if ( get_option( 'svl_db_version' ) !== SVL_VERSION ) {
		if ( function_exists( 'svl_create_inquiries_table' ) ) {
			svl_create_inquiries_table();
		}
		update_option( 'svl_db_version', SVL_VERSION );
	}
} );

if ( ! function_exists( 'samvedna_handle_consultation' ) ) {

	/**
	 * Allowed primary-concern values (mirror of the on-page select).
	 *
	 * @return string[]
	 */
	function samvedna_form_conditions() {
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
	 * Best-effort client IP.
	 *
	 * @return string
	 */
	function samvedna_client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return $ip ? $ip : '0.0.0.0';
	}

	/**
	 * Handle the AJAX submission: validate, store, email.
	 */
	function samvedna_handle_consultation() {
		if ( ! check_ajax_referer( 'samvedna_consultation', 'samvedna_nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session expired. Please refresh the page and try again.', 'samvedna-landing' ) ), 403 );
		}

		// Honeypot — accept silently, store nothing.
		if ( ! empty( $_POST['website_hp'] ) ) {
			wp_send_json_success( array( 'message' => __( 'Thank you. We will contact you shortly.', 'samvedna-landing' ) ) );
		}

		// Per-IP rate limit (10 / 10 min).
		$ip   = samvedna_client_ip();
		$key  = 'svl_rl_' . md5( $ip );
		$hits = (int) get_transient( $key );
		if ( $hits >= 10 ) {
			wp_send_json_error( array( 'message' => __( 'Too many requests. Please try again in a few minutes.', 'samvedna-landing' ) ), 429 );
		}

		$parent_name = isset( $_POST['parentName'] ) ? sanitize_text_field( wp_unslash( $_POST['parentName'] ) ) : '';
		$phone       = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$message     = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$source      = ( isset( $_POST['source'] ) && 'popup' === $_POST['source'] ) ? 'popup' : 'website';

		// Optional legacy fields — the live contact form only collects name, phone,
		// email and message, but these are still accepted (and stored) if present so
		// the schema/emails keep working either way.
		$child_age = isset( $_POST['childAge'] ) ? max( 0, (int) $_POST['childAge'] ) : 0;
		$condition = isset( $_POST['condition'] ) ? sanitize_text_field( wp_unslash( $_POST['condition'] ) ) : '';
		$country   = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
		$pref_time = isset( $_POST['preferredTime'] ) ? sanitize_text_field( wp_unslash( $_POST['preferredTime'] ) ) : '';

		$phone_digits = preg_replace( '/[\s\-()]/', '', $phone );

		$errors = array();
		if ( mb_strlen( trim( $parent_name ) ) < 2 ) { $errors['parentName'] = array( __( 'Enter your name.', 'samvedna-landing' ) ); }
		if ( ! preg_match( '/^\+?[0-9]{10,15}$/', $phone_digits ) ) { $errors['phone'] = array( __( 'Enter a valid phone number (at least 10 digits).', 'samvedna-landing' ) ); }
		if ( ! is_email( $email ) ) { $errors['email'] = array( __( 'Enter a valid email address.', 'samvedna-landing' ) ); }
		if ( mb_strlen( $message ) > 1000 ) { $errors['message'] = array( __( 'Keep the message under 1000 characters.', 'samvedna-landing' ) ); }

		if ( ! empty( $errors ) ) {
			wp_send_json_error( array(
				'message'     => __( 'Please correct the highlighted fields.', 'samvedna-landing' ),
				'fieldErrors' => $errors,
			), 422 );
		}

		// The stored table has NOT NULL columns from the original multi-field form;
		// give the concern a sensible value when the trimmed contact form omits it.
		if ( '' === $condition ) { $condition = 'General enquiry'; }

		global $wpdb;
		$stored = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			samvedna_inquiries_table(),
			array(
				'parent_name'    => $parent_name,
				'child_age'      => $child_age,
				'condition_type' => $condition,
				'country'        => $country,
				'phone'          => $phone,
				'email'          => $email,
				'message'        => $message ? $message : null,
				'preferred_time' => $pref_time,
				'source'         => $source,
				'ip_address'     => $ip,
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $stored ) {
			wp_send_json_error( array( 'message' => __( 'Unable to save your request right now. Please try again or call us.', 'samvedna-landing' ) ), 500 );
		}

		set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );
		samvedna_send_inquiry_email( compact( 'parent_name', 'child_age', 'condition', 'country', 'phone', 'email', 'message', 'pref_time', 'source' ) );

		wp_send_json_success( array(
			'message'     => __( 'Thank you. The Samvedna care desk has received your request and will contact you shortly.', 'samvedna-landing' ),
			'submittedAt' => current_time( 'c' ),
		) );
	}
	add_action( 'wp_ajax_samvedna_submit_consultation', 'samvedna_handle_consultation' );
	add_action( 'wp_ajax_nopriv_samvedna_submit_consultation', 'samvedna_handle_consultation' );

	/**
	 * Email the admin about a new inquiry.
	 *
	 * @param array $d Inquiry data.
	 */
	function samvedna_send_inquiry_email( $d ) {
		$to = apply_filters( 'svl_notification_email', get_option( 'admin_email' ) );

		$subject = sprintf(
			/* translators: %s: parent name. */
			__( 'New consultation request — %s', 'samvedna-landing' ),
			$d['parent_name']
		);

		$rows = array(
			__( 'Name', 'samvedna-landing' )    => $d['parent_name'],
			__( 'Phone', 'samvedna-landing' )   => $d['phone'],
			__( 'Email', 'samvedna-landing' )   => $d['email'],
			__( 'Message', 'samvedna-landing' ) => $d['message'] ? $d['message'] : '—',
			__( 'Source', 'samvedna-landing' )  => $d['source'],
		);

		$body = '<h2 style="font-family:sans-serif">' . esc_html__( 'New consultation request', 'samvedna-landing' ) . '</h2><table style="font-family:sans-serif;border-collapse:collapse">';
		foreach ( $rows as $label => $value ) {
			$body .= '<tr><td style="padding:6px 12px;font-weight:bold;border:1px solid #e2e8f0">' . esc_html( $label ) . '</td><td style="padding:6px 12px;border:1px solid #e2e8f0">' . nl2br( esc_html( (string) $value ) ) . '</td></tr>';
		}
		$body .= '</table>';

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: ' . $d['parent_name'] . ' <' . $d['email'] . '>',
		);

		wp_mail( $to, $subject, $body, $headers );
	}
}

/*
 * A small "Samvedna Leads" admin screen so submissions are viewable in wp-admin.
 * Only registered if the full Samvedna Core "Consultations" screen isn't present.
 */
if ( ! function_exists( 'samvedna_inquiries_admin_page' ) && ! function_exists( 'samvedna_render_inquiries_page' ) ) {
	add_action( 'admin_menu', function () {
		add_menu_page(
			__( 'Samvedna Leads', 'samvedna-landing' ),
			__( 'Samvedna Leads', 'samvedna-landing' ),
			'manage_options',
			'svl-leads',
			'svl_render_leads_page',
			'dashicons-heart',
			26
		);
	} );

	/**
	 * Render the leads table.
	 */
	function svl_render_leads_page() {
		global $wpdb;
		$table = samvedna_inquiries_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 200" );

		echo '<div class="wrap"><h1>' . esc_html__( 'Samvedna Leads', 'samvedna-landing' ) . '</h1>';
		if ( empty( $rows ) ) {
			echo '<p>' . esc_html__( 'No consultation requests yet.', 'samvedna-landing' ) . '</p></div>';
			return;
		}
		echo '<table class="widefat striped"><thead><tr>';
		foreach ( array( 'Date', 'Name', 'Phone', 'Email', 'Message', 'Source' ) as $h ) {
			echo '<th>' . esc_html( $h ) . '</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ( $rows as $r ) {
			echo '<tr>';
			echo '<td>' . esc_html( $r->created_at ) . '</td>';
			echo '<td>' . esc_html( $r->parent_name ) . '</td>';
			echo '<td>' . esc_html( $r->phone ) . '</td>';
			echo '<td>' . esc_html( $r->email ) . '</td>';
			echo '<td>' . esc_html( $r->message ) . '</td>';
			echo '<td>' . esc_html( $r->source ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table></div>';
	}
}
