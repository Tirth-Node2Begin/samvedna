"use client";

import { useState, useEffect } from "react";
import { journeySteps } from "@/constants/site";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import { cn } from "@/lib/utils";

export default function TreatmentJourney() {
  const [currentIndex, setCurrentIndex] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      // Loop from 0 to journeySteps.length - 1
      setCurrentIndex((prev) => (prev === journeySteps.length - 1 ? 0 : prev + 1));
    }, 2000); // Change slide every 2 seconds
    return () => clearInterval(timer);
  }, []);

  // Duplicate steps to create an endless-feel track
  const extendedSteps = [...journeySteps, ...journeySteps];

  return (
    <section id="journey" className="bg-[#F8FAF9] py-16 md:py-20 lg:py-[120px]">
      <div className="mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="max-w-3xl">
          <p className="text-sm font-semibold text-primary">Treatment journey</p>
          <AnimatedText
            as="h2"
            className="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl"
            text="A clear care path so parents know what happens after the first consultation."
          />
        </AnimatedReveal>

        <div className="mt-14 md:hidden">
          <div className="space-y-6 border-l border-primary/35 pl-6">
            {journeySteps.map((step, index) => (
              <AnimatedReveal key={step.title} delay={Math.min(index * 0.05, 0.24)}>
                <article className="relative rounded-card border border-border bg-white p-6">
                  <span className="absolute -left-[35px] top-6 font-display text-xl font-bold text-accent">
                    {String(index + 1).padStart(2, "0")}
                  </span>
                  <h3 className="font-display text-2xl font-semibold text-text">
                    {step.title}
                  </h3>
                  <p className="mt-4 text-base leading-7 text-muted">
                    {step.description}
                  </p>
                </article>
              </AnimatedReveal>
            ))}
          </div>
        </div>

        <div className="mt-20 hidden overflow-hidden pb-16 md:block">
          <div className="relative mx-auto w-full max-w-[1200px]">
            {/* The background connecting line */}
            <div className="absolute left-0 right-0 top-[88px] h-[2px] bg-primary/10" />
            
            {/* Carousel Track */}
            <div 
              className="flex transition-transform duration-1000 ease-in-out"
              style={{ transform: `translateX(-${currentIndex * (100 / extendedSteps.length)}%)`, width: "max-content" }}
            >
              {extendedSteps.map((_, idx) => {
                // The center card is always currentIndex + 1. Shift the step
                // mapping by one so the first centered card is step 1 (01).
                const stepIndex =
                  ((idx - 1) % journeySteps.length + journeySteps.length) %
                  journeySteps.length;
                const step = journeySteps[stepIndex];
                const isCenter = idx === currentIndex + 1;
                const displayNumber = String(stepIndex + 1).padStart(2, "0");

                return (
                  <div
                    key={`${step.title}-${idx}`}
                    className="w-[33.333333vw] max-w-[400px] flex-shrink-0 px-4"
                  >
                    <article
                      className={cn(
                        "relative flex min-h-[320px] flex-col justify-between rounded-[2rem] border p-8 transition-all duration-1000",
                        isCenter
                          ? "scale-105 border-primary/20 bg-white shadow-2xl ring-4 ring-primary/5"
                          : "scale-90 border-transparent bg-white/40 opacity-40 grayscale-[30%]"
                      )}
                    >
                      <div>
                        <p className={cn(
                          "font-display text-5xl font-bold transition-colors duration-1000",
                          isCenter ? "text-primary" : "text-primary/20"
                        )}>
                          {displayNumber}
                        </p>
                        <h3 className="mt-6 font-display text-2xl font-bold leading-tight text-text">
                          {step.title}
                        </h3>
                      </div>
                      <p className="mt-4 text-base leading-relaxed text-muted">
                        {step.description}
                      </p>
                    </article>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
