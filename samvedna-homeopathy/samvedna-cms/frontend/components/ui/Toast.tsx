"use client";

import { createContext, useCallback, useContext, useMemo, useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2, AlertTriangle, Info as InfoIcon, X } from "lucide-react";
import { cn } from "@/lib/format";

type ToastTone = "success" | "error" | "info";

interface Toast {
  id: number;
  tone: ToastTone;
  message: string;
}

const ToastContext = createContext<{
  toast: (message: string, tone?: ToastTone) => void;
} | null>(null);

const TONE_RAIL: Record<ToastTone, string> = {
  success: "border-l-emerald-500",
  error: "border-l-rose-500",
  info: "border-l-indigo-500",
};

const TONE_TILE: Record<ToastTone, string> = {
  success: "bg-emerald-50 text-emerald-600",
  error: "bg-rose-50 text-rose-600",
  info: "bg-indigo-50 text-indigo-600",
};

/**
 * The result toast: `rounded-2xl bg-white shadow-2xl ring-1 ring-black/5` with a
 * `border-l-4` tone rail and a tinted icon tile. Sits at z-[9999], above every
 * modal — a result must be readable over whatever raised it.
 */
export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const dismiss = useCallback((id: number) => {
    setToasts((current) => current.filter((t) => t.id !== id));
  }, []);

  const toast = useCallback(
    (message: string, tone: ToastTone = "success") => {
      const id = Date.now() + Math.random();
      setToasts((current) => [...current, { id, tone, message }]);
      // Errors stay long enough to read a validation sentence.
      window.setTimeout(() => dismiss(id), tone === "error" ? 6500 : 3800);
    },
    [dismiss]
  );

  const value = useMemo(() => ({ toast }), [toast]);

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div className="pointer-events-none fixed bottom-5 right-5 z-[9999] flex w-[min(92vw,26rem)] flex-col gap-2">
        <AnimatePresence initial={false}>
          {toasts.map((item) => (
            <motion.div
              key={item.id}
              layout
              role="status"
              initial={{ opacity: 0, y: 16, scale: 0.98 }}
              animate={{ opacity: 1, y: 0, scale: 1 }}
              exit={{ opacity: 0, x: 16, transition: { duration: 0.16 } }}
              transition={{ type: "spring", stiffness: 420, damping: 34 }}
              onClick={() => dismiss(item.id)}
              className={cn(
                "pointer-events-auto flex cursor-pointer items-start gap-3 rounded-2xl border-l-4 bg-white px-4 py-3.5",
                "shadow-2xl ring-1 ring-black/5 sm:min-w-[300px]",
                TONE_RAIL[item.tone]
              )}
            >
              <span
                className={cn("grid size-9 shrink-0 place-items-center rounded-xl", TONE_TILE[item.tone])}
                aria-hidden
              >
                {item.tone === "success" && <CheckCircle2 className="size-4" />}
                {item.tone === "error" && <AlertTriangle className="size-4" />}
                {item.tone === "info" && <InfoIcon className="size-4" />}
              </span>

              <p className="flex-1 pt-1 text-[12.5px] font-semibold leading-snug text-slate-700">
                {item.message}
              </p>

              <button
                type="button"
                onClick={(event) => {
                  event.stopPropagation();
                  dismiss(item.id);
                }}
                className="-mr-1 mt-0.5 grid size-6 shrink-0 place-items-center rounded-full text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-600"
                aria-label="Dismiss"
              >
                <X className="size-3.5" />
              </button>
            </motion.div>
          ))}
        </AnimatePresence>
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error("useToast must be used inside <ToastProvider>.");
  }
  return context.toast;
}
