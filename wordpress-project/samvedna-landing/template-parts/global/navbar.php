<?php
/**
 * Primary navigation (ports components/navigation/Navbar.tsx + MobileMenu.tsx).
 *
 * Scroll behaviour (hide on scroll-down, solid background after 80px) is handled
 * in assets/js/main.js via the [data-navbar] hooks. On non-front pages the bar
 * starts in its solid state since there is no hero behind it.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$nav_items   = samvedna_nav_items();
$logo_url    = samvedna_logo_url();
$is_front    = true;
$start_solid = $is_front ? 'false' : 'true';
?>
<header
	class="site-header fixed left-0 right-0 top-0 z-40 transition duration-300 translate-y-0 <?php echo $is_front ? 'bg-transparent' : 'is-solid border-b border-border bg-white/95 shadow-[0_10px_40px_rgba(17,24,39,0.06)] backdrop-blur-md'; ?>"
	data-navbar
	data-navbar-start-solid="<?php echo esc_attr( $start_solid ); ?>"
>
	<div class="mx-auto flex h-20 max-w-content items-center justify-between px-5 md:px-8">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3" aria-label="<?php esc_attr_e( 'Samvedna Homeopathy home', 'samvedna' ); ?>">
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Samvedna Homeopathy', 'samvedna' ); ?>" width="126" height="40" fetchpriority="high" decoding="async" />
		</a>

		<nav class="hidden items-center gap-7 lg:flex" aria-label="<?php esc_attr_e( 'Primary navigation', 'samvedna' ); ?>">
			<?php foreach ( $nav_items as $item ) : ?>
				<div class="group relative">
					<a
						href="<?php echo esc_url( $item['href'] ); ?>"
						class="nav-link flex items-center gap-1 py-4 text-[13px] font-bold uppercase tracking-wide transition"
					>
						<?php echo esc_html( $item['label'] ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</nav>

		<div class="hidden lg:block">
			<?php
			echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes.
				'label' => __( 'Book Consultation', 'samvedna' ),
				'href'  => samvedna_appointment_url(),
				'size'  => 'sm',
			) );
			?>
		</div>

		<button
			type="button"
			aria-label="<?php esc_attr_e( 'Open menu', 'samvedna' ); ?>"
			data-menu-open
			class="inline-flex h-11 w-11 items-center justify-center rounded-control border border-border bg-white/80 text-text lg:hidden"
		>
			<?php echo samvedna_icon( 'menu', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>
</header>

<!-- Mobile menu (ports MobileMenu.tsx) -->
<div class="mobile-menu fixed inset-0 z-50 hidden bg-white px-5 py-5 lg:hidden" data-mobile-menu aria-hidden="true">
	<div class="mx-auto flex max-w-content items-center justify-between">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3" data-menu-close>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Samvedna Homeopathy', 'samvedna' ); ?>" width="126" height="40" decoding="async" />
		</a>
		<button
			type="button"
			aria-label="<?php esc_attr_e( 'Close menu', 'samvedna' ); ?>"
			data-menu-close
			class="inline-flex h-11 w-11 items-center justify-center rounded-control border border-border text-text"
		>
			<?php echo samvedna_icon( 'x', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>

	<nav class="mx-auto mt-16 flex max-w-content flex-col gap-2" aria-label="<?php esc_attr_e( 'Mobile navigation', 'samvedna' ); ?>">
		<?php foreach ( $nav_items as $i => $item ) : ?>
			<div class="mobile-menu-item border-b border-border py-4" style="--i: <?php echo (int) $i; ?>">
				<a href="<?php echo esc_url( $item['href'] ); ?>" data-menu-close class="block font-display text-2xl font-semibold uppercase tracking-wide text-text">
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			</div>
		<?php endforeach; ?>
		<div class="mobile-menu-item mt-8" style="--i: <?php echo count( $nav_items ); ?>">
			<?php
			echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label' => __( 'Book Consultation', 'samvedna' ),
				'href'  => samvedna_appointment_url(),
				'size'  => 'lg',
				'attrs' => array( 'data-menu-close' => '1' ),
			) );
			?>
		</div>
	</nav>
</div>
