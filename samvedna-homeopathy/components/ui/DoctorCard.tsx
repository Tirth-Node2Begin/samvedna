import Image from "next/image";
import { ArrowUpRight, Stethoscope } from "lucide-react";
import type { TeamMember } from "@/types";
import { blurDataUrl } from "@/lib/utils";

type DoctorCardProps = {
  member: TeamMember;
  onSelect: () => void;
};

export default function DoctorCard({ member, onSelect }: DoctorCardProps) {
  return (
    <button
      type="button"
      onClick={onSelect}
      aria-label={`View the profile of ${member.name}`}
      suppressHydrationWarning
      className="group flex h-full w-full flex-col overflow-hidden rounded-card border border-border bg-white text-left transition duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-premium focus-visible:-translate-y-1 focus-visible:border-primary"
    >
      <div className="relative aspect-[4/3] overflow-hidden bg-surface">
        <Image
          src={member.image}
          alt={member.alt}
          fill
          sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw"
          className="object-cover object-[50%_15%] transition duration-500 group-hover:scale-105"
          placeholder="blur"
          blurDataURL={blurDataUrl}
          loading="lazy"
        />
        <span className="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary shadow-sm backdrop-blur">
          {member.experience} Experience
        </span>
      </div>

      <div className="flex flex-1 flex-col p-6">
        <h3 className="font-display text-xl font-semibold text-text transition duration-200 group-hover:text-primary">
          {member.name}
        </h3>
        <p className="mt-1 text-sm font-semibold text-primary">{member.title}</p>
        <p className="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-muted">
          <Stethoscope className="h-3.5 w-3.5 shrink-0 text-primary/70" aria-hidden="true" />
          {member.specialization}
        </p>
        <p className="mt-4 border-t border-border pt-4 text-sm leading-6 text-muted">
          {member.summary}
        </p>
        <span className="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-primary">
          View full profile
          <ArrowUpRight
            className="h-4 w-4 transition-transform duration-200 group-hover:-translate-y-0.5 group-hover:translate-x-0.5"
            aria-hidden="true"
          />
        </span>
      </div>
    </button>
  );
}
