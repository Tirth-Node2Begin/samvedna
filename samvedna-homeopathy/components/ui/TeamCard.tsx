import Image from "next/image";
import type { TeamMember } from "@/types";
import { blurDataUrl } from "@/lib/utils";

type TeamCardProps = {
  member: TeamMember;
};

export default function TeamCard({ member }: TeamCardProps) {
  return (
    <article className="group flex h-full flex-col overflow-hidden rounded-card border border-border bg-white">
      <div className="relative aspect-[4/3] overflow-hidden bg-surface">
        <Image
          src={member.image}
          alt={member.alt}
          fill
          sizes="(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw"
          className="object-cover object-[50%_15%] transition duration-500 group-hover:scale-[1.02]"
          placeholder="blur"
          blurDataURL={blurDataUrl}
          loading="lazy"
        />
      </div>
      <div className="flex flex-1 flex-col p-6">
        <h3 className="font-display text-2xl font-semibold text-text transition duration-200 group-hover:text-primary">
          {member.name}
        </h3>
        <p className="mt-2 text-sm font-semibold text-muted">{member.title}</p>
        <p className="mt-4 border-t border-border pt-4 text-sm leading-6 text-muted">
          {member.credential}
        </p>
      </div>
    </article>
  );
}
