<?php
/**
 * Medical team (ports MedicalTeam.tsx + DoctorsGrid.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$team = samvedna_get_team();
?>
<section id="doctors" class="scroll-mt-24 bg-white pt-16 md:scroll-mt-28 md:pt-20 lg:pt-[120px] pb-0">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="flex flex-col justify-between gap-8 md:flex-row md:items-end" data-reveal="fadeUp">
			<div class="max-w-3xl">
				<p class="text-sm font-semibold text-primary"><?php esc_html_e( 'Medical team', 'samvedna' ); ?></p>
				<?php samvedna_animated_text( 'A doctor-led team that stays with the family beyond the first visit.', 'h2', 'mt-4 font-display text-4xl font-semibold leading-tight text-text md:text-5xl' ); ?>
			</div>
			<p class="max-w-md text-base leading-7 text-muted"><?php esc_html_e( 'Each child is assessed, discussed, monitored, and refined through regular follow-ups so parents are not left guessing between visits.', 'samvedna' ); ?></p>
		</div>

		<div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
			<?php foreach ( array_values( $team ) as $index => $member ) : ?>
				<div class="h-full w-full" data-reveal="fadeUp" data-reveal-delay="<?php echo esc_attr( (string) ( $index * 0.08 ) ); ?>">
					<?php get_template_part( 'template-parts/cards/doctor-card', null, array( 'member' => $member, 'index' => $index ) ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Shared doctor profile modal (populated by main.js) -->
<div class="sam-modal fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6" data-modal="doctor" aria-hidden="true">
	<div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-modal-close></div>
	<div class="sam-modal__panel relative z-10 flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-premium outline-none" role="dialog" aria-modal="true" tabindex="-1">
		<button type="button" data-modal-close aria-label="<?php esc_attr_e( 'Close dialog', 'samvedna' ); ?>" class="absolute right-4 top-4 z-30 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-muted shadow-sm transition hover:bg-primary hover:text-white">
			<?php echo samvedna_icon( 'x', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<div class="no-scrollbar overflow-y-auto" data-lenis-prevent data-doctor-modal-body></div>
	</div>
</div>
