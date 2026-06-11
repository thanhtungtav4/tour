import type { Metadata } from "next";
import { getBlogPost } from "@/lib/api";
import { seoToMetadata } from "@/lib/seo";

interface LayoutProps {
  children: React.ReactNode;
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: LayoutProps): Promise<Metadata> {
  const { id } = await params;
  try {
    const post = await getBlogPost(id);
    const fallback: Metadata = {
      title: `${post.title} | Đôi Dép Adventure`,
      description: post.excerpt,
      openGraph: {
        title: post.title,
        description: post.excerpt,
        type: "article",
        images: post.image ? [{ url: post.image }] : undefined,
      },
    };
    return seoToMetadata(post.seo, fallback);
  } catch {
    return {
      title: "Bài viết không tồn tại | Đôi Dép Adventure",
    };
  }
}

export default function ExperienceLayout({ children }: LayoutProps) {
  return children;
}
