import type { TeamMember } from "@/types";

// NOTE: `specialization`, `experience` and the `profile` block below are
// editable content. The experience figures and profile copy are sensible
// placeholders — update them with verified information.
const team: TeamMember[] = [
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
