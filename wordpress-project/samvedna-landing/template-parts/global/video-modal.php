<?php
/**
 * Shared parent-video story modal (ports components/ui/VideoStoryModal.tsx).
 *
 * Populated by main.js when a video thumbnail is clicked: it injects either a
 * YouTube iframe (when a video id is present) or the "coming soon" poster.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$youtube = '';
foreach ( samvedna_social_links() as $link ) {
	if ( 'YouTube' === $link['label'] ) {
		$youtube = $link['href'];
		break;
	}
}
?>
<div class="sam-modal fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6" data-modal="video" data-youtube-channel="<?php echo esc_url( $youtube ); ?>" aria-hidden="true">
	<div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-modal-close></div>
	<div class="sam-modal__panel relative z-10 flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white p-0 shadow-premium outline-none" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Video story', 'samvedna' ); ?>" tabindex="-1">
		<button type="button" data-modal-close aria-label="<?php esc_attr_e( 'Close dialog', 'samvedna' ); ?>" class="absolute right-4 top-4 z-30 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-muted shadow-sm transition hover:bg-primary hover:text-white">
			<?php echo samvedna_icon( 'x', 'h-5 w-5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<div class="no-scrollbar overflow-y-auto" data-lenis-prevent data-video-modal-body></div>
	</div>
</div>
