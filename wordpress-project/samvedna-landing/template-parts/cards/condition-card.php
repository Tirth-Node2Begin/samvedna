<?php
/**
 * Condition card (ports components/ui/ConditionCard.tsx).
 *
 * @param array $args { @type array $condition, @type bool $is_large }
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$condition = isset( $args['condition'] ) ? $args['condition'] : array();
$is_large  = ! empty( $args['is_large'] );
$variant   = isset( $condition['variant'] ) ? $condition['variant'] : 'light';
$featured  = ( 'dark' === $variant );

if ( $featured ) {
	$article_class = 'bg-primary/[0.03] border-2 border-primary/10 text-text shadow-sm hover:shadow-md hover:border-primary/30';
} elseif ( 'soft' === $variant ) {
	$article_class = 'bg-[#f4f7f6] border border-transparent hover:border-primary/20 hover:bg-white hover:shadow-lg';
} else {
	$article_class = 'bg-white border border-border hover:border-primary/30 hover:shadow-lg';
}
?>
<article class="group relative flex h-full w-full flex-col overflow-hidden rounded-3xl p-6 sm:p-8 transition-all duration-500 hover:-translate-y-1 <?php echo esc_attr( $article_class ); ?>">
	<?php if ( $featured ) : ?>
		<div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-primary/5 blur-[56px] transition-transform duration-700 group-hover:scale-110"></div>
	<?php elseif ( $is_large ) : ?>
		<div class="absolute bottom-0 right-0 h-40 w-40 opacity-5" style="background:radial-gradient(circle at bottom right, var(--color-primary) 0%, transparent 70%)"></div>
	<?php endif; ?>

	<div class="relative z-10 flex h-full flex-col gap-5">
		<div class="flex items-start justify-between">
			<div class="flex h-12 w-12 items-center justify-center rounded-2xl transition-transform duration-500 group-hover:scale-110 <?php echo $featured ? 'bg-primary/10 text-primary' : 'bg-primary/5 text-primary'; ?>">
				<?php echo samvedna_icon( isset( $condition['icon'] ) ? $condition['icon'] : 'activity', 'h-6 w-6', 1.5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="flex h-8 w-8 items-center justify-center rounded-full opacity-0 -translate-x-4 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 <?php echo $featured ? 'bg-primary/10 text-primary' : 'bg-primary/5 text-primary'; ?>">
				<?php echo samvedna_icon( 'arrow-up-right', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>

		<div class="mt-4">
			<h3 class="font-display text-xl font-bold leading-tight text-text <?php echo $is_large ? 'lg:text-3xl' : 'lg:text-xl'; ?>"><?php echo esc_html( $condition['name'] ); ?></h3>
			<p class="mt-3 text-sm leading-relaxed text-muted"><?php echo esc_html( $condition['description'] ); ?></p>
		</div>
	</div>
</article>
