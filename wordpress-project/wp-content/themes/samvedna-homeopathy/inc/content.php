<?php
/**
 * Content resolvers.
 *
 * Each function returns normalized arrays for the templates. When the matching
 * custom post type (registered by the samvedna-core plugin) has published
 * entries they are used; otherwise the baked-in defaults from inc/defaults.php
 * are returned so the site is never empty.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query a custom post type, returning posts or an empty array.
 *
 * @param string $post_type CPT key.
 * @param int    $limit     Max posts (-1 for all).
 * @return WP_Post[]
 */
function samvedna_cpt_posts( $post_type, $limit = -1 ) {
	if ( ! post_type_exists( $post_type ) ) {
		return array();
	}
	$query = new WP_Query( array(
		'post_type'              => $post_type,
		'posts_per_page'         => $limit,
		'orderby'                => 'menu_order date',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'ignore_sticky_posts'    => true,
	) );
	return $query->posts;
}

/**
 * Conditions treated. CPT: `sam_condition`. Meta: icon, span, variant.
 *
 * @return array
 */
function samvedna_get_conditions() {
	$posts    = samvedna_cpt_posts( 'sam_condition' );
	$defaults = samvedna_default_conditions();
	if ( empty( $posts ) ) {
		return $defaults;
	}

	$out = array();
	foreach ( $posts as $i => $post ) {
		$layout = isset( $defaults[ $i ] ) ? $defaults[ $i ] : end( $defaults );
		$out[]  = array(
			'name'        => get_the_title( $post ),
			'description' => has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( $post->post_content ),
			'icon'        => samvedna_get_field( 'icon', $layout['icon'], $post->ID ),
			'span'        => samvedna_get_field( 'span', $layout['span'], $post->ID ),
			'variant'     => samvedna_get_field( 'variant', $layout['variant'], $post->ID ),
		);
	}
	return $out;
}

/**
 * Medical team. CPT: `doctor`.
 *
 * @return array
 */
function samvedna_get_team() {
	$posts = samvedna_cpt_posts( 'doctor' );
	if ( empty( $posts ) ) {
		return samvedna_default_team();
	}

	$out = array();
	foreach ( $posts as $post ) {
		$seed_img = get_post_meta( $post->ID, 'samvedna_image_file', true );
		$image    = has_post_thumbnail( $post )
			? get_the_post_thumbnail_url( $post, 'samvedna-card' )
			: samvedna_image_url( $seed_img ? $seed_img : 'samvedna-associate-portrait.webp' );

		$out[] = array(
			'name'            => get_the_title( $post ),
			'title'           => samvedna_get_field( 'doctor_title', 'Consultant', $post->ID ),
			'image'           => $image,
			'alt'             => get_the_title( $post ),
			'specialization'  => samvedna_get_field( 'specialization', '', $post->ID ),
			'experience'      => samvedna_get_field( 'experience', '', $post->ID ),
			'summary'         => has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 24 ),
			'qualifications'  => samvedna_to_list( samvedna_get_field( 'qualifications', array( 'BHMS' ), $post->ID ) ),
			'about'           => samvedna_get_field( 'about', wp_strip_all_tags( $post->post_content ), $post->ID ),
			'specializations' => samvedna_to_list( samvedna_get_field( 'specializations', array(), $post->ID ) ),
			'treatments'      => samvedna_to_list( samvedna_get_field( 'treatments', array(), $post->ID ) ),
			'languages'       => samvedna_to_list( samvedna_get_field( 'languages', array( 'English', 'Hindi', 'Gujarati' ), $post->ID ) ),
			'consultation'    => samvedna_get_field( 'consultation', '', $post->ID ),
		);
	}
	return $out;
}

/**
 * FAQ items. CPT: `sam_faq` (title = question, content = answer).
 *
 * @return array
 */
function samvedna_get_faq() {
	$posts = samvedna_cpt_posts( 'sam_faq' );
	if ( empty( $posts ) ) {
		return samvedna_default_faq();
	}
	$out = array();
	foreach ( $posts as $post ) {
		$out[] = array(
			'question' => get_the_title( $post ),
			'answer'   => wp_strip_all_tags( $post->post_content ),
		);
	}
	return $out;
}

/**
 * Achievements. CPT: `achievement`. Meta: event, icon, location, icon_bg.
 *
 * @return array
 */
function samvedna_get_achievements() {
	$posts    = samvedna_cpt_posts( 'achievement' );
	$defaults = samvedna_default_achievements();
	if ( empty( $posts ) ) {
		return $defaults;
	}
	$out = array();
	foreach ( $posts as $i => $post ) {
		$d     = isset( $defaults[ $i ] ) ? $defaults[ $i ] : end( $defaults );
		$out[] = array(
			'title'       => get_the_title( $post ),
			'event'       => samvedna_get_field( 'event', $d['event'], $post->ID ),
			'description' => has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( $post->post_content ),
			'icon'        => samvedna_get_field( 'icon', $d['icon'], $post->ID ),
			'location'    => samvedna_get_field( 'location', $d['location'], $post->ID ),
			'icon_bg'     => $d['icon_bg'],
		);
	}
	return $out;
}

/**
 * Care plans. CPT: `care_plan`.
 *
 * @return array
 */
function samvedna_get_plans() {
	$posts = samvedna_cpt_posts( 'care_plan' );
	if ( empty( $posts ) ) {
		return samvedna_default_plans();
	}
	$out = array();
	foreach ( $posts as $post ) {
		$out[] = array(
			'name'     => get_the_title( $post ),
			'tagline'  => samvedna_get_field( 'tagline', '', $post->ID ),
			'duration' => samvedna_get_field( 'duration', '', $post->ID ),
			'price'    => samvedna_get_field( 'price', '', $post->ID ),
			'features' => samvedna_to_list( samvedna_get_field( 'features', array(), $post->ID ) ),
			'popular'  => (bool) samvedna_get_field( 'popular', false, $post->ID ),
			'cta'      => samvedna_get_field( 'cta', __( 'Get started', 'samvedna' ), $post->ID ),
		);
	}
	return $out;
}

/**
 * Video testimonials. CPT: `testimonial`.
 *
 * @return array
 */
function samvedna_get_video_testimonials() {
	$posts = samvedna_cpt_posts( 'testimonial' );
	if ( empty( $posts ) ) {
		// Defaults store bare filenames; resolve them to real theme image URLs.
		$defaults = samvedna_default_video_testimonials();
		foreach ( $defaults as &$item ) {
			$item['poster'] = samvedna_image_url( $item['poster'] );
		}
		unset( $item );
		return $defaults;
	}
	$out = array();
	foreach ( $posts as $post ) {
		$seed_img = get_post_meta( $post->ID, 'samvedna_image_file', true );
		$poster   = has_post_thumbnail( $post )
			? get_the_post_thumbnail_url( $post, 'samvedna-blog' )
			: samvedna_image_url( $seed_img ? $seed_img : 'assistant-doctor-cabin.webp' );
		$out[] = array(
			'youtube_id' => samvedna_get_field( 'youtube_id', '', $post->ID ),
			'poster'     => $poster,
			'alt'        => get_the_title( $post ),
			'name'       => samvedna_get_field( 'family_name', get_the_title( $post ), $post->ID ),
			'condition'  => samvedna_get_field( 'condition', '', $post->ID ),
			'location'   => samvedna_get_field( 'location', '', $post->ID ),
			'duration'   => samvedna_get_field( 'duration', '', $post->ID ),
		);
	}
	return $out;
}

/**
 * Blog cards for the front-page carousel. Uses real WP posts when available,
 * otherwise the sample posts from defaults.
 *
 * @param int $limit Max posts.
 * @return array
 */
function samvedna_get_blog_cards( $limit = 6 ) {
	$query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => $limit,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );

	if ( ! $query->have_posts() ) {
		$cards = array();
		foreach ( samvedna_default_blogs() as $post ) {
			$post['image'] = samvedna_image_url( $post['image'] );
			$post['url']   = '#';
			$cards[]       = $post;
		}
		return $cards;
	}

	$cards = array();
	foreach ( $query->posts as $post ) {
		$cats     = get_the_category( $post->ID );
		$category = ! empty( $cats ) ? $cats[0]->name : __( 'Article', 'samvedna' );
		$seed_img = get_post_meta( $post->ID, 'samvedna_image_file', true );
		$image    = has_post_thumbnail( $post )
			? get_the_post_thumbnail_url( $post, 'samvedna-blog' )
			: samvedna_image_url( $seed_img ? $seed_img : 'samvedna-auditorium.webp' );
		$cards[] = array(
			'title'     => get_the_title( $post ),
			'excerpt'   => has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 22 ),
			'image'     => $image,
			'alt'       => get_the_title( $post ),
			'category'  => $category,
			'date'      => get_the_date( 'M j, Y', $post ),
			'read_time' => samvedna_read_time( $post->ID ),
			'url'       => get_permalink( $post ),
		);
	}
	wp_reset_postdata();
	return $cards;
}

/**
 * Normalize an ACF value (array of rows, newline string, or array) into a flat
 * list of strings.
 *
 * @param mixed $value Raw value.
 * @return string[]
 */
function samvedna_to_list( $value ) {
	if ( is_string( $value ) ) {
		$value = preg_split( '/\r\n|\r|\n/', $value );
	}
	if ( ! is_array( $value ) ) {
		return array();
	}
	$out = array();
	foreach ( $value as $item ) {
		if ( is_array( $item ) ) {
			$item = reset( $item ); // ACF repeater single sub-field row.
		}
		$item = trim( (string) $item );
		if ( '' !== $item ) {
			$out[] = $item;
		}
	}
	return $out;
}
