"use client";

import { useEffect } from "react";
import { usePathname, useRouter } from "next/navigation";
import { AnimatePresence, motion } from "framer-motion";
import { useSession } from "@/lib/session";
import { Sidebar } from "@/components/layout/Sidebar";
import { AppHeader } from "@/components/layout/AppHeader";
import { ShellBootSkeleton } from "@/components/layout/ShellBootSkeleton";
import { pageTransition } from "@/lib/motion";
import { api } from "@/lib/api";
import { useToast } from "@/components/ui/Toast";

/**
 * The signed-in shell.
 *
 * Three facts that break code if you forget them:
 *   1. `<main>` is the real scroll container, not the document. Anything that
 *      locks scroll or measures the gutter must account for it.
 *   2. The left margin is `lg:ml-[var(--n2b-sb)]` and only the *pinned* rail
 *      width counts — a hover-expand overlays the content deliberately.
 *   3. `--n2b-sb` has a static 86px default, so the first paint is already
 *      correctly offset before the rail hydrates.
 */
export default function WorkspaceLayout({ children }: { children: React.ReactNode }) {
  const { user, loading } = useSession();
  const router = useRouter();
  const pathname = usePathname();
  const toast = useToast();

  // The morning greeting: once per calendar day, on the first page after sign-in,
  // say how many patients need medicine today. localStorage keeps it to one.
  useEffect(() => {
    if (!user) return;
    const key = `samvedna_morning_${new Date().toISOString().slice(0, 10)}`;
    try {
      if (window.localStorage.getItem(key)) return;
    } catch {
      return;
    }
    void api
      .get<{ medicine: { dueToday: number; overdue: number } }>("/notifications/summary")
      .then(({ medicine }) => {
        try {
          window.localStorage.setItem(key, "1");
        } catch {
          /* private mode — we will just greet again next load */
        }
        const total = medicine.dueToday + medicine.overdue;
        if (total === 0) return;
        const parts = [];
        if (medicine.dueToday) parts.push(`${medicine.dueToday} due today`);
        if (medicine.overdue) parts.push(`${medicine.overdue} overdue`);
        toast(`Good morning — medicine to send: ${parts.join(", ")}. Open the bell or the Medicine board.`, "info");
      })
      .catch(() => undefined);
  }, [user, toast]);

  useEffect(() => {
    if (!loading && !user) {
      router.replace("/login");
    }
  }, [loading, user, router]);

  // The boot skeleton mirrors the floating-chrome geometry exactly, so the
  // shell does not jump when the real rail and bar mount.
  if (loading || !user) {
    return <ShellBootSkeleton />;
  }

  return (
    <div className="flex h-screen w-full overflow-hidden bg-background">
      <Sidebar />

      <div className="ml-0 flex min-w-0 flex-1 flex-col overflow-hidden transition-[margin] duration-[260ms] ease-[cubic-bezier(0.32,0.72,0,1)] motion-reduce:transition-none lg:ml-[var(--n2b-sb)]">
        <AppHeader />

        <main id="app-scroll" className="flex-1 overflow-y-auto">
          <AnimatePresence mode="wait">
            <motion.div key={pathname} variants={pageTransition} initial="hidden" animate="show">
              {children}
            </motion.div>
          </AnimatePresence>
        </main>
      </div>
    </div>
  );
}
