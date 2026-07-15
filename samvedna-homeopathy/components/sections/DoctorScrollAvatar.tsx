"use client";

import { useEffect, useState } from "react";
import { createPortal } from "react-dom";
import Image from "next/image";
import { Award } from "lucide-react";
import useReducedMotion from "@/hooks/useReducedMotion";

type AvatarFrame = {
  left: number;
  top: number;
  width: number;
  height: number;
  progress: number;
  visible: boolean;
};

const clamp = (value: number, min = 0, max = 1): number => Math.min(max, Math.max(min, value));
const lerp = (start: number, end: number, progress: number): number => start + (end - start) * progress;

/* Shared avatar visuals. `progress` drives the hero (0) -> doctor-section (1) styling. */
function AvatarInner({ progress }: { progress: number }) {
  const badgeOpacity = clamp(1 - progress * 2.4);
  const glowOpacity = clamp((progress - 0.35) / 0.65) * 0.85;
  const labelRadius = lerp(999, 16, progress);
  const labelBottom = lerp(-16, -24, progress);
  const labelPaddingY = lerp(8, 16, progress);
  const labelPaddingX = lerp(20, 32, progress);
  const nameSize = lerp(14, 20, progress);
  const titleSize = lerp(10, 12, progress);

  return (
    <div className="relative h-full w-full">
      <div
        className="absolute inset-0 -z-10 rounded-full bg-cyan-100 blur-[80px]"
        style={{ opacity: glowOpacity }}
      />

      <div
        className="absolute right-1 -top-2 z-10 flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3 py-2 shadow-lg backdrop-blur-xl sm:-right-10"
        style={{
          opacity: badgeOpacity,
          transform: `scale(${lerp(1, 0.85, progress)})`,
        }}
      >
        <div className="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-400/20">
          <Award className="h-3 w-3 text-amber-400" />
        </div>
        <div>
          <p className="text-[11px] font-bold leading-tight text-white">20+ Years</p>
          <p className="text-[10px] leading-tight text-white/55">Experience</p>
        </div>
      </div>

      <Image
        src="/images/dr-krunal-kosada-removebg-preview.png"
        alt=""
        fill
        priority
        sizes="(min-width: 1024px) 320px, 260px"
        className="object-contain drop-shadow-[0_20px_40px_rgba(0,0,0,0.35)]"
      />

      <div
        className="absolute left-1/2 flex -translate-x-1/2 flex-col items-center whitespace-nowrap border border-white/20 bg-[#0c1e35]/90 shadow-2xl backdrop-blur-md"
        style={{
          bottom: labelBottom,
          borderRadius: labelRadius,
          padding: `${labelPaddingY}px ${labelPaddingX}px`,
        }}
      >
        <p className="font-black leading-none text-white" style={{ fontSize: nameSize }}>
          Dr. Krunal Kosada
        </p>
        <p className="mt-1 font-bold uppercase leading-none text-cyan-300" style={{ fontSize: titleSize }}>
          Founder &amp; Chief Consultant
        </p>
      </div>
    </div>
  );
}

export default function DoctorScrollAvatar() {
  const prefersReducedMotion = useReducedMotion();
  const [isDesktop, setIsDesktop] = useState<boolean | null>(null);
  const [frame, setFrame] = useState<AvatarFrame | null>(null);
  const [mobileTarget, setMobileTarget] = useState<HTMLElement | null>(null);

  /* Track the desktop breakpoint (matchMedia is client-only). */
  useEffect(() => {
    const mq = window.matchMedia("(min-width: 1024px)");
    const update = (): void => setIsDesktop(mq.matches);
    update();
    mq.addEventListener("change", update);
    return () => mq.removeEventListener("change", update);
  }, []);

  /* Desktop: the avatar floats (fixed) from the hero to the doctor section, scroll-linked. */
  useEffect(() => {
    if (isDesktop !== true) {
      setFrame(null);
      return;
    }

    const origin = document.querySelector<HTMLElement>("[data-doctor-avatar-origin]");
    const target = document.querySelector<HTMLElement>("[data-doctor-avatar-target]");
    const targetSection = target?.closest<HTMLElement>("section");

    if (!origin || !target || !targetSection) return;

    let frameId = 0;

    const read = (): void => {
      const scrollY = window.scrollY;
      const scrollX = window.scrollX;
      const viewportHeight = window.innerHeight;

      const originRect = origin.getBoundingClientRect();
      const targetRect = target.getBoundingClientRect();
      const sectionRect = targetSection.getBoundingClientRect();

      const originDoc = {
        left: originRect.left + scrollX,
        top: originRect.top + scrollY,
        width: originRect.width,
        height: originRect.height,
      };

      const targetDoc = {
        left: targetRect.left + scrollX,
        top: targetRect.top + scrollY,
        width: targetRect.width,
        height: targetRect.height,
      };

      const transitionEnd = Math.max(1, sectionRect.top + scrollY);
      const rawProgress = clamp(scrollY / transitionEnd);
      const progress = prefersReducedMotion ? (rawProgress >= 1 ? 1 : 0) : rawProgress;

      setFrame({
        left: lerp(originDoc.left, targetDoc.left, progress) - scrollX,
        top: lerp(originDoc.top, targetDoc.top, progress) - scrollY,
        width: lerp(originDoc.width, targetDoc.width, progress),
        height: lerp(originDoc.height, targetDoc.height, progress),
        progress,
        visible: targetRect.bottom > -viewportHeight * 0.35 && originRect.top < viewportHeight * 1.25,
      });
    };

    const scheduleRead = (): void => {
      window.cancelAnimationFrame(frameId);
      frameId = window.requestAnimationFrame(read);
    };

    const resizeObserver = new ResizeObserver(scheduleRead);
    resizeObserver.observe(origin);
    resizeObserver.observe(target);
    resizeObserver.observe(targetSection);

    scheduleRead();
    window.addEventListener("scroll", scheduleRead, { passive: true });
    window.addEventListener("resize", scheduleRead);

    return () => {
      window.cancelAnimationFrame(frameId);
      resizeObserver.disconnect();
      window.removeEventListener("scroll", scheduleRead);
      window.removeEventListener("resize", scheduleRead);
    };
  }, [isDesktop, prefersReducedMotion]);

  /* Mobile: no scroll-linked motion (it stutters against the address-bar resize) —
     park the avatar statically inside the doctor intro section instead. */
  useEffect(() => {
    if (isDesktop === false) {
      setMobileTarget(document.querySelector<HTMLElement>("[data-doctor-avatar-target]"));
    } else {
      setMobileTarget(null);
    }
  }, [isDesktop]);

  if (isDesktop === null) return null;

  if (!isDesktop) {
    if (!mobileTarget) return null;
    return createPortal(
      <div aria-hidden="true" data-doctor-scroll-avatar className="pointer-events-none absolute inset-0 z-[1]">
        <AvatarInner progress={1} />
      </div>,
      mobileTarget,
    );
  }

  if (!frame || !frame.visible) return null;

  return (
    <div
      aria-hidden="true"
      data-doctor-scroll-avatar
      className="pointer-events-none fixed z-[35]"
      style={{
        left: frame.left,
        top: frame.top,
        width: frame.width,
        height: frame.height,
      }}
    >
      <AvatarInner progress={frame.progress} />
    </div>
  );
}
