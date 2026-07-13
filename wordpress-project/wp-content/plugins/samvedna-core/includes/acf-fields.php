<?php
/**
 * ACF options page + field groups (registered in PHP so they exist as soon as
 * ACF is active — no JSON import required). All fields are optional; the theme
 * falls back to its bundled defaults when a field is empty.
 *
 * @package Samvedna_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the options page.
 */
function samvedna_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page( array(
		'page_title' => __( 'Samvedna Settings', 'samvedna-core' ),
		'menu_title' => __( 'Samvedna Settings', 'samvedna-core' ),
		'menu_slug'  => 'samvedna-settings',
		'capability' => 'manage_options',
		'icon_url'   => 'dashicons-admin-customizer',
		'position'   => 3,
		'redirect'   => false,
	) );
}
add_action( 'acf/init', 'samvedna_acf_options_page' );

/**
 * Helper to build a simple ACF field array.
 *
 * @param string $key   Field key suffix.
 * @param string $name  Field name.
 * @param string $label Label.
 * @param string $type  Field type.
 * @param array  $extra Extra args.
 * @return array
 */
function samvedna_acf_field( $key, $name, $label, $type = 'text', $extra = array() ) {
	return array_merge( array(
		'key'   => 'field_sam_' . $key,
		'label' => $label,
		'name'  => $name,
		'type'  => $type,
	), $extra );
}

/**
 * Register field groups.
 */
function samvedna_acf_field_groups() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/* ---- Site settings (options page) ---- */
	acf_add_local_field_group( array(
		'key'      => 'group_sam_settings',
		'title'    => __( 'Samvedna Site Settings', 'samvedna-core' ),
		'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'samvedna-settings' ) ) ),
		'fields'   => array(
			samvedna_acf_field( 'tab_contact', '', __( 'Contact', 'samvedna-core' ), 'tab' ),
			samvedna_acf_field( 'contact_phone_primary', 'contact_phone_primary', __( 'Primary phone', 'samvedna-core' ) ),
			samvedna_acf_field( 'contact_phone_secondary', 'contact_phone_secondary', __( 'Secondary phone', 'samvedna-core' ) ),
			samvedna_acf_field( 'contact_phone_href', 'contact_phone_href', __( 'Phone link (tel:)', 'samvedna-core' ) ),
			samvedna_acf_field( 'contact_whatsapp_href', 'contact_whatsapp_href', __( 'WhatsApp link', 'samvedna-core' ) ),
			samvedna_acf_field( 'contact_email', 'contact_email', __( 'Email', 'samvedna-core' ) ),
			samvedna_acf_field( 'contact_address', 'contact_address', __( 'Address', 'samvedna-core' ), 'textarea', array( 'rows' => 2 ) ),
			samvedna_acf_field( 'contact_hours', 'contact_hours', __( 'Working hours', 'samvedna-core' ) ),
			samvedna_acf_field( 'notification_email', 'notification_email', __( 'Form notification email', 'samvedna-core' ), 'text', array( 'instructions' => __( 'Where consultation submissions are emailed. Defaults to the site admin email.', 'samvedna-core' ) ) ),

			samvedna_acf_field( 'tab_social', '', __( 'Social', 'samvedna-core' ), 'tab' ),
			samvedna_acf_field( 'social_links', 'social_links', __( 'Social links', 'samvedna-core' ), 'repeater', array(
				'button_label' => __( 'Add link', 'samvedna-core' ),
				'sub_fields'   => array(
					samvedna_acf_field( 'social_label', 'label', __( 'Label', 'samvedna-core' ) ),
					samvedna_acf_field( 'social_href', 'href', __( 'URL', 'samvedna-core' ), 'url' ),
				),
			) ),

			samvedna_acf_field( 'tab_hero', '', __( 'Hero', 'samvedna-core' ), 'tab' ),
			samvedna_acf_field( 'hero_card1_title', 'hero_card1_title', __( 'Left card title', 'samvedna-core' ) ),
			samvedna_acf_field( 'hero_card1_text', 'hero_card1_text', __( 'Left card text', 'samvedna-core' ), 'textarea', array( 'rows' => 3 ) ),
			samvedna_acf_field( 'hero_card2_title', 'hero_card2_title', __( 'Right card title', 'samvedna-core' ) ),
			samvedna_acf_field( 'hero_card2_text', 'hero_card2_text', __( 'Right card text', 'samvedna-core' ), 'textarea', array( 'rows' => 3 ) ),
			samvedna_acf_field( 'founder_name', 'founder_name', __( 'Founder name', 'samvedna-core' ) ),
			samvedna_acf_field( 'founder_title', 'founder_title', __( 'Founder title', 'samvedna-core' ) ),

			samvedna_acf_field( 'tab_seo', '', __( 'SEO', 'samvedna-core' ), 'tab' ),
			samvedna_acf_field( 'meta_description', 'meta_description', __( 'Meta description', 'samvedna-core' ), 'textarea', array( 'rows' => 3 ) ),
			samvedna_acf_field( 'og_image', 'og_image', __( 'Social share image', 'samvedna-core' ), 'image', array( 'return_format' => 'url', 'preview_size' => 'medium' ) ),
			samvedna_acf_field( 'address_street', 'address_street', __( 'Schema: street', 'samvedna-core' ) ),
			samvedna_acf_field( 'address_locality', 'address_locality', __( 'Schema: city', 'samvedna-core' ) ),
			samvedna_acf_field( 'address_region', 'address_region', __( 'Schema: region', 'samvedna-core' ) ),
			samvedna_acf_field( 'address_postal', 'address_postal', __( 'Schema: postal code', 'samvedna-core' ) ),
			samvedna_acf_field( 'address_country', 'address_country', __( 'Schema: country code', 'samvedna-core' ) ),

			samvedna_acf_field( 'tab_form', '', __( 'Form & popup', 'samvedna-core' ), 'tab' ),
			samvedna_acf_field( 'appointment_url', 'appointment_url', __( 'Book Consultation URL', 'samvedna-core' ), 'url' ),
			samvedna_acf_field( 'form_redirect_mode', 'form_redirect_mode', __( 'Redirect after submit', 'samvedna-core' ), 'select', array(
				'choices' => array( 'none' => 'None', 'thankYou' => 'Thank-you page', 'appointment' => 'Appointment URL', 'whatsapp' => 'WhatsApp', 'custom' => 'Custom URL' ),
				'default_value' => 'thankYou',
			) ),
			samvedna_acf_field( 'form_redirect_url', 'form_redirect_url', __( 'Custom redirect URL', 'samvedna-core' ), 'url' ),
			samvedna_acf_field( 'form_redirect_delay_ms', 'form_redirect_delay_ms', __( 'Redirect delay (ms)', 'samvedna-core' ), 'number', array( 'default_value' => 2200 ) ),
			samvedna_acf_field( 'popup_delay_ms', 'popup_delay_ms', __( 'Popup delay (ms)', 'samvedna-core' ), 'number', array( 'default_value' => 12000 ) ),

			samvedna_acf_field( 'tab_footer', '', __( 'Footer', 'samvedna-core' ), 'tab' ),
			samvedna_acf_field( 'footer_about', 'footer_about', __( 'Footer about text', 'samvedna-core' ), 'textarea', array( 'rows' => 3 ) ),
		),
	) );

	/* ---- Doctor fields ---- */
	acf_add_local_field_group( array(
		'key'      => 'group_sam_doctor',
		'title'    => __( 'Doctor Details', 'samvedna-core' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'doctor' ) ) ),
		'fields'   => array(
			samvedna_acf_field( 'doctor_title', 'doctor_title', __( 'Title / role', 'samvedna-core' ) ),
			samvedna_acf_field( 'doctor_specialization', 'specialization', __( 'Specialization', 'samvedna-core' ) ),
			samvedna_acf_field( 'doctor_experience', 'experience', __( 'Experience (e.g. 20+ Years)', 'samvedna-core' ) ),
			samvedna_acf_field( 'doctor_about', 'about', __( 'About', 'samvedna-core' ), 'textarea', array( 'rows' => 4 ) ),
			samvedna_acf_field( 'doctor_qualifications', 'qualifications', __( 'Qualifications (one per line)', 'samvedna-core' ), 'textarea', array( 'rows' => 3 ) ),
			samvedna_acf_field( 'doctor_specializations', 'specializations', __( 'Specializations (one per line)', 'samvedna-core' ), 'textarea', array( 'rows' => 4 ) ),
			samvedna_acf_field( 'doctor_treatments', 'treatments', __( 'Treatments (one per line)', 'samvedna-core' ), 'textarea', array( 'rows' => 4 ) ),
			samvedna_acf_field( 'doctor_languages', 'languages', __( 'Languages (one per line)', 'samvedna-core' ), 'textarea', array( 'rows' => 3 ) ),
			samvedna_acf_field( 'doctor_consultation', 'consultation', __( 'Consultation note', 'samvedna-core' ), 'textarea', array( 'rows' => 2 ) ),
		),
	) );

	/* ---- Condition fields ---- */
	acf_add_local_field_group( array(
		'key'      => 'group_sam_condition',
		'title'    => __( 'Condition Layout', 'samvedna-core' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'sam_condition' ) ) ),
		'fields'   => array(
			samvedna_acf_field( 'cond_icon', 'icon', __( 'Icon', 'samvedna-core' ), 'select', array(
				'choices' => array( 'brain' => 'Brain', 'activity' => 'Activity', 'book-open' => 'Book', 'message-circle' => 'Message', 'sprout' => 'Sprout', 'sparkles' => 'Sparkles', 'heart-pulse' => 'Heart pulse' ),
			) ),
			samvedna_acf_field( 'cond_variant', 'variant', __( 'Card style', 'samvedna-core' ), 'select', array(
				'choices' => array( 'dark' => 'Featured (dark)', 'light' => 'Light', 'soft' => 'Soft' ),
				'default_value' => 'light',
			) ),
			samvedna_acf_field( 'cond_span', 'span', __( 'Grid span (advanced)', 'samvedna-core' ), 'text', array(
				'instructions' => __( 'Tailwind grid classes, e.g. "md:col-span-2 lg:col-span-2". Leave blank for default.', 'samvedna-core' ),
			) ),
		),
	) );

	/* ---- Care plan fields ---- */
	acf_add_local_field_group( array(
		'key'      => 'group_sam_plan',
		'title'    => __( 'Care Plan Details', 'samvedna-core' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'care_plan' ) ) ),
		'fields'   => array(
			samvedna_acf_field( 'plan_tagline', 'tagline', __( 'Tagline', 'samvedna-core' ) ),
			samvedna_acf_field( 'plan_price', 'price', __( 'Price', 'samvedna-core' ) ),
			samvedna_acf_field( 'plan_duration', 'duration', __( 'Duration', 'samvedna-core' ) ),
			samvedna_acf_field( 'plan_features', 'features', __( 'Features (one per line)', 'samvedna-core' ), 'textarea', array( 'rows' => 6 ) ),
			samvedna_acf_field( 'plan_popular', 'popular', __( 'Most preferred?', 'samvedna-core' ), 'true_false', array( 'ui' => 1 ) ),
			samvedna_acf_field( 'plan_cta', 'cta', __( 'Button label', 'samvedna-core' ) ),
		),
	) );

	/* ---- Achievement fields ---- */
	acf_add_local_field_group( array(
		'key'      => 'group_sam_achievement',
		'title'    => __( 'Achievement Details', 'samvedna-core' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'achievement' ) ) ),
		'fields'   => array(
			samvedna_acf_field( 'ach_event', 'event', __( 'Event / organisation', 'samvedna-core' ) ),
			samvedna_acf_field( 'ach_location', 'location', __( 'Location', 'samvedna-core' ) ),
			samvedna_acf_field( 'ach_icon', 'icon', __( 'Icon', 'samvedna-core' ), 'select', array(
				'choices' => array( 'trophy' => 'Trophy', 'mic' => 'Microphone', 'users' => 'Users', 'globe' => 'Globe', 'book-open' => 'Book', 'medal' => 'Medal' ),
			) ),
		),
	) );

	/* ---- Testimonial (video story) fields ---- */
	acf_add_local_field_group( array(
		'key'      => 'group_sam_testimonial',
		'title'    => __( 'Video Story Details', 'samvedna-core' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'testimonial' ) ) ),
		'fields'   => array(
			samvedna_acf_field( 'vid_youtube_id', 'youtube_id', __( 'YouTube video ID', 'samvedna-core' ), 'text', array( 'instructions' => __( 'The part after watch?v= . Leave blank to show the “coming soon” poster.', 'samvedna-core' ) ) ),
			samvedna_acf_field( 'vid_family_name', 'family_name', __( 'Family name', 'samvedna-core' ) ),
			samvedna_acf_field( 'vid_condition', 'condition', __( 'Condition', 'samvedna-core' ) ),
			samvedna_acf_field( 'vid_location', 'location', __( 'Location', 'samvedna-core' ) ),
			samvedna_acf_field( 'vid_duration', 'duration', __( 'Duration label (e.g. 2:10)', 'samvedna-core' ) ),
		),
	) );
}
add_action( 'acf/init', 'samvedna_acf_field_groups' );
