"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Loader2, Lock, Info, Inbox } from "lucide-react";
import { cn, initials } from "@/lib/format";
import { barFill } from "@/lib/motion";
import {
  CARD,
  EYEBROW,
  TONE_BAR,
  TONE_GLYPH,
  TONE_PANEL,
  TONE_SOFT,
  TONE_TILE,
  type Tone,
} from "@/lib/tokens";

export type { Tone };

/* ---------------------------------------------------------------- Button -- */

type ButtonVariant = "primary" | "secondary" | "ghost" | "danger" | "subtle";
type ButtonSize = "sm" | "md" | "lg";

const buttonBase =
  "inline-flex items-center justify-center gap-2 font-bold transition-colors " +
  "disabled:cursor-not-allowed disabled:opacity-55 whitespace-nowrap " +
  "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/10";

const buttonVariants: Record<ButtonVariant, string> = {
  // The one gradient CTA. `hover:scale-[1.03]` is allowed here and nowhere else.
  primary:
    "rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25 " +
    "transition-transform hover:scale-[1.02] active:scale-[0.99] disabled:hover:scale-100 motion-reduce:transition-none",
  secondary:
    "rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 shadow-sm hover:bg-slate-50",
  ghost: "rounded-lg font-medium text-slate-600 hover:bg-slate-100 hover:text-navy",
  danger: "rounded-xl bg-rose-600 text-white shadow-sm hover:bg-rose-700",
  subtle: "rounded-xl bg-indigo-50 font-bold text-indigo-700 hover:bg-indigo-100",
};

const buttonSizes: Record<ButtonSize, string> = {
  sm: "h-8 px-3 text-xs",
  md: "h-10 px-4 text-sm",
  lg: "h-11 px-5 text-sm",
};

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  loading?: boolean;
}

export function Button({
  variant = "primary",
  size = "md",
  loading = false,
  className,
  children,
  disabled,
  ...props
}: ButtonProps) {
  return (
    <button
      className={cn(buttonBase, buttonVariants[variant], buttonSizes[size], className)}
      disabled={disabled || loading}
      {...props}
    >
      {loading && <Loader2 className="size-3.5 animate-spin" aria-hidden />}
      {children}
    </button>
  );
}

export function LinkButton({
  href,
  variant = "secondary",
  size = "md",
  className,
  children,
}: {
  href: string;
  variant?: ButtonVariant;
  size?: ButtonSize;
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <Link href={href} className={cn(buttonBase, buttonVariants[variant], buttonSizes[size], className)}>
      {children}
    </Link>
  );
}

/** Circular chrome action button — the header/toolbar icon control. */
export function IconButton({
  label,
  className,
  children,
  ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement> & { label: string }) {
  return (
    <button
      type="button"
      aria-label={label}
      title={label}
      className={cn(
        "grid size-10 shrink-0 place-items-center rounded-full border border-slate-200 bg-white",
        "text-slate-500 transition-colors hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600",
        "disabled:cursor-not-allowed disabled:opacity-50",
        className
      )}
      {...props}
    >
      {children}
    </button>
  );
}

/* ------------------------------------------------------------------ Card -- */

export function Card({ className, children }: { className?: string; children: React.ReactNode }) {
  return <section className={cn(CARD, className)}>{children}</section>;
}

export function CardHeader({
  title,
  subtitle,
  badge,
  actions,
}: {
  title: React.ReactNode;
  subtitle?: React.ReactNode;
  badge?: React.ReactNode;
  actions?: React.ReactNode;
}) {
  return (
    <header className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
      <div className="min-w-0">
        <h2 className="truncate text-base font-bold text-navy">{title}</h2>
        {subtitle && <p className="mt-0.5 text-[12.5px] font-medium text-slate-500">{subtitle}</p>}
      </div>
      <div className="flex shrink-0 flex-wrap items-center gap-2">
        {badge}
        {actions}
      </div>
    </header>
  );
}

export function CardBody({ className, children }: { className?: string; children: React.ReactNode }) {
  return <div className={cn("p-5", className)}>{children}</div>;
}

/* ---------------------------------------------------------------- Labels -- */

export function SectionLabel({ children, hint }: { children: React.ReactNode; hint?: string }) {
  return (
    <div className="mb-3 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
      <h3 className={EYEBROW}>{children}</h3>
      {hint && <span className="text-[11px] font-medium normal-case tracking-normal text-slate-400">{hint}</span>}
    </div>
  );
}

export function Divider({ className }: { className?: string }) {
  return <div className={cn("h-px bg-slate-100", className)} />;
}

/* ----------------------------------------------------------------- Badge -- */

export function Badge({
  tone = "slate",
  className,
  children,
  ...props
}: React.HTMLAttributes<HTMLSpanElement> & { tone?: Tone }) {
  return (
    <span
      className={cn(
        "inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold",
        TONE_SOFT[tone],
        className
      )}
      {...props}
    >
      {children}
    </span>
  );
}

/** Map any API status string onto a tone, so badges stay consistent app-wide. */
export function toneForStatus(status: string): Tone {
  switch (status) {
    case "active":
    case "done":
    case "closed":
    case "signed":
    case "resolved":
    case "achieved":
    case "improving":
    case "approved":
      return "emerald";
    case "upcoming":
    case "awaiting_signoff":
    case "in_progress":
    case "pending":
    case "progressing":
    case "reviewing":
    case "on_hold":
    case "stable":
      return "amber";
    case "missed":
    case "declining":
    case "open":
      return "rose";
    case "rescheduled":
    case "draft":
      return "indigo";
    default:
      return "slate";
  }
}

/* ----------------------------------------------------------- Stat / KPI --- */

/**
 * The standard KPI tile. White card, gradient icon tile, eyebrow label, and a
 * `font-black tabular-nums` value in navy.
 *
 * No hover lift — hover recolours the border only.
 */
export function StatCard({
  label,
  value,
  hint,
  tone = "indigo",
  icon: Icon,
  href,
}: {
  label: string;
  value: React.ReactNode;
  hint?: string;
  tone?: Tone;
  icon?: React.ComponentType<{ className?: string }>;
  href?: string;
}) {
  const body = (
    <>
      <div className="flex items-start justify-between gap-3">
        <span className={EYEBROW}>{label}</span>
        {Icon && (
          <span
            className={cn(
              "grid size-9 shrink-0 place-items-center rounded-xl text-white shadow-sm",
              TONE_TILE[tone]
            )}
            aria-hidden
          >
            <Icon className="size-[17px]" />
          </span>
        )}
      </div>
      <p className="mt-3 text-2xl font-black tabular-nums leading-none tracking-tight text-navy">
        {value}
      </p>
      {hint && <p className="mt-2 text-[11.5px] font-medium text-slate-400">{hint}</p>}
    </>
  );

  const className =
    "flex h-full flex-col rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition-colors hover:border-indigo-200";

  return href ? (
    <Link href={href} className={className}>
      {body}
    </Link>
  ) : (
    <div className={cn(className, "hover:border-slate-200/70")}>{body}</div>
  );
}

/** Compact metric — three-up rows inside a card. */
export function Metric({
  value,
  label,
  tone,
  hint,
}: {
  value: React.ReactNode;
  label: string;
  tone?: Tone;
  hint?: string;
}) {
  const valueColor = tone ? TONE_GLYPH[tone].replace("-500", "-600") : "text-navy";

  return (
    <div className="rounded-xl border border-slate-200/70 bg-slate-50/60 px-3.5 py-3 text-center">
      <div className={cn("text-xl font-black tabular-nums leading-tight", valueColor)}>{value}</div>
      <div className="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
        {label}
      </div>
      {hint && <div className="mt-0.5 text-[10.5px] font-medium text-slate-400">{hint}</div>}
    </div>
  );
}

/* ----------------------------------------------------------- ProgressBar -- */

export function ProgressBar({
  percent,
  tone = "indigo",
  delay = 0,
  className,
}: {
  percent: number;
  tone?: Tone;
  delay?: number;
  className?: string;
}) {
  return (
    <div className={cn("h-1.5 w-full overflow-hidden rounded-full bg-slate-100", className)}>
      <motion.div className={cn("h-full rounded-full", TONE_BAR[tone])} {...barFill(percent, delay)} />
    </div>
  );
}

/* ------------------------------------------------------------ Lock / note -- */

/** A rule the user cannot argue with. Slate by default, amber when blocking. */
export function LockRow({ children, tone = "slate" }: { children: React.ReactNode; tone?: "slate" | "amber" }) {
  return (
    <div
      className={cn(
        "flex items-start gap-2.5 rounded-xl border px-3.5 py-3 text-[12.5px] font-medium",
        tone === "amber" ? TONE_PANEL.amber : "border-slate-200/70 bg-slate-50 text-slate-600"
      )}
    >
      <Lock className="mt-0.5 size-3.5 shrink-0" aria-hidden />
      <span>{children}</span>
    </div>
  );
}

export function InfoNote({ children, tone = "indigo" }: { children: React.ReactNode; tone?: Tone }) {
  return (
    <div
      className={cn(
        "flex items-start gap-2.5 rounded-xl border px-3.5 py-3 text-[12.5px] font-medium",
        TONE_PANEL[tone]
      )}
    >
      <Info className="mt-0.5 size-3.5 shrink-0" aria-hidden />
      <span>{children}</span>
    </div>
  );
}

/* ------------------------------------------------------------ Empty/load -- */

export function EmptyState({
  icon: Icon = Inbox,
  title,
  description,
  action,
}: {
  icon?: React.ComponentType<{ className?: string }>;
  title: string;
  description?: string;
  action?: React.ReactNode;
}) {
  return (
    <div className="m-5 flex min-h-40 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 px-6 py-10 text-center">
      <Icon className="size-8 text-slate-300" aria-hidden />
      <p className="mt-3 text-sm font-semibold text-slate-500">{title}</p>
      {description && (
        <p className="mt-1 max-w-sm text-[12.5px] font-medium text-slate-400">{description}</p>
      )}
      {action && <div className="mt-4">{action}</div>}
    </div>
  );
}

export function Spinner({ label = "Loading" }: { label?: string }) {
  return (
    <div className="flex items-center justify-center gap-2 py-16 text-[12.5px] font-semibold text-slate-400">
      <Loader2 className="size-4 animate-spin" aria-hidden />
      {label}…
    </div>
  );
}

export function ErrorNote({ message, retry }: { message: string; retry?: () => void }) {
  return (
    <div
      role="alert"
      className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4"
    >
      <p className="text-[12.5px] font-semibold text-rose-700">{message}</p>
      {retry && (
        <Button variant="secondary" size="sm" onClick={retry}>
          Try again
        </Button>
      )}
    </div>
  );
}

/** Skeleton block for loading states. */
export function Skeleton({ className }: { className?: string }) {
  return <div className={cn("animate-pulse rounded-md bg-slate-100", className)} />;
}

/* ---------------------------------------------------------------- Avatar -- */

export function Avatar({
  name,
  className,
  presence = false,
}: {
  name: string;
  className?: string;
  presence?: boolean;
}) {
  return (
    <span className="relative inline-flex shrink-0">
      <span
        className={cn(
          "grid size-8 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-[12px] font-bold text-white",
          className
        )}
        aria-hidden
      >
        {initials(name)}
      </span>
      {presence && (
        <span
          className="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-white bg-emerald-400"
          aria-hidden
        />
      )}
    </span>
  );
}

/** Frosted icon chip — colour lands on the glyph, never on the chip. */
export function IconChip({
  icon: Icon,
  tone = "indigo",
  className,
}: {
  icon: React.ComponentType<{ className?: string; strokeWidth?: number }>;
  tone?: Tone;
  className?: string;
}) {
  return (
    <span
      className={cn(
        "grid size-11 shrink-0 place-items-center rounded-full bg-slate-100/70 ring-1 ring-inset ring-white/80",
        TONE_GLYPH[tone],
        className
      )}
      aria-hidden
    >
      <Icon className="size-[19px]" strokeWidth={1.9} />
    </span>
  );
}
