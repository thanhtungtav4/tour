"use client";

import { useState, use } from "react";
import Link from "next/link";
import { motion, AnimatePresence } from "framer-motion";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { tours, getTourBySlug } from "@/data/tours";
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
} from "@/components/icons";
import { cn } from "@/lib/utils";

interface PageProps {
  params: Promise<{ slug: string }>;
}

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
            >
              <ChevronLeftIcon className="w-6 h-6" />
            </button>
            <button
              onClick={(e) => { e.stopPropagation(); goToNext(); }}
              className="absolute right-4 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
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
  const [activeTab, setActiveTab] = useState<"lich-trinh" | "chi-tiet" | "bao-gom">("lich-trinh");
  const [activeImage, setActiveImage] = useState(0);
  const [isGalleryOpen, setIsGalleryOpen] = useState(false);
  
  const tour = getTourBySlug(slug);

  if (!tour) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Tour không tìm thấy</h1>
          <Link href="/routes" className="text-[#16a249] hover:underline">
            ← Quay lại danh sách tour
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
  const difficulty = difficultyConfig[tour.difficulty as keyof typeof difficultyConfig];

  // Gallery images from tour data (always 5 images)
  const galleryImages = tour.gallery;

  // Tour Summary data
  const tourSummary = {
    duration: tour.duration,
    distance: "8-10 km",
    elevation: "1.200m",
    maxAltitude: "1.500m",
    terrain: "Rừng, đồi, suối",
    ageMin: "16+",
    fitness: "Trung bình",
  };

  // Detailed schedule
  const schedule = [
    {
      time: "05:30 - 06:00",
      title: "Đón khách tại điểm hẹn",
      description: "Xe đưa đón tận nơi trong khu vực TP.HCM và các tỉnh lân cận. Check-in và nhận dụng cụ.",
      icon: MapPinIcon,
    },
    {
      time: "07:00 - 09:00",
      title: "Di chuyển đến điểm xuất phát",
      description: "Trên đường đi, hướng dẫn viên sẽ giới thiệu về tour, những lưu ý an toàn và phổ biến kỹ năng trekking cơ bản.",
      icon: MountainIcon,
    },
    {
      time: "09:00 - 09:30",
      title: "Khởi động & Brief về an toàn",
      description: "Thực hiện các bài khởi động, kiểm tra trang bị và phổ biến quy tắc an toàn trên trail.",
      icon: ShieldCheckIcon,
    },
    {
      time: "09:30 - 12:00",
      title: "Bắt đầu Trekking - Đoạn 1",
      description: "Đoạn đường đầu tiên đi qua rừng thông, dốc nhẹ. Cảnh quan bắt đầu mở ra với tầm view bao quát.",
      subActivities: [
        "Đoạn đường: 3km",
        "Độ cao tăng: +400m",
        "Nghỉ 2 lần, mỗi lần 10 phút",
      ],
      icon: FootprintsIcon,
    },
    {
      time: "12:00 - 13:00",
      title: "Nghỉ trưa & Bữa trưa",
      description: "Nghỉ ngơi tại điểm có bóng mát, thưởng thức bữa trưa với các món ăn địa phương. Thời gian tự do chụp ảnh.",
      subActivities: [
        "Bữa trưa: Cơm rang, mì xào, đồ uống",
        "Thời gian nghỉ: 60 phút",
      ],
      icon: SunIcon,
    },
    {
      time: "13:00 - 15:30",
      title: "Trekking - Đoạn 2 (Đỉnh)",
      description: "Đoạn cuối leo lên đỉnh. Địa hình dốc hơn nhưng tầm view tuyệt đẹp. Đạt đỉnh và chụp ảnh kỷ niệm.",
      subActivities: [
        "Đoạn đường: 4km",
        "Độ cao tăng: +600m",
        "Đạt đỉnh: Checkpoint 1.500m",
      ],
      icon: MountainIcon,
    },
    {
      time: "15:30 - 16:00",
      title: "Đạt đỉnh - Ngắm cảnh",
      description: "Thời gian nghỉ ngơi tại đỉnh, ngắm toàn cảnh panorama 360°. Chụp ảnh, chia sẻ trải nghiệm.",
      subActivities: [
        "Thời gian: 30 phút",
        "Độ cao đỉnh: 1.500m",
      ],
      icon: EyeIcon,
    },
    {
      time: "16:00 - 18:00",
      title: "Xuống núi",
      description: "Quay trở lại điểm xuất phát theo đường cũ. Đường xuống dốc hơn, cần chú ý an toàn.",
      subActivities: [
        "Đoạn đường: 5km",
        "Độ cao giảm: -1.000m",
        "Thời gian: ~2 tiếng",
      ],
      icon: FootprintsIcon,
    },
    {
      time: "18:00 - 20:00",
      title: "Về đến TP.HCM",
      description: "Kết thúc chuyến đi, tiễn khách tại điểm đón. Hẹn gặp lại trong chuyến đi tiếp theo!",
      icon: CalendarIcon,
    },
  ];

  // What's included
  const included = {
    included: [
      "Xe đưa đón khứ hồi (TP.HCM)",
      "Bữa trưa tại trail",
      "Nước uống (2 chai/person)",
      "Hướng dẫn viên chuyên nghiệp (2 người)",
      "Bảo hiểm du lịch",
      "Dụng cụ an toàn (nón, găng tay)",
      "Áo phông Đôi Dép Adventure",
      "Khăn đa năng",
    ],
    notIncluded: [
      "Thuốc men personal",
      "Đồ ăn vặt cá nhân",
      "Tip cho HDV (tùy ý)",
      "Chi phí cá nhân khác",
    ],
  };

  // Gear recommendations
  const gearList = [
    { icon: "👟", name: "Giày trekking", important: true },
    { icon: "🎒", name: "Ba lô 20-30L", important: true },
    { icon: "🧴", name: "Kem chống nắng", important: true },
    { icon: "🧢", name: "Mũ/nón", important: true },
    { icon: "👕", name: "Áo thun thoáng khí", important: false },
    { icon: "🩳", name: "Quần dài trekking", important: false },
    { icon: "🔦", name: "Đèn pin/flashlight", important: false },
    { icon: "💧", name: "Bình nước 1.5L", important: true },
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      {/* Hero Gallery - Optimized Grid */}
      <section className="relative">
        <div className="flex flex-col lg:flex-row">
          {/* Main Large Image */}
          <div 
            className="relative w-full lg:w-2/3 aspect-[16/10] lg:aspect-auto lg:h-[60vh] overflow-hidden cursor-pointer group bg-gray-100"
            onClick={() => setIsGalleryOpen(true)}
          >
            <img
              src={galleryImages[0]}
              alt={tour.name}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              onError={(e) => {
                const target = e.target as HTMLImageElement;
                target.style.background = 'linear-gradient(135deg, #16a249 0%, #10b981 100%)';
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
          
          {/* Side Images Stack */}
          <div className="w-full lg:w-1/3 flex flex-row lg:flex-col">
            {galleryImages.slice(1, 5).map((img, index) => (
              <div 
                key={index}
                className="relative w-1/2 lg:w-full aspect-[4/3] lg:aspect-auto lg:h-1/4 overflow-hidden cursor-pointer group bg-gray-100"
                onClick={() => { setActiveImage(index + 1); setIsGalleryOpen(true); }}
              >
                <img
                  src={img}
                  alt={`${tour.name} ${index + 2}`}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  onError={(e) => {
                    const target = e.target as HTMLImageElement;
                    target.style.background = 'linear-gradient(135deg, #16a249 0%, #10b981 100%)';
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
        </div>
      </section>

      {/* Tour Info Bar */}
      <section className="bg-white shadow-md sticky top-[81px] z-40 border-b border-gray-200">
        <div className="container mx-auto px-4">
          <div className="flex flex-wrap items-center gap-4 py-4 text-sm">
            <Link href="/routes" className="text-gray-500 hover:text-gray-700 flex items-center gap-1">
              Tour <ChevronRightIcon className="w-4 h-4" />
            </Link>
            <span className="font-medium text-gray-900">{tour.name}</span>
            <div className="flex items-center gap-4 ml-auto">
              <div className="flex items-center gap-1 text-[#16a249]">
                <StarIcon className="w-4 h-4 fill-current" />
                <span className="font-semibold">4.9</span>
                <span className="text-gray-500">(128 đánh giá)</span>
              </div>
              <div className="flex items-center gap-1 text-gray-600">
                <UsersIcon className="w-4 h-4" />
                <span>{tour.availableSpots} chỗ trống</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <div className="container mx-auto px-4 py-8">
        <div className="grid lg:grid-cols-3 gap-8">
          {/* Left Content */}
          <div className="lg:col-span-2 space-y-6">
            
            {/* Title & Quick Info */}
            <div>
              <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                {tour.name}
              </h1>
              <p className="text-lg text-gray-600 mb-6">
                {tour.description}
              </p>
              
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
                  {tour.departureTime}
                </span>
              </div>
            </div>

            {/* Tour Summary Card */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="bg-gradient-to-r from-[#16a249] to-[#10b981] rounded-2xl p-6 text-white"
            >
              <h3 className="text-xl font-bold mb-4 flex items-center gap-2">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Tour Summary
              </h3>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {[
                  { label: "Thời gian", value: tourSummary.duration, icon: ClockIcon },
                  { label: "Quãng đường", value: tourSummary.distance, icon: FootprintsIcon },
                  { label: "Độ cao", value: tourSummary.elevation, icon: MountainIcon },
                  { label: "Độ cao MAX", value: tourSummary.maxAltitude, icon: ChevronRightIcon },
                ].map((item, index) => (
                  <div key={index} className="bg-white/10 rounded-xl p-4 text-center">
                    <item.icon className="w-5 h-5 mx-auto mb-2 opacity-80" />
                    <p className="text-2xl font-bold">{item.value}</p>
                    <p className="text-sm opacity-80">{item.label}</p>
                  </div>
                ))}
              </div>
            </motion.div>

            {/* Highlights */}
            <div className="bg-white rounded-2xl p-6 shadow-sm">
              <h3 className="text-xl font-bold text-gray-900 mb-4">Điểm nổi bật</h3>
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
                    <div className="w-10 h-10 rounded-full bg-[#16a249] flex items-center justify-center">
                      <CheckIcon className="w-5 h-5 text-white" />
                    </div>
                    <span className="font-medium text-gray-800">{highlight}</span>
                  </motion.div>
                ))}
              </div>
            </div>

            {/* Tabs Section */}
            <div className="bg-white rounded-2xl shadow-sm overflow-hidden">
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
                    {schedule.map((item, index) => (
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
                            <item.icon className="w-5 h-5" />
                          </div>
                          {index < schedule.length - 1 && (
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
                          <h4 className="text-lg font-bold text-gray-900 mb-2">{item.title}</h4>
                          <p className="text-gray-600 mb-3">{item.description}</p>
                          
                          {/* Sub Activities */}
                          {item.subActivities && (
                            <div className="bg-gray-50 rounded-xl p-4 space-y-2">
                              {item.subActivities.map((sub, subIndex) => (
                                <div key={subIndex} className="flex items-center gap-2 text-sm text-gray-600">
                                  <div className="w-1.5 h-1.5 rounded-full bg-[#16a249]" />
                                  <span>{sub}</span>
                                </div>
                              ))}
                            </div>
                          )}
                        </div>
                      </motion.div>
                    ))}
                  </div>
                )}

                {activeTab === "chi-tiet" && (
                  <div className="space-y-6">
                    {/* About Tour */}
                    <div>
                      <h4 className="text-lg font-bold text-gray-900 mb-3">Giới thiệu</h4>
                      <p className="text-gray-600 leading-relaxed mb-4">
                        {tour.description}
                      </p>
                      <p className="text-gray-600 leading-relaxed">
                        Hành trình mang đến cho bạn trải nghiệm tuyệt vời với thiên nhiên hoang sơ, 
                        không khí trong lành và những khung cảnh đẹp mê lòng người. 
                        Đội ngũ hướng dẫn viên giàu kinh nghiệm sẽ đồng hành cùng bạn trong suốt chuyến đi, 
                        đảm bảo an toàn và mang đến những khoảnh khắc đáng nhớ nhất.
                      </p>
                    </div>

                    {/* Difficulty Guide */}
                    <div className="bg-gray-50 rounded-xl p-6">
                      <h4 className="text-lg font-bold text-gray-900 mb-4">Mức độ: {difficulty.label}</h4>
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
                      <h4 className="text-lg font-bold text-gray-900 mb-4">Trang bị khuyến nghị</h4>
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
                      <h4 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <CheckIcon className="w-5 h-5 text-[#16a249]" />
                        Đã bao gồm
                      </h4>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {included.included.map((item, index) => (
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
                    </div>

                    {/* Not Included */}
                    <div>
                      <h4 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Không bao gồm
                      </h4>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {included.notIncluded.map((item, index) => (
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
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Reviews */}
            <div className="bg-white rounded-2xl p-6 shadow-sm">
              <div className="flex items-center justify-between mb-6">
                <h3 className="text-xl font-bold text-gray-900">Đánh giá từ khách hàng</h3>
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
                    {tour.price.toLocaleString("vi-VN")}
                  </span>
                  <span className="text-gray-500">/người</span>
                </div>

                <div className="space-y-3 mb-6">
                  <div className="flex items-center gap-3 text-gray-600">
                    <CalendarIcon className="w-5 h-5 text-[#16a249]" />
                    <span>Khởi hành: {tour.departureTime}</span>
                  </div>
                  <div className="flex items-center gap-3 text-gray-600">
                    <UsersIcon className="w-5 h-5 text-[#16a249]" />
                    <span>Còn {tour.availableSpots} chỗ trống</span>
                  </div>
                  <div className="flex items-center gap-3 text-gray-600">
                    <ClockIcon className="w-5 h-5 text-[#16a249]" />
                    <span>Thời gian: {tour.duration}</span>
                  </div>
                </div>

                <div className="space-y-3">
                  <input
                    type="number"
                    placeholder="Số lượng (1-10)"
                    min="1"
                    max={Math.min(tour.availableSpots, 10)}
                    defaultValue="1"
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent"
                  />
                  <input
                    type="tel"
                    placeholder="Số điện thoại *"
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent"
                  />
                  <input
                    type="email"
                    placeholder="Email"
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16a249] focus:border-transparent"
                  />
                </div>

                <Link href={`/booking/${slug}`}>
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
                <h4 className="font-bold mb-2">Cần hỗ trợ?</h4>
                <p className="text-white/80 text-sm mb-4">Liên hệ trực tiếp với chúng tôi</p>
                <a href="tel:0909123456" className="flex items-center gap-2 text-xl font-bold hover:underline">
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  0909 123 456
                </a>
                <p className="text-white/60 text-sm mt-2">Zalo: 0909 123 456</p>
              </motion.div>

              {/* Share Card */}
              <div className="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p className="text-sm text-gray-500 mb-3">Chia sẻ tour</p>
                <div className="flex gap-3">
                  <button className="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center hover:bg-blue-600 transition-colors">
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                  </button>
                  <button className="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition-colors">
                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                  </button>
                  <button className="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition-colors">
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