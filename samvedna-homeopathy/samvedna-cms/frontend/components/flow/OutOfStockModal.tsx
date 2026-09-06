"use client";

import { useEffect, useState } from "react";
import { PackageX, CalendarClock } from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useToast } from "@/components/ui/Toast";
import { Modal } from "@/components/ui/Modal";
import { Button } from "@/components/ui/primitives";
import { Field, Input, Textarea } from "@/components/ui/form";
import { addDaysISO, formatDate, todayISO } from "@/lib/format";
import type { MedicineSupply } from "@/types";

interface DeferResponse {
  supply: MedicineSupply;
  message: string;
}

const DEFAULT_DEFER = 5;

/**
 * The medicine is not available.
 *
 * Deliberately NOT a delivery: nothing ships, so `lastDeliveredOn` is untouched.
 * Only the next due date moves — by 5 days from today — and the case stays
 * flagged out-of-stock until a real delivery clears it. The panel states the new
 * date before you confirm, because that date is what the parent gets told.
 */
export function OutOfStockModal({
  supply,
  open,
  onClose,
  onDeferred,
  defaultDeferDays = DEFAULT_DEFER,
}: {
  supply: MedicineSupply | null;
  open: boolean;
  onClose: () => void;
  onDeferred?: (updated: MedicineSupply) => void;
  defaultDeferDays?: number;
}) {
  const toast = useToast();
  const [deferDays, setDeferDays] = useState(String(defaultDeferDays));
  const [notes, setNotes] = useState("");
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (open) {
      setDeferDays(String(defaultDeferDays));
      setNotes("");
      setErrors({});
    }
  }, [open, supply?.caseId, defaultDeferDays]);

  if (!supply) return null;

  const days = Number(deferDays);
  const validDays = Number.isFinite(days) && days >= 1 && days <= 60;
  const newDue = validDays ? addDaysISO(todayISO(), days) : null;

  async function submit() {
    if (!supply) return;
    setBusy(true);
    setErrors({});
    try {
      const response = await api.post<DeferResponse>(`/medicine/${supply.caseId}/out-of-stock`, {
        deferDays: days,
        notes,
      });
      toast(response.message, "info");
      onDeferred?.(response.supply);
      onClose();
    } catch (caught) {
      if (caught instanceof ApiError) {
        setErrors(caught.fields);
        toast(caught.message, "error");
      } else {
        toast("Could not record the shortage.", "error");
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={`Medicine out of stock — ${supply.patientName ?? "patient"}`}
      description="Nothing is delivered. The due date moves and the parent can be told a firm new date."
      icon={PackageX}
      tone="amber"
      width="md"
      footer={
        <>
          <Button variant="secondary" onClick={onClose} disabled={busy}>
            Cancel
          </Button>
          <Button loading={busy} onClick={() => void submit()} disabled={!validDays}>
            <CalendarClock className="size-4" /> Defer delivery
          </Button>
        </>
      }
    >
      <div className="flex flex-col gap-4">
        <Field
          label="Deliver after"
          hint="days from today"
          required
          error={errors.deferDays ?? (!validDays ? "Enter between 1 and 60 days." : undefined)}
        >
          <Input
            type="number"
            min={1}
            max={60}
            value={deferDays}
            onChange={(event) => setDeferDays(event.target.value)}
            invalid={Boolean(errors.deferDays) || !validDays}
          />
        </Field>

        <Field label="Why" hint="optional — shown on the case timeline">
          <Textarea
            value={notes}
            onChange={(event) => setNotes(event.target.value)}
            placeholder="Carcinosin 200 out of stock with the supplier; restock expected mid-week."
            rows={2}
          />
        </Field>

        {/* The consequence, stated before the click. */}
        <div className="flex items-start gap-3 rounded-xl border border-amber-200/70 bg-amber-50 px-4 py-3">
          <span className="grid size-9 shrink-0 place-items-center rounded-full bg-white text-amber-600 ring-1 ring-inset ring-amber-100">
            <CalendarClock className="size-4" />
          </span>
          <p className="text-[12.5px] font-semibold text-amber-800">
            Delivery moves to{" "}
            <span className="font-black tabular-nums">{newDue ? formatDate(newDue) : "—"}</span>
            <span className="mt-0.5 block text-[11px] font-medium text-amber-700/80">
              Today&apos;s reminder is cleared and this patient drops off the day&apos;s list. The
              morning reminder returns on the new date.
            </span>
          </p>
        </div>
      </div>
    </Modal>
  );
}
