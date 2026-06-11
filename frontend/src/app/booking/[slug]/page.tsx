"use client";

import { useState, use, useEffect } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { getTourBySlug, getRentalItems, createBooking, uploadFile, TourDetail, RentalItem } from "@/lib/api";
import { getTourImage } from "@/lib/utils";
import {
  CalendarIcon,
  UsersIcon,
  ClockIcon,
  StarIcon,
  ChevronRightIcon,
  CheckIcon,
  PhoneIcon,
  MailIcon,
  ArrowLeftIcon,
  SparklesIcon,
} from "@/components/icons";
import { cn } from "@/lib/utils";
import { useSettings } from "@/hooks/useSettings";

const formatDateToVN = (dateStr: string) => {
  if (!dateStr) return "";
  const parts = dateStr.split("-");
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return dateStr;
};

interface PageProps {
  params: Promise<{ slug: string }>;
}

const STEPS = [
  { id: 1, label: "Chọn ngày", icon: CalendarIcon },
  { id: 2, label: "Thông tin", icon: UsersIcon },
  { id: 3, label: "Dịch vụ", icon: SparklesIcon },
  { id: 4, label: "Thanh toán", icon: CheckIcon },
];

export default function BookingTourPage({ params }: PageProps) {
  const { settings } = useSettings();
  const { slug } = use(params);
  const router = useRouter();
  const [currentStep, setCurrentStep] = useState(1);
  const [tour, setTour] = useState<TourDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [rentalItems, setRentalItems] = useState<RentalItem[]>([]);

  useEffect(() => {
    let cancelled = false;

    Promise.all([
      getTourBySlug(slug),
      getRentalItems(),
    ]).then(([tourData, rentalData]) => {
      if (cancelled) return;
      setTour(tourData);
      setRentalItems(rentalData);
    }).catch(() => {
      if (!cancelled) setTour(null);
    }).finally(() => {
      if (!cancelled) setLoading(false);
    });

    return () => {
      cancelled = true;
    };
  }, [slug]);

  // Form state
  const [formData, setFormData] = useState({
    departureDate: "",
    participants: 1,
    selectedServices: [] as string[],
    rentalItems: {} as Record<string, number>,
    fullName: "",
    phone: "",
    email: "",
    idNumber: "",
    birthDate: "",
    healthStatus: "",
    pickupPointId: 0,
    idCardImage: "",
    notes: "",
    fillAllInfo: false,
    participantsInfo: [] as { name: string; phone: string; email: string; birthDate: string; idNumber: string; healthStatus: string; pickupPointId: number; idCardImage: string }[],
  });

  // Client-side initialization of query parameters to prevent hydration mismatches
  const [maxDate, setMaxDate] = useState("");
  useEffect(() => {
    const sp = new URLSearchParams(window.location.search);
    const slots = parseInt(sp.get("slots") || "1", 10);
    const name = sp.get("name") || "";
    const phone = sp.get("phone") || "";
    const email = sp.get("email") || "";
    const date = sp.get("date") || "";

    setFormData((prev) => ({
      ...prev,
      departureDate: date || prev.departureDate,
      participants: slots > 0 ? slots : prev.participants,
      fullName: name || prev.fullName,
      phone: phone || prev.phone,
      email: email || prev.email,
    }));
    setMaxDate(new Date().toISOString().split("T")[0]);
  }, []);

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState<"cash" | "transfer">("transfer");
  const [bookingError, setBookingError] = useState<string | null>(null);
  const [uploadError, setUploadError] = useState<string | null>(null);

  const updateFormData = (field: string, value: any) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const toggleService = (serviceId: string) => {
    const current = formData.selectedServices;
    if (current.includes(serviceId)) {
      updateFormData("selectedServices", current.filter((id) => id !== serviceId));
    } else {
      updateFormData("selectedServices", [...current, serviceId]);
    }
  };

  const nextStep = () => {
    if (currentStep < 4) setCurrentStep(currentStep + 1);
  };

  const prevStep = () => {
    if (currentStep > 1) setCurrentStep(currentStep - 1);
  };

  const handleUploadCCCD = async (file: File, index: number) => {
    try {
      setUploadError(null);
      if (index === -1) {
        updateFormData("idCardImage", "UPLOADING");
      } else {
        const newInfo = [...formData.participantsInfo];
        newInfo[index] = { ...newInfo[index], idCardImage: "UPLOADING" };
        updateFormData("participantsInfo", newInfo);
      }
      
      const res = await uploadFile(file);
      
      if (index === -1) {
        updateFormData("idCardImage", res.url);
      } else {
        const newInfo = [...formData.participantsInfo];
        newInfo[index] = { ...newInfo[index], idCardImage: res.url };
        updateFormData("participantsInfo", newInfo);
      }
    } catch (err: any) {
      console.error(err);
      setUploadError(err.message || "Không thể tải ảnh lên, vui lòng thử lại.");
      if (index === -1) {
        updateFormData("idCardImage", "");
      } else {
        const newInfo = [...formData.participantsInfo];
        newInfo[index] = { ...newInfo[index], idCardImage: "" };
        updateFormData("participantsInfo", newInfo);
      }
    }
  };

  const handleSubmit = async () => {
    setIsSubmitting(true);
    setBookingError(null);

    try {
      const result = await createBooking({
        tour_slug: slug,
        departure_date: formData.departureDate,
        pickup_point_id: formData.pickupPointId,
        participants: formData.participants,
        services: formData.selectedServices.map((serviceId) => {
          const service = tour?.services?.find((s) => s.id === serviceId);
          return service ? service.post_id : serviceId;
        }),
        rental_items: Object.entries(formData.rentalItems).reduce((acc, [itemId, qty]) => {
          const item = rentalItems.find((r) => r.id === itemId);
          const key = item ? item.post_id : itemId;
          acc[key] = qty;
          return acc;
        }, {} as Record<string | number, number>),
        payment_method: paymentMethod,
        main_contact: {
          full_name: formData.fullName,
          phone: formData.phone,
          email: formData.email,
        },
        passengers: [
          {
            full_name: formData.fullName,
            phone: formData.phone,
            email: formData.email,
            birth_date: formatDateToVN(formData.birthDate),
            id_number: formData.idNumber,
            health_status: formData.healthStatus,
            pickup_point_id: formData.pickupPointId,
            id_card_image: formData.idCardImage || undefined,
          },
          ...formData.participantsInfo.map((p) => ({
            full_name: p.name,
            phone: p.phone,
            email: p.email || undefined,
            birth_date: formatDateToVN(p.birthDate),
            id_number: p.idNumber,
            health_status: p.healthStatus,
            pickup_point_id: p.pickupPointId || formData.pickupPointId,
            id_card_image: p.idCardImage || undefined,
          })),
        ],
        notes: formData.notes || undefined,
        agree_terms: true,
      });

      router.push(`/booking/success?bookingId=${result.booking_id}&tour=${encodeURIComponent(tour?.name || "")}&date=${encodeURIComponent(formData.departureDate)}&participants=${formData.participants}&total=${result.total_amount}`);
      router.refresh();
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Đã có lỗi xảy ra";
      setBookingError(message === "tour_not_found" ? "Tour không tồn tại" :
        message === "departure_not_found" ? "Ngày khởi hành không tồn tại" :
        message === "departure_full" ? "Tour đã hết chỗ" :
        "Đã có lỗi xảy ra, vui lòng thử lại");
    } finally {
      setIsSubmitting(false);
    }
  };

  const servicesTotal = (tour?.services || []).reduce((sum, service) => {
    if (formData.selectedServices.includes(service.id)) {
      return sum + (service.price * formData.participants);
    }
    return sum;
  }, 0);

  const rentalTotal = Object.entries(formData.rentalItems).reduce((sum, [id, qty]) => {
    const service = rentalItems.find(s => s.id === id);
    if (service && qty > 0) {
      return sum + (service.price * qty);
    }
    return sum;
  }, 0);

  const totalPrice = (tour?.price || 0) * formData.participants + servicesTotal + rentalTotal;

  const selectedDeparture = tour?.departure_dates.find(d => d.date === formData.departureDate);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto mb-4" />
          <p className="text-gray-500">Đang tải thông tin tour...</p>
        </div>
      </div>
    );
  }

  if (!tour) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Tour không tìm thấy</h1>
          <Link href="/booking" className="text-emerald-600 hover:underline">
            ← Quay lại trang đặt tour
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />

      <main className="pt-[81px] pb-16">
        {/* Page Title for SEO & Accessibility */}
        <h1 className="sr-only">Đặt tour: {tour.name}</h1>

        {/* Progress Steps */}
        <div className="bg-white shadow-sm sticky top-0 z-40 mt-[81px]">
          <div className="container mx-auto px-4 py-4">
            <div className="flex items-center justify-center gap-4 lg:gap-8">
              {STEPS.map((step, index) => (
                <div key={step.id} className="flex items-center gap-3">
                  <div className="flex items-center gap-2">
                    <div
                      className={cn(
                        "w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all",
                        currentStep > step.id
                          ? "bg-emerald-500 text-white"
                          : currentStep === step.id
                          ? "bg-emerald-500 text-white ring-4 ring-emerald-100"
                          : "bg-gray-200 text-gray-500"
                      )}
                    >
                      {currentStep > step.id ? (
                        <CheckIcon className="w-5 h-5" />
                      ) : (
                        <step.icon className="w-5 h-5" />
                      )}
                    </div>
                    <span
                      className={cn(
                        "hidden sm:block font-medium",
                        currentStep >= step.id ? "text-gray-900" : "text-gray-400"
                      )}
                    >
                      {step.label}
                    </span>
                  </div>
                  {index < STEPS.length - 1 && (
                    <div
                      className={cn(
                        "w-12 lg:w-20 h-0.5 rounded-full",
                        currentStep > step.id ? "bg-emerald-500" : "bg-gray-200"
                      )}
                    />
                  )}
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="container mx-auto px-4 py-8">
          <div className="grid lg:grid-cols-3 gap-8">
            {/* Main Content */}
            <div className="lg:col-span-2">
              <AnimatePresence mode="wait">
                {/* Step 1: Select Date */}
                {currentStep === 1 && (
                  <motion.div
                    key="step1"
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -20 }}
                    className="bg-white rounded-2xl shadow-sm p-6 lg:p-8"
                  >
                    <h2 className="text-2xl font-bold text-gray-900 mb-6">Chọn ngày khởi hành</h2>

                    {/* Tour Summary */}
                    <div className="bg-gray-50 rounded-xl p-4 mb-6">
                      <div className="flex items-center gap-4">
                        <div className="w-20 h-20 rounded-xl overflow-hidden bg-emerald-100">
                          <img
                            src={getTourImage(tour.thumbnail || tour.gallery?.[0] || "")}
                            alt={tour.name}
                            className="w-full h-full object-cover"
                          />
                        </div>
                        <div>
                          <h3 className="font-bold text-gray-900">{tour.name}</h3>
                          <p className="text-sm text-gray-500">{tour.duration}</p>
                          <div className="flex items-center gap-2 mt-1">
                            <StarIcon className="w-4 h-4 text-yellow-400 fill-current" />
                            <span className="text-sm font-medium">4.9</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    {/* Date Selection */}
                    <div className="mb-6">
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Ngày khởi hành <span className="text-red-500">*</span>
                      </label>
                      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        {tour.departure_dates.map((dep) => {
                          const date = new Date(dep.date);
                          const day = date.getDate();
                          const month = date.getMonth() + 1;
                          const weekdays = ["CN", "T2", "T3", "T4", "T5", "T6", "T7"];
                          const weekday = weekdays[date.getDay()];
                          const formattedDate = `${weekday}, ${day}/${month}`;
                          const isSelected = formData.departureDate === dep.date;

                          return (
                            <button
                              key={dep.date}
                              onClick={() => updateFormData("departureDate", dep.date)}
                              className={cn(
                                "p-4 rounded-xl border-2 text-left transition-all",
                                isSelected
                                  ? "border-emerald-500 bg-emerald-50"
                                  : "border-gray-200 hover:border-emerald-300"
                              )}
                            >
                              <p className="text-sm font-semibold text-gray-900">{formattedDate}</p>
                              <p className="text-xs text-gray-500 mt-1">Còn {dep.available_spots} chỗ</p>
                            </button>
                          );
                        })}
                      </div>
                    </div>

                    {/* Participants */}
                    <div className="mb-6">
                      <label className="block text-sm font-medium text-gray-700 mb-3">
                        Số lượng người tham gia <span className="text-red-500">*</span>
                      </label>
                      <div className="flex items-center gap-4">
                        <button
                          onClick={() => updateFormData("participants", Math.max(1, formData.participants - 1))}
                          className="w-12 h-12 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors text-xl font-bold"
                        >
                          -
                        </button>
                        <span className="text-2xl font-bold text-gray-900 w-16 text-center">
                          {formData.participants}
                        </span>
                        <button
                          onClick={() => updateFormData("participants", Math.min(selectedDeparture?.available_spots || tour.available_spots, formData.participants + 1))}
                          className="w-12 h-12 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors text-xl font-bold"
                        >
                          +
                        </button>
                        <span className="text-gray-500">/ {selectedDeparture?.available_spots || tour.available_spots} chỗ</span>
                      </div>
                    </div>

                    {/* Hint */}
                    <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
                      <div className="flex items-start gap-3">
                        <CalendarIcon className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                        <div>
                          <p className="font-medium text-amber-800">Lưu ý:</p>
                          <p className="text-sm text-amber-700 mt-1">
                            Bạn có thể chọn dịch vụ kèm theo ở bước tiếp theo.
                          </p>
                        </div>
                      </div>
                    </div>
                  </motion.div>
                )}

                {/* Step 2: Contact Info */}
                {currentStep === 2 && (
                  <motion.div
                    key="step2"
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -20 }}
                    className="bg-white rounded-2xl shadow-sm p-6 lg:p-8"
                  >
                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Thông tin đặt tour</h2>
                    <p className="text-gray-500 mb-6">Điền thông tin người đại diện đặt tour.{formData.participants > 1 ? " Thông tin người tham gia khác có thể nhập ngay hoặc gửi link qua email." : ""}</p>

                    {/* Option: Fill all now or just 1 */}
                    {formData.participants > 1 && (
                      <div className="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <label className="flex items-center gap-3 cursor-pointer">
                          <input
                            type="checkbox"
                            checked={formData.fillAllInfo}
                            onChange={(e) => {
                              updateFormData("fillAllInfo", e.target.checked);
                              if (!e.target.checked) {
                                updateFormData("participantsInfo", []);
                              } else {
                                const emptyInfo = Array.from({ length: formData.participants - 1 }, () => ({ name: "", phone: "", email: "", birthDate: "", idNumber: "", healthStatus: "", pickupPointId: 0, idCardImage: "" }));
                                updateFormData("participantsInfo", emptyInfo);
                              }
                            }}
                            className="w-5 h-5 text-emerald-500 rounded focus:ring-emerald-500"
                          />
                          <span className="font-medium text-gray-700">Nhập thông tin tất cả {formData.participants} người ngay bây giờ</span>
                        </label>
                        {!formData.fillAllInfo && (
                          <p className="text-sm text-gray-500 mt-2 ml-8">
                            Bỏ trống để hệ thống gửi link qua email cho {formData.participants - 1} người còn lại
                          </p>
                        )}
                      </div>
                    )}

                    <div className="space-y-5">
                      {/* Main contact person */}
                      <div>
                        <h3 className="font-semibold text-gray-800 mb-3">Người đại diện (bắt buộc)</h3>
                        <div className="space-y-4">
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                              Họ và tên <span className="text-red-500">*</span>
                            </label>
                            <div className="relative">
                              <UsersIcon className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                              <input
                                type="text"
                                value={formData.fullName}
                                onChange={(e) => updateFormData("fullName", e.target.value)}
                                placeholder="Nhập họ và tên"
                                className="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                              />
                            </div>
                          </div>

                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                              Số điện thoại <span className="text-red-500">*</span>
                            </label>
                            <div className="relative">
                              <PhoneIcon className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                              <input
                                type="tel"
                                value={formData.phone}
                                onChange={(e) => updateFormData("phone", e.target.value)}
                                placeholder="0xxx xxx xxx"
                                className="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                              />
                            </div>
                          </div>

                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                              Email <span className="text-red-500">*</span>
                              <span className="text-gray-400 font-normal ml-1">
                                {formData.fillAllInfo ? "(xác nhận đặt tour)" : "(gửi link bổ sung thông tin)"}
                              </span>
                            </label>
                            <div className="relative">
                              <MailIcon className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                              <input
                                type="email"
                                value={formData.email}
                                onChange={(e) => updateFormData("email", e.target.value)}
                                placeholder="example@gmail.com"
                                className="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                              />
                            </div>
                          </div>

                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                              Ngày sinh <span className="text-red-500">*</span>
                            </label>
                            <input
                              type="date"
                              max={maxDate}
                              value={formData.birthDate}
                              onChange={(e) => updateFormData("birthDate", e.target.value)}
                              className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                          </div>

                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                              Tình trạng sức khỏe / Bệnh lý <span className="text-red-500">*</span>
                            </label>
                            <input
                              type="text"
                              value={formData.healthStatus}
                              onChange={(e) => updateFormData("healthStatus", e.target.value)}
                              placeholder="Mắc bệnh tim, hen suyễn... hoặc ghi 'Không' nếu sức khỏe bình thường"
                              className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                          </div>

                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                              Ảnh CCCD / Passport (Mặt trước) <span className="text-gray-400 font-normal">(tùy chọn)</span>
                            </label>
                            {formData.idCardImage === "UPLOADING" ? (
                              <div className="flex items-center justify-center h-32 border-2 border-dashed border-gray-250 rounded-xl bg-gray-50">
                                <div className="flex flex-col items-center gap-2">
                                  <svg className="animate-spin h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                  </svg>
                                  <span className="text-sm text-gray-500 font-medium">Đang tải ảnh lên...</span>
                                </div>
                              </div>
                            ) : formData.idCardImage ? (
                              <div className="relative inline-block mt-1">
                                <img
                                  src={formData.idCardImage}
                                  alt="Ảnh CCCD"
                                  className="w-48 h-32 object-cover rounded-xl border border-gray-250 shadow-sm"
                                />
                                <button
                                  type="button"
                                  onClick={() => updateFormData("idCardImage", "")}
                                  className="absolute -top-2 -right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors shadow-md focus:outline-none"
                                  aria-label="Xóa ảnh"
                                >
                                  <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                  </svg>
                                </button>
                              </div>
                            ) : (
                              <label className="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 hover:border-emerald-400 transition-all group">
                                <div className="flex flex-col items-center gap-1.5 text-center px-4">
                                  <svg className="w-8 h-8 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                  </svg>
                                  <span className="text-sm font-medium text-gray-600 group-hover:text-emerald-600 transition-colors">Tải ảnh lên (mặt trước CCCD)</span>
                                  <span className="text-xs text-gray-400">Chấp nhận JPG, PNG dung lượng dưới 5MB</span>
                                </div>
                                <input
                                  type="file"
                                  accept="image/*"
                                  onChange={(e) => {
                                    const file = e.target.files?.[0];
                                    if (file) handleUploadCCCD(file, -1);
                                  }}
                                  className="hidden"
                                />
                              </label>
                            )}
                            {uploadError && (
                              <p className="text-xs text-red-500 mt-1">{uploadError}</p>
                            )}
                          </div>
                        </div>
                      </div>

                      {/* Additional participants */}
                      {formData.fillAllInfo && formData.participants > 1 && (
                        <div>
                          <h3 className="font-semibold text-gray-800 mb-3">
                            Thông tin {formData.participants - 1} người còn lại
                          </h3>
                          <div className="space-y-4">
                            {formData.participantsInfo.map((_, index) => (
                              <div key={index} className="p-4 bg-gray-50 rounded-xl">
                                <p className="text-sm font-medium text-gray-600 mb-3">Người thứ {index + 2}</p>
                                <div className="space-y-4">
                                  <div className="grid grid-cols-2 gap-4">
                                    <div>
                                      <label className="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span className="text-red-500">*</span></label>
                                      <input
                                        type="text"
                                        placeholder="Họ và tên"
                                        value={formData.participantsInfo[index]?.name || ""}
                                        onChange={(e) => {
                                          const newInfo = [...formData.participantsInfo];
                                          newInfo[index] = { ...newInfo[index], name: e.target.value };
                                          updateFormData("participantsInfo", newInfo);
                                        }}
                                        className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                      />
                                    </div>
                                    <div>
                                      <label className="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span className="text-red-500">*</span></label>
                                      <input
                                        type="tel"
                                        placeholder="Số điện thoại"
                                        value={formData.participantsInfo[index]?.phone || ""}
                                        onChange={(e) => {
                                          const newInfo = [...formData.participantsInfo];
                                          newInfo[index] = { ...newInfo[index], phone: e.target.value };
                                          updateFormData("participantsInfo", newInfo);
                                        }}
                                        className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                      />
                                    </div>
                                  </div>
                                  <div className="grid grid-cols-3 gap-4">
                                    <div>
                                      <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                      <input
                                        type="email"
                                        placeholder="Email"
                                        value={formData.participantsInfo[index]?.email || ""}
                                        onChange={(e) => {
                                          const newInfo = [...formData.participantsInfo];
                                          newInfo[index] = { ...newInfo[index], email: e.target.value };
                                          updateFormData("participantsInfo", newInfo);
                                        }}
                                        className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                      />
                                    </div>
                                    <div>
                                      <label className="block text-sm font-medium text-gray-700 mb-1">Ngày sinh <span className="text-red-500">*</span></label>
                                      <input
                                        type="date"
                                        max={maxDate}
                                        value={formData.participantsInfo[index]?.birthDate || ""}
                                        onChange={(e) => {
                                          const newInfo = [...formData.participantsInfo];
                                          newInfo[index] = { ...newInfo[index], birthDate: e.target.value };
                                          updateFormData("participantsInfo", newInfo);
                                        }}
                                        className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                      />
                                    </div>
                                    <div>
                                      <label className="block text-sm font-medium text-gray-700 mb-1">Điểm đón</label>
                                      <select
                                        value={formData.participantsInfo[index]?.pickupPointId || 0}
                                        onChange={(e) => {
                                          const newInfo = [...formData.participantsInfo];
                                          newInfo[index] = { ...newInfo[index], pickupPointId: parseInt(e.target.value, 10) || 0 };
                                          updateFormData("participantsInfo", newInfo);
                                        }}
                                        className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
                                      >
                                        <option value={0}>Chọn điểm đón</option>
                                        {(tour.pickup_points || []).map((pp) => (
                                          <option key={pp.id} value={pp.id}>
                                            {pp.name}{pp.address ? ` — ${pp.address}` : ""}
                                          </option>
                                        ))}
                                      </select>
                                    </div>
                                  </div>
                                  
                                  <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Tình trạng sức khỏe / Bệnh lý <span className="text-red-500">*</span></label>
                                    <input
                                      type="text"
                                      placeholder="Ghi rõ bệnh lý hoặc 'Không'"
                                      value={formData.participantsInfo[index]?.healthStatus || ""}
                                      onChange={(e) => {
                                        const newInfo = [...formData.participantsInfo];
                                        newInfo[index] = { ...newInfo[index], healthStatus: e.target.value };
                                        updateFormData("participantsInfo", newInfo);
                                      }}
                                      className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    />
                                  </div>

                                  <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                      Ảnh CCCD / Passport (Mặt trước) <span className="text-gray-400 font-normal">(tùy chọn)</span>
                                    </label>
                                    {formData.participantsInfo[index]?.idCardImage === "UPLOADING" ? (
                                      <div className="flex items-center justify-center h-28 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50">
                                        <div className="flex flex-col items-center gap-1.5">
                                          <svg className="animate-spin h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                          </svg>
                                          <span className="text-xs text-gray-500 font-medium">Đang tải ảnh lên...</span>
                                        </div>
                                      </div>
                                    ) : formData.participantsInfo[index]?.idCardImage ? (
                                      <div className="relative inline-block mt-1">
                                        <img
                                          src={formData.participantsInfo[index].idCardImage}
                                          alt="Ảnh CCCD"
                                          className="w-40 h-28 object-cover rounded-xl border border-gray-200 shadow-sm"
                                        />
                                        <button
                                          type="button"
                                          onClick={() => {
                                            const newInfo = [...formData.participantsInfo];
                                            newInfo[index] = { ...newInfo[index], idCardImage: "" };
                                            updateFormData("participantsInfo", newInfo);
                                          }}
                                          className="absolute -top-2 -right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors shadow-md focus:outline-none"
                                          aria-label="Xóa ảnh"
                                        >
                                          <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                          </svg>
                                        </button>
                                      </div>
                                    ) : (
                                      <label className="flex flex-col items-center justify-center h-28 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 hover:border-emerald-400 transition-all group">
                                        <div className="flex flex-col items-center gap-1 text-center px-4">
                                          <svg className="w-6 h-6 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                          </svg>
                                          <span className="text-xs font-medium text-gray-600 group-hover:text-emerald-600 transition-colors">Tải ảnh lên CCCD</span>
                                        </div>
                                        <input
                                          type="file"
                                          accept="image/*"
                                          onChange={(e) => {
                                            const file = e.target.files?.[0];
                                            if (file) handleUploadCCCD(file, index);
                                          }}
                                          className="hidden"
                                        />
                                      </label>
                                    )}
                                  </div>
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}

                      {/* Pickup point for main contact */}
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                          Điểm đón của bạn <span className="text-red-500">*</span>
                        </label>
                        <select
                          value={formData.pickupPointId || 0}
                          onChange={(e) => updateFormData("pickupPointId", parseInt(e.target.value, 10) || 0)}
                          className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
                        >
                          <option value={0}>Chọn điểm đón</option>
                          {(tour.pickup_points || []).map((pp) => (
                            <option key={pp.id} value={pp.id}>
                              {pp.name}{pp.address ? ` — ${pp.address}` : ""}{pp.pickup_time ? ` (${pp.pickup_time})` : ""}
                            </option>
                          ))}
                        </select>
                        {(!tour.pickup_points || tour.pickup_points.length === 0) && (
                          <p className="mt-1 text-xs text-amber-600">Tour này chưa có điểm đón. Vui lòng liên hệ tổng đài để được hỗ trợ.</p>
                        )}
                      </div>

                      {/* Notes */}
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                          Ghi chú <span className="text-gray-400">(tùy chọn)</span>
                        </label>
                        <textarea
                          value={formData.notes}
                          onChange={(e) => updateFormData("notes", e.target.value)}
                          placeholder="Ví dụ: có người bị dị ứng thức ăn, cần hỗ trợ đặc biệt..."
                          rows={3}
                          className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                        />
                      </div>
                    </div>
                  </motion.div>
                )}

                {/* Step 3: Services */}
                {currentStep === 3 && (
                  <motion.div
                    key="step3"
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -20 }}
                    className="bg-white rounded-2xl shadow-sm p-6 lg:p-8"
                  >
                    <h2 className="text-2xl font-bold text-gray-900 mb-6">Dịch vụ kèm theo</h2>
                    <p className="text-gray-500 mb-6">Chọn dịch vụ và thuê đồ trekking (tùy chọn, có thể bỏ qua).</p>

                    {/* Tour Services */}
                    {tour.services && tour.services.length > 0 && (
                      <div className="mb-8">
                        <h3 className="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                          <SparklesIcon className="w-5 h-5 text-emerald-600" />
                          Dịch vụ tour
                        </h3>
                        <div className="space-y-3">
                          {tour.services.map((service) => {
                            const isSelected = formData.selectedServices.includes(service.id);
                            return (
                              <label
                                key={service.id}
                                className={cn(
                                  "flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all",
                                  isSelected
                                    ? "border-emerald-500 bg-emerald-50"
                                    : "border-gray-200 hover:border-emerald-300"
                                )}
                              >
                                <input
                                  type="checkbox"
                                  checked={isSelected}
                                  onChange={() => toggleService(service.id)}
                                  className="mt-1 w-5 h-5 text-emerald-500 rounded focus:ring-emerald-500"
                                />
                                <div className="flex-1">
                                  <div className="flex items-center justify-between">
                                    <span className="font-medium text-gray-900">{service.name}</span>
                                    <span className="font-semibold text-emerald-600">
                                      +{service.price.toLocaleString("vi-VN")}đ/{service.unit}
                                    </span>
                                  </div>
                                  <p className="text-sm text-gray-500 mt-1">{service.description}</p>
                                  {isSelected && (
                                    <p className="text-sm text-emerald-600 mt-1 font-medium">
                                      Tổng: {(service.price * formData.participants).toLocaleString("vi-VN")}đ ({formData.participants} {service.unit})
                                    </p>
                                  )}
                                </div>
                              </label>
                            );
                          })}
                        </div>
                      </div>
                    )}

                    {/* Rental Items */}
                    <div>
                      <h3 className="font-semibold text-gray-800 mb-3">Thuê đồ trekking & Camping</h3>
                      <div className="space-y-3">
                        {rentalItems.map((item) => {
                          const qty = formData.rentalItems[item.id] || 0;
                          return (
                            <div
                              key={item.id}
                              className={cn(
                                "p-4 rounded-xl border-2 transition-all",
                                qty > 0
                                  ? "border-emerald-500 bg-emerald-50"
                                  : "border-gray-200"
                              )}
                            >
                              <div className="flex items-start justify-between gap-4">
                                <div className="flex items-start gap-3 flex-1">
                                  <span className="text-2xl">{item.icon}</span>
                                  <div className="flex-1">
                                    <div className="flex items-center justify-between">
                                      <span className="font-medium text-gray-900">{item.name}</span>
                                      <span className="font-semibold text-emerald-600 whitespace-nowrap">
                                        +{item.price.toLocaleString("vi-VN")}đ/{item.unit}
                                      </span>
                                    </div>
                                    <p className="text-sm text-gray-500 mt-0.5">{item.description}</p>
                                  </div>
                                </div>
                                <div className="flex items-center gap-2">
                                  <button
                                    onClick={() => {
                                      const newQty = Math.max(0, qty - 1);
                                      updateFormData("rentalItems", { ...formData.rentalItems, [item.id]: newQty });
                                    }}
                                    className={cn(
                                      "w-9 h-9 rounded-lg border flex items-center justify-center text-lg font-bold transition-colors",
                                      qty > 0
                                        ? "border-emerald-300 bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                                        : "border-gray-200 text-gray-400 cursor-not-allowed"
                                    )}
                                    disabled={qty === 0}
                                  >
                                    -
                                  </button>
                                  <span className="w-8 text-center font-bold text-gray-900">{qty}</span>
                                  <button
                                    onClick={() => {
                                      const newQty = qty + 1;
                                      updateFormData("rentalItems", { ...formData.rentalItems, [item.id]: newQty });
                                    }}
                                    className="w-9 h-9 rounded-lg border border-emerald-300 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 flex items-center justify-center text-lg font-bold transition-colors"
                                  >
                                    +
                                  </button>
                                </div>
                              </div>
                              {qty > 0 && (
                                <p className="text-sm text-emerald-600 mt-2 font-medium text-right">
                                  Tổng: {(item.price * qty).toLocaleString("vi-VN")}đ
                                </p>
                              )}
                            </div>
                          );
                        })}
                      </div>
                      {rentalTotal > 0 && (
                        <div className="mt-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                          <div className="flex items-center justify-between">
                            <span className="font-medium text-gray-700">Tổng tiền thuê đồ</span>
                            <span className="text-xl font-bold text-emerald-600">{rentalTotal.toLocaleString("vi-VN")}đ</span>
                          </div>
                        </div>
                      )}
                    </div>
                  </motion.div>
                )}

                {/* Step 4: Payment */}
                {currentStep === 4 && (
                  <motion.div
                    key="step4"
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -20 }}
                    className="bg-white rounded-2xl shadow-sm p-6 lg:p-8"
                  >
                    <h2 className="text-2xl font-bold text-gray-900 mb-6">Thanh toán</h2>

                    {/* Booking Summary */}
                    <div className="bg-gray-50 rounded-xl p-5 mb-6">
                      <h3 className="font-bold text-gray-900 mb-4">Thông tin đặt tour</h3>

                      <div className="space-y-3">
                        <div className="flex justify-between">
                          <span className="text-gray-500">Mã đặt tour</span>
                          <span className="font-mono font-bold text-blue-600">— sẽ được tạo sau khi xác nhận —</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-500">Tour</span>
                          <span className="font-medium text-gray-900">{tour.name}</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-500">Ngày khởi hành</span>
                          <span className="font-medium text-gray-900">
                            {formData.departureDate && (() => {
                              const date = new Date(formData.departureDate);
                              const day = date.getDate();
                              const month = date.getMonth() + 1;
                              const year = date.getFullYear();
                              const weekdays = ["Chủ nhật", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7"];
                              const weekday = weekdays[date.getDay()];
                              return `${weekday}, ${day}/${month}/${year}`;
                            })()}
                          </span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-500">Số người</span>
                          <span className="font-medium text-gray-900">{formData.participants}</span>
                        </div>
                        <div className="border-t pt-3">
                          <div className="flex justify-between mb-2">
                            <span className="text-gray-500">Giá tour</span>
                            <span className="font-medium">{tour.price.toLocaleString("vi-VN")}đ x {formData.participants}</span>
                          </div>
                          {formData.selectedServices.length > 0 && tour.services?.map(service => {
                            if (formData.selectedServices.includes(service.id)) {
                              return (
                                <div key={service.id} className="flex justify-between mb-2">
                                  <span className="text-gray-500">{service.name}</span>
                                  <span className="font-medium">+{(service.price * formData.participants).toLocaleString("vi-VN")}đ</span>
                                </div>
                              );
                            }
                            return null;
                          })}
                          {rentalTotal > 0 && (
                            <div className="pt-2 border-t mt-2">
                              <p className="text-sm font-medium text-gray-700 mb-2">Đồ thuê:</p>
                              {Object.entries(formData.rentalItems).map(([id, qty]) => {
                                if (qty <= 0) return null;
                                const item = rentalItems.find(s => s.id === id);
                                if (!item) return null;
                                return (
                                  <div key={id} className="flex justify-between mb-1 text-sm">
                                    <span className="text-gray-500">{item.icon} {item.name} x{qty}</span>
                                    <span className="font-medium">+{(item.price * qty).toLocaleString("vi-VN")}đ</span>
                                  </div>
                                );
                              })}
                            </div>
                          )}
                        </div>
                        <div className="border-t pt-3 flex justify-between">
                          <span className="font-bold text-gray-900">Tổng cộng</span>
                          <span className="font-bold text-2xl text-emerald-600">{totalPrice.toLocaleString("vi-VN")}đ</span>
                        </div>
                      </div>
                    </div>

                    {/* Contact Info */}
                    <div className="bg-gray-50 rounded-xl p-5 mb-6">
                      <h3 className="font-bold text-gray-900 mb-4">Thông tin liên hệ</h3>
                      <div className="space-y-3">
                        <div className="flex justify-between">
                          <span className="text-gray-500">Người đại diện</span>
                          <span className="font-medium text-gray-900">{formData.fullName} - {formData.phone}</span>
                        </div>
                        <div className="grid grid-cols-2 gap-2 text-sm pt-2 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                          <div className="flex justify-between">
                            <span className="text-gray-500">Ngày sinh</span>
                            <span className="font-medium text-gray-900">{formatDateToVN(formData.birthDate) || "Chưa nhập"}</span>
                          </div>
                          <div className="flex justify-between">
                            <span className="text-gray-500">Sức khỏe / Bệnh lý</span>
                            <span className="font-medium text-gray-900">{formData.healthStatus || "Bình thường"}</span>
                          </div>
                          {formData.idCardImage && (
                            <div className="flex justify-between items-center col-span-2 pt-2 border-t border-gray-100 mt-2">
                              <span className="text-gray-500 text-sm">Ảnh CCCD</span>
                              <img src={formData.idCardImage} alt="Ảnh CCCD Đại diện" className="w-16 h-10 object-cover rounded border border-gray-200 shadow-sm" />
                            </div>
                          )}
                        </div>
                        {formData.fillAllInfo && formData.participantsInfo.map((info, index) => (
                          <div key={index} className="pt-3 border-t">
                            <p className="text-sm font-medium text-gray-700 mb-1">Người thứ {index + 2}</p>
                            <div className="grid grid-cols-2 gap-2 text-sm">
                              <div className="flex justify-between">
                                <span className="text-gray-500">Họ tên</span>
                                <span className="font-medium text-gray-900">{info.name || "Chưa nhập"}</span>
                              </div>
                              <div className="flex justify-between">
                                <span className="text-gray-500">SĐT</span>
                                <span className="font-medium text-gray-900">{info.phone || "Chưa nhập"}</span>
                              </div>
                              <div className="flex justify-between">
                                <span className="text-gray-500">Email</span>
                                <span className="font-medium text-gray-900">{info.email || "Chưa nhập"}</span>
                              </div>
                              <div className="flex justify-between">
                                <span className="text-gray-500">Ngày sinh</span>
                                <span className="font-medium text-gray-900">{formatDateToVN(info.birthDate) || "Chưa nhập"}</span>
                              </div>
                              <div className="flex justify-between">
                                <span className="text-gray-500">Điểm đón</span>
                                <span className="font-medium text-gray-900">{tour.pickup_points.find(pp => pp.id === info.pickupPointId)?.name || "Chưa chọn"}</span>
                              </div>
                              <div className="flex justify-between">
                                <span className="text-gray-500">Sức khỏe</span>
                                <span className="font-medium text-gray-900">{info.healthStatus || "Bình thường"}</span>
                              </div>
                              {info.idCardImage && (
                                <div className="flex justify-between items-center col-span-2 pt-1 border-t border-gray-100 mt-1">
                                  <span className="text-gray-500">Ảnh CCCD</span>
                                  <img src={info.idCardImage} alt={`Ảnh CCCD người thứ ${index + 2}`} className="w-16 h-10 object-cover rounded border border-gray-200 shadow-sm" />
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                        <div className="flex justify-between">
                          <span className="text-gray-500">Email</span>
                          <span className="font-medium text-gray-900">{formData.email}</span>
                        </div>
                        {formData.notes && (
                          <div className="pt-3 border-t">
                            <span className="text-gray-500 block mb-1">Ghi chú</span>
                            <span className="font-medium text-gray-900">{formData.notes}</span>
                          </div>
                        )}
                      </div>
                    </div>
                    {!formData.fillAllInfo && formData.participants > 1 && (
                      <div className="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p className="text-sm text-blue-700">
                          <SparklesIcon className="w-4 h-4 inline mr-1" />
                          Thông tin {formData.participants - 1} người còn lại sẽ được gửi link qua email: <strong>{formData.email}</strong>
                        </p>
                      </div>
                    )}
                    {formData.fillAllInfo && (
                      <p className="mt-3 text-sm text-emerald-600">
                        <CheckIcon className="w-4 h-4 inline mr-1" />
                        Đã nhập đủ thông tin {formData.participants} người
                      </p>
                    )}

                    {/* Payment Methods */}
                    <div className="mb-6">
                      <h3 className="font-bold text-gray-900 mb-4">Phương thức thanh toán</h3>
                      <div className="space-y-3">
                        <label className={cn(
                          "flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all",
                          paymentMethod === "transfer"
                            ? "border-emerald-500 bg-emerald-50"
                            : "border-gray-200 hover:border-gray-300"
                        )}>
                          <input
                            type="radio"
                            name="payment"
                            value="transfer"
                            checked={paymentMethod === "transfer"}
                            onChange={() => setPaymentMethod("transfer")}
                            className="w-5 h-5 text-emerald-500"
                          />
                          <div className="flex-1">
                            <p className="font-medium text-gray-900">Chuyển khoản ngân hàng</p>
                            <p className="text-sm text-gray-500">Quét mã QR hoặc chuyển khoản thủ công</p>
                          </div>
                        </label>
                        <label className={cn(
                          "flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all",
                          paymentMethod === "cash"
                            ? "border-emerald-500 bg-emerald-50"
                            : "border-gray-200 hover:border-gray-300"
                        )}>
                          <input
                            type="radio"
                            name="payment"
                            value="cash"
                            checked={paymentMethod === "cash"}
                            onChange={() => setPaymentMethod("cash")}
                            className="w-5 h-5 text-emerald-500"
                          />
                          <div className="flex-1">
                            <p className="font-medium text-gray-900">Thanh toán khi gặp HDV</p>
                            <p className="text-sm text-gray-500">Tiền mặt hoặc chuyển khoản trước khi khởi hành</p>
                          </div>
                        </label>
                      </div>
                    </div>

                    {/* QR Code */}
                    {paymentMethod === "transfer" && (
                      <div className="mb-6 p-6 bg-gradient-to-br from-emerald-50 to-blue-50 border border-emerald-200 rounded-2xl">
                        <h3 className="font-bold text-gray-900 mb-2 text-center">Thanh toán qua chuyển khoản</h3>
                        <p className="text-sm text-gray-600 text-center mb-4">
                          Sau khi xác nhận đặt tour, bạn sẽ được chuyển tới trang chi tiết với mã QR thanh toán, số tài khoản và nội dung chuyển khoản chính xác cho đơn này.
                        </p>
                        <div className="bg-white rounded-xl p-4 border border-emerald-100">
                          <div className="flex justify-between py-1">
                            <span className="text-gray-500">Số tiền dự kiến</span>
                            <span className="font-bold text-lg text-emerald-600">{totalPrice.toLocaleString("vi-VN")}đ</span>
                          </div>
                        </div>
                        <div className="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                          <p className="text-sm text-amber-800">
                            ⚠️ Vui lòng chuyển <strong>đúng số tiền</strong> và <strong>đúng nội dung</strong> hiển thị ở trang xác nhận để hệ thống tự động đối soát.
                          </p>
                        </div>
                      </div>
                    )}

                    {/* Terms */}
                    <div className="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                      <p className="text-sm text-amber-800">
                        Bằng việc xác nhận, bạn đồng ý với{" "}
                        <a href="/policies/terms" className="underline font-medium">điều khoản dịch vụ</a> và{" "}
                        <a href="/policies/cancel" className="underline font-medium">chính sách hủy tour</a> của Đôi Dép Adventure.
                      </p>
                    </div>

                    {bookingError && (
                      <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p className="text-sm text-red-700">{bookingError}</p>
                      </div>
                    )}
                  </motion.div>
                )}
              </AnimatePresence>

              {/* Navigation Buttons */}
              <div className="flex items-center justify-between mt-6">
                {currentStep > 1 ? (
                  <button
                    onClick={prevStep}
                    className="flex items-center gap-2 px-6 py-3 text-gray-600 hover:text-gray-900 font-medium transition-colors"
                  >
                    <ArrowLeftIcon className="w-5 h-5" />
                    Quay lại
                  </button>
                ) : (
                  <Link
                    href="/booking"
                    className="flex items-center gap-2 px-6 py-3 text-gray-600 hover:text-gray-900 font-medium transition-colors"
                  >
                    <ArrowLeftIcon className="w-5 h-5" />
                    Quay lại
                  </Link>
                )}

                {currentStep < 4 ? (
                  <button
                    onClick={nextStep}
                    disabled={
                      (currentStep === 1 && (!formData.departureDate || formData.participants < 1)) ||
                      (currentStep === 2 && (
                        !formData.fullName || !formData.phone || !formData.email ||
                        !formData.birthDate || !formData.healthStatus || !formData.pickupPointId ||
                        (formData.fillAllInfo && formData.participants > 1 &&
                          formData.participantsInfo.some(p => !p.name || !p.phone || !p.birthDate || !p.healthStatus)
                        )
                      ))
                    }
                    className="flex items-center gap-2 px-8 py-3 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Tiếp tục
                    <ChevronRightIcon className="w-5 h-5" />
                  </button>
                ) : (
                  <button
                    onClick={handleSubmit}
                    disabled={isSubmitting}
                    className="flex items-center gap-2 px-8 py-3 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-colors disabled:opacity-50"
                  >
                    {isSubmitting ? (
                      <>
                        <svg className="animate-spin w-5 h-5" viewBox="0 0 24 24">
                          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Đang xử lý...
                      </>
                    ) : (
                      <>
                        Thanh toán
                        <CheckIcon className="w-5 h-5" />
                      </>
                    )}
                  </button>
                )}
              </div>
            </div>

            {/* Sidebar - Price Summary */}
            <div className="lg:col-span-1">
              <div className="sticky top-[200px] space-y-4">
                {/* Tour Card */}
                <div className="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                  <img
                    src={tour.gallery[0]}
                    alt={tour.name}
                    className="w-full h-40 object-cover rounded-xl mb-4"
                  />
                  <h3 className="font-bold text-gray-900 text-lg mb-2">{tour.name}</h3>
                  <p className="text-sm text-gray-500 mb-4">{tour.description}</p>
                  <div className="space-y-2 text-sm">
                    <div className="flex items-center gap-2 text-gray-600">
                      <ClockIcon className="w-4 h-4" />
                      <span>{tour.duration}</span>
                    </div>
                    <div className="flex items-center gap-2 text-gray-600">
                      <UsersIcon className="w-4 h-4" />
                      <span>{formData.participants} người</span>
                    </div>
                  </div>
                </div>

                {/* Price Summary */}
                <div className="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                  <h4 className="font-bold text-gray-900 mb-4">Chi phí</h4>
                  <div className="space-y-3 text-sm">
                    <div className="flex justify-between">
                      <span className="text-gray-500">Giá tour</span>
                      <span className="font-medium">{tour.price.toLocaleString("vi-VN")}đ</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-500">Số người</span>
                      <span className="font-medium">x{formData.participants}</span>
                    </div>
                    {servicesTotal > 0 && (
                      <div className="flex justify-between">
                        <span className="text-gray-500">Dịch vụ</span>
                        <span className="font-medium text-emerald-600">+{servicesTotal.toLocaleString("vi-VN")}đ</span>
                      </div>
                    )}
                    {rentalTotal > 0 && (
                      <div className="flex justify-between">
                        <span className="text-gray-500">Thuê đồ</span>
                        <span className="font-medium text-emerald-600">+{rentalTotal.toLocaleString("vi-VN")}đ</span>
                      </div>
                    )}
                    <div className="border-t pt-3 flex justify-between">
                      <span className="font-bold text-gray-900">Tổng cộng</span>
                      <span className="font-bold text-xl text-emerald-600">{totalPrice.toLocaleString("vi-VN")}đ</span>
                    </div>
                  </div>
                </div>

                {/* Booking Reference */}
                <div className="bg-blue-50 border border-blue-200 rounded-2xl p-5">
                  <h4 className="font-bold text-blue-900 mb-2">Mã đặt tour</h4>
                  <p className="font-mono text-base text-blue-700 text-center py-2">Mã sẽ được hệ thống tạo sau khi bạn xác nhận đặt tour.</p>
                  <p className="text-xs text-blue-700 mt-2">Bạn sẽ thấy mã ở trang xác nhận để dùng làm nội dung chuyển khoản.</p>
                </div>

                {/* Support */}
                <div className="bg-gradient-to-br from-emerald-600 to-emerald-500 rounded-2xl p-5 text-white">
                  <h4 className="font-bold mb-2">Cần hỗ trợ?</h4>
                  <p className="text-emerald-100 text-sm mb-4">Liên hệ trực tiếp với chúng tôi</p>
                  <a
                    href={`tel:${settings?.hotline ? settings.hotline.replace(/\s+/g, "") : "0961804359"}`}
                    className="flex items-center gap-2 text-white font-semibold hover:text-emerald-100 transition-colors"
                  >
                    <PhoneIcon className="w-5 h-5" />
                    {settings?.hotline || "096 180 43 59"}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
