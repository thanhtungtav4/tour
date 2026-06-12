import { Header } from "@/components/Header";
import { HeroSection } from "@/components/HeroSection";
import { FeaturedTours } from "@/components/FeaturedTours";
import { AboutSection } from "@/components/AboutSection";
import { PartnersSection } from "@/components/PartnersSection";
import { BlogSection } from "@/components/BlogSection";
import { Footer } from "@/components/Footer";
import { getHomepageData } from "@/lib/api";
import type { Metadata } from "next";
import { seoToMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  try {
    const data = await getHomepageData();
    const fallback: Metadata = {
      title: "Đôi Dép Adventure | Chuyến đi trekking, cắm trại dã ngoại chuyên nghiệp",
      description: "Trải nghiệm những chuyến đi trekking, camping tuyệt vời nhất cùng đội ngũ hướng dẫn viên chuyên nghiệp.",
    };
    return seoToMetadata(data.seo, fallback);
  } catch {
    return {
      title: "Đôi Dép Adventure | Chuyến đi trekking, cắm trại dã ngoại chuyên nghiệp",
      description: "Trải nghiệm những chuyến đi trekking, camping tuyệt vời nhất cùng đội ngũ hướng dẫn viên chuyên nghiệp.",
    };
  }
}

export default async function Home() {
  let homepageData = null;
  try {
    homepageData = await getHomepageData();
  } catch (err) {
    console.error("Failed to load homepage data in Server Component:", err);
  }

  return (
    <div className="min-h-screen flex flex-col">
      {homepageData?.seo?.schema && (
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(homepageData.seo.schema) }}
        />
      )}
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero */}
        <HeroSection data={homepageData?.hero} />

        {/* FeaturedTours (Upcoming schedule with stats, filters and dynamic grid) */}
        <FeaturedTours />

        {/* Partners Section */}
        <PartnersSection data={homepageData?.ecosystem} />

        {/* About Section */}
        <AboutSection data={homepageData?.about} />

        {/* Blog Section */}
        <BlogSection />
      </main>
      <Footer />
    </div>
  );
}

