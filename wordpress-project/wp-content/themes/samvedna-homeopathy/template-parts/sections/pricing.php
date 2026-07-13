<?php
/**
 * Care plans / pricing (ports components/sections/Pricing.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$plans = array_values( samvedna_get_plans() );
?>
<section id="pricing" class="bg-bg-soft py-16 md:py-20 lg:py-[120px]">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="mx-auto max-w-3xl text-center" data-reveal="fadeUp">
			<p class="text-sm font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Care Plans', 'samvedna' ); ?></p>
			<?php samvedna_animated_text( 'Structured, Evidence-Led Homeopathy', 'h2', 'mt-4 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl' ); ?>
			<p class="mt-6 text-balance text-base leading-8 text-muted md:text-lg"><?php esc_html_e( 'Every child progresses differently — but the care they receive should always be predictable, structured, and led by a trained medical team.', 'samvedna' ); ?></p>
		</div>

		<div class="mt-14 grid gap-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-8 items-stretch">
			<?php foreach ( $plans as $index => $plan ) : ?>
				<?php $popular = ! empty( $plan['popular'] ); ?>
				<div class="h-full" data-reveal="fadeUp" data-reveal-delay="<?php echo esc_attr( (string) ( $index * 0.1 ) ); ?>">
					<div class="relative flex h-full flex-col rounded-3xl border p-8 transition-all duration-300 bg-white <?php echo $popular ? 'border-primary ring-1 ring-primary shadow-premium' : 'border-border shadow-sm hover:shadow-md'; ?>">
						<?php if ( $popular ) : ?>
							<div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-primary to-secondary px-4 py-1 text-xs font-bold text-white shadow-sm uppercase tracking-wide"><?php esc_html_e( 'Most Preferred', 'samvedna' ); ?></div>
						<?php endif; ?>

						<div class="mb-6">
							<h3 class="font-display text-2xl font-semibold text-text"><?php echo esc_html( $plan['name'] ); ?></h3>
							<p class="mt-3 min-h-[3rem] text-sm leading-relaxed text-muted"><?php echo esc_html( $plan['tagline'] ); ?></p>
						</div>

						<div class="mb-6 flex items-baseline text-text">
							<span class="text-4xl font-bold tracking-tight"><?php echo esc_html( $plan['price'] ); ?></span>
							<span class="ml-1 text-sm font-medium text-muted">/ <?php echo esc_html( $plan['duration'] ); ?></span>
						</div>

						<ul class="mb-8 flex-1 space-y-4">
							<?php foreach ( samvedna_to_list( $plan['features'] ) as $feature ) : ?>
								<li class="flex items-start text-sm text-text">
									<?php echo samvedna_icon( 'check', 'mr-3 mt-1 h-4 w-4 shrink-0 text-primary', 2.5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span class="leading-snug"><?php echo esc_html( $feature ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>

						<div class="mt-auto pt-4">
							<?php
							echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'label'   => $plan['cta'],
								'href'    => '#consultation',
								'variant' => $popular ? 'primary' : 'secondary',
								'class'   => 'w-full',
							) );
							?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
