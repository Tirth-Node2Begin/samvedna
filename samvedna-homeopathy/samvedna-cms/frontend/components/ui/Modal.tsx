"use client";

import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import { AnimatePresence, motion } from "framer-motion";
import { X } from "lucide-react";
import { modalBackdrop, modalPanel } from "@/lib/motion";
import { cn } from "@/lib/format";
import { TONE_TILE, type Tone } from "@/lib/tokens";

/**
 * The one modal.
 *
 * Non-negotiables from the style guide, all honoured here:
 *   1. Backdrop is `bg-black/55`, no blur.
 *   2. Portalled to <body> — a `fixed inset-0` overlay declared inside a
 *      `space-y-6` page inherits a 24px top margin and sits off-centre.
 *   3. `z-50` minimum, or it paints under the rail.
 *   4. Scroll is locked on <main> as well as <body>, with the gutter padded
 *      back so the page does not jolt sideways.
 *
 * Anatomy: gradient-wash header with an icon tile, a scrolling body, and a
 * slate footer whose rightmost control is the confirm.
 */
export function Modal({
  open,
  onClose,
  title,
  description,
  icon: Icon,
  tone = "indigo",
  footer,
  width = "md",
  children,
}: {
  open: boolean;
  onClose: () => void;
  title: string;
  description?: string;
  icon?: React.ComponentType<{ className?: string }>;
  tone?: Tone;
  footer?: React.ReactNode;
  width?: "sm" | "md" | "lg" | "xl";
  children: React.ReactNode;
}) {
  const [mounted, setMounted] = useState(false);
  const panelRef = useRef<HTMLDivElement>(null);
  const openerRef = useRef<HTMLElement | null>(null);

  // Callers almost always pass an inline `() => setX(null)`, which is a new
  // function on every parent render. Reading it through a ref keeps the
  // lock/focus effect keyed on `open` alone — otherwise a parent re-render
  // (the header bell polls every minute) tears the effect down and re-runs it
  // mid-dialog, unlocking scroll for a frame and bouncing focus to the opener.
  const onCloseRef = useRef(onClose);
  useEffect(() => {
    onCloseRef.current = onClose;
  }, [onClose]);

  useEffect(() => setMounted(true), []);

  useEffect(() => {
    if (!open) return;

    openerRef.current = document.activeElement as HTMLElement | null;

    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") onCloseRef.current();
    };
    document.addEventListener("keydown", onKey);

    // Lock BOTH the document and the shell's real scroll container, padding
    // back the scrollbar width so nothing shifts sideways.
    const main = document.getElementById("app-scroll");
    const gutter = window.innerWidth - document.documentElement.clientWidth;
    const previous = {
      bodyOverflow: document.body.style.overflow,
      bodyPad: document.body.style.paddingRight,
      mainOverflow: main?.style.overflow ?? "",
    };
    document.body.style.overflow = "hidden";
    if (gutter > 0) document.body.style.paddingRight = `${gutter}px`;
    if (main) main.style.overflow = "hidden";

    panelRef.current?.focus();

    return () => {
      document.removeEventListener("keydown", onKey);
      document.body.style.overflow = previous.bodyOverflow;
      document.body.style.paddingRight = previous.bodyPad;
      if (main) main.style.overflow = previous.mainOverflow;
      // Restore focus only if the opener is still in the document.
      if (openerRef.current?.isConnected) openerRef.current.focus();
    };
  }, [open]);

  if (!mounted) return null;

  const maxWidth = { sm: "max-w-md", md: "max-w-lg", lg: "max-w-2xl", xl: "max-w-4xl" }[width];

  return createPortal(
    <AnimatePresence>
      {open && (
        <motion.div
          className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/55 p-4 pt-[8vh]"
          variants={modalBackdrop}
          initial="hidden"
          animate="show"
          exit="exit"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget) onClose();
          }}
        >
          <motion.div
            ref={panelRef}
            role="dialog"
            aria-modal="true"
            aria-label={title}
            tabIndex={-1}
            className={cn(
              "w-full max-w-[95vw] overflow-hidden rounded-2xl bg-white shadow-2xl outline-none",
              maxWidth
            )}
            variants={modalPanel}
            initial="hidden"
            animate="show"
            exit="exit"
          >
            <header className="flex items-start justify-between gap-4 border-b border-slate-100 bg-gradient-to-br from-indigo-50/70 to-white px-5 py-4 sm:px-6 sm:py-5">
              <div className="flex min-w-0 items-start gap-3">
                {Icon && (
                  <span
                    className={cn(
                      "grid size-10 shrink-0 place-items-center rounded-xl text-white shadow-sm",
                      TONE_TILE[tone]
                    )}
                    aria-hidden
                  >
                    <Icon className="size-[18px]" />
                  </span>
                )}
                <div className="min-w-0">
                  <h2 className="text-base font-bold text-navy">{title}</h2>
                  {description && (
                    <p className="mt-0.5 text-[12.5px] font-medium text-slate-500">{description}</p>
                  )}
                </div>
              </div>

              <button
                type="button"
                onClick={onClose}
                aria-label="Close"
                className="-mr-1 -mt-1 grid size-8 shrink-0 place-items-center rounded-full text-slate-400 transition-colors hover:bg-white hover:text-slate-700"
              >
                <X className="size-4" />
              </button>
            </header>

            <div className="max-h-[62vh] min-h-0 overflow-y-auto p-5 sm:p-6">{children}</div>

            {footer && (
              <footer className="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                {footer}
              </footer>
            )}
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>,
    document.body
  );
}
