import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: 'export',
  basePath: '/sazon-cordoba',
  trailingSlash: true,
  images: {
    unoptimized: true
  }
};

export default nextConfig;
