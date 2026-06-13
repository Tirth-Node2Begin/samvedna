import dynamic from "next/dynamic";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import {
  Globe2,
  Video,
  HeartHandshake,
  Stethoscope,
  MessageCircle,
} from "lucide-react";

const WorldMap = dynamic(() => import("@/components/ui/WorldMap"), {
  loading: () => (
    <div className="h-[520px] bg-primary/[0.03] rounded-3xl" aria-hidden="true" />
  ),
});

export default function InternationalReach() {
  return (
    <section
      id="international-reach"
      className="relative scroll-mt-24 overflow-hidden py-16 text-text md:scroll-mt-28 md:py-20 lg:py-[120px]"
    >
      {/* Subtle gradient background */}
      <div className="absolute inset-0 bg-gradient-to-b from-[#F0F5FF] via-[#F8FAF9] to-white" />
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-[radial-gradient(circle,rgba(37,99,235,0.04),transparent_60%)]" />

      <div className="relative mx-auto max-w-content px-5 md:px-8">
        {/* ── Header ── */}
        <div className="text-center max-w-3xl mx-auto mb-14 lg:mb-20">
          <AnimatedReveal>
            <div className="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/[0.06] px-4 py-1.5 mb-6">
              <Globe2 className="h-3.5 w-3.5 text-primary" strokeWidth={2} />
              <span className="text-xs font-semibold uppercase tracking-wider text-primary">
                International reach
              </span>
            </div>
          </AnimatedReveal>
          <AnimatedText
            as="h2"
            className="text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl lg:text-[44px] lg:leading-[1.15]"
            text="Worldwide consultations with care that stays connected."
          />
          <AnimatedReveal delay={0.06}>
            <p className="mt-5 text-balance text-base leading-7 text-muted md:text-lg md:leading-8">
              Families across 11+ countries consult from home while receiving
              personalized plans, medicine guidance, and continuous follow-up support.
            </p>
          </AnimatedReveal>
        </div>

        {/* ── Stats Row ── */}
        <AnimatedReveal delay={0.08}>
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-14 lg:mb-20">
            {[
              { value: "11+", label: "Countries", icon: Globe2 },
              { value: "10,000+", label: "Patients treated", icon: HeartHandshake },
              { value: "Online", label: "Follow-up care", icon: Video },
              { value: "24/7", label: "Support access", icon: MessageCircle },
            ].map((stat) => (
              <div
                key={stat.label}
                className="group relative rounded-2xl border border-primary/10 bg-white/70 p-5 text-center backdrop-blur-sm transition-all duration-300 hover:border-primary/25 hover:shadow-[0_8px_32px_rgba(37,99,235,0.08)] hover:-translate-y-0.5"
              >
                <div className="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/[0.07] text-primary transition-colors duration-300 group-hover:bg-primary/[0.12]">
                  <stat.icon className="h-5 w-5" strokeWidth={1.8} />
                </div>
                <p className="font-display text-2xl font-bold leading-none text-primary sm:text-3xl">
                  {stat.value}
                </p>
                <p className="mt-2 text-xs font-semibold text-muted sm:text-sm">
                  {stat.label}
                </p>
              </div>
            ))}
          </div>
        </AnimatedReveal>

        {/* ── Map + Feature Cards ── */}
        <div className="grid gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-14">
          {/* Map */}
          <AnimatedReveal variant="slideRight" delay={0.1}>
            <div className="relative rounded-3xl border border-primary/[0.08] bg-white/60 p-4 shadow-[0_4px_24px_rgba(37,99,235,0.04)] backdrop-blur-sm">
              <WorldMap />
            </div>
          </AnimatedReveal>

          {/* Feature Cards */}
          <AnimatedReveal variant="slideLeft" delay={0.14}>
            <div className="space-y-4">
              {[
                {
                  icon: Video,
                  title: "Consult from home",
                  description:
                    "Parents share reports, videos, symptoms, and development history before the care plan is finalized.",
                  accent: "from-blue-500/10 to-cyan-500/10",
                  iconBg: "bg-blue-500/10 text-blue-600",
                },
                {
                  icon: Stethoscope,
                  title: "Personalized care plans",
                  description:
                    "Each plan is tailored to the child's unique presentation — not a one-size-fits-all approach.",
                  accent: "from-teal-500/10 to-emerald-500/10",
                  iconBg: "bg-teal-500/10 text-teal-600",
                },
                {
                  icon: HeartHandshake,
                  title: "Follow-up without gaps",
                  description:
                    "Progress is reviewed over calls and messages so guidance continues between appointments.",
                  accent: "from-violet-500/10 to-purple-500/10",
                  iconBg: "bg-violet-500/10 text-violet-600",
                },
              ].map((feature) => (
                <div
                  key={feature.title}
                  className="group relative overflow-hidden rounded-2xl border border-primary/[0.08] bg-white/80 p-5 backdrop-blur-sm transition-all duration-300 hover:border-primary/20 hover:shadow-[0_8px_28px_rgba(37,99,235,0.06)]"
                >
                  {/* Hover gradient */}
                  <div
                    className={`absolute inset-0 bg-gradient-to-br ${feature.accent} opacity-0 transition-opacity duration-300 group-hover:opacity-100`}
                  />
                  <div className="relative flex gap-4">
                    <div
                      className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${feature.iconBg} transition-transform duration-300 group-hover:scale-105`}
                    >
                      <feature.icon className="h-5 w-5" strokeWidth={1.8} />
                    </div>
                    <div>
                      <p className="font-semibold text-text">{feature.title}</p>
                      <p className="mt-1.5 text-sm leading-relaxed text-muted">
                        {feature.description}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </AnimatedReveal>
        </div>


      </div>
    </section>
  );
}
