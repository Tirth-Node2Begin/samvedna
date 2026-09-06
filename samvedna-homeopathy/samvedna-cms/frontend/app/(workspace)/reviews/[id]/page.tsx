"use client";

import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { AlertCircle, ArrowRight, Plus, Save, Trash2, TriangleAlert, PenLine } from "lucide-react";
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
  LinkButton,
  LockRow,
  SectionLabel,
  Spinner,
  toneForStatus,
} from "@/components/ui/primitives";
import { Field, FieldRow, Input, RadioCards, Select, Textarea } from "@/components/ui/form";
import { CompletionMeter, DetailGrid, GoalList, SignoffList } from "@/components/flow/panels";
import { formatDate, humanise, statusLabel } from "@/lib/format";
import type { Baseline, CareCase, Goal, Patient, Plan, Review, Signoff } from "@/types";

interface Payload {
  review: Review;
  goals: Goal[];
  previousGoals: Goal[];
  signoffs: Signoff[];
  canSign: boolean;
  case: CareCase | null;
  patient: Patient | null;
  baseline: Baseline | null;
  plan: Plan | null;
  adherence: number;
  mandatory: Record<string, string>;
}

interface Draft {
  improvements: string;
  stagnation: string;
  protocolDecision: string;
  protocolRationale: string;
  therapyNotes: string;
  adherencePercent: number;
  goals: Goal[];
}

const emptyGoal: Goal = { title: "", metric: "", status: "pending" };

/**
 * Step 4 — the bi-monthly review.
 *
 * The template is "forced" in a specific sense: a draft can always be saved,
 * but submitting runs the same five-gate validation the server runs, and a
 * protocol that is anything other than "continued as-is" makes the rationale
 * field mandatory. Nothing here can be waved through.
 */
export default function ReviewPage() {
  const params = useParams<{ id: string }>();
  const reviewId = Number(params.id);
  const router = useRouter();
  const toast = useToast();
  const { meta, user } = useSession();

  const { data, error, loading, reload } = useApi<Payload>(`/reviews/${reviewId}`);

  const [draft, setDraft] = useState<Draft | null>(null);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState<"save" | "submit" | "sign" | null>(null);
  const [signComment, setSignComment] = useState("");

  useEffect(() => {
    if (!data) return;
    setDraft({
      improvements: data.review.improvements,
      stagnation: data.review.stagnation,
      protocolDecision: data.review.protocolDecision,
      protocolRationale: data.review.protocolRationale,
      therapyNotes: data.review.therapyNotes,
      adherencePercent: data.review.adherencePercent || data.adherence,
      goals: data.goals.length ? data.goals : [{ ...emptyGoal }, { ...emptyGoal }, { ...emptyGoal }],
    });
  }, [data]);

  if (loading) return <Spinner label="Loading review" />;
  if (error)
    return (
      <PageShell>
        <ErrorNote message={error} retry={reload} />
      </PageShell>
    );
  if (!data || !draft) return null;

  const { review, patient, case: careCase, plan, signoffs, canSign, previousGoals } = data;
  const closed = review.status === "closed";
  const readOnly = closed;

  const patch = (partial: Partial<Draft>) => setDraft({ ...draft, ...partial });

  const setGoal = (index: number, partial: Partial<Goal>) => {
    const goals = draft.goals.map((goal, i) => (i === index ? { ...goal, ...partial } : goal));
    patch({ goals });
  };

  const rationaleRequired = Boolean(draft.protocolDecision) && draft.protocolDecision !== "continued";
  const filledGoals = draft.goals.filter((goal) => goal.title.trim());

  // Same five gates the server scores, so the meter never disagrees with a submit.
  const gates = [
    Boolean(draft.improvements.trim()),
    Boolean(draft.stagnation.trim()),
    Boolean(draft.protocolDecision) && (!rationaleRequired || Boolean(draft.protocolRationale.trim())),
    filledGoals.length > 0,
    Boolean(draft.therapyNotes.trim()),
  ];
  const passed = gates.filter(Boolean).length;
  const percent = Math.round((passed / gates.length) * 100);

  const payload = () => ({ ...draft, goals: draft.goals.filter((goal) => goal.title.trim()) });

  async function save() {
    setBusy("save");
    setErrors({});
    try {
      const response = await api.put<{ fields: Record<string, string> }>(`/reviews/${reviewId}`, payload());
      setErrors(response.fields);
      toast("Draft saved.");
      await reload();
    } catch (caught) {
      toast(caught instanceof ApiError ? caught.message : "Could not save the draft.", "error");
    } finally {
      setBusy(null);
    }
  }

  async function submit() {
    setBusy("submit");
    setErrors({});
    try {
      const response = await api.post<{ closed: boolean; progressId?: number; message?: string }>(
        `/reviews/${reviewId}/submit`,
        payload()
      );
      if (response.closed) {
        toast("Review closed — the progress dashboard has been generated.");
        router.push(`/progress/${review.caseId}/${review.cycle}`);
        return;
      }
      toast(response.message ?? "Review submitted and waiting on sign-off.", "info");
      await reload();
    } catch (caught) {
      if (caught instanceof ApiError) {
        setErrors(caught.fields);
        toast(caught.message, "error");
      } else {
        toast("Could not submit the review.", "error");
      }
    } finally {
      setBusy(null);
    }
  }

  async function sign() {
    setBusy("sign");
    try {
      const response = await api.post<{ closed: boolean }>(`/reviews/${reviewId}/signoff`, {
        comment: signComment,
      });
      if (response.closed) {
        toast("Signed off — the review is closed and the dashboard is ready.");
        router.push(`/progress/${review.caseId}/${review.cycle}`);
        return;
      }
      toast("Your sign-off is recorded.");
      await reload();
    } catch (caught) {
      toast(caught instanceof ApiError ? caught.message : "Could not record the sign-off.", "error");
    } finally {
      setBusy(null);
    }
  }

  const pendingRoles = signoffs.filter((s) => s.status === "pending").map((s) => s.roleLabel);

  return (
    <PageShell>
      <PageHeader
        title={`Cycle ${review.cycle} review`}
        description={`${patient?.childName ?? ""} · ${plan?.name ?? ""} plan · reviewed ${formatDate(review.reviewDate)}`}
        step={{ number: 4, label: "Bi-monthly review" }}
        actions={<Badge tone={toneForStatus(review.status)}>{statusLabel(review.status)}</Badge>}
      />

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_312px]">
        <div className="flex flex-col gap-4">
          {/* Context */}
          <Card>
            <CardBody>
              <DetailGrid
                cols={4}
                items={[
                  { label: "Patient", value: patient?.childName ?? "—" },
                  { label: "Cycle", value: `Cycle ${review.cycle} of ${careCase?.totalCycles ?? "—"}`, tone: "locked" },
                  { label: "Review date", value: formatDate(review.reviewDate) },
                  { label: "Next review date", value: formatDate(review.nextReviewDate), tone: "locked" },
                ]}
              />
            </CardBody>
          </Card>

          {/* Carried-forward goals */}
          {previousGoals.length > 0 && (
            <Card>
              <CardHeader
                title={`Goals set in cycle ${review.cycle - 1}`}
                subtitle="What this review is scoring"
              />
              <CardBody>
                <GoalList goals={previousGoals} />
              </CardBody>
            </Card>
          )}

          {/* The forced template */}
          <Card>
            <CardHeader
              title="Mandatory review fields"
              subtitle="The review cannot close without every one of these"
              badge={<Badge tone={percent === 100 ? "emerald" : "amber"}>{passed} of 5</Badge>}
            />
            <CardBody className="flex flex-col gap-4">
              <Field label="Improvements observed in the last cycle" required error={errors.improvements}>
                <Textarea
                  value={draft.improvements}
                  onChange={(event) => patch({ improvements: event.target.value })}
                  placeholder="Be concrete and measurable — 'meltdowns down from 4/week to 2/week' rather than 'doing better'."
                  rows={3}
                  invalid={Boolean(errors.improvements)}
                  readOnly={readOnly}
                />
              </Field>

              <Field label="Areas of stagnation / concern" required error={errors.stagnation}>
                <Textarea
                  value={draft.stagnation}
                  onChange={(event) => patch({ stagnation: event.target.value })}
                  placeholder="What has not moved, and what is getting worse."
                  rows={3}
                  invalid={Boolean(errors.stagnation)}
                  readOnly={readOnly}
                />
              </Field>

              <Field label="Protocol decision" required error={errors.protocolDecision}>
                <RadioCards
                  value={draft.protocolDecision}
                  onChange={(value) => patch({ protocolDecision: value })}
                  columns={2}
                  invalid={Boolean(errors.protocolDecision)}
                  options={(meta?.protocolDecisions ?? []).map((decision) => ({
                    value: decision.key,
                    label: decision.label,
                  }))}
                />
              </Field>

              {rationaleRequired && (
                <Field
                  label="Rationale for the change"
                  required
                  hint="mandatory whenever the protocol is not continued as-is"
                  error={errors.protocolRationale}
                >
                  <Textarea
                    value={draft.protocolRationale}
                    onChange={(event) => patch({ protocolRationale: event.target.value })}
                    placeholder="Which remedy or dosage changed, and the clinical observation that justified it."
                    rows={3}
                    invalid={Boolean(errors.protocolRationale)}
                    readOnly={readOnly}
                  />
                </Field>
              )}

              {/* Goals */}
              <div>
                <div className="mb-2 flex items-center justify-between gap-2">
                  <label className="text-[11.5px] font-medium text-slate-500">
                    Goals for the next cycle <span className="text-rose-600">*</span>
                  </label>
                  {!readOnly && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => patch({ goals: [...draft.goals, { ...emptyGoal }] })}
                    >
                      <Plus className="size-3.5" /> Add goal
                    </Button>
                  )}
                </div>

                <div className="flex flex-col gap-2">
                  {draft.goals.map((goal, index) => (
                    <div
                      key={index}
                      className="grid grid-cols-1 gap-2 rounded-xl border border-slate-200/70 bg-slate-50 p-2.5 sm:grid-cols-[1.4fr_1fr_auto_auto]"
                    >
                      <Input
                        value={goal.title}
                        onChange={(event) => setGoal(index, { title: event.target.value })}
                        placeholder={
                          ["Improve verbal initiation", "Consistent 8-hour sleep", "Reduce school meltdowns"][index] ??
                          "Goal"
                        }
                        readOnly={readOnly}
                        invalid={Boolean(errors.goals) && !goal.title.trim() && index === 0}
                      />
                      <Input
                        value={goal.metric}
                        onChange={(event) => setGoal(index, { metric: event.target.value })}
                        placeholder="Measurable target"
                        readOnly={readOnly}
                      />
                      <Select
                        value={goal.status}
                        onChange={(event) => setGoal(index, { status: event.target.value as Goal["status"] })}
                        disabled={readOnly}
                        options={[
                          { value: "pending", label: "Pending" },
                          { value: "progressing", label: "Progressing" },
                          { value: "achieved", label: "Achieved" },
                          { value: "missed", label: "Missed" },
                        ]}
                      />
                      {!readOnly && (
                        <button
                          type="button"
                          onClick={() => patch({ goals: draft.goals.filter((_, i) => i !== index) })}
                          className="rounded-xl px-2 text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600"
                          aria-label="Remove goal"
                        >
                          <Trash2 className="size-3.5" />
                        </button>
                      )}
                    </div>
                  ))}
                </div>

                {errors.goals && (
                  <p className="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-rose-600">
                    <AlertCircle className="size-3.5" /> {errors.goals}
                  </p>
                )}
              </div>

              <FieldRow cols={2}>
                <Field label="Therapy coordination notes" required error={errors.therapyNotes}>
                  <Textarea
                    value={draft.therapyNotes}
                    onChange={(event) => patch({ therapyNotes: event.target.value })}
                    placeholder="What was agreed with the speech therapist, OT, school or other clinicians."
                    rows={3}
                    invalid={Boolean(errors.therapyNotes)}
                    readOnly={readOnly}
                  />
                </Field>

                <Field
                  label="Medicine adherence this cycle"
                  hint={`${data.adherence}% from the adherence checks`}
                >
                  <Input
                    type="number"
                    min={0}
                    max={100}
                    value={draft.adherencePercent}
                    onChange={(event) => patch({ adherencePercent: Number(event.target.value) })}
                    readOnly={readOnly}
                  />
                </Field>
              </FieldRow>
            </CardBody>
          </Card>

          {/* Escalation decision */}
          <Card>
            <CardHeader title="Escalation decision" subtitle="Does this case need a Senior or Founder opinion?" />
            <CardBody className="flex flex-col gap-3">
              {review.escalate ? (
                <div className="rounded-xl border border-amber-300/35 bg-amber-50 px-3 py-2.5 text-[12px] text-amber-700">
                  Escalated. {review.escalateNote}
                </div>
              ) : (
                <DetailGrid
                  cols={2}
                  items={[
                    { label: "Escalate to Senior / Founder?", value: "No — progress on track" },
                    { label: "Next review date (auto)", value: formatDate(review.nextReviewDate), tone: "locked" },
                  ]}
                />
              )}

              {!closed && patient && (
                <div>
                  <LinkButton href={`/patients/${patient.id}?tab=escalations`} variant="danger" size="sm">
                    <TriangleAlert className="size-3.5" /> Raise an escalation on this case
                  </LinkButton>
                </div>
              )}
            </CardBody>
          </Card>

          {/* Sign-off */}
          <Card>
            <CardHeader
              title="Sign-off"
              subtitle={
                plan?.requiresSenior
                  ? `${plan.name} plan — Senior Doctor sign-off is mandatory`
                  : "Case Doctor sign-off closes the review on this plan"
              }
            />
            <CardBody className="flex flex-col gap-3.5">
              <SignoffList signoffs={signoffs} />

              {review.status === "awaiting_signoff" && pendingRoles.length > 0 && (
                <LockRow tone="amber">
                  Review cannot be closed until {pendingRoles.join(" and ")} signs off.
                </LockRow>
              )}

              {canSign && review.status === "awaiting_signoff" && (
                <div className="rounded-xl border border-indigo-300/30 bg-indigo-50/60 p-3.5">
                  <p className="mb-2 text-[12px] font-medium text-indigo-700">
                    You have a pending sign-off on this review.
                  </p>
                  <Textarea
                    value={signComment}
                    onChange={(event) => setSignComment(event.target.value)}
                    placeholder="Optional comment — what you agreed with, or what you want watched next cycle."
                    rows={2}
                  />
                  <Button className="mt-2.5" loading={busy === "sign"} onClick={() => void sign()}>
                    <PenLine className="size-3.5" /> Sign off as {user?.roleLabel}
                  </Button>
                </div>
              )}

              {closed && (
                <div className="rounded-xl border border-indigo-300/30 bg-indigo-50/60 px-3 py-2.5 text-[12px] text-indigo-700">
                  Closed on {formatDate(review.closedAt)} —{" "}
                  {review.closedOnTime ? "on time" : "late"}
                  {review.parentSatisfaction ? ` · parent satisfaction ${review.parentSatisfaction}/5` : ""}.
                </div>
              )}
            </CardBody>
          </Card>
        </div>

        {/* Sticky action rail */}
        <aside className="xl:sticky xl:top-20 xl:self-start">
          <Card>
            <CardBody className="flex flex-col gap-3.5">
              <SectionLabel>Template completeness</SectionLabel>
              <CompletionMeter percent={percent} label={`${passed} of 5 fields`} />

              <ul className="flex flex-col gap-1.5 text-[11.5px]">
                {[
                  ["Improvements observed", gates[0]],
                  ["Stagnation / concern", gates[1]],
                  [rationaleRequired ? "Protocol decision + rationale" : "Protocol decision", gates[2]],
                  [`Next-cycle goals (${filledGoals.length})`, gates[3]],
                  ["Therapy coordination notes", gates[4]],
                ].map(([label, done]) => (
                  <li key={String(label)} className="flex items-center gap-2">
                    <span className={`size-1.5 shrink-0 rounded-full ${done ? "bg-indigo-600" : "bg-amber-500"}`} />
                    <span className={done ? "text-slate-500" : "text-amber-700"}>{label}</span>
                  </li>
                ))}
              </ul>

              {rationaleRequired && (
                <LockRow>
                  You changed the protocol — the rationale field is now mandatory and will be kept in the case
                  record permanently.
                </LockRow>
              )}

              {!closed && (
                <div className="flex flex-col gap-2">
                  <Button onClick={() => void submit()} loading={busy === "submit"} disabled={percent !== 100}>
                    Submit &amp; generate dashboard <ArrowRight className="size-3.5" />
                  </Button>
                  <Button variant="secondary" onClick={() => void save()} loading={busy === "save"}>
                    <Save className="size-3.5" /> Save draft
                  </Button>
                </div>
              )}

              {closed && careCase && (
                <LinkButton href={`/progress/${careCase.id}/${review.cycle}`} variant="primary">
                  View progress dashboard <ArrowRight className="size-3.5" />
                </LinkButton>
              )}

              {careCase && (
                <p className="border-t border-slate-200/70 pt-3 text-[11px] font-medium text-slate-400">
                  {careCase.code} · {careCase.plan?.name} · review every {careCase.plan?.reviewIntervalDays} days ·
                  protocol {humanise(draft.protocolDecision) || "not decided"}
                </p>
              )}
            </CardBody>
          </Card>
        </aside>
      </div>
    </PageShell>
  );
}
