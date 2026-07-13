<?php
/**
 * FAQ (ports FAQ.tsx + FAQAccordion.tsx; Radix replaced with a CSS/JS accordion).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$faq = array_values( samvedna_get_faq() );
?>
<section id="faq" class="bg-bg-soft py-16 md:py-20 lg:py-[120px]">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="mx-auto w-full max-w-5xl text-center" data-reveal="fadeUp">
			<p class="text-sm font-semibold text-primary"><?php esc_html_e( 'Parent questions', 'samvedna' ); ?></p>
			<?php samvedna_animated_text( 'Honest answers for parents before the first consultation.', 'h2', 'mt-4 font-display text-4xl font-semibold leading-tight text-text md:text-5xl' ); ?>
			<p class="mx-auto mt-6 max-w-3xl text-lg leading-8 text-muted"><?php esc_html_e( 'These are the questions families ask when they are comparing care options, therapies, and next steps for a child with developmental challenges.', 'samvedna' ); ?></p>
			<div class="mt-8 flex justify-center">
				<?php
				echo samvedna_button( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'label'   => __( 'Speak With Our Team', 'samvedna' ),
					'href'    => samvedna_contact( 'phone_href' ),
					'variant' => 'secondary',
				) );
				?>
			</div>
		</div>

		<div class="mx-auto mt-12 w-full max-w-3xl lg:mt-16" data-reveal="fadeUp">
			<div class="flex w-full flex-col gap-4" data-faq-accordion>
				<?php foreach ( $faq as $index => $item ) : ?>
					<div class="faq-item group rounded-2xl border border-border bg-white px-6 transition-all duration-300 hover:border-primary/20 hover:shadow-md data-[state=open]:border-primary/30 data-[state=open]:shadow-md sm:px-8" data-state="closed">
						<h3>
							<button type="button" id="faq-trigger-<?php echo (int) $index; ?>" aria-expanded="false" aria-controls="faq-panel-<?php echo (int) $index; ?>" data-faq-trigger
								class="flex w-full items-center justify-between gap-6 py-6 text-left font-display text-lg font-semibold leading-snug text-text transition hover:text-primary md:text-xl">
								<span><?php echo esc_html( $item['question'] ); ?></span>
								<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/5 transition-colors duration-300 group-hover:bg-primary/10 group-data-[state=open]:bg-primary">
									<?php echo samvedna_icon( 'chevron-down', 'h-5 w-5 text-primary transition-transform duration-300 group-data-[state=open]:rotate-180 group-data-[state=open]:text-white' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
							</button>
						</h3>
						<div class="faq-content text-muted" id="faq-panel-<?php echo (int) $index; ?>" role="region" aria-labelledby="faq-trigger-<?php echo (int) $index; ?>">
							<div><p class="pb-8 text-base leading-relaxed md:text-lg"><?php echo esc_html( $item['answer'] ); ?></p></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
