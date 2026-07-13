<?php
/**
 * Final CTA + consultation form (ports components/sections/FinalCTA.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

?>
<section id="consultation" class="bg-bg-soft py-10 md:py-16 lg:py-20">
	<div class="mx-auto mb-8 max-w-7xl px-5 text-center md:px-8" data-reveal="fadeUp">
		<?php samvedna_animated_text( 'Book a Consultation for Your Child', 'h2', 'font-display text-4xl font-semibold text-text md:text-5xl' ); ?>
		<p class="mt-3 text-lg text-muted"><?php esc_html_e( 'Speak with the Samvedna Care Desk and get expert guidance for the next step.', 'samvedna' ); ?></p>
	</div>

	<div class="mx-auto max-w-7xl px-5 md:px-8">
		<div class="overflow-hidden rounded-3xl border border-border bg-white shadow-premium lg:grid lg:grid-cols-[0.85fr_1.15fr]" data-reveal="scaleIn">
			<!-- Left -->
			<div class="flex flex-col justify-center bg-primary/[0.03] p-8 text-left lg:p-12 xl:p-16 border-r border-border">
				<div>
					<p class="text-sm font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Begin with expert guidance', 'samvedna' ); ?></p>
					<?php samvedna_animated_text( "Share your child's concerns with a team experienced in autism and developmental challenges.", 'h2', 'mt-4 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl' ); ?>
					<p class="mt-5 text-base leading-relaxed text-muted md:text-lg"><?php esc_html_e( "The first step is a calm conversation about your child's history, current therapies, reports, and daily challenges.", 'samvedna' ); ?></p>
					<div class="mt-8 grid grid-cols-2 gap-3">
						<?php
						echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'label' => __( 'Book Consultation', 'samvedna' ),
							'href'  => samvedna_appointment_url(),
							'size'  => 'md',
							'class' => 'w-full text-center',
						) );
						echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'label'    => __( 'Speak With Our Team', 'samvedna' ),
							'href'     => samvedna_contact( 'phone_href' ),
							'size'     => 'md',
							'variant'  => 'secondary',
							'icon'     => 'phone',
							'icon_pos' => 'left',
							'class'    => 'w-full text-center',
						) );
						?>
					</div>
				</div>
			</div>

			<!-- Right: form -->
			<div class="p-8 lg:p-12 xl:p-16">
				<?php svl_part( 'template-parts/global/consultation-form', null, array( 'source' => 'website' ) ); ?>
			</div>
		</div>
	</div>
</section>
