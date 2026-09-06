"use client";

import { useEffect } from "react";
import { usePathname } from "next/navigation";

/**
 * Lenis smooth scrolling, pointed at `<main>`.
 *
 * The shell makes `<main id="app-scroll">` the scroll container, not the
 * document, so Lenis has to be handed that element as its wrapper — the default
 * (window) would attach to a container that never scrolls.
 *
 * It re-attaches on route change because the login screen has no `<main>`, and
 * scrolls the new page back to the top itself: with Lenis owning the scroll
 * position, Next's own restoration does not land.
 */
export function SmoothScroll() {
  const pathname = usePathname();

  useEffect(() => {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    const wrapper = document.getElementById("app-scroll");
    if (!wrapper) return;

    const content = wrapper.firstElementChild as HTMLElement | null;
    if (!content) return;
    const wrapperElement = wrapper;
    const contentElement = content;

    let cancelled = false;
    let frame = 0;
    let lenis: {
      raf: (time: number) => void;
      scrollTo: (target: number, options?: { immediate?: boolean }) => void;
      destroy: () => void;
    } | null = null;

    async function boot() {
      const { default: Lenis } = await import("lenis");
      if (cancelled) return;

      lenis = new Lenis({
        wrapper: wrapperElement,
        content: contentElement,
        duration: 0.9,
        easing: (t: number) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        wheelMultiplier: 1,
        touchMultiplier: 1.4,
        // Touch devices already have momentum scrolling; doubling it feels wrong.
        syncTouch: false,
      });

      lenis.scrollTo(0, { immediate: true });

      const raf = (time: number) => {
        lenis?.raf(time);
        frame = requestAnimationFrame(raf);
      };
      frame = requestAnimationFrame(raf);
    }

    void boot();

    return () => {
      cancelled = true;
      if (frame) cancelAnimationFrame(frame);
      lenis?.destroy();
    };
  }, [pathname]);

  return null;
}
