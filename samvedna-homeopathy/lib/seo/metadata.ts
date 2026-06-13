import type { Metadata } from "next";
import { siteUrl } from "@/lib/utils";

export function createMetadata(): Metadata {
  return {
    metadataBase: new URL(siteUrl),
    title: "Samvedna Homeopathy | Autism & ADHD Specialist - Dr. Krunal Kosada",
    description:
      "20+ years of specialized homeopathic support for Autism, ADHD, Speech Delay, Learning Disability, Developmental Delay, Genetic Disorders and Neurological Disorders. Worldwide online consultations.",
    alternates: {
      canonical: siteUrl
    },
    icons: {
      icon: "/images/samvedna-logo.webp",
      shortcut: "/images/samvedna-logo.webp",
      apple: "/images/samvedna-logo.webp",
    },
    openGraph: {
      title:
        "Samvedna Homeopathy | Autism & ADHD Specialist - Dr. Krunal Kosada",
      description:
        "Specialized pediatric neurodevelopmental homeopathic care for families in India and internationally.",
      url: siteUrl,
      siteName: "Samvedna Homeopathy",
      locale: "en_IN",
      type: "website",
      images: [
        {
          url: "/images/dr-krunal-kosada.jpg",
          width: 1200,
          height: 1800,
          alt: "Dr. Krunal Kosada of Samvedna Homeopathy"
        }
      ]
    },
    twitter: {
      card: "summary_large_image",
      title:
        "Samvedna Homeopathy | Autism & ADHD Specialist - Dr. Krunal Kosada",
      description:
        "20+ years of specialized homeopathic support for child developmental and neurological concerns.",
      images: ["/images/dr-krunal-kosada.jpg"]
    }
  };
}
