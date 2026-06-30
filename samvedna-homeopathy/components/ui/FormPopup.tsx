"use client";

import { useEffect, useState } from "react";
import ConsultationForm from "@/components/ui/ConsultationForm";
import Modal from "@/components/ui/Modal";
import { FORM_POPUP } from "@/lib/config/forms";

export default function FormPopup() {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    let alreadyShown = false;
    try {
      alreadyShown = sessionStorage.getItem(FORM_POPUP.sessionKey) === "1";
    } catch {
      // sessionStorage unavailable (e.g. privacy mode) — fail open silently.
    }
    if (alreadyShown) return;

    const timer = window.setTimeout(() => {
      setOpen(true);
      try {
        // Mark as shown immediately so it never reappears this session,
        // even if the user reloads or navigates without closing it.
        sessionStorage.setItem(FORM_POPUP.sessionKey, "1");
      } catch {
        // ignore
      }
    }, FORM_POPUP.delayMs);

    return () => window.clearTimeout(timer);
  }, []);

  return (
    <Modal open={open} onClose={() => setOpen(false)} ariaLabel="Book a consultation" className="max-w-2xl">
      <div className="p-6 sm:p-8">
        <ConsultationForm source="popup" />
      </div>
    </Modal>
  );
}
