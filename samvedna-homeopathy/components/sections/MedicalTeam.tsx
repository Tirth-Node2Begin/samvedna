import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import DoctorsGrid from "@/components/sections/DoctorsGrid";
import type { TeamMember } from "@/types";

export default function MedicalTeam({ members }: { members: TeamMember[] }) {
  // Admin-managed: no doctor profiles means no team section at all, rather than
  // a heading above an empty grid.
  if (members.length === 0) {
    return null;
  }

  return (
    <section id="doctors" className="scroll-mt-24 bg-white pt-16 md:scroll-mt-28 md:pt-20 lg:pt-[120px] pb-0">
      <div className="mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="flex flex-col justify-between gap-8 md:flex-row md:items-end">
          <div className="max-w-3xl">
            <p className="text-sm font-semibold text-primary">Medical team</p>
            <AnimatedText
              as="h2"
              className="mt-4 font-display text-4xl font-semibold leading-tight text-text md:text-5xl"
              text="A doctor-led team that stays with the family beyond the first visit."
            />
          </div>
          <p className="max-w-md text-base leading-7 text-muted">
            Each child is assessed, discussed, monitored, and refined through
            regular follow-ups so parents are not left guessing between visits.
          </p>
        </AnimatedReveal>

        <DoctorsGrid members={members} />
      </div>
    </section>
  );
}
