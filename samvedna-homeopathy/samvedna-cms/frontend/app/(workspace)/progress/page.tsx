"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { TrendingUp, ChevronRight, Send, FileBarChart, MailWarning } from "lucide-react";
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
import { Select } from "@/components/ui/form";
import { formatDate, humanise } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import { TABLE_HEAD, TABLE_ROW } from "@/lib/tokens";
import type { ProgressReport } from "@/types";

type Row = ProgressReport & {
  caseCode: string;
  patientId: number;
  patientName: string;
  patientCode: string;
  doctorName: string;
};

interface Payload {
  reports: Row[];
  counts: { total: number; shared: number; unshared: number };
}

export default function ProgressIndexPage() {
  const [trend, setTrend] = useState("");
  const [shared, setShared] = useState("");

  const path = useMemo(() => `/progress${qs({ trend, shared })}`, [trend, shared]);
  const { data, error, loading, reload } = useApi<Payload>(path);

  const reports = data?.reports ?? [];

  return (
    <PageShell wide>
      <PageHeader
        title="Progress reports"
        description="One dashboard per case per cycle, generated automatically when a review closes."
        step={{ number: 6, label: "Progress" }}
        eyebrowIcon={TrendingUp}
      />

      {data && (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <StatCard
            label="Reports generated"
            value={data.counts.total}
            hint="One per closed cycle"
            icon={FileBarChart}
            tone="indigo"
          />
          <StatCard
            label="Shared with parents"
            value={data.counts.shared}
            hint="WhatsApp, portal or email"
            icon={Send}
            tone="emerald"
          />
          <StatCard
            label="Not yet shared"
            value={data.counts.unshared}
            hint="Awaiting a send"
            icon={MailWarning}
            tone={data.counts.unshared ? "amber" : "emerald"}
          />
        </div>
      )}

      <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
        <Select
          value={trend}
          onChange={(event) => setTrend(event.target.value)}
          placeholder="All trends"
          className="w-full sm:w-56"
          options={[
            { value: "improving", label: "Improving" },
            { value: "stable", label: "Stable" },
            { value: "declining", label: "Declining" },
          ]}
        />
        <Select
          value={shared}
          onChange={(event) => setShared(event.target.value)}
          placeholder="Shared and unshared"
          className="w-full sm:w-64"
          options={[
            { value: "1", label: "Shared with the parent" },
            { value: "0", label: "Not yet shared" },
          ]}
        />
      </div>

      {loading && <Spinner label="Loading progress reports" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && !error && reports.length === 0 && (
        <Card>
          <EmptyState
            icon={TrendingUp}
            title="No progress reports yet"
            description="A report is generated the moment a cycle review closes with all sign-offs in place."
          />
        </Card>
      )}

      {!loading && reports.length > 0 && (
        <motion.div
          variants={stagger}
          initial="hidden"
          animate="show"
          className="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm"
        >
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead>
                <tr className={TABLE_HEAD}>
                  <th className="px-6 py-3.5">Patient</th>
                  <th className="hidden px-6 py-3.5 sm:table-cell">Goals</th>
                  <th className="hidden px-6 py-3.5 lg:table-cell">Adherence</th>
                  <th className="px-6 py-3.5 text-right">Trend</th>
                  <th className="w-[52px] px-4 py-3.5" />
                </tr>
              </thead>

              <tbody className="divide-y divide-slate-100 text-sm">
                {reports.map((report) => (
                  <motion.tr key={report.id} variants={staggerItem} className={`group ${TABLE_ROW}`}>
                    <td className="px-6 py-3.5">
                      <Link href={`/progress/${report.caseId}/${report.cycle}`} className="block">
                        <span className="block text-[13.5px] font-bold text-navy">
                          {report.patientName}
                        </span>
                        <span className="mt-0.5 block text-[11.5px] font-medium text-slate-400">
                          {report.patientCode} · Cycle {report.cycle} · {report.periodLabel}
                          {report.doctorName ? ` · ${report.doctorName}` : ""}
                        </span>
                      </Link>
                    </td>

                    <td className="hidden w-[180px] px-6 py-3.5 sm:table-cell">
                      <ProgressBar
                        percent={report.goalsTotal ? (report.goalsProgressing / report.goalsTotal) * 100 : 0}
                        tone="emerald"
                      />
                      <span className="mt-1.5 block text-[11px] font-bold tabular-nums text-slate-400">
                        {report.goalsProgressing} of {report.goalsTotal} progressing
                      </span>
                    </td>

                    <td className="hidden w-[180px] px-6 py-3.5 lg:table-cell">
                      <ProgressBar percent={report.adherencePercent} />
                      <span className="mt-1.5 block text-[11px] font-bold tabular-nums text-slate-400">
                        {report.adherencePercent}% adherence
                      </span>
                    </td>

                    <td className="px-6 py-3.5">
                      <span className="flex flex-wrap items-center justify-end gap-1.5">
                        {report.sharedAt ? (
                          <Badge tone="emerald">
                            <Send className="size-3" /> Shared
                          </Badge>
                        ) : (
                          <Badge tone="amber">Not shared</Badge>
                        )}
                        <Badge tone={toneForStatus(report.overallTrend)}>
                          {humanise(report.overallTrend)}
                        </Badge>
                      </span>
                    </td>

                    <td className="px-4 py-3.5 text-center">
                      <Link
                        href={`/progress/${report.caseId}/${report.cycle}`}
                        aria-label={`Open cycle ${report.cycle} dashboard`}
                        className="inline-grid size-8 place-items-center rounded-full text-slate-300 transition-colors group-hover:bg-indigo-50 group-hover:text-indigo-600"
                      >
                        <ChevronRight className="size-4" />
                      </Link>
                    </td>
                  </motion.tr>
                ))}
              </tbody>
            </table>
          </div>
        </motion.div>
      )}
    </PageShell>
  );
}
