import type { Metadata, Viewport } from "next";
import { DM_Sans } from "next/font/google";
import "./globals.css";
import { SessionProvider } from "@/lib/session";
import { ToastProvider } from "@/components/ui/Toast";
import { SmoothScroll } from "@/components/layout/SmoothScroll";

// DM Sans is the app's only typeface. Loaded at build time and self-hosted, so
// there are zero runtime requests to Google.
const dmSans = DM_Sans({
  subsets: ["latin"],
  variable: "--font-dm-sans",
  display: "swap",
});

export const metadata: Metadata = {
  title: {
    default: "Samvedna CMS",
    template: "%s · Samvedna CMS",
  },
  description:
    "Clinical case management for Samvedna Homeopathy — intake, care plans, follow-up schedules, forced reviews, escalations and parent progress dashboards.",
  robots: { index: false, follow: false },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  themeColor: "#f4f4f5",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={dmSans.variable}>
      <body>
        <SmoothScroll />
        <SessionProvider>
          <ToastProvider>{children}</ToastProvider>
        </SessionProvider>
      </body>
    </html>
  );
}
