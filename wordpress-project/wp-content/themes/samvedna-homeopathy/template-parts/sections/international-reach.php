<?php
/**
 * International reach + orbital world map
 * (ports InternationalReach.tsx + WorldMap.tsx).
 *
 * Planet orbits are pure CSS: each planet uses the sam-orbit keyframe with
 * per-planet custom properties (--a angle, --d direction, --dur duration) and a
 * counter-rotating label so the flag/text stay upright.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$planets  = samvedna_default_map_planets();
$orbits   = array( 140, 210, 280 );
$swan      = samvedna_image_url( 'samvedna-logo-swan.webp' );

$stats = array(
	array( 'value' => '11+', 'label' => __( 'Countries', 'samvedna' ), 'icon' => 'globe' ),
	array( 'value' => '10,000+', 'label' => __( 'Patients treated', 'samvedna' ), 'icon' => 'heart-handshake' ),
	array( 'value' => 'Online', 'label' => __( 'Follow-up care', 'samvedna' ), 'icon' => 'video' ),
	array( 'value' => '24/7', 'label' => __( 'Support access', 'samvedna' ), 'icon' => 'message-circle' ),
);

$features = array(
	array( 'icon' => 'video', 'title' => __( 'Consult from home', 'samvedna' ), 'description' => __( 'Parents share reports, videos, symptoms, and development history before the care plan is finalized.', 'samvedna' ), 'accent' => 'from-blue-500/10 to-cyan-500/10', 'icon_bg' => 'bg-blue-500/10 text-blue-600' ),
	array( 'icon' => 'stethoscope', 'title' => __( 'Personalized care plans', 'samvedna' ), 'description' => __( "Each plan is tailored to the child's unique presentation — not a one-size-fits-all approach.", 'samvedna' ), 'accent' => 'from-teal-500/10 to-emerald-500/10', 'icon_bg' => 'bg-teal-500/10 text-teal-600' ),
	array( 'icon' => 'heart-handshake', 'title' => __( 'Follow-up without gaps', 'samvedna' ), 'description' => __( 'Progress is reviewed over calls and messages so guidance continues between appointments.', 'samvedna' ), 'accent' => 'from-violet-500/10 to-purple-500/10', 'icon_bg' => 'bg-violet-500/10 text-violet-600' ),
);
?>
<section id="international-reach" class="relative scroll-mt-24 overflow-hidden py-16 text-text md:scroll-mt-28 md:py-20 lg:py-[120px]">
	<div class="absolute inset-0 bg-gradient-to-b from-[#F0F5FF] via-[#F8FAF9] to-white"></div>
	<div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px]" style="background:radial-gradient(circle, rgba(37,99,235,0.04), transparent 60%)"></div>

	<div class="relative mx-auto max-w-content px-5 md:px-8">
		<div class="text-center max-w-3xl mx-auto mb-14 lg:mb-20">
			<div data-reveal="fadeUp">
				<div class="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/[0.06] px-4 py-1.5 mb-6">
					<?php echo samvedna_icon( 'globe', 'h-3.5 w-3.5 text-primary', 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="text-xs font-semibold uppercase tracking-wider text-primary"><?php esc_html_e( 'International reach', 'samvedna' ); ?></span>
				</div>
			</div>
			<?php samvedna_animated_text( 'Worldwide consultations with care that stays connected.', 'h2', 'text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl lg:text-[44px] lg:leading-[1.15]' ); ?>
			<div data-reveal="fadeUp" data-reveal-delay="0.06">
				<p class="mt-5 text-balance text-base leading-7 text-muted md:text-lg md:leading-8"><?php esc_html_e( 'Families across 11+ countries consult from home while receiving personalized plans, medicine guidance, and continuous follow-up support.', 'samvedna' ); ?></p>
			</div>
		</div>

		<div data-reveal="fadeUp" data-reveal-delay="0.08">
			<div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-14 lg:mb-20">
				<?php foreach ( $stats as $stat ) : ?>
					<div class="group relative rounded-2xl border border-primary/10 bg-white/70 p-5 text-center backdrop-blur-sm transition-all duration-300 hover:border-primary/25 hover:shadow-[0_8px_32px_rgba(37,99,235,0.08)] hover:-translate-y-0.5">
						<div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/[0.07] text-primary transition-colors duration-300 group-hover:bg-primary/[0.12]">
							<?php echo samvedna_icon( $stat['icon'], 'h-5 w-5', 1.8 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<p class="font-display text-2xl font-bold leading-none text-primary sm:text-3xl"><?php echo esc_html( $stat['value'] ); ?></p>
						<p class="mt-2 text-xs font-semibold text-muted sm:text-sm"><?php echo esc_html( $stat['label'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="grid gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-14">
			<!-- Map -->
			<div data-reveal="slideRight" data-reveal-delay="0.1">
				<div class="relative rounded-3xl border border-primary/[0.08] bg-white/60 p-4 shadow-[0_4px_24px_rgba(37,99,235,0.04)] backdrop-blur-sm">
					<div class="relative mx-auto w-full max-w-[820px]">
						<!-- Desktop orbital -->
						<div class="hidden h-[640px] overflow-hidden rounded-[2.5rem] bg-white sm:block relative border border-primary/5 shadow-sm">
							<div class="absolute inset-0 opacity-[0.02] pointer-events-none" style="background-image:radial-gradient(circle at 2px 2px, #2563EB 1.5px, transparent 0);background-size:36px 36px"></div>
							<div class="absolute inset-0" style="background:radial-gradient(circle at 50% 50%, rgba(37,99,235,0.04), transparent 60%)"></div>

							<?php foreach ( $orbits as $radius ) : ?>
								<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full border border-dashed border-primary/20 pointer-events-none" style="width:<?php echo (int) ( $radius * 2 ); ?>px;height:<?php echo (int) ( $radius * 2 ); ?>px"></div>
							<?php endforeach; ?>

							<!-- Central sun -->
							<div class="absolute top-1/2 left-1/2 z-30 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
								<div class="relative flex h-[110px] w-[110px] items-center justify-center rounded-full bg-white shadow-[0_0_60px_rgba(37,99,235,0.15)] border border-primary/10">
									<div class="anim-sun-glow absolute inset-0 rounded-full border border-primary/30"></div>
									<img src="<?php echo esc_url( $swan ); ?>" alt="<?php esc_attr_e( 'Samvedna Swan Logo', 'samvedna' ); ?>" width="64" height="64" loading="lazy" decoding="async" class="object-contain drop-shadow-sm" />
								</div>
							</div>

							<!-- Orbiting planets -->
							<?php foreach ( $planets as $p ) : ?>
								<div class="anim-orbit absolute top-1/2 left-1/2 z-20" style="width:0;height:0;--a:<?php echo (int) $p['angle']; ?>deg;--d:<?php echo (int) $p['direction']; ?>;--dur:<?php echo (int) $p['speed']; ?>s">
									<div class="absolute top-1/2 left-1/2" style="transform:translate(-50%,-50%) translateX(<?php echo (int) $p['radius']; ?>px)">
										<div class="anim-orbit-counter" style="--a:<?php echo (int) $p['angle']; ?>deg;--d:<?php echo (int) $p['direction']; ?>;--dur:<?php echo (int) $p['speed']; ?>s">
											<div class="group relative flex items-center gap-2 rounded-full border border-white/80 bg-white/90 py-1.5 pl-2 pr-3 text-[13px] leading-none shadow-[0_4px_20px_rgba(37,99,235,0.08)] backdrop-blur-md transition-all duration-300 hover:shadow-[0_8px_32px_rgba(37,99,235,0.16)] hover:-translate-y-0.5 hover:bg-white cursor-default">
												<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 overflow-hidden relative border border-black/5">
													<img src="https://flagcdn.com/<?php echo esc_attr( $p['code'] ); ?>.svg" alt="<?php echo esc_attr( $p['name'] ); ?> flag" width="24" height="24" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover" />
												</div>
												<span class="font-semibold text-slate-800 whitespace-nowrap"><?php echo esc_html( $p['name'] ); ?></span>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Mobile list -->
						<div class="sm:hidden">
							<div class="flex items-center gap-4 rounded-2xl bg-gradient-to-br from-primary/[0.04] to-transparent p-5 border border-primary/10">
								<div class="flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm ring-2 ring-primary/10">
									<img src="<?php echo esc_url( $swan ); ?>" alt="<?php esc_attr_e( 'Samvedna Swan Logo', 'samvedna' ); ?>" width="32" height="32" loading="lazy" decoding="async" class="object-contain" />
								</div>
								<div>
									<p class="font-display text-lg font-bold text-slate-900"><?php esc_html_e( 'Worldwide access', 'samvedna' ); ?></p>
									<p class="text-sm text-slate-500 font-medium"><?php esc_html_e( 'Global online care', 'samvedna' ); ?></p>
								</div>
							</div>
							<div class="mt-6 grid grid-cols-1 gap-3">
								<?php foreach ( $planets as $p ) : ?>
									<div class="flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white p-3 shadow-sm transition-colors hover:border-primary/20">
										<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-50 overflow-hidden relative border border-slate-200">
											<img src="https://flagcdn.com/<?php echo esc_attr( $p['code'] ); ?>.svg" alt="<?php echo esc_attr( $p['name'] ); ?> flag" width="40" height="40" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover" />
										</div>
										<div class="flex-1"><span class="font-semibold text-slate-900"><?php echo esc_html( $p['name'] ); ?></span></div>
										<div class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
											<?php echo samvedna_icon( 'map-pin', 'h-3 w-3', 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											<span><?php esc_html_e( 'Active', 'samvedna' ); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Feature cards -->
			<div data-reveal="slideLeft" data-reveal-delay="0.14">
				<div class="space-y-4">
					<?php foreach ( $features as $feature ) : ?>
						<div class="group relative overflow-hidden rounded-2xl border border-primary/[0.08] bg-white/80 p-5 backdrop-blur-sm transition-all duration-300 hover:border-primary/20 hover:shadow-[0_8px_28px_rgba(37,99,235,0.06)]">
							<div class="absolute inset-0 bg-gradient-to-br <?php echo esc_attr( $feature['accent'] ); ?> opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
							<div class="relative flex gap-4">
								<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl <?php echo esc_attr( $feature['icon_bg'] ); ?> transition-transform duration-300 group-hover:scale-105">
									<?php echo samvedna_icon( $feature['icon'], 'h-5 w-5', 1.8 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<div>
									<p class="font-semibold text-text"><?php echo esc_html( $feature['title'] ); ?></p>
									<p class="mt-1.5 text-sm leading-relaxed text-muted"><?php echo esc_html( $feature['description'] ); ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
