import type { Config } from "tailwindcss";

/**
 * Design DNA (see `samvedna-cms/updated style.md`):
 * indigo + violet accents on a hueless white-grey canvas, navy ink, DM Sans,
 * heavy weights, generous radii, whisper-light shadows, floating chrome.
 * Light mode only.
 *
 * Neutrals are `slate`, brand is `indigo`. `gray` / `blue` / `red` / `green`
 * are deliberately absent from this config and must not appear in new code —
 * their modern twins are slate / indigo / rose / emerald.
 */
const config: Config = {
  content: [
    "./app/**/*.{ts,tsx}",
    "./components/**/*.{ts,tsx}",
    "./lib/**/*.{ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        /** The heading colour. `text-navy` is the live one; `bg-navy` is rare. */
        navy: "#1E3A5F",
        primary: {
          indigo: "#4F46E5",
          violet: "#7C3AED",
        },
        /** Chrome surfaces — the only tinted surfaces in the shell. */
        chrome: {
          DEFAULT: "var(--n2b-chrome)",
          hover: "var(--n2b-chrome-hover)",
          active: "var(--n2b-chrome-active)",
        },
        background: "var(--background)",
        foreground: "var(--foreground)",
      },
      fontFamily: {
        sans: [
          "var(--font-dm-sans)",
          "ui-sans-serif",
          "system-ui",
          "-apple-system",
          "Segoe UI",
          "Arial",
          "sans-serif",
        ],
      },
      boxShadow: {
        /** The canonical two-layer chrome lift: rail, bar, glass cards, hero. */
        chrome: "0 1px 2px rgba(15,23,42,0.04), 0 12px 40px -16px rgba(15,23,42,0.18)",
        "chrome-lg": "0 1px 2px rgba(15,23,42,0.05), 0 24px 60px -20px rgba(15,23,42,0.28)",
        "chrome-hover": "0 1px 2px rgba(15,23,42,0.05), 0 20px 52px -18px rgba(15,23,42,0.26)",
        /** Coloured shadow — the primary CTA only. */
        brand: "0 10px 24px -10px rgba(79,70,229,0.45)",
      },
      borderRadius: {
        chrome: "26px",
        bar: "20px",
      },
      maxWidth: {
        shell: "1760px",
        page: "1500px",
        wide: "1600px",
      },
      transitionTimingFunction: {
        chrome: "cubic-bezier(0.32, 0.72, 0, 1)",
        entrance: "cubic-bezier(0.22, 1, 0.36, 1)",
        overlay: "cubic-bezier(0.16, 1, 0.3, 1)",
        exit: "cubic-bezier(0.4, 0, 1, 1)",
      },
      keyframes: {
        "tab-in": {
          from: { opacity: "0", transform: "translateY(6px)" },
          to: { opacity: "1", transform: "none" },
        },
        "panel-in": {
          from: { opacity: "0", transform: "translateY(12px) scale(0.98)" },
          to: { opacity: "1", transform: "none" },
        },
        "backdrop-in": { from: { opacity: "0" }, to: { opacity: "1" } },
        "pop-in": {
          from: { opacity: "0", transform: "scale(0.96)" },
          to: { opacity: "1", transform: "none" },
        },
        "orb-drift": {
          "0%, 100%": { transform: "translate3d(0,0,0) scale(1)" },
          "50%": { transform: "translate3d(14px,-18px,0) scale(1.06)" },
        },
        "online-pulse": {
          "0%, 100%": { opacity: "1", transform: "scale(1)" },
          "50%": { opacity: "0.65", transform: "scale(0.88)" },
        },
      },
      animation: {
        "tab-in": "tab-in 240ms cubic-bezier(0.22,1,0.36,1)",
        "panel-in": "panel-in 240ms cubic-bezier(0.16,1,0.3,1)",
        "backdrop-in": "backdrop-in 180ms ease-out",
        "pop-in": "pop-in 140ms cubic-bezier(0.16,1,0.3,1)",
        "orb-drift": "orb-drift 26s ease-in-out infinite",
        "orb-drift-slow": "orb-drift 34s ease-in-out infinite reverse",
        "online-pulse": "online-pulse 2.4s ease-in-out infinite",
      },
    },
  },
};

export default config;
