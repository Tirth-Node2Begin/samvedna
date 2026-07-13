<?php
/**
 * Parent video testimonials.
 *
 * Standalone section before the blog carousel. Renders the stories as a
 * continuous right-to-left marquee (CSS animation `sam-marquee` in app.css).
 * The card set is duplicated so the loop is seamless; the duplicated set is
 * hidden from assistive tech. Each card opens the shared video modal via the
 * same `data-video-*` attributes the blog cards used to carry.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$videos = array_values( samvedna_get_video_testimonials() );

if ( empty( $videos ) ) {
	return;
}

// Slower for fewer cards, faster cap for many — keeps a comfortable glide.
$duration = max( 24, count( $videos ) * 7 );
?>
<section id="testimonials" class="scroll-mt-24 overflow-hidden bg-bg-soft pt-16 md:scroll-mt-28 md:pt-20 lg:pt-[120px]">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="max-w-2xl" data-reveal="fadeUp">
			<p class="text-sm font-semibold text-primary"><?php esc_html_e( 'Parent stories', 'samvedna' ); ?></p>
			<?php samvedna_animated_text( 'Real families sharing their child\'s progress.', 'h2', 'mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl' ); ?>
			<p class="mt-4 max-w-md text-base leading-7 text-muted"><?php esc_html_e( 'Hear directly from parents about their experience with autism, ADHD, speech delay and developmental care at Samvedna.', 'samvedna' ); ?></p>
		</div>
	</div>

	<div class="marquee-viewport mt-12" data-reveal="fadeUp">
		<div class="marquee-track flex w-max will-change-transform" style="--marquee-duration: <?php echo (int) $duration; ?>s">
			<?php
			// Render the set twice: the first is interactive, the second is a
			// presentational clone (hidden from AT / keyboard) for a seamless loop.
			for ( $copy = 0; $copy < 2; $copy++ ) :
				foreach ( $videos as $video ) :
					$clone = $copy > 0;
					?>
					<button type="button"
						data-video-open
						data-video-id="<?php echo esc_attr( $video['youtube_id'] ); ?>"
						data-video-poster="<?php echo esc_url( $video['poster'] ); ?>"
						data-video-alt="<?php echo esc_attr( $video['alt'] ); ?>"
						data-video-name="<?php echo esc_attr( $video['name'] ); ?>"
						data-video-condition="<?php echo esc_attr( $video['condition'] ); ?>"
						data-video-location="<?php echo esc_attr( $video['location'] ); ?>"
						<?php if ( $clone ) : ?>aria-hidden="true" tabindex="-1"<?php else : ?>aria-label="<?php echo esc_attr( sprintf( /* translators: 1: family name, 2: condition. */ __( 'Play parent video story: %1$s, %2$s', 'samvedna' ), $video['name'], $video['condition'] ) ); ?>"<?php endif; ?>
						class="group/video relative mr-6 block aspect-[4/3] w-[78vw] shrink-0 overflow-hidden rounded-card border border-border bg-slate-900 text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-premium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary sm:w-[44vw] lg:w-[31vw] xl:w-[380px]">
						<img src="<?php echo esc_url( $video['poster'] ); ?>" alt="<?php echo $clone ? '' : esc_attr( $video['alt'] ); ?>" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover/video:scale-[1.05]" />

						<?php // Gradient: dark at top (for the overlaid text) and bottom (for the duration). ?>
						<span class="absolute inset-0 bg-gradient-to-b from-slate-900/85 via-slate-900/5 to-slate-900/70"></span>

						<?php // Overlaid text — written above the video. ?>
						<span class="absolute inset-x-0 top-0 p-4">
							<span class="block text-white/85"><?php echo samvedna_icon( 'quote', 'h-6 w-6', 0, 'currentColor' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php if ( ! empty( $video['condition'] ) ) : ?>
								<span class="mt-1.5 block font-display text-base font-semibold leading-snug text-white"><?php echo esc_html( $video['condition'] ); ?></span>
							<?php endif; ?>
							<span class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-white/85">
								<span class="font-semibold"><?php echo esc_html( $video['name'] ); ?></span>
								<?php if ( ! empty( $video['location'] ) ) : ?>
									<span class="opacity-50">&bull;</span>
									<span class="inline-flex items-center gap-1">
										<?php echo samvedna_icon( 'map-pin', 'h-3 w-3' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<?php echo esc_html( $video['location'] ); ?>
									</span>
								<?php endif; ?>
							</span>
						</span>

						<span class="absolute left-1/2 top-1/2 flex h-12 w-12 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-primary shadow-lg transition duration-300 group-hover/video:scale-110 group-hover/video:bg-primary group-hover/video:text-white">
							<?php echo samvedna_icon( 'play', 'ml-0.5 h-5 w-5', 2, 'currentColor' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>

						<?php if ( ! empty( $video['duration'] ) ) : ?>
							<span class="absolute bottom-2.5 right-2.5 rounded bg-slate-900/75 px-1.5 py-0.5 text-[11px] font-semibold leading-none text-white"><?php echo esc_html( $video['duration'] ); ?></span>
						<?php endif; ?>
					</button>
					<?php
				endforeach;
			endfor;
			?>
		</div>
	</div>
</section>
