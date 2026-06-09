import Link from "next/link";
import { Header } from "@/components/Header";
import { HeroSection } from "@/components/HeroSection";
import { TourCard } from "@/components/TourCard";
import { AboutSection } from "@/components/AboutSection";
import { PartnersSection } from "@/components/PartnersSection";
import { BlogSection } from "@/components/BlogSection";
import { Footer } from "@/components/Footer";
import { tours } from "@/data/tours";

export default function Home() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero */}
        <HeroSection />

        {/* Tours Section */}
        <section className="py-5 md:py-20">
          <div className="container mx-auto px-5 md:px-20">
            {/* Section Header */}
            <div className="flex items-center justify-between mb-8">
              <div>
                <h2 className="text-2xl md:text-4xl font-bold text-[#0e1425]">
                  Tour nổi bật
                </h2>
                <p className="text-[#6b7280] mt-1">Khám phá những tuyến đường tuyệt vời</p>
              </div>
              <Link
                href="/booking"
                className="hidden md:flex items-center gap-2 text-emerald-600 font-semibold hover:gap-3 transition-all"
              >
                Xem tất cả
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </Link>
            </div>

            {/* Tours Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {tours.slice(0, 6).map((tour) => (
                <TourCard key={tour.id} tour={tour} />
              ))}
            </div>

            {/* View All Button */}
            <div className="mt-10 text-center">
              <Link
                href="/booking"
                className="inline-flex items-center gap-2 px-8 py-3 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition-colors"
              >
                Xem tất cả tour
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </Link>
            </div>
          </div>
        </section>

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
