"use client";

import { useState, useEffect } from "react";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { TourCard } from "@/components/TourCard";
import { getTours, TourListItem } from "@/lib/api";
import { SearchIcon, StarIcon, SlidersIcon, XIcon } from "@/components/icons";
import { cn } from "@/lib/utils";

type SortOption = "default" | "price-asc" | "price-desc" | "rating-desc";
type QuickFilter = "all" | "popular" | "weekend" | "best-price" | "top-rated";
type PriceRange = "all" | "under-300" | "300-500" | "500-800" | "over-800";

export default function BookingPage() {
  const [tours, setTours] = useState<TourListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedDifficulty, setSelectedDifficulty] = useState<string>("all");
  const [selectedDuration, setSelectedDuration] = useState<string>("all");
  const [selectedPriceRange, setSelectedPriceRange] = useState<PriceRange>("all");
  const [selectedDepartureTime, setSelectedDepartureTime] = useState<string>("all");
  const [sortBy, setSortBy] = useState<SortOption>("default");
  const [quickFilter, setQuickFilter] = useState<QuickFilter>("all");
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    let cancelled = false;

    getTours({
      search: searchQuery || undefined,
      difficulty: selectedDifficulty !== "all" ? selectedDifficulty : undefined,
      duration: selectedDuration !== "all" ? selectedDuration : undefined,
      departure_time: selectedDepartureTime !== "all" ? selectedDepartureTime : undefined,
      price_min: selectedPriceRange === "under-300" ? 0 : selectedPriceRange === "300-500" ? 300000 : selectedPriceRange === "500-800" ? 500000 : selectedPriceRange === "over-800" ? 800000 : undefined,
      price_max: selectedPriceRange === "under-300" ? 300000 : selectedPriceRange === "300-500" ? 500000 : selectedPriceRange === "500-800" ? 800000 : undefined,
      sort: sortBy !== "default" ? sortBy : undefined,
      per_page: 50,
    }).then((res) => {
      if (cancelled) return;
      let result = res.data;
      if (quickFilter === "best-price") result = [...result].sort((a, b) => a.price - b.price);
      else if (quickFilter === "popular") result = [...result].sort((a, b) => b.review_count - a.review_count);
      setTours(result);
    }).finally(() => {
      if (!cancelled) setLoading(false);
    });

    return () => {
      cancelled = true;
    };
  }, [searchQuery, selectedDifficulty, selectedDuration, selectedPriceRange, selectedDepartureTime, sortBy, quickFilter]);

  const priceOptions = [
    { value: "all", label: "Tất cả giá" },
    { value: "under-300", label: "Dưới 300k" },
    { value: "300-500", label: "300k - 500k" },
    { value: "500-800", label: "500k - 800k" },
    { value: "over-800", label: "Trên 800k" },
  ];

  const sortOptions = [
    { value: "default", label: "Mặc định" },
    { value: "price-asc", label: "Giá: Thấp → Cao" },
    { value: "price-desc", label: "Giá: Cao → Thấp" },
    { value: "rating-desc", label: "Đánh giá cao nhất" },
  ];

  const quickFilterOptions: { value: QuickFilter; label: string; icon: string }[] = [
    { value: "all", label: "Tất cả", icon: "" },
    { value: "popular", label: "Phổ biến", icon: "🔥" },
    { value: "best-price", label: "Giá tốt", icon: "💰" },
    { value: "top-rated", label: "Đánh giá cao", icon: "⭐" },
  ];

  const activeFilterCount = [
    selectedDifficulty !== "all",
    selectedDuration !== "all",
    selectedPriceRange !== "all",
    selectedDepartureTime !== "all",
    searchQuery !== "",
  ].filter(Boolean).length;

  const clearAllFilters = () => {
    setSearchQuery("");
    setSelectedDifficulty("all");
    setSelectedDuration("all");
    setSelectedPriceRange("all");
    setSelectedDepartureTime("all");
    setQuickFilter("all");
    setSortBy("default");
  };

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Compact Hero */}
        <section className="py-8 lg:py-12 px-4 bg-gradient-to-b from-emerald-50 to-white">
          <div className="container mx-auto text-center">
            <h1 className="text-3xl lg:text-5xl font-bold text-gray-900 mb-2">
              Khám phá <span className="text-emerald-600">tour trekking</span>
            </h1>
            <p className="text-base text-gray-500 max-w-xl mx-auto">
              12+ tuyến đường đa dạng, phù hợp mọi trình độ
            </p>
          </div>
        </section>

        {/* Search & Filters */}
        <section className="sticky top-0 z-40 mt-[81px] bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
          <div className="container mx-auto px-4 py-3">
            {/* Search Row */}
            <div className="flex items-center gap-3">
              <div className="flex-1 relative">
                <SearchIcon className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Tìm theo tên tour, địa điểm..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-11 pr-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100 outline-none transition-all text-sm"
                />
                {searchQuery && (
                  <button
                    onClick={() => setSearchQuery("")}
                    className="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 rounded-full hover:bg-gray-200"
                  >
                    <XIcon className="w-4 h-4 text-gray-400" />
                  </button>
                )}
              </div>

              {/* Quick Filters - Desktop */}
              <div className="hidden md:flex items-center gap-1.5">
                {quickFilterOptions.map((opt) => (
                  <button
                    key={opt.value}
                    onClick={() => setQuickFilter(opt.value)}
                    className={cn(
                      "px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all",
                      quickFilter === opt.value
                        ? "bg-emerald-500 text-white shadow-sm"
                        : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                    )}
                  >
                    {opt.icon && <span className="mr-1">{opt.icon}</span>}
                    {opt.label}
                  </button>
                ))}
              </div>

              {/* Filter Toggle Button */}
              <button
                onClick={() => setShowFilters(!showFilters)}
                className={cn(
                  "flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium transition-all",
                  showFilters || activeFilterCount > 0
                    ? "border-emerald-500 bg-emerald-50 text-emerald-700"
                    : "border-gray-200 bg-white text-gray-600 hover:bg-gray-50"
                )}
              >
                <SlidersIcon className="w-4 h-4" />
                <span className="hidden sm:inline">Bộ lọc</span>
                {activeFilterCount > 0 && (
                  <span className="w-5 h-5 flex items-center justify-center bg-emerald-500 text-white text-xs rounded-full">
                    {activeFilterCount}
                  </span>
                )}
              </button>
            </div>

            {/* Expanded Filters */}
            {showFilters && (
              <div className="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                {/* Difficulty */}
                <div>
                  <label className="block text-xs font-medium text-gray-500 mb-1.5">Độ khó</label>
                  <div className="flex flex-wrap gap-1.5">
                    {[
                      { value: "all", label: "Tất cả" },
                      { value: "easy", label: "Dễ" },
                      { value: "medium", label: "Trung bình" },
                      { value: "hard", label: "Khó" },
                    ].map((opt) => (
                      <button
                        key={opt.value}
                        onClick={() => setSelectedDifficulty(opt.value)}
                        className={cn(
                          "px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all",
                          selectedDifficulty === opt.value
                            ? "bg-emerald-500 text-white"
                            : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                        )}
                      >
                        {opt.label}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Duration */}
                <div>
                  <label className="block text-xs font-medium text-gray-500 mb-1.5">Thời gian</label>
                  <div className="flex flex-wrap gap-1.5">
                    {[
                      { value: "all", label: "Tất cả" },
                      { value: "1day", label: "1 ngày" },
                      { value: "multi", label: "2+ ngày" },
                    ].map((opt) => (
                      <button
                        key={opt.value}
                        onClick={() => setSelectedDuration(opt.value)}
                        className={cn(
                          "px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all",
                          selectedDuration === opt.value
                            ? "bg-emerald-500 text-white"
                            : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                        )}
                      >
                        {opt.label}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Price Range */}
                <div>
                  <label className="block text-xs font-medium text-gray-500 mb-1.5">Khoảng giá</label>
                  <select
                    value={selectedPriceRange}
                    onChange={(e) => setSelectedPriceRange(e.target.value as PriceRange)}
                    className="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none"
                  >
                    {priceOptions.map((opt) => (
                      <option key={opt.value} value={opt.value}>{opt.label}</option>
                    ))}
                  </select>
                </div>

                {/* Departure Time */}
                <div>
                  <label className="block text-xs font-medium text-gray-500 mb-1.5">Giờ khởi hành</label>
                  <select
                    value={selectedDepartureTime}
                    onChange={(e) => setSelectedDepartureTime(e.target.value)}
                    className="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none"
                  >
                    <option value="all">Tất cả</option>
                    <option value="Sáng">Sáng</option>
                    <option value="Hệ thống">Hệ thống</option>
                  </select>
                </div>

                {/* Sort */}
                <div>
                  <label className="block text-xs font-medium text-gray-500 mb-1.5">Sắp xếp</label>
                  <select
                    value={sortBy}
                    onChange={(e) => setSortBy(e.target.value as SortOption)}
                    className="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none"
                  >
                    {sortOptions.map((opt) => (
                      <option key={opt.value} value={opt.value}>{opt.label}</option>
                    ))}
                  </select>
                </div>
              </div>
            )}

            {/* Active Filters Bar */}
            {activeFilterCount > 0 && (
              <div className="mt-3 flex items-center gap-2 flex-wrap">
                <span className="text-xs text-gray-500">Đang lọc:</span>
                {searchQuery && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs rounded-md">
                    &ldquo;{searchQuery}&rdquo;
                    <button onClick={() => setSearchQuery("")} className="hover:text-emerald-900">
                      <XIcon className="w-3 h-3" />
                    </button>
                  </span>
                )}
                {selectedDifficulty !== "all" && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs rounded-md">
                    {selectedDifficulty === "easy" ? "Dễ" : selectedDifficulty === "medium" ? "Trung bình" : "Khó"}
                    <button onClick={() => setSelectedDifficulty("all")} className="hover:text-emerald-900">
                      <XIcon className="w-3 h-3" />
                    </button>
                  </span>
                )}
                {selectedDuration !== "all" && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs rounded-md">
                    {selectedDuration === "1day" ? "1 ngày" : "2+ ngày"}
                    <button onClick={() => setSelectedDuration("all")} className="hover:text-emerald-900">
                      <XIcon className="w-3 h-3" />
                    </button>
                  </span>
                )}
                {selectedPriceRange !== "all" && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs rounded-md">
                    {priceOptions.find(o => o.value === selectedPriceRange)?.label}
                    <button onClick={() => setSelectedPriceRange("all")} className="hover:text-emerald-900">
                      <XIcon className="w-3 h-3" />
                    </button>
                  </span>
                )}
                {selectedDepartureTime !== "all" && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs rounded-md">
                    {selectedDepartureTime}
                    <button onClick={() => setSelectedDepartureTime("all")} className="hover:text-emerald-900">
                      <XIcon className="w-3 h-3" />
                    </button>
                  </span>
                )}
                <button
                  onClick={clearAllFilters}
                  className="text-xs text-gray-400 hover:text-red-500 underline ml-1"
                >
                  Xóa tất cả
                </button>
              </div>
            )}
          </div>
        </section>

        {/* Mobile Quick Filters */}
        <section className="md:hidden border-b border-gray-100 bg-white">
          <div className="container mx-auto px-4 py-2 flex gap-2 overflow-x-auto scrollbar-hide">
            {quickFilterOptions.map((opt) => (
              <button
                key={opt.value}
                onClick={() => setQuickFilter(opt.value)}
                className={cn(
                  "flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-all",
                  quickFilter === opt.value
                    ? "bg-emerald-500 text-white"
                    : "bg-gray-100 text-gray-600"
                )}
              >
                {opt.icon && <span className="mr-1">{opt.icon}</span>}
                {opt.label}
              </button>
            ))}
          </div>
        </section>

        {/* Tour Grid */}
        <section className="py-6 lg:py-10">
          <div className="container mx-auto px-4">
            {/* Results Header */}
            <div className="flex items-center justify-between mb-6">
              <p className="text-sm text-gray-500">
                <span className="font-semibold text-gray-900">{tours.length}</span> tour
              </p>
              <div className="flex items-center gap-1 text-xs text-gray-400">
                <StarIcon className="w-3.5 h-3.5 text-yellow-400 fill-current" />
                <span>4.9/5</span>
              </div>
            </div>

            {/* Loading State */}
            {loading && (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {[1, 2, 3].map((i) => (
                  <div key={i} className="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 animate-pulse">
                    <div className="aspect-[4/3] bg-gray-200" />
                    <div className="p-4 space-y-3">
                      <div className="h-4 bg-gray-200 rounded w-3/4" />
                      <div className="h-3 bg-gray-200 rounded w-1/2" />
                      <div className="h-6 bg-gray-200 rounded w-1/3" />
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Tour Cards */}
            {!loading && (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {tours.map((tour) => (
                  <div key={tour.id} className="group">
                    <TourCard tour={{
                      id: tour.slug,
                      slug: tour.slug,
                      name: tour.name,
                      description: tour.description,
                      imageFilename: tour.image_filename,
                      gallery: tour.gallery,
                      price: tour.price,
                      difficulty: tour.difficulty,
                      duration: tour.duration,
                      availableSpots: tour.available_spots,
                      departureTime: tour.departure_times[0],
                      highlights: tour.highlights,
                      departureDates: [],
                    }} />
                  </div>
                ))}
              </div>
            )}

            {/* Empty State */}
            {!loading && tours.length === 0 && (
              <div className="text-center py-16">
                <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <SearchIcon className="w-10 h-10 text-gray-400" />
                </div>
                <h2 className="text-xl font-semibold text-gray-900 mb-2">Không tìm thấy tour</h2>
                <p className="text-gray-500 mb-4">Thử thay đổi bộ lọc để tìm kiếm tour phù hợp</p>
                <button
                  onClick={clearAllFilters}
                  className="px-6 py-3 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-colors"
                >
                  Xóa bộ lọc
                </button>
              </div>
            )}
          </div>
        </section>

        {/* Stats Section */}
        <section className="py-8 px-4 bg-gray-50 border-t border-gray-100">
          <div className="container mx-auto flex justify-center gap-12 text-center">
            <div>
              <p className="text-3xl font-bold text-emerald-600">12+</p>
              <p className="text-sm text-gray-500">Tuyến đường</p>
            </div>
            <div>
              <p className="text-3xl font-bold text-emerald-600">3000+</p>
              <p className="text-sm text-gray-500">Khách hàng</p>
            </div>
            <div>
              <p className="text-3xl font-bold text-emerald-600">4.9</p>
              <p className="text-sm text-gray-500">Đánh giá</p>
            </div>
          </div>
        </section>

        {/* CTA Banner */}
        <section className="py-12 px-4 bg-gradient-to-r from-emerald-600 to-emerald-500">
          <div className="container mx-auto text-center text-white">
            <h2 className="text-2xl lg:text-3xl font-bold mb-4">Cần hỗ trợ đặt tour?</h2>
            <p className="text-emerald-100 mb-6">Liên hệ trực tiếp với chúng tôi qua Zalo để được tư vấn nhanh nhất</p>
            <a
              href="https://zalo.me/0928382087"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-3 px-8 py-4 bg-white text-emerald-600 font-bold rounded-xl hover:bg-emerald-50 transition-colors shadow-lg"
            >
              Chat Zalo ngay
            </a>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}
