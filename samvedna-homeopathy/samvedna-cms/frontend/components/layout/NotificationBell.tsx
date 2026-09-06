"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Bell, Pill, PackageX, CheckCheck, X, ArrowRight } from "lucide-react";
import { api } from "@/lib/api";
import { cn, timeAgo } from "@/lib/format";
import { DeliverMedicineModal } from "@/components/flow/DeliverMedicineModal";
import type { AppNotification, MedicineSupply, NotificationsPayload } from "@/types";

const POLL_MS = 60_000;

/**
 * The header bell.
 *
 * Polls the summary every minute so the badge stays live without a socket,
 * and loads the full list only when the panel opens. Medicine notices carry a
 * one-click "Mark delivered" so the morning routine is: open bell, tick, done.
 */
export function NotificationBell() {
  const pathname = usePathname();
  const [unread, setUnread] = useState(0);
  const [open, setOpen] = useState(false);
  const [items, setItems] = useState<AppNotification[]>([]);
  const [loading, setLoading] = useState(false);
  const [deliverFor, setDeliverFor] = useState<MedicineSupply | null>(null);
  const panelRef = useRef<HTMLDivElement>(null);

  const refreshCount = useCallback(async () => {
    try {
      const summary = await api.get<{ counts: { unread: number } }>("/notifications/summary");
      setUnread(summary.counts.unread);
    } catch {
      /* the bell simply keeps its last count when the API is unreachable */
    }
  }, []);

  const loadList = useCallback(async () => {
    setLoading(true);
    try {
      const payload = await api.get<NotificationsPayload>("/notifications?limit=12");
      setItems(payload.notifications);
      setUnread(payload.counts.unread);
    } catch {
      /* leave the last list in place */
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void refreshCount();
    const timer = window.setInterval(() => void refreshCount(), POLL_MS);
    return () => window.clearInterval(timer);
  }, [refreshCount]);

  // Route changes close the panel; a fresh page should not carry it over.
  useEffect(() => setOpen(false), [pathname]);

  useEffect(() => {
    if (!open) return;
    void loadList();
    const onClick = (event: MouseEvent) => {
      if (panelRef.current && !panelRef.current.contains(event.target as Node)) setOpen(false);
    };
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setOpen(false);
    };
    document.addEventListener("mousedown", onClick);
    document.addEventListener("keydown", onKey);
    return () => {
      document.removeEventListener("mousedown", onClick);
      document.removeEventListener("keydown", onKey);
    };
  }, [open, loadList]);

  async function markRead(id: number) {
    setItems((current) => current.map((n) => (n.id === id ? { ...n, status: "read" } : n)));
    try {
      const r = await api.post<{ counts: { unread: number } }>(`/notifications/${id}/read`);
      setUnread(r.counts.unread);
    } catch {
      /* optimistic update stands */
    }
  }

  async function dismiss(id: number) {
    setItems((current) => current.filter((n) => n.id !== id));
    try {
      const r = await api.post<{ counts: { unread: number } }>(`/notifications/${id}/dismiss`);
      setUnread(r.counts.unread);
    } catch {
      /* optimistic update stands */
    }
  }

  async function markAllRead() {
    setItems((current) => current.map((n) => (n.status === "unread" ? { ...n, status: "read" } : n)));
    try {
      const r = await api.post<{ counts: { unread: number } }>("/notifications/read-all");
      setUnread(r.counts.unread);
    } catch {
      /* optimistic update stands */
    }
  }

  /** Open the delivery dialog straight from a medicine notice. */
  async function openDeliver(notice: AppNotification) {
    if (!notice.caseId) return;
    try {
      const payload = await api.get<{ supply: MedicineSupply | null; patient: { childName: string; code: string } }>(
        `/medicine/${notice.caseId}`
      );
      if (payload.supply) {
        setDeliverFor({ ...payload.supply, patientName: payload.patient.childName, patientCode: payload.patient.code });
        setOpen(false);
      }
    } catch {
      /* the page-level toast will surface any API error on submit */
    }
  }

  const openItems = items.filter((n) => n.status === "unread" || n.status === "read");

  return (
    <>
      <div className="relative" ref={panelRef}>
        <button
          type="button"
          onClick={() => setOpen((value) => !value)}
          aria-label={unread ? `${unread} unread notifications` : "Notifications"}
          aria-expanded={open}
          className={cn(
            "relative grid size-10 place-items-center rounded-full border bg-white transition-colors",
            open
              ? "border-indigo-300 bg-indigo-50 text-indigo-600"
              : "border-slate-200 text-slate-500 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600"
          )}
        >
          <Bell className="size-4" />
          {unread > 0 && (
            <span className="absolute -right-1 -top-1 grid h-[18px] min-w-[18px] place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-black tabular-nums text-white ring-2 ring-white">
              {unread > 9 ? "9+" : unread}
            </span>
          )}
        </button>

        {open && (
          <div className="absolute right-0 top-full z-50 mt-2 w-[min(92vw,26rem)] overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-xl shadow-slate-900/10 motion-safe:animate-pop-in">
            <header className="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-br from-indigo-50/70 to-white px-4 py-3">
              <div>
                <p className="text-[13px] font-bold text-navy">Notifications</p>
                <p className="text-[11px] font-medium text-slate-500">
                  {unread ? `${unread} unread` : "You are all caught up"}
                </p>
              </div>
              {unread > 0 && (
                <button
                  type="button"
                  onClick={() => void markAllRead()}
                  className="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold text-indigo-600 transition-colors hover:bg-indigo-50"
                >
                  <CheckCheck className="size-3.5" /> Mark all read
                </button>
              )}
            </header>

            <div className="hide-scrollbar max-h-[60vh] overflow-y-auto">
              {loading && items.length === 0 && (
                <p className="px-4 py-8 text-center text-[12px] font-medium text-slate-400">Loading…</p>
              )}

              {!loading && openItems.length === 0 && (
                <div className="px-4 py-9 text-center">
                  <Bell className="mx-auto size-7 text-slate-300" />
                  <p className="mt-2 text-[12.5px] font-semibold text-slate-500">Nothing needs you right now</p>
                  <p className="mt-0.5 text-[11px] font-medium text-slate-400">
                    Medicine reminders appear here each morning.
                  </p>
                </div>
              )}

              <ul className="divide-y divide-slate-100">
                {openItems.map((notice) => {
                  const shortage = notice.isOutOfStock;
                  return (
                    <li
                      key={notice.id}
                      className={cn(
                        "group flex gap-3 px-4 py-3 transition-colors hover:bg-slate-50/70",
                        notice.status === "unread" && "bg-indigo-50/30"
                      )}
                      onMouseEnter={() => {
                        if (notice.status === "unread") void markRead(notice.id);
                      }}
                    >
                      <span
                        className={cn(
                          "mt-0.5 grid size-9 shrink-0 place-items-center rounded-full ring-1 ring-inset",
                          shortage
                            ? "bg-amber-50 text-amber-600 ring-amber-100"
                            : "bg-emerald-50 text-emerald-600 ring-emerald-100"
                        )}
                        aria-hidden
                      >
                        {shortage ? <PackageX className="size-4" /> : <Pill className="size-4" />}
                      </span>

                      <div className="min-w-0 flex-1">
                        <p className="text-[12.5px] font-bold leading-snug text-navy">{notice.title}</p>
                        <p className="mt-0.5 text-[11.5px] font-medium leading-snug text-slate-500">
                          {notice.body}
                        </p>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                          {notice.isActionable && (
                            <button
                              type="button"
                              onClick={() => void openDeliver(notice)}
                              className="inline-flex items-center gap-1 rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white transition-colors hover:bg-emerald-700"
                            >
                              Mark delivered
                            </button>
                          )}
                          {notice.href && (
                            <Link
                              href={notice.href}
                              className="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[11px] font-bold text-slate-500 transition-colors hover:bg-slate-100 hover:text-navy"
                            >
                              Open <ArrowRight className="size-3" />
                            </Link>
                          )}
                          <span className="ml-auto text-[10.5px] font-medium text-slate-400">
                            {timeAgo(notice.createdAt)}
                          </span>
                        </div>
                      </div>

                      <button
                        type="button"
                        onClick={() => void dismiss(notice.id)}
                        aria-label="Dismiss"
                        className="grid size-7 shrink-0 place-items-center self-start rounded-full text-slate-300 opacity-0 transition-all hover:bg-slate-100 hover:text-slate-600 group-hover:opacity-100"
                      >
                        <X className="size-3.5" />
                      </button>
                    </li>
                  );
                })}
              </ul>
            </div>

            <footer className="border-t border-slate-100 bg-slate-50 px-4 py-2.5 text-center">
              <Link
                href="/notifications"
                className="text-[12px] font-bold text-indigo-600 transition-colors hover:text-indigo-700"
              >
                View all notifications
              </Link>
            </footer>
          </div>
        )}
      </div>

      <DeliverMedicineModal
        supply={deliverFor}
        open={Boolean(deliverFor)}
        onClose={() => setDeliverFor(null)}
        onDelivered={() => void refreshCount()}
      />
    </>
  );
}
