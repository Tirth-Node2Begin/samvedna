"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { ArrowRight, Save } from "lucide-react";
import { api, ApiError } from "@/lib/api";
import { useSession } from "@/lib/session";
import { useToast } from "@/components/ui/Toast";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Button,
  Card,
  CardBody,
  LockRow,
  SectionLabel,
  InfoNote,
} from "@/components/ui/primitives";
import { Field, FieldRow, Input, Select, Textarea } from "@/components/ui/form";
import {
  BaselineFields,
  emptyBaseline,
  type BaselineDraft,
} from "@/components/flow/BaselineFields";
import { CompletionMeter } from "@/components/flow/panels";
import type { BaselineVerdict, Patient } from "@/types";

interface ProfileDraft {
  childName: string;
  dob: string;
  gender: string;
  guardianName: string;
  guardianRelation: string;
  phone: string;
  altPhone: string;
  email: string;
  city: string;
  state: string;
  country: string;
  referralSource: string;
  notes: string;
}

const emptyProfile: ProfileDraft = {
  childName: "",
  dob: "",
  gender: "unspecified",
  guardianName: "",
  guardianRelation: "Mother",
  phone: "",
  altPhone: "",
  email: "",
  city: "",
  state: "",
  country: "India",
  referralSource: "",
  notes: "",
};

/**
 * Step 1 — patient registration and baseline.
 *
 * The page deliberately does NOT block the save. A draft can always be stored;
 * what is blocked is moving on to a care plan, and the lock strip at the bottom
 * says exactly which fields are standing in the way.
 */
export default function NewPatientPage() {
  const router = useRouter();
  const toast = useToast();
  const { meta } = useSession();

  const [profile, setProfile] = useState<ProfileDraft>(emptyProfile);
  const [baseline, setBaseline] = useState<BaselineDraft>(() => emptyBaseline(null));
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);

  // The marker catalogue arrives with /meta, which may land after first paint.
  useEffect(() => {
    if (meta && baseline.markers.length === 0) {
      setBaseline((current) => ({ ...current, markers: emptyBaseline(meta).markers }));
    }
  }, [meta, baseline.markers.length]);

  const ratedCount = baseline.markers.filter((marker) => marker.rating).length;
  const concernCount = baseline.concerns.filter((concern) => concern.trim()).length;

  // Mirrors Baseline::evaluate() on the server — five gates, scored as n/5.
  const gates = [
    Boolean(baseline.conditionType),
    Boolean(baseline.severity),
    Boolean(baseline.therapyInvolvement.trim()),
    concernCount === 3,
    baseline.markers.length > 0 && ratedCount === baseline.markers.length,
  ];
  const passed = gates.filter(Boolean).length;
  const percent = Math.round((passed / gates.length) * 100);
  const canActivate = passed === gates.length;

  async function save(thenSelectPlan: boolean) {
    if (!profile.childName.trim()) {
      setErrors({ childName: "Child name is required." });
      toast("The child's name is required before saving.", "error");
      return;
    }

    setSaving(true);
    setErrors({});

    try {
      const response = await api.post<{ patient: Patient; verdict: BaselineVerdict }>("/patients", {
        ...profile,
        ...baseline,
        concerns: baseline.concerns.filter((concern) => concern.trim()),
        markers: baseline.markers.map((marker) => ({ key: marker.key, rating: marker.rating })),
      });

      if (thenSelectPlan && response.verdict.complete) {
        toast(`${response.patient.childName} registered. Now choose a care plan.`);
        router.push(`/patients/${response.patient.id}/plan`);
        return;
      }

      if (thenSelectPlan) {
        setErrors(response.verdict.missing);
        toast("Saved as a draft — the baseline is not complete enough to activate a case yet.", "error");
      } else {
        toast(`${response.patient.childName} saved as a draft.`);
      }
      router.push(`/patients/${response.patient.id}`);
    } catch (caught) {
      if (caught instanceof ApiError) {
        setErrors(caught.fields);
        toast(caught.message, "error");
      } else {
        toast("Could not save this patient.", "error");
      }
      setSaving(false);
    }
  }

  return (
    <PageShell>
      <PageHeader
        title="New patient intake"
        description="Register the child and capture the clinical starting point. The baseline is what every later review is measured against."
        step={{ number: 1, label: "Intake" }}
      />

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div className="flex flex-col gap-4">
          {/* Profile */}
          <Card>
            <CardBody>
              <SectionLabel>Patient profile</SectionLabel>

              <FieldRow cols={2}>
                <Field label="Child's name" required error={errors.childName}>
                  <Input
                    value={profile.childName}
                    onChange={(event) => setProfile({ ...profile, childName: event.target.value })}
                    placeholder="Aarav Mehta"
                    invalid={Boolean(errors.childName)}
                    autoFocus
                  />
                </Field>

                <Field label="Date of birth">
                  <Input
                    type="date"
                    value={profile.dob}
                    onChange={(event) => setProfile({ ...profile, dob: event.target.value })}
                  />
                </Field>
              </FieldRow>

              <FieldRow cols={3}>
                <Field label="Gender" className="mt-3.5">
                  <Select
                    value={profile.gender}
                    onChange={(event) => setProfile({ ...profile, gender: event.target.value })}
                    options={[
                      { value: "unspecified", label: "Not specified" },
                      { value: "male", label: "Male" },
                      { value: "female", label: "Female" },
                      { value: "other", label: "Other" },
                    ]}
                  />
                </Field>

                <Field label="Guardian name" className="mt-3.5">
                  <Input
                    value={profile.guardianName}
                    onChange={(event) => setProfile({ ...profile, guardianName: event.target.value })}
                    placeholder="Priya Mehta"
                  />
                </Field>

                <Field label="Relation to child" className="mt-3.5">
                  <Select
                    value={profile.guardianRelation}
                    onChange={(event) => setProfile({ ...profile, guardianRelation: event.target.value })}
                    options={["Mother", "Father", "Grandparent", "Guardian", "Other"].map((value) => ({
                      value,
                      label: value,
                    }))}
                  />
                </Field>
              </FieldRow>

              <FieldRow cols={3}>
                <Field label="Contact number" className="mt-3.5">
                  <Input
                    value={profile.phone}
                    onChange={(event) => setProfile({ ...profile, phone: event.target.value })}
                    placeholder="+91 98765 43210"
                  />
                </Field>

                <Field label="Alternate number" className="mt-3.5">
                  <Input
                    value={profile.altPhone}
                    onChange={(event) => setProfile({ ...profile, altPhone: event.target.value })}
                  />
                </Field>

                <Field label="Email" className="mt-3.5">
                  <Input
                    type="email"
                    value={profile.email}
                    onChange={(event) => setProfile({ ...profile, email: event.target.value })}
                    placeholder="parent@example.com"
                  />
                </Field>
              </FieldRow>

              <FieldRow cols={4}>
                <Field label="City" className="mt-3.5">
                  <Input
                    value={profile.city}
                    onChange={(event) => setProfile({ ...profile, city: event.target.value })}
                  />
                </Field>
                <Field label="State" className="mt-3.5">
                  <Input
                    value={profile.state}
                    onChange={(event) => setProfile({ ...profile, state: event.target.value })}
                  />
                </Field>
                <Field label="Country" className="mt-3.5">
                  <Input
                    value={profile.country}
                    onChange={(event) => setProfile({ ...profile, country: event.target.value })}
                  />
                </Field>
                <Field label="Referral source" className="mt-3.5">
                  <Input
                    value={profile.referralSource}
                    onChange={(event) => setProfile({ ...profile, referralSource: event.target.value })}
                    placeholder="Google, referral, Instagram…"
                  />
                </Field>
              </FieldRow>

              <Field label="Internal notes" hint="not shared with the parent" className="mt-3.5">
                <Textarea
                  value={profile.notes}
                  onChange={(event) => setProfile({ ...profile, notes: event.target.value })}
                  rows={2}
                />
              </Field>
            </CardBody>
          </Card>

          {/* Baseline */}
          <Card>
            <CardBody>
              <BaselineFields value={baseline} onChange={setBaseline} meta={meta} errors={errors} />
            </CardBody>
          </Card>
        </div>

        {/* Sticky gate panel */}
        <aside className="xl:sticky xl:top-20 xl:self-start">
          <Card>
            <CardBody className="flex flex-col gap-3.5">
              <SectionLabel>Case activation</SectionLabel>

              <CompletionMeter percent={percent} label={`${passed} of ${gates.length} gates cleared`} />

              <ul className="flex flex-col gap-1.5 text-[11.5px]">
                {[
                  ["Condition classification", gates[0]],
                  ["Severity level", gates[1]],
                  ["Therapy involvement", gates[2]],
                  [`Top 3 parent concerns (${concernCount}/3)`, gates[3]],
                  [`Functional markers (${ratedCount}/${baseline.markers.length || 9})`, gates[4]],
                ].map(([label, done]) => (
                  <li key={String(label)} className="flex items-center gap-2">
                    <span
                      className={`size-1.5 shrink-0 rounded-full ${done ? "bg-indigo-600" : "bg-amber-500"}`}
                      aria-hidden
                    />
                    <span className={done ? "text-slate-500" : "text-amber-700"}>{label}</span>
                  </li>
                ))}
              </ul>

              {canActivate ? (
                <InfoNote tone="emerald">
                  Baseline complete. Saving will take you straight to care plan selection.
                </InfoNote>
              ) : (
                <LockRow tone="amber">
                  Case activation blocked — complete all baseline fields to proceed. Saving a draft is always
                  allowed.
                </LockRow>
              )}

              <div className="flex flex-col gap-2">
                <Button onClick={() => void save(true)} loading={saving} disabled={!canActivate}>
                  Save &amp; select care plan <ArrowRight className="size-3.5" />
                </Button>
                <Button variant="secondary" onClick={() => void save(false)} loading={saving}>
                  <Save className="size-3.5" /> Save draft
                </Button>
              </div>
            </CardBody>
          </Card>
        </aside>
      </div>
    </PageShell>
  );
}
