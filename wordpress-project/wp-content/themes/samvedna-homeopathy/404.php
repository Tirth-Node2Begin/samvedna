<?php
/**
 * 404 (ports app/not-found.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="flex min-h-[70vh] items-center bg-bg-soft px-5 py-20">
	<div class="mx-auto max-w-2xl text-center">
		<p class="text-sm font-semibold text-primary"><?php esc_html_e( 'Page not found', 'samvedna' ); ?></p>
		<h1 class="mt-4 font-display text-5xl font-semibold leading-tight text-text"><?php esc_html_e( 'This page is not available.', 'samvedna' ); ?></h1>
		<p class="mt-6 text-lg leading-8 text-muted"><?php esc_html_e( 'The Samvedna Homeopathy homepage has the consultation details, conditions, approach, and contact information you may need.', 'samvedna' ); ?></p>
		<div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
			<?php
			echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label' => __( 'Return Home', 'samvedna' ),
				'href'  => home_url( '/' ),
			) );
			?>
			<div class="w-full max-w-xs"><?php get_search_form(); ?></div>
		</div>
	</div>
</section>

<?php
get_footer();
