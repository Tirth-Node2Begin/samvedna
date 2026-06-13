import { Phone } from "lucide-react";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import Button from "@/components/ui/Button";
import ConsultationForm from "@/components/ui/ConsultationForm";
import { contact } from "@/lib/utils";

export default function FinalCTA() {
  return (
    <section
      id="consultation"
      className="bg-bg-soft py-10 md:py-16 lg:py-20"
    >
      <AnimatedReveal className="mx-auto mb-8 max-w-7xl px-5 text-center md:px-8">
        <AnimatedText
          as="h2"
          className="font-display text-4xl font-semibold text-text md:text-5xl"
          text="Book a Consultation for Your Child"
        />
        <p className="mt-3 text-lg text-muted">Speak with the Samvedna Care Desk and get expert guidance for the next step.</p>
      </AnimatedReveal>
      <div className="mx-auto max-w-7xl px-5 md:px-8">
        <AnimatedReveal
          className="overflow-hidden rounded-3xl border border-border bg-white shadow-premium lg:grid lg:grid-cols-[0.85fr_1.15fr]"
          variant="scaleIn"
          viewportAmount={0.12}
        >
          {/* Left: Content */}
          <div className="flex flex-col justify-between bg-primary/[0.03] p-8 text-left lg:p-12 xl:p-16 border-r border-border">
            <div>
              <p className="text-sm font-semibold uppercase tracking-wider text-primary">Begin with expert guidance</p>
              <AnimatedText
                as="h2"
                className="mt-4 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl"
                text="Share your child's concerns with a team experienced in autism and developmental challenges."
              />
              <p className="mt-5 text-base leading-relaxed text-muted md:text-lg">
                The first step is a calm conversation about your child&apos;s
                history, current therapies, reports, and daily challenges.
              </p>
              <div className="mt-8 flex flex-wrap items-center gap-4">
                <Button href="https://autismhomeohelp.com/online-consulting/" size="lg" className="w-full sm:w-auto">
                  Book Consultation
                </Button>
                <Button
                  href={contact.phoneHref}
                  size="lg"
                  variant="secondary"
                  className="w-full sm:w-auto"
                  icon={<Phone className="h-5 w-5" aria-hidden="true" />}
                  iconPosition="left"
                >
                  Speak With Our Team
                </Button>
              </div>
            </div>

            <div className="mt-12 lg:mt-16 border-t border-border pt-8">
              <h3 className="text-lg font-semibold text-text">Why parents choose Samvedna</h3>
              <ul className="mt-5 flex flex-col gap-4 text-sm text-text/80 md:text-base">
                <li className="flex items-start gap-3">
                  <div className="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10">
                    <svg className="h-3 w-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <span>20+ years experience with 10,000+ patients treated</span>
                </li>
                <li className="flex items-start gap-3">
                  <div className="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10">
                    <svg className="h-3 w-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <span>1,000+ autism cases managed with personalized treatment plans</span>
                </li>
                <li className="flex items-start gap-3">
                  <div className="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10">
                    <svg className="h-3 w-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <span>Worldwide online consultations with continuous follow-ups</span>
                </li>
              </ul>
            </div>
          </div>

          {/* Right: Form */}
          <div className="p-8 lg:p-12 xl:p-16">
            <ConsultationForm />
          </div>
        </AnimatedReveal>
      </div>
    </section>
  );
}
