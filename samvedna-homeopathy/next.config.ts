import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  experimental: {
    optimizePackageImports: ["lucide-react", "framer-motion"],
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
  async rewrites() {
    const phpUrl = process.env.PHP_ADMIN_URL || "http://localhost:8080";
    return [
      {
        source: "/admin",
        destination: `${phpUrl}/admin/index.php`,
      },
      {
        source: "/admin/:path*",
        destination: `${phpUrl}/admin/:path*`,
      }
    ];
  }
};

export default nextConfig;
