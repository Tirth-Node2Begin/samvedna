"use client";

import { HERO_WASH, PAGE_H1 } from "@/lib/tokens";
import { cn } from "@/lib/format";

/**
 * `PageHero` — the standard page top, and the app's hero signature.
 *
 * The wash (`from-white via-indigo-50/80 to-violet-50`) plus the two blurred
 * orbs at exactly these offsets is one system. If you change it here, it changes
 * everywhere — that is the point of having a single component.
 *
 * `step` prints the flow step this screen implements, which keeps the built CMS
 * legible against the approved page-flow document.
 */
export function PageHeader({
  title,
  description,
  eyebrow,
  eyebrowIcon: EyebrowIcon,
  step,
  actions,
  className,
}: {
  title: string;
  description?: string;
  eyebrow?: string;
  eyebrowIcon?: React.ComponentType<{ className?: string }>;
  step?: { number: number | string; label: string };
  actions?: React.ReactNode;
  className?: string;
}) {
  const badgeText = step ? `Step ${step.number} · ${step.label}` : eyebrow;

  return (
    <div
      className={cn(
        "relative overflow-hidden rounded-3xl border border-slate-200/70 px-6 py-6 shadow-sm sm:px-8",
        HERO_WASH,
        className
      )}
    >
      {/* Decorative orbs — pointer-events-none so they can never eat a click. */}
      <div
        className="pointer-events-none absolute -right-8 -top-20 size-56 rounded-full bg-indigo-200/40 blur-3xl motion-safe:animate-orb-drift"
        aria-hidden
      />
      <div
        className="pointer-events-none absolute -bottom-24 left-1/4 size-56 rounded-full bg-violet-200/30 blur-3xl motion-safe:animate-orb-drift-slow"
        aria-hidden
      />

      <div className="relative flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
        <div className="min-w-0">
          {badgeText && (
            <span className="inline-flex items-center gap-1.5 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">
              {EyebrowIcon && <EyebrowIcon className="size-3" />}
              {badgeText}
            </span>
          )}
          <h1 className={cn(badgeText && "mt-2.5", PAGE_H1)}>{title}</h1>
          {description && (
            <p className="mt-1.5 max-w-2xl text-sm font-medium text-slate-500">{description}</p>
          )}
        </div>

        {actions && <div className="flex shrink-0 flex-wrap items-center gap-3">{actions}</div>}
      </div>
    </div>
  );
}

/**
 * The standard page wrapper. `space-y-6` is the vertical rhythm between
 * sections; keep it consistent within a page.
 */
export function PageShell({
  children,
  wide = false,
}: {
  children: React.ReactNode;
  wide?: boolean;
}) {
  return (
    <div
      className={cn(
        "mx-auto w-full space-y-6 p-4 pb-10 sm:p-6 lg:p-8",
        wide ? "max-w-[1600px]" : "max-w-[1500px]"
      )}
    >
      {children}
    </div>
  );
}
