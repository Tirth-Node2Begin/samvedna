<?php
/**
 * Trust bar metrics (ports components/sections/TrustBar.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$metrics = samvedna_option( 'trust_metrics', samvedna_default_trust_metrics() );
$total   = count( $metrics );
?>
<section class="border-y border-border bg-bg-soft">
	<div class="mx-auto grid max-w-content grid-cols-1 px-5 py-8 md:grid-cols-2 md:px-8 lg:grid-cols-4">
		<?php foreach ( array_values( $metrics ) as $index => $metric ) : ?>
			<div class="border-border py-5 md:px-8 lg:border-l first:lg:border-l-0">
				<p class="text-sm font-semibold text-primary"><?php echo esc_html( $metric['label'] ); ?></p>
				<p class="mt-3 font-display text-2xl font-semibold leading-tight text-text"><?php echo esc_html( $metric['value'] ); ?></p>
				<?php if ( $index < $total - 1 ) : ?>
					<div class="mt-5 h-px bg-border lg:hidden"></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
