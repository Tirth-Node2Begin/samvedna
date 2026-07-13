<?php
/**
 * Single post.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="mx-auto max-w-content px-5 py-16 md:px-8 md:py-24">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content/content-single' );

		if ( comments_open() || get_comments_number() ) {
			echo '<div class="mx-auto mt-16 max-w-3xl">';
			comments_template();
			echo '</div>';
		}

		// Related posts (same category).
		$cats = wp_get_post_categories( get_the_ID() );
		if ( ! empty( $cats ) ) :
			$related = new WP_Query( array(
				'category__in'        => $cats,
				'post__not_in'        => array( get_the_ID() ),
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			) );
			if ( $related->have_posts() ) :
				?>
				<div class="mx-auto mt-20 max-w-content">
					<h2 class="font-display text-2xl font-semibold text-text"><?php esc_html_e( 'Related articles', 'samvedna' ); ?></h2>
					<div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
						<?php while ( $related->have_posts() ) : $related->the_post(); ?>
							<?php get_template_part( 'template-parts/content/content' ); ?>
						<?php endwhile; ?>
					</div>
				</div>
				<?php
			endif;
			wp_reset_postdata();
		endif;
	endwhile;
	?>
</div>

<?php
get_footer();
