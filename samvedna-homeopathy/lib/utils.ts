import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export const siteUrl = "https://samvednahomeopathy.com";

export const blurDataUrl =
  "data:image/gif;base64,R0lGODlhAQABAIAAAPj7+QAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==";

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
