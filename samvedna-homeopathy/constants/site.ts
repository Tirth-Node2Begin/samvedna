import type { NavItem } from "@/types";

/**
 * Primary navigation.
 *
 * Every section link is root-relative (`/#id`), NOT a bare `#id`. The navbar is
 * rendered on every route, and a bare hash on a page that has no such section
 * — /consultation, /blog/[slug] — only rewrites the URL fragment and leaves the
 * visitor where they are. `/#id` sends them back to the homepage section from
 * anywhere, and still scrolls without a reload when already on the homepage.
 */
export const navItems: NavItem[] = [
  { label: "HOME", href: "/#home" },
  { label: "CONDITIONS", href: "/#conditions" },
  { label: "ABOUT DOCTOR", href: "/#doctors" },
  { label: "CARE PROCESS", href: "/#journey" },
  { label: "PARENT STORIES", href: "/#testimonials" },
  { label: "FAQ", href: "/#faq" },
  { label: "CONTACT", href: "/#consultation" }
];

export const heroStats = [
  { value: "20+", label: "Years Experience", detail: "Focused child developmental care" },
  { value: "10,000+", label: "Patients Treated", detail: "Across pediatric and family care" },
  { value: "1,000+", label: "Autism Cases", detail: "Managed with ongoing follow-up" },
  { value: "Worldwide", label: "Online Consultations", detail: "For India and international families" }
];

export const trustMetrics = [
  {
    label: "Clinical experience",
    value: "20+ years"
  },
  {
    label: "Patients treated",
    value: "10,000+"
  },
  {
    label: "Autism cases managed",
    value: "1,000+"
  },
  {
    label: "Online consultations",
    value: "Worldwide"
  }
];

export const trustReasons = [
  {
    title: "Personalized Treatment Plans",
    description:
      "Every plan begins with the child's developmental history, behavior, speech, sensory responses, health, and parent observations."
  },
  {
    title: "Continuous Follow-Ups",
    description:
      "Reviews track communication, attention, sleep, behavior, learning readiness, and practical changes parents notice at home."
  },
  {
    title: "Autism & ADHD Support",
    description:
      "Focused homeopathic care for autism, ADHD, speech delay, learning concerns, and related developmental challenges."
  },
  {
    title: "Parent Guidance at Every Step",
    description:
      "Parents receive clear next steps, report guidance, and steady communication so the plan feels easier to follow."
  },
  {
    title: "Worldwide Online Consultations",
    description:
      "Families in India and abroad can begin care through structured online consultations and follow-up support."
  },
  {
    title: "Ethical Developmental Care",
    description:
      "No fixed promises or cure claims; the focus is individualized support, careful monitoring, and child-centered progress."
  }
];

export const founderCredentials = [
  "BHMS",
  "FCAH",
  "20+ years experience",
  "Autism-focused pediatric care"
];

export const journeySteps = [
  {
    title: "Parent Intake",
    description:
      "Parents share the child's diagnosis, milestones, daily challenges, therapy history, reports, and current concerns."
  },
  {
    title: "Detailed Assessment",
    description:
      "The clinical team studies developmental, behavioral, emotional, sensory, speech, sleep, and health patterns."
  },
  {
    title: "Personalized Plan",
    description:
      "A homeopathic care plan is selected around the child's individual presentation, not only the diagnostic label."
  },
  {
    title: "Medicine & Guidance",
    description:
      "Parents receive medicine instructions, practical guidance, and coordination for local or distance care."
  },
  {
    title: "Continuous Follow-Ups",
    description:
      "Follow-ups review response, concerns, parent observations, and changes seen at home, therapy, or school."
  },
  {
    title: "Progress Refinement",
    description:
      "The plan is adjusted as communication, attention, sleep, behavior, learning readiness, and routines evolve."
  }
];

export const reachCountries = [
  "India",
  "UAE",
  "UK",
  "USA",
  "Canada",
  "Australia",
  "Singapore",
  "Germany",
  "New Zealand",
  "South Africa",
  "Kuwait"
];

export const socialLinks = [
  { label: "Instagram", href: "https://www.instagram.com/samvedna_homeopathy/" },
  { label: "YouTube", href: "https://www.youtube.com/user/drkrunalkosada" },
  { label: "Facebook", href: "https://www.facebook.com/61559641756389/" },
  { label: "LinkedIn", href: "https://in.linkedin.com/in/dr-krunal-kosada-7459511b" }
];
