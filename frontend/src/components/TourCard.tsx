"use client";

import Image from "next/image";
import Link from "next/link";
import { cn } from "@/lib/utils";
import { CalendarIcon, MapPinIcon } from "@/components/icons";

interface TourCardProps {
  tour: {
    id: string;
    slug: string;
    name: string;
    description: string;
    imageFilename?: string;
    gallery: string[];
    price: number;
    difficulty: "easy" | "medium" | "hard";
    duration: string;
    availableSpots: number;
    departureTime: string;
    highlights: string[];
    departureDates: { date: string; availableSpots: number }[];
  };
  className?: string;
}

const difficultyConfig = {
  easy: {
    label: "Dễ",
    badgeBg: "bg-emerald-100",
    badgeText: "text-emerald-800",
  },
  medium: {
    label: "Trung bình",
    badgeBg: "bg-amber-100",
    badgeText: "text-amber-800",
  },
  hard: {
    label: "Khó",
    badgeBg: "bg-red-100",
    badgeText: "text-red-800",
  },
} as const;

const departureConfig = {
  Sáng: { label: "Sáng", bg: "bg-orange-100", text: "text-orange-700" },
  Hệ: { label: "Hệ thống", bg: "bg-blue-100", text: "text-blue-700" },
  Tối: { label: "Tối", bg: "bg-purple-100", text: "text-purple-700" },
} as const;

export function TourCard({ tour, className }: TourCardProps) {
  const difficulty = difficultyConfig[tour.difficulty];
  const departureKey = (Object.keys(departureConfig).find(key => tour.departureTime.includes(key)) || "Sáng");
  const departure = departureConfig[departureKey as keyof typeof departureConfig];

  return (
    <Link
      href={`/routes/${tour.slug}`}
      className={cn(
        "group block bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100",
        "hover:shadow-xl hover:border-emerald-200 transition-all duration-300",
        "hover:-translate-y-1",
        className
      )}
    >
      {/* Image Container */}
      <div className="relative aspect-[4/3] overflow-hidden">
        <Image
          src={tour.gallery?.[0] || `/images/${tour.imageFilename || `${tour.slug}.jpg`}`}
          alt={tour.name}
          fill
          sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
          className="object-cover transition-transform duration-500 group-hover:scale-105"
        />

        {/* Gradient Overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />

        {/* Top Badges */}
        <div className="absolute top-3 left-3 right-3 flex items-center justify-between">
          {/* Difficulty Badge */}
          <span
            className={cn(
              "px-2.5 py-1 text-xs font-bold uppercase tracking-wide rounded-full",
              difficulty.badgeBg,
              difficulty.badgeText
            )}
          >
            {difficulty.label}
          </span>

          {/* Departure Badge */}
          <span
            className={cn(
              "px-2.5 py-1 text-xs font-semibold rounded-full",
              departure.bg,
              departure.text
            )}
          >
            {tour.departureTime}
          </span>
        </div>

        {/* Bottom Content */}
        <div className="absolute bottom-0 left-0 right-0 p-4">
          <h3 className="text-lg font-bold text-white mb-1 drop-shadow-lg line-clamp-1">
            {tour.name}
          </h3>
          <p className="text-sm text-white/90 line-clamp-2 leading-relaxed">
            {tour.description}
          </p>
        </div>
      </div>

      {/* Card Content */}
      <div className="p-4">
        {/* Meta Row */}
        <div className="flex items-center gap-4 text-sm text-gray-500 mb-3">
          <div className="flex items-center gap-1.5">
            <CalendarIcon className="w-4 h-4" />
            <span>{tour.duration}</span>
          </div>
          <div className="flex items-center gap-1.5">
            <MapPinIcon className="w-4 h-4" />
            <span>Việt Nam</span>
          </div>
        </div>

        {/* Highlights */}
        {tour.highlights && tour.highlights.length > 0 && (
          <div className="flex flex-wrap gap-1.5 mb-4">
            {tour.highlights.slice(0, 3).map((highlight, i) => (
              <span
                key={i}
                className="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full"
              >
                {highlight}
              </span>
            ))}
          </div>
        )}

        {/* Divider */}
        <div className="border-t border-gray-100 pt-4">
          {/* Bottom Row */}
          <div className="flex items-center justify-between">
            <div>
              <span className="text-2xl font-bold text-emerald-700">
                {tour.price.toLocaleString("vi-VN")}đ
              </span>
              <span className="text-xs text-gray-500">/người</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="flex items-center gap-1">
                <div className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse" />
                <span className="text-sm text-gray-500">Còn {tour.availableSpots} chỗ</span>
              </div>
              <div className="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                <svg className="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Link>
  );
}
