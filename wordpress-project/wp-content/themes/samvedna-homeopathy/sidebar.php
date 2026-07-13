<?php
/**
 * Blog sidebar.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="space-y-6 lg:sticky lg:top-28 lg:self-start" aria-label="<?php esc_attr_e( 'Blog sidebar', 'samvedna' ); ?>">
	<?php if ( is_active_sidebar( 'sidebar-blog' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-blog' ); ?>
	<?php else : ?>
		<section class="widget rounded-card border border-border bg-white p-6">
			<h2 class="font-display text-lg font-semibold text-text mb-4"><?php esc_html_e( 'Search', 'samvedna' ); ?></h2>
			<?php get_search_form(); ?>
		</section>

		<section class="widget rounded-card border border-border bg-white p-6">
			<h2 class="font-display text-lg font-semibold text-text mb-4"><?php esc_html_e( 'Recent articles', 'samvedna' ); ?></h2>
			<ul class="space-y-3">
				<?php
				$recent = get_posts( array( 'numberposts' => 5, 'post_status' => 'publish' ) );
				foreach ( $recent as $post ) :
					?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="text-sm leading-6 text-muted transition hover:text-primary"><?php echo esc_html( get_the_title( $post ) ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section class="widget rounded-card border border-border bg-white p-6">
			<h2 class="font-display text-lg font-semibold text-text mb-4"><?php esc_html_e( 'Categories', 'samvedna' ); ?></h2>
			<ul class="space-y-3 [&_a]:text-sm [&_a]:text-muted [&_a:hover]:text-primary">
				<?php wp_list_categories( array( 'title_li' => '', 'show_count' => true ) ); ?>
			</ul>
		</section>
	<?php endif; ?>
</aside>
