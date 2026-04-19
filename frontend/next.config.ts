import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'http',
        hostname: 'localhost',
        port: '8000',
      },
      {
        protocol: 'https',
        hostname: 'api.tiendatiziano.com',
      },
      {
        protocol: 'https',
        hostname: 'tiendatiziano.com',
      },
      {
        protocol: 'https',
        hostname: 'www.tiendatiziano.com',
      },
    ],
  },
};

export default nextConfig;
