export type NavItem = {
  label: string;
  href: string;
  subItems?: NavItem[];
};

export type Condition = {
  name: string;
  description: string;
  span: "featured" | "standard" | "compact";
};

export type DoctorProfile = {
  qualifications: string[];
  about: string;
  specializations: string[];
  treatments: string[];
  certifications?: string[];
  awards?: string[];
  languages: string[];
  consultation: string;
};

export type TeamMember = {
  name: string;
  title: string;
  credential: string;
  image: string;
  alt: string;
  /** Department / area of specialization shown on the card. */
  specialization: string;
  /** Years of experience, e.g. "20+ Years". */
  experience: string;
  /** Short description shown on the card. */
  summary: string;
  /** Detailed information surfaced in the profile dialog. */
  profile: DoctorProfile;
};

export type BlogPost = {
  slug: string;
  title: string;
  excerpt: string;
  image: string;
  alt: string;
  category: string;
  date: string;
  readTime: string;
  href: string;
};

export type Testimonial = {
  quote: string;
  name: string;
  condition: string;
  location: string;
};

export type FaqItem = {
  question: string;
  answer: string;
};
