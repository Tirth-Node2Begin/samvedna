"use client";

import { ChevronLeft, ChevronRight, Star } from "lucide-react";
import { useMemo, useState } from "react";
import testimonials from "@/constants/testimonials";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";

function wrapIndex(index: number): number {
  const length = testimonials.length;
  return ((index % length) + length) % length;
}

export default function Testimonials() {
  const [activeIndex, setActiveIndex] = useState(0);

  const visibleDesktop = useMemo(
    () =>
      [0, 1, 2].map((offset) => testimonials[wrapIndex(activeIndex + offset)]),
    [activeIndex]
  );
  const visibleMobile = testimonials[wrapIndex(activeIndex)];

  const previous = (): void => setActiveIndex((index) => wrapIndex(index - 1));
  const next = (): void => setActiveIndex((index) => wrapIndex(index + 1));

  return (
    <section id="testimonials" className="bg-bg-soft pt-10 pb-16 md:pt-16 md:pb-24 lg:pt-20 lg:pb-32 relative overflow-hidden">
      {/* Decorative background circle */}
      <div className="absolute top-0 right-0 -translate-y-1/2 translate-x-1/3 w-[800px] h-[800px] rounded-full bg-primary/5 blur-3xl pointer-events-none" />
      
      <div className="mx-auto max-w-content px-5 md:px-8 relative z-10">
        <AnimatedReveal className="flex flex-col justify-between gap-8 md:flex-row md:items-end">
          <div className="max-w-3xl">
            <div className="flex items-center gap-2">
              <span className="h-px w-8 bg-accent" />
              <p className="text-sm font-bold uppercase tracking-widest text-primary">Parent experiences</p>
            </div>
            <AnimatedText
              as="h2"
              className="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl lg:text-[40px] lg:leading-[1.2]"
              text="Parents trust care that listens carefully, explains clearly, and follows through."
            />
          </div>
          <div className="flex gap-3">
            <button
              type="button"
              aria-label="Previous testimonial"
              onClick={previous}
              suppressHydrationWarning
              className="inline-flex h-14 w-14 items-center justify-center rounded-full border border-border bg-white text-primary transition-all hover:border-primary hover:bg-primary hover:text-white hover:shadow-lg active:scale-95"
            >
              <ChevronLeft className="h-6 w-6" aria-hidden="true" />
            </button>
            <button
              type="button"
              aria-label="Next testimonial"
              onClick={next}
              suppressHydrationWarning
              className="inline-flex h-14 w-14 items-center justify-center rounded-full border border-border bg-white text-primary transition-all hover:border-primary hover:bg-primary hover:text-white hover:shadow-lg active:scale-95"
            >
              <ChevronRight className="h-6 w-6" aria-hidden="true" />
            </button>
          </div>
        </AnimatedReveal>

        <div className="mt-16 md:mt-20" aria-live="polite">
          {/* Mobile View */}
          <div className="lg:hidden">
            <AnimatedReveal key={activeIndex}>
              <article className="flex flex-col justify-between min-h-[380px] rounded-3xl border border-border/60 bg-white p-8 shadow-sm">
                <div>
                  <div className="flex gap-1 text-accent mb-6">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className="h-5 w-5 fill-current" />
                    ))}
                  </div>
                  <p className="text-lg italic leading-relaxed text-text/80">
                    &ldquo;{visibleMobile.quote}&rdquo;
                  </p>
                </div>
                <div className="mt-8 flex items-center gap-4 pt-6 border-t border-border/40">
                  <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xl font-bold text-primary">
                    {visibleMobile.name.charAt(0)}
                  </div>
                  <div>
                    <p className="font-bold text-text">{visibleMobile.name}</p>
                    <p className="text-xs font-medium text-muted mt-1">
                      {visibleMobile.condition}
                      <span className="mx-1.5 opacity-50">|</span>
                      {visibleMobile.location}
                    </p>
                  </div>
                </div>
              </article>
            </AnimatedReveal>
          </div>

          {/* Desktop View */}
          <div className="hidden lg:grid lg:grid-cols-3 gap-8">
            {visibleDesktop.map((testimonial, idx) => (
              <article
                key={`${testimonial.name}-${testimonial.location}-${idx}`}
                className="flex flex-col justify-between min-h-[400px] rounded-3xl border border-border/60 bg-white p-10 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:border-primary/20 group relative overflow-hidden"
              >
                {/* Subtle gradient overlay on hover */}
                <div className="absolute inset-0 bg-gradient-to-br from-primary/0 to-primary/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100 pointer-events-none" />
                
                <div className="relative z-10">
                  <div className="flex gap-1 text-accent mb-8">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className="h-5 w-5 fill-current transition-transform group-hover:scale-110" style={{ transitionDelay: `${i * 50}ms` }} />
                    ))}
                  </div>
                  <p className="text-lg italic leading-relaxed text-text/80">
                    &ldquo;{testimonial.quote}&rdquo;
                  </p>
                </div>
                
                <div className="mt-10 flex items-center gap-4 pt-6 border-t border-border/40 relative z-10">
                  <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xl font-bold text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                    {testimonial.name.charAt(0)}
                  </div>
                  <div>
                    <p className="font-bold text-text">{testimonial.name}</p>
                    <p className="text-xs font-medium text-muted mt-1">
                      {testimonial.condition}
                      <span className="mx-1.5 opacity-50">|</span>
                      {testimonial.location}
                    </p>
                  </div>
                </div>
              </article>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
