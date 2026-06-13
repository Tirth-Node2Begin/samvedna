import type { Condition } from "@/types";

export const conditionList = [
  "Autism Spectrum Disorder Support",
  "ADHD Support",
  "Learning Disability Support",
  "Speech Delay Support",
  "Developmental Delay Support",
  "Genetic Disorders Support",
  "Neurological Disorders Support"
] as const;

const conditions: Condition[] = [
  {
    name: "Autism Spectrum Disorder Support",
    description:
      "Individualized support for communication, social interaction, sensory needs, behavior, sleep, and family routines.",
    span: "featured"
  },
  {
    name: "ADHD Support",
    description:
      "Care focused on attention, hyperactivity, impulsivity, sleep, emotional regulation, and learning readiness.",
    span: "standard"
  },
  {
    name: "Learning Disability Support",
    description:
      "Guidance for children struggling with reading, writing, processing, classroom readiness, and confidence.",
    span: "compact"
  },
  {
    name: "Speech Delay Support",
    description:
      "Support for expressive speech, understanding, non-verbal communication, and connection alongside therapies.",
    span: "compact"
  },
  {
    name: "Developmental Delay Support",
    description:
      "Structured care for children whose milestones, regulation, and everyday developmental progress need support.",
    span: "standard"
  },
  {
    name: "Genetic Disorders Support",
    description:
      "Individualized supportive care for children with genetic and syndrome-related developmental challenges.",
    span: "standard"
  },
  {
    name: "Neurological Disorders Support",
    description:
      "Homeopathic support for pediatric neurological and neurodevelopmental concerns with careful monitoring.",
    span: "standard"
  }
];

export default conditions;
