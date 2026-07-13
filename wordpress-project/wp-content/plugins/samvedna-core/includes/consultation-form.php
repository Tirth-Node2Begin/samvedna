<?php
/**
 * Consultation form handler (ports lib/actions/booking.ts + lib/db/inquiries.ts).
 *
 * Validates server-side, stores to the inquiries table, emails the clinic, and
 * returns JSON for the AJAX form in the theme.
 *
 * @package Samvedna_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Allowed primary-concern values (mirrors constants/conditions.ts conditionList).
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
 * Handle the AJAX submission.
 */
function samvedna_handle_consultation() {
	// Nonce.
	if ( ! check_ajax_referer( 'samvedna_consultation', 'samvedna_nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Your session expired. Please refresh the page and try again.', 'samvedna-core' ) ), 403 );
	}

	// Honeypot — silently accept so bots don't learn, but store nothing.
	if ( ! empty( $_POST['website_hp'] ) ) {
		wp_send_json_success( array( 'message' => __( 'Thank you. We will contact you shortly.', 'samvedna-core' ) ) );
	}

	// Rate limit per IP (10 submissions / 10 minutes).
	$ip  = samvedna_client_ip();
	$key = 'samvedna_rl_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 10 ) {
		wp_send_json_error( array( 'message' => __( 'Too many requests. Please try again in a few minutes.', 'samvedna-core' ) ), 429 );
	}

	// Collect + sanitise.
	$parent_name = isset( $_POST['parentName'] ) ? sanitize_text_field( wp_unslash( $_POST['parentName'] ) ) : '';
	$child_age   = isset( $_POST['childAge'] ) ? (int) $_POST['childAge'] : -1;
	$condition   = isset( $_POST['condition'] ) ? sanitize_text_field( wp_unslash( $_POST['condition'] ) ) : '';
	$country     = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
	$phone       = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message     = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$pref_time   = isset( $_POST['preferredTime'] ) ? sanitize_text_field( wp_unslash( $_POST['preferredTime'] ) ) : '';
	$source      = ( isset( $_POST['source'] ) && 'popup' === $_POST['source'] ) ? 'popup' : 'website';

	// Validate (mirrors the Zod schema).
	$errors = array();
	if ( mb_strlen( trim( $parent_name ) ) < 2 ) { $errors['parentName'] = array( __( "Enter the parent's full name.", 'samvedna-core' ) ); }
	if ( $child_age < 0 || $child_age > 18 ) { $errors['childAge'] = array( __( 'Please enter an age between 0 and 18.', 'samvedna-core' ) ); }
	if ( ! in_array( $condition, samvedna_form_conditions(), true ) ) { $errors['condition'] = array( __( 'Choose the primary concern.', 'samvedna-core' ) ); }
	if ( mb_strlen( trim( $country ) ) < 2 ) { $errors['country'] = array( __( 'Enter your country.', 'samvedna-core' ) ); }
	if ( ! preg_match( '/^\+?[0-9]{8,15}$/', $phone ) ) { $errors['phone'] = array( __( 'Use 8 to 15 digits, with optional country code.', 'samvedna-core' ) ); }
	if ( ! is_email( $email ) ) { $errors['email'] = array( __( 'Enter a valid email address.', 'samvedna-core' ) ); }
	if ( mb_strlen( $message ) > 500 ) { $errors['message'] = array( __( 'Keep the message under 500 characters.', 'samvedna-core' ) ); }
	if ( ! in_array( $pref_time, array( 'morning', 'afternoon', 'evening' ), true ) ) { $errors['preferredTime'] = array( __( 'Choose a preferred consultation time.', 'samvedna-core' ) ); }

	if ( ! empty( $errors ) ) {
		wp_send_json_error( array(
			'message'     => __( 'Please correct the highlighted fields.', 'samvedna-core' ),
			'fieldErrors' => $errors,
		), 422 );
	}

	// Store.
	global $wpdb;
	$table  = samvedna_inquiries_table();
	$stored = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
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
		wp_send_json_error( array( 'message' => __( 'Unable to save your request right now. Please try again or call us.', 'samvedna-core' ) ), 500 );
	}

	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	// Notify the clinic.
	samvedna_send_inquiry_email( compact( 'parent_name', 'child_age', 'condition', 'country', 'phone', 'email', 'message', 'pref_time', 'source' ) );

	wp_send_json_success( array(
		'message'     => __( 'Thank you. The Samvedna care desk has received your request and will contact you shortly.', 'samvedna-core' ),
		'submittedAt' => current_time( 'c' ),
	) );
}
add_action( 'wp_ajax_samvedna_submit_consultation', 'samvedna_handle_consultation' );
add_action( 'wp_ajax_nopriv_samvedna_submit_consultation', 'samvedna_handle_consultation' );

/**
 * Send the admin notification email.
 *
 * @param array $d Inquiry data.
 */
function samvedna_send_inquiry_email( $d ) {
	$to = get_option( 'admin_email' );
	if ( function_exists( 'get_field' ) ) {
		$custom = get_field( 'notification_email', 'option' );
		if ( $custom && is_email( $custom ) ) {
			$to = $custom;
		}
	}

	$subject = sprintf(
		/* translators: %s: parent name. */
		__( 'New consultation request — %s', 'samvedna-core' ),
		$d['parent_name']
	);

	$rows = array(
		__( 'Parent name', 'samvedna-core' )    => $d['parent_name'],
		__( 'Child age', 'samvedna-core' )       => $d['child_age'],
		__( 'Primary concern', 'samvedna-core' ) => $d['condition'],
		__( 'Country', 'samvedna-core' )         => $d['country'],
		__( 'Phone', 'samvedna-core' )           => $d['phone'],
		__( 'Email', 'samvedna-core' )           => $d['email'],
		__( 'Preferred time', 'samvedna-core' )  => ucfirst( $d['pref_time'] ),
		__( 'Source', 'samvedna-core' )          => $d['source'],
		__( 'Message', 'samvedna-core' )         => $d['message'] ? $d['message'] : '—',
	);

	$body = '<h2 style="font-family:sans-serif">' . esc_html__( 'New consultation request', 'samvedna-core' ) . '</h2><table style="font-family:sans-serif;border-collapse:collapse">';
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

/**
 * Best-effort client IP.
 *
 * @return string
 */
function samvedna_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return $ip ? $ip : '0.0.0.0';
}
