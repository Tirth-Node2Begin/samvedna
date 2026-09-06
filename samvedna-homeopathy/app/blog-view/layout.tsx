import type { Metadata } from "next";

/**
 * The fallback route renders a real post but at a URL that is an Apache
 * implementation detail, so keep it out of the index — the canonical page for
 * any post is /blog/<slug>, which exists as soon as the site is rebuilt.
 */
export const metadata: Metadata = {
  title: "Article | Samvedna Homeopathy",
  robots: { index: false, follow: true },
};

export default function BlogViewLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
