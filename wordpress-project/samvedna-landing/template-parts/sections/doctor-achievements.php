<?php
/**
 * Doctor achievements (ports components/sections/DoctorAchievements.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$achievements = array_values( samvedna_get_achievements() );
$featured     = isset( $achievements[0] ) ? $achievements[0] : array();

/**
 * Render one supporting achievement card.
 *
 * @param array $a     Achievement row.
 * @param float $delay Reveal delay.
 */
$render_card = function ( $a, $delay ) {
	if ( empty( $a ) ) {
		return;
	}
	?>
	<div class="flex-1" data-reveal="fadeUp" data-reveal-delay="<?php echo esc_attr( (string) $delay ); ?>">
		<article class="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-transparent bg-bg-soft p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-200/50">
			<div class="absolute inset-0 bg-white"></div>
			<div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-slate-100 to-slate-200 transition-colors duration-300 group-hover:from-slate-200 group-hover:to-slate-300"></div>
			<div class="relative z-10">
				<div class="mb-6 flex h-12 w-12 items-center justify-center rounded-xl <?php echo esc_attr( $a['icon_bg'] ); ?>">
					<?php echo samvedna_icon( $a['icon'], 'h-5 w-5', 1.8 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<h3 class="font-display text-lg font-semibold leading-snug text-text"><span class="text-text"><?php echo esc_html( $a['title'] ); ?></span></h3>
				<p class="mt-3 text-sm leading-relaxed text-muted"><?php echo esc_html( $a['description'] ); ?></p>
			</div>
		</article>
	</div>
	<?php
};
?>
<section id="achievements" class="relative scroll-mt-24 overflow-hidden bg-gradient-to-b from-white via-[#F7FBFA] to-white py-16 text-text md:scroll-mt-28 md:py-20 lg:py-[112px]">
	<div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(37,99,235,0.03)_1px,transparent_1px),linear-gradient(180deg,rgba(20,184,166,0.03)_1px,transparent_1px)] bg-[size:56px_56px] opacity-60"></div>

	<div class="relative mx-auto max-w-content px-5 md:px-8">
		<div class="mx-auto max-w-3xl text-center">
			<div data-reveal="fadeUp">
				<div class="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-white px-4 py-1.5 shadow-sm">
					<?php echo samvedna_icon( 'trophy', 'h-3.5 w-3.5 text-primary', 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="text-xs font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Top 5 Achievements', 'samvedna' ); ?></span>
				</div>
			</div>

			<?php samvedna_animated_text( 'International recognition, arranged for parents to understand at a glance.', 'h2', 'mt-5 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl lg:text-[42px] lg:leading-[1.18]' ); ?>

			<div data-reveal="fadeUp" data-reveal-delay="0.06">
				<p class="mx-auto mt-5 max-w-xl text-base leading-7 text-muted md:text-lg"><?php esc_html_e( 'From international conferences to national leadership, these milestones reflect experience in child-focused homeopathic care.', 'samvedna' ); ?></p>
			</div>
		</div>

		<div class="mt-14 grid gap-6 lg:grid-cols-3 lg:gap-8 items-stretch">
			<div class="flex flex-col gap-6">
				<?php $render_card( isset( $achievements[1] ) ? $achievements[1] : array(), 0.15 ); ?>
				<?php $render_card( isset( $achievements[2] ) ? $achievements[2] : array(), 0.20 ); ?>
			</div>

			<div class="h-full" data-reveal="fadeUp" data-reveal-delay="0.1">
				<article class="group relative flex h-full flex-col overflow-hidden rounded-[32px] border border-primary/10 bg-white p-8 shadow-premium transition-all duration-300 hover:shadow-[0_32px_96px_rgba(37,99,235,0.12)] md:p-10">
					<div class="absolute inset-0 bg-gradient-to-b from-primary/[0.02] to-transparent"></div>
					<div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-primary via-accent to-secondary"></div>
					<div class="relative z-10 flex flex-col h-full items-center text-center">
						<div class="mb-8 flex h-20 w-20 items-center justify-center rounded-2xl bg-primary text-white shadow-xl shadow-primary/20">
							<?php echo samvedna_icon( 'trophy', 'h-10 w-10', 1.5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="mb-6 inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/[0.04] px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary">
							<?php echo samvedna_icon( 'map-pin', 'h-3.5 w-3.5', 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo esc_html( isset( $featured['location'] ) ? $featured['location'] : '' ); ?>
						</div>
						<h3 class="font-display text-2xl font-bold leading-tight text-text md:text-3xl lg:text-[34px]"><?php echo esc_html( isset( $featured['title'] ) ? $featured['title'] : '' ); ?></h3>
						<p class="mt-4 flex items-center justify-center gap-2 text-sm font-semibold text-primary">
							<?php echo samvedna_icon( 'medal', 'h-4 w-4 shrink-0', 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo esc_html( isset( $featured['event'] ) ? $featured['event'] : '' ); ?>
						</p>
						<div class="mt-auto pt-8">
							<p class="text-base leading-relaxed text-muted md:text-lg"><?php echo esc_html( isset( $featured['description'] ) ? $featured['description'] : '' ); ?></p>
						</div>
					</div>
				</article>
			</div>

			<div class="flex flex-col gap-6">
				<?php $render_card( isset( $achievements[3] ) ? $achievements[3] : array(), 0.25 ); ?>
				<?php $render_card( isset( $achievements[4] ) ? $achievements[4] : array(), 0.30 ); ?>
			</div>
		</div>
	</div>
</section>
