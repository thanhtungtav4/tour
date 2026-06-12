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

export default async function ExperienceLayout({ children, params }: LayoutProps) {
  const { id } = await params;
  let schemaData: any = null;
  try {
    const post = await getBlogPost(id);
    schemaData = post.seo?.schema;
  } catch (err) {
    console.error("Failed to load schema for post in layout:", err);
  }

  return (
    <>
      {schemaData && (
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(schemaData) }}
        />
      )}
      {children}
    </>
  );
}
