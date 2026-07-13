<?php
/**
 * Blog carousel (ports Blogs.tsx + BlogsCarousel.tsx).
 *
 * All cards are rendered once: the first three sit in visible cells, the rest in
 * a hidden pool. main.js periodically crossfades one visible card with a random
 * pooled card (matching the original "swap one" behaviour).
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$cards   = array_values( samvedna_get_blog_cards( 6 ) );
$visible = 3;
$total   = count( $cards );
?>
<section id="blogs" class="scroll-mt-24 bg-bg-soft py-16 md:scroll-mt-28 md:py-20 lg:py-[120px]">
	<div class="mx-auto max-w-content px-5 md:px-8">
		<div class="flex flex-col justify-between gap-6 md:flex-row md:items-end" data-reveal="fadeUp">
			<div class="max-w-3xl">
				<p class="text-sm font-semibold text-primary"><?php esc_html_e( 'From our blog', 'samvedna' ); ?></p>
				<?php samvedna_animated_text( 'Guidance for parents, written from real clinical experience.', 'h2', 'mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl' ); ?>
			</div>
			<p class="max-w-md text-base leading-7 text-muted"><?php esc_html_e( 'Practical articles on autism, ADHD, speech delay and developmental care — to help families take confident next steps.', 'samvedna' ); ?></p>
		</div>

		<div data-reveal="fadeUp">
			<div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8" data-blogs-carousel>
				<?php for ( $slot = 0; $slot < min( $visible, $total ); $slot++ ) : ?>
					<div class="blog-cell relative h-full" data-blog-cell>
						<div class="blog-card-fade h-full" data-blog-card>
							<?php
							get_template_part( 'template-parts/cards/blog-card', null, array(
								'post' => $cards[ $slot ],
							) );
							?>
						</div>
					</div>
				<?php endfor; ?>
			</div>

			<?php // Hidden pool of remaining cards for the swap animation. ?>
			<div class="hidden" data-blogs-pool aria-hidden="true">
				<?php for ( $i = $visible; $i < $total; $i++ ) : ?>
					<div class="blog-card-fade h-full" data-blog-card>
						<?php
						get_template_part( 'template-parts/cards/blog-card', null, array(
							'post' => $cards[ $i ],
						) );
						?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</section>
