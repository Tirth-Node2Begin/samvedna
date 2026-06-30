"use client";

import Image from "next/image";
import { Play } from "lucide-react";
import { useState } from "react";
import videoTestimonials from "@/constants/videoTestimonials";
import { socialLinks } from "@/constants/site";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import Modal from "@/components/ui/Modal";
import { blurDataUrl } from "@/lib/utils";

const youtubeChannel =
  socialLinks.find((link) => link.label === "YouTube")?.href ??
  "https://www.youtube.com";

export default function VideoTestimonials() {
  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const active = activeIndex === null ? null : videoTestimonials[activeIndex];

  const close = (): void => setActiveIndex(null);

  return (
    <section
      id="testimonials"
      className="relative overflow-hidden bg-bg-soft pt-10 pb-16 md:pt-16 md:pb-24 lg:pt-20 lg:pb-32"
    >
      {/* Decorative background circle (matches site testimonial styling) */}
      <div className="pointer-events-none absolute right-0 top-0 h-[800px] w-[800px] -translate-y-1/2 translate-x-1/3 rounded-full bg-primary/5 blur-3xl" />

      <div className="relative z-10 mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="max-w-3xl">
          <div className="flex items-center gap-2">
            <span className="h-px w-8 bg-accent" />
            <p className="text-sm font-bold uppercase tracking-widest text-primary">
              Parent stories
            </p>
          </div>
          <AnimatedText
            as="h2"
            className="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl lg:text-[40px] lg:leading-[1.2]"
            text="Hear families share their journey in their own words."
          />
          <p className="mt-5 text-base leading-7 text-muted">
            Real parents talk about the care, the consultations and the progress
            they have seen — tap any story to watch.
          </p>
        </AnimatedReveal>

        <div className="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
          {videoTestimonials.map((video, index) => (
            <AnimatedReveal
              key={`${video.name}-${index}`}
              className="h-full"
              delay={Math.min(index * 0.06, 0.3)}
            >
              <button
                type="button"
                onClick={() => setActiveIndex(index)}
                aria-label={`Play video story: ${video.name}, ${video.condition}`}
                suppressHydrationWarning
                className="group relative block aspect-video w-full overflow-hidden rounded-card border border-border bg-surface text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-premium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                <Image
                  src={video.poster}
                  alt={video.alt}
                  fill
                  sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                  className="object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                  placeholder="blur"
                  blurDataURL={blurDataUrl}
                  loading="lazy"
                />

                {/* Readability gradient */}
                <div className="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/10 to-slate-900/10" />

                {/* Play button */}
                <span className="absolute left-1/2 top-1/2 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-primary shadow-lg backdrop-blur transition duration-300 group-hover:scale-110 group-hover:bg-primary group-hover:text-white">
                  <Play className="ml-0.5 h-7 w-7 fill-current" aria-hidden="true" />
                </span>

                {video.duration ? (
                  <span className="absolute right-3 top-3 rounded-full bg-slate-900/70 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">
                    {video.duration}
                  </span>
                ) : null}

                {/* Caption */}
                <div className="absolute inset-x-0 bottom-0 p-5">
                  <p className="font-semibold text-white">{video.name}</p>
                  <p className="mt-1 text-xs font-medium text-white/80">
                    {video.condition}
                    <span className="mx-1.5 opacity-60">|</span>
                    {video.location}
                  </p>
                </div>
              </button>
            </AnimatedReveal>
          ))}
        </div>
      </div>

      <Modal
        open={active !== null}
        onClose={close}
        ariaLabel={active ? `${active.name} video story` : "Video story"}
        className="max-w-3xl p-0"
      >
        {active ? (
          <div>
            <div className="aspect-video w-full overflow-hidden rounded-t-3xl bg-black">
              {active.youtubeId ? (
                <iframe
                  key={active.youtubeId}
                  src={`https://www.youtube-nocookie.com/embed/${active.youtubeId}?autoplay=1&rel=0&modestbranding=1`}
                  title={`${active.name} — ${active.condition}`}
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowFullScreen
                  className="h-full w-full border-0"
                />
              ) : (
                <div className="relative h-full w-full">
                  <Image
                    src={active.poster}
                    alt={active.alt}
                    fill
                    sizes="(min-width: 768px) 768px, 100vw"
                    className="object-cover opacity-60"
                    placeholder="blur"
                    blurDataURL={blurDataUrl}
                  />
                  <div className="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-slate-900/50 p-6 text-center">
                    <p className="text-lg font-semibold text-white">
                      Full video coming soon
                    </p>
                    <a
                      href={youtubeChannel}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary hover:text-white"
                    >
                      <Play className="h-4 w-4 fill-current" aria-hidden="true" />
                      Watch on YouTube
                    </a>
                  </div>
                </div>
              )}
            </div>

            <div className="p-6">
              <p className="font-bold text-text">{active.name}</p>
              <p className="mt-1 text-sm font-medium text-muted">
                {active.condition}
                <span className="mx-1.5 opacity-50">|</span>
                {active.location}
              </p>
            </div>
          </div>
        ) : null}
      </Modal>
    </section>
  );
}
