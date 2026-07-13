<?php
/**
 * Single post content.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$cats = get_the_category();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'mx-auto max-w-3xl' ); ?>>
	<header class="text-center">
		<?php if ( ! empty( $cats ) ) : ?>
			<a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="text-sm font-semibold uppercase tracking-wider text-primary"><?php echo esc_html( $cats[0]->name ); ?></a>
		<?php endif; ?>
		<h1 class="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-5xl"><?php the_title(); ?></h1>
		<div class="mt-5 flex items-center justify-center gap-3 text-sm text-muted">
			<span><?php echo esc_html( get_the_date() ); ?></span>
			<span aria-hidden="true">·</span>
			<span class="inline-flex items-center gap-1"><?php echo samvedna_icon( 'clock', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( samvedna_read_time() ); ?></span>
			<span aria-hidden="true">·</span>
			<span><?php echo esc_html( get_the_author() ); ?></span>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="mt-10 overflow-hidden rounded-3xl border border-border">
			<?php the_post_thumbnail( 'large', array( 'class' => 'h-auto w-full object-cover', 'decoding' => 'async' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="prose-samvedna mt-10 text-base leading-8 text-text/90">
		<?php
		the_content();
		wp_link_pages( array(
			'before' => '<div class="mt-6 flex flex-wrap gap-2 text-sm font-semibold text-primary">' . esc_html__( 'Pages:', 'samvedna' ),
			'after'  => '</div>',
		) );
		?>
	</div>

	<?php if ( has_tag() ) : ?>
		<div class="mt-10 flex flex-wrap gap-2 border-t border-border pt-8">
			<?php
			$tags = get_the_tags();
			foreach ( (array) $tags as $tag ) :
				?>
				<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="rounded-control border border-border px-3 py-1 text-xs font-semibold text-muted transition hover:border-primary hover:text-primary">#<?php echo esc_html( $tag->name ); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</article>
