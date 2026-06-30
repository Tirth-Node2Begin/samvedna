"use client";

import Image from "next/image";
import {
  Award,
  BadgeCheck,
  CalendarClock,
  GraduationCap,
  Languages,
  Sparkles,
  Stethoscope
} from "lucide-react";
import { useEffect, useId, useState, type ComponentType } from "react";
import Modal from "@/components/ui/Modal";
import type { TeamMember } from "@/types";
import { blurDataUrl } from "@/lib/utils";

type DoctorProfileModalProps = {
  member: TeamMember | null;
  onClose: () => void;
};

type ListSectionProps = {
  icon: ComponentType<{ className?: string }>;
  title: string;
  items: string[];
};

function ListSection({ icon: Icon, title, items }: ListSectionProps) {
  if (items.length === 0) return null;

  return (
    <div>
      <h3 className="flex items-center gap-2 font-display text-base font-semibold text-text">
        <Icon className="h-4 w-4 text-primary" aria-hidden="true" />
        {title}
      </h3>
      <ul className="mt-3 grid gap-2 sm:grid-cols-2">
        {items.map((item) => (
          <li key={item} className="flex items-start gap-2 text-sm leading-6 text-muted">
            <BadgeCheck className="mt-0.5 h-4 w-4 shrink-0 text-accent" aria-hidden="true" />
            <span>{item}</span>
          </li>
        ))}
      </ul>
    </div>
  );
}

export default function DoctorProfileModal({ member, onClose }: DoctorProfileModalProps) {
  const titleId = useId();
  // Retain the last selected member so content stays visible during the
  // modal's exit animation (when `member` has already been reset to null).
  const [shown, setShown] = useState<TeamMember | null>(member);

  useEffect(() => {
    if (member) setShown(member);
  }, [member]);

  return (
    <Modal open={Boolean(member)} onClose={onClose} labelledBy={titleId} className="max-w-3xl">
      {shown ? (
        <article>
          {/* Header */}
          <div className="grid gap-6 border-b border-border bg-primary/[0.03] p-6 sm:grid-cols-[160px_1fr] sm:p-8">
            <div className="relative mx-auto aspect-[4/5] w-40 overflow-hidden rounded-2xl bg-surface shadow-sm sm:mx-0 sm:w-full">
              <Image
                src={shown.image}
                alt={shown.alt}
                fill
                sizes="160px"
                className="object-cover object-[50%_15%]"
                placeholder="blur"
                blurDataURL={blurDataUrl}
              />
            </div>

            <div className="flex flex-col justify-center">
              <h2
                id={titleId}
                className="font-display text-2xl font-semibold text-text md:text-3xl"
              >
                {shown.name}
              </h2>
              <p className="mt-1 text-sm font-semibold text-primary">{shown.title}</p>
              <p className="mt-2 inline-flex items-center gap-1.5 text-sm text-muted">
                <Stethoscope className="h-4 w-4 text-primary/70" aria-hidden="true" />
                {shown.specialization}
              </p>

              <div className="mt-4 flex flex-wrap gap-2">
                <span className="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                  <CalendarClock className="h-3.5 w-3.5" aria-hidden="true" />
                  {shown.experience} Experience
                </span>
                {shown.profile.qualifications.map((qualification) => (
                  <span
                    key={qualification}
                    className="inline-flex items-center gap-1.5 rounded-full border border-border bg-white px-3 py-1 text-xs font-semibold text-text"
                  >
                    <GraduationCap className="h-3.5 w-3.5 text-primary/70" aria-hidden="true" />
                    {qualification}
                  </span>
                ))}
              </div>
            </div>
          </div>

          {/* Body */}
          <div className="space-y-7 p-6 sm:p-8">
            <div>
              <h3 className="flex items-center gap-2 font-display text-base font-semibold text-text">
                <Sparkles className="h-4 w-4 text-primary" aria-hidden="true" />
                About the doctor
              </h3>
              <p className="mt-3 text-sm leading-7 text-muted">{shown.profile.about}</p>
            </div>

            <ListSection
              icon={Stethoscope}
              title="Specializations"
              items={shown.profile.specializations}
            />
            <ListSection
              icon={BadgeCheck}
              title="Treatments"
              items={shown.profile.treatments}
            />
            <ListSection
              icon={GraduationCap}
              title="Certifications"
              items={shown.profile.certifications ?? []}
            />
            <ListSection icon={Award} title="Awards" items={shown.profile.awards ?? []} />

            <div>
              <h3 className="flex items-center gap-2 font-display text-base font-semibold text-text">
                <Languages className="h-4 w-4 text-primary" aria-hidden="true" />
                Languages
              </h3>
              <div className="mt-3 flex flex-wrap gap-2">
                {shown.profile.languages.map((language) => (
                  <span
                    key={language}
                    className="rounded-full bg-bg-soft px-3 py-1 text-xs font-medium text-text ring-1 ring-border"
                  >
                    {language}
                  </span>
                ))}
              </div>
            </div>

            <div className="rounded-2xl border border-primary/15 bg-primary/[0.04] p-5">
              <h3 className="flex items-center gap-2 font-display text-base font-semibold text-text">
                <CalendarClock className="h-4 w-4 text-primary" aria-hidden="true" />
                Consultation
              </h3>
              <p className="mt-2 text-sm leading-6 text-muted">{shown.profile.consultation}</p>
            </div>
          </div>
        </article>
      ) : null}
    </Modal>
  );
}
