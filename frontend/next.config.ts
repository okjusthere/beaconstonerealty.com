import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  // Removed output: 'export' for Cloudflare Pages (dynamic SSR)
  trailingSlash: true,
  images: {
    // Use Cloudflare Image Resizing when available, fallback to unoptimized
    unoptimized: true,
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'cdn.sanity.io',
      },
      {
        protocol: 'https',
        hostname: 'uploads.kevv.ai',
      },
    ],
  },
};

export default nextConfig;
