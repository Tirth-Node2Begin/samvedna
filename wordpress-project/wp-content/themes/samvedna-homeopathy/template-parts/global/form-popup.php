<?php
/**
 * Timed consultation popup (ports components/ui/FormPopup.tsx).
 *
 * Hidden by default; shown by main.js after `samvednaData.popupDelay` ms, once
 * per browser session.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sam-modal fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6" data-modal="popup" aria-hidden="true">
	<div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-modal-close></div>
	<div class="sam-modal__panel relative z-10 flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl bg-white shadow-premium outline-none" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Book a consultation', 'samvedna' ); ?>" tabindex="-1">
		<button type="button" data-modal-close aria-label="<?php esc_attr_e( 'Close dialog', 'samvedna' ); ?>" class="absolute right-4 top-4 z-30 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-muted shadow-sm transition hover:bg-primary hover:text-white">
			<?php echo samvedna_icon( 'x', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<div class="no-scrollbar overflow-y-auto p-6 sm:p-8" data-lenis-prevent>
			<?php get_template_part( 'template-parts/global/consultation-form', null, array( 'source' => 'popup' ) ); ?>
		</div>
	</div>
</div>
