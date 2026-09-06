import type { Metadata } from "next";
import type { ReactNode } from "react";
import "./globals.css";
import { dmSans, inter, interTight } from "@/app/fonts";
import Navbar from "@/components/navigation/Navbar";
import AnimationProvider from "@/components/providers/AnimationProvider";
import CurrencyProvider from "@/components/providers/CurrencyProvider";
import LenisProvider from "@/components/providers/LenisProvider";
import { createMetadata } from "@/lib/seo/metadata";
import { allJsonLd } from "@/lib/seo/jsonld";

export const metadata: Metadata = createMetadata();

type RootLayoutProps = {
  children: ReactNode;
};

export default function RootLayout({ children }: RootLayoutProps) {
  const schemas = allJsonLd();

  return (
    <html
      lang="en"
      className={`${inter.variable} ${interTight.variable} ${dmSans.variable} overflow-x-hidden`}
      suppressHydrationWarning
    >
      <head>
        {schemas.map((schema, index) => (
          <script
            key={index}
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
          />
        ))}
      </head>
      <body className="font-body antialiased overflow-x-hidden" suppressHydrationWarning>
        <CurrencyProvider>
          <LenisProvider>
            <AnimationProvider>
              <Navbar />
              <main>{children}</main>
            </AnimationProvider>
          </LenisProvider>
        </CurrencyProvider>
      </body>
    </html>
  );
}
