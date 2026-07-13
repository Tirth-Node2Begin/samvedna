<?php
/**
 * Site footer (ports components/sections/Footer.tsx) + global modals.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$nav_items   = samvedna_nav_items();
$social      = samvedna_social_links();
$conditions  = samvedna_condition_list();
$logo_url    = samvedna_logo_url();
$current_year = (int) current_time( 'Y' );
?>
</main><!-- #main -->

<footer class="bg-bg-soft text-text border-t border-border">
	<div class="mx-auto grid max-w-content gap-10 px-5 py-16 md:grid-cols-2 md:px-8 lg:grid-cols-[1.2fr_0.8fr_0.9fr_1.1fr]">
		<div>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Samvedna Homeopathy', 'samvedna' ); ?>" width="157" height="50" class="rounded-control" decoding="async" loading="lazy" />
			<p class="mt-6 max-w-sm text-base leading-7 text-muted">
				<?php echo esc_html( samvedna_option( 'footer_about', 'Samvedna supports children with autism, ADHD, speech delay, learning difficulty, developmental delay, genetic concerns, and neurological disorders through personalized homeopathic care.' ) ); ?>
			</p>
			<div class="mt-6 flex flex-wrap gap-3">
				<?php foreach ( $social as $link ) : ?>
					<a href="<?php echo esc_url( $link['href'] ); ?>" target="_blank" rel="noreferrer"
						class="rounded-control border border-border px-3 py-2 text-sm font-semibold text-text transition hover:border-primary hover:text-primary">
						<?php echo esc_html( $link['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<div>
			<h2 class="font-display text-xl font-semibold"><?php esc_html_e( 'Quick Links', 'samvedna' ); ?></h2>
			<ul class="mt-5 space-y-3">
				<?php foreach ( $nav_items as $item ) : ?>
					<li>
						<a href="<?php echo esc_url( $item['href'] ); ?>" class="text-sm text-muted transition hover:text-primary">
							<?php echo esc_html( $item['label'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div>
			<h2 class="font-display text-xl font-semibold"><?php esc_html_e( 'Conditions Treated', 'samvedna' ); ?></h2>
			<ul class="mt-5 space-y-3">
				<?php foreach ( $conditions as $condition ) : ?>
					<li>
						<a href="<?php echo esc_url( home_url( '/#conditions' ) ); ?>" class="text-sm text-muted transition hover:text-primary">
							<?php echo esc_html( $condition ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div>
			<h2 class="font-display text-xl font-semibold"><?php esc_html_e( 'Contact', 'samvedna' ); ?></h2>
			<address class="mt-5 not-italic text-sm leading-7 text-muted">
				<?php echo esc_html( samvedna_contact( 'address' ) ); ?>
			</address>
			<div class="mt-5 space-y-2 text-sm text-muted">
				<p><a href="<?php echo esc_attr( samvedna_contact( 'phone_href' ) ); ?>" class="hover:text-primary"><?php echo esc_html( samvedna_contact( 'phone_primary' ) ); ?></a></p>
				<p><?php echo esc_html( samvedna_contact( 'phone_secondary' ) ); ?></p>
				<p><a href="mailto:<?php echo esc_attr( samvedna_contact( 'email' ) ); ?>" class="hover:text-primary"><?php echo esc_html( samvedna_contact( 'email' ) ); ?></a></p>
				<p><?php echo esc_html( samvedna_contact( 'hours' ) ); ?></p>
			</div>
		</div>
	</div>

	<div class="border-t border-border">
		<div class="mx-auto flex max-w-content flex-col gap-4 px-5 py-6 text-xs leading-6 text-muted md:flex-row md:items-center md:justify-between md:px-8">
			<p>
				<?php
				/* translators: %d: current year. */
				printf( esc_html__( 'Copyright %d Samvedna Homeopathy. All rights reserved.', 'samvedna' ), esc_html( $current_year ) );
				?>
			</p>
			<p><?php esc_html_e( 'Medical disclaimer: information on this site is educational and does not replace an individual consultation.', 'samvedna' ); ?></p>
		</div>
	</div>
</footer>

<?php
// Global consultation popup (ports components/ui/FormPopup.tsx). Shown by JS after a delay.
get_template_part( 'template-parts/global/form-popup' );
// Shared video story modal (ports components/ui/VideoStoryModal.tsx). Populated by JS.
get_template_part( 'template-parts/global/video-modal' );
?>

<?php wp_footer(); ?>
</body>
</html>
