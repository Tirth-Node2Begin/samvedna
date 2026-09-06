"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Menu, LogOut, ChevronDown, ShieldCheck, LifeBuoy } from "lucide-react";
import { NotificationBell } from "./NotificationBell";
import { useSession } from "@/lib/session";
import { cn, initials } from "@/lib/format";
import { CHROME_SHADOW } from "@/lib/tokens";
import { toggleSidebar } from "./sidebar-events";

/* --------------------------------------------------------------- crumbs -- */

/** Full path → the page's real name. Used for the LAST crumb. */
const PAGE_LABELS: Record<string, string> = {
  "/dashboard": "Dashboard",
  "/patients": "Patient register",
  "/patients/new": "New intake",
  "/schedule": "Follow-up schedule",
  "/reviews": "Reviews",
  "/escalations": "Escalations",
  "/progress": "Progress reports",
  "/plans": "Care plans",
  "/team": "Team & roles",
  "/activity": "Audit log",
  "/medicine": "Medicine supply",
  "/notifications": "Notifications",
};

/** Per-segment fixes the prettifier cannot infer. */
const SEGMENT_LABELS: Record<string, string> = {
  cms: "CMS",
  plan: "Care plan",
  patients: "Patients",
  reviews: "Reviews",
  escalations: "Escalations",
  progress: "Progress",
};

/** Paths that are real pages, so a parent crumb may link to them. */
const LINKABLE_PARENTS = new Set(Object.keys(PAGE_LABELS));

/**
 * Record ids never reach the trail: bare numbers, uuids, and opaque tokens
 * (≥8 chars carrying a digit and no separator).
 */
function isIdSegment(segment: string): boolean {
  if (/^\d+$/.test(segment)) return true;
  if (/^[0-9a-f]{8}-[0-9a-f]{4}-/i.test(segment)) return true;
  return segment.length >= 8 && /\d/.test(segment) && !/[-_]/.test(segment);
}

function prettifySegment(segment: string): string {
  const fixed = SEGMENT_LABELS[segment];
  if (fixed) return fixed;
  return segment
    .replace(/[-_]/g, " ")
    .replace(/\b\w/g, (character) => character.toUpperCase());
}

interface Crumb {
  label: string;
  href?: string;
}

function buildCrumbs(pathname: string): Crumb[] {
  const segments = pathname.split("/").filter(Boolean).filter((s) => !isIdSegment(s));
  if (segments.length === 0) return [{ label: "Dashboard" }];

  const crumbs: Crumb[] = [];
  let path = "";

  segments.forEach((segment, index) => {
    path += `/${segment}`;
    const last = index === segments.length - 1;
    // The last crumb takes the page's real name; parents take the folder name.
    const label = last ? (PAGE_LABELS[pathname] ?? PAGE_LABELS[path] ?? prettifySegment(segment)) : prettifySegment(segment);
    crumbs.push({ label, href: last || !LINKABLE_PARENTS.has(path) ? undefined : path });
  });

  return crumbs;
}

/* --------------------------------------------------------------- header -- */

/**
 * The floating bar.
 *
 * Same surface, shadow and 18px inset as the rail — the two read as one piece
 * of chrome. It shows *where you are* rather than repeating the page title,
 * because every page already renders its own hero.
 *
 * No z-index: it is a flex sibling above <main> in DOM order, which is enough.
 * A z-50 header would tie with the rail.
 */
export function AppHeader() {
  const pathname = usePathname();
  const { user, signOut, demo } = useSession();
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  const crumbs = useMemo(() => buildCrumbs(pathname), [pathname]);

  useEffect(() => {
    if (!menuOpen) return;
    const onClick = (event: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setMenuOpen(false);
      }
    };
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setMenuOpen(false);
    };
    document.addEventListener("mousedown", onClick);
    document.addEventListener("keydown", onKey);
    return () => {
      document.removeEventListener("mousedown", onClick);
      document.removeEventListener("keydown", onKey);
    };
  }, [menuOpen]);

  useEffect(() => setMenuOpen(false), [pathname]);

  return (
    <header className="shrink-0 px-3 pb-2.5 pt-3 sm:px-[18px] sm:pt-[18px]">
      <div
        className={cn(
          "mx-auto flex h-[54px] w-full max-w-[1760px] items-center gap-1.5 rounded-[20px]",
          "bg-[var(--n2b-chrome)] px-2 sm:gap-3 sm:px-4",
          CHROME_SHADOW
        )}
      >
        <button
          type="button"
          onClick={toggleSidebar}
          aria-label="Open navigation"
          className="grid size-9 shrink-0 place-items-center rounded-full text-slate-500 transition-colors hover:bg-white hover:text-slate-900 lg:hidden"
        >
          <Menu className="size-4" />
        </button>

        {/* Breadcrumb — plain slash separators, no chevrons, no icons. */}
        <nav aria-label="Breadcrumb" className="flex min-w-0 items-center gap-1.5">
          {crumbs.map((crumb, index) => {
            const last = index === crumbs.length - 1;
            return (
              <span key={`${crumb.label}-${index}`} className="flex min-w-0 items-center gap-1.5">
                {index > 0 && (
                  <span
                    aria-hidden
                    className="hidden text-[13px] font-light text-slate-300 sm:inline"
                  >
                    /
                  </span>
                )}
                {last ? (
                  <span className="truncate text-[15px] font-semibold tracking-tight text-slate-900">
                    {crumb.label}
                  </span>
                ) : crumb.href ? (
                  <Link
                    href={crumb.href}
                    className="hidden truncate text-[13px] font-medium text-slate-400 transition-colors hover:text-slate-700 sm:inline"
                  >
                    {crumb.label}
                  </Link>
                ) : (
                  <span className="hidden truncate text-[13px] font-medium text-slate-400 sm:inline">
                    {crumb.label}
                  </span>
                )}
              </span>
            );
          })}
        </nav>

        {/* Action cluster */}
        <div className="ml-auto flex shrink-0 items-center gap-1.5 sm:gap-2">
          {demo && (
            <span className="hidden items-center gap-1.5 rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-200 xl:inline-flex">
              <ShieldCheck className="size-3" aria-hidden /> Demo · seeded data
            </span>
          )}

          <button
            type="button"
            aria-label="Support"
            className="hidden size-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 transition-colors hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600 sm:grid"
          >
            <LifeBuoy className="size-4" />
          </button>

          <NotificationBell />

          <span className="mx-0.5 hidden h-7 w-px bg-slate-200/80 sm:block" aria-hidden />

          {user && (
            <div className="relative" ref={menuRef}>
              <button
                type="button"
                onClick={() => setMenuOpen((open) => !open)}
                className="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-2 transition-colors hover:border-indigo-300 sm:pr-3.5"
              >
                <span className="relative shrink-0">
                  <span className="grid size-8 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-[13px] font-bold text-white">
                    {initials(user.name)}
                  </span>
                  <span
                    className="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-white bg-emerald-400"
                    aria-hidden
                  />
                </span>
                <span className="hidden text-left leading-tight sm:block">
                  <span className="block text-[12.5px] font-bold text-slate-900">{user.name}</span>
                  <span className="block text-[10.5px] font-medium text-slate-400">
                    {user.roleLabel}
                  </span>
                </span>
                <ChevronDown
                  className={cn(
                    "hidden size-3.5 text-slate-400 transition-transform sm:block",
                    menuOpen && "rotate-180"
                  )}
                />
              </button>

              {menuOpen && (
                <div className="absolute right-0 top-full z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-xl shadow-slate-900/10 motion-safe:animate-pop-in">
                  <div className="border-b border-slate-100 px-4 py-3.5">
                    <p className="text-[13px] font-bold text-slate-900">{user.name}</p>
                    <p className="mt-0.5 text-[11.5px] font-medium text-slate-500">
                      {user.title || user.roleLabel}
                    </p>
                    <p className="mt-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                      @{user.username}
                    </p>
                  </div>
                  <Link
                    href="/team"
                    className="block px-4 py-2.5 text-[13px] font-medium text-slate-700 transition-colors hover:bg-slate-50"
                  >
                    Team &amp; roles
                  </Link>
                  <button
                    type="button"
                    onClick={() => void signOut()}
                    className="flex w-full items-center gap-2 border-t border-slate-100 px-4 py-2.5 text-left text-[13px] font-bold text-rose-600 transition-colors hover:bg-rose-50"
                  >
                    <LogOut className="size-3.5" /> Sign out
                  </button>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
