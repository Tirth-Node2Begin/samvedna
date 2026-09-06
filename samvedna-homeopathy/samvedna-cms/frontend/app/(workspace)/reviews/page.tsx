"use client";

import { Suspense, useMemo, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { motion } from "framer-motion";
import { ClipboardCheck, ChevronRight, Lock, PenLine, CircleCheck, FileEdit } from "lucide-react";
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
import { TABLE_HEAD, TABLE_ROW } from "@/lib/tokens";
import type { Review } from "@/types";

interface Payload {
  reviews: Review[];
  counts: { draft: number; awaiting: number; closed: number };
  pendingForMe: number;
}

const FILTERS = [
  { value: "", label: "All" },
  { value: "draft", label: "In progress" },
  { value: "awaiting_signoff", label: "Awaiting sign-off" },
  { value: "closed", label: "Closed" },
] as const;

export default function ReviewsPage() {
  return (
    <Suspense fallback={<Spinner label="Loading reviews" />}>
      <ReviewsInner />
    </Suspense>
  );
}

function ReviewsInner() {
  const searchParams = useSearchParams();
  const [status, setStatus] = useState("");
  const [mineOnly, setMineOnly] = useState(searchParams.get("mine") === "1");

  const path = useMemo(() => `/reviews${qs({ status, mine: mineOnly ? 1 : "" })}`, [status, mineOnly]);
  const { data, error, loading, reload } = useApi<Payload>(path);

  const reviews = data?.reviews ?? [];

  return (
    <PageShell wide>
      <PageHeader
        title="Reviews"
        description="Every bi-monthly review runs on the same forced template and cannot close with a field left empty."
        step={{ number: 4, label: "Review" }}
        eyebrowIcon={ClipboardCheck}
      />

      {data && (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <StatCard label="In progress" value={data.counts.draft} hint="Drafts open" icon={FileEdit} tone="indigo" />
          <StatCard
            label="Awaiting sign-off"
            value={data.counts.awaiting}
            hint="Blocked from closing"
            icon={PenLine}
            tone={data.counts.awaiting ? "amber" : "emerald"}
          />
          <StatCard
            label="Closed"
            value={data.counts.closed}
            hint="Dashboards generated"
            icon={CircleCheck}
            tone="emerald"
          />
        </div>
      )}

      <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
        <Segmented value={status} onChange={setStatus} options={[...FILTERS]} />

        <button
          type="button"
          onClick={() => setMineOnly((value) => !value)}
          aria-pressed={mineOnly}
          className={cn(
            "inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-[12.5px] font-bold transition-colors",
            mineOnly
              ? "border-indigo-300 bg-indigo-50 text-indigo-700"
              : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
          )}
        >
          <PenLine className="size-3.5" /> Waiting on my sign-off
          {data && data.pendingForMe > 0 && (
            <span className="rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-black tabular-nums text-amber-700">
              {data.pendingForMe}
            </span>
          )}
        </button>
      </div>

      {loading && <Spinner label="Loading reviews" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && !error && reviews.length === 0 && (
        <Card>
          <EmptyState
            icon={ClipboardCheck}
            title="No reviews here"
            description="Reviews open from their scheduled touchpoint at the end of each cycle."
          />
        </Card>
      )}

      {!loading && reviews.length > 0 && (
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
                  <th className="hidden px-6 py-3.5 sm:table-cell">Review date</th>
                  <th className="hidden px-6 py-3.5 lg:table-cell">Author</th>
                  <th className="hidden px-6 py-3.5 xl:table-cell">Template</th>
                  <th className="px-6 py-3.5 text-right">Status</th>
                  <th className="w-[52px] px-4 py-3.5" />
                </tr>
              </thead>

              <tbody className="divide-y divide-slate-100 text-sm">
                {reviews.map((review) => (
                  <motion.tr key={review.id} variants={staggerItem} className={`group ${TABLE_ROW}`}>
                    <td className="px-6 py-3.5">
                      <Link href={`/reviews/${review.id}`} className="block">
                        <span className="block text-[13.5px] font-bold text-navy">
                          {review.patientName}
                        </span>
                        <span className="mt-0.5 block text-[11.5px] font-medium text-slate-400">
                          {review.patientCode} · Cycle {review.cycle} of {review.totalCycles} ·{" "}
                          {review.planName}
                        </span>
                      </Link>
                    </td>

                    <td className="hidden px-6 py-3.5 sm:table-cell">
                      <span className="block text-[12.5px] font-semibold tabular-nums text-slate-700">
                        {formatDate(review.reviewDate)}
                      </span>
                      {review.nextReviewDate && (
                        <span className="block text-[11.5px] font-medium text-slate-400">
                          Next {formatDate(review.nextReviewDate)}
                        </span>
                      )}
                    </td>

                    <td className="hidden px-6 py-3.5 lg:table-cell">
                      <span className="block truncate text-[12.5px] font-semibold text-slate-700">
                        {review.authorName || "Unassigned"}
                      </span>
                    </td>

                    <td className="hidden w-[160px] px-6 py-3.5 xl:table-cell">
                      <ProgressBar
                        percent={review.completeness}
                        tone={review.completeness === 100 ? "emerald" : "amber"}
                      />
                      <span className="mt-1.5 block text-[11px] font-bold tabular-nums text-slate-400">
                        {review.completeness}% complete
                      </span>
                    </td>

                    <td className="px-6 py-3.5">
                      <span className="flex flex-wrap items-center justify-end gap-1.5">
                        {review.status === "awaiting_signoff" && review.requiresSenior && (
                          <Badge tone="amber">
                            <Lock className="size-3" /> Senior
                          </Badge>
                        )}
                        <Badge tone={toneForStatus(review.status)}>
                          {statusLabel(review.status)}
                        </Badge>
                      </span>
                    </td>

                    <td className="px-4 py-3.5 text-center">
                      <Link
                        href={`/reviews/${review.id}`}
                        aria-label={`Open cycle ${review.cycle} review`}
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
