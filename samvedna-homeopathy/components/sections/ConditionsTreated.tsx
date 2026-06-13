import conditions from "@/constants/conditions";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import ConditionCard from "@/components/ui/ConditionCard";
import { cn } from "@/lib/utils";

const bentoLayouts = [
  { span: "md:col-span-2 lg:col-span-2", variant: "dark" as const }, // 0: Autism
  { span: "md:col-span-1 lg:col-span-1", variant: "light" as const }, // 1: ADHD
  { span: "md:col-span-1 lg:col-span-1", variant: "soft" as const }, // 2: Learning
  { span: "md:col-span-1 lg:col-span-1", variant: "soft" as const }, // 3: Speech
  { span: "md:col-span-1 lg:col-span-1", variant: "light" as const }, // 4: Developmental
  { span: "md:col-span-1 lg:col-span-1", variant: "light" as const }, // 5: Genetic
  { span: "md:col-span-1 lg:col-span-1", variant: "dark" as const }, // 6: Neurological
];

export default function ConditionsTreated() {
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
            const layout = bentoLayouts[index] || { span: "", variant: "light" };
            return (
              <AnimatedReveal
                key={condition.name}
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
