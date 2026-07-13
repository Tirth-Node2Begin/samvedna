<?php
/**
 * Blog card (ports components/ui/BlogCard.tsx).
 *
 * @param array $args { @type array $post }
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$post = isset( $args['post'] ) ? $args['post'] : array();
$href = ! empty( $post['url'] ) ? $post['url'] : '#';
?>
<article class="group flex h-full flex-col overflow-hidden rounded-card border border-border bg-white transition duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-premium">
	<div class="relative aspect-[16/10] overflow-hidden bg-surface">
		<a href="<?php echo esc_url( $href ); ?>" class="absolute inset-0 z-0 block">
			<span class="absolute -inset-[10%] block will-change-transform" data-parallax>
				<img src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( isset( $post['alt'] ) ? $post['alt'] : $post['title'] ); ?>" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]" />
			</span>
		</a>

		<span class="pointer-events-none absolute left-3 top-3 z-10 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary shadow-sm backdrop-blur"><?php echo esc_html( $post['category'] ); ?></span>
	</div>

	<div class="flex flex-1 flex-col p-6">
		<div class="flex items-center gap-3 text-xs font-medium text-muted">
			<span><?php echo esc_html( $post['date'] ); ?></span>
			<span class="inline-flex items-center gap-1">
				<?php echo samvedna_icon( 'clock', 'h-3.5 w-3.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( $post['read_time'] ); ?>
			</span>
		</div>
		<h3 class="mt-3 font-display text-lg font-semibold leading-snug text-text transition duration-200 group-hover:text-primary">
			<a href="<?php echo esc_url( $href ); ?>" class="focus-visible:underline"><?php echo esc_html( $post['title'] ); ?></a>
		</h3>
		<p class="mt-3 text-sm leading-6 text-muted"><?php echo esc_html( $post['excerpt'] ); ?></p>
		<a href="<?php echo esc_url( $href ); ?>" class="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-primary">
			<?php esc_html_e( 'Read article', 'samvedna' ); ?>
			<?php echo samvedna_icon( 'arrow-up-right', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</article>
