<?php
/**
 * Doctor card + hidden profile (ports DoctorCard.tsx + DoctorProfileModal.tsx).
 *
 * The button opens the shared doctor modal; main.js clones the adjacent hidden
 * profile block into the modal panel.
 *
 * @param array $args { @type array $member, @type int $index }
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$member = isset( $args['member'] ) ? $args['member'] : array();
$index  = isset( $args['index'] ) ? (int) $args['index'] : 0;
$img    = samvedna_image_url( $member['image'], 'samvedna-card' );
$pid    = 'doctor-profile-' . $index;

/**
 * Output a profile list section (specializations / treatments / etc.).
 *
 * @param string   $icon  Icon key.
 * @param string   $title Section title.
 * @param string[] $items Items.
 */
$list_section = function ( $icon, $title, $items ) {
	if ( empty( $items ) ) {
		return;
	}
	?>
	<div>
		<h3 class="flex items-center gap-2 font-display text-base font-semibold text-text">
			<?php echo samvedna_icon( $icon, 'h-4 w-4 text-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo esc_html( $title ); ?>
		</h3>
		<ul class="mt-3 grid gap-2 sm:grid-cols-2">
			<?php foreach ( $items as $item ) : ?>
				<li class="flex items-start gap-2 text-sm leading-6 text-muted">
					<?php echo samvedna_icon( 'badge-check', 'mt-0.5 h-4 w-4 shrink-0 text-accent' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $item ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
};
?>
<button type="button" data-doctor-open="<?php echo esc_attr( $pid ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: doctor name. */ __( 'View the profile of %s', 'samvedna' ), $member['name'] ) ); ?>"
	class="group flex h-full w-full flex-col overflow-hidden rounded-card border border-border bg-white text-left transition duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-premium focus-visible:-translate-y-1 focus-visible:border-primary">
	<div class="relative aspect-[4/3] overflow-hidden bg-surface">
		<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $member['alt'] ); ?>" loading="lazy" decoding="async" class="h-full w-full object-cover object-[50%_15%] transition duration-500 group-hover:scale-105" />
		<span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary shadow-sm backdrop-blur"><?php echo esc_html( $member['experience'] ); ?> <?php esc_html_e( 'Experience', 'samvedna' ); ?></span>
	</div>
	<div class="flex flex-1 flex-col p-6">
		<h3 class="font-display text-xl font-semibold text-text transition duration-200 group-hover:text-primary"><?php echo esc_html( $member['name'] ); ?></h3>
		<p class="mt-1 text-sm font-semibold text-primary"><?php echo esc_html( $member['title'] ); ?></p>
		<p class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-muted">
			<?php echo samvedna_icon( 'stethoscope', 'h-3.5 w-3.5 shrink-0 text-primary/70' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo esc_html( $member['specialization'] ); ?>
		</p>
		<p class="mt-4 border-t border-border pt-4 text-sm leading-6 text-muted"><?php echo esc_html( $member['summary'] ); ?></p>
		<span class="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-primary">
			<?php esc_html_e( 'View full profile', 'samvedna' ); ?>
			<?php echo samvedna_icon( 'arrow-up-right', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
	</div>
</button>

<!-- Hidden profile content cloned into the shared modal -->
<div id="<?php echo esc_attr( $pid ); ?>" class="hidden" data-doctor-profile>
	<article>
		<div class="grid gap-6 border-b border-border bg-primary/[0.03] p-6 sm:grid-cols-[160px_1fr] sm:p-8">
			<div class="relative mx-auto aspect-[4/5] w-40 overflow-hidden rounded-2xl bg-surface shadow-sm sm:mx-0 sm:w-full">
				<img src="<?php echo esc_url( samvedna_image_url( $member['image'], 'samvedna-portrait' ) ); ?>" alt="<?php echo esc_attr( $member['alt'] ); ?>" loading="lazy" decoding="async" class="h-full w-full object-cover object-[50%_15%]" />
			</div>
			<div class="flex flex-col justify-center">
				<h2 class="font-display text-2xl font-semibold text-text md:text-3xl" data-modal-title><?php echo esc_html( $member['name'] ); ?></h2>
				<p class="mt-1 text-sm font-semibold text-primary"><?php echo esc_html( $member['title'] ); ?></p>
				<p class="mt-2 inline-flex items-center gap-1.5 text-sm text-muted">
					<?php echo samvedna_icon( 'stethoscope', 'h-4 w-4 text-primary/70' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( $member['specialization'] ); ?>
				</p>
				<div class="mt-4 flex flex-wrap gap-2">
					<span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
						<?php echo samvedna_icon( 'calendar-clock', 'h-3.5 w-3.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( $member['experience'] ); ?> <?php esc_html_e( 'Experience', 'samvedna' ); ?>
					</span>
					<?php foreach ( samvedna_to_list( $member['qualifications'] ) as $q ) : ?>
						<span class="inline-flex items-center gap-1.5 rounded-full border border-border bg-white px-3 py-1 text-xs font-semibold text-text">
							<?php echo samvedna_icon( 'graduation-cap', 'h-3.5 w-3.5 text-primary/70' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo esc_html( $q ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="space-y-7 p-6 sm:p-8">
			<div>
				<h3 class="flex items-center gap-2 font-display text-base font-semibold text-text">
					<?php echo samvedna_icon( 'sparkles', 'h-4 w-4 text-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'About the doctor', 'samvedna' ); ?>
				</h3>
				<p class="mt-3 text-sm leading-7 text-muted"><?php echo esc_html( $member['about'] ); ?></p>
			</div>

			<?php
			$list_section( 'stethoscope', __( 'Specializations', 'samvedna' ), samvedna_to_list( $member['specializations'] ) );
			$list_section( 'badge-check', __( 'Treatments', 'samvedna' ), samvedna_to_list( $member['treatments'] ) );
			if ( ! empty( $member['certifications'] ) ) {
				$list_section( 'graduation-cap', __( 'Certifications', 'samvedna' ), samvedna_to_list( $member['certifications'] ) );
			}
			if ( ! empty( $member['awards'] ) ) {
				$list_section( 'award', __( 'Awards', 'samvedna' ), samvedna_to_list( $member['awards'] ) );
			}
			?>

			<div>
				<h3 class="flex items-center gap-2 font-display text-base font-semibold text-text">
					<?php echo samvedna_icon( 'languages', 'h-4 w-4 text-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Languages', 'samvedna' ); ?>
				</h3>
				<div class="mt-3 flex flex-wrap gap-2">
					<?php foreach ( samvedna_to_list( $member['languages'] ) as $language ) : ?>
						<span class="rounded-full bg-bg-soft px-3 py-1 text-xs font-medium text-text ring-1 ring-border"><?php echo esc_html( $language ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( ! empty( $member['consultation'] ) ) : ?>
				<div class="rounded-2xl border border-primary/15 bg-primary/[0.04] p-5">
					<h3 class="flex items-center gap-2 font-display text-base font-semibold text-text">
						<?php echo samvedna_icon( 'calendar-clock', 'h-4 w-4 text-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Consultation', 'samvedna' ); ?>
					</h3>
					<p class="mt-2 text-sm leading-6 text-muted"><?php echo esc_html( $member['consultation'] ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</article>
</div>
