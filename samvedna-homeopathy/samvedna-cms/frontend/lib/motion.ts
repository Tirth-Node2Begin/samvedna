import type { Transition, Variants } from "framer-motion";

/**
 * Motion is subtle, short and interruptible. Three easing families, by role:
 *
 *   shell / chrome      cubic-bezier(0.32, 0.72, 0, 1)   260ms
 *   entrances           cubic-bezier(0.22, 1, 0.36, 1)   180–280ms
 *   popovers / overlays cubic-bezier(0.16, 1, 0.3, 1)    140–300ms
 *   exits               cubic-bezier(0.4, 0, 1, 1)       140–170ms
 *
 * No hover lift, no scale, no coloured glow — hover recolours, or deepens the
 * same neutral shadow. `hover:scale-[1.03]` is the primary CTA's alone.
 */

export const EASE_ENTRANCE: Transition["ease"] = [0.22, 1, 0.36, 1];
export const EASE_OVERLAY: Transition["ease"] = [0.16, 1, 0.3, 1];
export const EASE_CHROME: Transition["ease"] = [0.32, 0.72, 0, 1];

export const pageTransition: Variants = {
  hidden: { opacity: 0, y: 6 },
  show: { opacity: 1, y: 0, transition: { duration: 0.24, ease: EASE_ENTRANCE } },
};

/** Parent for a list whose children fade up one after another. */
export const stagger: Variants = {
  hidden: {},
  show: { transition: { staggerChildren: 0.04, delayChildren: 0.03 } },
};

export const staggerItem: Variants = {
  hidden: { opacity: 0, y: 8 },
  show: { opacity: 1, y: 0, transition: { duration: 0.28, ease: EASE_ENTRANCE } },
};

/** Timeline entries draw in along the track. */
export const timelineItem: Variants = {
  hidden: { opacity: 0, x: -8 },
  show: { opacity: 1, x: 0, transition: { duration: 0.26, ease: EASE_ENTRANCE } },
};

export const modalBackdrop: Variants = {
  hidden: { opacity: 0 },
  show: { opacity: 1, transition: { duration: 0.18, ease: "easeOut" } },
  exit: { opacity: 0, transition: { duration: 0.16, ease: [0.4, 0, 1, 1] } },
};

export const modalPanel: Variants = {
  hidden: { opacity: 0, y: 12, scale: 0.98 },
  show: { opacity: 1, y: 0, scale: 1, transition: { duration: 0.24, ease: EASE_OVERLAY } },
  exit: { opacity: 0, y: 8, scale: 0.985, transition: { duration: 0.16, ease: [0.4, 0, 1, 1] } },
};

/** Bar fills animate their width from zero on mount. */
export function barFill(percent: number, delay = 0) {
  return {
    initial: { width: "0%" },
    animate: { width: `${Math.max(0, Math.min(100, percent))}%` },
    transition: { duration: 0.7, ease: EASE_ENTRANCE, delay },
  };
}

/** The sliding tab highlight — a layoutId element, so it travels rather than pops. */
export const tabHighlightSpring: Transition = {
  type: "spring",
  stiffness: 500,
  damping: 34,
  mass: 0.9,
};
