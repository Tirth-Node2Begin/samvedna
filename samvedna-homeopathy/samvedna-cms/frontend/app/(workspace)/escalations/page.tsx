"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { TriangleAlert, ChevronRight, Clock, CircleCheck, Timer } from "lucide-react";
import { useApi } from "@/lib/useApi";
import { qs } from "@/lib/api";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Card,
  EmptyState,
  ErrorNote,
  ProgressBar,
  Spinner,
  StatCard,
  toneForStatus,
} from "@/components/ui/primitives";
import { Segmented } from "@/components/ui/form";
import { cn, formatDate, statusLabel } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import type { Escalation } from "@/types";

interface Payload {
  escalations: Escalation[];
  counts: { open: number; inProgress: number; resolved: number; overdue: number };
}

const FILTERS = [
  { value: "", label: "All" },
  { value: "open", label: "Open" },
  { value: "in_progress", label: "In progress" },
  { value: "resolved", label: "Resolved" },
] as const;

export default function EscalationsPage() {
  const [status, setStatus] = useState("");
  const path = useMemo(() => `/escalations${qs({ status })}`, [status]);
  const { data, error, loading, reload } = useApi<Payload>(path);

  const escalations = data?.escalations ?? [];

  return (
    <PageShell>
      <PageHeader
        title="Escalations"
        description="Cases that need a Senior or Founder opinion. Every one carries a 7-day resolution SLA."
        step={{ number: 5, label: "Escalation" }}
        eyebrowIcon={TriangleAlert}
      />

      {data && (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <StatCard
            label="Open"
            value={data.counts.open}
            hint="Not yet picked up"
            icon={TriangleAlert}
            tone={data.counts.open ? "amber" : "emerald"}
          />
          <StatCard
            label="In progress"
            value={data.counts.inProgress}
            hint="With a reviewer"
            icon={Timer}
            tone="indigo"
          />
          <StatCard
            label="Resolved"
            value={data.counts.resolved}
            hint="Closed with notes"
            icon={CircleCheck}
            tone="emerald"
          />
          <StatCard
            label="Past SLA"
            value={data.counts.overdue}
            hint="Beyond 7 days"
            icon={Clock}
            tone={data.counts.overdue ? "rose" : "emerald"}
          />
        </div>
      )}

      <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
        <Segmented value={status} onChange={setStatus} options={[...FILTERS]} />
      </div>

      {loading && <Spinner label="Loading escalations" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && !error && escalations.length === 0 && (
        <Card>
          <EmptyState
            icon={TriangleAlert}
            title="No escalations here"
            description="Every case is progressing inside its plan."
          />
        </Card>
      )}

      {!loading && escalations.length > 0 && (
        <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-3">
          {escalations.map((escalation) => {
            const open = escalation.status === "open" || escalation.status === "in_progress";
            // How much of the 7-day window is spent.
            const spent = Math.max(
              0,
              Math.min(100, ((escalation.slaDays - escalation.daysLeft) / escalation.slaDays) * 100)
            );

            return (
              <motion.div key={escalation.id} variants={staggerItem}>
                <Link
                  href={`/escalations/${escalation.id}`}
                  className={cn(
                    "group flex flex-col gap-3.5 rounded-2xl border bg-white p-5 shadow-sm transition-colors",
                    escalation.isOverdue
                      ? "border-rose-200 hover:border-rose-300"
                      : "border-slate-200/70 hover:border-indigo-200"
                  )}
                >
                  <div className="flex items-center gap-4">
                    <div className="min-w-0 flex-[2]">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="text-[13.5px] font-bold text-navy">
                          {escalation.patientName}
                        </span>
                        <Badge tone={toneForStatus(escalation.status)}>
                          {statusLabel(escalation.status)}
                        </Badge>
                        <Badge tone={escalation.level === "founder" ? "violet" : "slate"}>
                          {escalation.level === "founder" ? "With the Founder" : "With the Senior Doctor"}
                        </Badge>
                      </div>
                      <p className="mt-1 truncate text-[11.5px] font-medium text-slate-400">
                        {escalation.code} · raised {formatDate(escalation.raisedAt)}
                        {escalation.raisedByName ? ` by ${escalation.raisedByName}` : ""}
                      </p>
                    </div>

                    <div className="hidden min-w-0 flex-1 sm:block">
                      <p className="truncate text-[12.5px] font-semibold text-slate-700">
                        {escalation.reasonLabels[0] ?? "—"}
                      </p>
                      {escalation.reasonLabels.length > 1 && (
                        <p className="text-[11.5px] font-medium text-slate-400">
                          +{escalation.reasonLabels.length - 1} more reason
                          {escalation.reasonLabels.length > 2 ? "s" : ""}
                        </p>
                      )}
                    </div>

                    <Badge tone={escalation.isOverdue ? "rose" : open ? "amber" : "emerald"}>
                      <Clock className="size-3" />
                      {escalation.status === "resolved"
                        ? `Resolved ${formatDate(escalation.resolvedAt)}`
                        : escalation.isOverdue
                          ? `${Math.abs(escalation.daysLeft)}d overdue`
                          : `${escalation.daysLeft}d left`}
                    </Badge>

                    <ChevronRight className="size-4 shrink-0 text-slate-300 transition-colors group-hover:text-indigo-600" />
                  </div>

                  {open && (
                    <ProgressBar
                      percent={spent}
                      tone={escalation.isOverdue ? "rose" : spent > 70 ? "amber" : "indigo"}
                    />
                  )}
                </Link>
              </motion.div>
            );
          })}
        </motion.div>
      )}
    </PageShell>
  );
}
