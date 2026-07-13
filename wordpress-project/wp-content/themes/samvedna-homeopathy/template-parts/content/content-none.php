<?php
/**
 * "No results" placeholder.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="rounded-card border border-border bg-white p-10 text-center">
	<h2 class="font-display text-2xl font-semibold text-text"><?php esc_html_e( 'Nothing found', 'samvedna' ); ?></h2>
	<p class="mx-auto mt-4 max-w-md text-base leading-7 text-muted"><?php esc_html_e( 'We could not find what you were looking for. Try a different search, or head back to the homepage.', 'samvedna' ); ?></p>
	<div class="mt-6 flex justify-center"><?php get_search_form(); ?></div>
</div>
