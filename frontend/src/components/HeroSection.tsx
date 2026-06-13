"use client";

import Link from "next/link";
import Image from "next/image";
import { MapPinIcon, UsersIcon, CalendarIcon, MountainIcon, ChevronRightIcon } from "@/components/icons";

interface HeroSectionProps {
  data?: {
    banner?: string;
    badge?: string;
    title?: string;
    subtitle?: string;
  };
}

export function HeroSection({ data }: HeroSectionProps) {
  const banner = data?.banner || "/images/banner3.jpg";
  const badge = data?.badge || "Trekking & Camping Experience";
  const subtitle = data?.subtitle || "Trải nghiệm những chuyến đi trekking, camping tuyệt vời nhất cùng đội ngũ hướng dẫn viên chuyên nghiệp";
  
  const rawTitle = data?.title || "Khám phá|thiên nhiên Việt Nam";
  const titleParts = rawTitle.split("|");

  return (
    <section className="relative">
      {/* Banner with optimized image */}
      <div className="relative h-[400px] sm:h-[480px] lg:h-[560px] overflow-hidden">
        {/* Background Image (optimized using Next.js Image component) */}
        <Image
          src={banner}
          alt="Đôi Dép Adventure - Khám phá thiên nhiên Việt Nam"
          fill
          priority
          sizes="100vw"
          className="object-cover -z-20"
          quality={85}
        />
        {/* Gradient Overlay */}
        <div className="absolute inset-0 bg-gradient-to-b from-black/20 to-black/70 -z-10" />
        
        {/* Fallback gradient if image fails */}
        <div className="absolute inset-0 bg-gradient-to-br from-emerald-900 via-gray-900 to-gray-800 -z-30" />

        {/* Content */}
        <div className="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
          <div className="mb-4">
            <span className="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-white text-sm font-medium">
              <MountainIcon className="w-4 h-4" />
              {badge}
            </span>
          </div>
          <h1 className="sr-only">Đôi Dép Adventure - Khám phá thiên nhiên Việt Nam</h1>
          <h2 className="text-3xl sm:text-5xl lg:text-7xl font-bold text-white mb-4 sm:mb-6 leading-tight max-w-4xl">
            {titleParts[0]}
            {titleParts[1] && (
              <span className="block text-emerald-400">{titleParts[1]}</span>
            )}
          </h2>
          <p className="text-sm sm:text-base md:text-lg text-white/80 max-w-2xl mb-6 sm:mb-8 leading-relaxed">
            {subtitle}
          </p>

          {/* CTA Buttons */}
          <div className="flex flex-col sm:flex-row gap-4">
            <Link
              href="/booking"
              className="inline-flex items-center gap-2 px-8 py-4 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5"
            >
              Đặt tour ngay
              <ChevronRightIcon className="w-5 h-5" />
            </Link>
            <Link
              href="/experience"
              className="inline-flex items-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-md border border-white/20 text-white font-bold rounded-xl hover:bg-white/20 transition-all"
            >
              Xem trải nghiệm
            </Link>
          </div>
        </div>

        {/* Scroll indicator */}
        <div className="absolute bottom-6 left-1/2 -translate-x-1/2 animate-bounce hidden sm:block">
          <div className="w-8 h-12 rounded-full border-2 border-white/30 flex items-start justify-center p-2">
            <div className="w-1.5 h-3 bg-white/60 rounded-full animate-pulse" />
          </div>
        </div>
      </div>
    </section>
  );
}