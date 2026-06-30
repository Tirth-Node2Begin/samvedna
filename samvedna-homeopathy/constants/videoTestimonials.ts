import type { VideoTestimonial } from "@/types";

// Parent video stories. Replace each `youtubeId` with the real video id from
// the clinic's YouTube channel (the part after `watch?v=`), and swap `poster`
// for a real thumbnail as footage becomes available. Cards with an empty
// `youtubeId` show the poster with a "coming soon" fallback in the player.
const videoTestimonials: VideoTestimonial[] = [
  {
    youtubeId: "",
    poster: "/images/assistant-doctor-cabin.webp",
    alt: "Parent sharing their child's autism care journey",
    name: "Parent family",
    condition: "Autism support",
    location: "Canada",
    duration: "2:10"
  },
  {
    youtubeId: "",
    poster: "/images/samvedna-associate-portrait.webp",
    alt: "Parent describing speech delay progress",
    name: "Parent family",
    condition: "Speech delay support",
    location: "India",
    duration: "1:48"
  },
  {
    youtubeId: "",
    poster: "/images/dr-yakshika.jpg",
    alt: "Parent talking about developmental delay care",
    name: "Parent family",
    condition: "Developmental delay support",
    location: "India",
    duration: "2:35"
  },
  {
    youtubeId: "",
    poster: "/images/member-3.jpg",
    alt: "Parent sharing their ADHD support experience",
    name: "Parent family",
    condition: "ADHD support",
    location: "India",
    duration: "1:55"
  },
  {
    youtubeId: "",
    poster: "/images/samvedna-auditorium.webp",
    alt: "Parent reflecting on continuous follow-up care",
    name: "Parent family",
    condition: "Learning support",
    location: "India",
    duration: "2:22"
  },
  {
    youtubeId: "",
    poster: "/images/dr-krunal-kosada.webp",
    alt: "Parent thanking the care team",
    name: "Parent family",
    condition: "Behavioral support",
    location: "United Kingdom",
    duration: "1:40"
  }
];

export default videoTestimonials;
