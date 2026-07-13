<?php
/**
 * Consultation form (ports components/ui/ConsultationForm.tsx).
 *
 * Submitted via AJAX in assets/js/main.js to the `samvedna_submit_consultation`
 * action. Server-side validation, storage and email live in the samvedna-core
 * plugin (includes/class-consultation-form.php).
 *
 * @param array $args { @type string $source 'website'|'popup' }
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$source = ( isset( $args['source'] ) && 'popup' === $args['source'] ) ? 'popup' : 'website';
$field  = 'mt-1.5 block w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-base text-text shadow-sm transition-all placeholder:text-muted/60 hover:border-primary/50 focus:border-primary focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary/10';
$times  = array( 'morning' => __( 'Morning', 'samvedna' ), 'afternoon' => __( 'Afternoon', 'samvedna' ), 'evening' => __( 'Evening', 'samvedna' ) );
?>
<form class="sam-consultation-form flex w-full flex-col text-left" data-consultation-form novalidate>
	<div class="mb-6 flex flex-col gap-2 border-b border-border/60 pb-5 pr-12">
		<div>
			<p class="text-sm font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Consultation request', 'samvedna' ); ?></p>
			<h3 class="mt-1.5 font-display text-xl font-semibold text-text md:text-2xl"><?php esc_html_e( "Tell us what your child needs help with.", 'samvedna' ); ?></h3>
		</div>
		<p class="text-sm leading-6 text-muted"><?php esc_html_e( 'A team member will review your details and guide you through the next step.', 'samvedna' ); ?></p>
	</div>

	<fieldset class="grid gap-x-5 gap-y-4 md:grid-cols-2" data-form-fields>
		<label class="block md:col-span-2">
			<span class="text-sm font-semibold text-text"><?php esc_html_e( 'Name', 'samvedna' ); ?></span>
			<input type="text" name="parentName" class="<?php echo esc_attr( $field ); ?>" placeholder="<?php esc_attr_e( 'Name', 'samvedna' ); ?>" required />
			<p class="mt-2 hidden text-sm font-medium text-red-700" data-error-for="parentName"></p>
		</label>

		<label class="block">
			<span class="text-sm font-semibold text-text"><?php esc_html_e( 'Phone / Mobile', 'samvedna' ); ?></span>
			<input type="tel" name="phone" inputmode="tel" class="<?php echo esc_attr( $field ); ?>" placeholder="<?php esc_attr_e( 'Phone/Mobile (10 digits)', 'samvedna' ); ?>" required />
			<p class="mt-2 hidden text-sm font-medium text-red-700" data-error-for="phone"></p>
		</label>

		<label class="block">
			<span class="text-sm font-semibold text-text"><?php esc_html_e( 'Email', 'samvedna' ); ?></span>
			<input type="email" name="email" inputmode="email" class="<?php echo esc_attr( $field ); ?>" placeholder="<?php esc_attr_e( 'you@company.com', 'samvedna' ); ?>" required />
			<p class="mt-2 hidden text-sm font-medium text-red-700" data-error-for="email"></p>
		</label>

		<label class="block md:col-span-2">
			<span class="text-sm font-semibold text-text"><?php esc_html_e( 'Your Message', 'samvedna' ); ?></span>
			<textarea name="message" class="<?php echo esc_attr( $field ); ?> min-h-[104px] resize-none leading-relaxed" placeholder="<?php esc_attr_e( 'Your Message', 'samvedna' ); ?>"></textarea>
			<p class="mt-2 hidden text-sm font-medium text-red-700" data-error-for="message"></p>
		</label>
	</fieldset>

	<input type="hidden" name="source" value="<?php echo esc_attr( $source ); ?>" />
	<input type="hidden" name="samvedna_nonce" value="<?php echo esc_attr( wp_create_nonce( 'samvedna_consultation' ) ); ?>" />
	<?php // Honeypot — bots fill this; humans never see it. ?>
	<div class="absolute left-[-9999px]" aria-hidden="true">
		<label>Website<input type="text" name="website_hp" tabindex="-1" autocomplete="off" /></label>
	</div>

	<div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
		<button type="submit" data-submit class="inline-flex h-14 items-center justify-center gap-2 rounded-control border border-transparent bg-gradient-to-r from-primary to-secondary px-6 text-base font-semibold leading-none text-white shadow-md transition duration-200 hover:opacity-90 disabled:pointer-events-none disabled:opacity-60">
			<span data-submit-icon><?php echo samvedna_icon( 'send', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span data-submit-label><?php esc_html_e( 'Send Message', 'samvedna' ); ?></span>
		</button>

		<p class="hidden text-sm font-medium" role="status" aria-live="polite" data-form-message></p>
	</div>
</form>
