"use client";

import { useState } from "react";
import { useParams } from "next/navigation";
import { ArrowUpRight, CheckCircle2, Clock } from "lucide-react";
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
  ProgressBar,
  SectionLabel,
  Spinner,
  toneForStatus,
} from "@/components/ui/primitives";
import { CheckItem, Field, Textarea } from "@/components/ui/form";
import { DetailGrid, EscalationChain } from "@/components/flow/panels";
import { formatDate, formatDateTime, statusLabel } from "@/lib/format";
import type { CareCase, Escalation, EscalationStep, Patient, Review } from "@/types";

interface Payload {
  escalation: Escalation & { raisedByName?: string };
  steps: EscalationStep[];
  reasons: { key: string; label: string }[];
  case: CareCase | null;
  patient: Patient | null;
  review: Review | null;
}

/** Step 5 — escalation management. */
export default function EscalationPage() {
  const params = useParams<{ id: string }>();
  const escalationId = Number(params.id);
  const toast = useToast();
  const { can } = useSession();

  const { data, error, loading, reload } = useApi<Payload>(`/escalations/${escalationId}`);
  const [resolution, setResolution] = useState("");
  const [fieldError, setFieldError] = useState("");
  const [busy, setBusy] = useState<"advance" | "resolve" | null>(null);

  if (loading) return <Spinner label="Loading escalation" />;
  if (error)
    return (
      <PageShell>
        <ErrorNote message={error} retry={reload} />
      </PageShell>
    );
  if (!data) return null;

  const { escalation, steps, reasons, patient, review } = data;
  const open = escalation.status === "open" || escalation.status === "in_progress";
  const spent = Math.max(
    0,
    Math.min(100, ((escalation.slaDays - escalation.daysLeft) / escalation.slaDays) * 100)
  );

  async function advance() {
    setBusy("advance");
    try {
      await api.post(`/escalations/${escalationId}/advance`);
      toast("Escalated to the Founder.");
      await reload();
    } catch (caught) {
      toast(caught instanceof ApiError ? caught.message : "Could not advance the escalation.", "error");
    } finally {
      setBusy(null);
    }
  }

  async function resolve() {
    setBusy("resolve");
    setFieldError("");
    try {
      await api.post(`/escalations/${escalationId}/resolve`, { resolutionNotes: resolution });
      toast("Escalation resolved.");
      setResolution("");
      await reload();
    } catch (caught) {
      if (caught instanceof ApiError) {
        setFieldError(caught.fields.resolutionNotes ?? "");
        toast(caught.message, "error");
      }
    } finally {
      setBusy(null);
    }
  }

  return (
    <PageShell>
      <PageHeader
        title={`Escalation ${escalation.code}`}
        description={`${patient?.childName ?? ""} · raised ${formatDateTime(escalation.raisedAt)}${escalation.raisedByName ? ` by ${escalation.raisedByName}` : ""}`}
        step={{ number: 5, label: "Escalation" }}
        actions={
          <Badge tone={escalation.isOverdue ? "rose" : toneForStatus(escalation.status)}>
            {statusLabel(escalation.status)}
          </Badge>
        }
      />

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_312px]">
        <div className="flex flex-col gap-4">
          {/* SLA banner */}
          <Card
            className={escalation.isOverdue ? "border-rose-300/40" : open ? "border-amber-300/35" : undefined}
          >
            <CardBody className="flex flex-col gap-3">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <p
                  className={`flex items-center gap-1.5 text-[13px] font-medium ${escalation.isOverdue ? "text-rose-600" : open ? "text-amber-700" : "text-indigo-700"}`}
                >
                  <Clock className="size-4" />
                  {escalation.status === "resolved"
                    ? `Resolved on ${formatDate(escalation.resolvedAt)}`
                    : escalation.isOverdue
                      ? `${Math.abs(escalation.daysLeft)} days past the resolution deadline`
                      : `${escalation.daysLeft} of ${escalation.slaDays} days remaining`}
                </p>
                <span className="text-[11.5px] font-medium text-slate-500">Resolve by {formatDate(escalation.dueDate)}</span>
              </div>
              {open && (
                <ProgressBar
                  percent={spent}
                  tone={escalation.isOverdue ? "rose" : spent > 70 ? "amber" : "indigo"}
                />
              )}
            </CardBody>
          </Card>

          {/* Reasons */}
          <Card>
            <CardHeader title="Reason for escalation" subtitle="Recorded when the escalation was raised" />
            <CardBody>
              <div className="flex flex-col gap-0.5">
                {reasons.map((reason) => (
                  <CheckItem key={reason.key} checked={escalation.reasons.includes(reason.key)} disabled>
                    {reason.label}
                  </CheckItem>
                ))}
              </div>
            </CardBody>
          </Card>

          {/* Clinical notes */}
          <Card>
            <CardHeader title="Clinical notes" />
            <CardBody>
              <p className="whitespace-pre-line rounded-xl border border-slate-200/70 bg-slate-50 px-3.5 py-3 text-[12.5px] leading-relaxed text-slate-700">
                {escalation.notes || "No notes recorded."}
              </p>

              {review && (
                <div className="mt-3">
                  <LinkButton href={`/reviews/${review.id}`} variant="secondary" size="sm">
                    Open the cycle {review.cycle} review this came from
                  </LinkButton>
                </div>
              )}
            </CardBody>
          </Card>

          {/* Resolution */}
          <Card>
            <CardHeader
              title="Resolution"
              subtitle="An escalation cannot close without notes explaining how it was resolved"
            />
            <CardBody className="flex flex-col gap-3">
              <DetailGrid
                cols={2}
                items={[
                  { label: "Resolution timeframe", value: `Must resolve within ${escalation.slaDays} days`, tone: "locked" },
                  {
                    label: "Resolution status",
                    value: statusLabel(escalation.status),
                    tone: escalation.status === "resolved" ? "success" : "warn",
                  },
                ]}
              />

              {escalation.status === "resolved" ? (
                <p className="whitespace-pre-line rounded-xl border border-indigo-300/30 bg-indigo-50/60 px-3.5 py-3 text-[12.5px] text-indigo-700">
                  {escalation.resolutionNotes}
                </p>
              ) : can("founder", "senior_doctor") ? (
                <Field label="Resolution notes" required error={fieldError}>
                  <Textarea
                    value={resolution}
                    onChange={(event) => setResolution(event.target.value)}
                    placeholder="What was decided, what changes to the protocol or plan, and what the parent was told."
                    rows={4}
                    invalid={Boolean(fieldError)}
                  />
                </Field>
              ) : (
                <LockRow>
                  Only a Senior Doctor or the Founder can resolve an escalation. Yours is recorded and visible to
                  both.
                </LockRow>
              )}
            </CardBody>
          </Card>
        </div>

        {/* Chain + actions */}
        <aside className="flex flex-col gap-4 xl:sticky xl:top-20 xl:self-start">
          <Card>
            <CardBody className="flex flex-col gap-3.5">
              <SectionLabel>Approval chain</SectionLabel>
              <EscalationChain steps={steps} />

              {open && can("founder", "senior_doctor") && (
                <div className="flex flex-col gap-2 border-t border-slate-200/70 pt-3.5">
                  <Button loading={busy === "resolve"} onClick={() => void resolve()}>
                    <CheckCircle2 className="size-3.5" /> Mark resolved
                  </Button>
                  {escalation.level === "senior" && (
                    <Button variant="secondary" loading={busy === "advance"} onClick={() => void advance()}>
                      <ArrowUpRight className="size-3.5" /> Escalate to the Founder
                    </Button>
                  )}
                </div>
              )}
            </CardBody>
          </Card>

          {patient && (
            <Card>
              <CardBody className="flex flex-col gap-2.5">
                <SectionLabel>Case</SectionLabel>
                <p className="text-[12.5px] font-medium text-slate-700">{patient.childName}</p>
                <p className="text-[11.5px] font-medium text-slate-500">
                  {patient.code}
                  {data.case ? ` · ${data.case.plan?.name} · ${data.case.cycleLabel}` : ""}
                </p>
                <LinkButton href={`/patients/${patient.id}`} variant="secondary" size="sm">
                  Open the case workspace
                </LinkButton>
              </CardBody>
            </Card>
          )}
        </aside>
      </div>
    </PageShell>
  );
}
