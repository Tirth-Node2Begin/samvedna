import type { Metadata } from "next";
import { CheckCircle2, Home, MessageCircle } from "lucide-react";
import Button from "@/components/ui/Button";
import { contact } from "@/lib/utils";

export const metadata: Metadata = {
  title: "Thank You | Samvedna Homeopathy",
  description:
    "Thank you for contacting Samvedna Homeopathy. Our care desk will reach out to you shortly.",
  robots: { index: false, follow: true }
};

export default function ThankYouPage() {
  return (
    <main className="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-primary via-primary-dark to-secondary px-5 py-28">
      <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(255,255,255,0.18),transparent_60%)]" />

      <section className="relative w-full max-w-xl rounded-3xl bg-white p-8 text-center shadow-premium sm:p-12">
        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-success/10">
          <CheckCircle2 className="h-9 w-9 text-success" aria-hidden="true" />
        </div>

        <h1 className="mt-6 font-display text-3xl font-semibold text-text md:text-4xl">
          Thank you for reaching out
        </h1>
        <p className="mx-auto mt-4 max-w-md text-base leading-7 text-muted">
          The Samvedna care desk has received your request and will contact you
          shortly to guide you through the next step for your child&apos;s care.
        </p>

        <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
          <Button href="/" size="lg" icon={<Home className="h-5 w-5" aria-hidden="true" />} iconPosition="left">
            Back to home
          </Button>
          <Button
            href={contact.whatsappHref}
            target="_blank"
            rel="noopener noreferrer"
            size="lg"
            variant="secondary"
            icon={<MessageCircle className="h-5 w-5" aria-hidden="true" />}
            iconPosition="left"
          >
            Chat on WhatsApp
          </Button>
        </div>
      </section>
    </main>
  );
}
