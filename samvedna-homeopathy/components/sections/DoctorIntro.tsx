"use client";

import React, { useRef } from "react";
import { motion, useScroll, useMotionValueEvent } from "framer-motion";
import Image from "next/image";
import { Award, ShieldCheck, Stethoscope } from "lucide-react";

export default function DoctorIntro() {
  const containerRef = useRef<HTMLElement>(null);
  const { scrollYProgress } = useScroll({
    target: containerRef,
    offset: ["start end", "center center"],
  });

  const [isScrolled, setIsScrolled] = React.useState(false);
  const { scrollY } = useScroll();
  useMotionValueEvent(scrollY, "change", (latest) => {
    setIsScrolled(latest > 50);
  });

  return (
    <section ref={containerRef} className="relative isolate w-full bg-white py-20 sm:py-28">
      {/* Background decoration */}
      <div className="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,theme(colors.cyan.50),transparent_70%)] overflow-hidden" />

      <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.6 }}
          className="text-center mb-16"
        >
          <h2 className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Meet Your Doctor</h2>
          <p className="mt-4 text-lg text-slate-600">A trusted expert dedicated to your child's holistic development.</p>
        </motion.div>

        {/* Center Layout Container */}
        <div className="relative flex flex-col items-center justify-center lg:flex-row lg:justify-between lg:gap-10">
          
          {/* Left Side Data */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7, delay: 0.2 }}
            className="flex w-full flex-col gap-6 lg:w-[300px]"
          >
            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50">
              <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50">
                <Award className="h-6 w-6 text-amber-500" />
              </div>
              <h3 className="text-xl font-bold text-slate-900">20+ Years</h3>
              <p className="mt-2 text-sm text-slate-600">Extensive clinical experience in pediatric neurodevelopmental homeopathy.</p>
            </div>

            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50">
              <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50">
                <ShieldCheck className="h-6 w-6 text-cyan-600" />
              </div>
              <h3 className="text-xl font-bold text-slate-900">Trusted Care</h3>
              <p className="mt-2 text-sm text-slate-600">Thousands of families trust our safe, natural, side-effect-free treatments.</p>
            </div>
          </motion.div>

          {/* Center Avatar (Scroll Move) */}
          <div className="relative my-12 flex flex-col items-center lg:my-0 lg:w-[320px] min-h-[420px]">
            {isScrolled && (
            <motion.div
              layoutId="doctor-avatar-container"
              className="relative z-10 flex w-full flex-col items-center"
              transition={{ duration: 0.8, type: "spring", bounce: 0.2 }}
            >
              {/* Soft Glow Behind Avatar */}
              <div className="absolute inset-0 -z-10 animate-pulse rounded-full bg-cyan-100 blur-[80px]" />
              
              <motion.div layoutId="doctor-img" className="relative w-[320px] h-[420px]">
                <Image
                  src="/images/dr-krunal-kosada-removebg-preview.png"
                  alt="Dr. Krunal Kosada"
                  fill
                  className="object-contain drop-shadow-2xl"
                />
              </motion.div>

              {/* Name Label */}
              <motion.div layoutId="doctor-name" className="absolute -bottom-6 flex flex-col items-center whitespace-nowrap rounded-2xl border border-white bg-slate-900 px-8 py-4 shadow-2xl">
                <p className="text-xl font-black text-white">Dr. Krunal Kosada</p>
                <p className="mt-1 text-xs font-bold tracking-widest text-cyan-400 uppercase">Founder &amp; Chief Consultant</p>
              </motion.div>
            </motion.div>
            )}
          </div>
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7, delay: 0.4 }}
            className="flex w-full flex-col gap-6 lg:w-[300px]"
          >
            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50">
              <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50">
                <Stethoscope className="h-6 w-6 text-emerald-600" />
              </div>
              <h3 className="text-xl font-bold text-slate-900">BHMS, FCAH</h3>
              <p className="mt-2 text-sm text-slate-600">Highly qualified with specialized expertise in holistic child psychiatry.</p>
            </div>

           <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50 text-center flex flex-col justify-center items-center h-full min-h-[160px] bg-gradient-to-br from-cyan-600 to-emerald-600 text-white">
               <p className="text-lg font-medium leading-relaxed italic">
                 "Every child deserves to be understood as an individual."
               </p>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
