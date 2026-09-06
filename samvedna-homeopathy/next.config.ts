import { PHASE_DEVELOPMENT_SERVER } from "next/constants";
import type { NextConfig } from "next";

// Config is a function of the build phase so the Core PHP proxy below can be
// attached only for `next dev`. During `next build` (static export) it is left
// off entirely — export does not apply rewrites, and omitting them keeps the
// build output free of the "rewrites will not work with output: export" warning.
export default function config(phase: string): NextConfig {
  const isDev = phase === PHASE_DEVELOPMENT_SERVER;
  const php = process.env.PHP_DEV_URL ?? "http://127.0.0.1:8000";

  const nextConfig: NextConfig = {
    // The site is deployed to PHP-only cPanel hosting: no Node runtime, so the app
    // is exported as plain HTML/CSS/JS into out/ and served by Apache.
    //
    // Consequences, all handled elsewhere:
    //  - No server actions -> the consultation form POSTs to /api/leads.php.
    //  - In production, core-php shares the docroot, so /samvedna, /api and
    //    /uploads are real paths served natively by Apache (deploy/htaccess-site.conf).
    //  - No on-demand rendering -> blog pages are prebuilt via generateStaticParams
    //    and refreshed in the browser (lib/content.client.ts).
    //
    // IMPORTANT: `output: "export"` is applied for `next build` ONLY. Turning it on
    // during `next dev` makes the dev server render dynamic routes (/blog/[slug]) in
    // export mode, which crashes with "Expected clientReferenceManifest to be
    // defined" and also disables the dev rewrites below. Dev runs as a normal Next
    // server; the export happens at build time (see the `if (!isDev)` guard).
    ...(isDev ? {} : { output: "export" as const }),

    // Directory-style URLs (/blog/slug/index.html) so Apache resolves them without
    // per-page rewrite rules.
    trailingSlash: true,

    experimental: {
      optimizePackageImports: ["lucide-react"],
    },

    allowedDevOrigins: [
      "available-pavestone-icy.ngrok-free.dev",
      "*.ngrok-free.dev",
    ],

    images: {
      // next/image's default loader needs a running server. Apache serves the
      // original files from /images and /uploads instead.
      unoptimized: true,
    },
  };

  // Dev-only proxy to the Core PHP backend so the whole app is same-origin on
  // :3000 — the admin panel at /samvedna, the form endpoints at /api, and
  // uploaded images at /uploads. Requires the PHP server running
  // (npm run dev:php, which listens on 127.0.0.1:8000).
  if (isDev) {
    nextConfig.rewrites = async () => [
      { source: "/samvedna", destination: `${php}/samvedna` },
      { source: "/samvedna/:path*", destination: `${php}/samvedna/:path*` },
      { source: "/api/:path*", destination: `${php}/api/:path*` },
      { source: "/uploads/:path*", destination: `${php}/uploads/:path*` },
    ];
  }

  return nextConfig;
}
