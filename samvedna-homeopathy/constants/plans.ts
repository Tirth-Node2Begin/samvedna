/**
 * Care plans — single source of truth.
 *
 * Rendered by components/sections/Pricing.tsx and read by the assessment wizard
 * (components/consultation/*) so the chosen plan's name and price can be shown on
 * the payment step and stored with the submission. Each card links to
 * /consultation/?plan=<slug>.
 */

/** Which currency to display prices in. INR for visitors in India, USD elsewhere. */
export type Currency = "INR" | "USD";

export type Plan = {
  slug: string;
  name: string;
  tagline: string;
  duration: string;
  /** Rupee price shown to visitors detected in India. */
  priceINR: string;
  /** Dollar price shown to visitors outside India. */
  priceUSD: string;
  features: string[];
  popular: boolean;
  cta: string;
};

export const plans: Plan[] = [
  {
    slug: "starter",
    name: "Starter (Trial)",
    tagline: "Safe start to experience structured Samvedna care.",
    duration: "2 months",
    priceINR: "₹14,999",
    priceUSD: "$599",
    features: [
      "1st case consultation by Senior Doctor",
      "Medicines for 2 months",
      "1 Bi-Monthly Review (60-day review)",
      "Written instructions for medicines & routines",
      "WhatsApp support (≤48h response)",
    ],
    popular: false,
    cta: "Start with 2 Months",
  },
  {
    slug: "standard",
    name: "Standard",
    tagline: "Balanced, evidence-led care with two-doctor oversight.",
    duration: "6 months",
    priceINR: "₹39,999",
    priceUSD: "$1,499",
    features: [
      "1st case consultation by Senior Doctor",
      "Medicines for 6 months",
      "Every 2 months (3 sessions)",
      "Case + Senior doctor oversight",
      "Progress dashboard",
      "Therapy coordination (1 call/cycle)",
      "Priority slots",
      "WhatsApp support (24–36h response)",
    ],
    popular: true,
    cta: "Choose Standard Plan",
  },
  {
    slug: "premium",
    name: "Premium",
    tagline: "High-intensity supervision with founder review.",
    duration: "6 months",
    priceINR: "₹54,999",
    priceUSD: "$1,999",
    features: [
      "1st case consultation by Founder",
      "Medicines for 6 months",
      "Monthly follow-ups",
      "Case + Senior + Founder review",
      "Same-day support",
      "Custom tweaks",
      "Founder Q&A webinar access",
    ],
    popular: false,
    cta: "Apply for Premium",
  },
];

/** Look up a plan by its slug (from the ?plan= query param). */
export function getPlanBySlug(slug: string | null | undefined): Plan | undefined {
  if (!slug) return undefined;
  return plans.find((plan) => plan.slug === slug);
}

/** The price string to show for a plan in the given currency. */
export function getPlanPrice(plan: Plan, currency: Currency): string {
  return currency === "INR" ? plan.priceINR : plan.priceUSD;
}
