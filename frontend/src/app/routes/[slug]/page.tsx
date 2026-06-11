"use client";

import { useState, use, useEffect } from "react";
import Link from "next/link";
import { motion, AnimatePresence } from "framer-motion";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { getTourBySlug, TourDetail } from "@/lib/api";
import { useSettings } from "@/hooks/useSettings";
import { 
  ClockIcon, 
  MapPinIcon, 
  UsersIcon, 
  StarIcon, 
  ShieldCheckIcon,
  CheckIcon,
  CalendarIcon,
  ChevronRightIcon,
  MountainIcon,
  SunIcon,
  FootprintsIcon,
  EyeIcon,
  XIcon,
  ChevronLeftIcon,
  ChevronRightIcon as ChevronRightIconAlt,
  CompassIcon,
  FlameIcon,
} from "@/components/icons";
import { cn, getTourGallery } from "@/lib/utils";

interface PageProps {
  params: Promise<{ slug: string }>;
}

const getItineraryIcon = (activity: string) => {
  const text = activity.toLowerCase();
  if (text.includes("đón") || text.includes("hẹn")) return MapPinIcon;
  if (text.includes("ăn") || text.includes("bbq") || text.includes("sáng") || text.includes("trưa") || text.includes("tối")) return SunIcon;
  if (text.includes("trek") || text.includes("leo") || text.includes("đỉnh") || text.includes("núi")) return MountainIcon;
  if (text.includes("nghỉ") || text.includes("hoàng hôn") || text.includes("lửa trại")) return EyeIcon;
  return FootprintsIcon;
};

// Image Gallery Modal
function ImageGalleryModal({ 
  images, 
  initialIndex, 
  onClose 
}: { 
  images: string[]; 
  initialIndex: number; 
  onClose: () => void;
}) {
  const [currentIndex, setCurrentIndex] = useState(initialIndex);

  const goToPrevious = () => {
    setCurrentIndex((prev) => (prev === 0 ? images.length - 1 : prev - 1));
  };

  const goToNext = () => {
    setCurrentIndex((prev) => (prev === images.length - 1 ? 0 : prev + 1));
  };

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      className="fixed inset-0 z-50 bg-black/95 flex items-center justify-center"
      onClick={onClose}
    >
      {/* Close Button */}
      <button
        className="absolute top-4 right-4 z-10 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
        onClick={(e) => { e.stopPropagation(); onClose(); }}
        aria-label="Đóng thư viện ảnh"
      >
        <XIcon className="w-6 h-6" />
      </button>

      {/* Counter */}
      <div className="absolute top-4 left-1/2 -translate-x-1/2 text-white text-sm font-medium z-10">
        {currentIndex + 1} / {images.length}
      </div>

      {/* Main Image */}
      <div className="relative w-full h-full flex items-center justify-center p-8" onClick={(e) => e.stopPropagation()}>
        <AnimatePresence mode="wait">
          <motion.img
            key={currentIndex}
            src={images[currentIndex]}
            alt={`Image ${currentIndex + 1}`}
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.9 }}
            transition={{ duration: 0.2 }}
            className="max-w-full max-h-full object-contain"
          />
        </AnimatePresence>

        {/* Navigation Arrows */}
        {images.length > 1 && (
          <>
            <button
              onClick={(e) => { e.stopPropagation(); goToPrevious(); }}
              className="absolute left-4 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
              aria-label="Ảnh trước"
            >
              <ChevronLeftIcon className="w-6 h-6" />
            </button>
            <button
              onClick={(e) => { e.stopPropagation(); goToNext(); }}
              className="absolute right-4 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
              aria-label="Ảnh tiếp theo"
            >
              <ChevronRightIconAlt className="w-6 h-6" />
            </button>
          </>
        )}
      </div>

      {/* Thumbnail Strip */}
      <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 p-4 bg-black/50 rounded-xl">
        {images.map((img, index) => (
          <button
            key={index}
            onClick={(e) => { e.stopPropagation(); setCurrentIndex(index); }}
            className={cn(
              "w-16 h-12 rounded-lg overflow-hidden border-2 transition-all",
              currentIndex === index ? "border-white scale-110" : "border-transparent opacity-60 hover:opacity-100"
            )}
          >
            <img src={img} alt={`Thumbnail ${index + 1}`} className="w-full h-full object-cover" />
          </button>
        ))}
      </div>
    </motion.div>
  );
}

export default function TourDetailPage({ params }: PageProps) {
  const { slug } = use(params);
  const { settings } = useSettings();
  const [activeTab, setActiveTab] = useState<"lich-trinh" | "chi-tiet" | "bao-gom">("lich-trinh");
  const [activeImage, setActiveImage] = useState(0);
  const [isGalleryOpen, setIsGalleryOpen] = useState(false);
  const [quantity, setQuantity] = useState(1);
  const [fullName, setFullName] = useState("");
  const [phone, setPhone] = useState("");
  const [email, setEmail] = useState("");

  const [tour, setTour] = useState<TourDetail | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    getTourBySlug(slug)
      .then((tourData) => {
        if (cancelled) return;
        setTour(tourData);
      })
      .catch((err) => {
        console.error("Error loading tour details:", err);
        if (!cancelled) setTour(null);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [slug]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto mb-4" />
          <p className="text-gray-500">Đang tải chi tiết tour...</p>
        </div>
      </div>
    );
  }

  if (!tour) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Tour không tìm thấy</h1>
          <Link href="/booking" className="text-[#16a249] hover:underline">
            ← Quay lại trang đặt tour
          </Link>
        </div>
      </div>
    );
  }

  const difficultyConfig = {
    easy: { label: "Dễ", color: "bg-green-100 text-green-700", desc: "Phù hợp người mới bắt đầu" },
    medium: { label: "Trung bình", color: "bg-yellow-100 text-yellow-700", desc: "Có kinh nghiệm trekking" },
    hard: { label: "Khó", color: "bg-red-100 text-red-700", desc: "Thử thách cao" },
  };
  const difficulty = difficultyConfig[tour.difficulty as keyof typeof difficultyConfig] || difficultyConfig.easy;

  const galleryImages = getTourGallery(
    tour.gallery && tour.gallery.length > 0 ? tour.gallery : []
  );

  // Tour Summary data
  const tourSummary = {
    duration: tour.duration,
    distance: tour.distance || "8-10 km",
    elevation: tour.elevation || "1.200m",
    maxAltitude: tour.max_altitude || "1.500m",
    terrain: tour.terrain || "Rừng, đồi, suối",
    ageMin: tour.age_min || "16+",
    fitness: tour.fitness || "Trung bình",
  };

  // Gear recommendations
  const gearList = tour.gear_list && tour.gear_list.length > 0 
    ? tour.gear_list 
    : [
        { icon: "👟", name: "Giày trekking", important: true },
        { icon: "🎒", name: "Ba lô 20-30L", important: true },
        { icon: "🧴", name: "Kem chống nắng", important: true },
        { icon: "🧢", name: "Mũ/nón", important: true },
        { icon: "👕", name: "Áo thun thoáng khí", important: false },
        { icon: "🩳", name: "Quần dài trekking", important: false },
        { icon: "🔦", name: "Đèn pin/flashlight", important: false },
        { icon: "💧", name: "Bình nước 1.5L", important: true },
      ];

  const itinerary = tour.itinerary || [];
  const includedList = tour.included || [];
  const excludedList = tour.excluded || [];

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      {/* Hero Gallery - Grid Layout */}
      <section className="relative">
        <div className="grid grid-cols-2 lg:grid-cols-4 h-[50vh] lg:h-[60vh]">
          {/* Main Large Image */}
          <div 
            className="col-span-2 row-span-2 relative overflow-hidden cursor-pointer group bg-gray-100"
            onClick={() => setIsGalleryOpen(true)}
          >
            <img
              src={galleryImages[0]}
              alt={tour.name}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              onError={(e) => {
                const target = e.target as HTMLImageElement;
                target.onerror = null;
                target.src = '/images/default-tour.jpg';
              }}
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />
            
            {/* View All Button */}
            <button 
              onClick={(e) => { e.stopPropagation(); setIsGalleryOpen(true); }}
              className="absolute bottom-4 right-4 px-4 py-2 bg-black/50 backdrop-blur-sm text-white text-sm font-medium rounded-lg hover:bg-black/70 transition-colors flex items-center gap-2"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Xem tất cả {galleryImages.length} ảnh
            </button>
          </div>
          
          {/* Side Images */}
          {galleryImages.slice(1, 5).map((img, index) => (
            <div 
              key={index}
              className="relative overflow-hidden cursor-pointer group bg-gray-100"
              onClick={() => { setActiveImage(index + 1); setIsGalleryOpen(true); }}
            >
              <img
                src={img}
                alt={`${tour.name} ${index + 2}`}
                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                onError={(e) => {
                  const target = e.target as HTMLImageElement;
                  target.onerror = null;
                  target.src = '/images/default-tour.jpg';
                }}
              />
              {/* Overlay on last visible image if more than 4 */}
              {index === 2 && galleryImages.length > 4 && (
                <div className="absolute inset-0 bg-black/50 flex items-center justify-center">
                  <span className="text-white font-bold text-lg">+{galleryImages.length - 4}</span>
                </div>
              )}
            </div>
          ))}
        </div>
      </section>

      {/* Tour Info Bar */}
      <section className="bg-white shadow-md sticky top-[81px] z-40 border-b border-gray-200">
        <div className="container mx-auto px-4">
          <div className="flex flex-wrap items-center justify-between gap-4 py-3 text-sm">
            <div className="flex items-center gap-2">
              <Link href="/booking" className="text-gray-500 hover:text-emerald-700 flex items-center gap-1 font-medium">
                Tour <ChevronRightIcon className="w-4 h-4" />
              </Link>
              <span className="font-bold text-gray-900 max-w-[120px] sm:max-w-xs truncate">{tour.name}</span>
              
              {/* Desktop Sub Navigation tabs */}
              <div className="hidden md:flex items-center gap-5 ml-6 border-l border-gray-200 pl-6">
                <button
                  onClick={() => document.getElementById("tong-quan")?.scrollIntoView({ behavior: "smooth" })}
                  className="text-gray-500 hover:text-emerald-700 font-semibold cursor-pointer transition-colors"
                >
                  Tổng quan
                </button>
                <button
                  onClick={() => {
                    setActiveTab("lich-trinh");
                    document.getElementById("tabs-section")?.scrollIntoView({ behavior: "smooth" });
                  }}
                  className={cn(
                    "font-semibold cursor-pointer transition-colors",
                    activeTab === "lich-trinh" ? "text-emerald-700 font-bold" : "text-gray-500 hover:text-emerald-700"
                  )}
                >
                  Lịch trình
                </button>
                <button
                  onClick={() => {
                    setActiveTab("chi-tiet");
                    document.getElementById("tabs-section")?.scrollIntoView({ behavior: "smooth" });
                  }}
                  className={cn(
                    "font-semibold cursor-pointer transition-colors",
                    activeTab === "chi-tiet" ? "text-emerald-700 font-bold" : "text-gray-500 hover:text-emerald-700"
                  )}
                >
                  Chi tiết
                </button>
                <button
                  onClick={() => document.getElementById("danh-gia")?.scrollIntoView({ behavior: "smooth" })}
                  className="text-gray-500 hover:text-emerald-700 font-semibold cursor-pointer transition-colors"
                >
                  Đánh giá
                </button>
              </div>
            </div>

            <div className="flex items-center gap-4">
              <div className="hidden sm:flex items-center gap-1 text-[#16a249]">
                <StarIcon className="w-4 h-4 fill-current" />
                <span className="font-bold text-emerald-800">4.9</span>
                <span className="text-gray-500 text-xs">(128)</span>
              </div>
              <div className="flex items-center gap-1.5 text-gray-600 bg-gray-50 px-3 py-1 rounded-full border border-gray-100">
                <UsersIcon className="w-3.5 h-3.5 text-emerald-600" />
                <span className="text-xs font-medium">{tour.available_spots} chỗ trống</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <div className="container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Left Content */}
          <div id="tong-quan" className="lg:col-span-2 space-y-8 scroll-mt-[150px]">
            {/* Title & Description */}
            <div>
              <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{tour.name}</h1>
              <p className="text-gray-600 leading-relaxed text-lg mb-6">{tour.description}</p>
              
              {/* Quick Stats */}
              <div className="flex flex-wrap gap-3">
                <span className={cn("inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium", difficulty.color)}>
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  {difficulty.label}
                </span>
                <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                  <ClockIcon className="w-4 h-4" />
                  {tour.duration}
                </span>
                <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                  <MapPinIcon className="w-4 h-4" />
                  {tour.departure_times?.[0] || "Sáng"}
                </span>
              </div>
            </div>

            {/* Tour Summary Card */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="bg-gradient-to-r from-[#16a249] to-[#10b981] rounded-2xl p-6 text-white"
            >
              <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Tour Summary
              </h2>
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                {[
                  { label: "Thời gian", value: tourSummary.duration, icon: ClockIcon },
                  { label: "Quãng đường", value: tourSummary.distance, icon: FootprintsIcon },
                  { label: "Độ cao", value: tourSummary.elevation, icon: MountainIcon },
                  { label: "Độ cao MAX", value: tourSummary.maxAltitude, icon: ChevronRightIcon },
                  { label: "Địa hình", value: tourSummary.terrain, icon: CompassIcon },
                  { label: "Độ tuổi", value: tourSummary.ageMin, icon: UsersIcon },
                  { label: "Thể lực", value: tourSummary.fitness, icon: FlameIcon },
                ].map((item, index) => (
                  <div key={index} className="bg-white/10 rounded-xl p-4 text-center flex flex-col justify-between min-h-[110px]">
                    <item.icon className="w-5 h-5 mx-auto mb-1.5 opacity-80 flex-shrink-0" />
                    <div className="flex-grow flex items-center justify-center">
                      <p className="text-base sm:text-lg font-bold leading-snug">{item.value}</p>
                    </div>
                    <p className="text-xs opacity-75 mt-1.5 uppercase tracking-wider font-semibold">{item.label}</p>
                  </div>
                ))}
              </div>
            </motion.div>

            {/* Highlights */}
            <div className="bg-white rounded-2xl p-6 shadow-sm">
              <h2 className="text-xl font-bold text-gray-900 mb-4">Điểm nổi bật</h2>
              {tour.highlights && tour.highlights.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  {tour.highlights.map((highlight, index) => (
                    <motion.div
                      key={index}
                      initial={{ opacity: 0, y: 20 }}
                      whileInView={{ opacity: 1, y: 0 }}
                      viewport={{ once: true }}
                      transition={{ delay: index * 0.1 }}
                      className="flex items-center gap-3 p-4 bg-gradient-to-r from-[#16a249]/5 to-transparent rounded-xl border border-gray-100"
                    >
                      <div className="w-10 h-10 rounded-full bg-[#16a249] flex items-center justify-center flex-shrink-0">
                        <CheckIcon className="w-5 h-5 text-white" />
                      </div>
                      <span className="font-medium text-gray-800">{highlight}</span>
                    </motion.div>
                  ))}
                </div>
              ) : (
                <p className="text-gray-500">Thông tin điểm nổi bật đang được cập nhật.</p>
              )}
            </div>

            {/* Tabs Section */}
            <div id="tabs-section" className="scroll-mt-[150px] bg-white rounded-2xl shadow-sm overflow-hidden">
              <div className="flex border-b">
                {[
                  { id: "lich-trinh", label: "Lịch trình" },
                  { id: "chi-tiet", label: "Chi tiết tour" },
                  { id: "bao-gom", label: "Bao gồm" },
                ].map((tab) => (
                  <button
                    key={tab.id}
                    onClick={() => setActiveTab(tab.id as typeof activeTab)}
                    className={cn(
                      "flex-1 py-4 px-6 text-center font-medium transition-colors relative",
                      activeTab === tab.id
                        ? "text-[#16a249] bg-[#16a249]/5"
                        : "text-gray-500 hover:text-gray-700"
                    )}
                  >
                    {tab.label}
                    {activeTab === tab.id && (
                      <motion.div
                        layoutId="activeTab"
                        className="absolute bottom-0 left-0 right-0 h-0.5 bg-[#16a249]"
                      />
                    )}
                  </button>
                ))}
              </div>

              <div className="p-6">
                {activeTab === "lich-trinh" && (
                  <div className="space-y-6">
                    {itinerary.length > 0 ? (
                      itinerary.map((item, index) => {
                        const IconComponent = getItineraryIcon(item.activity);
                        return (
                          <motion.div
                            key={index}
                            initial={{ opacity: 0, x: -20 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: index * 0.05 }}
                            className="flex gap-4"
                          >
                            {/* Timeline */}
                            <div className="flex flex-col items-center">
                              <div className={cn(
                                "w-12 h-12 rounded-2xl flex items-center justify-center",
                                "bg-gradient-to-br from-[#16a249] to-[#10b981] text-white shadow-lg"
                              )}>
                                <IconComponent className="w-5 h-5" />
                              </div>
                              {index < itinerary.length - 1 && (
                                <div className="w-0.5 flex-1 bg-gray-200 my-2 min-h-[40px]" />
                              )}
                            </div>
                            
                            {/* Content */}
                            <div className="flex-1 pb-6">
                              <div className="flex items-center gap-3 mb-2">
                                <span className="text-sm font-semibold text-[#16a249] bg-[#16a249]/10 px-3 py-1 rounded-full">
                                  {item.time}
                                </span>
                              </div>
                              <h4 className="text-lg font-bold text-gray-900 mb-2">{item.activity}</h4>
                            </div>
                          </motion.div>
                        );
                      })
                    ) : (
                      <p className="text-gray-500 text-center py-6">Chưa có lịch trình chi tiết.</p>
                    )}
                  </div>
                )}

                {activeTab === "chi-tiet" && (
                  <div className="space-y-6">
                    {/* About Tour */}
                    <div>
                      <h3 className="text-lg font-bold text-gray-900 mb-3">Giới thiệu</h3>
                      {tour.content ? (
                        <div 
                          className="prose max-w-none text-gray-600 leading-relaxed"
                          dangerouslySetInnerHTML={{ __html: tour.content }}
                        />
                      ) : (
                        <>
                          <p className="text-gray-600 leading-relaxed mb-4">
                            {tour.description}
                          </p>
                          <p className="text-gray-600 leading-relaxed">
                            Hành trình mang đến cho bạn trải nghiệm tuyệt vời với thiên nhiên hoang sơ, 
                            không khí trong lành và những khung cảnh đẹp mê lòng người. 
                            Đội ngũ hướng dẫn viên giàu kinh nghiệm sẽ đồng hành cùng bạn trong suốt chuyến đi, 
                            đảm bảo an toàn và mang đến những khoảnh khắc đáng nhớ nhất.
                          </p>
                        </>
                      )}
                    </div>

                    {/* Difficulty Guide */}
                    <div className="bg-gray-50 rounded-xl p-6">
                      <h3 className="text-lg font-bold text-gray-900 mb-4">Mức độ: {difficulty.label}</h3>
                      <p className="text-gray-600 mb-3">{difficulty.desc}</p>
                      <div className="flex items-center gap-2">
                        <span className="text-sm text-gray-500">Độ khó:</span>
                        <div className="flex gap-1">
                          {['easy', 'medium', 'hard'].map((level, index) => (
                            <div
                              key={level}
                              className={cn(
                                "w-8 h-2 rounded-full",
                                tour.difficulty === level ? "bg-[#16a249]" :
                                (tour.difficulty === 'easy' && index === 0) ? "bg-[#16a249]" :
                                (tour.difficulty === 'medium' && index <= 1) ? "bg-[#16a249]" :
                                (tour.difficulty === 'hard' && index <= 2) ? "bg-[#16a249]" : "bg-gray-200"
                              )}
                            />
                          ))}
                        </div>
                      </div>
                    </div>

                    {/* Gear List */}
                    <div>
                      <h3 className="text-lg font-bold text-gray-900 mb-4">Trang bị khuyến nghị</h3>
                      <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                        {gearList.map((gear, index) => (
                          <div
                            key={index}
                            className={cn(
                              "flex items-center gap-3 p-3 rounded-xl border",
                              gear.important ? "border-[#16a249] bg-[#16a249]/5" : "border-gray-200"
                            )}
                          >
                            <span className="text-2xl">{gear.icon}</span>
                            <span className="text-sm font-medium text-gray-700">{gear.name}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                {activeTab === "bao-gom" && (
                  <div className="space-y-6">
                    {/* Included */}
                    <div>
                      <h3 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <CheckIcon className="w-5 h-5 text-[#16a249]" />
                        Đã bao gồm
                      </h3>
                      {includedList.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                          {includedList.map((item, index) => (
                            <motion.div
                              key={index}
                              initial={{ opacity: 0 }}
                              whileInView={{ opacity: 1 }}
                              viewport={{ once: true }}
                              transition={{ delay: index * 0.05 }}
                              className="flex items-center gap-3 p-3 bg-green-50 rounded-xl"
                            >
                              <CheckIcon className="w-5 h-5 text-[#16a249] flex-shrink-0" />
                              <span className="text-gray-700">{item}</span>
                            </motion.div>
                          ))}
                        </div>
                      ) : (
                        <p className="text-gray-500">Chi tiết dịch vụ đã bao gồm đang được cập nhật.</p>
                      )}
                    </div>

                    {/* Not Included */}
                    <div>
                      <h3 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Không bao gồm
                      </h3>
                      {excludedList.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                          {excludedList.map((item, index) => (
                            <div
                              key={index}
                              className="flex items-center gap-3 p-3 bg-gray-50 rounded-xl"
                            >
                              <div className="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center flex-shrink-0">
                                <svg className="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                              </div>
                              <span className="text-gray-500">{item}</span>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <p className="text-gray-500">Chi tiết dịch vụ không bao gồm đang được cập nhật.</p>
                      )}
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Reviews */}
            <div id="danh-gia" className="scroll-mt-[150px] bg-white rounded-2xl p-6 shadow-sm">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-900">Đánh giá từ khách hàng</h2>
                <div className="flex items-center gap-1">
                  <StarIcon className="w-5 h-5 text-yellow-400 fill-current" />
                  <span className="font-semibold">4.9</span>
                  <span className="text-gray-500 text-sm">(128 đánh giá)</span>
                </div>
              </div>
              <div className="space-y-4">
                {[
                  { name: "Minh Tuấn", avatar: "MT", rating: 5, date: "15/01/2026", comment: "Tour rất tuyệt vời! Hướng dẫn viên nhiệt tình, cảnh đẹp không thể tả. Đỉnh view 360° siêu đẹp. Sẽ quay lại!" },
                  { name: "Hồng Anh", avatar: "HA", rating: 5, date: "10/01/2026", comment: "Trải nghiệm đáng nhớ. Đường đi an toàn, view đẹp, team building rất vui. Ăn uống ngon." },
                  { name: "Việt Hùng", avatar: "VH", rating: 4, date: "05/01/2026", comment: "Tour tốt, HDV chu đáo. Địa hình vừa phải, phù hợp với người mới tập trekking." },
                ].map((review, index) => (
                  <motion.div
                    key={index}
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true }}
                    transition={{ delay: index * 0.1 }}
                    className="p-4 bg-gray-50 rounded-xl"
                  >
                    <div className="flex items-center gap-3 mb-2">
                      <div className="w-10 h-10 rounded-full bg-[#16a249] text-white flex items-center justify-center font-semibold">
                        {review.avatar}
                      </div>
                      <div className="flex-1">
                        <div className="flex items-center justify-between">
                          <p className="font-medium text-gray-900">{review.name}</p>
                          <span className="text-xs text-gray-400">{review.date}</span>
                        </div>
                        <div className="flex gap-0.5">
                          {Array.from({ length: 5 }).map((_, i) => (
                            <StarIcon key={i} className={cn("w-4 h-4", i < review.rating ? "text-yellow-400 fill-current" : "text-gray-300")} />
                          ))}
                        </div>
                      </div>
                    </div>
                    <p className="text-gray-600 text-sm">{review.comment}</p>
                  </motion.div>
                ))}
              </div>
            </div>
          </div>

          {/* Right Sidebar - Booking */}
          <div className="lg:col-span-1">
            <div className="sticky top-[140px] space-y-6">
              {/* Price Card */}
              <motion.div
                whileHover={{ y: -4 }}
                className="bg-white rounded-2xl p-6 shadow-lg border border-gray-100"
              >
                <div className="mb-6">
                  <span className="text-3xl font-bold text-[#16a249]">
                    {(tour.price * quantity).toLocaleString("vi-VN")}
                  </span>
                  <span className="text-gray-500">/ {quantity > 1 ? `${quantity} khách` : 'người'}</span>
                </div>

                <div className="space-y-3 mb-6">
                  <div className="flex items-center gap-3 text-gray-600">
                    <CalendarIcon className="w-5 h-5 text-[#16a249]" />
                    <span>Khởi hành: {tour.departure_times?.[0] || "Sáng"}</span>
                  </div>
                  <div className="flex items-center gap-3 text-gray-600">
                    <UsersIcon className="w-5 h-5 text-[#16a249]" />
                    <span>Còn {tour.available_spots} chỗ trống</span>
                  </div>
                  <div className="flex items-center gap-3 text-gray-600">
                    <ClockIcon className="w-5 h-5 text-[#16a249]" />
                    <span>Thời gian: {tour.duration}</span>
                  </div>
                </div>

                <div className="space-y-3">
                  <input
                    type="text"
                    placeholder="Họ và tên *"
                    value={fullName}
                    onChange={(e) => setFullName(e.target.value)}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent"
                  />
                  <input
                    type="number"
                    placeholder="Số lượng (1-10)"
                    min="1"
                    max={Math.min(tour.available_spots || 10, 10)}
                    value={quantity}
                    onChange={(e) => setQuantity(Math.max(1, parseInt(e.target.value) || 1))}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent"
                  />
                  <input
                    type="tel"
                    placeholder="Số điện thoại *"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent"
                  />
                  <input
                    type="email"
                    placeholder="Email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent"
                  />
                </div>

                <Link href={`/booking/${slug}?slots=${quantity}&phone=${encodeURIComponent(phone)}&email=${encodeURIComponent(email)}&name=${encodeURIComponent(fullName)}`}>
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    className="w-full mt-6 py-4 bg-[#16a249] text-white font-bold rounded-xl hover:bg-[#0d7a3a] transition-colors shadow-lg shadow-[#16a249]/30"
                  >
                    Đặt tour ngay
                  </motion.button>
                </Link>

                <div className="flex items-center gap-2 mt-4 text-sm text-gray-500 justify-center">
                  <ShieldCheckIcon className="w-4 h-4 text-[#16a249]" />
                  <span>Thanh toán an toàn 100%</span>
                </div>
              </motion.div>

              {/* Contact Card */}
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.2 }}
                className="bg-gradient-to-br from-[#16a249] to-[#0d7a3a] rounded-2xl p-6 text-white"
              >
                <h3 className="font-bold mb-2">Cần hỗ trợ?</h3>
                <p className="text-white/80 text-sm mb-4">Liên hệ trực tiếp với chúng tôi để được tư vấn miễn phí 24/7</p>
                <div className="flex flex-col gap-2">
                  <a href={`tel:${settings?.hotline ? settings.hotline.replace(/\s+/g, "") : "0961804359"}`} className="flex items-center justify-center gap-2 py-3 bg-white text-emerald-800 font-bold rounded-xl hover:bg-emerald-50 transition-colors shadow-sm">
                    <svg className="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Gọi {settings?.hotline || "096 180 43 59"}
                  </a>
                  <a href={settings?.zalo_link || "https://zalo.me/0961804359"} target="_blank" rel="noopener noreferrer" className="flex items-center justify-center gap-2 py-3 bg-[#0068ff] hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-sm">
                    <span className="font-extrabold text-lg">Z</span>
                    Nhắn Zalo: {settings?.hotline || "096 180 43 59"}
                  </a>
                </div>
              </motion.div>

              {/* Share Card */}
              <div className="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p className="text-sm text-gray-500 mb-3">Chia sẻ tour</p>
                <div className="flex gap-3">
                  <button className="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center hover:bg-blue-600 transition-colors" aria-label="Chia sẻ qua Facebook">
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                  </button>
                  <button className="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition-colors" aria-label="Chia sẻ qua Zalo">
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                  </button>
                  <button className="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition-colors" aria-label="Chia sẻ qua Twitter">
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Image Gallery Modal */}
      <AnimatePresence>
        {isGalleryOpen && (
          <ImageGalleryModal
            images={galleryImages}
            initialIndex={activeImage}
            onClose={() => setIsGalleryOpen(false)}
          />
        )}
      </AnimatePresence>

      <Footer />
    </div>
  );
}