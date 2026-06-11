import type { MetadataRoute } from "next";
import { getTours, getBlogPosts } from "@/lib/api";

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || "https://doi-dep.vercel.app";

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const staticRoutes: MetadataRoute.Sitemap = [
    { url: SITE_URL, changeFrequency: "daily", priority: 1 },
    { url: `${SITE_URL}/about`, changeFrequency: "monthly", priority: 0.7 },
    { url: `${SITE_URL}/contact`, changeFrequency: "monthly", priority: 0.6 },
    { url: `${SITE_URL}/experience`, changeFrequency: "weekly", priority: 0.8 },
    { url: `${SITE_URL}/booking`, changeFrequency: "daily", priority: 0.9 },
    { url: `${SITE_URL}/booking/lookup`, changeFrequency: "monthly", priority: 0.4 },
  ];

  try {
    const [{ data: tours }, posts] = await Promise.all([
      getTours({ per_page: 200 }),
      getBlogPosts(),
    ]);

    const tourRoutes: MetadataRoute.Sitemap = tours.map((t) => ({
      url: `${SITE_URL}/routes/${t.slug}`,
      changeFrequency: "weekly",
      priority: 0.8,
    }));

    const postRoutes: MetadataRoute.Sitemap = posts.map((p) => ({
      url: `${SITE_URL}/experience/${p.id}`,
      changeFrequency: "monthly",
      priority: 0.6,
    }));

    return [...staticRoutes, ...tourRoutes, ...postRoutes];
  } catch {
    return staticRoutes;
  }
}
