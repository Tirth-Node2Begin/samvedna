<?php
/**
 * Scroll-linked doctor avatar (ports components/sections/DoctorScrollAvatar.tsx).
 *
 * A fixed, pointer-events-none element. main.js lerps its position and size
 * between [data-doctor-avatar-origin] (hero) and [data-doctor-avatar-target]
 * (doctor intro) as the page scrolls, and drives the badge/label/glow progress.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

$doctor_img = samvedna_image_url( 'dr-krunal-kosada-removebg-preview.png' );
?>
<div aria-hidden="true" data-doctor-scroll-avatar class="pointer-events-none fixed z-[35]" style="display:none">
	<div class="relative h-full w-full">
		<div class="absolute inset-0 -z-10 rounded-full bg-cyan-100 blur-[80px]" data-avatar-glow style="opacity:0"></div>

		<div class="absolute right-1 -top-2 z-10 flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3 py-2 shadow-lg backdrop-blur-xl sm:-right-10" data-avatar-badge>
			<div class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-400/20">
				<?php echo samvedna_icon( 'award', 'h-3 w-3 text-amber-400' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div>
				<p class="text-[11px] font-bold leading-tight text-white">20+ Years</p>
				<p class="text-[10px] leading-tight text-white/55"><?php esc_html_e( 'Experience', 'samvedna' ); ?></p>
			</div>
		</div>

		<img src="<?php echo esc_url( $doctor_img ); ?>" alt="" decoding="async" class="absolute inset-0 h-full w-full object-contain drop-shadow-[0_20px_40px_rgba(0,0,0,0.35)]" />

		<div class="absolute left-1/2 flex -translate-x-1/2 flex-col items-center whitespace-nowrap border border-white/20 bg-[#0c1e35]/90 shadow-2xl backdrop-blur-md" data-avatar-label style="bottom:-16px;border-radius:999px;padding:8px 20px">
			<p class="font-black leading-none text-white" data-avatar-name style="font-size:14px"><?php echo esc_html( samvedna_option( 'founder_name', 'Dr. Krunal Kosada' ) ); ?></p>
			<p class="mt-1 font-bold uppercase leading-none text-cyan-300" data-avatar-title style="font-size:10px"><?php echo esc_html( samvedna_option( 'founder_title', 'Founder & Chief Consultant' ) ); ?></p>
		</div>
	</div>
</div>
