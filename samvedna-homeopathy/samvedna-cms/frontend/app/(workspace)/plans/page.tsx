"use client";

import { useEffect, useState } from "react";
import { motion } from "framer-motion";
import { Package, Pencil, Plus } from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useApi } from "@/lib/useApi";
import { useSession } from "@/lib/session";
import { useToast } from "@/components/ui/Toast";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Button,
  Card,
  CardBody,
  CardHeader,
  ErrorNote,
  LockRow,
  SectionLabel,
  Spinner,
} from "@/components/ui/primitives";
import { CheckItem, Field, FieldRow, Input, Textarea } from "@/components/ui/form";
import { Modal } from "@/components/ui/Modal";
import { PlanCard } from "@/components/flow/PlanCard";
import { stagger, staggerItem } from "@/lib/motion";
import type { Plan } from "@/types";

interface Draft {
  code: string;
  name: string;
  tagline: string;
  price: number;
  durationMonths: number;
  reviewCount: number;
  reviewIntervalDays: number;
  adherenceIntervalDays: number;
  rescheduleWindowDays: number;
  requiresSenior: boolean;
  requiresFounder: boolean;
  founderIntervalDays: number;
  clinicalCover: string;
  features: string;
  highlight: boolean;
  sortOrder: number;
}

const blankDraft: Draft = {
  code: "",
  name: "",
  tagline: "",
  price: 0,
  durationMonths: 6,
  reviewCount: 3,
  reviewIntervalDays: 60,
  adherenceIntervalDays: 15,
  rescheduleWindowDays: 7,
  requiresSenior: true,
  requiresFounder: false,
  founderIntervalDays: 0,
  clinicalCover: "",
  features: "",
  highlight: false,
  sortOrder: 0,
};

function toDraft(plan: Plan): Draft {
  return {
    code: plan.code,
    name: plan.name,
    tagline: plan.tagline,
    price: plan.price,
    durationMonths: plan.durationMonths,
    reviewCount: plan.reviewCount,
    reviewIntervalDays: plan.reviewIntervalDays,
    adherenceIntervalDays: plan.adherenceIntervalDays,
    rescheduleWindowDays: plan.rescheduleWindowDays,
    requiresSenior: plan.requiresSenior,
    requiresFounder: plan.requiresFounder,
    founderIntervalDays: plan.founderIntervalDays,
    clinicalCover: plan.clinicalCover,
    features: plan.features.join("\n"),
    highlight: plan.highlight,
    sortOrder: plan.sortOrder,
  };
}

/**
 * The plan catalogue — the rules engine behind step 2. Editing a cadence here
 * changes how every *future* schedule is generated; already-generated
 * touchpoints keep the dates they were given.
 */
export default function PlansPage() {
  const { can } = useSession();
  const toast = useToast();
  const { data, error, loading, reload } = useApi<{ plans: Plan[] }>("/plans");

  const [editing, setEditing] = useState<Plan | null>(null);
  const [creating, setCreating] = useState(false);
  const [draft, setDraft] = useState<Draft>(blankDraft);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (editing) setDraft(toDraft(editing));
    else if (creating) setDraft(blankDraft);
  }, [editing, creating]);

  const plans = data?.plans ?? [];
  const isFounder = can("founder");

  async function save() {
    setSaving(true);
    setErrors({});
    const body = {
      ...draft,
      features: draft.features
        .split("\n")
        .map((line) => line.trim())
        .filter(Boolean),
    };

    try {
      if (editing) {
        await api.put(`/plans/${editing.id}`, body);
        toast(`${draft.name} updated.`);
      } else {
        await api.post("/plans", body);
        toast(`${draft.name} created.`);
      }
      setEditing(null);
      setCreating(false);
      await reload();
    } catch (caught) {
      if (caught instanceof ApiError) {
        setErrors(caught.fields);
        toast(caught.message, "error");
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <PageShell>
      <PageHeader
        title="Care plans"
        description="Price, duration and follow-up cadence. These rows are what lock the schedule — the CMS never lets a user type an interval by hand."
        step={{ number: 2, label: "Care plan" }}
        eyebrowIcon={Package}
        actions={
          isFounder ? (
            <Button onClick={() => setCreating(true)}>
              <Plus className="size-3.5" /> New plan
            </Button>
          ) : undefined
        }
      />

      {loading && <Spinner label="Loading plans" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && plans.length > 0 && (
        <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-4">
          <motion.div variants={staggerItem} className="grid grid-cols-1 gap-4 pt-3 md:grid-cols-3">
            {plans.map((plan) => (
              <PlanCard key={plan.id} plan={plan} />
            ))}
          </motion.div>

          {plans.map((plan) => (
            <motion.div key={plan.id} variants={staggerItem}>
              <Card>
                <CardHeader
                  title={`${plan.name} — ${plan.priceLabel}`}
                  subtitle={plan.cadenceLabel}
                  badge={
                    <>
                      {plan.highlight && <Badge tone="indigo">Most preferred</Badge>}
                      <Badge tone="slate">{plan.activeCases ?? 0} active cases</Badge>
                    </>
                  }
                  actions={
                    isFounder ? (
                      <Button variant="secondary" size="sm" onClick={() => setEditing(plan)}>
                        <Pencil className="size-3.5" /> Edit
                      </Button>
                    ) : undefined
                  }
                />
                <CardBody>
                  <div className="grid grid-cols-2 gap-x-4 gap-y-2.5 text-[12px] sm:grid-cols-4">
                    {[
                      ["Duration", plan.durationLabel],
                      ["Formal reviews", `${plan.reviewCount} (${plan.cycles ?? plan.reviewCount} cycles)`],
                      ["Review interval", `${plan.reviewIntervalDays} days`],
                      ["Adherence check", `${plan.adherenceIntervalDays} days`],
                      ["Reschedule window", `±${plan.rescheduleWindowDays} days`],
                      ["Senior sign-off", plan.requiresSenior ? "Mandatory" : "Not required"],
                      [
                        "Founder review",
                        plan.requiresFounder ? `Every ${plan.founderIntervalDays} days` : "Escalation only",
                      ],
                      ["Clinical cover", plan.clinicalCover || "—"],
                    ].map(([label, value]) => (
                      <div key={label}>
                        <p className="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">{label}</p>
                        <p className="mt-0.5 text-[12.5px] font-semibold text-slate-700">{value}</p>
                      </div>
                    ))}
                  </div>
                </CardBody>
              </Card>
            </motion.div>
          ))}
        </motion.div>
      )}

      <Modal
        open={creating || Boolean(editing)}
        onClose={() => {
          setCreating(false);
          setEditing(null);
        }}
        title={editing ? `Edit ${editing.name}` : "New care plan"}
        description="Cadence values here drive every schedule generated from this plan."
        width="lg"
        footer={
          <>
            <Button
              variant="secondary"
              onClick={() => {
                setCreating(false);
                setEditing(null);
              }}
            >
              Cancel
            </Button>
            <Button loading={saving} onClick={() => void save()}>
              {editing ? "Save changes" : "Create plan"}
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-4">
          <div>
            <SectionLabel>Identity</SectionLabel>
            <FieldRow cols={2}>
              <Field label="Plan name" required error={errors.name}>
                <Input
                  value={draft.name}
                  onChange={(event) => setDraft({ ...draft, name: event.target.value })}
                  invalid={Boolean(errors.name)}
                />
              </Field>
              <Field label="Code" required hint="lowercase, no spaces" error={errors.code}>
                <Input
                  value={draft.code}
                  onChange={(event) => setDraft({ ...draft, code: event.target.value })}
                  invalid={Boolean(errors.code)}
                  placeholder="standard"
                />
              </Field>
            </FieldRow>
            <FieldRow cols={2}>
              <Field label="Tagline" className="mt-3.5">
                <Input
                  value={draft.tagline}
                  onChange={(event) => setDraft({ ...draft, tagline: event.target.value })}
                />
              </Field>
              <Field label="Price (INR)" className="mt-3.5">
                <Input
                  type="number"
                  value={draft.price}
                  onChange={(event) => setDraft({ ...draft, price: Number(event.target.value) })}
                />
              </Field>
            </FieldRow>
          </div>

          <div>
            <SectionLabel>Cadence</SectionLabel>
            <FieldRow cols={3}>
              <Field label="Duration (months)">
                <Input
                  type="number"
                  min={1}
                  value={draft.durationMonths}
                  onChange={(event) => setDraft({ ...draft, durationMonths: Number(event.target.value) })}
                />
              </Field>
              <Field label="Formal reviews">
                <Input
                  type="number"
                  min={1}
                  value={draft.reviewCount}
                  onChange={(event) => setDraft({ ...draft, reviewCount: Number(event.target.value) })}
                />
              </Field>
              <Field label="Review interval (days)">
                <Input
                  type="number"
                  min={7}
                  value={draft.reviewIntervalDays}
                  onChange={(event) => setDraft({ ...draft, reviewIntervalDays: Number(event.target.value) })}
                />
              </Field>
            </FieldRow>
            <FieldRow cols={3}>
              <Field label="Adherence interval (days)" className="mt-3.5">
                <Input
                  type="number"
                  min={0}
                  value={draft.adherenceIntervalDays}
                  onChange={(event) => setDraft({ ...draft, adherenceIntervalDays: Number(event.target.value) })}
                />
              </Field>
              <Field label="Reschedule window (± days)" className="mt-3.5">
                <Input
                  type="number"
                  min={0}
                  value={draft.rescheduleWindowDays}
                  onChange={(event) => setDraft({ ...draft, rescheduleWindowDays: Number(event.target.value) })}
                />
              </Field>
              <Field label="Founder review interval (days)" className="mt-3.5">
                <Input
                  type="number"
                  min={0}
                  value={draft.founderIntervalDays}
                  onChange={(event) => setDraft({ ...draft, founderIntervalDays: Number(event.target.value) })}
                />
              </Field>
            </FieldRow>
          </div>

          <div>
            <SectionLabel>Clinical cover</SectionLabel>
            <div className="flex flex-col gap-0.5">
              <CheckItem
                checked={draft.requiresSenior}
                onToggle={() => setDraft({ ...draft, requiresSenior: !draft.requiresSenior })}
              >
                Senior Doctor sign-off is mandatory on every review
              </CheckItem>
              <CheckItem
                checked={draft.requiresFounder}
                onToggle={() => setDraft({ ...draft, requiresFounder: !draft.requiresFounder })}
              >
                Founder review is scheduled on this plan
              </CheckItem>
              <CheckItem
                checked={draft.highlight}
                onToggle={() => setDraft({ ...draft, highlight: !draft.highlight })}
              >
                Show as &ldquo;most preferred&rdquo;
              </CheckItem>
            </div>

            <Field label="Cover summary" className="mt-3.5">
              <Input
                value={draft.clinicalCover}
                onChange={(event) => setDraft({ ...draft, clinicalCover: event.target.value })}
                placeholder="Case Doctor + Senior Doctor"
              />
            </Field>

            <Field label="Features" hint="one per line" className="mt-3.5">
              <Textarea
                value={draft.features}
                onChange={(event) => setDraft({ ...draft, features: event.target.value })}
                rows={4}
              />
            </Field>
          </div>

          {editing && (editing.activeCases ?? 0) > 0 && (
            <LockRow tone="amber">
              {editing.activeCases} active case{editing.activeCases === 1 ? "" : "s"} run on this plan. Changing the
              cadence affects schedules generated from now on — touchpoints already created keep their dates.
            </LockRow>
          )}
        </div>
      </Modal>

      {!loading && plans.length === 0 && !error && (
        <Card>
          <CardBody>
            <p className="flex items-center gap-2 text-[12.5px] font-medium text-slate-500">
              <Package className="size-4" /> No plans yet. Run the migration to seed the catalogue.
            </p>
          </CardBody>
        </Card>
      )}
    </PageShell>
  );
}
