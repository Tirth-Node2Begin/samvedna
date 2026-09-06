"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { CalendarClock, ChevronRight, CircleAlert, CircleCheck, CalendarDays } from "lucide-react";
import { useApi } from "@/lib/useApi";
import { qs } from "@/lib/api";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Card,
  CardHeader,
  EmptyState,
  ErrorNote,
  Spinner,
  StatCard,
  toneForStatus,
} from "@/components/ui/primitives";
import { Select } from "@/components/ui/form";
import { cn, formatDate, relativeDays, statusLabel } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import type { ScheduleEvent } from "@/types";

interface Payload {
  events: ScheduleEvent[];
  counts: { overdue: number; upcoming: number; done: number };
}

/** Dot colour per status. Full static strings — JIT-safe. */
const DOT: Record<string, string> = {
  done: "bg-emerald-500",
  upcoming: "bg-amber-500",
  missed: "bg-rose-500",
  rescheduled: "bg-indigo-500",
  scheduled: "bg-slate-300",
};

/** Step 3 across every case at once — the practice-wide follow-up board. */
export default function SchedulePage() {
  const [status, setStatus] = useState("");
  const [type, setType] = useState("");

  const path = useMemo(() => `/schedule${qs({ status, type, limit: 400 })}`, [status, type]);
  const { data, error, loading, reload } = useApi<Payload>(path);

  const events = useMemo(() => data?.events ?? [], [data]);

  // Group by due date so the board reads like a diary rather than a flat table.
  const grouped = useMemo(() => {
    const map = new Map<string, ScheduleEvent[]>();
    for (const event of events) {
      const list = map.get(event.dueDate) ?? [];
      list.push(event);
      map.set(event.dueDate, list);
    }
    return [...map.entries()];
  }, [events]);

  return (
    <PageShell>
      <PageHeader
        title="Follow-up schedule"
        description="Every touchpoint the care plans generated, across all active cases."
        step={{ number: 3, label: "Schedule" }}
        eyebrowIcon={CalendarClock}
      />

      {data && (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <StatCard
            label="Overdue"
            value={data.counts.overdue}
            hint="Missed follow-ups"
            icon={CircleAlert}
            tone={data.counts.overdue ? "rose" : "emerald"}
          />
          <StatCard
            label="Upcoming"
            value={data.counts.upcoming}
            hint="Due within 14 days"
            icon={CalendarDays}
            tone="amber"
          />
          <StatCard
            label="Completed"
            value={data.counts.done}
            hint="Touchpoints closed"
            icon={CircleCheck}
            tone="emerald"
          />
        </div>
      )}

      <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
        <Select
          value={status}
          onChange={(event) => setStatus(event.target.value)}
          placeholder="All statuses"
          className="w-full sm:w-56"
          options={["scheduled", "upcoming", "done", "missed", "rescheduled"].map((value) => ({
            value,
            label: statusLabel(value),
          }))}
        />
        <Select
          value={type}
          onChange={(event) => setType(event.target.value)}
          placeholder="All touchpoint types"
          className="w-full sm:w-72"
          options={[
            { value: "activation", label: "Plan activation" },
            { value: "adherence", label: "Adherence check (non-consult)" },
            { value: "review", label: "Formal review" },
            { value: "founder_review", label: "Founder review" },
          ]}
        />
      </div>

      {loading && <Spinner label="Loading the schedule" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && !error && events.length === 0 && (
        <Card>
          <EmptyState
            icon={CalendarClock}
            title="Nothing matches these filters"
            description="Try clearing the status or type filter."
          />
        </Card>
      )}

      {!loading && grouped.length > 0 && (
        <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-4">
          {grouped.map(([date, dayEvents]) => (
            <motion.div key={date} variants={staggerItem}>
              <Card>
                <CardHeader
                  title={formatDate(date)}
                  subtitle={`${dayEvents.length} touchpoint${dayEvents.length === 1 ? "" : "s"}`}
                  badge={
                    <span
                      className={cn(
                        "text-[11px] font-bold",
                        dayEvents[0].isOverdue ? "text-rose-600" : "text-slate-400"
                      )}
                    >
                      {relativeDays(dayEvents[0].daysAway)}
                    </span>
                  }
                />
                <ul className="divide-y divide-slate-100">
                  {dayEvents.map((event) => (
                    <li key={event.id}>
                      <Link
                        href={`/patients/${event.patientId}?tab=schedule`}
                        className="group flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50/70"
                      >
                        <span
                          className={cn(
                            "size-2.5 shrink-0 rounded-full",
                            DOT[event.status] ?? "bg-slate-300"
                          )}
                          aria-hidden
                        />
                        <span className="min-w-0 flex-1">
                          <span className="block truncate text-[13px] font-bold text-navy">
                            {event.patientName} — {event.title}
                          </span>
                          <span className="block truncate text-[11.5px] font-medium text-slate-500">
                            {event.patientCode} · Cycle {event.cycle}
                            {event.ownerName ? ` · ${event.ownerName}` : ""}
                          </span>
                        </span>
                        {!event.isConsult && (
                          <Badge tone="slate" className="hidden sm:inline-flex">
                            Non-consult
                          </Badge>
                        )}
                        <Badge tone={toneForStatus(event.status)}>{statusLabel(event.status)}</Badge>
                        <ChevronRight className="size-4 shrink-0 text-slate-300 transition-colors group-hover:text-indigo-600" />
                      </Link>
                    </li>
                  ))}
                </ul>
              </Card>
            </motion.div>
          ))}
        </motion.div>
      )}
    </PageShell>
  );
}
