"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  LayoutDashboard,
  Users,
  CalendarClock,
  ClipboardCheck,
  TriangleAlert,
  TrendingUp,
  Package,
  UserCog,
  ScrollText,
  Pill,
  Bell,
  ChevronLeft,
  ChevronRight,
  Search,
  LogOut,
  X,
} from "lucide-react";
import { cn, initials } from "@/lib/format";
import { useSession } from "@/lib/session";
import {
  CHROME_CURVE,
  CHROME_SHADOW,
  CHROME_SHADOW_LG,
  COLLAPSED_W,
  EXPANDED_W,
  GUTTER,
} from "@/lib/tokens";
import { onSidebarToggle } from "./sidebar-events";

/** 44px — the icon slot every glyph in the rail parks in (68 − 2×12 card padding). */
const ICON_SLOT = COLLAPSED_W - 24;

const PIN_KEY = "samvedna_sidebar_pinned";

interface NavItem {
  href: string;
  label: string;
  icon: React.ComponentType<{ className?: string; strokeWidth?: number }>;
  /** The flow step this screen implements, shown only when expanded. */
  step?: number;
}

const NAV: { group: string; items: NavItem[] }[] = [
  {
    group: "Overview",
    items: [
      { href: "/dashboard", label: "Dashboard", icon: LayoutDashboard },
      { href: "/notifications", label: "Notifications", icon: Bell },
    ],
  },
  {
    group: "Care flow",
    items: [
      { href: "/patients", label: "Patients", icon: Users, step: 1 },
      { href: "/schedule", label: "Follow-up schedule", icon: CalendarClock, step: 3 },
      { href: "/medicine", label: "Medicine supply", icon: Pill },
      { href: "/reviews", label: "Reviews", icon: ClipboardCheck, step: 4 },
      { href: "/escalations", label: "Escalations", icon: TriangleAlert, step: 5 },
      { href: "/progress", label: "Progress reports", icon: TrendingUp, step: 6 },
    ],
  },
  {
    group: "Practice",
    items: [
      { href: "/plans", label: "Care plans", icon: Package, step: 2 },
      { href: "/team", label: "Team & roles", icon: UserCog },
      { href: "/activity", label: "Audit log", icon: ScrollText },
    ],
  },
];

/**
 * The floating rail.
 *
 * A card, not a flush column: inset 18px on all sides, `rounded-[26px]`, chrome
 * surface, chrome shadow, no border. 68px collapsed ⇄ 272px expanded, and a
 * hover-expand *overlays* the page rather than reflowing it — only the pinned
 * width is written to `--n2b-sb`.
 *
 * Deliberately monochrome. No indigo, no accent bar, no ring, in any state —
 * the calm is the design. Colour in this app belongs to the content.
 */
export function Sidebar() {
  const pathname = usePathname();
  const { user, signOut } = useSession();

  // Read the stored pin lazily in the initializer. The rail is client-only, so
  // there is no hydration pass to mismatch, and setState-in-effect is banned.
  const [pinned, setPinned] = useState(() => {
    if (typeof window === "undefined") return false;
    try {
      return window.localStorage.getItem(PIN_KEY) === "1";
    } catch {
      return false;
    }
  });
  const [hovered, setHovered] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);

  const expanded = pinned || hovered || mobileOpen;

  /* The shell margin follows the *pinned* width only. */
  useEffect(() => {
    document.documentElement.style.setProperty(
      "--n2b-sb",
      `${(pinned ? EXPANDED_W : COLLAPSED_W) + GUTTER * 2}px`
    );
  }, [pinned]);

  useEffect(() => onSidebarToggle(() => setMobileOpen((open) => !open)), []);

  // Escape closes the drawer; the route changing closes it too.
  useEffect(() => {
    if (!mobileOpen) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setMobileOpen(false);
    };
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [mobileOpen]);

  useEffect(() => {
    setMobileOpen(false);
  }, [pathname]);

  const togglePin = () => {
    setPinned((current) => {
      const next = !current;
      try {
        window.localStorage.setItem(PIN_KEY, next ? "1" : "0");
      } catch {
        /* private mode — the rail just forgets between sessions */
      }
      return next;
    });
  };

  // Longest matching prefix, so /patients does not also light up on /patients/new
  // when a more specific item exists.
  const activeHref = NAV.flatMap((section) => section.items)
    .map((item) => item.href)
    .filter((href) => pathname === href || pathname.startsWith(`${href}/`))
    .sort((a, b) => b.length - a.length)[0];

  return (
    <>
      {/* Mobile backdrop — always mounted so it can fade BOTH ways. */}
      <div
        onClick={() => setMobileOpen(false)}
        aria-hidden
        className={cn(
          "fixed inset-0 z-40 bg-slate-900/40 transition-opacity duration-200 lg:hidden",
          mobileOpen ? "opacity-100 backdrop-blur-sm" : "pointer-events-none opacity-0"
        )}
      />

      <aside
        onMouseEnter={() => setHovered(true)}
        onMouseLeave={() => setHovered(false)}
        style={{ ["--rail-w" as string]: `${expanded ? EXPANDED_W : COLLAPSED_W}px` }}
        className={cn(
          "fixed z-50 flex flex-col overflow-visible rounded-[26px] bg-[var(--n2b-chrome)]",
          "inset-y-[18px] left-[18px]",
          // Mobile: a fixed-width drawer that only ever slides.
          "w-[272px] max-w-[calc(100vw-36px)]",
          // Desktop: width is a CSS var so the mobile drawer keeps a fixed width.
          "lg:w-[var(--rail-w)]",
          "transition-[width,transform,box-shadow]",
          CHROME_CURVE,
          expanded ? CHROME_SHADOW_LG : CHROME_SHADOW,
          mobileOpen ? "translate-x-0" : "-translate-x-[120%] lg:translate-x-0"
        )}
      >
        {/* Brand — fixed 68px slot, glyph parked in the same ICON_SLOT as every icon */}
        <div className="flex h-[68px] shrink-0 items-center px-3">
          <Link href="/dashboard" className="flex items-center overflow-hidden">
            <span
              style={{ width: ICON_SLOT }}
              className="flex shrink-0 items-center justify-center"
            >
              <span className="grid size-[34px] place-items-center rounded-[11px] bg-gradient-to-br from-indigo-500 to-indigo-700 text-[15px] font-black text-white ring-1 ring-slate-900/10">
                S
              </span>
            </span>
            <Reveal open={expanded}>
              <span className="block whitespace-nowrap leading-tight">
                <span className="block text-[14px] font-bold tracking-tight text-slate-900">
                  Samvedna CMS
                </span>
                <span className="block text-[10.5px] font-medium text-slate-400">
                  Case management
                </span>
              </span>
            </Reveal>
          </Link>

          <button
            type="button"
            onClick={() => setMobileOpen(false)}
            aria-label="Close navigation"
            className="ml-auto grid size-8 shrink-0 place-items-center rounded-full text-slate-400 transition-colors hover:bg-white hover:text-slate-700 lg:hidden"
          >
            <X className="size-4" />
          </button>
        </div>

        {/* Search — fixed 52px slot; collapses to the icon slot */}
        <div className="flex h-[52px] shrink-0 items-center px-3">
          <div
            className={cn(
              "flex h-10 items-center overflow-hidden rounded-xl border border-slate-200/80 bg-white",
              "transition-[width]",
              CHROME_CURVE,
              expanded ? "w-full" : "w-11"
            )}
          >
            <span style={{ width: 42 }} className="flex shrink-0 items-center justify-center">
              <Search className="size-[15px] text-slate-400" strokeWidth={1.9} />
            </span>
            <input
              placeholder="Search"
              tabIndex={expanded ? 0 : -1}
              aria-hidden={!expanded}
              className="w-full min-w-0 border-0 bg-transparent pr-3 text-[13px] font-medium text-slate-700 outline-none placeholder:text-slate-400"
            />
          </div>
        </div>

        {/* Nav */}
        <nav className="hide-scrollbar min-h-0 flex-1 touch-pan-y overflow-y-auto overscroll-contain px-3 pb-2">
          {NAV.map((section) => (
            <div key={section.group} className="mb-3">
              <div className="flex h-[18px] items-center px-2.5">
                {expanded ? (
                  <span className="whitespace-nowrap text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                    {section.group}
                  </span>
                ) : (
                  <span className="mx-auto h-px w-4 rounded-full bg-slate-300" aria-hidden />
                )}
              </div>

              <ul className="mt-1 flex flex-col gap-0.5">
                {section.items.map((item) => {
                  const active = item.href === activeHref;
                  return (
                    <li key={item.href}>
                      <NavRow item={item} active={active} expanded={expanded} />
                    </li>
                  );
                })}
              </ul>
            </div>
          ))}
        </nav>

        {/* Profile card */}
        {user && (
          <div className="shrink-0 p-3 pt-1">
            <div className="flex items-center overflow-hidden rounded-xl border border-slate-200/80 bg-white p-[5px]">
              <span className="relative shrink-0">
                <span className="grid size-8 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-[12px] font-bold text-white">
                  {initials(user.name)}
                </span>
                <span
                  className="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-white bg-emerald-400"
                  aria-hidden
                />
              </span>
              <Reveal open={expanded} maxWidth={168}>
                <span className="block whitespace-nowrap pl-2.5 leading-tight">
                  <span className="block truncate text-[12.5px] font-bold text-slate-900">
                    {user.name}
                  </span>
                  <span className="block truncate text-[10.5px] font-medium text-slate-400">
                    {user.roleLabel}
                  </span>
                </span>
              </Reveal>
              {expanded && (
                <button
                  type="button"
                  onClick={() => void signOut()}
                  aria-label="Sign out"
                  className="ml-auto mr-1 grid size-7 shrink-0 place-items-center rounded-full text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600"
                >
                  <LogOut className="size-3.5" />
                </button>
              )}
            </div>
          </div>
        )}

        {/* Pin chevron */}
        <button
          type="button"
          onClick={togglePin}
          aria-pressed={pinned}
          aria-label={pinned ? "Collapse menu" : "Keep menu open"}
          className="absolute -right-3 top-[26px] hidden size-6 place-items-center rounded-full border border-slate-200/90 bg-white text-slate-400 shadow-[0_2px_8px_-2px_rgba(15,23,42,0.16)] transition-colors hover:text-slate-700 lg:grid"
        >
          {pinned ? <ChevronLeft className="size-3.5" /> : <ChevronRight className="size-3.5" />}
        </button>
      </aside>
    </>
  );
}

/* ------------------------------------------------------------------- row -- */

function NavRow({
  item,
  active,
  expanded,
}: {
  item: NavItem;
  active: boolean;
  expanded: boolean;
}) {
  const ref = useRef<HTMLAnchorElement>(null);

  /**
   * The active row already wears its settled fill, so hover has nothing to
   * change — it reads dead. A cursor-tracked light sits on top instead.
   *
   * Pointer position is written to CSS custom properties on the row, never to
   * React state: a setState per mousemove would re-render the whole nav ~60×/s.
   */
  const onMove = useCallback((event: React.MouseEvent<HTMLAnchorElement>) => {
    const rect = event.currentTarget.getBoundingClientRect();
    event.currentTarget.style.setProperty("--spot-x", `${event.clientX - rect.left}px`);
    event.currentTarget.style.setProperty("--spot-y", `${event.clientY - rect.top}px`);
  }, []);

  return (
    <Link
      ref={ref}
      href={item.href}
      onMouseMove={onMove}
      aria-current={active ? "page" : undefined}
      title={expanded ? undefined : item.label}
      className={cn(
        "group relative flex h-10 w-full items-center overflow-hidden rounded-xl text-[13.5px] outline-none",
        "transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-slate-900/10",
        active
          ? "bg-[var(--n2b-chrome-active)] font-medium text-slate-900"
          : "font-normal text-slate-500 hover:bg-[var(--n2b-chrome-hover)] hover:text-slate-900"
      )}
    >
      {/* Spotlight — tiny alphas, immediate falloff, slow fade. No specular core. */}
      <span
        aria-hidden
        className="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-[450ms] ease-out group-hover:opacity-100"
        style={{
          background:
            "radial-gradient(110px circle at var(--spot-x,50%) var(--spot-y,50%), rgba(139,92,246,0.07), transparent 70%)," +
            "radial-gradient(210px circle at var(--spot-x,50%) var(--spot-y,50%), rgba(99,102,241,0.04), transparent 78%)",
        }}
      />

      <span style={{ width: ICON_SLOT }} className="relative flex shrink-0 justify-center">
        <item.icon
          className={cn(
            "size-[18px] transition-colors",
            active ? "text-slate-900" : "text-slate-400 group-hover:text-slate-700"
          )}
          strokeWidth={active ? 1.9 : 1.6}
        />
      </span>

      <Reveal open={expanded} maxWidth={190}>
        <span className="relative flex items-center gap-2 whitespace-nowrap">
          {item.label}
          {item.step && (
            <span
              title={`Flow step ${item.step}`}
              className={cn(
                "rounded-full px-1.5 text-[9.5px] font-bold tabular-nums",
                active ? "bg-white/70 text-slate-500" : "bg-slate-200/60 text-slate-400"
              )}
            >
              {item.step}
            </span>
          )}
        </span>
      </Reveal>
    </Link>
  );
}

/* ---------------------------------------------------------------- reveal -- */

/**
 * One shared collapse for every text run in the rail — nav label, group heading,
 * profile block — so the whole card reads as a single motion.
 *
 * No padding on the child: `max-w-0` clips text, but padding still adds real
 * width and would overflow the 68px rail.
 */
function Reveal({
  open,
  maxWidth = 190,
  children,
}: {
  open: boolean;
  maxWidth?: number;
  children: React.ReactNode;
}) {
  return (
    <span
      style={{ maxWidth: open ? maxWidth : 0 }}
      className={cn(
        "block overflow-hidden [will-change:max-width,opacity,transform]",
        "transition-[max-width,opacity,transform]",
        CHROME_CURVE,
        open ? "opacity-100 delay-[60ms]" : "-translate-x-1 opacity-0"
      )}
    >
      {children}
    </span>
  );
}
