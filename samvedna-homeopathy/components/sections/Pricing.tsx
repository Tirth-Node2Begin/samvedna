import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import Button from "@/components/ui/Button";
import { cn } from "@/lib/utils";

const plans = [
  {
    name: "Starter (Trial)",
    tagline: "Safe start to experience structured Samvedna care.",
    duration: "2 months",
    price: "₹14,999",
    features: [
      "Medicines for 2 months",
      "1 Bi-Monthly Review (60-day review)",
      "Written instructions for medicines & routines",
      "WhatsApp support (≤48h response)",
    ],
    popular: false,
    cta: "Start with 2 Months"
  },
  {
    name: "Standard",
    tagline: "Balanced, evidence-led care with two-doctor oversight.",
    duration: "6 months",
    price: "₹39,999",
    features: [
      "Medicines for 6 months",
      "Every 2 months (3 sessions)",
      "Case + Senior doctor oversight",
      "Progress dashboard",
      "Therapy coordination (1 call/cycle)",
      "Priority slots",
      "WhatsApp support (24–36h response)",
    ],
    popular: true,
    cta: "Choose Standard Plan"
  },
  {
    name: "Premium",
    tagline: "High-intensity supervision with founder review.",
    duration: "6 months",
    price: "₹54,999",
    features: [
      "Medicines for 6 months",
      "Monthly follow-ups",
      "Case + Senior + Founder review",
      "Same-day support",
      "Custom tweaks",
      "Founder Q&A webinar access",
    ],
    popular: false,
    cta: "Apply for Premium"
  },
];

function CheckIcon(props: React.ComponentProps<"svg">) {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.5"
      strokeLinecap="round"
      strokeLinejoin="round"
      {...props}
    >
      <polyline points="20 6 9 17 4 12" />
    </svg>
  );
}

export default function Pricing() {
  return (
    <section id="pricing" className="bg-bg-soft py-16 md:py-20 lg:py-[120px]">
      <div className="mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="mx-auto max-w-3xl text-center">
          <p className="text-sm font-semibold uppercase tracking-wider text-primary">Care Plans</p>
          <AnimatedText
            as="h2"
            className="mt-4 text-balance font-display text-3xl font-semibold leading-tight text-text md:text-4xl"
            text="Structured, Evidence-Led Homeopathy"
          />
          <p className="mt-6 text-balance text-base leading-8 text-muted md:text-lg">
            Every child progresses differently — but the care they receive should always be predictable, structured, and led by a trained medical team.
          </p>
        </AnimatedReveal>

        <div className="mt-14 grid gap-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-8 items-stretch">
          {plans.map((plan, index) => (
            <AnimatedReveal
              key={plan.name}
              className="h-full"
              delay={index * 0.1}
              variant="fadeUp"
            >
              <div
                className={cn(
                  "relative flex h-full flex-col rounded-3xl border p-8 transition-all duration-300 bg-white",
                  plan.popular 
                    ? "border-primary ring-1 ring-primary shadow-premium" 
                    : "border-border shadow-sm hover:shadow-md"
                )}
              >
                {plan.popular && (
                  <div className="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-primary to-secondary px-4 py-1 text-xs font-bold text-white shadow-sm uppercase tracking-wide">
                    Most Preferred
                  </div>
                )}
                <div className="mb-6">
                  <h3 className="font-display text-2xl font-semibold text-text">{plan.name}</h3>
                  <p className="mt-3 min-h-[3rem] text-sm leading-relaxed text-muted">{plan.tagline}</p>
                </div>
                
                <div className="mb-6 flex items-baseline text-text">
                  <span className="text-4xl font-bold tracking-tight">{plan.price}</span>
                  <span className="ml-1 text-sm font-medium text-muted">/ {plan.duration}</span>
                </div>
                
                <ul className="mb-8 flex-1 space-y-4">
                  {plan.features.map((feature, i) => (
                    <li key={i} className="flex items-start text-sm text-text">
                      <CheckIcon className="mr-3 mt-1 h-4 w-4 shrink-0 text-primary" />
                      <span className="leading-snug">{feature}</span>
                    </li>
                  ))}
                </ul>
                
                <div className="mt-auto pt-4">
                  <Button
                    variant={plan.popular ? "primary" : "secondary"}
                    className="w-full"
                    href="#contact"
                  >
                    {plan.cta}
                  </Button>
                </div>
              </div>
            </AnimatedReveal>
          ))}
        </div>
      </div>
    </section>
  );
}
