"use client";

import { useState } from "react";
import { useParams } from "next/navigation";
import { motion } from "framer-motion";
import { Send, Printer, ArrowLeft, ArrowRight, Sparkles } from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useApi } from "@/lib/useApi";
import { useToast } from "@/components/ui/Toast";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Button,
  Card,
  CardBody,
  CardHeader,
  ErrorNote,
  InfoNote,
  LinkButton,
  Metric,
  SectionLabel,
  Spinner,
  toneForStatus,
} from "@/components/ui/primitives";
import { Select } from "@/components/ui/form";
import { AreaBars, GoalList } from "@/components/flow/panels";
import { formatDate, humanise } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import type { CareCase, Goal, Patient, ProgressReport, Review } from "@/types";

interface Payload {
  report: ProgressReport;
  case: CareCase;
  patient: Patient | null;
  review: Review | null;
  objectives: Goal[];
  history: ProgressReport[];
  nextCycle: number;
}

/** Step 6 — the auto-generated progress dashboard. */
export default function ProgressDashboardPage() {
  const params = useParams<{ caseId: string; cycle: string }>();
  const caseId = Number(params.caseId);
  const cycle = Number(params.cycle);
  const toast = useToast();

  const { data, error, loading, reload } = useApi<Payload>(`/progress/${caseId}/${cycle}`);
  const [channel, setChannel] = useState("whatsapp");
  const [sharing, setSharing] = useState(false);

  if (loading) return <Spinner label="Loading progress dashboard" />;
  if (error)
    return (
      <PageShell>
        <ErrorNote message={error} retry={reload} />
      </PageShell>
    );
  if (!data) return null;

  const { report, case: careCase, patient, objectives, history, nextCycle } = data;
  const scorecard = report.scorecard ?? {};

  async function share() {
    setSharing(true);
    try {
      await api.post(`/progress/${report.id}/share`, { channel });
      toast(`Dashboard shared with the parent via ${channel}.`);
      await reload();
    } catch (caught) {
      toast(caught instanceof ApiError ? caught.message : "Could not share the dashboard.", "error");
    } finally {
      setSharing(false);
    }
  }

  const prevCycle = cycle > 1 ? cycle - 1 : null;
  const hasNext = history.some((entry) => entry.cycle === nextCycle) && nextCycle !== cycle;

  return (
    <PageShell>
      <PageHeader
        title={`Cycle ${report.cycle} progress dashboard`}
        description={`${patient?.childName ?? ""} · ${report.periodLabel}`}
        step={{ number: 6, label: "Progress" }}
        actions={
          <>
            <Badge tone={toneForStatus(report.overallTrend)}>{humanise(report.overallTrend)}</Badge>
            <Button variant="secondary" size="sm" onClick={() => window.print()} className="no-print">
              <Printer className="size-3.5" /> Print
            </Button>
          </>
        }
      />

      <div className="mb-4">
        <InfoNote tone="indigo">
          <Sparkles className="mr-1 inline size-3.5" />
          Auto-generated at the close of the cycle {report.cycle} review. Nothing on this page was typed by hand —
          the trend, the area bars and the objectives all come from the review template.
        </InfoNote>
      </div>

      <motion.div variants={stagger} initial="hidden" animate="show" className="flex flex-col gap-4">
        {/* Headline metrics */}
        <motion.div variants={staggerItem}>
          <Card>
            <CardHeader
              title={patient?.childName ?? "Patient"}
              subtitle={`${careCase.plan?.name ?? ""} plan · ${careCase.code} · review period ${report.periodLabel}`}
            />
            <CardBody>
              <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <Metric value={humanise(report.overallTrend)} label="Overall trend" tone="emerald" />
                <Metric
                  value={`${report.goalsProgressing} / ${report.goalsTotal}`}
                  label="Goals progressing"
                />
                <Metric value={`${report.adherencePercent}%`} label="Medicine adherence" />
              </div>
            </CardBody>
          </Card>
        </motion.div>

        <div className="grid grid-cols-1 gap-4 lg:grid-cols-[1.35fr_1fr]">
          {/* Area-wise progress */}
          <motion.div variants={staggerItem}>
            <Card className="h-full">
              <CardHeader
                title="Area-wise progress"
                subtitle="Charted against the exact markers captured at intake"
              />
              <CardBody>
                <AreaBars areas={report.areas} />
              </CardBody>
            </Card>
          </motion.div>

          <motion.div variants={staggerItem} className="flex flex-col gap-4">
            {/* Next cycle objectives */}
            <Card>
              <CardHeader
                title={`Cycle ${nextCycle} objectives`}
                subtitle="Carried forward from the review goals"
              />
              <CardBody>
                <GoalList
                  goals={
                    objectives.length
                      ? objectives
                      : report.objectives.map((objective) => ({
                          title: objective.title,
                          metric: objective.metric,
                          status: objective.done ? "achieved" : "pending",
                        }))
                  }
                />
              </CardBody>
            </Card>

            {/* Internal scorecard */}
            <Card>
              <CardHeader title="CMS audit scorecard" subtitle="Internal only — not shared with the parent" />
              <CardBody>
                <div className="grid grid-cols-3 gap-3">
                  <Metric
                    value={`${scorecard.completeness ?? 0}%`}
                    label="CMS completeness"
                    tone={(scorecard.completeness ?? 0) === 100 ? "emerald" : "amber"}
                  />
                  <Metric
                    value={scorecard.closure ?? "—"}
                    label="Review closure"
                    tone={scorecard.onTime ? "emerald" : "amber"}
                  />
                  <Metric value={`${scorecard.satisfaction ?? "—"}/5`} label="Parent satisfaction" />
                </div>
                <p className="mt-3 text-[11.5px] font-medium text-slate-500">
                  Case Doctor: <span className="text-slate-700">{scorecard.doctor ?? "Unassigned"}</span>
                </p>
              </CardBody>
            </Card>
          </motion.div>
        </div>

        {/* Sharing */}
        <motion.div variants={staggerItem}>
          <Card className="no-print">
            <CardHeader
              title="Share with the parent"
              subtitle="Every cycle closes with the family being told what changed"
              badge={
                report.sharedAt ? (
                  <Badge tone="emerald">
                    Shared via {report.sharedChannel} on {formatDate(report.sharedAt)}
                  </Badge>
                ) : (
                  <Badge tone="amber">Not shared yet</Badge>
                )
              }
            />
            <CardBody className="flex flex-wrap items-end gap-3">
              <div className="w-full sm:w-56">
                <SectionLabel>Channel</SectionLabel>
                <Select
                  value={channel}
                  onChange={(event) => setChannel(event.target.value)}
                  options={[
                    { value: "whatsapp", label: "WhatsApp" },
                    { value: "portal", label: "Parent portal" },
                    { value: "email", label: "Email" },
                  ]}
                />
              </div>
              <Button loading={sharing} onClick={() => void share()}>
                <Send className="size-3.5" /> {report.sharedAt ? "Share again" : "Share with parent"}
              </Button>
            </CardBody>
          </Card>
        </motion.div>

        {/* Cycle navigation */}
        <motion.div variants={staggerItem} className="flex flex-wrap items-center justify-between gap-2 no-print">
          {prevCycle ? (
            <LinkButton href={`/progress/${caseId}/${prevCycle}`} variant="secondary" size="sm">
              <ArrowLeft className="size-3.5" /> Cycle {prevCycle}
            </LinkButton>
          ) : (
            <span />
          )}

          <div className="flex gap-2">
            {patient && (
              <LinkButton href={`/patients/${patient.id}?tab=schedule`} variant="secondary" size="sm">
                Next cycle schedule
              </LinkButton>
            )}
            {hasNext && (
              <LinkButton href={`/progress/${caseId}/${nextCycle}`} variant="primary" size="sm">
                Cycle {nextCycle} <ArrowRight className="size-3.5" />
              </LinkButton>
            )}
          </div>
        </motion.div>
      </motion.div>
    </PageShell>
  );
}
