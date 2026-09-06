"use client";

import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import { cn } from "@/lib/utils";
import { achievements } from "@/constants/achievements";
import {
  BookOpen,
  Globe2,
  MapPin,
  Medal,
  Mic2,
  Trophy,
  Users,
} from "lucide-react";

const achievementMeta = [
  {
    Icon: Trophy,
    location: "Greece",
    label: "International Speaker",
    accent: "from-primary to-blue-400",
    iconBg: "bg-primary text-white shadow-md shadow-primary/20",
    numberColor: "text-primary/[0.07]",
  },
  {
    Icon: Mic2,
    location: "Dubai",
    label: "International Forum",
    accent: "from-cyan-500 to-teal-400",
    iconBg: "bg-slate-800 text-white", // Dark icon background as seen in reference
    numberColor: "text-cyan-500/[0.07]",
  },
  {
    Icon: Users,
    location: "India",
    label: "Leadership Role",
    accent: "from-emerald-500 to-green-400",
    iconBg: "bg-slate-800 text-white",
    numberColor: "text-emerald-500/[0.07]",
  },
  {
    Icon: Globe2,
    location: "Istanbul · Spain · USA",
    label: "Congress Presenter",
    accent: "from-violet-500 to-purple-400",
    iconBg: "bg-slate-800 text-white",
    numberColor: "text-violet-500/[0.07]",
  },
  {
    Icon: BookOpen,
    location: "Greece",
    label: "Advanced Homeopathy",
    accent: "from-amber-500 to-orange-400",
    iconBg: "bg-slate-800 text-white",
    numberColor: "text-amber-500/[0.07]",
  },
] as const;

export default function DoctorAchievements() {
  const featured = achievements[0];
  const featuredMeta = achievementMeta[0];

  const renderSupportingCard = (index: number) => {
    const achievement = achievements[index];
    const meta = achievementMeta[index];
    const Icon = meta.Icon;

    return (
      <AnimatedReveal
        key={achievement.title}
        className="flex-1"
        delay={0.1 + index * 0.05}
        variant="fadeUp"
      >
        <article className="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-transparent bg-bg-soft p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-200/50">
          <div className="absolute inset-0 bg-white" />
          <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-slate-100 to-slate-200 transition-colors duration-300 group-hover:from-slate-200 group-hover:to-slate-300" />
          
          <div className="relative z-10">
            <div className={cn("mb-6 flex h-12 w-12 items-center justify-center rounded-xl", meta.iconBg)}>
              <Icon className="h-5 w-5" strokeWidth={1.8} />
            </div>

            <h3 className="font-display text-lg font-semibold leading-snug text-text">
              <span className="text-text">{achievement.title}</span>
            </h3>
            
            <p className="mt-3 text-sm leading-relaxed text-muted">
              {achievement.description}
            </p>
          </div>
        </article>
      </AnimatedReveal>
    );
  };

  return (
    <section
      id="achievements"
      className="relative scroll-mt-24 overflow-hidden bg-gradient-to-b from-white via-[#F7FBFA] to-white py-16 text-text md:scroll-mt-28 md:py-20 lg:py-[112px]"
    >
      <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(37,99,235,0.03)_1px,transparent_1px),linear-gradient(180deg,rgba(20,184,166,0.03)_1px,transparent_1px)] bg-[size:56px_56px] opacity-60" />

      <div className="relative mx-auto max-w-content px-5 md:px-8">
        {/* ─── Header ─── */}
        <div className="mx-auto max-w-3xl text-center">
          <AnimatedReveal>
            <div className="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-white px-4 py-1.5 shadow-sm">
              <Trophy className="h-3.5 w-3.5 text-primary" strokeWidth={2} />
              <span className="text-xs font-semibold uppercase tracking-wider text-primary">
                Top 5 Achievements
              </span>
            </div>
          </AnimatedReveal>

          <AnimatedText
            as="h2"
            className="mt-5 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl lg:text-[42px] lg:leading-[1.18]"
            text="International recognition, arranged for parents to understand at a glance."
          />

          <AnimatedReveal delay={0.06}>
            <p className="mx-auto mt-5 max-w-xl text-base leading-7 text-muted md:text-lg">
              From international conferences to national leadership, these
              milestones reflect experience in child-focused homeopathic care.
            </p>
          </AnimatedReveal>
        </div>

        {/* ─── 3 Column Grid Layout ─── */}
        <div className="mt-14 grid gap-6 lg:grid-cols-3 lg:gap-8 items-stretch">
          
          {/* Left Column (2 Cards) */}
          <div className="flex flex-col gap-6">
            {renderSupportingCard(1)}
            {renderSupportingCard(2)}
          </div>

          {/* Center Column (Featured Card) */}
          <AnimatedReveal delay={0.1} variant="fadeUp" className="h-full">
            <article className="group relative flex h-full flex-col overflow-hidden rounded-[32px] border border-primary/10 bg-white p-8 shadow-premium transition-all duration-300 hover:shadow-[0_32px_96px_rgba(37,99,235,0.12)] md:p-10">
              {/* Image placeholder / background gradient for center card to give it presence */}
              <div className="absolute inset-0 bg-gradient-to-b from-primary/[0.02] to-transparent" />
              <div className="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-primary via-accent to-secondary" />

              <div className="relative z-10 flex flex-col h-full items-center text-center">
                <div className="mb-8 flex h-20 w-20 items-center justify-center rounded-2xl bg-primary text-white shadow-xl shadow-primary/20">
                  <Trophy className="h-10 w-10" strokeWidth={1.5} />
                </div>
                
                <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/[0.04] px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary">
                  <MapPin className="h-3.5 w-3.5" strokeWidth={2} />
                  {featuredMeta.location}
                </div>

                <h3 className="font-display text-2xl font-bold leading-tight text-text md:text-3xl lg:text-[34px]">
                  {featured.title}
                </h3>
                
                <p className="mt-4 flex items-center justify-center gap-2 text-sm font-semibold text-primary">
                  <Medal className="h-4 w-4 shrink-0" strokeWidth={2} />
                  {featured.event}
                </p>
                
                <div className="mt-auto pt-8">
                  <p className="text-base leading-relaxed text-muted md:text-lg">
                    {featured.description}
                  </p>
                </div>
              </div>
            </article>
          </AnimatedReveal>

          {/* Right Column (2 Cards) */}
          <div className="flex flex-col gap-6">
            {renderSupportingCard(3)}
            {renderSupportingCard(4)}
          </div>

        </div>
      </div>
    </section>
  );
}
