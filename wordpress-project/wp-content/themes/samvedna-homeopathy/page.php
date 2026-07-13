<?php
/**
 * Standard page.
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
		get_template_part( 'template-parts/content/content-page' );

		if ( comments_open() || get_comments_number() ) {
			echo '<div class="mx-auto mt-16 max-w-3xl">';
			comments_template();
			echo '</div>';
		}
	endwhile;
	?>
</div>

<?php
get_footer();
