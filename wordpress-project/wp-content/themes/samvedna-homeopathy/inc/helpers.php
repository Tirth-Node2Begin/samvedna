<?php
/**
 * Field/content helpers with graceful fallbacks.
 *
 * Every getter works with OR without Advanced Custom Fields installed: when ACF
 * is active and a value is set it is returned, otherwise the baked-in default
 * from inc/defaults.php is used. This keeps the site pixel-perfect on a fresh
 * install and fully editable once ACF + content are in place.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a value from the ACF options page, falling back to a default.
 *
 * @param string $key     Field key on the options page.
 * @param mixed  $default Default when ACF is absent or the field is empty.
 * @return mixed
 */
function samvedna_option( $key, $default = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $key, 'option' );
		if ( null !== $value && '' !== $value && array() !== $value ) {
			return $value;
		}
	}
	return $default;
}

/**
 * Get an ACF field for a post, falling back to a default.
 *
 * @param string    $field_name Field name.
 * @param mixed     $fallback   Fallback value.
 * @param int|false $post_id    Optional post ID (defaults to current post).
 * @return mixed
 */
function samvedna_get_field( $field_name, $fallback = '', $post_id = false ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, $post_id );
		if ( null !== $value && '' !== $value && array() !== $value ) {
			return $value;
		}
	}
	// Fallback to raw post meta so seeded content still resolves without ACF.
	$pid = $post_id ? $post_id : get_the_ID();
	if ( $pid ) {
		$meta = get_post_meta( $pid, $field_name, true );
		if ( '' !== $meta && null !== $meta ) {
			return $meta;
		}
	}
	return $fallback;
}

/**
 * Get an ACF sub-field (inside have_rows loops), with a fallback.
 *
 * @param string $field_name Sub-field name.
 * @param mixed  $fallback   Fallback value.
 * @return mixed
 */
function samvedna_get_sub_field( $field_name, $fallback = '' ) {
	if ( function_exists( 'get_sub_field' ) ) {
		$value = get_sub_field( $field_name );
		if ( null !== $value && '' !== $value && array() !== $value ) {
			return $value;
		}
	}
	return $fallback;
}

/**
 * Resolve a single contact detail.
 *
 * Looks for an ACF option named `contact_{key}` first, then the baked-in
 * default. Example keys: phone_primary, phone_secondary, phone_href,
 * whatsapp_href, email, address, hours.
 *
 * @param string $key     Contact key.
 * @param string $default Optional explicit default (otherwise pulled from defaults).
 * @return string
 */
function samvedna_contact( $key, $default = '' ) {
	$defaults = samvedna_default_contact();
	$fallback = '' !== $default ? $default : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
	return (string) samvedna_option( 'contact_' . $key, $fallback );
}

/**
 * Resolve an image to a URL.
 *
 * Accepts: a bare filename present in assets/images/, a full URL, an attachment
 * ID, or an ACF image array. Always returns a usable URL string.
 *
 * @param mixed  $image Filename | URL | attachment ID | ACF image array.
 * @param string $size  Registered image size when resolving attachments.
 * @return string
 */
function samvedna_image_url( $image, $size = 'large' ) {
	if ( empty( $image ) ) {
		return '';
	}

	if ( is_array( $image ) ) {
		if ( isset( $image['sizes'][ $size ] ) ) {
			return $image['sizes'][ $size ];
		}
		return isset( $image['url'] ) ? $image['url'] : '';
	}

	if ( is_numeric( $image ) ) {
		$url = wp_get_attachment_image_url( (int) $image, $size );
		return $url ? $url : '';
	}

	if ( preg_match( '#^https?://#', $image ) || str_starts_with( $image, '/' ) ) {
		return $image;
	}

	return SAMVEDNA_URI . '/assets/images/' . ltrim( $image, '/' );
}

/**
 * Primary navigation items.
 *
 * Returns simple {label, href} rows. If a "primary" menu is assigned, its items
 * are used; otherwise the baked-in anchor links are returned.
 *
 * @return array<int,array{label:string,href:string}>
 */
function samvedna_nav_items() {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		$items = wp_get_nav_menu_items( $locations['primary'] );
		if ( $items ) {
			$out = array();
			foreach ( $items as $item ) {
				if ( (int) $item->menu_item_parent !== 0 ) {
					continue; // Top-level only; the design has no dropdowns by default.
				}
				$out[] = array(
					'label' => $item->title,
					'href'  => $item->url,
				);
			}
			if ( $out ) {
				return $out;
			}
		}
	}

	$items = samvedna_default_nav_items();

	// On non-front pages, point in-page anchors back to the home page so the
	// section links keep working everywhere.
	if ( ! is_front_page() ) {
		$home = trailingslashit( home_url( '/' ) );
		foreach ( $items as &$item ) {
			if ( isset( $item['href'][0] ) && '#' === $item['href'][0] ) {
				$item['href'] = $home . $item['href'];
			}
		}
		unset( $item );
	}

	return $items;
}

/**
 * Social links (ACF repeater `social_links` of {label,href} or defaults).
 *
 * @return array<int,array{label:string,href:string}>
 */
function samvedna_social_links() {
	$links = samvedna_option( 'social_links', array() );
	if ( is_array( $links ) && ! empty( $links ) ) {
		$out = array();
		foreach ( $links as $row ) {
			$label = isset( $row['label'] ) ? $row['label'] : '';
			$href  = isset( $row['href'] ) ? $row['href'] : ( isset( $row['url'] ) ? $row['url'] : '' );
			if ( $label && $href ) {
				$out[] = array( 'label' => $label, 'href' => $href );
			}
		}
		if ( $out ) {
			return $out;
		}
	}
	return samvedna_default_social_links();
}

/**
 * The site logo URL (custom logo if set, else the bundled asset).
 *
 * @return string
 */
function samvedna_logo_url() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return SAMVEDNA_URI . '/assets/images/samvedna-logo.webp';
}

/**
 * The "Book Consultation" button URL used across the header/CTAs.
 *
 * @return string
 */
function samvedna_appointment_url() {
	return (string) samvedna_option( 'appointment_url', 'https://autismhomeohelp.com/online-consulting/' );
}

/**
 * Read time estimate for a post, in the "N min read" format.
 *
 * @param int|null $post_id Optional post ID.
 * @return string
 */
function samvedna_read_time( $post_id = null ) {
	$content = get_post_field( 'post_content', $post_id ?: get_the_ID() );
	$words   = max( 1, str_word_count( wp_strip_all_tags( (string) $content ) ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d: estimated reading time in minutes. */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'samvedna' ), $minutes );
}
