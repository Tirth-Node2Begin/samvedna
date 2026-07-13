<?php
/**
 * Conditions treated (ports components/sections/ConditionsTreated.tsx).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$conditions = samvedna_get_conditions();
?>
<section id="conditions" class="bg-white py-16 md:py-20 lg:py-[120px]">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="mx-auto max-w-3xl text-center" data-reveal="fadeUp">
			<p class="text-sm font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'Conditions we support', 'samvedna' ); ?></p>
			<?php samvedna_animated_text( 'Focused support for the developmental challenges parents worry about most.', 'h2', 'mt-4 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl' ); ?>
			<p class="mt-6 text-balance text-base leading-8 text-muted md:text-lg">
				<?php esc_html_e( 'Autism, ADHD, speech delay, learning concerns, developmental delay, genetic disorders, and neurological concerns are approached through one child-centered clinical picture.', 'samvedna' ); ?>
			</p>
		</div>

		<div class="mt-14 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
			<?php foreach ( array_values( $conditions ) as $index => $condition ) : ?>
				<?php
				$span  = isset( $condition['span'] ) ? $condition['span'] : '';
				$delay = min( $index * 0.04, 0.2 );
				?>
				<div class="h-full <?php echo esc_attr( $span ); ?>" data-reveal="scaleIn" data-reveal-delay="<?php echo esc_attr( (string) $delay ); ?>">
					<?php get_template_part( 'template-parts/cards/condition-card', null, array( 'condition' => $condition, 'is_large' => ( 0 === $index ) ) ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
