"use client";

import { useMemo, useState } from "react";
import { motion } from "framer-motion";
import { ScrollText } from "lucide-react";
import { useApi } from "@/lib/useApi";
import { qs } from "@/lib/api";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Avatar,
  Badge,
  Card,
  CardBody,
  EmptyState,
  ErrorNote,
  Spinner,
  type Tone,
} from "@/components/ui/primitives";
import { Select } from "@/components/ui/form";
import { formatDateTime, humanise, timeAgo } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import type { ActivityEntry } from "@/types";

const ENTITY_TONES: Record<string, Tone> = {
  patient: "indigo",
  baseline: "indigo",
  case: "emerald",
  schedule: "slate",
  review: "violet",
  escalation: "rose",
  progress: "emerald",
  plan: "amber",
  user: "slate",
  auth: "slate",
};

/** The audit trail — who did what, to which record, when. */
export default function ActivityPage() {
  const [entityType, setEntityType] = useState("");
  const path = useMemo(() => `/activity${qs({ entityType, limit: 150 })}`, [entityType]);
  const { data, error, loading, reload } = useApi<{ activity: ActivityEntry[] }>(path);

  const activity = useMemo(() => data?.activity ?? [], [data]);

  // Group by calendar day so a long trail stays scannable.
  const grouped = useMemo(() => {
    const map = new Map<string, ActivityEntry[]>();
    for (const entry of activity) {
      const day = (entry.createdAt ?? "").slice(0, 10);
      const list = map.get(day) ?? [];
      list.push(entry);
      map.set(day, list);
    }
    return [...map.entries()];
  }, [activity]);

  return (
    <PageShell>
      <PageHeader
        title="Audit log"
        description="Every clinically meaningful action in the CMS, in the order it happened."
        eyebrow="Audit"
        eyebrowIcon={ScrollText}
      />

      <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
        <div className="w-full sm:max-w-xs">
          <Select
            value={entityType}
            onChange={(event) => setEntityType(event.target.value)}
            placeholder="All record types"
            options={[
              { value: "patient", label: "Patients" },
              { value: "baseline", label: "Baselines" },
              { value: "case", label: "Cases" },
              { value: "schedule", label: "Schedule" },
              { value: "review", label: "Reviews" },
              { value: "escalation", label: "Escalations" },
              { value: "progress", label: "Progress reports" },
              { value: "plan", label: "Care plans" },
              { value: "user", label: "Team" },
            ]}
          />
        </div>
      </div>

      {loading && <Spinner label="Loading the audit log" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && !error && activity.length === 0 && (
        <Card>
          <EmptyState icon={ScrollText} title="Nothing logged yet" description="Actions appear here as they happen." />
        </Card>
      )}

      {!loading && grouped.length > 0 && (
        <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-4">
          {grouped.map(([day, entries]) => (
            <motion.div key={day} variants={staggerItem}>
              <p className="mb-2.5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                {formatDateTime(`${day} 00:00:00`).split(",")[0]}
              </p>
              <Card>
                <ul className="divide-y divide-slate-100">
                    {entries.map((entry) => (
                      <li key={entry.id} className="flex items-start gap-3 px-5 py-3">
                        <Avatar name={entry.actorName} className="size-7 text-[10px]" />
                        <div className="min-w-0 flex-1">
                          <p className="text-[12.5px] font-semibold text-slate-700">{entry.summary || humanise(entry.action)}</p>
                          <p className="mt-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                            {entry.actorName} · {timeAgo(entry.createdAt)}
                          </p>
                        </div>
                        {entry.entityType && (
                          <Badge tone={ENTITY_TONES[entry.entityType] ?? "slate"}>
                            {humanise(entry.entityType)}
                          </Badge>
                        )}
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
