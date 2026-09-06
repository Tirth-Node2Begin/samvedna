export type NavItem = {
  label: string;
  href: string;
  subItems?: NavItem[];
};

export type Condition = {
  /** Present when the condition comes from the admin/API. */
  id?: number;
  name: string;
  description: string;
  /** Optional photo. Empty means the card shows its built-in icon instead. */
  image?: string;
  alt?: string;
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
  /** Present when the post comes from the admin/API (absent for static seed data). */
  id?: number;
  slug: string;
  title: string;
  excerpt: string;
  /** Full article body (HTML or plain text) — only returned by the API. */
  content?: string;
  /** Post author, e.g. "Dr. Krunal Kosada" — returned by the API. */
  author?: string;
  image: string;
  alt: string;
  category: string;
  date: string;
  readTime: string;
  href: string;
};

export type VideoTestimonial = {
  /** YouTube video id (the part after `watch?v=`). Leave empty to show the
   *  poster with a "coming soon" fallback instead of an embedded player. */
  youtubeId: string;
  /** Local poster/thumbnail image shown before the video plays. */
  poster: string;
  alt: string;
  name: string;
  condition: string;
  location: string;
  /** Optional duration label shown on the card, e.g. "2:14". */
  duration?: string;
};

export type FaqItem = {
  question: string;
  answer: string;
};
