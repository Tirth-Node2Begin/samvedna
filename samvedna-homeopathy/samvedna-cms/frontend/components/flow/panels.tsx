"use client";

import { motion } from "framer-motion";
import { CheckCircle2, Circle, Clock } from "lucide-react";
import { Badge, ProgressBar, toneForStatus } from "@/components/ui/primitives";
import { cn, formatDate, formatDateTime, statusLabel } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import { EYEBROW } from "@/lib/tokens";
import type { EscalationStep, ProgressArea, Signoff } from "@/types";

/* -------------------------------------------------------------- Sign-off -- */

/** The sign-off strip. A pending row is what blocks a review from closing. */
export function SignoffList({ signoffs }: { signoffs: Signoff[] }) {
  if (!signoffs.length) {
    return (
      <p className="text-[12.5px] font-medium text-slate-500">
        No sign-offs required on this plan.
      </p>
    );
  }

  return (
    <div className="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
      {signoffs.map((signoff) => (
        <div
          key={signoff.id}
          className={cn(
            "rounded-xl border px-3.5 py-3",
            signoff.status === "signed"
              ? "border-emerald-200/70 bg-emerald-50/60"
              : "border-slate-200/70 bg-white"
          )}
        >
          <p className="text-[13px] font-bold text-navy">{signoff.userName}</p>
          <p className="text-[11.5px] font-medium text-slate-500">{signoff.roleLabel}</p>
          <div className="mt-2 flex flex-wrap items-center gap-2">
            <Badge tone={toneForStatus(signoff.status)}>
              {signoff.status === "signed" ? "Signed off" : "Pending sign-off"}
            </Badge>
            {signoff.signedAt && (
              <span className="text-[10.5px] font-medium text-slate-400">
                {formatDateTime(signoff.signedAt)}
              </span>
            )}
          </div>
          {signoff.comment && (
            <p className="mt-2 text-[11.5px] font-medium italic text-slate-500">
              &ldquo;{signoff.comment}&rdquo;
            </p>
          )}
        </div>
      ))}
    </div>
  );
}

/* ------------------------------------------------------ Escalation chain -- */

/** The Senior → Founder approval chain. */
export function EscalationChain({ steps }: { steps: EscalationStep[] }) {
  return (
    <motion.ul variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-2">
      {steps.map((step) => {
        const dormant = step.status === "standby";
        return (
          <motion.li
            key={step.id}
            variants={staggerItem}
            className={cn(
              "flex items-center justify-between gap-3 rounded-xl border px-3.5 py-3",
              dormant ? "border-slate-200/70 bg-slate-50 opacity-70" : "border-slate-200/70 bg-white"
            )}
          >
            <div className="min-w-0">
              <p className="truncate text-[13px] font-bold text-navy">{step.userName}</p>
              <p className="text-[11.5px] font-medium text-slate-500">
                {step.roleLabel}
                {step.role === "founder" && " — if unresolved by Senior"}
              </p>
            </div>
            <Badge tone={toneForStatus(step.status)}>{statusLabel(step.status)}</Badge>
          </motion.li>
        );
      })}
    </motion.ul>
  );
}

/* ---------------------------------------------------------- Area-wise UI -- */

/** Area-wise progress, charted against the markers captured at intake. */
export function AreaBars({ areas }: { areas: ProgressArea[] }) {
  if (!areas.length) {
    return (
      <p className="text-[12.5px] font-medium text-slate-500">
        No baseline markers recorded for this case.
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-3.5">
      {areas.map((area, index) => (
        <div key={area.key}>
          <div className="mb-1.5 flex items-baseline justify-between gap-3">
            <span className="text-[12.5px] font-semibold text-slate-700">{area.label}</span>
            <span className="flex items-baseline gap-2.5">
              <span className="text-[10.5px] font-medium tabular-nums text-slate-400">
                baseline {area.baseline}
              </span>
              <span
                className={cn(
                  "text-[11.5px] font-bold",
                  area.trend === "improving"
                    ? "text-emerald-600"
                    : area.trend === "declining"
                      ? "text-rose-600"
                      : "text-amber-600"
                )}
              >
                {area.trend === "improving"
                  ? "Improving"
                  : area.trend === "declining"
                    ? "Declining"
                    : "Stable"}
              </span>
            </span>
          </div>
          <ProgressBar
            percent={area.score}
            tone={area.trend === "improving" ? "emerald" : area.trend === "declining" ? "rose" : "amber"}
            delay={index * 0.04}
          />
        </div>
      ))}
    </div>
  );
}

/* ------------------------------------------------------------- Cycle bar -- */

export function CycleOverview({
  cycles,
}: {
  cycles: {
    cycle: number;
    label: string;
    percent: number;
    isCurrent: boolean;
    done: number;
    events: number;
  }[];
}) {
  return (
    <div className="flex flex-col gap-3">
      {cycles.map((cycle, index) => (
        <div key={cycle.cycle} className="flex items-center gap-3">
          <span
            className={cn(
              "w-[76px] shrink-0 text-[12px]",
              cycle.isCurrent ? "font-bold text-navy" : "font-medium text-slate-500"
            )}
          >
            Cycle {cycle.cycle}
            {cycle.isCurrent && <span className="ml-1 text-indigo-500">•</span>}
          </span>
          <ProgressBar percent={cycle.percent} delay={index * 0.05} className="flex-1" />
          <span className="hidden w-[124px] shrink-0 text-right text-[11px] font-medium text-slate-400 sm:block">
            {cycle.label}
          </span>
          <span className="w-[46px] shrink-0 text-right text-[11px] font-bold tabular-nums text-slate-500">
            {cycle.done}/{cycle.events}
          </span>
        </div>
      ))}
    </div>
  );
}

/* ------------------------------------------------------------- Goal list -- */

export function GoalList({
  goals,
  onToggle,
}: {
  goals: { id?: number; title: string; metric: string; status: string }[];
  onToggle?: (index: number) => void;
}) {
  if (!goals.length) {
    return (
      <p className="text-[12.5px] font-medium text-slate-500">No goals recorded for this cycle.</p>
    );
  }

  return (
    <ul className="flex flex-col gap-1.5">
      {goals.map((goal, index) => {
        const done = goal.status === "achieved";
        const Wrapper = onToggle ? "button" : "div";
        return (
          <li key={goal.id ?? index}>
            <Wrapper
              {...(onToggle ? { type: "button" as const, onClick: () => onToggle(index) } : {})}
              className={cn(
                "flex w-full items-start gap-2.5 rounded-xl px-2 py-2 text-left",
                onToggle && "transition-colors hover:bg-slate-50"
              )}
            >
              {done ? (
                <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-emerald-500" />
              ) : goal.status === "progressing" ? (
                <Clock className="mt-0.5 size-4 shrink-0 text-amber-500" />
              ) : (
                <Circle className="mt-0.5 size-4 shrink-0 text-slate-300" />
              )}
              <span className="min-w-0 flex-1">
                <span className="block text-[13px] font-semibold leading-snug text-slate-700">
                  {goal.title}
                </span>
                {goal.metric && (
                  <span className="block text-[11.5px] font-medium text-slate-400">{goal.metric}</span>
                )}
              </span>
              <Badge tone={toneForStatus(goal.status)}>{statusLabel(goal.status)}</Badge>
            </Wrapper>
          </li>
        );
      })}
    </ul>
  );
}

/* --------------------------------------------------------- Detail fields -- */

/** Read-only label/value pairs — the shape used all over the flow. */
export function DetailGrid({
  items,
  cols = 2,
}: {
  items: {
    label: string;
    value: React.ReactNode;
    tone?: "default" | "locked" | "success" | "warn";
  }[];
  cols?: 2 | 3 | 4;
}) {
  const grid = {
    2: "sm:grid-cols-2",
    3: "sm:grid-cols-2 lg:grid-cols-3",
    4: "sm:grid-cols-2 lg:grid-cols-4",
  }[cols];

  return (
    <div className={cn("grid grid-cols-1 gap-3.5", grid)}>
      {items.map((item) => (
        <div key={item.label} className="flex flex-col gap-1.5">
          <span className="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
            {item.label}
          </span>
          <span
            className={cn(
              "flex min-h-10 items-center rounded-xl border px-3.5 py-2 text-[13px] font-semibold",
              item.tone === "locked" && "border-slate-200 bg-slate-50 text-slate-500",
              item.tone === "success" && "border-emerald-200/70 bg-emerald-50 text-emerald-700",
              item.tone === "warn" && "border-amber-200/70 bg-amber-50 text-amber-700",
              (!item.tone || item.tone === "default") && "border-slate-200 bg-white text-slate-700"
            )}
          >
            {item.value || "—"}
          </span>
        </div>
      ))}
    </div>
  );
}

/** "n of m complete" strip, shown above a locked action. */
export function CompletionMeter({ percent, label }: { percent: number; label: string }) {
  return (
    <div>
      <div className="mb-2 flex items-baseline justify-between gap-3">
        <span className={EYEBROW}>Completeness</span>
        <span
          className={cn(
            "text-[12px] font-black tabular-nums",
            percent === 100 ? "text-emerald-600" : "text-amber-600"
          )}
        >
          {percent}%
        </span>
      </div>
      <ProgressBar percent={percent} tone={percent === 100 ? "emerald" : "amber"} />
      <p className="mt-1.5 text-[11px] font-medium text-slate-400">{label}</p>
    </div>
  );
}

export { formatDate };
