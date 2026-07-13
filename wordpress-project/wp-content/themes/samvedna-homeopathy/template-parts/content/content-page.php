<?php
/**
 * Standard page content.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'mx-auto max-w-3xl' ); ?>>
	<header class="text-center">
		<h1 class="font-display text-3xl font-semibold leading-tight text-text md:text-5xl"><?php the_title(); ?></h1>
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
</article>
