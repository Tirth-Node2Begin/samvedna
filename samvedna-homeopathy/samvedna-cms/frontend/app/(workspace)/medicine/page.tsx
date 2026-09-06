"use client";

import { Suspense, useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { motion } from "framer-motion";
import {
  Pill,
  CalendarCheck2,
  CircleAlert,
  CalendarDays,
  PackageCheck,
  PackageX,
  Phone,
  ChevronRight,
  Truck,
} from "lucide-react";
import { useApi } from "@/lib/useApi";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Button,
  Card,
  CardHeader,
  EmptyState,
  ErrorNote,
  InfoNote,
  Spinner,
  StatCard,
} from "@/components/ui/primitives";
import { DeliverMedicineModal } from "@/components/flow/DeliverMedicineModal";
import { OutOfStockModal } from "@/components/flow/OutOfStockModal";
import { cn, formatDate } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import { EYEBROW } from "@/lib/tokens";
import type { MedicineBoard, MedicineSupply } from "@/types";

export default function MedicinePage() {
  return (
    <Suspense fallback={<Spinner label="Loading medicine board" />}>
      <MedicineInner />
    </Suspense>
  );
}

/**
 * The daily medicine board.
 *
 * Three buckets, in the order someone should work through them: what is due
 * today, what has slipped, what is coming. Every row has one big obvious
 * action. Nothing here needs explaining to a coordinator on their first day.
 */
function MedicineInner() {
  const searchParams = useSearchParams();
  const { data, error, loading, reload } = useApi<MedicineBoard>("/medicine");
  const [deliverFor, setDeliverFor] = useState<MedicineSupply | null>(null);
  const [outOfStockFor, setOutOfStockFor] = useState<MedicineSupply | null>(null);

  // `/medicine?case=12` (from a notification) opens that case's dialog directly.
  const focusCase = Number(searchParams.get("case") ?? 0);
  useEffect(() => {
    if (!data || !focusCase || deliverFor) return;
    const all = [...data.dueToday, ...data.overdue, ...data.upcoming, ...data.later];
    const match = all.find((s) => s.caseId === focusCase);
    if (match) setDeliverFor(match);
    // Only on first load of the data — not every re-render.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data, focusCase]);

  const sections = useMemo(
    () =>
      data
        ? [
            {
              key: "dueToday",
              title: "Due today",
              subtitle: "Send these before the day ends",
              rows: data.dueToday,
              tone: "emerald" as const,
              empty: "Nothing is due today.",
            },
            {
              key: "overdue",
              title: "Overdue",
              subtitle: "These dates have passed — chase first",
              rows: data.overdue,
              tone: "rose" as const,
              empty: "No overdue deliveries. Well done.",
            },
            {
              key: "upcoming",
              title: "Coming up",
              subtitle: "Due within the next 7 days",
              rows: data.upcoming,
              tone: "amber" as const,
              empty: "Nothing due in the next week.",
            },
          ]
        : [],
    [data]
  );

  if (loading) return <Spinner label="Loading medicine board" />;
  if (error) {
    return (
      <PageShell>
        <ErrorNote message={error} retry={reload} />
      </PageShell>
    );
  }
  if (!data) return null;

  return (
    <PageShell wide>
      <PageHeader
        eyebrow="Medicine supply"
        eyebrowIcon={Pill}
        title="Who needs medicine today"
        description={`Every active case is on a 15-day supply cycle. Mark a delivery and the next reminder is set automatically — ${formatDate(data.today)}.`}
      />

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <StatCard
          label="Due today"
          value={data.counts.dueToday}
          hint="Send before end of day"
          icon={CalendarCheck2}
          tone={data.counts.dueToday ? "emerald" : "slate"}
        />
        <StatCard
          label="Overdue"
          value={data.counts.overdue}
          hint="Past their due date"
          icon={CircleAlert}
          tone={data.counts.overdue ? "rose" : "emerald"}
        />
        <StatCard
          label="Coming up"
          value={data.counts.upcoming}
          hint="Next 7 days"
          icon={CalendarDays}
          tone="amber"
        />
        <StatCard
          label="Out of stock"
          value={data.counts.outOfStock}
          hint="Waiting on supply"
          icon={PackageX}
          tone={data.counts.outOfStock ? "amber" : "emerald"}
        />
        <StatCard
          label="Delivered this month"
          value={data.counts.deliveredThisMonth}
          hint="Logged deliveries"
          icon={PackageCheck}
          tone="indigo"
        />
      </div>

      <InfoNote tone="indigo">
        How it works: each delivery you mark sets the next due date to <strong>delivery date + 15 days</strong>.
        On that morning the patient appears under <strong>Due today</strong> and the bell lights up. If a
        date slips, they move to <strong>Overdue</strong> until you mark the delivery. If the
        medicine is unavailable, <strong>Out of stock</strong> pushes that patient forward
        without marking anything delivered.
      </InfoNote>

      <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-4">
        {sections.map((section) => (
          <motion.div key={section.key} variants={staggerItem}>
            <Card>
              <CardHeader
                title={section.title}
                subtitle={section.subtitle}
                badge={
                  <Badge tone={section.rows.length ? section.tone : "slate"}>
                    {section.rows.length} patient{section.rows.length === 1 ? "" : "s"}
                  </Badge>
                }
              />
              {section.rows.length === 0 ? (
                <EmptyState icon={Pill} title={section.empty} />
              ) : (
                <ul className="divide-y divide-slate-100">
                  {section.rows.map((supply) => (
                    <SupplyRow
                      key={supply.id}
                      supply={supply}
                      onDeliver={() => setDeliverFor(supply)}
                      onOutOfStock={() => setOutOfStockFor(supply)}
                    />
                  ))}
                </ul>
              )}
            </Card>
          </motion.div>
        ))}

        {data.later.length > 0 && (
          <motion.div variants={staggerItem}>
            <Card>
              <CardHeader
                title="Later"
                subtitle="More than a week away — nothing to do yet"
                badge={<Badge tone="slate">{data.later.length}</Badge>}
              />
              <ul className="divide-y divide-slate-100">
                {data.later.map((supply) => (
                  <SupplyRow
                    key={supply.id}
                    supply={supply}
                    onDeliver={() => setDeliverFor(supply)}
                    onOutOfStock={() => setOutOfStockFor(supply)}
                    quiet
                  />
                ))}
              </ul>
            </Card>
          </motion.div>
        )}

        {data.recent.length > 0 && (
          <motion.div variants={staggerItem}>
            <Card>
              <CardHeader
                title="Recent supply activity"
                subtitle="Deliveries and out-of-stock deferrals, newest first"
              />
              <ul className="divide-y divide-slate-100">
                {data.recent.map((delivery) => (
                  <li key={delivery.id} className="flex items-center gap-3 px-5 py-3">
                    <span
                      className={cn(
                        "grid size-8 shrink-0 place-items-center rounded-full ring-1 ring-inset",
                        delivery.isDeferral
                          ? "bg-amber-50 text-amber-600 ring-amber-100"
                          : "bg-slate-100/70 text-slate-500 ring-white/80"
                      )}
                    >
                      {delivery.isDeferral ? (
                        <PackageX className="size-4" strokeWidth={1.9} />
                      ) : (
                        <Truck className="size-4" strokeWidth={1.9} />
                      )}
                    </span>
                    <span className="min-w-0 flex-1">
                      <span className="block truncate text-[12.5px] font-bold text-navy">
                        {delivery.patientName}
                        <span className="ml-1.5 font-medium text-slate-400">{delivery.patientCode}</span>
                      </span>
                      <span className="block text-[11.5px] font-medium text-slate-500">
                        {formatDate(delivery.deliveredOn)} ·{" "}
                        {delivery.isDeferral ? delivery.kindLabel : delivery.modeLabel}
                        {delivery.reference ? ` · ${delivery.reference}` : ""}
                        {delivery.deliveredByName ? ` · by ${delivery.deliveredByName}` : ""}
                      </span>
                    </span>
                    <span className={cn(EYEBROW, "hidden shrink-0 sm:block")}>
                      Next {formatDate(delivery.nextDueOn)}
                    </span>
                  </li>
                ))}
              </ul>
            </Card>
          </motion.div>
        )}
      </motion.div>

      <OutOfStockModal
        supply={outOfStockFor}
        open={Boolean(outOfStockFor)}
        defaultDeferDays={data.defaultDeferDays}
        onClose={() => setOutOfStockFor(null)}
        onDeferred={() => void reload()}
      />

      <DeliverMedicineModal
        supply={deliverFor}
        open={Boolean(deliverFor)}
        onClose={() => setDeliverFor(null)}
        onDelivered={() => void reload()}
      />
    </PageShell>
  );
}

/* ------------------------------------------------------------------ Row -- */

function SupplyRow({
  supply,
  onDeliver,
  onOutOfStock,
  quiet = false,
}: {
  supply: MedicineSupply;
  onDeliver: () => void;
  onOutOfStock: () => void;
  quiet?: boolean;
}) {
  const tone = supply.state === "overdue" ? "rose" : supply.state === "due_today" ? "emerald" : "amber";

  return (
    <li className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center">
      <span
        className={cn(
          "grid size-10 shrink-0 place-items-center rounded-full ring-1 ring-inset",
          tone === "rose" && "bg-rose-50 text-rose-600 ring-rose-100",
          tone === "emerald" && "bg-emerald-50 text-emerald-600 ring-emerald-100",
          tone === "amber" && "bg-amber-50 text-amber-600 ring-amber-100"
        )}
        aria-hidden
      >
        <Pill className="size-[18px]" strokeWidth={1.9} />
      </span>

      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-center gap-2">
          <Link href={`/patients/${supply.patientId}?tab=medicine`} className="text-[13.5px] font-bold text-navy hover:underline">
            {supply.patientName}
          </Link>
          <Badge tone={quiet ? "slate" : tone}>{supply.stateLabel}</Badge>
          {supply.isOutOfStock && (
            <Badge tone="amber" title={supply.stockNote || undefined}>
              <PackageX className="size-3" /> Out of stock
            </Badge>
          )}
        </div>
        <p className="mt-0.5 text-[11.5px] font-medium text-slate-500">
          {supply.patientCode}
          {supply.guardianName ? ` · ${supply.guardianName}` : ""}
          {supply.city ? ` · ${supply.city}` : ""}
          {supply.caseDoctorName ? ` · ${supply.caseDoctorName}` : ""}
        </p>
      </div>

      <div className="grid grid-cols-2 gap-x-6 gap-y-1 text-[11.5px] sm:w-[260px] sm:shrink-0">
        <span className={EYEBROW}>Last delivered</span>
        <span className={EYEBROW}>Next due</span>
        <span className="font-bold tabular-nums text-slate-700">
          {supply.lastDeliveredOn ? formatDate(supply.lastDeliveredOn) : "Plan start"}
        </span>
        <span
          className={cn(
            "font-black tabular-nums",
            tone === "rose" ? "text-rose-600" : tone === "emerald" ? "text-emerald-700" : "text-navy"
          )}
        >
          {formatDate(supply.nextDueOn)}
        </span>
      </div>

      <div className="flex shrink-0 items-center gap-2 sm:ml-2">
        {supply.phone && (
          <a
            href={`tel:${supply.phone.replace(/\s+/g, "")}`}
            title={`Call ${supply.guardianName ?? "guardian"} · ${supply.phone}`}
            className="grid size-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 transition-colors hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600"
          >
            <Phone className="size-4" />
          </a>
        )}
        <Button
          variant={quiet ? "secondary" : "primary"}
          size="md"
          onClick={onDeliver}
          className={cn(!quiet && tone === "emerald" && "from-emerald-600 to-teal-600 shadow-emerald-500/25")}
        >
          <CalendarCheck2 className="size-4" /> Mark delivered
        </Button>
        {!supply.isOutOfStock && (
          <Button variant="secondary" size="md" onClick={onOutOfStock} title="Medicine unavailable — defer the delivery">
            <PackageX className="size-4" />
            <span className="hidden lg:inline">Out of stock</span>
          </Button>
        )}
        <Link
          href={`/patients/${supply.patientId}?tab=medicine`}
          aria-label="Open patient"
          className="grid size-10 place-items-center rounded-full text-slate-300 transition-colors hover:bg-indigo-50 hover:text-indigo-600"
        >
          <ChevronRight className="size-4" />
        </Link>
      </div>
    </li>
  );
}
