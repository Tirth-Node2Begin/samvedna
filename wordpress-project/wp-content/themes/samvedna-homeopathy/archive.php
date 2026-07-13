<?php
/**
 * Archive (category, tag, date, author, custom taxonomy).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="border-b border-border bg-bg-soft">
	<div class="mx-auto max-w-content px-5 py-16 text-center md:px-8 md:py-20">
		<p class="text-sm font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Browsing', 'samvedna' ); ?></p>
		<h1 class="mt-4 font-display text-4xl font-semibold leading-tight text-text md:text-5xl"><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="mx-auto mt-5 max-w-2xl text-base leading-7 text-muted"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
		<?php endif; ?>
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
