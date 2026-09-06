"use client";

import { useEffect, useState } from "react";
import { motion } from "framer-motion";
import { Plus, Pencil, UserCog } from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useApi } from "@/lib/useApi";
import { useSession } from "@/lib/session";
import { useToast } from "@/components/ui/Toast";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Avatar,
  Badge,
  Button,
  Card,
  CardBody,
  ErrorNote,
  Metric,
  ProgressBar,
  SectionLabel,
  Spinner,
} from "@/components/ui/primitives";
import { Field, FieldRow, Input, Select } from "@/components/ui/form";
import { Modal } from "@/components/ui/Modal";
import { stagger, staggerItem } from "@/lib/motion";
import type { Role, TeamMember } from "@/types";

interface Draft {
  name: string;
  username: string;
  password: string;
  email: string;
  phone: string;
  title: string;
  role: Role;
  status: string;
}

const blankDraft: Draft = {
  name: "",
  username: "",
  password: "",
  email: "",
  phone: "",
  title: "",
  role: "case_doctor",
  status: "active",
};

/** Team, roles, caseload and the per-doctor CMS audit scorecard. */
export default function TeamPage() {
  const { can, meta } = useSession();
  const toast = useToast();
  const { data, error, loading, reload } = useApi<{ team: TeamMember[] }>("/team");

  const [editing, setEditing] = useState<TeamMember | null>(null);
  const [creating, setCreating] = useState(false);
  const [draft, setDraft] = useState<Draft>(blankDraft);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (editing) {
      setDraft({
        name: editing.name,
        username: editing.username,
        password: "",
        email: editing.email,
        phone: editing.phone,
        title: editing.title,
        role: editing.role,
        status: editing.status,
      });
    } else if (creating) {
      setDraft(blankDraft);
    }
  }, [editing, creating]);

  const team = data?.team ?? [];
  const isFounder = can("founder");

  async function save() {
    setSaving(true);
    setErrors({});
    try {
      if (editing) {
        await api.put(`/team/${editing.id}`, draft);
        toast(`${draft.name} updated.`);
      } else {
        await api.post("/team", draft);
        toast(`${draft.name} added to the team.`);
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
        title="Team & roles"
        eyebrow="Practice"
        eyebrowIcon={UserCog}
        description="Who can do what, how much each doctor is carrying, and how well the CMS is being operated."
        actions={
          isFounder ? (
            <Button onClick={() => setCreating(true)}>
              <Plus className="size-3.5" /> Add team member
            </Button>
          ) : undefined
        }
      />

      {loading && <Spinner label="Loading the team" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && team.length > 0 && (
        <motion.div
          variants={stagger}
          initial="hidden"
          animate="show"
          className="grid grid-cols-1 gap-4 lg:grid-cols-2"
        >
          {team.map((member) => (
            <motion.div key={member.id} variants={staggerItem}>
              <Card className="h-full">
                <CardBody className="flex flex-col gap-4">
                  <div className="flex items-start gap-3">
                    <Avatar name={member.name} className="size-10 text-[13px]" />
                    <div className="min-w-0 flex-1">
                      <p className="text-[14px] font-bold text-navy">{member.name}</p>
                      <p className="text-[11.5px] font-medium text-slate-500">{member.title || member.roleLabel}</p>
                      <div className="mt-1.5 flex flex-wrap gap-1.5">
                        <Badge tone={member.role === "founder" ? "violet" : "indigo"}>{member.roleLabel}</Badge>
                        {member.pendingSignoffs > 0 && (
                          <Badge tone="amber">{member.pendingSignoffs} sign-off pending</Badge>
                        )}
                        {member.openEscalations > 0 && (
                          <Badge tone="rose">{member.openEscalations} escalation</Badge>
                        )}
                      </div>
                    </div>
                    {isFounder && (
                      <Button variant="ghost" size="sm" onClick={() => setEditing(member)}>
                        <Pencil className="size-3.5" />
                      </Button>
                    )}
                  </div>

                  <div className="grid grid-cols-3 gap-2.5">
                    <Metric value={member.caseload} label="Active cases" />
                    <Metric value={member.scorecard.reviewsClosed} label="Reviews closed" />
                    <Metric
                      value={member.scorecard.satisfaction || "—"}
                      label="Parent rating"
                      tone={member.scorecard.satisfaction >= 4.5 ? "emerald" : "slate"}
                    />
                  </div>

                  <div className="flex flex-col gap-2.5 border-t border-slate-200/70 pt-3">
                    <SectionLabel>CMS audit scorecard</SectionLabel>
                    <div>
                      <div className="mb-1 flex justify-between text-[11.5px]">
                        <span className="font-semibold text-slate-500">Template completeness</span>
                        <span className="font-black tabular-nums text-navy">{member.scorecard.completeness}%</span>
                      </div>
                      <ProgressBar
                        percent={member.scorecard.completeness}
                        tone={member.scorecard.completeness === 100 ? "emerald" : "amber"}
                      />
                    </div>
                    <div>
                      <div className="mb-1 flex justify-between text-[11.5px]">
                        <span className="font-semibold text-slate-500">On-time review closure</span>
                        <span className="font-black tabular-nums text-navy">{member.scorecard.onTimeRate}%</span>
                      </div>
                      <ProgressBar
                        percent={member.scorecard.onTimeRate}
                        tone={member.scorecard.onTimeRate >= 80 ? "emerald" : "amber"}
                      />
                    </div>
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
        title={editing ? `Edit ${editing.name}` : "Add a team member"}
        width="md"
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
              {editing ? "Save changes" : "Add member"}
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3.5">
          <FieldRow cols={2}>
            <Field label="Full name" required error={errors.name}>
              <Input
                value={draft.name}
                onChange={(event) => setDraft({ ...draft, name: event.target.value })}
                invalid={Boolean(errors.name)}
                placeholder="Dr. Anjali Rao"
              />
            </Field>
            <Field label="Role" required error={errors.role}>
              <Select
                value={draft.role}
                onChange={(event) => setDraft({ ...draft, role: event.target.value as Role })}
                invalid={Boolean(errors.role)}
                options={(meta?.roles ?? []).map((role) => ({ value: role.key, label: role.label }))}
              />
            </Field>
          </FieldRow>

          {!editing && (
            <FieldRow cols={2}>
              <Field label="Username" required error={errors.username}>
                <Input
                  value={draft.username}
                  onChange={(event) => setDraft({ ...draft, username: event.target.value })}
                  invalid={Boolean(errors.username)}
                />
              </Field>
              <Field label="Password" required hint="at least 8 characters" error={errors.password}>
                <Input
                  type="password"
                  value={draft.password}
                  onChange={(event) => setDraft({ ...draft, password: event.target.value })}
                  invalid={Boolean(errors.password)}
                />
              </Field>
            </FieldRow>
          )}

          <Field label="Title">
            <Input
              value={draft.title}
              onChange={(event) => setDraft({ ...draft, title: event.target.value })}
              placeholder="Case Doctor — Developmental Care"
            />
          </Field>

          <FieldRow cols={2}>
            <Field label="Email">
              <Input
                type="email"
                value={draft.email}
                onChange={(event) => setDraft({ ...draft, email: event.target.value })}
              />
            </Field>
            <Field label="Phone">
              <Input
                value={draft.phone}
                onChange={(event) => setDraft({ ...draft, phone: event.target.value })}
              />
            </Field>
          </FieldRow>

          {editing && (
            <Field label="Status">
              <Select
                value={draft.status}
                onChange={(event) => setDraft({ ...draft, status: event.target.value })}
                options={[
                  { value: "active", label: "Active" },
                  { value: "inactive", label: "Inactive" },
                ]}
              />
            </Field>
          )}
        </div>
      </Modal>
    </PageShell>
  );
}
