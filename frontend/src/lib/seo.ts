import type { Metadata } from "next";
import type { SeoMeta } from "@/lib/api";

/**
 * Convert Yoast SEO meta (từ backend WordPress) sang Next.js Metadata.
 * Trả về `fallback` nếu seo null (admin chưa cài Yoast hoặc chưa điền).
 */
export function seoToMetadata(seo: SeoMeta | null, fallback: Metadata): Metadata {
  if (!seo) return fallback;

  const title = seo.title || fallback.title || "";
  const description = seo.description || (typeof fallback.description === "string" ? fallback.description : "");
  const ogTitle = seo.og_title || title;
  const ogDescription = seo.og_description || description;
  const ogImage = seo.og_image || "";

  const robots = (() => {
    const r = seo.robots.toLowerCase();
    const index = !r.includes("noindex");
    const follow = !r.includes("nofollow");
    return { index, follow };
  })();

  const metadata: Metadata = {
    title,
    description,
    robots,
    openGraph: {
      title: ogTitle,
      description: ogDescription,
      type: (seo.og_type as "website" | "article") || "article",
      images: ogImage ? [{ url: ogImage }] : undefined,
    },
    twitter: {
      card: "summary_large_image",
      title: seo.twitter_title || ogTitle,
      images: seo.twitter_image ? [seo.twitter_image] : ogImage ? [ogImage] : undefined,
    },
  };

  if (seo.canonical) {
    metadata.alternates = { canonical: seo.canonical };
  }

  return metadata;
}
