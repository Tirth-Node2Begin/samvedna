<?php
/**
 * Meet Your Doctor (ports components/sections/DoctorIntro.tsx).
 *
 * The centre column holds [data-doctor-avatar-target] — the end position for
 * the scroll-linked doctor avatar that begins in the hero.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="relative isolate w-full bg-white py-20 sm:py-28">
	<div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" style="background:radial-gradient(ellipse at top, #ecfeff, transparent 70%)"></div>

	<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
		<div class="text-center mb-16" data-reveal="fadeUp">
			<h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"><?php esc_html_e( 'Meet Your Doctor', 'samvedna' ); ?></h2>
			<p class="mt-4 text-lg text-slate-600"><?php esc_html_e( "A trusted expert dedicated to your child's holistic development.", 'samvedna' ); ?></p>
		</div>

		<div class="relative flex flex-col items-center justify-center lg:flex-row lg:justify-between lg:gap-10">
			<!-- Left -->
			<div class="flex w-full flex-col gap-6 lg:w-[300px]" data-reveal="slideRight" data-reveal-delay="0.2">
				<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50">
					<div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50">
						<?php echo samvedna_icon( 'award', 'h-6 w-6 text-amber-500' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<h3 class="text-xl font-bold text-slate-900">20+ Years</h3>
					<p class="mt-2 text-sm text-slate-600"><?php esc_html_e( 'Extensive clinical experience in pediatric neurodevelopmental homeopathy.', 'samvedna' ); ?></p>
				</div>
				<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50">
					<div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50">
						<?php echo samvedna_icon( 'shield-check', 'h-6 w-6 text-cyan-600' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<h3 class="text-xl font-bold text-slate-900"><?php esc_html_e( 'Trusted Care', 'samvedna' ); ?></h3>
					<p class="mt-2 text-sm text-slate-600"><?php esc_html_e( 'Thousands of families trust our safe, natural, side-effect-free treatments.', 'samvedna' ); ?></p>
				</div>
			</div>

			<!-- Center avatar target -->
			<div class="relative my-12 flex flex-col items-center lg:my-0 lg:w-[320px] min-h-[420px]">
				<div data-doctor-avatar-target class="relative h-[420px] w-[320px]" aria-hidden="true"></div>
			</div>

			<!-- Right -->
			<div class="flex w-full flex-col gap-6 lg:w-[300px]" data-reveal="slideLeft" data-reveal-delay="0.4">
				<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50">
					<div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50">
						<?php echo samvedna_icon( 'stethoscope', 'h-6 w-6 text-emerald-600' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<h3 class="text-xl font-bold text-slate-900">BHMS, FCAH</h3>
					<p class="mt-2 text-sm text-slate-600"><?php esc_html_e( 'Highly qualified with specialized expertise in holistic child psychiatry.', 'samvedna' ); ?></p>
				</div>
				<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50 text-center flex flex-col justify-center items-center h-full min-h-[160px] bg-gradient-to-br from-cyan-600 to-emerald-600 text-white">
					<p class="text-lg font-medium leading-relaxed italic">&ldquo;<?php esc_html_e( 'Every child deserves to be understood as an individual.', 'samvedna' ); ?>&rdquo;</p>
				</div>
			</div>
		</div>
	</div>
</section>
