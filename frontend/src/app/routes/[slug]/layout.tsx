import type { Metadata } from "next";
import { getTourBySlug } from "@/lib/api";
import { seoToMetadata } from "@/lib/seo";

interface LayoutProps {
  children: React.ReactNode;
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: LayoutProps): Promise<Metadata> {
  const { slug } = await params;
  try {
    const tour = await getTourBySlug(slug);
    const fallback: Metadata = {
      title: `${tour.name} | Đôi Dép Adventure`,
      description: tour.description,
      openGraph: {
        title: tour.name,
        description: tour.description,
        images: tour.thumbnail ? [{ url: tour.thumbnail }] : undefined,
      },
    };
    return seoToMetadata(tour.seo, fallback);
  } catch {
    return {
      title: "Tour không tồn tại | Đôi Dép Adventure",
    };
  }
}

export default function RouteSlugLayout({ children }: LayoutProps) {
  return children;
}
