<?php
/**
 * Why families trust Samvedna (ports components/sections/WhyFamiliesTrust.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$reasons = samvedna_option( 'trust_reasons', samvedna_default_trust_reasons() );
?>
<section class="bg-white pb-16 pt-8 md:pb-20 md:pt-12 lg:pb-[120px] lg:pt-16">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
			<div class="max-w-2xl" data-reveal="fadeUp">
				<p class="text-sm font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Why families trust Samvedna', 'samvedna' ); ?></p>
				<?php samvedna_animated_text( 'Parents feel more confident when care is personal, consistent, and closely followed.', 'h2', 'mt-4 text-balance font-display text-3xl font-semibold leading-[1.2] text-text md:text-4xl' ); ?>
			</div>

			<div data-reveal="slideLeft" data-reveal-delay="0.1">
				<div class="relative mx-auto flex w-full max-w-[320px] items-center justify-center sm:max-w-sm lg:ml-auto lg:mr-0">
					<div class="absolute inset-0" style="background:radial-gradient(circle at center, rgba(37,99,235,0.06), transparent 70%)"></div>
					<div class="relative flex h-48 w-48 items-center justify-center rounded-full border border-primary/10 bg-white shadow-[0_8px_32px_rgba(37,99,235,0.08)]">
						<div class="anim-ring-1 absolute inset-0 -m-8 rounded-full border border-dashed border-primary/20"></div>
						<div class="anim-ring-2 absolute inset-0 -m-16 rounded-full border border-primary/10"></div>
						<div class="anim-pulse-scale flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-primary to-blue-700 text-white shadow-lg shadow-primary/30 relative z-10">
							<?php echo samvedna_icon( 'shield-check', 'h-9 w-9', 1.8 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="anim-trust-icon-1 absolute -left-6 top-6 flex h-14 w-14 items-center justify-center rounded-2xl border border-border bg-white text-emerald-500 shadow-xl shadow-black/5 z-20">
							<?php echo samvedna_icon( 'heart-handshake', 'h-6 w-6', 1.8 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="anim-trust-icon-2 absolute -right-4 bottom-4 flex h-12 w-12 items-center justify-center rounded-2xl border border-border bg-white text-amber-400 shadow-xl shadow-black/5 z-20">
							<?php echo samvedna_icon( 'star', 'h-5 w-5', 2, 'currentColor' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-16 grid gap-4 md:mt-20 md:grid-cols-2 lg:grid-cols-3">
			<?php foreach ( array_values( $reasons ) as $index => $reason ) : ?>
				<div class="h-full" data-reveal="fadeUp" data-reveal-delay="<?php echo esc_attr( (string) min( $index * 0.04, 0.2 ) ); ?>">
					<article class="group relative flex h-full min-h-[220px] flex-col overflow-hidden rounded-3xl border border-primary/10 bg-white p-7 transition duration-300 hover:border-primary/30 hover:shadow-[0_8px_30px_rgba(37,99,235,0.06)]">
						<div class="absolute bottom-0 left-0 top-0 w-1.5 origin-left scale-y-0 bg-primary transition duration-300 group-hover:scale-y-100"></div>
						<div class="transition duration-300 group-hover:translate-x-1.5">
							<h3 class="font-display text-xl font-semibold text-text md:text-2xl"><?php echo esc_html( $reason['title'] ); ?></h3>
							<p class="mt-4 text-sm leading-relaxed text-muted md:text-base md:leading-7"><?php echo esc_html( $reason['description'] ); ?></p>
						</div>
					</article>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
