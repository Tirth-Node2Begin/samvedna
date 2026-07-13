<?php
/**
 * Shared posts grid + pagination for the main query.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :
	?>
	<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content/content', get_post_type() );
		endwhile;
		?>
	</div>

	<div class="mt-12">
		<?php
		the_posts_pagination( array(
			'mid_size'           => 1,
			'prev_text'          => esc_html__( 'Previous', 'samvedna' ),
			'next_text'          => esc_html__( 'Next', 'samvedna' ),
			'screen_reader_text' => esc_html__( 'Posts navigation', 'samvedna' ),
			'class'              => 'samvedna-pagination',
		) );
		?>
	</div>
	<?php
else :
	get_template_part( 'template-parts/content/content-none' );
endif;
