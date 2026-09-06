"use client";

import { CHROME_SHADOW } from "@/lib/tokens";

/**
 * The boot state.
 *
 * Mirrors the floating-chrome geometry exactly — 18px inset, 68px rail,
 * `rounded-[26px]`, a 54px `rounded-[20px]` bar — so the shell does not jump
 * when the real rail and header mount. The rail sits at z-40 here, one below
 * the live rail, so a late mount paints over it cleanly.
 */
export function ShellBootSkeleton() {
  return (
    <div className="flex h-screen w-full overflow-hidden bg-background">
      <div
        className={`fixed inset-y-[18px] left-[18px] z-40 w-[68px] rounded-[26px] bg-[var(--n2b-chrome)] ${CHROME_SHADOW}`}
        aria-hidden
      >
        <div className="flex h-[68px] items-center justify-center">
          <div className="size-[34px] animate-pulse rounded-[11px] bg-slate-200" />
        </div>
        <div className="flex flex-col items-center gap-2 px-3 pt-2">
          {Array.from({ length: 8 }).map((_, index) => (
            <div key={index} className="h-10 w-11 animate-pulse rounded-xl bg-slate-200/70" />
          ))}
        </div>
      </div>

      <div className="ml-0 flex min-w-0 flex-1 flex-col overflow-hidden lg:ml-[var(--n2b-sb)]">
        <div className="shrink-0 px-3 pb-2.5 pt-3 sm:px-[18px] sm:pt-[18px]">
          <div
            className={`mx-auto flex h-[54px] w-full max-w-[1760px] items-center gap-3 rounded-[20px] bg-[var(--n2b-chrome)] px-4 ${CHROME_SHADOW}`}
          >
            <div className="h-4 w-32 animate-pulse rounded-full bg-slate-200" />
            <div className="ml-auto size-8 animate-pulse rounded-full bg-slate-200" />
          </div>
        </div>

        <main className="flex-1 overflow-hidden">
          <div className="mx-auto w-full max-w-[1500px] space-y-6 p-4 sm:p-6 lg:p-8">
            <div className="h-[132px] animate-pulse rounded-3xl bg-white/70" />
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              {Array.from({ length: 4 }).map((_, index) => (
                <div key={index} className="h-[118px] animate-pulse rounded-2xl bg-white/70" />
              ))}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
