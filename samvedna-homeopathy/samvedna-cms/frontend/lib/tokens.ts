/**
 * Exported class-string constants.
 *
 * The style guide's rule: when two files must look *identical*, share the class
 * string rather than re-typing it. Everything here is a full static string so
 * Tailwind's JIT can see it — never interpolate a class name.
 */

/* ---------------------------------------------------------------- chrome -- */

/** The canonical two-layer lift. One string, four surfaces. */
export const CHROME_SHADOW =
  "shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_40px_-16px_rgba(15,23,42,0.18)]";

/** Rail hover / pin-expanded — the same neutral, deepened. Never a hue. */
export const CHROME_SHADOW_LG =
  "shadow-[0_1px_2px_rgba(15,23,42,0.05),0_24px_60px_-20px_rgba(15,23,42,0.28)]";

/** Glass-card hover. */
export const CHROME_SHADOW_HOVER =
  "hover:shadow-[0_1px_2px_rgba(15,23,42,0.05),0_20px_52px_-18px_rgba(15,23,42,0.26)]";

/** One curve, one duration, for every shell movement. */
export const CHROME_CURVE =
  "duration-[260ms] ease-[cubic-bezier(0.32,0.72,0,1)] motion-reduce:transition-none";

/** Geometry — three files express this one number; keep them in step. */
export const GUTTER = 18;
export const COLLAPSED_W = 68;
export const EXPANDED_W = 272;
export const BAR_H = 54;

/* ------------------------------------------------------------------ hero -- */

/**
 * The app's hero signature. Duplicated nowhere else — import it.
 * `from-white via-indigo-50/80 to-violet-50` plus two blurred orbs.
 */
export const HERO_WASH = "bg-gradient-to-br from-white via-indigo-50/80 to-violet-50";

export const HERO_PRIMARY_BTN =
  "group/btn inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r " +
  "from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-black text-white " +
  "shadow-lg shadow-indigo-500/25 transition-transform hover:scale-[1.03] " +
  "active:scale-[0.99] disabled:opacity-60 disabled:hover:scale-100 motion-reduce:transition-none";

export const HERO_SECONDARY_BTN =
  "inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white " +
  "px-4 py-2.5 text-sm font-bold text-navy shadow-sm transition-colors hover:bg-slate-50";

/* ----------------------------------------------------------------- glass -- */

const GLASS_BASE =
  "relative isolate overflow-hidden rounded-[26px] border border-white/70 backdrop-blur-xl " +
  "ring-1 ring-slate-900/[0.04] " + CHROME_SHADOW;

/** Clear glass — the KPI cards only, so their numbers carry the row. */
export const GLASS_SURFACE = `${GLASS_BASE} bg-white/75`;

/** Tinted glass — every other card on a glass page. */
export const GLASS_SURFACE_TINTED = `${GLASS_BASE} bg-gradient-to-br from-white/85 via-indigo-50/60 to-indigo-100/55`;

export const GLASS_HOVER = `transition-shadow duration-300 ${CHROME_SHADOW_HOVER}`;

/** Icon chip fill on glass — frosted neutral. Colour lands on the GLYPH. */
export const GLASS_TILE = "bg-slate-100/70 ring-1 ring-inset ring-white/80";

/** Row inside a glass card. */
export const GLASS_ROW =
  "rounded-2xl bg-white/60 ring-1 ring-inset ring-slate-900/[0.045] transition-colors duration-200 hover:bg-white/95";

/** The edge-light every glass card carries along its top. */
export const GLASS_EDGE =
  "pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-white to-transparent";

/* ------------------------------------------------------------------ tone -- */

export type Tone = "indigo" | "emerald" | "amber" | "rose" | "slate" | "violet";

/** Soft badge / panel fills. Full static strings — JIT-safe. */
export const TONE_SOFT: Record<Tone, string> = {
  indigo: "bg-indigo-50 text-indigo-700",
  emerald: "bg-emerald-50 text-emerald-700",
  amber: "bg-amber-50 text-amber-700",
  rose: "bg-rose-50 text-rose-700",
  slate: "bg-slate-100 text-slate-600",
  violet: "bg-violet-50 text-violet-700",
};

/** The same tones as a callout panel — soft fill plus a hairline ring. */
export const TONE_PANEL: Record<Tone, string> = {
  indigo: "border-indigo-200/70 bg-indigo-50 text-indigo-700",
  emerald: "border-emerald-200/70 bg-emerald-50 text-emerald-700",
  amber: "border-amber-200/70 bg-amber-50 text-amber-700",
  rose: "border-rose-200/70 bg-rose-50 text-rose-700",
  slate: "border-slate-200/70 bg-slate-50 text-slate-600",
  violet: "border-violet-200/70 bg-violet-50 text-violet-700",
};

/** Gradient icon tiles for page cards. */
export const TONE_TILE: Record<Tone, string> = {
  indigo: "bg-gradient-to-br from-indigo-500 to-violet-600",
  emerald: "bg-gradient-to-br from-emerald-500 to-teal-600",
  amber: "bg-gradient-to-br from-amber-500 to-orange-600",
  rose: "bg-gradient-to-br from-rose-500 to-pink-600",
  slate: "bg-gradient-to-br from-slate-500 to-slate-700",
  violet: "bg-gradient-to-br from-violet-500 to-purple-600",
};

/** Glyph colour on a frosted chip — colour lands here, never on the chip. */
export const TONE_GLYPH: Record<Tone, string> = {
  indigo: "text-indigo-500",
  emerald: "text-emerald-500",
  amber: "text-amber-500",
  rose: "text-rose-500",
  slate: "text-slate-500",
  violet: "text-violet-500",
};

/** Progress-bar fills. */
export const TONE_BAR: Record<Tone, string> = {
  indigo: "bg-gradient-to-r from-indigo-500 to-violet-500",
  emerald: "bg-emerald-500",
  amber: "bg-amber-500",
  rose: "bg-rose-500",
  slate: "bg-slate-300",
  violet: "bg-violet-500",
};

/* ---------------------------------------------------------------- typo -- */

/** The signature eyebrow. */
export const EYEBROW = "text-[10px] font-black uppercase tracking-[0.2em] text-slate-400";
/** The dashboard's slightly looser variant. */
export const EYEBROW_SOFT = "text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400";
/** Page H1 inside a hero. */
export const PAGE_H1 = "text-2xl font-black tracking-tight text-navy sm:text-3xl";
/** Section / card title. */
export const CARD_TITLE = "text-base font-bold text-navy";
/** Any number that sits in a column or animates. */
export const NUMERIC = "tabular-nums";

/* --------------------------------------------------------------- surface -- */

/** The default card. */
export const CARD = "rounded-2xl border border-slate-200/70 bg-white shadow-sm";
/** Table header row. */
export const TABLE_HEAD =
  "bg-slate-50/70 border-b border-slate-100 text-[11px] font-black uppercase tracking-widest text-slate-400";
/** Row hover — background only, never a lift or a coloured stripe. */
export const TABLE_ROW = "transition-colors hover:bg-slate-50/70";
