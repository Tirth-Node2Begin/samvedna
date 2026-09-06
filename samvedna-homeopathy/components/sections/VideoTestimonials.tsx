"use client";

import Image from "next/image";
import { useState, type CSSProperties } from "react";
import { MapPin, Play, Quote } from "lucide-react";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import VideoStoryModal from "@/components/ui/VideoStoryModal";
import type { VideoTestimonial } from "@/types";
import { blurDataUrl, defaultBlogImage } from "@/lib/utils";

// Standalone "Parent stories" section shown before the blog carousel. Mirrors
// the WordPress build (`template-parts/sections/video-testimonials.php`):
// a continuous right-to-left marquee of video cards, each opening the shared
// video modal. Slower for fewer cards, capped so many cards still glide.
export default function VideoTestimonials({
  videos,
}: {
  videos: VideoTestimonial[];
}) {
  const [activeVideo, setActiveVideo] = useState<VideoTestimonial | null>(null);

  if (videos.length === 0) {
    return null;
  }

  const marqueeDuration = Math.max(24, videos.length * 7);

  // Render the set twice: the first is interactive, the second is a
  // presentational clone (hidden from AT / keyboard) for a seamless loop.
  const loop = [...videos, ...videos];

  return (
    <section
      id="testimonials"
      className="scroll-mt-24 overflow-hidden bg-bg-soft py-16 md:scroll-mt-28 md:py-20 lg:py-[120px]"
    >
      <div className="mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="max-w-2xl">
          <p className="text-sm font-semibold text-primary">Parent stories</p>
          <AnimatedText
            as="h2"
            className="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl"
            text="Real families sharing their child's progress."
          />
          <p className="mt-4 max-w-md text-base leading-7 text-muted">
            Hear directly from parents about their experience with autism, ADHD,
            speech delay and developmental care at Samvedna.
          </p>
        </AnimatedReveal>
      </div>

      <AnimatedReveal className="marquee-viewport mt-12">
        <div
          className="marquee-track flex w-max will-change-transform"
          style={{ "--marquee-duration": `${marqueeDuration}s` } as CSSProperties}
        >
          {loop.map((video, index) => {
            const clone = index >= videos.length;
            return (
              <button
                key={`${video.condition}-${index}`}
                type="button"
                // Some browser extensions (form fillers / "verify" tools) inject
                // an `fdprocessedid` attribute onto buttons before React hydrates,
                // which trips a hydration attribute-mismatch warning. This element
                // is deterministic, so suppress that third-party-only diff.
                suppressHydrationWarning
                onClick={() => setActiveVideo(video)}
                aria-hidden={clone || undefined}
                tabIndex={clone ? -1 : undefined}
                aria-label={
                  clone
                    ? undefined
                    : `Play parent video story: ${video.name}, ${video.condition}`
                }
                className="group/video relative mr-6 block aspect-[4/3] w-[78vw] shrink-0 overflow-hidden rounded-card border border-border bg-slate-900 text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-premium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary sm:w-[44vw] lg:w-[31vw] xl:w-[380px]"
              >
                <Image
                  src={video.poster || defaultBlogImage}
                  alt={clone ? "" : video.alt}
                  fill
                  sizes="(min-width: 1280px) 380px, (min-width: 1024px) 31vw, (min-width: 640px) 44vw, 78vw"
                  className="object-cover transition-transform duration-500 group-hover/video:scale-[1.05]"
                  placeholder="blur"
                  blurDataURL={blurDataUrl}
                  // Eager, NOT lazy: these cards live in a continuously
                  // transform-animated marquee, where native lazy-loading often
                  // never fires its intersection check and the posters stay blank.
                  loading="eager"
                  // A dead YouTube thumbnail (private/removed video → 404) would
                  // otherwise leave a blank dark card; fall back to a local image.
                  onError={(event) => {
                    const img = event.currentTarget;
                    if (img.dataset.fallback) return;
                    img.dataset.fallback = "1";
                    img.src = defaultBlogImage;
                  }}
                />

                {/* Dark at top (for the overlaid text) and bottom (for the duration). */}
                <span className="absolute inset-0 bg-gradient-to-b from-slate-900/85 via-slate-900/5 to-slate-900/70" />

                {/* Overlaid text — written above the video. */}
                <span className="absolute inset-x-0 top-0 p-4">
                  <span className="block text-white/85">
                    <Quote
                      className="h-6 w-6 fill-current"
                      strokeWidth={0}
                      aria-hidden="true"
                    />
                  </span>
                  {video.condition ? (
                    <span className="mt-1.5 block font-display text-base font-semibold leading-snug text-white">
                      {video.condition}
                    </span>
                  ) : null}
                  <span className="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-white/85">
                    <span className="font-semibold">{video.name}</span>
                    {video.location ? (
                      <>
                        <span className="opacity-50">&bull;</span>
                        <span className="inline-flex items-center gap-1">
                          <MapPin className="h-3 w-3" aria-hidden="true" />
                          {video.location}
                        </span>
                      </>
                    ) : null}
                  </span>
                </span>

                <span className="absolute left-1/2 top-1/2 flex h-12 w-12 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-primary shadow-lg transition duration-300 group-hover/video:scale-110 group-hover/video:bg-primary group-hover/video:text-white">
                  <Play className="ml-0.5 h-5 w-5 fill-current" aria-hidden="true" />
                </span>

                {video.duration ? (
                  <span className="absolute bottom-2.5 right-2.5 rounded bg-slate-900/75 px-1.5 py-0.5 text-[11px] font-semibold leading-none text-white">
                    {video.duration}
                  </span>
                ) : null}
              </button>
            );
          })}
        </div>
      </AnimatedReveal>

      <VideoStoryModal video={activeVideo} onClose={() => setActiveVideo(null)} />
    </section>
  );
}
