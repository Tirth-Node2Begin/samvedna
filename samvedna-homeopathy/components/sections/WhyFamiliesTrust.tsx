"use client";

import { trustReasons } from "@/constants/site";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import { ShieldCheck, HeartHandshake, Star } from "lucide-react";
import { motion } from "framer-motion";

export default function WhyFamiliesTrust() {
  return (
    <section className="bg-white pb-16 pt-8 md:pb-20 md:pt-12 lg:pb-[120px] lg:pt-16">
      <div className="mx-auto max-w-content px-5 md:px-8">
        
        {/* Header Grid */}
        <div className="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
          <AnimatedReveal className="max-w-2xl">
            <p className="text-sm font-semibold uppercase tracking-wider text-primary">
              Why families trust Samvedna
            </p>
            <AnimatedText
              as="h2"
              className="mt-4 text-balance font-display text-3xl font-semibold leading-[1.2] text-text md:text-4xl"
              text="Parents feel more confident when care is personal, consistent, and closely followed."
            />
          </AnimatedReveal>

          {/* Right Side Visual Object */}
          <AnimatedReveal variant="slideLeft" delay={0.1}>
            <div className="relative mx-auto flex w-full max-w-[320px] items-center justify-center sm:max-w-sm lg:ml-auto lg:mr-0">
              {/* Decorative backgrounds */}
              <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(37,99,235,0.06),transparent_70%)]" />
              
              <div className="relative flex h-48 w-48 items-center justify-center rounded-full border border-primary/10 bg-white shadow-[0_8px_32px_rgba(37,99,235,0.08)]">
                {/* Rotating Rings */}
                <motion.div 
                  animate={{ rotate: 360 }}
                  transition={{ duration: 25, repeat: Infinity, ease: "linear" }}
                  className="absolute inset-0 -m-8 rounded-full border border-dashed border-primary/20" 
                />
                <motion.div 
                  animate={{ rotate: -360, scale: [1, 1.05, 1] }}
                  transition={{ rotate: { duration: 35, repeat: Infinity, ease: "linear" }, scale: { duration: 4, repeat: Infinity, ease: "easeInOut" } }}
                  className="absolute inset-0 -m-16 rounded-full border border-primary/10" 
                />
                
                {/* Center Icon */}
                <motion.div 
                  animate={{ scale: [1, 1.08, 1] }}
                  transition={{ duration: 3.5, repeat: Infinity, ease: "easeInOut" }}
                  className="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-primary to-blue-700 text-white shadow-lg shadow-primary/30 relative z-10"
                >
                  <ShieldCheck className="h-9 w-9" strokeWidth={1.8} />
                </motion.div>

                {/* Floating Icons */}
                <motion.div 
                  animate={{ y: [0, -12, 0], rotate: [0, -6, 0] }}
                  transition={{ duration: 5, repeat: Infinity, ease: "easeInOut" }}
                  className="absolute -left-6 top-6 flex h-14 w-14 items-center justify-center rounded-2xl border border-border bg-white text-emerald-500 shadow-xl shadow-black/5 z-20"
                >
                  <HeartHandshake className="h-6 w-6" strokeWidth={1.8} />
                </motion.div>
                
                <motion.div 
                  animate={{ y: [0, 15, 0], rotate: [0, 8, 0] }}
                  transition={{ duration: 6.5, repeat: Infinity, ease: "easeInOut", delay: 1 }}
                  className="absolute -right-4 bottom-4 flex h-12 w-12 items-center justify-center rounded-2xl border border-border bg-white text-amber-400 shadow-xl shadow-black/5 z-20"
                >
                  <Star className="h-5 w-5" strokeWidth={2} fill="currentColor" />
                </motion.div>
              </div>
            </div>
          </AnimatedReveal>
        </div>

        {/* Features Grid */}
        <div className="mt-16 grid gap-4 md:mt-20 md:grid-cols-2 lg:grid-cols-3">
          {trustReasons.map((reason, index) => (
            <AnimatedReveal
              key={reason.title}
              className="h-full"
              delay={Math.min(index * 0.04, 0.2)}
            >
              <article className="group relative flex h-full min-h-[220px] flex-col overflow-hidden rounded-3xl border border-primary/10 bg-white p-7 transition duration-300 hover:border-primary/30 hover:shadow-[0_8px_30px_rgba(37,99,235,0.06)]">
                <div className="absolute bottom-0 left-0 top-0 w-1.5 origin-left scale-y-0 bg-primary transition duration-300 group-hover:scale-y-100" />
                <div className="transition duration-300 group-hover:translate-x-1.5">
                  <h3 className="font-display text-xl font-semibold text-text md:text-2xl">
                    {reason.title}
                  </h3>
                  <p className="mt-4 text-sm leading-relaxed text-muted md:text-base md:leading-7">
                    {reason.description}
                  </p>
                </div>
              </article>
            </AnimatedReveal>
          ))}
        </div>
      </div>
    </section>
  );
}
