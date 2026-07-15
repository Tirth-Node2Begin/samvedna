<?php
/**
 * Hero (ports components/sections/Hero.tsx).
 *
 * Mouse parallax (background + swan) and the floating swan/ambient icons are
 * driven by assets/js/main.js + CSS keyframes. The [data-doctor-avatar-origin]
 * anchor is the start position for the scroll-linked doctor avatar.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$swan_img = samvedna_image_url( 'samvedna-logo-swan.webp' );
$bg_img   = samvedna_image_url( 'hero.webp' );
?>
<section id="home" class="relative isolate pt-20" style="min-height:100svh">
	<!-- Background (parallax) -->
	<div class="absolute inset-0 -z-30 overflow-hidden">
		<div class="absolute inset-0 scale-[1.10]" data-hero-bg>
			<img src="<?php echo esc_url( $bg_img ); ?>" alt="" fetchpriority="high" decoding="async" class="absolute inset-0 h-full w-full object-cover object-center blur-[6px]" />
			<div class="absolute inset-0 bg-gradient-to-b from-[#0a1628]/40 via-transparent to-[#0a1628]/55"></div>
			<div class="absolute inset-0 bg-gradient-to-r from-[#0a1628]/30 via-transparent to-[#0a1628]/30"></div>
		</div>
	</div>

	<div class="pointer-events-none absolute inset-0 -z-10" style="background:radial-gradient(ellipse 80% 70% at 50% 50%, transparent 40%, rgba(10,22,40,0.55) 100%)"></div>

	<div class="relative mx-auto flex min-h-[100svh] max-w-[1400px] flex-col items-center justify-center px-4 py-8 sm:px-8">
		<div class="relative w-full max-w-6xl flex-1 flex flex-col justify-center">
			<div class="relative w-full flex-1 min-h-[520px] flex flex-col lg:flex-row items-center justify-center pt-20 lg:pt-0 gap-8 lg:gap-0">

				<!-- Floating ambient icons -->
				<div class="anim-float-a absolute left-[20%] top-[55%] z-0 flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md">
					<?php echo samvedna_icon( 'heart-pulse', 'h-6 w-6 text-emerald-200/60' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="anim-float-b absolute left-[5%] bottom-[20%] z-0 flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 backdrop-blur-md">
					<?php echo samvedna_icon( 'activity', 'h-4 w-4 text-cyan-200/60' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="anim-float-c absolute right-[16%] top-[40%] z-0 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md">
					<?php echo samvedna_icon( 'stethoscope', 'h-7 w-7 text-emerald-200/60' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<!-- Left glass card -->
				<div class="order-3 relative z-20 w-[90%] max-w-[400px] lg:absolute lg:left-4 lg:top-4 lg:w-[280px]">
					<div class="relative rounded-2xl border border-white/20 bg-white/10 p-4 shadow-[0_8px_32px_rgba(0,0,0,0.18)] backdrop-blur-xl flex flex-col justify-start py-6 px-5" data-reveal="fadeUp" data-reveal-delay="0.1">
						<div class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-white/20 via-transparent to-transparent"></div>
						<h3 class="mb-3 text-sm font-bold tracking-widest text-cyan-300 uppercase"><?php echo esc_html( samvedna_option( 'hero_card1_title', 'What is Samvedna?' ) ); ?></h3>
						<p class="text-[14px] leading-relaxed text-white/80"><?php echo esc_html( samvedna_option( 'hero_card1_text', 'A dedicated center for world-class homeopathy and child psychiatry, focusing on holistic development.' ) ); ?></p>
					</div>
				</div>

				<!-- Right glass card -->
				<div class="order-4 relative z-20 w-[90%] max-w-[400px] lg:absolute lg:right-4 lg:top-4 lg:w-[260px]">
					<div class="relative rounded-2xl border border-white/20 bg-white/10 p-4 shadow-[0_8px_32px_rgba(0,0,0,0.18)] backdrop-blur-xl flex min-h-[140px] flex-col justify-center" data-reveal="fadeUp" data-reveal-delay="0.2">
						<div class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-white/20 via-transparent to-transparent"></div>
						<h3 class="mb-3 text-sm font-bold tracking-widest text-emerald-300 uppercase"><?php echo esc_html( samvedna_option( 'hero_card2_title', 'Our Promise' ) ); ?></h3>
						<p class="text-[13px] leading-relaxed text-white/80"><?php echo esc_html( samvedna_option( 'hero_card2_text', "Safe, natural, and side-effect-free homeopathic treatments perfectly tailored for your child's unique needs." ) ); ?></p>
					</div>
				</div>

				<!-- Doctor avatar origin anchor (the floating doctor image is positioned here at scroll top). Hidden on mobile — the avatar shows statically inside the doctor intro section there. -->
				<div class="order-2 relative lg:absolute lg:bottom-16 lg:right-0 z-30 hidden lg:flex flex-col items-center w-[210px] min-h-[300px] lg:mt-0">
					<div data-doctor-avatar-origin class="relative h-[280px] w-[210px]" aria-hidden="true"></div>
				</div>

				<!-- Center swan -->
				<div class="order-1 relative z-10 mx-auto flex h-[350px] w-full max-w-[600px] items-center justify-center lg:h-[500px]" data-hero-swan>
					<div class="absolute left-1/2 top-1/2 h-72 w-[350px] -translate-x-1/2 -translate-y-1/2 rounded-full" style="background:radial-gradient(ellipse, rgba(0,196,196,0.18) 0%, transparent 70%);filter:blur(40px)"></div>
					<div class="anim-swan relative">
						<img src="<?php echo esc_url( $swan_img ); ?>" alt="<?php esc_attr_e( 'Samvedna Homeopathy — Swan symbol of healing', 'samvedna' ); ?>" width="480" height="520" fetchpriority="high" decoding="async" class="relative z-10 drop-shadow-[0_40px_80px_rgba(0,196,196,0.4)]" style="filter:drop-shadow(0 0 60px rgba(0,196,196,0.25))" />
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
