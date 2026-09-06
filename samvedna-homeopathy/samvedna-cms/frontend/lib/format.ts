import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/** "12 Mar 2018" */
export function formatDate(value?: string | null): string {
  if (!value) return "—";
  const date = new Date(value.includes(" ") ? value.replace(" ", "T") : value);
  if (Number.isNaN(date.getTime())) return "—";
  return date.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
}

/** "12 Mar, 4:30 pm" */
export function formatDateTime(value?: string | null): string {
  if (!value) return "—";
  const date = new Date(value.includes(" ") ? value.replace(" ", "T") : value);
  if (Number.isNaN(date.getTime())) return "—";
  return date.toLocaleString("en-IN", {
    day: "numeric",
    month: "short",
    hour: "numeric",
    minute: "2-digit",
  });
}

/** "3 days ago" / "in 5 days" */
export function relativeDays(days: number): string {
  if (days === 0) return "today";
  if (days === 1) return "tomorrow";
  if (days === -1) return "yesterday";
  return days > 0 ? `in ${days} days` : `${Math.abs(days)} days ago`;
}

export function timeAgo(value?: string | null): string {
  if (!value) return "—";
  const date = new Date(value.includes(" ") ? value.replace(" ", "T") : value);
  if (Number.isNaN(date.getTime())) return "—";
  const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
  if (seconds < 60) return "just now";
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  if (days < 30) return `${days}d ago`;
  return formatDate(value);
}

/** Today as YYYY-MM-DD in the local timezone (not UTC — toISOString shifts the day). */
export function todayISO(): string {
  const now = new Date();
  const offset = now.getTimezoneOffset() * 60000;
  return new Date(now.getTime() - offset).toISOString().slice(0, 10);
}

export function addDaysISO(iso: string, days: number): string {
  const date = new Date(`${iso}T00:00:00`);
  date.setDate(date.getDate() + days);
  const offset = date.getTimezoneOffset() * 60000;
  return new Date(date.getTime() - offset).toISOString().slice(0, 10);
}

export function initials(name: string): string {
  return name
    .replace(/^(Dr\.?|Mr\.?|Mrs\.?|Ms\.?)\s+/i, "")
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? "")
    .join("");
}

/** "continued_adjusted" -> "Continued adjusted" */
export function humanise(value?: string | null): string {
  if (!value) return "—";
  const spaced = value.replace(/_/g, " ");
  return spaced.charAt(0).toUpperCase() + spaced.slice(1);
}

export function statusLabel(status: string): string {
  const map: Record<string, string> = {
    draft: "Draft",
    active: "Active",
    on_hold: "On hold",
    completed: "Completed",
    discharged: "Discharged",
    scheduled: "Scheduled",
    upcoming: "Upcoming",
    done: "Done",
    missed: "Missed",
    rescheduled: "Rescheduled",
    awaiting_signoff: "Awaiting sign-off",
    closed: "Closed",
    open: "Open",
    in_progress: "In progress",
    resolved: "Resolved",
    pending: "Pending",
    signed: "Signed",
    progressing: "Progressing",
    achieved: "Achieved",
    standby: "Standby",
    reviewing: "Reviewing",
    approved: "Approved",
  };
  return map[status] ?? humanise(status);
}
