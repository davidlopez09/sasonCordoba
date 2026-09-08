import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  basePath: process.env.NODE_ENV === 'production' ? '/sazon-cordoba' : '',
  trailingSlash: true,
  images: {
    unoptimized: true
  }
};

export default nextConfig;
