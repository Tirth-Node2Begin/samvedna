import Image from "next/image";
import Link from "next/link";
import { conditionList } from "@/constants/conditions";
import { navItems, socialLinks } from "@/constants/site";
import { contact } from "@/lib/utils";

export default function Footer() {
  return (
    <footer className="bg-bg-soft text-text border-t border-border">
      <div className="mx-auto grid max-w-content gap-10 px-5 py-16 md:grid-cols-2 md:px-8 lg:grid-cols-[1.2fr_0.8fr_0.9fr_1.1fr]">
        <div>
          <Image
            src="/images/samvedna-logo.webp"
            alt="Samvedna Homeopathy"
            width={157}
            height={50}
            className="rounded-control"
          />
          <p className="mt-6 max-w-sm text-base leading-7 text-muted">
            Samvedna supports children with autism, ADHD, speech delay,
            learning difficulty, developmental delay, genetic concerns, and
            neurological disorders through personalized homeopathic care.
          </p>
          <div className="mt-6 flex flex-wrap gap-3">
            {socialLinks.map((link) => (
              <a
                key={link.label}
                href={link.href}
                target="_blank"
                rel="noreferrer"
                className="rounded-control border border-border px-3 py-2 text-sm font-semibold text-text transition hover:border-primary hover:text-primary"
              >
                {link.label}
              </a>
            ))}
          </div>
        </div>

        <div>
          <h2 className="font-display text-xl font-semibold">Quick Links</h2>
          <ul className="mt-5 space-y-3">
            {navItems.map((item) => (
              <li key={item.href}>
                <a
                  href={item.href}
                  className="text-sm text-muted transition hover:text-primary"
                >
                  {item.label}
                </a>
              </li>
            ))}
          </ul>
        </div>

        <div>
          <h2 className="font-display text-xl font-semibold">Conditions Treated</h2>
          <ul className="mt-5 space-y-3">
            {conditionList.map((condition) => (
              <li key={condition}>
                <Link
                  href="/#conditions"
                  className="text-sm text-muted transition hover:text-primary"
                >
                  {condition}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        <div>
          <h2 className="font-display text-xl font-semibold">Contact</h2>
          <address className="mt-5 not-italic text-sm leading-7 text-muted">
            {contact.address}
          </address>
          <div className="mt-5 space-y-2 text-sm text-muted">
            <p>
              <a href={contact.phoneHref} className="hover:text-primary">
                {contact.phonePrimary}
              </a>
            </p>
            <p>{contact.phoneSecondary}</p>
            <p>
              <a href={`mailto:${contact.email}`} className="hover:text-primary">
                {contact.email}
              </a>
            </p>
            <p>{contact.hours}</p>
          </div>
        </div>
      </div>

      <div className="border-t border-border">
        <div className="mx-auto flex max-w-content flex-col gap-4 px-5 py-6 text-xs leading-6 text-muted md:flex-row md:items-center md:justify-between md:px-8">
          <p>Copyright 2026 Samvedna Homeopathy. All rights reserved.</p>
          <p>
            Medical disclaimer: information on this site is educational and does
            not replace an individual consultation.
          </p>
        </div>
      </div>
    </footer>
  );
}
