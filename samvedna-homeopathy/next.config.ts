import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  experimental: {
    optimizePackageImports: ["lucide-react"],
  },
  allowedDevOrigins: [
    "available-pavestone-icy.ngrok-free.dev",
    "*.ngrok-free.dev",
  ],
  images: {
    formats: ["image/avif", "image/webp"],
    deviceSizes: [640, 768, 1024, 1280, 1440, 1920],
    imageSizes: [96, 128, 256, 384]
  },
  async redirects() {
    return [
      {
        source: "/samvedna",
        destination: "/admin/login",
        permanent: false,
      },
      {
        source: "/samvedna/:path*",
        destination: "/admin/login",
        permanent: false,
      },
    ];
  },
  async rewrites() {
    const phpUrl = process.env.PHP_ADMIN_URL || "http://localhost:8000";
    return [
      {
        source: "/admin",
        destination: `${phpUrl}/admin/index.php`,
      },
      {
        source: "/admin/:path*",
        destination: `${phpUrl}/admin/:path*`,
      },
      // Public read-only JSON API served by the Core PHP backend.
      {
        source: "/php-api/:path*",
        destination: `${phpUrl}/api/:path*`,
      },
      // Media uploaded through the admin panel (stored under core-php/uploads).
      {
        source: "/uploads/:path*",
        destination: `${phpUrl}/uploads/:path*`,
      }
    ];
  }
};

export default nextConfig;
