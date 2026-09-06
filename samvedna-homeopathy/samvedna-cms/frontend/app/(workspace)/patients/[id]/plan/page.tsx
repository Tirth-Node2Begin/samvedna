"use client";

import { useEffect, useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { ArrowRight, CalendarClock } from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useApi } from "@/lib/useApi";
import { useToast } from "@/components/ui/Toast";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Button,
  Card,
  CardBody,
  ErrorNote,
  LinkButton,
  LockRow,
  SectionLabel,
  Spinner,
} from "@/components/ui/primitives";
import { Field, FieldRow, Input, LockedValue, Select } from "@/components/ui/form";
import { PlanCard } from "@/components/flow/PlanCard";
import { addDaysISO, formatDate, todayISO } from "@/lib/format";
import type { Plan, PatientWorkspace, TeamMember } from "@/types";

/**
 * Step 2 — care plan selection and role assignment.
 *
 * Everything downstream of this screen (schedule cadence, who must sign a
 * review, how far a follow-up may move) is decided by the plan row picked here,
 * which is why the derived fields are shown locked rather than editable.
 */
export default function CarePlanPage() {
  const params = useParams<{ id: string }>();
  const patientId = Number(params.id);
  const router = useRouter();
  const toast = useToast();

  const workspace = useApi<PatientWorkspace>(`/patients/${patientId}`);
  const plansQuery = useApi<{ plans: Plan[] }>("/plans");
  const teamQuery = useApi<{ team: TeamMember[] }>("/team");

  const [planId, setPlanId] = useState<number | null>(null);
  const [startDate, setStartDate] = useState(todayISO());
  const [caseDoctorId, setCaseDoctorId] = useState("");
  const [seniorDoctorId, setSeniorDoctorId] = useState("");
  const [founderId, setFounderId] = useState("");
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);

  const plans = useMemo(() => plansQuery.data?.plans ?? [], [plansQuery.data]);
  const team = useMemo(() => teamQuery.data?.team ?? [], [teamQuery.data]);
  const selectedPlan = plans.find((plan) => plan.id === planId) ?? null;

  // Default to the plan the practice sells most.
  useEffect(() => {
    if (planId === null && plans.length > 0) {
      setPlanId((plans.find((plan) => plan.highlight) ?? plans[0]).id);
    }
  }, [plans, planId]);

  // Pre-fill the founder slot — there is only ever one.
  useEffect(() => {
    if (!founderId) {
      const founder = team.find((member) => member.role === "founder");
      if (founder) setFounderId(String(founder.id));
    }
  }, [team, founderId]);

  const caseDoctors = team.filter((m) => m.role === "case_doctor" || m.role === "senior_doctor");
  const seniorDoctors = team.filter((m) => m.role === "senior_doctor" || m.role === "founder");
  const founders = team.filter((m) => m.role === "founder");

  /** Mirrors ScheduleGenerator::cycleCount + the end-date maths on the server. */
  const derived = useMemo(() => {
    if (!selectedPlan) return null;
    const byDuration = Math.floor((selectedPlan.durationMonths * 30) / Math.max(7, selectedPlan.reviewIntervalDays));
    const cycles = Math.max(1, Math.min(selectedPlan.reviewCount, Math.max(1, byDuration)));

    const end = new Date(`${startDate}T00:00:00`);
    end.setMonth(end.getMonth() + selectedPlan.durationMonths);
    const endISO = end.toISOString().slice(0, 10);

    const adherencePerCycle =
      selectedPlan.adherenceIntervalDays > 0
        ? Math.max(0, Math.ceil(selectedPlan.reviewIntervalDays / selectedPlan.adherenceIntervalDays) - 1)
        : 0;

    return {
      cycles,
      endISO,
      firstReview: addDaysISO(startDate, selectedPlan.reviewIntervalDays),
      touchpoints: 1 + cycles * (adherencePerCycle + 1),
    };
  }, [selectedPlan, startDate]);

  const patient = workspace.data?.patient;
  const verdict = workspace.data?.verdict;
  const existingCase = workspace.data?.case;

  async function activate() {
    if (!selectedPlan) return;

    setSaving(true);
    setErrors({});

    try {
      await api.post(`/patients/${patientId}/activate`, {
        planId: selectedPlan.id,
        startDate,
        caseDoctorId: Number(caseDoctorId) || 0,
        seniorDoctorId: Number(seniorDoctorId) || 0,
        founderId: Number(founderId) || 0,
      });
      toast(`Care plan confirmed — the follow-up schedule has been generated.`);
      router.push(`/patients/${patientId}?tab=schedule`);
    } catch (caught) {
      if (caught instanceof ApiError) {
        setErrors(caught.fields);
        toast(caught.message, "error");
        if (caught.isBaselineBlock) {
          router.push(`/patients/${patientId}?tab=baseline`);
        }
      } else {
        toast("Could not activate this care plan.", "error");
      }
      setSaving(false);
    }
  }

  if (workspace.loading || plansQuery.loading) return <Spinner label="Loading care plans" />;
  if (workspace.error) return <ErrorNote message={workspace.error} retry={workspace.reload} />;
  if (!patient) return null;

  if (existingCase) {
    return (
      <>
        <PageHeader
          title="Care plan"
        />
        <Card>
          <CardBody className="flex flex-col gap-3">
            <LockRow>
              {patient.childName} is already on the {existingCase.plan?.name} plan ({existingCase.cycleLabel}). A
              patient can only hold one live care plan at a time.
            </LockRow>
            <div>
              <LinkButton href={`/patients/${patientId}`} variant="primary" size="sm">
                Open the case workspace
              </LinkButton>
            </div>
          </CardBody>
        </Card>
      </>
    );
  }

  return (
    <PageShell>
      <PageHeader
        title="Care plan selection"
        description={`Confirming a plan activates ${patient.childName}'s case and generates the whole follow-up schedule.`}
        step={{ number: 2, label: "Care plan" }}
      />

      {verdict && !verdict.complete && (
        <div className="mb-4">
          <LockRow tone="amber">
            Case activation blocked — the baseline is only {verdict.percent}% complete.{" "}
            <a href={`/patients/${patientId}?tab=baseline`} className="font-medium underline">
              Finish the baseline first
            </a>
            .
          </LockRow>
        </div>
      )}

      <div className="flex flex-col gap-4">
        {/* Plans */}
        <Card>
          <CardBody>
            <SectionLabel hint="pricing and cadence come from the plan catalogue">Select plan</SectionLabel>
            <div className="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
              {plans.map((plan) => (
                <PlanCard key={plan.id} plan={plan} selected={plan.id === planId} onSelect={(p) => setPlanId(p.id)} />
              ))}
            </div>
          </CardBody>
        </Card>

        {/* Derived schedule */}
        <Card>
          <CardBody>
            <SectionLabel hint="computed from the plan — not editable">Auto-populated after plan selection</SectionLabel>

            <FieldRow cols={4}>
              <Field label="Plan start date" error={errors.startDate}>
                <Input
                  type="date"
                  value={startDate}
                  onChange={(event) => setStartDate(event.target.value)}
                  invalid={Boolean(errors.startDate)}
                />
              </Field>

              <Field label="Plan end date" locked>
                <LockedValue>{derived ? formatDate(derived.endISO) : "—"}</LockedValue>
              </Field>

              <Field label="Review cycles" locked>
                <LockedValue>{derived ? `Cycle 1 of ${derived.cycles}` : "—"}</LockedValue>
              </Field>

              <Field label="First formal review" locked>
                <LockedValue>{derived ? formatDate(derived.firstReview) : "—"}</LockedValue>
              </Field>
            </FieldRow>

            {selectedPlan && derived && (
              <div className="mt-3.5">
                <LockRow>
                  Follow-up frequency is locked by the plan — a formal review every{" "}
                  {selectedPlan.reviewIntervalDays} days and an adherence check every{" "}
                  {selectedPlan.adherenceIntervalDays} days. Reschedule allowed ±
                  {selectedPlan.rescheduleWindowDays} days only. Confirming generates{" "}
                  {derived.touchpoints} touchpoints.
                </LockRow>
              </div>
            )}
          </CardBody>
        </Card>

        {/* Roles */}
        <Card>
          <CardBody>
            <SectionLabel hint="mandatory">Doctor role assignment</SectionLabel>

            <FieldRow cols={3}>
              <Field label="Case Doctor (primary)" required error={errors.caseDoctorId}>
                <Select
                  value={caseDoctorId}
                  onChange={(event) => setCaseDoctorId(event.target.value)}
                  placeholder="Assign a case doctor"
                  invalid={Boolean(errors.caseDoctorId)}
                  options={caseDoctors.map((member) => ({
                    value: String(member.id),
                    label: `${member.name} — ${member.caseload} active`,
                  }))}
                />
              </Field>

              <Field
                label="Senior Doctor (secondary)"
                required={selectedPlan?.requiresSenior}
                hint={selectedPlan?.requiresSenior ? undefined : "not required on this plan"}
                error={errors.seniorDoctorId}
              >
                <Select
                  value={seniorDoctorId}
                  onChange={(event) => setSeniorDoctorId(event.target.value)}
                  placeholder={selectedPlan?.requiresSenior ? "Assign a senior doctor" : "Optional"}
                  invalid={Boolean(errors.seniorDoctorId)}
                  options={seniorDoctors.map((member) => ({ value: String(member.id), label: member.name }))}
                />
              </Field>

              <Field
                label="Founder"
                required={selectedPlan?.requiresFounder}
                hint={selectedPlan?.requiresFounder ? "quarterly review" : "escalation only"}
                error={errors.founderId}
              >
                <Select
                  value={founderId}
                  onChange={(event) => setFounderId(event.target.value)}
                  placeholder="Assign the founder"
                  invalid={Boolean(errors.founderId)}
                  options={founders.map((member) => ({ value: String(member.id), label: member.name }))}
                />
              </Field>
            </FieldRow>

            {selectedPlan?.requiresSenior && (
              <p className="mt-3 text-[11.5px] font-medium text-slate-500">
                On the {selectedPlan.name} plan the Senior Doctor must sign off every bi-monthly review before it
                can close.
              </p>
            )}
          </CardBody>
        </Card>

        <div className="flex flex-wrap items-center justify-end gap-2">
          <LinkButton href={`/patients/${patientId}`} variant="secondary">
            Back to patient
          </LinkButton>
          <Button onClick={() => void activate()} loading={saving} disabled={!selectedPlan}>
            <CalendarClock className="size-3.5" /> Confirm plan &amp; generate schedule
            <ArrowRight className="size-3.5" />
          </Button>
        </div>
      </div>
    </PageShell>
  );
}
