<?php
/**
 * One-time content seeder.
 *
 * Creates the required pages (Home / Blog / Thank You), seeds the custom post
 * types from the theme's bundled defaults, and adds sample blog posts so the
 * site is populated immediately after activation. Runs once (guarded by an
 * option) and is safe to re-run.
 *
 * @package Samvedna_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seed default content if it hasn't been seeded yet.
 */
function samvedna_core_seed_content() {
	if ( get_option( 'samvedna_seeded' ) === SAMVEDNA_CORE_VERSION ) {
		return;
	}

	// Load the theme's default data (works even if the theme isn't the active one yet).
	if ( ! function_exists( 'samvedna_default_team' ) ) {
		$defaults = get_theme_root() . '/samvedna-homeopathy/inc/defaults.php';
		if ( file_exists( $defaults ) ) {
			require_once $defaults;
		}
	}
	if ( ! function_exists( 'samvedna_default_team' ) ) {
		return; // Theme not present; nothing to seed yet.
	}

	samvedna_seed_pages();
	samvedna_seed_doctors();
	samvedna_seed_conditions();
	samvedna_seed_faqs();
	samvedna_seed_plans();
	samvedna_seed_achievements();
	samvedna_seed_testimonials();
	samvedna_seed_posts();

	update_option( 'samvedna_seeded', SAMVEDNA_CORE_VERSION );
}
add_action( 'admin_init', 'samvedna_core_seed_content' );

/**
 * Find or create a page by title.
 *
 * @param string $title    Page title.
 * @param string $slug     Page slug.
 * @param string $template Optional page template file.
 * @return int Page ID.
 */
function samvedna_seed_get_page( $title, $slug, $template = '' ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}
	$id = wp_insert_post( array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	) );
	if ( $id && $template ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	return $id ? $id : 0;
}

/**
 * Create the core pages and set reading options.
 */
function samvedna_seed_pages() {
	$home = samvedna_seed_get_page( __( 'Home', 'samvedna-core' ), 'home' );
	$blog = samvedna_seed_get_page( __( 'Blog', 'samvedna-core' ), 'blog' );
	samvedna_seed_get_page( __( 'Thank You', 'samvedna-core' ), 'thank-you', 'page-thank-you.php' );

	if ( $home && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		if ( $blog ) {
			update_option( 'page_for_posts', $blog );
		}
	}
}

/**
 * Sideload a theme image into the media library.
 *
 * @param string $file   Filename within the theme's assets/images/.
 * @param int    $parent Parent post ID.
 * @return int Attachment ID (0 on failure).
 */
function samvedna_seed_image( $file, $parent = 0 ) {
	if ( ! $file ) {
		return 0;
	}
	$path = get_theme_root() . '/samvedna-homeopathy/assets/images/' . $file;
	if ( ! file_exists( $path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$dest = trailingslashit( $upload['path'] ) . wp_unique_filename( $upload['path'], $file );
	if ( ! @copy( $path, $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
		return 0;
	}

	$filetype   = wp_check_filetype( $dest, null );
	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => sanitize_file_name( pathinfo( $file, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id = wp_insert_attachment( $attachment, $dest, $parent );
	if ( ! $attach_id ) {
		return 0;
	}
	$meta = wp_generate_attachment_metadata( $attach_id, $dest );
	wp_update_attachment_metadata( $attach_id, $meta );
	return $attach_id;
}

/**
 * Insert a CPT entry with meta + optional featured image.
 *
 * @param array $args { post_type, title, content, excerpt, menu_order, meta[], image }
 * @return int Post ID.
 */
function samvedna_seed_post( $args ) {
	$id = wp_insert_post( array(
		'post_type'    => $args['post_type'],
		'post_title'   => $args['title'],
		'post_content' => isset( $args['content'] ) ? $args['content'] : '',
		'post_excerpt' => isset( $args['excerpt'] ) ? $args['excerpt'] : '',
		'post_status'  => 'publish',
		'menu_order'   => isset( $args['menu_order'] ) ? (int) $args['menu_order'] : 0,
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	if ( ! empty( $args['meta'] ) ) {
		foreach ( $args['meta'] as $k => $v ) {
			update_post_meta( $id, $k, $v );
		}
	}
	if ( ! empty( $args['image'] ) ) {
		update_post_meta( $id, 'samvedna_image_file', $args['image'] );
		$att = samvedna_seed_image( $args['image'], $id );
		if ( $att ) {
			set_post_thumbnail( $id, $att );
		}
	}
	return $id;
}

/** Seed doctors. */
function samvedna_seed_doctors() {
	foreach ( samvedna_default_team() as $i => $m ) {
		samvedna_seed_post( array(
			'post_type'  => 'doctor',
			'title'      => $m['name'],
			'content'    => $m['about'],
			'excerpt'    => $m['summary'],
			'menu_order' => $i,
			'image'      => $m['image'],
			'meta'       => array(
				'doctor_title'    => $m['title'],
				'specialization'  => $m['specialization'],
				'experience'      => $m['experience'],
				'about'           => $m['about'],
				'qualifications'  => implode( "\n", $m['qualifications'] ),
				'specializations' => implode( "\n", $m['specializations'] ),
				'treatments'      => implode( "\n", $m['treatments'] ),
				'languages'       => implode( "\n", $m['languages'] ),
				'consultation'    => $m['consultation'],
			),
		) );
	}
}

/** Seed conditions. */
function samvedna_seed_conditions() {
	foreach ( samvedna_default_conditions() as $i => $c ) {
		samvedna_seed_post( array(
			'post_type'  => 'sam_condition',
			'title'      => $c['name'],
			'excerpt'    => $c['description'],
			'content'    => $c['description'],
			'menu_order' => $i,
			'meta'       => array( 'icon' => $c['icon'], 'span' => $c['span'], 'variant' => $c['variant'] ),
		) );
	}
}

/** Seed FAQs. */
function samvedna_seed_faqs() {
	foreach ( samvedna_default_faq() as $i => $f ) {
		samvedna_seed_post( array(
			'post_type'  => 'sam_faq',
			'title'      => $f['question'],
			'content'    => $f['answer'],
			'menu_order' => $i,
		) );
	}
}

/** Seed care plans. */
function samvedna_seed_plans() {
	foreach ( samvedna_default_plans() as $i => $p ) {
		samvedna_seed_post( array(
			'post_type'  => 'care_plan',
			'title'      => $p['name'],
			'menu_order' => $i,
			'meta'       => array(
				'tagline'  => $p['tagline'],
				'duration' => $p['duration'],
				'price'    => $p['price'],
				'features' => implode( "\n", $p['features'] ),
				'popular'  => $p['popular'] ? '1' : '',
				'cta'      => $p['cta'],
			),
		) );
	}
}

/** Seed achievements. */
function samvedna_seed_achievements() {
	foreach ( samvedna_default_achievements() as $i => $a ) {
		samvedna_seed_post( array(
			'post_type'  => 'achievement',
			'title'      => $a['title'],
			'excerpt'    => $a['description'],
			'content'    => $a['description'],
			'menu_order' => $i,
			'meta'       => array( 'event' => $a['event'], 'location' => $a['location'], 'icon' => $a['icon'] ),
		) );
	}
}

/** Seed video testimonials. */
function samvedna_seed_testimonials() {
	foreach ( samvedna_default_video_testimonials() as $i => $v ) {
		samvedna_seed_post( array(
			'post_type'  => 'testimonial',
			'title'      => $v['alt'],
			'menu_order' => $i,
			'image'      => $v['poster'],
			'meta'       => array(
				'youtube_id'  => $v['youtube_id'],
				'family_name' => $v['name'],
				'condition'   => $v['condition'],
				'location'    => $v['location'],
				'duration'    => $v['duration'],
			),
		) );
	}
}

/** Seed sample blog posts. */
function samvedna_seed_posts() {
	foreach ( samvedna_default_blogs() as $i => $b ) {
		if ( get_page_by_path( $b['slug'], OBJECT, 'post' ) ) {
			continue;
		}
		$date = gmdate( 'Y-m-d H:i:s', strtotime( $b['date'] ) ?: time() );
		$id   = wp_insert_post( array(
			'post_type'    => 'post',
			'post_title'   => $b['title'],
			'post_name'    => $b['slug'],
			'post_excerpt' => $b['excerpt'],
			'post_content' => '<p>' . esc_html( $b['excerpt'] ) . '</p><p>' . esc_html__( 'This is sample article content. Replace it with the full article from the Samvedna editorial team.', 'samvedna-core' ) . '</p>',
			'post_status'  => 'publish',
			'post_date'    => $date,
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$term = term_exists( $b['category'], 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $b['category'], 'category' );
		}
		if ( ! is_wp_error( $term ) ) {
			wp_set_post_categories( $id, array( (int) ( is_array( $term ) ? $term['term_id'] : $term ) ) );
		}
		update_post_meta( $id, 'samvedna_image_file', $b['image'] );
		$att = samvedna_seed_image( $b['image'], $id );
		if ( $att ) {
			set_post_thumbnail( $id, $att );
		}
	}
}
