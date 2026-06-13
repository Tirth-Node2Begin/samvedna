"use client";

import React, { useEffect, useRef } from "react";
import { motion, useMotionValue, useTransform, useSpring, useScroll, useMotionValueEvent } from "framer-motion";
import Image from "next/image";
import { Star, Users, Globe, Award, Sparkles, Shield, Stethoscope, ScrollText, HeartPulse, Activity } from "lucide-react";
import useReducedMotion from "@/hooks/useReducedMotion";


/* ─── floating glassmorphism card ─── */
interface GlassCardProps {
  children: React.ReactNode;
  className?: string;
  delay?: number;
}

function GlassCard({ children, className = "", delay = 0 }: GlassCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 24, scale: 0.96, filter: "blur(8px)" }}
      animate={{ opacity: 1, y: 0, scale: 1, filter: "blur(0px)" }}
      transition={{ duration: 0.5, delay, ease: [0.22, 1, 0.36, 1] }}
      className={`relative rounded-2xl border border-white/20 bg-white/10 p-4 shadow-[0_8px_32px_rgba(0,0,0,0.18)] backdrop-blur-xl ${className}`}
    >
      {/* inner shine */}
      <div className="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-white/20 via-transparent to-transparent" />
      {children}
    </motion.div>
  );
}

/* ─── floating pill badge ─── */
function Pill({ icon: Icon, text }: { icon: React.ElementType<{ className?: string }>; text: string }) {
  return (
    <div className="flex items-center gap-1.5 rounded-full border border-white/25 bg-white/15 px-3 py-1 backdrop-blur-md">
      <Icon className="h-3 w-3 text-cyan-300" />
      <span className="text-[11px] font-semibold text-white/90">{text}</span>
    </div>
  );
}

export default function Hero() {
  const prefersReducedMotion = useReducedMotion();
  const containerRef = useRef<HTMLDivElement>(null);

  const [isScrolled, setIsScrolled] = React.useState(false);
  const { scrollY } = useScroll();
  useMotionValueEvent(scrollY, "change", (latest) => {
    setIsScrolled(latest > 50);
  });

  /* ── mouse parallax ── */
  const mouseX = useMotionValue(0);
  const mouseY = useMotionValue(0);

  const swanX = useTransform(mouseX, [-0.5, 0.5], [-14, 14]);
  const swanY = useTransform(mouseY, [-0.5, 0.5], [-10, 10]);
  const bgX = useTransform(mouseX, [-0.5, 0.5], [8, -8]);
  const bgY = useTransform(mouseY, [-0.5, 0.5], [5, -5]);

  const springConfig = { damping: 28, stiffness: 120 };
  const swanXS = useSpring(swanX, springConfig);
  const swanYS = useSpring(swanY, springConfig);
  const bgXS = useSpring(bgX, springConfig);
  const bgYS = useSpring(bgY, springConfig);

  useEffect(() => {
    if (prefersReducedMotion) return;
    const handleMouse = (e: MouseEvent) => {
      const el = containerRef.current;
      if (!el) return;
      const rect = el.getBoundingClientRect();
      mouseX.set((e.clientX - rect.left) / rect.width - 0.5);
      mouseY.set((e.clientY - rect.top) / rect.height - 0.5);
    };
    window.addEventListener("mousemove", handleMouse);
    return () => window.removeEventListener("mousemove", handleMouse);
  }, [prefersReducedMotion, mouseX, mouseY]);

  return (
    <section
      id="home"
      ref={containerRef}
      className="relative isolate pt-20"
      style={{ minHeight: "100svh" }}
    >
      {/* Background wrapper to contain overflow */}
      <div className="absolute inset-0 -z-30 overflow-hidden">
        <motion.div
          style={prefersReducedMotion ? {} : { x: bgXS, y: bgYS }}
          className="absolute inset-0 scale-[1.10]"
        >
        <Image
          src="/images/hero.png"
          alt=""
          fill
          priority
          fetchPriority="high"
          sizes="100vw"
          className="object-cover object-center blur-sm"
        />
        {/* cinematic overlay gradient */}
        <div className="absolute inset-0 bg-gradient-to-b from-[#0a1628]/40 via-transparent to-[#0a1628]/55" />
        <div className="absolute inset-0 bg-gradient-to-r from-[#0a1628]/30 via-transparent to-[#0a1628]/30" />
        </motion.div>
      </div>

      <div
        className="pointer-events-none absolute inset-0 -z-10"
        style={{
          background:
            "radial-gradient(ellipse 80% 70% at 50% 50%, transparent 40%, rgba(10,22,40,0.55) 100%)",
        }}
      />

      <div
        className="pointer-events-none absolute inset-0 -z-10 opacity-[0.03]"
        style={{ backgroundImage: "url('/images/noise.png')", backgroundRepeat: "repeat" }}
      />
      
      {/* ─────────── MAIN LAYOUT ─────────── */}
      <div className="relative mx-auto flex min-h-[100svh] max-w-[1400px] flex-col items-center justify-center px-4 py-8 sm:px-8">

        {/* ── Hero Content Frame ── */}
        <div className="relative w-full max-w-6xl flex-1 flex flex-col justify-center">

          <motion.div
            initial={{ opacity: 0, scale: 0.97 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0, ease: [0.22, 1, 0.36, 1] }}
            className="relative w-full flex-1 min-h-[520px] flex flex-col lg:flex-row items-center justify-center pt-20 lg:pt-0 gap-8 lg:gap-0"
          >

            {/* ─── FLOATING AMBIENT ICONS ─── */}
            <motion.div
              animate={prefersReducedMotion ? {} : { y: [0, -15, 0], rotate: [0, 5, 0] }}
              transition={{ duration: 6, repeat: Infinity, ease: "easeInOut", delay: 1 }}
              className="absolute left-[45%] top-[10%] z-0 flex h-12 w-12 items-center justify-center rounded-full border border-white/10 bg-white/5 backdrop-blur-md"
            >
              <ScrollText className="h-5 w-5 text-cyan-200/60" />
            </motion.div>
            <motion.div
              animate={prefersReducedMotion ? {} : { y: [0, 20, 0], rotate: [0, -10, 0] }}
              transition={{ duration: 7, repeat: Infinity, ease: "easeInOut", delay: 2 }}
              className="absolute left-[20%] top-[55%] z-0 flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md"
            >
              <HeartPulse className="h-6 w-6 text-emerald-200/60" />
            </motion.div>
            <motion.div
              animate={prefersReducedMotion ? {} : { y: [0, -12, 0], scale: [1, 1.05, 1] }}
              transition={{ duration: 5, repeat: Infinity, ease: "easeInOut", delay: 0.5 }}
              className="absolute left-[5%] bottom-[20%] z-0 flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 backdrop-blur-md"
            >
              <Activity className="h-4 w-4 text-cyan-200/60" />
            </motion.div>
            <motion.div
              animate={prefersReducedMotion ? {} : { y: [0, 18, 0], rotate: [0, 8, 0] }}
              transition={{ duration: 6.5, repeat: Infinity, ease: "easeInOut", delay: 3 }}
              className="absolute right-[16%] top-[40%] z-0 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md"
            >
              <Stethoscope className="h-7 w-7 text-emerald-200/60" />
            </motion.div>

            {/* ─── LEFT TOP GLASS CARD ("What is Samvedna?") ─── */}
            <div className="order-1 relative lg:absolute lg:left-4 lg:top-4 z-20 w-[90%] max-w-[400px] lg:w-[280px]">
              <GlassCard delay={0.1} className="flex min-h-[160px] lg:min-h-[240px] flex-col justify-center">
                <h3 className="mb-4 text-sm font-bold tracking-widest text-cyan-300 uppercase">What is Samvedna?</h3>
                <p className="text-[14px] leading-relaxed text-white/80">
                  A dedicated center for world-class homeopathy and child psychiatry, focusing on holistic development.
                </p>
              </GlassCard>
            </div>

            {/* ─── RIGHT TOP GLASS CARD ("Branding / Promise") ─── */}
            <div className="order-2 relative lg:absolute lg:right-4 lg:top-4 z-20 w-[90%] max-w-[400px] lg:w-[260px]">
              <GlassCard delay={0.2} className="flex min-h-[140px] flex-col justify-center">
                <h3 className="mb-3 text-sm font-bold tracking-widest text-emerald-300 uppercase">Our Promise</h3>
                <p className="text-[13px] leading-relaxed text-white/80">
                  Safe, natural, and side-effect-free homeopathic treatments perfectly tailored for your child's unique needs.
                </p>
              </GlassCard>
            </div>

            {/* ─── RIGHT BOTTOM (Doctor Image & Experience) ─── */}
            <div className="order-4 relative lg:absolute lg:bottom-16 lg:right-0 z-30 flex flex-col items-center w-[210px] min-h-[300px] mt-8 lg:mt-0">
               {/* 
                 We render the avatar only if NOT scrolled. 
                 If scrolled > 50px, it unmounts and Framer Motion layoutId 
                 animates it to the DoctorIntro section!
               */}
               {!isScrolled && (
                  <motion.div
                    layoutId="doctor-avatar-container"
                    initial={{ opacity: 0, y: 30 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
                    className="relative flex flex-col items-center w-full"
                  >
                {/* experience badge floating near doctor (Top Right) */}
                <motion.div
                  initial={{ opacity: 0, scale: 0.8 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 0.4, delay: 0.4 }}
                  className="absolute -right-10 -top-2 z-40 flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-xl shadow-lg"
                >
                  <div className="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-400/20">
                    <Award className="h-3 w-3 text-amber-400" />
                  </div>
                  <div>
                    <p className="text-[11px] font-bold text-white">20+ Years</p>
                    <p className="text-[10px] text-white/55">Experience</p>
                  </div>
                </motion.div>

                {/* Doctor Image */}
                <motion.div layoutId="doctor-img" className="relative w-[210px] h-[280px]">
                  <Image
                    src="/images/dr-krunal-kosada-removebg-preview.png"
                    alt="Dr. Krunal Kosada"
                    fill
                    priority
                    className="object-contain drop-shadow-[0_20px_40px_rgba(0,0,0,0.4)]"
                  />
                </motion.div>

                {/* Name Label */}
                <motion.div layoutId="doctor-name" className="absolute -bottom-4 flex flex-col items-center whitespace-nowrap rounded-full border border-white/20 bg-[#0c1e35]/80 px-5 py-2 shadow-xl backdrop-blur-md">
                  <p className="text-sm font-black text-white">Dr. Krunal Kosada</p>
                  <p className="text-[10px] font-semibold tracking-wide text-cyan-300 uppercase">Founder &amp; Chief Consultant</p>
                </motion.div>
              </motion.div>
              )}
            </div>

            {/* ─── CENTER SWAN / CHARACTER ─── */}
            <motion.div
              style={prefersReducedMotion ? {} : { x: swanXS, y: swanYS }}
              className="order-3 relative z-10 mx-auto flex w-full max-w-[600px] items-center justify-center h-[350px] lg:h-[500px]"
            >
              {/* glow beneath swan */}
              <div
                className="absolute left-1/2 top-1/2 h-72 w-[350px] -translate-x-1/2 -translate-y-1/2 rounded-full"
                style={{
                  background:
                    "radial-gradient(ellipse, rgba(0,196,196,0.18) 0%, transparent 70%)",
                  filter: "blur(40px)",
                }}
              />

              {/* floating animation wrapper */}
              <motion.div
                animate={prefersReducedMotion ? {} : { y: [0, -20, 0] }}
                transition={{ duration: 5, repeat: Infinity, ease: "easeInOut" }}
                className="relative"
              >
                <Image
                  src="/images/samvedna-logo-swan.png"
                  alt="Samvedna Homeopathy — Swan symbol of healing"
                  width={480}
                  height={520}
                  priority
                  className="relative z-10 drop-shadow-[0_40px_80px_rgba(0,196,196,0.4)]"
                  style={{ filter: "drop-shadow(0 0 60px rgba(0,196,196,0.25))" }}
                />
              </motion.div>
            </motion.div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
