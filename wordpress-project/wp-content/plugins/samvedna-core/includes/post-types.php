<?php
/**
 * Custom post types & taxonomies.
 *
 * @package Samvedna_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all custom post types.
 */
function samvedna_register_post_types() {
	$types = array(
		'doctor' => array(
			'singular' => __( 'Doctor', 'samvedna-core' ),
			'plural'   => __( 'Doctors', 'samvedna-core' ),
			'icon'     => 'dashicons-businessperson',
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'slug'     => 'doctors',
			'public'   => true,
		),
		'testimonial' => array(
			'singular' => __( 'Testimonial', 'samvedna-core' ),
			'plural'   => __( 'Testimonials', 'samvedna-core' ),
			'icon'     => 'dashicons-format-video',
			'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'slug'     => 'stories',
			'public'   => false,
		),
		'sam_faq' => array(
			'singular' => __( 'FAQ', 'samvedna-core' ),
			'plural'   => __( 'FAQs', 'samvedna-core' ),
			'icon'     => 'dashicons-editor-help',
			'supports' => array( 'title', 'editor', 'page-attributes' ),
			'slug'     => 'faqs',
			'public'   => false,
		),
		'sam_condition' => array(
			'singular' => __( 'Condition', 'samvedna-core' ),
			'plural'   => __( 'Conditions', 'samvedna-core' ),
			'icon'     => 'dashicons-heart',
			'supports' => array( 'title', 'editor', 'excerpt', 'page-attributes' ),
			'slug'     => 'conditions-treated',
			'public'   => true,
		),
		'care_plan' => array(
			'singular' => __( 'Care Plan', 'samvedna-core' ),
			'plural'   => __( 'Care Plans', 'samvedna-core' ),
			'icon'     => 'dashicons-clipboard',
			'supports' => array( 'title', 'page-attributes' ),
			'slug'     => 'care-plans',
			'public'   => false,
		),
		'achievement' => array(
			'singular' => __( 'Achievement', 'samvedna-core' ),
			'plural'   => __( 'Achievements', 'samvedna-core' ),
			'icon'     => 'dashicons-awards',
			'supports' => array( 'title', 'editor', 'excerpt', 'page-attributes' ),
			'slug'     => 'achievements',
			'public'   => false,
		),
	);

	foreach ( $types as $key => $t ) {
		register_post_type( $key, array(
			'labels'             => array(
				'name'          => $t['plural'],
				'singular_name' => $t['singular'],
				'add_new_item'  => sprintf( /* translators: %s: singular name. */ __( 'Add New %s', 'samvedna-core' ), $t['singular'] ),
				'edit_item'     => sprintf( /* translators: %s: singular name. */ __( 'Edit %s', 'samvedna-core' ), $t['singular'] ),
				'menu_name'     => $t['plural'],
			),
			'public'             => $t['public'],
			'publicly_queryable' => $t['public'],
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'menu_icon'          => $t['icon'],
			'supports'           => $t['supports'],
			'has_archive'        => false,
			'rewrite'            => $t['public'] ? array( 'slug' => $t['slug'] ) : false,
			'hierarchical'       => false,
		) );
	}
}
add_action( 'init', 'samvedna_register_post_types' );

/**
 * Register custom taxonomies.
 */
function samvedna_register_taxonomies() {
	register_taxonomy( 'doctor_specialization', array( 'doctor' ), array(
		'labels'            => array(
			'name'          => __( 'Specializations', 'samvedna-core' ),
			'singular_name' => __( 'Specialization', 'samvedna-core' ),
			'menu_name'     => __( 'Specializations', 'samvedna-core' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'specialization' ),
	) );
}
add_action( 'init', 'samvedna_register_taxonomies' );
