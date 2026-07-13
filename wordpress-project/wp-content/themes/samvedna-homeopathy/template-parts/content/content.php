<?php
/**
 * Blog post card used in archive/blog/search loops.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$cats     = get_the_category();
$category = ! empty( $cats ) ? $cats[0]->name : __( 'Article', 'samvedna' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'group flex h-full flex-col overflow-hidden rounded-card border border-border bg-white transition duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-premium' ); ?>>
	<a href="<?php the_permalink(); ?>" class="relative block aspect-[16/10] overflow-hidden bg-surface">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'samvedna-blog', array( 'class' => 'h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( samvedna_image_url( 'samvedna-auditorium.webp' ) ); ?>" alt="" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]" loading="lazy" decoding="async" />
		<?php endif; ?>
		<span class="pointer-events-none absolute left-3 top-3 z-10 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary shadow-sm backdrop-blur"><?php echo esc_html( $category ); ?></span>
	</a>
	<div class="flex flex-1 flex-col p-6">
		<div class="flex items-center gap-3 text-xs font-medium text-muted">
			<span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
			<span class="inline-flex items-center gap-1">
				<?php echo samvedna_icon( 'clock', 'h-3.5 w-3.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( samvedna_read_time() ); ?>
			</span>
		</div>
		<h2 class="mt-3 font-display text-lg font-semibold leading-snug text-text transition duration-200 group-hover:text-primary">
			<a href="<?php the_permalink(); ?>" class="focus-visible:underline"><?php the_title(); ?></a>
		</h2>
		<p class="mt-3 text-sm leading-6 text-muted"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<a href="<?php the_permalink(); ?>" class="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-primary">
			<?php esc_html_e( 'Read article', 'samvedna' ); ?>
			<?php echo samvedna_icon( 'arrow-up-right', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</article>
