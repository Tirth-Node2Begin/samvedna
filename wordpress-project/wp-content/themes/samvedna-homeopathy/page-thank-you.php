<?php
/**
 * Template Name: Thank You
 *
 * Ports app/thank-you/page.tsx. Assign this template to a page with the slug
 * "thank-you" (the form redirects here after a successful submission).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

// Discourage indexing of the thank-you page.
add_action( 'wp_head', function () { echo '<meta name="robots" content="noindex,follow" />' . "\n"; }, 0 );

get_header();
?>
<section class="relative flex min-h-[80vh] items-center justify-center overflow-hidden bg-gradient-to-br from-primary via-primary-dark to-secondary px-5 py-28">
	<div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse at top, rgba(255,255,255,0.18), transparent 60%)"></div>

	<div class="relative w-full max-w-xl rounded-3xl bg-white p-8 text-center shadow-premium sm:p-12">
		<div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-success/10">
			<?php echo samvedna_icon( 'check-circle', 'h-9 w-9 text-success' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<h1 class="mt-6 font-display text-3xl font-semibold text-text md:text-4xl"><?php esc_html_e( 'Thank you for reaching out', 'samvedna' ); ?></h1>
		<p class="mx-auto mt-4 max-w-md text-base leading-7 text-muted"><?php esc_html_e( "The Samvedna care desk has received your request and will contact you shortly to guide you through the next step for your child's care.", 'samvedna' ); ?></p>

		<div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
			<?php
			echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'    => __( 'Back to home', 'samvedna' ),
				'href'     => home_url( '/' ),
				'size'     => 'lg',
				'icon'     => 'home',
				'icon_pos' => 'left',
			) );
			echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'    => __( 'Chat on WhatsApp', 'samvedna' ),
				'href'     => samvedna_contact( 'whatsapp_href' ),
				'size'     => 'lg',
				'variant'  => 'secondary',
				'icon'     => 'message-circle',
				'icon_pos' => 'left',
				'target'   => '_blank',
				'rel'      => 'noopener noreferrer',
			) );
			?>
		</div>
	</div>
</section>

<?php
get_footer();
