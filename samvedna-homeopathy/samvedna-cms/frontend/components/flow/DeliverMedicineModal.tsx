"use client";

import { useEffect, useState } from "react";
import { Pill, CalendarCheck2, ArrowRight } from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useToast } from "@/components/ui/Toast";
import { Modal } from "@/components/ui/Modal";
import { Button } from "@/components/ui/primitives";
import { Field, FieldRow, Input, Select, Textarea } from "@/components/ui/form";
import { addDaysISO, formatDate, todayISO } from "@/lib/format";
import type { MedicineSupply } from "@/types";

interface DeliverResponse {
  delivery: { deliveredOn: string; nextDueOn: string };
  supply: MedicineSupply;
  message: string;
}

/**
 * The one action the medicine feature is built around.
 *
 * Deliberately small: date (defaults to today), how it went out, an optional
 * tracking reference, a note. The panel shows the next-due date *before* you
 * confirm, so there is never a surprise about when the next reminder fires.
 */
export function DeliverMedicineModal({
  supply,
  open,
  onClose,
  onDelivered,
}: {
  supply: MedicineSupply | null;
  open: boolean;
  onClose: () => void;
  onDelivered?: (updated: MedicineSupply) => void;
}) {
  const toast = useToast();
  const [deliveredOn, setDeliveredOn] = useState(todayISO());
  const [mode, setMode] = useState("courier");
  const [reference, setReference] = useState("");
  const [notes, setNotes] = useState("");
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);

  // Reset every time the dialog opens for a (possibly different) patient.
  useEffect(() => {
    if (open) {
      setDeliveredOn(todayISO());
      setMode("courier");
      setReference("");
      setNotes("");
      setErrors({});
    }
  }, [open, supply?.caseId]);

  if (!supply) return null;

  const interval = supply.intervalDays;
  const nextDue = deliveredOn ? addDaysISO(deliveredOn, interval) : null;

  async function submit() {
    if (!supply) return;
    setBusy(true);
    setErrors({});
    try {
      const response = await api.post<DeliverResponse>(`/medicine/${supply.caseId}/deliver`, {
        deliveredOn,
        mode,
        reference,
        notes,
      });
      toast(response.message);
      onDelivered?.(response.supply);
      onClose();
    } catch (caught) {
      if (caught instanceof ApiError) {
        setErrors(caught.fields);
        toast(caught.message, "error");
      } else {
        toast("Could not record the delivery.", "error");
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={`Mark medicine delivered — ${supply.patientName ?? "patient"}`}
      description={
        supply.lastDeliveredOn
          ? `Last delivered ${formatDate(supply.lastDeliveredOn)} · every ${interval} days`
          : `First delivery on this cycle · every ${interval} days`
      }
      icon={Pill}
      tone="emerald"
      width="md"
      footer={
        <>
          <Button variant="secondary" onClick={onClose} disabled={busy}>
            Cancel
          </Button>
          <Button loading={busy} onClick={() => void submit()}>
            <CalendarCheck2 className="size-4" /> Confirm delivery
          </Button>
        </>
      }
    >
      <div className="flex flex-col gap-4">
        <FieldRow cols={2}>
          <Field label="Delivered on" required error={errors.deliveredOn}>
            <Input
              type="date"
              value={deliveredOn}
              max={todayISO()}
              onChange={(event) => setDeliveredOn(event.target.value)}
              invalid={Boolean(errors.deliveredOn)}
            />
          </Field>
          <Field label="How it went out">
            <Select
              value={mode}
              onChange={(event) => setMode(event.target.value)}
              options={[
                { value: "courier", label: "Courier" },
                { value: "hand", label: "Hand delivery" },
                { value: "pickup", label: "Clinic pickup" },
                { value: "other", label: "Other" },
              ]}
            />
          </Field>
        </FieldRow>

        <Field label="Tracking / reference" hint="optional">
          <Input
            value={reference}
            onChange={(event) => setReference(event.target.value)}
            placeholder="DTDC-048217"
          />
        </Field>

        <Field label="Note" hint="optional">
          <Textarea
            value={notes}
            onChange={(event) => setNotes(event.target.value)}
            placeholder="Handed to the mother; confirmed dosage chart is unchanged."
            rows={2}
          />
        </Field>

        {/* The consequence, stated before the click. */}
        <div className="flex items-center gap-3 rounded-xl border border-emerald-200/70 bg-emerald-50 px-4 py-3">
          <span className="grid size-9 shrink-0 place-items-center rounded-full bg-white text-emerald-600 ring-1 ring-inset ring-emerald-100">
            <ArrowRight className="size-4" />
          </span>
          <p className="text-[12.5px] font-semibold text-emerald-800">
            Next supply will be due on{" "}
            <span className="font-black tabular-nums">{nextDue ? formatDate(nextDue) : "—"}</span>
            <span className="block text-[11px] font-medium text-emerald-700/80">
              {interval} days after this delivery. The morning reminder will fire that day.
            </span>
          </p>
        </div>
      </div>
    </Modal>
  );
}
