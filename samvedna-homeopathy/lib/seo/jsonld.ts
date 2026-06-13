import faq from "@/constants/faq";
import { contact, siteUrl } from "@/lib/utils";

type JsonLd = Record<string, unknown>;

const sameAs = [
  "https://www.instagram.com/samvedna_homeopathy/",
  "https://www.youtube.com/user/drkrunalkosada",
  "https://www.facebook.com/61559641756389/",
  "https://in.linkedin.com/in/dr-krunal-kosada-7459511b"
];

const address = {
  "@type": "PostalAddress",
  streetAddress:
    "261, The Galleria Shopping Hub, Sanjeevkumar Auditorium Road, Pal",
  addressLocality: "Surat",
  addressRegion: "Gujarat",
  postalCode: "395009",
  addressCountry: "IN"
};

export function medicalBusinessJsonLd(): JsonLd {
  return {
    "@context": "https://schema.org",
    "@type": "MedicalBusiness",
    name: "Samvedna Homeopathy",
    url: siteUrl,
    image: `${siteUrl}/images/dr-krunal-kosada.jpg`,
    telephone: contact.phonePrimary,
    email: contact.email,
    address,
    geo: {
      "@type": "GeoCoordinates",
      latitude: 21.185443,
      longitude: 72.783439
    },
    medicalSpecialty: [
      "Pediatric",
      "Homeopathic",
      "DevelopmentalDisorder",
      "Neurologic"
    ],
    openingHoursSpecification: [
      {
        "@type": "OpeningHoursSpecification",
        dayOfWeek: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        opens: "10:00",
        closes: "19:00"
      }
    ],
    areaServed: ["India", "United Arab Emirates", "United Kingdom", "United States", "Canada", "Australia", "Singapore", "Germany"]
  };
}

export function physicianJsonLd(): JsonLd {
  return {
    "@context": "https://schema.org",
    "@type": "Physician",
    name: "Dr. Krunal Kosada",
    honorificPrefix: "Dr.",
    url: `${siteUrl}/#founder`,
    image: `${siteUrl}/images/dr-krunal-kosada.jpg`,
    medicalSpecialty: ["Homeopathy", "Pediatric Neurodevelopmental Care"],
    hasCredential: ["BHMS", "FCAH"],
    affiliation: {
      "@type": "Organization",
      name: "Samvedna Homeopathy",
      url: siteUrl
    },
    address,
    telephone: contact.phonePrimary
  };
}

export function organizationJsonLd(): JsonLd {
  return {
    "@context": "https://schema.org",
    "@type": "Organization",
    name: "Samvedna Homeopathy",
    url: siteUrl,
    logo: `${siteUrl}/images/samvedna-logo.webp`,
    foundingDate: "2002",
    founder: {
      "@type": "Person",
      name: "Dr. Krunal Kosada"
    },
    sameAs,
    contactPoint: [
      {
        "@type": "ContactPoint",
        telephone: contact.phonePrimary,
        contactType: "appointments",
        areaServed: "Worldwide",
        availableLanguage: ["English", "Hindi", "Gujarati"]
      }
    ]
  };
}

export function faqPageJsonLd(): JsonLd {
  return {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: faq.map((item) => ({
      "@type": "Question",
      name: item.question,
      acceptedAnswer: {
        "@type": "Answer",
        text: item.answer
      }
    }))
  };
}

export function breadcrumbJsonLd(): JsonLd {
  return {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: [
      {
        "@type": "ListItem",
        position: 1,
        name: "Home",
        item: siteUrl
      },
      {
        "@type": "ListItem",
        position: 2,
        name: "Conditions",
        item: `${siteUrl}/#conditions`
      },
      {
        "@type": "ListItem",
        position: 3,
        name: "About Dr. Kosada",
        item: `${siteUrl}/#founder`
      },
      {
        "@type": "ListItem",
        position: 4,
        name: "Contact",
        item: `${siteUrl}/#consultation`
      }
    ]
  };
}

export function allJsonLd(): JsonLd[] {
  return [
    medicalBusinessJsonLd(),
    physicianJsonLd(),
    organizationJsonLd(),
    faqPageJsonLd(),
    breadcrumbJsonLd()
  ];
}
