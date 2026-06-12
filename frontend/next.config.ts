import type { NextConfig } from "next";
import withBundleAnalyzer from "@next/bundle-analyzer";

const nextConfig: NextConfig = {
  images: {
    formats: ["image/avif", "image/webp"],
    remotePatterns: [
      {
        protocol: "https",
        hostname: "i.postimg.cc",
      },
      {
        protocol: "https",
        hostname: "images.unsplash.com",
      },
      {
        protocol: "https",
        hostname: "tour-api.nttung.dev",
        pathname: "/wp-content/uploads/**",
      },
      {
        protocol: "https",
        hostname: "chart.googleapis.com",
        pathname: "/chart/**",
      },
      {
        protocol: "https",
        hostname: "img.vietqr.io",
      },
      {
        protocol: "https",
        hostname: "secure.gravatar.com",
      },
    ],
  },
  async redirects() {
    return [
      {
        source: "/policies/safety",
        destination: "/chinh-sach-an-toan",
        permanent: true,
      },
      {
        source: "/policies/cancel",
        destination: "/chinh-sach-huy-ve",
        permanent: true,
      },
      {
        source: "/policies/exchange",
        destination: "/chinh-sach-doi-ve-bao-luu",
        permanent: true,
      },
      {
        source: "/policies/refund",
        destination: "/chinh-sach-hoan-tien",
        permanent: true,
      },
      {
        source: "/policies/privacy",
        destination: "/chinh-sach-bao-mat",
        permanent: true,
      },
      {
        source: "/policies/terms",
        destination: "/dieu-khoan-su-dung",
        permanent: true,
      },
    ];
  },
};

const bundleAnalyzer = withBundleAnalyzer({
  enabled: process.env.ANALYZE === "true",
});

export default bundleAnalyzer(nextConfig);
