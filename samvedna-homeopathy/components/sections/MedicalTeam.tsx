import team from "@/constants/team";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import TeamCard from "@/components/ui/TeamCard";

export default function MedicalTeam() {
  return (
    <section className="bg-white pt-16 md:pt-20 lg:pt-[120px] pb-0">
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

        <div className="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
          {team.map((member, index) => (
            <AnimatedReveal
              key={member.name}
              className="w-full"
              delay={index * 0.08}
            >
              <TeamCard member={member} />
            </AnimatedReveal>
          ))}
        </div>
      </div>
    </section>
  );
}
