"use client";

import { useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import {
  Users,
  CalendarClock,
  ClipboardCheck,
  TriangleAlert,
  Activity,
  Pill,
  TrendingUp,
  ArrowRight,
  Gauge,
  Sparkles,
  CalendarCheck2,
  Phone,
} from "lucide-react";
import { useApi } from "@/lib/useApi";
import { useSession } from "@/lib/session";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Card,
  CardBody,
  CardHeader,
  EmptyState,
  ErrorNote,
  IconChip,
  LinkButton,
  ProgressBar,
  Spinner,
  StatCard,
  toneForStatus,
  type Tone,
} from "@/components/ui/primitives";
import { cn, formatDate, relativeDays, statusLabel, timeAgo } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import { EYEBROW, HERO_PRIMARY_BTN } from "@/lib/tokens";
import { DeliverMedicineModal } from "@/components/flow/DeliverMedicineModal";
import type { DashboardPayload, MedicineSupply } from "@/types";

export default function DashboardPage() {
  const { user } = useSession();
  const { data, error, loading, reload } = useApi<DashboardPayload>("/dashboard");
  const [deliverFor, setDeliverFor] = useState<MedicineSupply | null>(null);

  if (loading) return <Spinner label="Loading the command dashboard" />;

  if (error) {
    return (
      <PageShell>
        <ErrorNote message={error} retry={reload} />
      </PageShell>
    );
  }
  if (!data) return null;

  const { metrics, upcoming, reviewQueue, escalations, activity, trends, scorecard } = data;

  const tiles: {
    label: string;
    value: number;
    hint: string;
    icon: React.ComponentType<{ className?: string }>;
    href: string;
    tone: Tone;
  }[] = [
    {
      label: "Active cases",
      value: metrics.activeCases,
      hint: `${metrics.draftIntakes} draft intake${metrics.draftIntakes === 1 ? "" : "s"}`,
      icon: Users,
      href: "/patients",
      tone: "indigo",
    },
    {
      label: "Reviews due",
      value: metrics.reviewsDue,
      hint: `${metrics.awaitingSignoff} awaiting sign-off`,
      icon: ClipboardCheck,
      href: "/reviews",
      tone: metrics.reviewsDue > 0 ? "amber" : "emerald",
    },
    {
      label: "Open escalations",
      value: metrics.openEscalations,
      hint:
        metrics.overdueEscalations > 0
          ? `${metrics.overdueEscalations} past the 7-day SLA`
          : "All within SLA",
      icon: TriangleAlert,
      href: "/escalations",
      tone: metrics.overdueEscalations > 0 ? "rose" : "emerald",
    },
    {
      label: "Overdue touchpoints",
      value: metrics.overdueTouchpoints,
      hint: "Missed follow-ups",
      icon: CalendarClock,
      href: "/schedule",
      tone: metrics.overdueTouchpoints > 0 ? "rose" : "emerald",
    },
    {
      label: "Medicine today",
      value: metrics.medicineDueToday,
      hint:
        metrics.medicineOverdue > 0
          ? `${metrics.medicineOverdue} overdue`
          : "15-day supply cycle",
      icon: Pill,
      href: "/medicine",
      tone: metrics.medicineOverdue > 0 ? "rose" : metrics.medicineDueToday > 0 ? "emerald" : "slate",
    },
  ];

  const medicineRows = [...data.medicine.overdue, ...data.medicine.dueToday];

  const trendRows: [string, number, Tone][] = [
    ["Improving", trends.improving, "emerald"],
    ["Stable", trends.stable, "amber"],
    ["Declining", trends.declining, "rose"],
  ];
  const trendTotal = Math.max(1, trends.improving + trends.stable + trends.declining);

  return (
    <PageShell>
      <PageHeader
        eyebrow="Command centre"
        eyebrowIcon={Sparkles}
        title={`Good to see you, ${user?.name.replace(/^Dr\.?\s+/i, "") ?? ""}`}
        description="Everything that needs a decision today, across every active case."
        actions={
          <Link href="/patients/new" className={HERO_PRIMARY_BTN}>
            New patient intake <ArrowRight className="size-4" />
          </Link>
        }
      />

      {metrics.pendingForMe > 0 && (
        <Link
          href="/reviews?mine=1"
          className="flex items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 ring-1 ring-amber-100 transition-colors hover:bg-amber-100/60"
        >
          <span className="flex items-center gap-3">
            <IconChip icon={ClipboardCheck} tone="amber" className="bg-white/70" />
            <span className="text-[13px] font-semibold text-amber-800">
              <strong className="font-black tabular-nums">{metrics.pendingForMe}</strong> review
              {metrics.pendingForMe === 1 ? "" : "s"} waiting on your sign-off.
            </span>
          </span>
          <ArrowRight className="size-4 shrink-0 text-amber-700" />
        </Link>
      )}

      {/* KPI row */}
      <motion.div
        variants={stagger}
        initial="hidden"
        animate="show"
        className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5"
      >
        {tiles.map((tile) => (
          <motion.div key={tile.label} variants={staggerItem}>
            <StatCard
              label={tile.label}
              value={tile.value}
              hint={tile.hint}
              icon={tile.icon}
              tone={tile.tone}
              href={tile.href}
            />
          </motion.div>
        ))}
      </motion.div>

      {/* Medicine to send today */}
      {medicineRows.length > 0 && (
        <Card className="border-emerald-200/70">
          <CardHeader
            title="Medicine to send today"
            subtitle="Mark each delivery and the next 15-day reminder is set for you"
            badge={
              <Badge tone={metrics.medicineOverdue > 0 ? "rose" : "emerald"}>
                {medicineRows.length} patient{medicineRows.length === 1 ? "" : "s"}
              </Badge>
            }
            actions={
              <LinkButton href="/medicine" variant="ghost" size="sm">
                Medicine board
              </LinkButton>
            }
          />
          <ul className="divide-y divide-slate-100">
            {medicineRows.map((supply) => {
              const overdue = supply.state === "overdue";
              return (
                <li key={supply.id} className="flex flex-col gap-3 px-5 py-3.5 sm:flex-row sm:items-center">
                  <span
                    className={cn(
                      "grid size-9 shrink-0 place-items-center rounded-full ring-1 ring-inset",
                      overdue ? "bg-rose-50 text-rose-600 ring-rose-100" : "bg-emerald-50 text-emerald-600 ring-emerald-100"
                    )}
                    aria-hidden
                  >
                    <Pill className="size-4" strokeWidth={1.9} />
                  </span>
                  <span className="min-w-0 flex-1">
                    <span className="flex flex-wrap items-center gap-2">
                      <Link href={`/patients/${supply.patientId}?tab=medicine`} className="text-[13px] font-bold text-navy hover:underline">
                        {supply.patientName}
                      </Link>
                      <Badge tone={overdue ? "rose" : "emerald"}>{supply.stateLabel}</Badge>
                    </span>
                    <span className="block text-[11.5px] font-medium text-slate-500">
                      {supply.guardianName ? `${supply.guardianName} · ` : ""}
                      {supply.phone ?? ""}
                      {supply.lastDeliveredOn ? ` · last ${formatDate(supply.lastDeliveredOn)}` : ""}
                    </span>
                  </span>
                  <span className="flex shrink-0 items-center gap-2">
                    {supply.phone && (
                      <a
                        href={`tel:${supply.phone.replace(/\s+/g, "")}`}
                        aria-label="Call guardian"
                        className="grid size-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 transition-colors hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600"
                      >
                        <Phone className="size-4" />
                      </a>
                    )}
                    <button
                      type="button"
                      onClick={() => setDeliverFor(supply)}
                      className="inline-flex h-9 items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                    >
                      <CalendarCheck2 className="size-3.5" /> Mark delivered
                    </button>
                  </span>
                </li>
              );
            })}
          </ul>
        </Card>
      )}

      {/* Practice health */}
      <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <Card>
          <CardHeader title="Medicine adherence" subtitle="Average across the last 90 days" />
          <CardBody>
            <p className="text-[34px] font-black tabular-nums leading-none tracking-tight text-navy">
              {metrics.adherence}%
            </p>
            <ProgressBar percent={metrics.adherence} className="mt-4" />
            <p className="mt-3 flex items-center gap-1.5 text-[11.5px] font-medium text-slate-500">
              <Pill className="size-3.5 text-indigo-500" /> Recorded at every 15-day adherence check
            </p>
          </CardBody>
        </Card>

        <Card>
          <CardHeader title="CMS completeness" subtitle="How fully reviews are being filled in" />
          <CardBody>
            <p className="text-[34px] font-black tabular-nums leading-none tracking-tight text-navy">
              {metrics.cmsCompleteness}%
            </p>
            <ProgressBar
              percent={metrics.cmsCompleteness}
              tone={metrics.cmsCompleteness === 100 ? "emerald" : "amber"}
              className="mt-4"
            />
            <p className="mt-3 flex items-center gap-1.5 text-[11.5px] font-medium text-slate-500">
              <Gauge className="size-3.5 text-indigo-500" />
              You: {scorecard.reviewsClosed} closed · {scorecard.onTimeRate}% on time ·{" "}
              {scorecard.satisfaction || "—"}/5
            </p>
          </CardBody>
        </Card>

        <Card>
          <CardHeader title="Case trends" subtitle="From generated progress reports" />
          <CardBody className="flex flex-col gap-3">
            {trendRows.map(([label, count, tone]) => (
              <div key={label}>
                <div className="mb-1.5 flex items-baseline justify-between">
                  <span className="text-[12px] font-semibold text-slate-600">{label}</span>
                  <span className="text-[12px] font-black tabular-nums text-navy">{count}</span>
                </div>
                <ProgressBar percent={(count / trendTotal) * 100} tone={tone} />
              </div>
            ))}
            <p className="mt-1 flex items-center gap-1.5 text-[11px] font-medium text-slate-400">
              <TrendingUp className="size-3.5" /> One report per case per cycle
            </p>
          </CardBody>
        </Card>
      </div>

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
        {/* Upcoming touchpoints */}
        <Card>
          <CardHeader
            title="Next touchpoints"
            subtitle="Auto-generated from each case's care plan"
            actions={
              <LinkButton href="/schedule" variant="ghost" size="sm">
                Full schedule
              </LinkButton>
            }
          />
          {upcoming.length === 0 ? (
            <EmptyState
              icon={CalendarClock}
              title="Nothing scheduled"
              description="Every touchpoint is done."
            />
          ) : (
            <ul className="divide-y divide-slate-100">
              {upcoming.map((event) => (
                <li key={event.id}>
                  <Link
                    href={`/patients/${event.patientId}`}
                    className="flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50/70"
                  >
                    <span className="min-w-0 flex-1">
                      <span className="block truncate text-[13px] font-bold text-navy">
                        {event.patientName} — {event.title}
                      </span>
                      <span className="block text-[11.5px] font-medium text-slate-500">
                        {formatDate(event.dueDate)} · Cycle {event.cycle}
                        {event.ownerName ? ` · ${event.ownerName}` : ""}
                      </span>
                    </span>
                    <span
                      className={cn(
                        "hidden shrink-0 text-[11px] font-bold sm:block",
                        event.isOverdue ? "text-rose-600" : "text-slate-400"
                      )}
                    >
                      {relativeDays(event.daysAway)}
                    </span>
                    <Badge tone={toneForStatus(event.status)}>{statusLabel(event.status)}</Badge>
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </Card>

        {/* Sign-off queue */}
        <Card>
          <CardHeader
            title="Awaiting sign-off"
            subtitle="Reviews that cannot close yet"
            actions={
              <LinkButton href="/reviews" variant="ghost" size="sm">
                All reviews
              </LinkButton>
            }
          />
          {reviewQueue.length === 0 ? (
            <EmptyState
              icon={ClipboardCheck}
              title="No reviews pending"
              description="Every submitted review is signed and closed."
            />
          ) : (
            <ul className="divide-y divide-slate-100">
              {reviewQueue.map((review) => (
                <li key={review.id}>
                  <Link
                    href={`/reviews/${review.id}`}
                    className="flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50/70"
                  >
                    <span className="min-w-0 flex-1">
                      <span className="block truncate text-[13px] font-bold text-navy">
                        {review.patientName} — cycle {review.cycle} of {review.totalCycles}
                      </span>
                      <span className="block truncate text-[11.5px] font-medium text-slate-500">
                        {review.planName} · reviewed {formatDate(review.reviewDate)} ·{" "}
                        {review.authorName}
                      </span>
                    </span>
                    <Badge tone="amber">Awaiting sign-off</Badge>
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </Card>

        {/* Escalations */}
        <Card>
          <CardHeader
            title="Open escalations"
            subtitle="7-day resolution SLA"
            actions={
              <LinkButton href="/escalations" variant="ghost" size="sm">
                Escalation board
              </LinkButton>
            }
          />
          {escalations.length === 0 ? (
            <EmptyState
              icon={TriangleAlert}
              title="No open escalations"
              description="Every case is progressing inside its plan."
            />
          ) : (
            <ul className="divide-y divide-slate-100">
              {escalations.map((escalation) => (
                <li key={escalation.id}>
                  <Link
                    href={`/escalations/${escalation.id}`}
                    className="flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50/70"
                  >
                    <span className="min-w-0 flex-1">
                      <span className="block truncate text-[13px] font-bold text-navy">
                        {escalation.patientName} · {escalation.code}
                      </span>
                      <span className="block truncate text-[11.5px] font-medium text-slate-500">
                        {escalation.reasonLabels[0] ?? "Escalation raised"}
                      </span>
                    </span>
                    <Badge tone={escalation.isOverdue ? "rose" : "amber"}>
                      {escalation.isOverdue
                        ? `${Math.abs(escalation.daysLeft)}d overdue`
                        : `${escalation.daysLeft}d left`}
                    </Badge>
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </Card>

        {/* Activity */}
        <Card>
          <CardHeader
            title="Recent activity"
            subtitle="Audit trail"
            actions={
              <LinkButton href="/activity" variant="ghost" size="sm">
                Full log
              </LinkButton>
            }
          />
          {activity.length === 0 ? (
            <EmptyState icon={Activity} title="No activity yet" />
          ) : (
            <ul className="divide-y divide-slate-100">
              {activity.map((entry) => (
                <li key={entry.id} className="flex items-start gap-3 px-5 py-3">
                  <span className="mt-1.5 size-1.5 shrink-0 rounded-full bg-indigo-400" aria-hidden />
                  <span className="min-w-0 flex-1">
                    <span className="block text-[12.5px] font-semibold text-slate-700">
                      {entry.summary || entry.action}
                    </span>
                    <span className={cn("mt-0.5 block", EYEBROW)}>
                      {entry.actorName} · {timeAgo(entry.createdAt)}
                    </span>
                  </span>
                </li>
              ))}
            </ul>
          )}
        </Card>
      </div>

      <DeliverMedicineModal
        supply={deliverFor}
        open={Boolean(deliverFor)}
        onClose={() => setDeliverFor(null)}
        onDelivered={() => void reload()}
      />
    </PageShell>
  );
}
