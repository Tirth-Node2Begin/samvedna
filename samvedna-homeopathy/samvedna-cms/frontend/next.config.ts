import path from "node:path";
import { fileURLToPath } from "node:url";
import type { NextConfig } from "next";

// The parent Samvedna site has its own lockfile one directory up, so Next infers
// the wrong workspace root and traces files from there. Pin it to this app.
const here = path.dirname(fileURLToPath(import.meta.url));

/**
 * The CMS runs on :3001 and the Core PHP API on :8001. Proxying /api keeps the
 * whole app same-origin, which is what lets the PHP session cookie work without
 * any token plumbing on the client.
 *
 * Start the backend first:
 *   php -S 127.0.0.1:8001 -t samvedna-cms/backend samvedna-cms/backend/router.php
 */
const php = process.env.PHP_API_URL ?? "http://127.0.0.1:8001";

const nextConfig: NextConfig = {
  outputFileTracingRoot: here,

  experimental: {
    optimizePackageImports: ["lucide-react"],
  },

  async rewrites() {
    return [
      { source: "/api/:path*", destination: `${php}/api/:path*` },
      { source: "/uploads/:path*", destination: `${php}/uploads/:path*` },
    ];
  },
};

export default nextConfig;
