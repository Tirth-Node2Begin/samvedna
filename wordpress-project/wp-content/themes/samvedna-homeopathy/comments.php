<?php
/**
 * Comments template.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area rounded-card border border-border bg-white p-6 sm:p-8">
	<?php if ( have_comments() ) : ?>
		<h2 class="font-display text-2xl font-semibold text-text">
			<?php
			$count = get_comments_number();
			if ( '1' === (string) $count ) {
				esc_html_e( 'One comment', 'samvedna' );
			} else {
				/* translators: %s: comment count. */
				printf( esc_html__( '%s comments', 'samvedna' ), esc_html( number_format_i18n( $count ) ) );
			}
			?>
		</h2>

		<ol class="mt-6 space-y-6">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size' => 48,
			) );
			?>
		</ol>

		<?php
		the_comments_pagination( array(
			'prev_text' => esc_html__( 'Previous', 'samvedna' ),
			'next_text' => esc_html__( 'Next', 'samvedna' ),
		) );
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="mt-6 text-sm text-muted"><?php esc_html_e( 'Comments are closed.', 'samvedna' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'class_form'         => 'mt-8 space-y-4',
		'title_reply_before' => '<h3 class="font-display text-xl font-semibold text-text">',
		'title_reply_after'  => '</h3>',
		'class_submit'       => 'inline-flex h-12 items-center justify-center rounded-control border border-transparent bg-gradient-to-r from-primary to-secondary px-5 text-[15px] font-semibold text-white shadow-md transition hover:opacity-90',
	) );
	?>
</div>
