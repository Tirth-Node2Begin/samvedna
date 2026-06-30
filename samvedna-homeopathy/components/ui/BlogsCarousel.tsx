"use client";

import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useState } from "react";
import BlogCard from "@/components/ui/BlogCard";
import VideoStoryModal from "@/components/ui/VideoStoryModal";
import { blogPosts } from "@/constants/blogs";
import videoTestimonials from "@/constants/videoTestimonials";
import type { VideoTestimonial } from "@/types";
import useReducedMotion from "@/hooks/useReducedMotion";

// How many cards are on screen at once, and how often a single random card is
// swapped out for a different post.
const VISIBLE = 3;
const INTERVAL_MS = 3000;

// Replace ONE random visible card with a random post that isn't already shown.
function swapOne(current: number[]): number[] {
  const used = new Set(current);
  const candidates = blogPosts
    .map((_, i) => i)
    .filter((i) => !used.has(i));
  if (candidates.length === 0) return current;

  const slot = Math.floor(Math.random() * current.length);
  const next = candidates[Math.floor(Math.random() * candidates.length)];
  const updated = [...current];
  updated[slot] = next;
  return updated;
}

export default function BlogsCarousel() {
  // Deterministic first render so the server HTML matches the client's first
  // render (no hydration mismatch). Randomisation only starts after mount,
  // inside the interval below.
  const [indices, setIndices] = useState<number[]>(() =>
    blogPosts.slice(0, VISIBLE).map((_, i) => i)
  );
  const [paused, setPaused] = useState(false);
  const [activeVideo, setActiveVideo] = useState<VideoTestimonial | null>(null);
  const prefersReducedMotion = useReducedMotion();

  // Pause swapping on hover/focus and while a video is open, so the card that
  // opened the player doesn't get swapped out from under the modal.
  useEffect(() => {
    if (paused || activeVideo !== null || blogPosts.length <= VISIBLE) return;
    const id = window.setInterval(() => {
      setIndices((current) => swapOne(current));
    }, INTERVAL_MS);
    return () => window.clearInterval(id);
  }, [paused, activeVideo]);

  const fadeDuration = prefersReducedMotion ? 0 : 0.5;

  return (
    <>
      <div
        className="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8"
        onMouseEnter={() => setPaused(true)}
        onMouseLeave={() => setPaused(false)}
        onFocusCapture={() => setPaused(true)}
        onBlurCapture={() => setPaused(false)}
      >
        {/* One stable cell per slot; only the card inside a cell crossfades when
            that slot's post changes, so the other two cards stay still. */}
        {indices.map((postIndex, slot) => (
          <div key={slot} className="relative h-full">
            <AnimatePresence initial={false} mode="popLayout">
              <motion.div
                key={blogPosts[postIndex].slug}
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                transition={{ duration: fadeDuration, ease: "easeOut" }}
                className="h-full"
              >
                <BlogCard
                  post={blogPosts[postIndex]}
                  video={videoTestimonials[postIndex % videoTestimonials.length]}
                  onPlayVideo={setActiveVideo}
                />
              </motion.div>
            </AnimatePresence>
          </div>
        ))}
      </div>

      <VideoStoryModal video={activeVideo} onClose={() => setActiveVideo(null)} />
    </>
  );
}
