"use client";

import { useEffect, useMemo, useState } from "react";
import { TourCard } from "@/components/TourCard";
import { SunIcon, CompassIcon, MoonIcon } from "@/components/icons";
import { cn } from "@/lib/utils";
import { getTours, TourListItem } from "@/lib/api";

export function FeaturedTours() {
  const [selectedTime, setSelectedTime] = useState<string[]>([]);
  const [selectedPrice, setSelectedPrice] = useState<string[]>([]);
  const [selectedDuration, setSelectedDuration] = useState<string[]>([]);
  const [selectedDifficulty, setSelectedDifficulty] = useState<string[]>([]);
  const [visibleCount, setVisibleCount] = useState(6);
  const [tours, setTours] = useState<TourListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    getTours({ per_page: 100 })
      .then(({ data }) => {
        if (!cancelled) setTours(data);
      })
      .catch((err: unknown) => {
        if (!cancelled) setError(err instanceof Error ? err.message : "Không tải được danh sách tour");
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, []);

  const toggleFilter = (group: "time" | "price" | "duration" | "difficulty", value: string) => {
    setVisibleCount(6);
    if (group === "time") {
      setSelectedTime(prev =>
        prev.includes(value) ? prev.filter(v => v !== value) : [...prev, value]
      );
    } else if (group === "price") {
      setSelectedPrice(prev =>
        prev.includes(value) ? prev.filter(v => v !== value) : [...prev, value]
      );
    } else if (group === "duration") {
      setSelectedDuration(prev =>
        prev.includes(value) ? prev.filter(v => v !== value) : [...prev, value]
      );
    } else if (group === "difficulty") {
      setSelectedDifficulty(prev =>
        prev.includes(value) ? prev.filter(v => v !== value) : [...prev, value]
      );
    }
  };

  const filteredTours = useMemo(() => tours.filter(tour => {
    if (selectedTime.length > 0) {
      const matchesTime = selectedTime.some(time =>
        tour.departure_times.some(dt => dt.toLowerCase().includes(time.toLowerCase()))
      );
      if (!matchesTime) return false;
    }

    if (selectedPrice.length > 0) {
      const matchesPrice = selectedPrice.some(priceOpt => {
        if (priceOpt === "Dưới 500k") return tour.price < 500000;
        if (priceOpt === "500k - 1tr") return tour.price >= 500000 && tour.price <= 1000000;
        if (priceOpt === "Trên 1tr") return tour.price > 1000000;
        return false;
      });
      if (!matchesPrice) return false;
    }

    if (selectedDuration.length > 0) {
      const matchesDuration = selectedDuration.some(durationOpt => {
        if (durationOpt === "1 ngày") return tour.duration === "1 ngày";
        if (durationOpt === "2-3 ngày") return tour.duration === "2-3 ngày" || /^[23] ngày/.test(tour.duration);
        if (durationOpt === "4+ ngày") return /^[4-9]\d* ngày/.test(tour.duration) || /\d+ đêm/.test(tour.duration);
        return false;
      });
      if (!matchesDuration) return false;
    }

    if (selectedDifficulty.length > 0) {
      const matchesDifficulty = selectedDifficulty.some(diffOpt => {
        const difficultyMap: Record<string, string> = {
          "Dễ": "easy",
          "Trung bình": "medium",
          "Khó": "hard"
        };
        return tour.difficulty === difficultyMap[diffOpt];
      });
      if (!matchesDifficulty) return false;
    }

    return true;
  }), [tours, selectedTime, selectedPrice, selectedDuration, selectedDifficulty]);

  const timeFilters = [
    { label: "Sáng", icon: <SunIcon className="w-3.5 h-3.5" /> },
    { label: "Hệ thống", icon: <CompassIcon className="w-3.5 h-3.5" /> },
    { label: "Tối", icon: <MoonIcon className="w-3.5 h-3.5" /> },
  ];

  const priceFilters = ["Dưới 500k", "500k - 1tr", "Trên 1tr"];
  const durationFilters = ["1 ngày", "2-3 ngày", "4+ ngày"];
  const difficultyFilters = ["Dễ", "Trung bình", "Khó"];

  return (
    <section className="py-16 md:py-24 bg-gradient-to-b from-white to-[#f5f7fa] overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Title Block */}
        <div className="text-center mb-10">
          <h2 className="text-4xl md:text-5xl lg:text-[56px] font-extrabold text-[#0e1425] tracking-tight mb-4">
            Lịch trình sắp tới
          </h2>
          <p className="text-lg text-gray-500 max-w-2xl mx-auto">
            Những trải nghiệm tuyệt vời đang chờ đón bạn trong thời gian tới
          </p>
        </div>

        {/* Stats Block */}
        <div className="flex justify-center gap-4 sm:gap-12 mb-12 text-center">
          <div className="flex flex-col items-center">
            <span className="text-[40px] sm:text-[48px] font-extrabold text-[#16a249] leading-none">
              {filteredTours.length}
            </span>
            <span className="text-xs sm:text-sm text-gray-500 mt-1 font-semibold uppercase tracking-wider">
              chuyến
            </span>
          </div>
          <div className="w-[1px] bg-gray-200 self-stretch my-2" />
          <div className="flex flex-col items-center justify-center">
            <span className="text-lg sm:text-2xl font-extrabold text-[#16a249] uppercase tracking-wide whitespace-nowrap">
              Sắp khởi hành
            </span>
          </div>
        </div>

        {/* Filter Pills Container */}
        <div className="flex flex-col gap-4 items-center mb-12 bg-white p-6 rounded-2xl shadow-sm border border-gray-100/80 max-w-4xl mx-auto">
          {/* Time & Price Filters row */}
          <div className="flex flex-wrap gap-6 justify-center w-full">
            {/* Time Filter Group */}
            <div className="flex gap-2 items-center">
              {timeFilters.map(item => {
                const isActive = selectedTime.includes(item.label);
                return (
                  <button
                    key={item.label}
                    onClick={() => toggleFilter("time", item.label)}
                    className={cn(
                      "flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-semibold transition focus-visible:outline-none cursor-pointer border",
                      isActive
                        ? "bg-[#16a249] border-[#16a249] text-white"
                        : "bg-white border-[#d3dae4] text-[#0e1425] hover:border-[#16a249] hover:text-[#16a249]"
                    )}
                  >
                    {item.icon}
                    <span>{item.label}</span>
                  </button>
                );
              })}
            </div>

            {/* Price Filter Group */}
            <div className="flex gap-2 items-center">
              {priceFilters.map(label => {
                const isActive = selectedPrice.includes(label);
                return (
                  <button
                    key={label}
                    onClick={() => toggleFilter("price", label)}
                    className={cn(
                      "px-3 py-1.5 rounded-full text-xs font-medium transition-all whitespace-nowrap cursor-pointer border",
                      isActive
                        ? "bg-[#16a249] border-[#16a249] text-white"
                        : "bg-white border-[#d3dae4] text-[#0e1425] hover:border-[#16a249] hover:text-[#16a249]"
                    )}
                  >
                    {label}
                  </button>
                );
              })}
            </div>
          </div>

          {/* Duration & Difficulty Filters row */}
          <div className="flex flex-wrap gap-6 justify-center w-full">
            {/* Duration Filter Group */}
            <div className="flex gap-2 items-center">
              {durationFilters.map(label => {
                const isActive = selectedDuration.includes(label);
                return (
                  <button
                    key={label}
                    onClick={() => toggleFilter("duration", label)}
                    className={cn(
                      "px-3 py-1.5 rounded-full text-xs font-medium transition-all whitespace-nowrap cursor-pointer border",
                      isActive
                        ? "bg-[#16a249] border-[#16a249] text-white"
                        : "bg-white border-[#d3dae4] text-[#0e1425] hover:border-[#16a249] hover:text-[#16a249]"
                    )}
                  >
                    {label}
                  </button>
                );
              })}
            </div>

            {/* Difficulty Filter Group */}
            <div className="flex gap-2 items-center">
              {difficultyFilters.map(label => {
                const isActive = selectedDifficulty.includes(label);
                return (
                  <button
                    key={label}
                    onClick={() => toggleFilter("difficulty", label)}
                    className={cn(
                      "px-3 py-1.5 rounded-full text-xs font-medium transition-all whitespace-nowrap cursor-pointer border",
                      isActive
                        ? "bg-[#16a249] border-[#16a249] text-white"
                        : "bg-white border-[#d3dae4] text-[#0e1425] hover:border-[#16a249] hover:text-[#16a249]"
                    )}
                  >
                    {label}
                  </button>
                );
              })}
            </div>
          </div>
        </div>

        {/* Tours Grid */}
        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 animate-pulse">
                <div className="aspect-[4/3] bg-gray-100" />
                <div className="p-4 space-y-3">
                  <div className="h-4 bg-gray-100 rounded w-2/3" />
                  <div className="h-3 bg-gray-100 rounded w-1/3" />
                  <div className="h-6 bg-gray-100 rounded w-1/2 mt-2" />
                </div>
              </div>
            ))}
          </div>
        ) : error ? (
          <div className="text-center py-12 bg-white rounded-2xl border border-rose-200 shadow-sm max-w-md mx-auto">
            <p className="text-rose-600 font-semibold">Không tải được danh sách tour</p>
            <p className="text-sm text-gray-500 mt-1">{error}</p>
          </div>
        ) : filteredTours.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            {filteredTours.slice(0, visibleCount).map(tour => (
              <TourCard key={tour.id} tour={tour} />
            ))}
          </div>
        ) : (
          <div className="text-center py-12 bg-white rounded-2xl border border-gray-150 shadow-sm max-w-md mx-auto">
            <svg className="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 className="text-lg font-bold text-gray-850 mb-1">Không tìm thấy chuyến đi</h3>
            <p className="text-sm text-gray-500">Thử chọn các tiêu chí bộ lọc khác xem sao nhé!</p>
          </div>
        )}

        {/* View More Button */}
        {filteredTours.length > visibleCount && (
          <div className="mt-12 flex justify-center">
            <button
              onClick={() => setVisibleCount(prev => prev + 6)}
              className="flex items-center gap-2 text-sm font-bold text-[#16a249] hover:gap-3 transition-all group cursor-pointer"
            >
              Xem thêm
              <svg className="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </button>
          </div>
        )}
      </div>
    </section>
  );
}
