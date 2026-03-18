import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  output: 'standalone',
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'beaconstonerealty.com',
      },
      {
        protocol: 'https',
        hostname: '*.beaconstonerealty.com',
      },
    ],
  },
};

export default nextConfig;
