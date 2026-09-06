import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import ConditionCard from "@/components/ui/ConditionCard";
import { cn } from "@/lib/utils";
import type { Condition } from "@/types";

/**
 * Bento sizing for the grid. The first card is always the wide, highlighted one;
 * the rest cycle through the softer variants so an admin adding an eighth
 * condition still gets a sensible-looking card.
 */
const bentoLayouts = [
  { span: "md:col-span-2 lg:col-span-2", variant: "dark" as const },
  { span: "md:col-span-1 lg:col-span-1", variant: "light" as const },
  { span: "md:col-span-1 lg:col-span-1", variant: "soft" as const },
  { span: "md:col-span-1 lg:col-span-1", variant: "soft" as const },
  { span: "md:col-span-1 lg:col-span-1", variant: "light" as const },
  { span: "md:col-span-1 lg:col-span-1", variant: "light" as const },
  { span: "md:col-span-1 lg:col-span-1", variant: "dark" as const },
];

export default function ConditionsTreated({
  conditions,
}: {
  conditions: Condition[];
}) {
  // Admin-managed: with no rows the whole section is hidden rather than leaving
  // a heading above an empty grid.
  if (conditions.length === 0) {
    return null;
  }

  return (
    <section id="conditions" className="bg-white py-16 md:py-20 lg:py-[120px]">
      <div className="mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="mx-auto max-w-3xl text-center">
          <p className="text-sm font-semibold uppercase tracking-wider text-primary">Conditions we support</p>
          <AnimatedText
            as="h2"
            className="mt-4 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl"
            text="Focused support for the developmental challenges parents worry about most."
          />
          <p className="mt-6 text-balance text-base leading-8 text-muted md:text-lg">
            Autism, ADHD, speech delay, learning concerns, developmental delay,
            genetic disorders, and neurological concerns are approached through
            one child-centered clinical picture.
          </p>
        </AnimatedReveal>

        <div className="mt-14 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {conditions.map((condition, index) => {
            const layout =
              index === 0
                ? bentoLayouts[0]
                : bentoLayouts[1 + ((index - 1) % (bentoLayouts.length - 1))];
            return (
              <AnimatedReveal
                key={condition.id ?? condition.name}
                className={cn("h-full", layout.span)}
                delay={Math.min(index * 0.04, 0.2)}
                variant="scaleIn"
              >
                <ConditionCard 
                  condition={condition} 
                  delay={Math.min(index * 0.05, 0.24)}
                  isLarge={index === 0} 
                  variant={layout.variant} 
                />
              </AnimatedReveal>
            );
          })}
        </div>
      </div>
    </section>
  );
}
