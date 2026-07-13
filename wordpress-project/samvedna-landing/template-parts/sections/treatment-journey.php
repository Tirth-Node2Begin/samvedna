<?php
/**
 * Treatment journey (ports components/sections/TreatmentJourney.tsx).
 *
 * Mobile: vertical timeline. Desktop: auto-advancing carousel driven by
 * assets/js/main.js (the centred card is highlighted; the track shifts every
 * 2s). The track is rendered with the steps duplicated for an endless feel.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$steps    = array_values( samvedna_option( 'journey_steps', samvedna_default_journey_steps() ) );
$count    = count( $steps );
$extended = array_merge( $steps, $steps );
?>
<section id="journey" class="bg-[#F8FAF9] py-16 md:py-20 lg:py-[120px]">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="max-w-3xl" data-reveal="fadeUp">
			<p class="text-sm font-semibold text-primary"><?php esc_html_e( 'Treatment journey', 'samvedna' ); ?></p>
			<?php samvedna_animated_text( 'A clear care path so parents know what happens after the first consultation.', 'h2', 'mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl' ); ?>
		</div>

		<!-- Mobile timeline -->
		<div class="mt-14 md:hidden">
			<div class="space-y-6 border-l border-primary/35 pl-6">
				<?php foreach ( $steps as $index => $step ) : ?>
					<div data-reveal="fadeUp" data-reveal-delay="<?php echo esc_attr( (string) min( $index * 0.05, 0.24 ) ); ?>">
						<article class="relative rounded-card border border-border bg-white p-6">
							<span class="absolute -left-[35px] top-6 font-display text-xl font-bold text-accent"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3 class="font-display text-2xl font-semibold text-text"><?php echo esc_html( $step['title'] ); ?></h3>
							<p class="mt-4 text-base leading-7 text-muted"><?php echo esc_html( $step['description'] ); ?></p>
						</article>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Desktop carousel -->
		<div class="mt-20 hidden overflow-hidden pb-16 md:block">
			<div class="relative mx-auto w-full max-w-[1200px]">
				<div class="absolute left-0 right-0 top-[88px] h-[2px] bg-primary/10"></div>
				<div class="journey-track" data-journey-track data-journey-count="<?php echo esc_attr( (string) count( $extended ) ); ?>" data-journey-steps="<?php echo esc_attr( (string) $count ); ?>">
					<?php foreach ( $extended as $idx => $unused ) : ?>
						<?php
						$step_index     = ( ( ( $idx - 1 ) % $count ) + $count ) % $count;
						$step           = $steps[ $step_index ];
						$display_number = str_pad( (string) ( $step_index + 1 ), 2, '0', STR_PAD_LEFT );
						$is_center      = ( 1 === $idx );
						?>
						<div class="w-[33.333333vw] max-w-[400px] flex-shrink-0 px-4">
							<article class="journey-card<?php echo $is_center ? ' is-center' : ''; ?> relative flex min-h-[320px] flex-col justify-between rounded-[2rem] border p-8" data-journey-card data-idx="<?php echo (int) $idx; ?>">
								<div>
									<p class="journey-num font-display text-5xl font-bold"><?php echo esc_html( $display_number ); ?></p>
									<h3 class="mt-6 font-display text-2xl font-bold leading-tight text-text"><?php echo esc_html( $step['title'] ); ?></h3>
								</div>
								<p class="mt-4 text-base leading-relaxed text-muted"><?php echo esc_html( $step['description'] ); ?></p>
							</article>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
