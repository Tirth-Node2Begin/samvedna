"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { Bell, Pill, PackageX, CheckCheck, X, ArrowRight, Inbox } from "lucide-react";
import { api } from "@/lib/api";
import { useApi } from "@/lib/useApi";
import { qs } from "@/lib/api";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Button,
  Card,
  EmptyState,
  ErrorNote,
  Spinner,
  StatCard,
  toneForStatus,
} from "@/components/ui/primitives";
import { Segmented } from "@/components/ui/form";
import { DeliverMedicineModal } from "@/components/flow/DeliverMedicineModal";
import { useToast } from "@/components/ui/Toast";
import { cn, formatDate, statusLabel, timeAgo } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import type { AppNotification, MedicineSupply, NotificationsPayload } from "@/types";

const FILTERS = [
  { value: "open", label: "Open" },
  { value: "unread", label: "Unread" },
  { value: "done", label: "Done" },
  { value: "dismissed", label: "Dismissed" },
  { value: "all", label: "All" },
] as const;

type Filter = (typeof FILTERS)[number]["value"];

/** The full notification history — the bell shows the latest few, this shows everything. */
export default function NotificationsPage() {
  const toast = useToast();
  const [filter, setFilter] = useState<Filter>("open");
  const [deliverFor, setDeliverFor] = useState<MedicineSupply | null>(null);

  const path = useMemo(() => {
    if (filter === "open") return "/notifications";
    if (filter === "all") return "/notifications?all=1";
    return `/notifications${qs({ status: filter })}`;
  }, [filter]);

  const { data, error, loading, reload, setData } = useApi<NotificationsPayload>(path);
  const items = data?.notifications ?? [];

  async function act(notice: AppNotification, action: "read" | "dismiss") {
    try {
      await api.post(`/notifications/${notice.id}/${action}`);
      await reload();
    } catch {
      toast("Could not update that notification.", "error");
    }
  }

  async function markAllRead() {
    try {
      const r = await api.post<{ marked: number }>("/notifications/read-all");
      toast(r.marked ? `${r.marked} marked as read.` : "Nothing was unread.", "info");
      await reload();
    } catch {
      toast("Could not mark notifications as read.", "error");
    }
  }

  async function openDeliver(notice: AppNotification) {
    if (!notice.caseId) return;
    const payload = await api.get<{ supply: MedicineSupply | null; patient: { childName: string; code: string } }>(
      `/medicine/${notice.caseId}`
    );
    if (payload.supply) {
      setDeliverFor({ ...payload.supply, patientName: payload.patient.childName, patientCode: payload.patient.code });
    }
  }

  return (
    <PageShell>
      <PageHeader
        eyebrow="Inbox"
        eyebrowIcon={Bell}
        title="Notifications"
        description="Every reminder the CMS has raised. Medicine reminders are generated each morning for any case whose 15-day supply is due."
        actions={
          data && data.counts.unread > 0 ? (
            <Button variant="secondary" onClick={() => void markAllRead()}>
              <CheckCheck className="size-4" /> Mark all read
            </Button>
          ) : undefined
        }
      />

      {data && (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <StatCard
            label="Unread"
            value={data.counts.unread}
            hint="Waiting for attention"
            icon={Bell}
            tone={data.counts.unread ? "indigo" : "slate"}
          />
          <StatCard
            label="Medicine due today"
            value={data.medicine.dueToday}
            hint={formatDate(data.today)}
            icon={Pill}
            tone={data.medicine.dueToday ? "emerald" : "slate"}
          />
          <StatCard
            label="Still to send"
            value={data.medicine.overdue}
            hint="Past their due date"
            icon={Pill}
            tone={data.medicine.overdue ? "rose" : "emerald"}
          />
        </div>
      )}

      <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
        <Segmented value={filter} onChange={setFilter} options={[...FILTERS]} />
      </div>

      {loading && <Spinner label="Loading notifications" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && !error && items.length === 0 && (
        <Card>
          <EmptyState
            icon={Inbox}
            title="Nothing here"
            description={
              filter === "open"
                ? "You are all caught up. New medicine reminders appear each morning."
                : "No notifications match this filter."
            }
          />
        </Card>
      )}

      {!loading && items.length > 0 && (
        <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-2">
          {items.map((notice) => {
            const open = notice.status === "unread" || notice.status === "read";
            const shortage = notice.isOutOfStock;
            return (
              <motion.div key={notice.id} variants={staggerItem}>
                <div
                  className={cn(
                    "flex gap-4 rounded-2xl border bg-white p-4 shadow-sm transition-colors",
                    notice.status === "unread" ? "border-indigo-200/70" : "border-slate-200/70",
                    !open && "opacity-70"
                  )}
                >
                  <span
                    className={cn(
                      "mt-0.5 grid size-10 shrink-0 place-items-center rounded-full ring-1 ring-inset",
                      shortage
                        ? "bg-amber-50 text-amber-600 ring-amber-100"
                        : "bg-emerald-50 text-emerald-600 ring-emerald-100"
                    )}
                    aria-hidden
                  >
                    {shortage ? (
                      <PackageX className="size-[18px]" strokeWidth={1.9} />
                    ) : (
                      <Pill className="size-[18px]" strokeWidth={1.9} />
                    )}
                  </span>

                  <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                      <p className="text-[13.5px] font-bold text-navy">{notice.title}</p>
                      <Badge tone={toneForStatus(notice.status === "unread" ? "open" : notice.status)}>
                        {statusLabel(notice.status)}
                      </Badge>
                    </div>
                    <p className="mt-1 text-[12.5px] font-medium text-slate-500">{notice.body}</p>
                    <p className="mt-1.5 text-[10.5px] font-black uppercase tracking-[0.14em] text-slate-400">
                      {notice.dueDate ? `Due ${formatDate(notice.dueDate)} · ` : ""}
                      raised {timeAgo(notice.createdAt)}
                      {notice.doneAt ? ` · done ${timeAgo(notice.doneAt)}` : ""}
                    </p>
                  </div>

                  <div className="flex shrink-0 flex-col items-end gap-2 sm:flex-row sm:items-center">
                    {open && notice.isActionable && (
                      <Button size="sm" onClick={() => void openDeliver(notice)}>
                        Mark delivered
                      </Button>
                    )}
                    {notice.href && (
                      <Link
                        href={notice.href}
                        className="inline-flex h-8 items-center gap-1 rounded-lg px-3 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-100 hover:text-navy"
                      >
                        Open <ArrowRight className="size-3" />
                      </Link>
                    )}
                    {open && (
                      <button
                        type="button"
                        onClick={() => void act(notice, "dismiss")}
                        aria-label="Dismiss"
                        className="grid size-8 place-items-center rounded-full text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-600"
                      >
                        <X className="size-4" />
                      </button>
                    )}
                  </div>
                </div>
              </motion.div>
            );
          })}
        </motion.div>
      )}

      <DeliverMedicineModal
        supply={deliverFor}
        open={Boolean(deliverFor)}
        onClose={() => setDeliverFor(null)}
        onDelivered={() => {
          setData(null);
          void reload();
        }}
      />
    </PageShell>
  );
}
