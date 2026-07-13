<?php
/**
 * Markup helpers — inline icons, scroll reveals, animated headings.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icon set (mirrors the lucide-react icons used in the Next project).
 *
 * @param string $name   Icon key.
 * @param string $class  CSS classes for the <svg>.
 * @param float  $stroke Stroke width.
 * @param string $fill   Fill ("none" or "currentColor" for solid icons).
 * @return string
 */
function samvedna_icon( $name, $class = 'h-6 w-6', $stroke = 2, $fill = 'none' ) {
	$paths = array(
		'activity'        => '<path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/>',
		'heart-pulse'     => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>',
		'stethoscope'     => '<path d="M11 2v2"/><path d="M5 2v2"/><path d="M5 3H4a2 2 0 0 0-2 2v4a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-1"/><path d="M8 15a6 6 0 0 0 12 0v-3"/><circle cx="20" cy="10" r="2"/>',
		'award'           => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
		'shield-check'    => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
		'brain'           => '<path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z"/><path d="M12 5a3 3 0 1 1 5.997.125 4 4 0 0 1 2.526 5.77 4 4 0 0 1-.556 6.588A4 4 0 1 1 12 18Z"/><path d="M15 13a4.5 4.5 0 0 1-3-4 4.5 4.5 0 0 1-3 4"/><path d="M17.599 6.5a3 3 0 0 0 .399-1.375"/><path d="M6.003 5.125A3 3 0 0 0 6.401 6.5"/><path d="M3.477 10.896a4 4 0 0 1 .585-.396"/><path d="M19.938 10.5a4 4 0 0 1 .585.396"/><path d="M6 18a4 4 0 0 1-1.967-.516"/><path d="M19.967 17.484A4 4 0 0 1 18 18"/>',
		'message-circle'  => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
		'sprout'          => '<path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>',
		'book-open'       => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
		'sparkles'        => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .962 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.962 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>',
		'arrow-up-right'  => '<path d="M7 7h10v10"/><path d="M7 17 17 7"/>',
		'globe'           => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
		'map-pin'         => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
		'medal'           => '<path d="M7.21 15 2.66 7.14a2 2 0 0 1 .13-2.2L4.4 2.8A2 2 0 0 1 6 2h12a2 2 0 0 1 1.6.8l1.6 2.14a2 2 0 0 1 .14 2.2L16.5 15"/><path d="M11 12 5.12 2.2"/><path d="m13 12 5.88-9.8"/><path d="M8 7h8"/><circle cx="12" cy="17" r="5"/><path d="M12 18v-2h-.5"/>',
		'mic'             => '<path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/>',
		'trophy'          => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
		'users'           => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
		'heart-handshake' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66"/><path d="m18 15-2-2"/><path d="m15 18-2-2"/>',
		'star'            => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
		'video'           => '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
		'badge-check'     => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
		'calendar-clock'  => '<path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5"/><path d="M17.5 17.5 16 16.3V14"/><circle cx="16" cy="16" r="6"/>',
		'graduation-cap'  => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>',
		'languages'       => '<path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/>',
		'chevron-down'    => '<path d="m6 9 6 6 6-6"/>',
		'chevron-left'    => '<path d="m15 18-6-6 6-6"/>',
		'chevron-right'   => '<path d="m9 18 6-6-6-6"/>',
		'quote'           => '<path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/><path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>',
		'phone'           => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'loader-circle'   => '<path d="M21 12a9 9 0 1 1-6.219-8.56"/>',
		'send'            => '<path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/>',
		'menu'            => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
		'x'               => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'play'            => '<polygon points="6 3 20 12 6 21 6 3"/>',
		'clock'           => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
		'check-circle'    => '<path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/>',
		'home'            => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
		'check'           => '<polyline points="20 6 9 17 4 12"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="%s" viewBox="0 0 24 24" fill="%s" stroke="currentColor" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
		esc_attr( $class ),
		esc_attr( $fill ),
		esc_attr( (string) $stroke ),
		$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
	);
}

/**
 * Echo a scroll-reveal attribute string for any element.
 *
 * Variants: fadeUp | fadeIn | scaleIn | slideLeft | slideRight.
 *
 * @param string $variant Reveal variant.
 * @param float  $delay   Delay in seconds.
 * @return string
 */
function samvedna_reveal_attr( $variant = 'fadeUp', $delay = 0 ) {
	$attr = sprintf( ' data-reveal="%s"', esc_attr( $variant ) );
	if ( $delay > 0 ) {
		$attr .= sprintf( ' data-reveal-delay="%s"', esc_attr( (string) round( (float) $delay, 3 ) ) );
	}
	return $attr;
}

/**
 * Open a reveal wrapper <div> (mirrors the AnimatedReveal component).
 *
 * @param string $class   Wrapper classes.
 * @param string $variant Reveal variant.
 * @param float  $delay   Delay in seconds.
 */
function samvedna_reveal_open( $class = '', $variant = 'fadeUp', $delay = 0 ) {
	printf(
		'<div class="%s"%s>',
		esc_attr( $class ),
		samvedna_reveal_attr( $variant, $delay ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped.
	);
}

/**
 * Close a reveal wrapper opened with samvedna_reveal_open().
 */
function samvedna_reveal_close() {
	echo '</div>';
}

/**
 * Render an animated, word-by-word heading (mirrors AnimatedText).
 *
 * @param string $text    Heading text (plain).
 * @param string $tag     h1|h2|h3|p|span.
 * @param string $class   CSS classes.
 * @param float  $delay   Stagger start delay in seconds.
 */
function samvedna_animated_text( $text, $tag = 'span', $class = '', $delay = 0 ) {
	$allowed = array( 'h1', 'h2', 'h3', 'p', 'span' );
	$tag     = in_array( $tag, $allowed, true ) ? $tag : 'span';
	$words   = preg_split( '/\s+/', trim( $text ) );

	printf(
		'<%1$s class="reveal-text %2$s" data-reveal-text aria-label="%3$s"%4$s>',
		$tag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted.
		esc_attr( $class ),
		esc_attr( $text ),
		$delay > 0 ? ' data-reveal-delay="' . esc_attr( (string) $delay ) . '"' : ''
	);

	echo '<span aria-hidden="true">';
	$last = count( $words ) - 1;
	foreach ( $words as $i => $word ) {
		echo '<span class="reveal-word-wrap"><span class="reveal-word">' . esc_html( $word ) . '</span></span>';
		if ( $i < $last ) {
			echo ' ';
		}
	}
	echo '</span>';

	printf( '</%s>', $tag ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted.
}

/**
 * Build a link/button matching the original Button component.
 *
 * @param array $args {
 *     @type string $label    Visible text.
 *     @type string $href     URL (renders an <a>; omit for a <button>).
 *     @type string $variant  primary|secondary|ghost|light.
 *     @type string $size     sm|md|lg.
 *     @type string $class    Extra classes.
 *     @type string $icon     Icon key (samvedna_icon) or raw SVG markup.
 *     @type string $icon_pos left|right.
 *     @type string $target   Anchor target.
 *     @type string $rel      Anchor rel.
 *     @type string $type     Button type when no href.
 *     @type array  $attrs    Extra HTML attributes (key => value).
 * }
 * @return string
 */
function samvedna_button( $args = array() ) {
	$a = wp_parse_args( $args, array(
		'label'    => '',
		'href'     => '',
		'variant'  => 'primary',
		'size'     => 'md',
		'class'    => '',
		'icon'     => '',
		'icon_pos' => 'right',
		'target'   => '',
		'rel'      => '',
		'type'     => 'button',
		'attrs'    => array(),
	) );

	$variants = array(
		'primary'   => 'border-transparent bg-gradient-to-r from-primary to-secondary text-white hover:opacity-90 shadow-md',
		'secondary' => 'border-border bg-white text-text hover:border-primary hover:text-primary',
		'ghost'     => 'border-border bg-transparent text-text hover:border-primary hover:bg-bg-soft hover:text-primary',
		'light'     => 'border-white/30 bg-white text-primary hover:border-white hover:bg-bg-soft',
	);
	$sizes = array(
		'sm' => 'h-10 px-4 text-sm',
		'md' => 'h-12 px-5 text-[15px]',
		'lg' => 'h-14 px-6 text-base',
	);

	$classes = trim( sprintf(
		'inline-flex items-center justify-center gap-2 rounded-control border font-semibold leading-none transition duration-200 disabled:pointer-events-none disabled:opacity-60 %s %s %s',
		isset( $sizes[ $a['size'] ] ) ? $sizes[ $a['size'] ] : $sizes['md'],
		isset( $variants[ $a['variant'] ] ) ? $variants[ $a['variant'] ] : $variants['primary'],
		$a['class']
	) );

	// Icon may be a known key or raw SVG.
	$icon_html = '';
	if ( $a['icon'] ) {
		$icon_html = preg_match( '/^[a-z0-9-]+$/', $a['icon'] ) ? samvedna_icon( $a['icon'], 'h-5 w-5' ) : $a['icon'];
	}

	$inner  = ( 'left' === $a['icon_pos'] ) ? $icon_html : '';
	$inner .= '<span>' . esc_html( $a['label'] ) . '</span>';
	$inner .= ( 'right' === $a['icon_pos'] ) ? $icon_html : '';

	$extra = '';
	foreach ( $a['attrs'] as $k => $v ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
	}

	if ( $a['href'] ) {
		return sprintf(
			'<a href="%s" class="%s"%s%s%s>%s</a>',
			esc_url( $a['href'] ),
			esc_attr( $classes ),
			$a['target'] ? ' target="' . esc_attr( $a['target'] ) . '"' : '',
			$a['rel'] ? ' rel="' . esc_attr( $a['rel'] ) . '"' : '',
			$extra,
			$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- composed of escaped parts.
		);
	}

	return sprintf(
		'<button type="%s" class="%s"%s>%s</button>',
		esc_attr( $a['type'] ),
		esc_attr( $classes ),
		$extra,
		$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- composed of escaped parts.
	);
}
