"use client";

import { AlertCircle } from "lucide-react";
import { Field, FieldRow, Input, RadioCards, Select, Textarea } from "@/components/ui/form";
import { SectionLabel } from "@/components/ui/primitives";
import { cn } from "@/lib/format";
import type { Baseline, Marker, Meta } from "@/types";

export interface BaselineDraft {
  conditionType: string;
  severity: string;
  diagnosisAge: string;
  medicalHistory: string;
  therapyInvolvement: string;
  concerns: string[];
  markers: Marker[];
}

/** Empty baseline seeded with the marker catalogue, so the checklist always renders. */
export function emptyBaseline(meta: Meta | null): BaselineDraft {
  return {
    conditionType: "",
    severity: "",
    diagnosisAge: "",
    medicalHistory: "",
    therapyInvolvement: "",
    concerns: ["", "", ""],
    markers: (meta?.markers ?? []).map((m) => ({ key: m.key, label: m.label, rating: "", score: 0 })),
  };
}

export function baselineToDraft(baseline: Baseline): BaselineDraft {
  return {
    conditionType: baseline.conditionType,
    severity: baseline.severity,
    diagnosisAge: baseline.diagnosisAge,
    medicalHistory: baseline.medicalHistory,
    therapyInvolvement: baseline.therapyInvolvement,
    concerns: [0, 1, 2].map((index) => baseline.concerns[index] ?? ""),
    markers: baseline.markers,
  };
}

/**
 * The mandatory baseline block from step 1 of the flow.
 *
 * Every field here participates in the activation gate, so `errors` comes
 * straight from the server's own verdict rather than a separate client rule —
 * the two can never drift apart.
 */
export function BaselineFields({
  value,
  onChange,
  meta,
  errors = {},
}: {
  value: BaselineDraft;
  onChange: (next: BaselineDraft) => void;
  meta: Meta | null;
  errors?: Record<string, string>;
}) {
  const patch = (partial: Partial<BaselineDraft>) => onChange({ ...value, ...partial });

  const setConcern = (index: number, text: string) => {
    const concerns = [...value.concerns];
    concerns[index] = text;
    patch({ concerns });
  };

  const setMarker = (key: string, rating: string) => {
    patch({
      markers: value.markers.map((marker) =>
        marker.key === key
          ? {
              ...marker,
              rating,
              score: meta?.markerRatings.find((r) => r.key === rating)?.score ?? 0,
            }
          : marker
      ),
    });
  };

  const ratedCount = value.markers.filter((marker) => marker.rating).length;
  const concernCount = value.concerns.filter((concern) => concern.trim()).length;

  return (
    <>
      <div>
        <SectionLabel hint="required to activate the case">Mandatory baseline</SectionLabel>
        <FieldRow cols={2}>
          <Field label="Condition classification" required error={errors.conditionType}>
            <Select
              value={value.conditionType}
              onChange={(event) => patch({ conditionType: event.target.value })}
              placeholder="Select a condition"
              invalid={Boolean(errors.conditionType)}
              options={(meta?.conditions ?? []).map((condition) => ({ value: condition, label: condition }))}
            />
          </Field>

          <Field label="Severity level" required error={errors.severity}>
            <RadioCards
              value={value.severity}
              onChange={(severity) => patch({ severity })}
              columns={3}
              invalid={Boolean(errors.severity)}
              options={(meta?.severities ?? []).map((severity) => ({
                value: severity.key,
                label: severity.label,
              }))}
            />
          </Field>
        </FieldRow>

        <FieldRow cols={2}>
          <Field label="Age at diagnosis" hint="optional" className="mt-3.5">
            <Input
              value={value.diagnosisAge}
              onChange={(event) => patch({ diagnosisAge: event.target.value })}
              placeholder="e.g. 3 years 2 months"
            />
          </Field>

          <Field label="Existing medical history" hint="optional" className="mt-3.5">
            <Input
              value={value.medicalHistory}
              onChange={(event) => patch({ medicalHistory: event.target.value })}
              placeholder="Birth history, illnesses, medication"
            />
          </Field>
        </FieldRow>

        <Field
          label="Current therapy involvement"
          required
          hint='write "None" if there is none'
          error={errors.therapyInvolvement}
          className="mt-3.5"
        >
          <Textarea
            value={value.therapyInvolvement}
            onChange={(event) => patch({ therapyInvolvement: event.target.value })}
            placeholder="Speech therapy (2×/week), Occupational therapy (1×/week), school shadow support…"
            invalid={Boolean(errors.therapyInvolvement)}
            rows={2}
          />
        </Field>
      </div>

      {/* Top 3 parent concerns */}
      <div className="mt-6">
        <SectionLabel hint={`${concernCount} of 3 captured — all mandatory`}>Top 3 parent concerns</SectionLabel>
        <p className="mb-3 text-[11.5px] font-medium text-slate-500">
          These are the anchors every future review and progress report is measured against. Write them in the
          parent&apos;s own words.
        </p>
        <div className="flex flex-col gap-2.5">
          {[0, 1, 2].map((index) => (
            <div key={index} className="flex items-center gap-2.5">
              <span
                className={cn(
                  "grid size-6 shrink-0 place-items-center rounded-full text-[11px] font-semibold",
                  value.concerns[index]?.trim()
                    ? "bg-indigo-600 text-white"
                    : "border border-slate-200/70 bg-slate-50 text-slate-400"
                )}
              >
                {index + 1}
              </span>
              <Input
                value={value.concerns[index] ?? ""}
                onChange={(event) => setConcern(index, event.target.value)}
                placeholder={
                  ["Limited verbal communication", "Sensory meltdowns in public", "Sleep disturbances"][index]
                }
                invalid={Boolean(errors.concerns) && !value.concerns[index]?.trim()}
              />
            </div>
          ))}
        </div>
        {errors.concerns && (
          <p className="mt-2 flex items-center gap-1.5 text-[11.5px] text-rose-600">
            <AlertCircle className="size-3.5" /> {errors.concerns}
          </p>
        )}
      </div>

      {/* Functional markers */}
      <div className="mt-6">
        <SectionLabel hint={`${ratedCount} of ${value.markers.length} rated — none skippable`}>
          Baseline functional markers
        </SectionLabel>
        <p className="mb-3 text-[11.5px] font-medium text-slate-500">
          The progress dashboard charts every cycle against exactly these markers, so an unrated one leaves a
          permanent hole in the child&apos;s record.
        </p>

        <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
          {value.markers.map((marker) => (
            <div
              key={marker.key}
              className={cn(
                "rounded-xl border px-3 py-2.5 transition-colors",
                marker.rating ? "border-slate-200/70 bg-white" : "border-amber-300/35 bg-amber-50/40"
              )}
            >
              <label className="mb-1.5 block text-[11.5px] font-medium text-slate-700">{marker.label}</label>
              <Select
                value={marker.rating}
                onChange={(event) => setMarker(marker.key, event.target.value)}
                placeholder="Not rated"
                invalid={Boolean(errors.markers) && !marker.rating}
                options={(meta?.markerRatings ?? []).map((rating) => ({
                  value: rating.key,
                  label: rating.label,
                }))}
              />
            </div>
          ))}
        </div>

        {errors.markers && (
          <p className="mt-2 flex items-center gap-1.5 text-[11.5px] text-rose-600">
            <AlertCircle className="size-3.5" /> {errors.markers}
          </p>
        )}
      </div>
    </>
  );
}
