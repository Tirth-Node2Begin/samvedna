"use client";

import { Suspense, useEffect, useMemo, useState } from "react";
import { useParams, useRouter, useSearchParams } from "next/navigation";
import { motion } from "framer-motion";
import {
  CalendarClock,
  ClipboardCheck,
  TriangleAlert,
  TrendingUp,
  Stethoscope,
  Package,
  Save,
  CheckCircle2,
  CalendarDays,
  Pill,
  ArrowRight,
  CalendarCheck2,
  Truck,
  Settings2,
  PackageX,
} from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useApi } from "@/lib/useApi";
import { useSession } from "@/lib/session";
import { useToast } from "@/components/ui/Toast";
import Link from "next/link";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import { HERO_PRIMARY_BTN } from "@/lib/tokens";
import {
  Badge,
  Button,
  Card,
  CardBody,
  CardHeader,
  EmptyState,
  ErrorNote,
  LinkButton,
  LockRow,
  Metric,
  ProgressBar,
  SectionLabel,
  Spinner,
  toneForStatus,
} from "@/components/ui/primitives";
import { Field, FieldRow, Input, Select, Textarea, CheckItem } from "@/components/ui/form";
import { Modal } from "@/components/ui/Modal";
import { Timeline } from "@/components/flow/Timeline";
import {
  AreaBars,
  CompletionMeter,
  CycleOverview,
  DetailGrid,
  GoalList,
  SignoffList,
} from "@/components/flow/panels";
import { BaselineFields, baselineToDraft, type BaselineDraft } from "@/components/flow/BaselineFields";
import { DeliverMedicineModal } from "@/components/flow/DeliverMedicineModal";
import { OutOfStockModal } from "@/components/flow/OutOfStockModal";
import { cn, formatDate, humanise, statusLabel, todayISO } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import type { MedicineSupply, PatientWorkspace, ScheduleEvent } from "@/types";

const TABS = [
  { key: "overview", label: "Overview", icon: Stethoscope },
  { key: "baseline", label: "Baseline", icon: ClipboardCheck },
  { key: "plan", label: "Care plan", icon: Package },
  { key: "schedule", label: "Schedule", icon: CalendarClock },
  { key: "medicine", label: "Medicine", icon: Pill },
  { key: "reviews", label: "Reviews", icon: ClipboardCheck },
  { key: "escalations", label: "Escalations", icon: TriangleAlert },
  { key: "progress", label: "Progress", icon: TrendingUp },
] as const;

type TabKey = (typeof TABS)[number]["key"];

export default function PatientWorkspacePage() {
  return (
    <Suspense fallback={<Spinner label="Loading case" />}>
      <PatientWorkspaceInner />
    </Suspense>
  );
}

function PatientWorkspaceInner() {
  const params = useParams<{ id: string }>();
  const patientId = Number(params.id);
  const searchParams = useSearchParams();
  const router = useRouter();
  const toast = useToast();
  const { meta, can } = useSession();

  const { data, error, loading, reload } = useApi<PatientWorkspace>(`/patients/${patientId}`);
  const [tab, setTab] = useState<TabKey>("overview");

  useEffect(() => {
    const requested = searchParams.get("tab") as TabKey | null;
    if (requested && TABS.some((t) => t.key === requested)) {
      setTab(requested);
    }
  }, [searchParams]);

  /* ------------------------------------------------------------ baseline -- */
  const [baselineDraft, setBaselineDraft] = useState<BaselineDraft | null>(null);
  const [baselineErrors, setBaselineErrors] = useState<Record<string, string>>({});
  const [savingBaseline, setSavingBaseline] = useState(false);

  useEffect(() => {
    if (data?.baseline) setBaselineDraft(baselineToDraft(data.baseline));
  }, [data?.baseline]);

  async function saveBaseline() {
    if (!baselineDraft) return;
    setSavingBaseline(true);
    setBaselineErrors({});
    try {
      const response = await api.put<{ verdict: { complete: boolean; missing: Record<string, string> } }>(
        `/patients/${patientId}/baseline`,
        {
          ...baselineDraft,
          concerns: baselineDraft.concerns.filter((concern) => concern.trim()),
          markers: baselineDraft.markers.map((marker) => ({ key: marker.key, rating: marker.rating })),
        }
      );
      if (response.verdict.complete) {
        toast("Baseline complete — this case can now be activated.");
      } else {
        setBaselineErrors(response.verdict.missing);
        toast("Baseline saved. Activation is still blocked by the fields highlighted below.", "info");
      }
      await reload();
    } catch (caught) {
      toast(caught instanceof ApiError ? caught.message : "Could not save the baseline.", "error");
    } finally {
      setSavingBaseline(false);
    }
  }

  /* ------------------------------------------------------------ schedule -- */
  const [activeEvent, setActiveEvent] = useState<ScheduleEvent | null>(null);
  const [rescheduleDate, setRescheduleDate] = useState("");
  const [adherence, setAdherence] = useState({
    compliance: "full",
    refill: "stocked",
    parentConcern: false,
    parentNote: "",
  });
  const [eventBusy, setEventBusy] = useState(false);
  const [eventError, setEventError] = useState("");

  function openEvent(event: ScheduleEvent) {
    setActiveEvent(event);
    setRescheduleDate(event.dueDate);
    setEventError("");
    setAdherence({ compliance: "full", refill: "stocked", parentConcern: false, parentNote: "" });
  }

  async function runEventAction(action: () => Promise<unknown>, success: string) {
    setEventBusy(true);
    setEventError("");
    try {
      await action();
      toast(success);
      setActiveEvent(null);
      await reload();
    } catch (caught) {
      const message = caught instanceof ApiError ? caught.message : "That action could not be completed.";
      setEventError(message);
      toast(message, "error");
    } finally {
      setEventBusy(false);
    }
  }

  async function openReview(event: ScheduleEvent) {
    setEventBusy(true);
    try {
      const response = await api.post<{ review: { id: number } }>(`/schedule/${event.id}/open-review`);
      router.push(`/reviews/${response.review.id}`);
    } catch (caught) {
      toast(caught instanceof ApiError ? caught.message : "Could not open the review.", "error");
      setEventBusy(false);
    }
  }

  /* ------------------------------------------------------------ medicine -- */
  const [deliverOpen, setDeliverOpen] = useState(false);
  const [outOfStockOpen, setOutOfStockOpen] = useState(false);
  const [intervalDraft, setIntervalDraft] = useState("");
  const [nextDueDraft, setNextDueDraft] = useState("");
  const [medicineBusy, setMedicineBusy] = useState(false);

  useEffect(() => {
    if (data?.medicine.supply) {
      setIntervalDraft(String(data.medicine.supply.intervalDays));
      setNextDueDraft(data.medicine.supply.nextDueOn);
    }
  }, [data?.medicine.supply]);

  async function saveMedicineCycle() {
    if (!data?.case) return;
    setMedicineBusy(true);
    try {
      await api.put(`/medicine/${data.case.id}`, {
        intervalDays: Number(intervalDraft) || 15,
        nextDueOn: nextDueDraft,
      });
      toast("Medicine cycle updated.");
      await reload();
    } catch (caught) {
      toast(caught instanceof ApiError ? caught.message : "Could not update the medicine cycle.", "error");
    } finally {
      setMedicineBusy(false);
    }
  }

  /* ---------------------------------------------------------- escalation -- */
  const [escalationOpen, setEscalationOpen] = useState(false);
  const [escReasons, setEscReasons] = useState<string[]>([]);
  const [escNotes, setEscNotes] = useState("");
  const [escErrors, setEscErrors] = useState<Record<string, string>>({});
  const [escBusy, setEscBusy] = useState(false);

  async function raiseEscalation() {
    if (!data?.case) return;
    setEscBusy(true);
    setEscErrors({});
    try {
      await api.post("/escalations", { caseId: data.case.id, reasons: escReasons, notes: escNotes });
      toast("Escalation raised — the Senior Doctor has 7 days to resolve it.");
      setEscalationOpen(false);
      setEscReasons([]);
      setEscNotes("");
      await reload();
      setTab("escalations");
    } catch (caught) {
      if (caught instanceof ApiError) {
        setEscErrors(caught.fields);
        toast(caught.message, "error");
      }
    } finally {
      setEscBusy(false);
    }
  }

  /* ---------------------------------------------------------------- data -- */
  const currentCycleEvents = useMemo(() => {
    if (!data?.case) return [];
    return data.schedule.filter((event) => event.cycle === data.case!.currentCycle);
  }, [data]);

  if (loading) return <Spinner label="Loading case" />;
  if (error)
    return (
      <PageShell>
        <ErrorNote message={error} retry={reload} />
      </PageShell>
    );
  if (!data) return null;

  const { patient, baseline, verdict, case: careCase, schedule, cycles, reviews, escalations, progress, medicine } = data;
  const supply: MedicineSupply | null = medicine.supply
    ? { ...medicine.supply, patientName: patient.childName, patientCode: patient.code, phone: patient.phone, guardianName: patient.guardianName }
    : null;
  const openEscalations = escalations.filter((e) => e.status === "open" || e.status === "in_progress");

  return (
    <PageShell>
      <PageHeader
        eyebrow={patient.code}
        eyebrowIcon={Stethoscope}
        title={patient.childName}
        description={[
          patient.code,
          patient.age !== null ? `${patient.age} years` : null,
          baseline.conditionType || null,
          baseline.severity ? `${humanise(baseline.severity)} severity` : null,
        ]
          .filter(Boolean)
          .join(" · ")}
        actions={
          <>
            <Badge tone={toneForStatus(patient.status)}>{statusLabel(patient.status)}</Badge>
            {!careCase && verdict.complete && (
              <Link href={`/patients/${patientId}/plan`} className={HERO_PRIMARY_BTN}>
                Select care plan <ArrowRight className="size-4" />
              </Link>
            )}
            {careCase && can("founder", "senior_doctor", "case_doctor") && (
              <button
                type="button"
                onClick={() => setEscalationOpen(true)}
                className="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-bold text-rose-600 shadow-sm transition-colors hover:bg-rose-50"
              >
                <TriangleAlert className="size-4" /> Raise escalation
              </button>
            )}
          </>
        }
      />

      {!verdict.complete && (
        <div className="mb-4">
          <LockRow tone="amber">
            Case activation blocked — complete all baseline fields to proceed ({verdict.percent}% done).{" "}
            <button type="button" onClick={() => setTab("baseline")} className="font-medium underline">
              Open the baseline
            </button>
            .
          </LockRow>
        </div>
      )}

      {/* Tabs */}
      <div className="mb-4 flex gap-1 overflow-x-auto border-b border-slate-200/70">
        {TABS.map((item) => {
          const active = tab === item.key;
          const count =
            item.key === "medicine"
              ? supply && supply.daysUntilDue <= 0
                ? 1
                : 0
              : item.key === "reviews"
              ? reviews.length
              : item.key === "escalations"
                ? escalations.length
                : item.key === "progress"
                  ? progress.length
                  : 0;

          return (
            <button
              key={item.key}
              type="button"
              onClick={() => setTab(item.key)}
              className={cn(
                "relative flex shrink-0 items-center gap-1.5 px-3 py-2.5 text-[12.5px] transition-colors",
                active ? "font-medium text-indigo-700" : "text-slate-500 hover:text-slate-700"
              )}
            >
              <item.icon className="size-3.5" />
              {item.label}
              {count > 0 && (
                <span className="rounded-full bg-slate-50 px-1.5 text-[10px] font-medium text-slate-500">{count}</span>
              )}
              {active && (
                <motion.span layoutId="case-tab" className="absolute inset-x-1 -bottom-px h-0.5 rounded-full bg-indigo-600" />
              )}
            </button>
          );
        })}
      </div>

      {/* ------------------------------------------------------------ tabs -- */}

      {tab === "overview" && (
        <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-4">
          {careCase ? (
            <motion.div variants={staggerItem}>
              <Card>
                <CardHeader
                  title={`${careCase.plan?.name} plan · ${careCase.code}`}
                  subtitle={`${formatDate(careCase.startDate)} → ${formatDate(careCase.endDate)}`}
                  badge={<Badge tone={toneForStatus(careCase.status)}>{statusLabel(careCase.status)}</Badge>}
                />
                <CardBody className="flex flex-col gap-4">
                  <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <Metric value={careCase.cycleLabel.replace("Cycle ", "")} label="Current cycle" tone="emerald" />
                    <Metric value={`${careCase.elapsedPercent}%`} label="Plan elapsed" />
                    <Metric value={careCase.daysRemaining} label="Days remaining" />
                    <Metric
                      value={openEscalations.length}
                      label="Open escalations"
                      tone={openEscalations.length ? "rose" : undefined}
                    />
                  </div>

                  <div>
                    <p className="mb-1.5 text-[11.5px] font-medium text-slate-500">Plan progress</p>
                    <ProgressBar percent={careCase.elapsedPercent} />
                  </div>

                  <DetailGrid
                    cols={3}
                    items={[
                      { label: "Case Doctor (primary)", value: careCase.caseDoctorName || "Unassigned" },
                      {
                        label: "Senior Doctor (secondary)",
                        value: careCase.seniorDoctorName || "Not required on this plan",
                        tone: careCase.seniorDoctorName ? "default" : "locked",
                      },
                      {
                        label: "Founder",
                        value: careCase.founderName || "Escalation only",
                        tone: careCase.founderName ? "default" : "locked",
                      },
                    ]}
                  />
                </CardBody>
              </Card>
            </motion.div>
          ) : (
            <motion.div variants={staggerItem}>
              <Card>
                <EmptyState
                  icon={Package}
                  title="No care plan yet"
                  description={
                    verdict.complete
                      ? "The baseline is complete — pick a plan to activate this case and generate the schedule."
                      : "Finish the mandatory baseline before a care plan can be selected."
                  }
                  action={
                    verdict.complete ? (
                      <LinkButton href={`/patients/${patientId}/plan`} variant="primary" size="sm">
                        Select care plan
                      </LinkButton>
                    ) : (
                      <Button variant="secondary" size="sm" onClick={() => setTab("baseline")}>
                        Complete the baseline
                      </Button>
                    )
                  }
                />
              </Card>
            </motion.div>
          )}

          <motion.div variants={staggerItem} className="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <Card>
              <CardHeader title="Parent concerns" subtitle="Captured at intake, tracked every cycle" />
              <CardBody>
                {baseline.concerns.filter(Boolean).length === 0 ? (
                  <p className="text-[12px] font-medium text-slate-500">No concerns recorded yet.</p>
                ) : (
                  <ol className="flex flex-col gap-2">
                    {baseline.concerns.filter(Boolean).map((concern, index) => (
                      <li key={concern} className="flex items-start gap-2.5 text-[12.5px] font-medium text-slate-700">
                        <span className="grid size-5 shrink-0 place-items-center rounded-full bg-indigo-50 text-[10.5px] font-semibold text-indigo-700">
                          {index + 1}
                        </span>
                        {concern}
                      </li>
                    ))}
                  </ol>
                )}
              </CardBody>
            </Card>

            <Card>
              <CardHeader title="Guardian & contact" />
              <CardBody>
                <DetailGrid
                  cols={2}
                  items={[
                    { label: "Guardian", value: `${patient.guardianName || "—"}${patient.guardianRelation ? ` (${patient.guardianRelation})` : ""}` },
                    { label: "Phone", value: patient.phone },
                    { label: "Email", value: patient.email },
                    { label: "Location", value: [patient.city, patient.state, patient.country].filter(Boolean).join(", ") },
                    { label: "Referral source", value: patient.referralSource },
                    { label: "Date of birth", value: formatDate(patient.dob) },
                  ]}
                />
                {patient.notes && (
                  <p className="mt-3 rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-2 text-[12px] font-medium text-slate-500">
                    {patient.notes}
                  </p>
                )}
              </CardBody>
            </Card>
          </motion.div>

          {careCase && currentCycleEvents.length > 0 && (
            <motion.div variants={staggerItem}>
              <Card>
                <CardHeader
                  title={`Cycle ${careCase.currentCycle} touchpoints`}
                  subtitle="The current cycle only — the full timeline is on the Schedule tab"
                  actions={
                    <Button variant="ghost" size="sm" onClick={() => setTab("schedule")}>
                      Full schedule
                    </Button>
                  }
                />
                <CardBody>
                  <Timeline events={currentCycleEvents} onSelect={openEvent} />
                </CardBody>
              </Card>
            </motion.div>
          )}
        </motion.div>
      )}

      {tab === "baseline" && baselineDraft && (
        <div className="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">
          <Card>
            <CardBody>
              <BaselineFields
                value={baselineDraft}
                onChange={setBaselineDraft}
                meta={meta}
                errors={baselineErrors}
              />
            </CardBody>
          </Card>

          <aside className="xl:sticky xl:top-20 xl:self-start">
            <Card>
              <CardBody className="flex flex-col gap-3.5">
                <SectionLabel>Activation gate</SectionLabel>
                <CompletionMeter
                  percent={verdict.percent}
                  label={verdict.complete ? "complete" : "incomplete"}
                />
                <p className="text-[11.5px] font-medium text-slate-500">
                  {verdict.ratedCount} of {verdict.totalCount} functional markers rated.
                </p>

                {verdict.complete ? (
                  <div className="rounded-xl border border-indigo-300/30 bg-indigo-50/60 px-3 py-2.5 text-[12px] text-indigo-700">
                    Baseline complete{baseline.completedAt ? ` on ${formatDate(baseline.completedAt)}` : ""}.
                  </div>
                ) : (
                  <LockRow tone="amber">
                    Case activation blocked — complete all baseline fields to proceed.
                  </LockRow>
                )}

                <Button onClick={() => void saveBaseline()} loading={savingBaseline}>
                  <Save className="size-3.5" /> Save baseline
                </Button>

                {!careCase && verdict.complete && (
                  <LinkButton href={`/patients/${patientId}/plan`} variant="secondary">
                    Select care plan <ArrowRight className="size-3.5" />
                  </LinkButton>
                )}
              </CardBody>
            </Card>
          </aside>
        </div>
      )}

      {tab === "plan" && (
        <Card>
          {careCase && careCase.plan ? (
            <>
              <CardHeader
                title={`${careCase.plan.name} — ${careCase.plan.priceLabel}`}
                subtitle={careCase.plan.cadenceLabel}
                badge={<Badge tone={toneForStatus(careCase.status)}>{statusLabel(careCase.status)}</Badge>}
              />
              <CardBody className="flex flex-col gap-4">
                <DetailGrid
                  cols={4}
                  items={[
                    { label: "Plan start date", value: formatDate(careCase.startDate) },
                    { label: "Plan end date", value: formatDate(careCase.endDate), tone: "locked" },
                    { label: "Current cycle", value: careCase.cycleLabel, tone: "locked" },
                    { label: "Plan status", value: statusLabel(careCase.status), tone: "success" },
                  ]}
                />

                <LockRow>
                  Follow-up frequency locked by plan — formal review every {careCase.plan.reviewIntervalDays} days,
                  adherence check every {careCase.plan.adherenceIntervalDays} days. Reschedule allowed ±
                  {careCase.plan.rescheduleWindowDays} days only.
                </LockRow>

                <div>
                  <SectionLabel>What this plan includes</SectionLabel>
                  <ul className="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                    {careCase.plan.features.map((feature) => (
                      <li key={feature} className="flex items-start gap-1.5 text-[12px] font-medium text-slate-500">
                        <CheckCircle2 className="mt-0.5 size-3.5 shrink-0 text-indigo-600" />
                        {feature}
                      </li>
                    ))}
                  </ul>
                </div>

                <div>
                  <SectionLabel>Cycle overview</SectionLabel>
                  <CycleOverview cycles={cycles} />
                </div>
              </CardBody>
            </>
          ) : (
            <EmptyState
              icon={Package}
              title="No plan selected"
              description="Activating a plan generates the schedule, the review cadence and the sign-off requirements."
              action={
                verdict.complete ? (
                  <LinkButton href={`/patients/${patientId}/plan`} variant="primary" size="sm">
                    Select care plan
                  </LinkButton>
                ) : undefined
              }
            />
          )}
        </Card>
      )}

      {tab === "schedule" && (
        <div className="flex flex-col gap-4">
          {careCase && (
            <Card>
              <CardHeader title="Cycle overview" subtitle="Touchpoints completed per cycle" />
              <CardBody>
                <CycleOverview cycles={cycles} />
              </CardBody>
            </Card>
          )}

          <Card>
            <CardHeader
              title="Follow-up schedule"
              subtitle="Auto-generated at activation — click a touchpoint to act on it"
              badge={<Badge tone="emerald">{schedule.length} touchpoints</Badge>}
            />
            <CardBody>
              {cycles.length > 0 ? (
                <div className="flex flex-col gap-6">
                  {cycles.map((cycle) => {
                    const cycleEvents = schedule.filter((event) => event.cycle === cycle.cycle);
                    if (!cycleEvents.length) return null;
                    return (
                      <div key={cycle.cycle}>
                        <SectionLabel hint={cycle.label}>
                          Cycle {cycle.cycle}
                          {cycle.isCurrent ? " — current" : ""}
                        </SectionLabel>
                        <Timeline events={cycleEvents} onSelect={openEvent} />
                      </div>
                    );
                  })}
                </div>
              ) : (
                <EmptyState
                  icon={CalendarClock}
                  title="No schedule yet"
                  description="The schedule is generated the moment a care plan is confirmed."
                />
              )}
            </CardBody>
          </Card>
        </div>
      )}

      {tab === "reviews" && (
        <div className="flex flex-col gap-3">
          {reviews.length === 0 ? (
            <Card>
              <EmptyState
                icon={ClipboardCheck}
                title="No reviews yet"
                description="A review opens from its scheduled touchpoint when the cycle ends."
                action={
                  careCase ? (
                    <Button variant="secondary" size="sm" onClick={() => setTab("schedule")}>
                      Open the schedule
                    </Button>
                  ) : undefined
                }
              />
            </Card>
          ) : (
            reviews.map((review) => (
              <Card key={review.id}>
                <CardHeader
                  title={`Cycle ${review.cycle} review — ${formatDate(review.reviewDate)}`}
                  subtitle={
                    review.protocolDecision
                      ? `Protocol: ${meta?.protocolDecisions.find((d) => d.key === review.protocolDecision)?.label ?? humanise(review.protocolDecision)}`
                      : "Not yet filled in"
                  }
                  badge={<Badge tone={toneForStatus(review.status)}>{statusLabel(review.status)}</Badge>}
                  actions={
                    <LinkButton href={`/reviews/${review.id}`} variant="secondary" size="sm">
                      Open review
                    </LinkButton>
                  }
                />
                <CardBody className="flex flex-col gap-3.5">
                  {review.improvements && (
                    <div>
                      <p className="text-[11px] font-medium text-slate-500">Improvements observed</p>
                      <p className="mt-0.5 text-[12.5px] font-medium text-slate-700">{review.improvements}</p>
                    </div>
                  )}
                  {review.stagnation && (
                    <div>
                      <p className="text-[11px] font-medium text-slate-500">Areas of stagnation / concern</p>
                      <p className="mt-0.5 text-[12.5px] font-medium text-slate-700">{review.stagnation}</p>
                    </div>
                  )}
                  {review.goals && review.goals.length > 0 && (
                    <div>
                      <p className="mb-1 text-[11px] font-medium text-slate-500">Goals for the next cycle</p>
                      <GoalList goals={review.goals} />
                    </div>
                  )}
                  {review.signoffs && review.signoffs.length > 0 && (
                    <div>
                      <p className="mb-1.5 text-[11px] font-medium text-slate-500">Sign-off</p>
                      <SignoffList signoffs={review.signoffs} />
                    </div>
                  )}
                </CardBody>
              </Card>
            ))
          )}
        </div>
      )}

      {tab === "escalations" && (
        <div className="flex flex-col gap-3">
          {escalations.length === 0 ? (
            <Card>
              <EmptyState
                icon={TriangleAlert}
                title="No escalations on this case"
                description="Escalate when a case stops moving, the clinical picture is mixed, or a parent's anxiety is unresolved."
                action={
                  careCase && can("founder", "senior_doctor", "case_doctor") ? (
                    <Button variant="danger" size="sm" onClick={() => setEscalationOpen(true)}>
                      Raise escalation
                    </Button>
                  ) : undefined
                }
              />
            </Card>
          ) : (
            escalations.map((escalation) => (
              <Card key={escalation.id}>
                <CardHeader
                  title={`${escalation.code} — raised ${formatDate(escalation.raisedAt)}`}
                  subtitle={`Level: ${escalation.level === "founder" ? "Founder" : "Senior Doctor"} · resolve by ${formatDate(escalation.dueDate)}`}
                  badge={
                    <Badge tone={escalation.isOverdue ? "rose" : toneForStatus(escalation.status)}>
                      {escalation.isOverdue ? `${Math.abs(escalation.daysLeft)}d overdue` : statusLabel(escalation.status)}
                    </Badge>
                  }
                  actions={
                    <LinkButton href={`/escalations/${escalation.id}`} variant="secondary" size="sm">
                      Open
                    </LinkButton>
                  }
                />
                <CardBody className="flex flex-col gap-2.5">
                  <ul className="flex flex-wrap gap-1.5">
                    {escalation.reasonLabels.map((label) => (
                      <li key={label}>
                        <Badge tone="amber">{label}</Badge>
                      </li>
                    ))}
                  </ul>
                  <p className="text-[12.5px] font-medium text-slate-700">{escalation.notes}</p>
                </CardBody>
              </Card>
            ))
          )}
        </div>
      )}

      {tab === "progress" && (
        <div className="flex flex-col gap-3">
          {progress.length === 0 ? (
            <Card>
              <EmptyState
                icon={TrendingUp}
                title="No progress reports yet"
                description="A report is generated automatically the moment a cycle review closes."
              />
            </Card>
          ) : (
            progress.map((report) => (
              <Card key={report.id}>
                <CardHeader
                  title={`Cycle ${report.cycle} — ${report.periodLabel}`}
                  subtitle={
                    report.sharedAt
                      ? `Shared with the parent via ${report.sharedChannel} on ${formatDate(report.sharedAt)}`
                      : "Not yet shared with the parent"
                  }
                  badge={<Badge tone={toneForStatus(report.overallTrend)}>{humanise(report.overallTrend)}</Badge>}
                  actions={
                    <LinkButton
                      href={`/progress/${report.caseId}/${report.cycle}`}
                      variant="secondary"
                      size="sm"
                    >
                      Open dashboard
                    </LinkButton>
                  }
                />
                <CardBody className="flex flex-col gap-4">
                  <div className="grid grid-cols-3 gap-3">
                    <Metric value={humanise(report.overallTrend)} label="Overall trend" tone="emerald" />
                    <Metric value={`${report.goalsProgressing} / ${report.goalsTotal}`} label="Goals progressing" />
                    <Metric value={`${report.adherencePercent}%`} label="Medicine adherence" />
                  </div>
                  <AreaBars areas={report.areas} />
                </CardBody>
              </Card>
            ))
          )}
        </div>
      )}

      {tab === "medicine" && (
        <div className="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
          <div className="flex flex-col gap-4">
            {supply ? (
              <Card
                className={cn(
                  supply.isOutOfStock && "border-amber-200/70",
                  !supply.isOutOfStock && supply.state === "overdue" && "border-rose-200/70",
                  !supply.isOutOfStock && supply.state === "due_today" && "border-emerald-200/70"
                )}
              >
                <CardHeader
                  title="Medicine supply"
                  subtitle={`Every ${supply.intervalDays} days · the next reminder fires on the due date`}
                  badge={
                    <>
                      {supply.isOutOfStock && (
                        <Badge tone="amber">
                          <PackageX className="size-3" /> Out of stock
                        </Badge>
                      )}
                      <Badge tone={supply.state === "overdue" ? "rose" : supply.state === "due_today" ? "emerald" : "amber"}>
                        {supply.stateLabel}
                      </Badge>
                    </>
                  }
                />
                <CardBody className="flex flex-col gap-5">
                  <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <Metric
                      value={supply.lastDeliveredOn ? formatDate(supply.lastDeliveredOn) : "Plan start"}
                      label="Last delivered"
                    />
                    <Metric
                      value={formatDate(supply.nextDueOn)}
                      label="Next due"
                      tone={supply.state === "overdue" ? "rose" : supply.state === "due_today" ? "emerald" : "indigo"}
                    />
                    <Metric value={`${supply.intervalDays} days`} label="Cycle" />
                  </div>

                  {supply.isOutOfStock && (
                    <div className="flex items-start gap-3 rounded-xl border border-amber-200/70 bg-amber-50 px-4 py-3">
                      <span className="grid size-9 shrink-0 place-items-center rounded-full bg-white text-amber-600 ring-1 ring-inset ring-amber-100">
                        <PackageX className="size-4" />
                      </span>
                      <p className="text-[12.5px] font-semibold text-amber-800">
                        Waiting on stock
                        {supply.outOfStockSince ? ` since ${formatDate(supply.outOfStockSince)}` : ""} — delivery
                        deferred to {formatDate(supply.nextDueOn)}.
                        {supply.stockNote && (
                          <span className="mt-0.5 block text-[11.5px] font-medium text-amber-700/80">
                            {supply.stockNote}
                          </span>
                        )}
                      </p>
                    </div>
                  )}

                  <div className="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <p className="min-w-0 flex-1 text-[12.5px] font-semibold text-slate-600">
                      {supply.isOutOfStock
                        ? "Mark it delivered once stock arrives — that clears the shortage and restarts the cycle."
                        : supply.state === "overdue"
                          ? `This supply was due ${formatDate(supply.nextDueOn)}. Mark the delivery as soon as it goes out.`
                          : supply.state === "due_today"
                            ? "Due today. Marking the delivery sets the next reminder automatically."
                            : `Nothing to do yet — the next supply is due ${formatDate(supply.nextDueOn)}.`}
                    </p>
                    <Button
                      onClick={() => setDeliverOpen(true)}
                      className={cn(supply.state !== "upcoming" && "from-emerald-600 to-teal-600 shadow-emerald-500/25")}
                    >
                      <CalendarCheck2 className="size-4" /> Mark delivered
                    </Button>
                    {!supply.isOutOfStock && (
                      <Button variant="secondary" onClick={() => setOutOfStockOpen(true)}>
                        <PackageX className="size-4" /> Out of stock
                      </Button>
                    )}
                  </div>

                  <div>
                    <SectionLabel hint="most recent first">Supply history</SectionLabel>
                    {medicine.history.length === 0 ? (
                      <p className="text-[12.5px] font-medium text-slate-500">Nothing logged yet.</p>
                    ) : (
                      <ul className="divide-y divide-slate-100 rounded-xl border border-slate-200/70">
                        {medicine.history.map((delivery) => (
                          <li key={delivery.id} className="flex items-center gap-3 px-4 py-3">
                            <span
                              className={cn(
                                "grid size-8 shrink-0 place-items-center rounded-full ring-1 ring-inset",
                                delivery.isDeferral
                                  ? "bg-amber-50 text-amber-600 ring-amber-100"
                                  : "bg-slate-100/70 text-slate-500 ring-white/80"
                              )}
                            >
                              {delivery.isDeferral ? (
                                <PackageX className="size-4" strokeWidth={1.9} />
                              ) : (
                                <Truck className="size-4" strokeWidth={1.9} />
                              )}
                            </span>
                            <span className="min-w-0 flex-1">
                              <span className="block text-[12.5px] font-bold tabular-nums text-navy">
                                {formatDate(delivery.deliveredOn)}
                                <span className="ml-2 font-medium text-slate-400">→ next {formatDate(delivery.nextDueOn)}</span>
                              </span>
                              <span className="block text-[11.5px] font-medium text-slate-500">
                                {delivery.isDeferral ? delivery.kindLabel : delivery.modeLabel}
                                {delivery.reference ? ` · ${delivery.reference}` : ""}
                                {delivery.deliveredByName ? ` · ${delivery.deliveredByName}` : ""}
                                {delivery.notes ? ` · ${delivery.notes}` : ""}
                              </span>
                            </span>
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                </CardBody>
              </Card>
            ) : (
              <Card>
                <EmptyState
                  icon={Pill}
                  title="No medicine cycle yet"
                  description="The 15-day supply cycle starts automatically when a care plan is activated."
                />
              </Card>
            )}
          </div>

          {supply && (
            <aside className="xl:sticky xl:top-20 xl:self-start">
              <Card>
                <CardBody className="flex flex-col gap-3.5">
                  <SectionLabel>Cycle settings</SectionLabel>
                  <p className="text-[12px] font-medium text-slate-500">
                    Change how often medicine goes out, or move the next due date by hand if a parent asks.
                  </p>
                  <Field label="Deliver every" hint="days">
                    <Input
                      type="number"
                      min={1}
                      max={90}
                      value={intervalDraft}
                      onChange={(event) => setIntervalDraft(event.target.value)}
                    />
                  </Field>
                  <Field label="Next due date">
                    <Input type="date" value={nextDueDraft} onChange={(event) => setNextDueDraft(event.target.value)} />
                  </Field>
                  <Button variant="secondary" loading={medicineBusy} onClick={() => void saveMedicineCycle()}>
                    <Settings2 className="size-4" /> Save cycle
                  </Button>
                  <LockRow>
                    The interval applies from the next delivery — the current due date only changes if you edit it above.
                  </LockRow>
                </CardBody>
              </Card>
            </aside>
          )}
        </div>
      )}

      {/* ---------------------------------------------------------- modals -- */}

      <Modal
        open={Boolean(activeEvent)}
        onClose={() => setActiveEvent(null)}
        title={activeEvent?.title ?? ""}
        description={
          activeEvent
            ? `Cycle ${activeEvent.cycle} · due ${formatDate(activeEvent.dueDate)} · ${statusLabel(activeEvent.status)}`
            : undefined
        }
        width="md"
      >
        {activeEvent && (
          <div className="flex flex-col gap-4">
            {eventError && (
              <p className="rounded-xl border border-rose-300/25 bg-rose-50 px-3 py-2 text-[12px] text-rose-600">
                {eventError}
              </p>
            )}

            {/* Adherence capture */}
            {activeEvent.type === "adherence" && activeEvent.status !== "done" && (
              <div>
                <SectionLabel hint="non-consult touchpoint">Record the adherence check</SectionLabel>
                <FieldRow cols={2}>
                  <Field label="Medicine compliance">
                    <Select
                      value={adherence.compliance}
                      onChange={(event) => setAdherence({ ...adherence, compliance: event.target.value })}
                      options={[
                        { value: "full", label: "Full — taken as prescribed" },
                        { value: "partial", label: "Partial — some doses missed" },
                        { value: "none", label: "None — not taken" },
                      ]}
                    />
                  </Field>
                  <Field label="Refill status">
                    <Select
                      value={adherence.refill}
                      onChange={(event) => setAdherence({ ...adherence, refill: event.target.value })}
                      options={[
                        { value: "stocked", label: "Stocked" },
                        { value: "due", label: "Refill due" },
                        { value: "requested", label: "Refill requested" },
                        { value: "lapsed", label: "Lapsed" },
                      ]}
                    />
                  </Field>
                </FieldRow>

                <div className="mt-3">
                  <CheckItem
                    checked={adherence.parentConcern}
                    onToggle={() => setAdherence({ ...adherence, parentConcern: !adherence.parentConcern })}
                  >
                    Parent raised a new concern — flag it for the next review
                  </CheckItem>
                </div>

                <Field label="Parent note" className="mt-3">
                  <Textarea
                    value={adherence.parentNote}
                    onChange={(event) => setAdherence({ ...adherence, parentNote: event.target.value })}
                    placeholder="What the parent reported in this call."
                    rows={2}
                  />
                </Field>

                <Button
                  className="mt-3.5 w-full"
                  loading={eventBusy}
                  onClick={() =>
                    void runEventAction(
                      () => api.post(`/schedule/${activeEvent.id}/adherence`, adherence),
                      "Adherence check recorded."
                    )
                  }
                >
                  <Pill className="size-3.5" /> Record &amp; mark done
                </Button>
              </div>
            )}

            {/* Formal review */}
            {(activeEvent.type === "review" || activeEvent.type === "founder_review") && (
              <div>
                <SectionLabel>Formal review</SectionLabel>
                <p className="mb-3 text-[12px] font-medium text-slate-500">
                  Opening the review creates it on the forced template. It cannot be closed until every mandatory
                  field is filled and the required sign-offs land.
                </p>
                <Button className="w-full" loading={eventBusy} onClick={() => void openReview(activeEvent)}>
                  <ClipboardCheck className="size-3.5" /> Open cycle {activeEvent.cycle} review
                </Button>
              </div>
            )}

            {/* Reschedule */}
            {activeEvent.status !== "done" && (
              <div className="border-t border-slate-200/70 pt-4">
                <SectionLabel hint={`locked to ±${careCase?.plan?.rescheduleWindowDays ?? 7} days`}>
                  Reschedule
                </SectionLabel>
                <p className="mb-2.5 text-[11.5px] font-medium text-slate-500">
                  Plan date: {formatDate(activeEvent.originalDueDate)}. The window is measured from that date, not
                  from the current one.
                </p>
                <div className="flex gap-2">
                  <Input
                    type="date"
                    value={rescheduleDate}
                    onChange={(event) => setRescheduleDate(event.target.value)}
                    min={todayISO()}
                  />
                  <Button
                    variant="secondary"
                    loading={eventBusy}
                    onClick={() =>
                      void runEventAction(
                        () => api.post(`/schedule/${activeEvent.id}/reschedule`, { dueDate: rescheduleDate }),
                        "Touchpoint rescheduled."
                      )
                    }
                  >
                    <CalendarDays className="size-3.5" /> Move
                  </Button>
                </div>
              </div>
            )}

            {/* Mark done */}
            {activeEvent.status !== "done" && activeEvent.type !== "adherence" && (
              <Button
                variant="secondary"
                loading={eventBusy}
                onClick={() =>
                  void runEventAction(
                    () => api.post(`/schedule/${activeEvent.id}/complete`),
                    "Touchpoint marked done."
                  )
                }
              >
                <CheckCircle2 className="size-3.5" /> Mark as done
              </Button>
            )}

            {activeEvent.status === "done" && (
              <p className="rounded-xl border border-indigo-300/25 bg-indigo-50/60 px-3 py-2.5 text-[12px] text-indigo-700">
                Completed{activeEvent.completedAt ? ` on ${formatDate(activeEvent.completedAt)}` : ""}.
                {activeEvent.notes ? ` ${activeEvent.notes}` : ""}
              </p>
            )}
          </div>
        )}
      </Modal>

      <OutOfStockModal
        supply={supply}
        open={outOfStockOpen}
        onClose={() => setOutOfStockOpen(false)}
        onDeferred={() => void reload()}
      />

      <DeliverMedicineModal
        supply={supply}
        open={deliverOpen}
        onClose={() => setDeliverOpen(false)}
        onDelivered={() => void reload()}
      />

      <Modal
        open={escalationOpen}
        onClose={() => setEscalationOpen(false)}
        title="Raise an escalation"
        description="The Senior Doctor reviews first; the Founder activates only if it is not resolved. Resolution window is locked to 7 days."
        width="lg"
        footer={
          <>
            <Button variant="secondary" onClick={() => setEscalationOpen(false)}>
              Cancel
            </Button>
            <Button variant="danger" loading={escBusy} onClick={() => void raiseEscalation()}>
              Raise escalation
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-4">
          <div>
            <SectionLabel hint="select all that apply">Reason for escalation</SectionLabel>
            <div className="flex flex-col gap-0.5">
              {(meta?.escalationReasons ?? []).map((reason) => (
                <CheckItem
                  key={reason.key}
                  checked={escReasons.includes(reason.key)}
                  onToggle={() =>
                    setEscReasons((current) =>
                      current.includes(reason.key)
                        ? current.filter((key) => key !== reason.key)
                        : [...current, reason.key]
                    )
                  }
                >
                  {reason.label}
                </CheckItem>
              ))}
            </div>
            {escErrors.reasons && <p className="mt-1.5 text-[11.5px] text-rose-600">{escErrors.reasons}</p>}
          </div>

          <Field label="Clinical notes for escalation" required error={escErrors.notes}>
            <Textarea
              value={escNotes}
              onChange={(event) => setEscNotes(event.target.value)}
              placeholder="What has been tried, what the parent is reporting, and what decision you need from the Senior Doctor."
              rows={4}
              invalid={Boolean(escErrors.notes)}
            />
          </Field>

          <LockRow>Resolution timeframe is locked at 7 days from the moment this escalation is raised.</LockRow>
        </div>
      </Modal>
    </PageShell>
  );
}
