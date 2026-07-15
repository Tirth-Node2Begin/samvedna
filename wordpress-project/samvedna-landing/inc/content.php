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
			'name'      => get_the_title( $post ),
			'tagline'   => samvedna_get_field( 'tagline', '', $post->ID ),
			'duration'  => samvedna_get_field( 'duration', '', $post->ID ),
			'price'     => samvedna_get_field( 'price', '', $post->ID ),
			'price_usd' => samvedna_get_field( 'price_usd', '', $post->ID ),
			'features'  => samvedna_to_list( samvedna_get_field( 'features', array(), $post->ID ) ),
			'popular'   => (bool) samvedna_get_field( 'popular', false, $post->ID ),
			'cta'       => samvedna_get_field( 'cta', __( 'Get started', 'samvedna' ), $post->ID ),
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
	// 1) Structured CPT testimonials, if this install defines them.
	$posts = samvedna_cpt_posts( 'testimonial' );
	if ( ! empty( $posts ) ) {
		$out = array();
		foreach ( $posts as $post ) {
			$seed_img = get_post_meta( $post->ID, 'samvedna_image_file', true );
			$poster   = has_post_thumbnail( $post )
				? get_the_post_thumbnail_url( $post, 'large' )
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

	// 2) Live: the real videos pulled from the site's video-testimonials page (cached).
	$live = svl_fetch_youtube_testimonials();
	if ( ! empty( $live ) ) {
		return $live;
	}

	// 3) Bundled snapshot of those real YouTube testimonials (used if the fetch fails).
	//    Posters are already absolute ytimg URLs; only resolve bare local filenames.
	$defaults = samvedna_default_video_testimonials();
	foreach ( $defaults as &$item ) {
		if ( empty( $item['poster'] ) || ! preg_match( '~^https?://~i', $item['poster'] ) ) {
			$item['poster'] = samvedna_image_url( $item['poster'] );
		}
	}
	unset( $item );
	return $defaults;
}

/**
 * Fetch the site's own video-testimonials page and extract its YouTube videos, so
 * the landing page's testimonial marquee stays in sync with that page. Cached in a
 * transient (12h). Returns [] on any failure (caller falls back to the snapshot).
 *
 * Point it elsewhere with the 'svl_video_source_url' filter (return '' to disable
 * the live fetch), or replace the whole list with the 'svl_video_testimonials' filter.
 *
 * @return array
 */
function svl_fetch_youtube_testimonials() {
	$pre = apply_filters( 'svl_video_testimonials', null );
	if ( is_array( $pre ) ) {
		return $pre;
	}

	$url = apply_filters( 'svl_video_source_url', 'https://autismhomeohelp.com/video-testimonials/' );
	if ( empty( $url ) ) {
		return array();
	}

	$key    = 'svl_yt_testimonials_' . md5( $url );
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$res = wp_remote_get( $url, array(
		'timeout'    => 6,
		'user-agent' => 'Mozilla/5.0 (compatible; SamvednaLanding)',
	) );
	if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
		set_transient( $key, array(), 30 * MINUTE_IN_SECONDS ); // brief negative cache
		return array();
	}

	$videos = svl_parse_video_playlist( wp_remote_retrieve_body( $res ) );
	set_transient( $key, $videos, 12 * HOUR_IN_SECONDS );
	return $videos;
}

/**
 * Extract an Elementor "video-playlist" widget's videos (title / YouTube id /
 * duration) from a page's HTML into the card shape used by the marquee.
 *
 * @param string $html Raw page HTML.
 * @return array
 */
function svl_parse_video_playlist( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return array();
	}
	$decoded = html_entity_decode( $html, ENT_QUOTES, 'UTF-8' );

	// Anchor on the widget that actually holds videos, then back up to its "tabs":[ .
	$anchor = strpos( $decoded, 'youtube_url' );
	if ( false === $anchor ) {
		return array();
	}
	$pos = strrpos( substr( $decoded, 0, $anchor ), '"tabs":[' );
	if ( false === $pos ) {
		return array();
	}
	$start = strpos( $decoded, '[', $pos );
	$depth = 0;
	$end   = -1;
	$len   = strlen( $decoded );
	for ( $i = $start; $i < $len; $i++ ) {
		$ch = $decoded[ $i ];
		if ( '[' === $ch ) {
			$depth++;
		} elseif ( ']' === $ch ) {
			$depth--;
			if ( 0 === $depth ) {
				$end = $i;
				break;
			}
		}
	}
	if ( $end < 0 ) {
		return array();
	}
	$tabs = json_decode( substr( $decoded, $start, $end - $start + 1 ), true );
	if ( ! is_array( $tabs ) ) {
		return array();
	}

	$out = array();
	foreach ( $tabs as $tab ) {
		if ( ! is_array( $tab ) ) {
			continue;
		}
		$src = isset( $tab['youtube_url'] ) ? (string) $tab['youtube_url'] : '';
		if ( ! preg_match( '~(?:shorts/|watch\?v=|embed/|youtu\.be/|/vi/)([A-Za-z0-9_-]{11})~', $src, $m ) ) {
			continue;
		}
		$id    = $m[1];
		$title = isset( $tab['title'] ) ? trim( (string) $tab['title'] ) : '';
		$title = preg_replace( '/^\s*Title(?=[A-Za-z0-9])/', '', $title ); // stray "Title" artifact
		$title = preg_replace( '/\s*#\S.*$/u', '', $title );               // drop trailing hashtags
		$title = trim( $title, " \t\n\r\0\x0B\"" );
		$out[] = array(
			'youtube_id' => $id,
			'poster'     => 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg',
			'alt'        => $title,
			'name'       => '',
			'condition'  => $title,
			'location'   => '',
			'duration'   => isset( $tab['duration'] ) ? (string) $tab['duration'] : '',
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
	// Pull the site's own blog posts. Filterable so a site whose blog uses a custom
	// post type or should be limited to a category can adjust the query without code
	// edits, e.g. add_filter('svl_blog_query_args', fn($a)=>['category_name'=>'autism']+$a).
	$query = new WP_Query( apply_filters( 'svl_blog_query_args', array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) ) );

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
			? get_the_post_thumbnail_url( $post, 'large' ) // 'large' is a core size, always available on the host.
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
