import dynamic from "next/dynamic";
import faq from "@/constants/faq";
import { contact } from "@/lib/utils";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import Button from "@/components/ui/Button";

const FAQAccordion = dynamic(() => import("@/components/ui/FAQAccordion"), {
  loading: () => (
    <div className="space-y-4" aria-hidden="true">
      {[0, 1, 2, 3].map((item) => (
        <div key={item} className="h-20 rounded-card bg-surface" />
      ))}
    </div>
  )
});

export default function FAQ() {
  return (
    <section id="faq" className="bg-bg-soft py-16 md:py-20 lg:py-[120px]">
      <div className="mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="mx-auto w-full max-w-5xl text-center">
          <p className="text-sm font-semibold text-primary">Parent questions</p>
          <AnimatedText
            as="h2"
            className="mt-4 font-display text-4xl font-semibold leading-tight text-text md:text-5xl"
            text="Honest answers for parents before the first consultation."
          />
          <p className="mx-auto mt-6 max-w-3xl text-lg leading-8 text-muted">
            These are the questions families ask when they are comparing care
            options, therapies, and next steps for a child with developmental
            challenges.
          </p>
          <div className="mt-8 flex justify-center">
            <Button href={contact.phoneHref} variant="secondary">
              Speak With Our Team
            </Button>
          </div>
        </AnimatedReveal>

        <AnimatedReveal className="mx-auto mt-12 w-full max-w-3xl lg:mt-16">
          <FAQAccordion items={faq} />
        </AnimatedReveal>
      </div>
    </section>
  );
}
