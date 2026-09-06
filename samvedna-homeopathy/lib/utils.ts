import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

// Drives metadataBase, canonical tags and every JSON-LD url. Set
// NEXT_PUBLIC_SITE_URL at build time when deploying to a different domain -
// a mismatch here tells search engines the real site lives somewhere else.
export const siteUrl = (
  process.env.NEXT_PUBLIC_SITE_URL || "https://samvedna.node2begin.com"
).replace(/\/+$/, "");

export const blurDataUrl =
  "data:image/gif;base64,R0lGODlhAQABAIAAAPj7+QAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==";

// Fallback cover used when a blog post has no image (e.g. an admin post created
// without uploading a thumbnail), so cards never render an empty/blank frame.
export const defaultBlogImage = "/images/samvedna-auditorium.webp";

export const contact = {
  phonePrimary: "+91-78748-76777",
  phoneSecondary: "+91-98986-48777",
  phoneHref: "tel:+917874876777",
  whatsappHref: "https://wa.me/917874876777",
  email: "samvedna.helpdesk@gmail.com",
  address:
    "261, The Galleria Shopping Hub, Sanjeevkumar Auditorium Road, Pal, Surat, Gujarat 395009, India",
  hours: "Monday to Saturday, 10 am to 7 pm"
} as const;

export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}
