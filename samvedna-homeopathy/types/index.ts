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

export type TeamMember = {
  name: string;
  title: string;
  credential: string;
  image: string;
  alt: string;
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
