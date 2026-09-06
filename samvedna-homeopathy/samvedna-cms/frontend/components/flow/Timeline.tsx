"use client";

import { motion } from "framer-motion";
import { cn, formatDate, relativeDays, statusLabel } from "@/lib/format";
import { stagger, timelineItem } from "@/lib/motion";
import { Badge, toneForStatus } from "@/components/ui/primitives";
import type { ScheduleEvent } from "@/types";

/** Dot colour per status. Full static strings — JIT-safe. */
const DOT: Record<string, string> = {
  done: "bg-emerald-500",
  upcoming: "bg-amber-500",
  missed: "bg-rose-500",
  rescheduled: "bg-indigo-500",
  scheduled: "bg-slate-300",
};

/**
 * The vertical follow-up timeline.
 *
 * Adherence checks read lighter than consults on purpose — they are non-consult
 * touchpoints and should not carry the same weight as a formal review.
 */
export function Timeline({
  events,
  onSelect,
  emptyLabel = "No touchpoints in this cycle.",
}: {
  events: ScheduleEvent[];
  onSelect?: (event: ScheduleEvent) => void;
  emptyLabel?: string;
}) {
  if (!events.length) {
    return <p className="py-6 text-center text-[12.5px] font-medium text-slate-400">{emptyLabel}</p>;
  }

  return (
    <motion.ol variants={stagger} initial="hidden" animate="show" className="flex flex-col">
      {events.map((event, index) => {
        const last = index === events.length - 1;
        const clickable = Boolean(onSelect);

        return (
          <motion.li key={event.id} variants={timelineItem} className="flex gap-3.5">
            <div className="flex flex-col items-center pt-2.5">
              <span
                className={cn(
                  "size-2.5 shrink-0 rounded-full ring-4 ring-white",
                  DOT[event.status] ?? "bg-slate-300"
                )}
                aria-hidden
              />
              {!last && <span className="w-px flex-1 bg-slate-100" aria-hidden />}
            </div>

            <div className={cn("min-w-0 flex-1", last ? "pb-1" : "pb-4")}>
              <button
                type="button"
                disabled={!clickable}
                onClick={() => onSelect?.(event)}
                className={cn(
                  "-mx-2 w-full rounded-xl px-2 py-1.5 text-left",
                  clickable ? "transition-colors hover:bg-slate-50" : "cursor-default"
                )}
              >
                <div className="flex flex-wrap items-center gap-x-2.5 gap-y-1.5">
                  <span className="text-[13px] font-bold text-navy">
                    {formatDate(event.dueDate)} — {event.title}
                  </span>
                  <Badge tone={toneForStatus(event.status)}>{statusLabel(event.status)}</Badge>
                  {!event.isConsult && (
                    <Badge tone="slate" className="font-semibold">
                      Non-consult
                    </Badge>
                  )}
                  {event.status !== "done" && (
                    <span
                      className={cn(
                        "text-[11px] font-bold",
                        event.isOverdue ? "text-rose-600" : "text-slate-400"
                      )}
                    >
                      {relativeDays(event.daysAway)}
                    </span>
                  )}
                </div>

                <p className="mt-1 text-[11.5px] font-medium text-slate-500">
                  Cycle {event.cycle}
                  {event.ownerName ? ` · ${event.ownerName}` : ""}
                  {event.notes ? ` · ${event.notes}` : ""}
                </p>

                {event.dueDate !== event.originalDueDate && (
                  <p className="mt-1 text-[10.5px] font-bold text-indigo-600">
                    Rescheduled from {formatDate(event.originalDueDate)}
                  </p>
                )}
              </button>
            </div>
          </motion.li>
        );
      })}
    </motion.ol>
  );
}
