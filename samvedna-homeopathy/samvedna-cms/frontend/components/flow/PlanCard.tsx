"use client";

import { Check } from "lucide-react";
import { cn } from "@/lib/format";
import { EYEBROW } from "@/lib/tokens";
import type { Plan } from "@/types";

/** The care-plan tile. Selected state is an indigo tint + ring, never a lift. */
export function PlanCard({
  plan,
  selected,
  onSelect,
  compact = false,
}: {
  plan: Plan;
  selected?: boolean;
  onSelect?: (plan: Plan) => void;
  compact?: boolean;
}) {
  const interactive = Boolean(onSelect);

  return (
    <button
      type="button"
      disabled={!interactive}
      onClick={() => onSelect?.(plan)}
      aria-pressed={interactive ? selected : undefined}
      className={cn(
        "relative flex h-full flex-col rounded-2xl border p-5 text-left shadow-sm transition-colors",
        selected
          ? "border-indigo-300 bg-indigo-50/60 ring-1 ring-indigo-200"
          : "border-slate-200/70 bg-white",
        interactive && !selected && "hover:border-indigo-200 hover:bg-slate-50/60",
        !interactive && "cursor-default"
      )}
    >
      {plan.highlight && (
        <span className="absolute -top-2.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 px-3 py-0.5 text-[10px] font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-indigo-500/25">
          Most preferred
        </span>
      )}

      <div className="flex items-start justify-between gap-2">
        <div className="min-w-0">
          <p className="text-[13px] font-bold text-navy">{plan.name}</p>
          {plan.tagline && (
            <p className="text-[11.5px] font-medium text-slate-500">{plan.tagline}</p>
          )}
        </div>
        {selected && (
          <span className="grid size-5 shrink-0 place-items-center rounded-full bg-indigo-600 text-white">
            <Check className="size-3" strokeWidth={3} />
          </span>
        )}
      </div>

      <p
        className={cn(
          "mt-3 text-[26px] font-black tabular-nums leading-none tracking-tight",
          selected ? "text-indigo-700" : "text-navy"
        )}
      >
        {plan.priceLabel}
      </p>

      <p className="mt-2 text-[11.5px] font-semibold text-slate-500">
        {plan.durationLabel} · {plan.reviewCount} review{plan.reviewCount === 1 ? "" : "s"}
      </p>
      <p className="mt-0.5 text-[11.5px] font-medium text-slate-400">{plan.clinicalCover}</p>

      {!compact && plan.features.length > 0 && (
        <ul className="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4">
          {plan.features.map((feature) => (
            <li
              key={feature}
              className="flex items-start gap-2 text-[11.5px] font-medium text-slate-500"
            >
              <Check className="mt-0.5 size-3 shrink-0 text-emerald-500" strokeWidth={3} />
              {feature}
            </li>
          ))}
        </ul>
      )}

      {compact && (
        <p className={cn("mt-4 border-t border-slate-100 pt-3", EYEBROW)}>
          {plan.reviewIntervalDays}-day reviews
        </p>
      )}
    </button>
  );
}
