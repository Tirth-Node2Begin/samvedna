<?php
/**
 * Search results.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="border-b border-border bg-bg-soft">
	<div class="mx-auto max-w-content px-5 py-16 text-center md:px-8 md:py-20">
		<p class="text-sm font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Search results', 'samvedna' ); ?></p>
		<h1 class="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl">
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Results for “%s”', 'samvedna' ), '<span class="text-primary">' . esc_html( get_search_query() ) . '</span>' );
			?>
		</h1>
		<div class="mx-auto mt-6 max-w-md"><?php get_search_form(); ?></div>
	</div>
</section>

<div class="mx-auto max-w-content px-5 py-16 md:px-8 md:py-20">
	<div class="grid gap-10 lg:grid-cols-[1fr_320px]">
		<div><?php get_template_part( 'template-parts/loop-grid' ); ?></div>
		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
