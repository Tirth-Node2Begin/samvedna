"use client";

import { AnimatePresence, motion } from "framer-motion";
import { useLenis } from "lenis/react";
import { X } from "lucide-react";
import {
  useCallback,
  useEffect,
  useRef,
  useState,
  type KeyboardEvent,
  type ReactNode
} from "react";
import { createPortal } from "react-dom";
import useReducedMotion from "@/hooks/useReducedMotion";
import { cn } from "@/lib/utils";

const FOCUSABLE_SELECTOR =
  'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

type ModalProps = {
  open: boolean;
  onClose: () => void;
  children: ReactNode;
  /** id of the element labelling the dialog (for aria-labelledby). */
  labelledBy?: string;
  /** Accessible name when there is no visible heading to reference. */
  ariaLabel?: string;
  /** Extra classes for the dialog panel. */
  className?: string;
  showCloseButton?: boolean;
};

export default function Modal({
  open,
  onClose,
  children,
  labelledBy,
  ariaLabel,
  className,
  showCloseButton = true
}: ModalProps) {
  const prefersReducedMotion = useReducedMotion();
  const panelRef = useRef<HTMLDivElement>(null);
  const previouslyFocused = useRef<HTMLElement | null>(null);
  const lenis = useLenis();
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  // Lock background scroll, pause Lenis and move focus into the dialog.
  useEffect(() => {
    if (!open) return;

    previouslyFocused.current = document.activeElement as HTMLElement | null;
    const { body } = document;
    const previousOverflow = body.style.overflow;
    body.style.overflow = "hidden";
    lenis?.stop();

    const focusTimer = window.setTimeout(() => {
      const panel = panelRef.current;
      const first = panel?.querySelector<HTMLElement>(FOCUSABLE_SELECTOR);
      (first ?? panel)?.focus();
    }, 0);

    return () => {
      window.clearTimeout(focusTimer);
      body.style.overflow = previousOverflow;
      lenis?.start();
      previouslyFocused.current?.focus?.();
    };
  }, [open, lenis]);

  const handleKeyDown = useCallback(
    (event: KeyboardEvent<HTMLDivElement>) => {
      if (event.key === "Escape") {
        event.stopPropagation();
        onClose();
        return;
      }

      if (event.key !== "Tab") return;

      const panel = panelRef.current;
      if (!panel) return;

      const focusable = Array.from(
        panel.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR)
      ).filter((el) => el.offsetParent !== null || el === document.activeElement);

      if (focusable.length === 0) {
        event.preventDefault();
        panel.focus();
        return;
      }

      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      const active = document.activeElement as HTMLElement | null;

      if (event.shiftKey) {
        if (active === first || !panel.contains(active)) {
          event.preventDefault();
          last.focus();
        }
      } else if (active === last || !panel.contains(active)) {
        event.preventDefault();
        first.focus();
      }
    },
    [onClose]
  );

  if (!mounted) return null;

  return createPortal(
    <AnimatePresence>
      {open ? (
        <motion.div
          className="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: prefersReducedMotion ? 0 : 0.2 }}
          onKeyDown={handleKeyDown}
        >
          <div
            className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
            onClick={onClose}
            aria-hidden="true"
          />

          <motion.div
            ref={panelRef}
            role="dialog"
            aria-modal="true"
            aria-labelledby={labelledBy}
            aria-label={labelledBy ? undefined : ariaLabel}
            tabIndex={-1}
            // Let the panel scroll natively (wheel + touch) even though Lenis
            // smooth-scroll is active/stopped on the page behind it.
            data-lenis-prevent
            initial={
              prefersReducedMotion
                ? { opacity: 0 }
                : { opacity: 0, scale: 0.96, y: 16 }
            }
            animate={
              prefersReducedMotion ? { opacity: 1 } : { opacity: 1, scale: 1, y: 0 }
            }
            exit={
              prefersReducedMotion
                ? { opacity: 0 }
                : { opacity: 0, scale: 0.97, y: 10 }
            }
            transition={{
              duration: prefersReducedMotion ? 0 : 0.28,
              ease: [0.22, 1, 0.36, 1]
            }}
            className={cn(
              "relative z-10 max-h-[90vh] w-full overflow-y-auto rounded-3xl bg-white shadow-premium outline-none",
              className
            )}
          >
            {showCloseButton ? (
              <button
                type="button"
                onClick={onClose}
                aria-label="Close dialog"
                suppressHydrationWarning
                className="absolute right-4 top-4 z-20 inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-white/90 text-text shadow-sm transition hover:border-primary hover:text-primary focus-visible:border-primary"
              >
                <X className="h-5 w-5" aria-hidden="true" />
              </button>
            ) : null}

            {children}
          </motion.div>
        </motion.div>
      ) : null}
    </AnimatePresence>,
    document.body
  );
}
