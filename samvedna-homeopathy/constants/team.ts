import type { TeamMember } from "@/types";

// NOTE: `specialization`, `experience` and the `profile` block below are
// editable content. Dr. Krunal Kosada's details reflect the existing site
// copy; for the other consultants the experience figures and profile copy
// are sensible placeholders — update them with verified information.
const team: TeamMember[] = [
  {
    name: "Dr. Krunal Kosada",
    title: "Founder & Lead Clinician",
    credential: "BHMS, FCAH | 20+ years of pediatric neurodevelopmental care",
    image: "/images/dr-krunal-kosada.jpg",
    alt: "Dr. Krunal Kosada, founder of Samvedna Homeopathy",
    specialization: "Pediatric Neurodevelopmental Homeopathy",
    experience: "20+ Years",
    summary:
      "Leads individualized homeopathic care for autism, ADHD, speech delay and developmental concerns.",
    profile: {
      qualifications: ["BHMS", "FCAH (Fellowship in Classical & Advanced Homeopathy)"],
      about:
        "Dr. Krunal Kosada built Samvedna for families navigating autism, ADHD, speech delay, learning difficulty, developmental delay, genetic concerns and pediatric neurological disorders. The clinical focus is simple and demanding: understand the whole child, create a personalized plan, and stay close through continuous follow-ups as parents track progress over time.",
      specializations: [
        "Autism Spectrum Disorder",
        "ADHD & attention concerns",
        "Speech & developmental delay",
        "Learning difficulty",
        "Pediatric neurological disorders"
      ],
      treatments: [
        "Individualized homeopathic care plans",
        "Behaviour, sleep & sensory support",
        "Parent guidance and home strategies",
        "Structured follow-up and progress reviews"
      ],
      certifications: [
        "President, HMAI Surat Unit",
        "Fellowship in Classical & Advanced Homeopathy"
      ],
      awards: [
        "Guest Speaker — Hellenic Homeopathic Medical Society, Greece",
        "Key Presenter — 3rd International AYUSH Exhibition & Conference, Dubai",
        "Global Scientific Presenter — LMHI Congresses & JAHC San Antonio"
      ],
      languages: ["English", "Hindi", "Gujarati"],
      consultation:
        "Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment."
    }
  },
  {
    name: "Dr. Krishna Thakor",
    title: "Senior Consultant",
    credential: "BHMS | Case coordination, long-term follow-up, and family guidance",
    image: "/images/samvedna-associate-portrait.webp",
    alt: "Dr. Krishna Thakor, consultant for child neurodevelopment care",
    specialization: "Case Coordination & Follow-up Care",
    experience: "12+ Years",
    summary:
      "Coordinates long-term care and follow-ups so families stay supported between consultations.",
    profile: {
      qualifications: ["BHMS"],
      about:
        "Dr. Krishna Thakor focuses on case coordination and long-term follow-up, helping families stay supported between visits with clear next steps and steady communication.",
      specializations: [
        "Long-term case coordination",
        "Follow-up and progress monitoring",
        "Family guidance"
      ],
      treatments: [
        "Follow-up consultations",
        "Care-plan monitoring",
        "Parent communication and support"
      ],
      languages: ["English", "Hindi", "Gujarati"],
      consultation:
        "Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment."
    }
  },
  {
    name: "Dr. Yakshika",
    title: "Consultant",
    credential: "BHMS | Case coordination and family guidance",
    image: "/images/dr-yakshika.jpg",
    alt: "Dr. Yakshika, consultant for child neurodevelopment care",
    specialization: "Developmental Care & Family Guidance",
    experience: "6+ Years",
    summary:
      "Supports developmental care and guides parents through assessments and daily routines.",
    profile: {
      qualifications: ["BHMS"],
      about:
        "Dr. Yakshika supports children's developmental care and works closely with parents through assessments, routines and ongoing guidance.",
      specializations: [
        "Developmental care",
        "Assessment support",
        "Family guidance"
      ],
      treatments: [
        "Developmental assessments",
        "Supportive homeopathic care",
        "Parent guidance"
      ],
      languages: ["English", "Hindi", "Gujarati"],
      consultation:
        "Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment."
    }
  },
  {
    name: "Medical Team Member",
    title: "Consultant",
    credential: "BHMS | Case coordination and family guidance",
    image: "/images/member-3.jpg",
    alt: "Medical Team Member, consultant for child neurodevelopment care",
    specialization: "Consultation & Coordination",
    experience: "5+ Years",
    summary:
      "Assists with consultations, coordination and day-to-day family support across cases.",
    profile: {
      qualifications: ["BHMS"],
      about:
        "A consultant on the Samvedna team supporting consultations, coordination and day-to-day family guidance across cases.",
      specializations: ["Consultation support", "Case coordination", "Family guidance"],
      treatments: [
        "Supportive consultations",
        "Care coordination",
        "Parent guidance"
      ],
      languages: ["English", "Hindi", "Gujarati"],
      consultation:
        "Online & in-clinic consultations · Monday to Saturday, 10 am to 7 pm · By appointment."
    }
  }
];

export default team;
