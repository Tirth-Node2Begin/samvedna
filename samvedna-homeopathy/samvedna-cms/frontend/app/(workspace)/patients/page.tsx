"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { Users, Lock, ChevronRight, UserPlus } from "lucide-react";
import { useApi } from "@/lib/useApi";
import { useDebouncedValue } from "@/lib/useDebouncedValue";
import { useSession } from "@/lib/session";
import { qs } from "@/lib/api";
import { PageHeader, PageShell } from "@/components/layout/PageHeader";
import {
  Badge,
  Card,
  EmptyState,
  ErrorNote,
  LinkButton,
  Spinner,
  toneForStatus,
} from "@/components/ui/primitives";
import { SearchInput, Select } from "@/components/ui/form";
import { cn, formatDate, humanise, statusLabel } from "@/lib/format";
import { stagger, staggerItem } from "@/lib/motion";
import { EYEBROW, HERO_PRIMARY_BTN, TABLE_HEAD, TABLE_ROW } from "@/lib/tokens";
import type { Patient } from "@/types";

interface Payload {
  patients: Patient[];
  counts: { total: number; active: number; draft: number };
}

export default function PatientsPage() {
  const { meta } = useSession();
  const [search, setSearch] = useState("");
  const debouncedSearch = useDebouncedValue(search.trim(), 350);
  const [status, setStatus] = useState("");
  const [condition, setCondition] = useState("");
  const [severity, setSeverity] = useState("");

  // Filters are server-side so the list stays honest for large registers.
  const path = useMemo(
    () => `/patients${qs({ search: debouncedSearch, status, condition, severity })}`,
    [debouncedSearch, status, condition, severity]
  );
  const { data, error, loading, reload } = useApi<Payload>(path);

  const patients = data?.patients ?? [];

  return (
    <PageShell wide>
      <PageHeader
        title="Patient register"
        description="Every child in the practice, with their baseline status and live care plan."
        step={{ number: 1, label: "Intake" }}
        eyebrowIcon={Users}
        actions={
          <Link href="/patients/new" className={HERO_PRIMARY_BTN}>
            <UserPlus className="size-4" /> New patient intake
          </Link>
        }
      />

      {/* Filter toolbar */}
      <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
        <SearchInput
          value={search}
          onChange={setSearch}
          placeholder="Search name, guardian, code or phone"
          className="w-full sm:w-80"
        />
        <div className="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 lg:w-auto lg:flex-1">
          <Select
            value={status}
            onChange={(event) => setStatus(event.target.value)}
            placeholder="All statuses"
            className="min-w-[9rem]"
            options={(meta?.patientStatuses ?? []).map((value) => ({
              value,
              label: statusLabel(value),
            }))}
          />
          <Select
            value={condition}
            onChange={(event) => setCondition(event.target.value)}
            placeholder="All conditions"
            className="min-w-[11rem]"
            options={(meta?.conditions ?? []).map((value) => ({ value, label: value }))}
          />
          <Select
            value={severity}
            onChange={(event) => setSeverity(event.target.value)}
            placeholder="All severities"
            className="min-w-[9rem]"
            options={(meta?.severities ?? []).map((s) => ({ value: s.key, label: s.label }))}
          />
        </div>

        {data && (
          <p className={cn(EYEBROW, "basis-full whitespace-nowrap lg:ml-auto lg:basis-auto")}>
            {data.counts.total} total · {data.counts.active} active · {data.counts.draft} draft
          </p>
        )}
      </div>

      {loading && <Spinner label="Loading the register" />}
      {error && <ErrorNote message={error} retry={reload} />}

      {!loading && !error && patients.length === 0 && (
        <Card>
          <EmptyState
            icon={Users}
            title="No patients match these filters"
            description="Clear the filters, or register the first patient to start a case."
            action={
              <LinkButton href="/patients/new" variant="primary" size="sm">
                New patient intake
              </LinkButton>
            }
          />
        </Card>
      )}

      {/* Register table */}
      {!loading && patients.length > 0 && (
        <motion.div
          variants={stagger}
          initial="hidden"
          animate="show"
          className="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm"
        >
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead>
                <tr className={TABLE_HEAD}>
                  <th className="px-6 py-3.5">Patient</th>
                  <th className="hidden px-6 py-3.5 sm:table-cell">Condition</th>
                  <th className="hidden px-6 py-3.5 lg:table-cell">Care plan</th>
                  <th className="hidden px-6 py-3.5 xl:table-cell">Case doctor</th>
                  <th className="px-6 py-3.5 text-right">Status</th>
                  <th className="w-[52px] px-4 py-3.5" />
                </tr>
              </thead>

              <tbody className="divide-y divide-slate-100 text-sm">
                {patients.map((patient) => {
                  const blocked = !patient.baselineComplete;
                  return (
                    <motion.tr
                      key={patient.id}
                      variants={staggerItem}
                      className={`group ${TABLE_ROW}`}
                    >
                      <td className="px-6 py-3.5">
                        <Link href={`/patients/${patient.id}`} className="block">
                          <span className="block text-[13.5px] font-bold text-navy">
                            {patient.childName}
                          </span>
                          <span className="mt-0.5 block text-[11.5px] font-medium text-slate-400">
                            {patient.code}
                            {patient.age !== null ? ` · ${patient.age} yrs` : ""}
                            {patient.guardianName ? ` · ${patient.guardianName}` : ""}
                          </span>
                        </Link>
                      </td>

                      <td className="hidden px-6 py-3.5 sm:table-cell">
                        <span className="block text-[12.5px] font-semibold text-slate-700">
                          {patient.condition || "—"}
                        </span>
                        <span className="block text-[11.5px] font-medium text-slate-400">
                          {patient.severity ? humanise(patient.severity) : "Severity not set"}
                        </span>
                      </td>

                      <td className="hidden px-6 py-3.5 lg:table-cell">
                        <span className="block text-[12.5px] font-semibold text-slate-700">
                          {patient.planName || "No plan yet"}
                        </span>
                        <span className="block text-[11.5px] font-medium text-slate-400">
                          {patient.cycleLabel || (blocked ? "Activation blocked" : "Ready to activate")}
                        </span>
                      </td>

                      <td className="hidden px-6 py-3.5 xl:table-cell">
                        <span className="block text-[12.5px] font-semibold text-slate-700">
                          {patient.caseDoctorName || "Unassigned"}
                        </span>
                        <span className="block text-[11.5px] font-medium text-slate-400">
                          {patient.startDate ? `Started ${formatDate(patient.startDate)}` : "—"}
                        </span>
                      </td>

                      <td className="px-6 py-3.5">
                        <span className="flex flex-wrap items-center justify-end gap-1.5">
                          {blocked && (
                            <Badge tone="rose">
                              <Lock className="size-3" /> Baseline
                            </Badge>
                          )}
                          <Badge tone={toneForStatus(patient.status)}>
                            {statusLabel(patient.status)}
                          </Badge>
                        </span>
                      </td>

                      <td className="px-4 py-3.5 text-center">
                        <Link
                          href={`/patients/${patient.id}`}
                          aria-label={`Open ${patient.childName}`}
                          className="inline-grid size-8 place-items-center rounded-full text-slate-300 transition-colors group-hover:bg-indigo-50 group-hover:text-indigo-600"
                        >
                          <ChevronRight className="size-4" />
                        </Link>
                      </td>
                    </motion.tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </motion.div>
      )}
    </PageShell>
  );
}
