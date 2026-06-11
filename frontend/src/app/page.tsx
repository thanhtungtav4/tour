import { Header } from "@/components/Header";
import { HeroSection } from "@/components/HeroSection";
import { FeaturedTours } from "@/components/FeaturedTours";
import { AboutSection } from "@/components/AboutSection";
import { PartnersSection } from "@/components/PartnersSection";
import { BlogSection } from "@/components/BlogSection";
import { Footer } from "@/components/Footer";

export default function Home() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero */}
        <HeroSection />

        {/* FeaturedTours (Upcoming schedule with stats, filters and dynamic grid) */}
        <FeaturedTours />

        {/* Partners Section */}
        <PartnersSection />

        {/* About Section */}
        <AboutSection />

        {/* Blog Section */}
        <BlogSection />
      </main>
      <Footer />
    </div>
  );
}

