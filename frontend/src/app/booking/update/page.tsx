"use client";

import { Suspense, useState, useEffect } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import {
  getBooking,
  updateBookingPassengers,
  getPickupPoints,
  BookingDetail,
  PickupPoint,
} from "@/lib/api";
import {
  CalendarIcon,
  UsersIcon,
  PhoneIcon,
  MailIcon,
  ArrowLeftIcon,
  SparklesIcon,
  CheckIcon,
} from "@/components/icons";
import { cn } from "@/lib/utils";

export default function BookingUpdatePage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-gray-50 flex items-center justify-center">
          <div className="flex flex-col items-center gap-3">
            <svg className="animate-spin h-10 w-10 text-emerald-500" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            <span className="text-gray-600 font-medium">Đang tải trang...</span>
          </div>
        </div>
      }
    >
      <BookingUpdateContent />
    </Suspense>
  );
}

function BookingUpdateContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const bookingId = searchParams.get("bookingId") || "";
  const email = searchParams.get("email") || "";
  const expires = searchParams.get("expires") || "";
  const token = searchParams.get("token") || "";

  const [booking, setBooking] = useState<BookingDetail | null>(null);
  const [pickupPoints, setPickupPoints] = useState<PickupPoint[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const [saving, setSaving] = useState(false);
  const [maxDate, setMaxDate] = useState("");

  // Local state for passengers
  const [passengers, setPassengers] = useState<any[]>([]);

  useEffect(() => {
    setMaxDate(new Date().toISOString().split("T")[0]);
  }, []);

  useEffect(() => {
    if (!bookingId || !email || !expires || !token) {
      setError("Liên kết cập nhật thông tin không hợp lệ hoặc thiếu thông tin xác thực. Vui lòng kiểm tra lại liên kết trong email.");
      setLoading(false);
      return;
    }

    let isSubscribed = true;

    const loadData = async () => {
      try {
        const [bookingData, pickupPointsData] = await Promise.all([
          getBooking(bookingId, email, expires, token),
          getPickupPoints(),
        ]);

        if (!isSubscribed) return;

        // Verify if booking main contact email matches (case insensitive check)
        const contactEmail = bookingData.main_contact?.email?.toLowerCase();
        if (contactEmail !== email.toLowerCase()) {
          setError("Email xác thực không khớp với email liên hệ trên đơn đặt tour.");
          setLoading(false);
          return;
        }

        setBooking(bookingData);
        setPickupPoints(pickupPointsData || []);

        // Populate local passenger state
        const initialPassengers = bookingData.passengers.map((p) => ({
          id: p.id,
          full_name: p.full_name || "",
          phone: p.phone || "",
          email: p.email || "",
          birth_date: p.birth_date || "",
          pickup_point_id: p.pickup_point_id || 0,
          health_status: p.health_status || "",
          seat: p.seat || "",
          checked_in: p.checked_in || false,
        }));
        setPassengers(initialPassengers);
        setLoading(false);
      } catch (err: any) {
        console.error(err);
        if (isSubscribed) {
          let errorMsg = "Có lỗi xảy ra khi tải thông tin đơn đặt tour. Vui lòng thử lại sau.";
          if (err.message === "link_expired") {
            errorMsg = "Liên kết cập nhật thông tin thành viên đã hết hạn trước khi chuyến đi bắt đầu.";
          } else if (err.message === "invalid_token") {
            errorMsg = "Liên kết xác thực không hợp lệ hoặc đã bị thay đổi.";
          } else if (err.message === "booking_not_found") {
            errorMsg = "Không tìm thấy đơn hàng tương ứng trên hệ thống.";
          }
          setError(errorMsg);
          setLoading(false);
        }
      }
    };

    loadData();

    return () => {
      isSubscribed = false;
    };
  }, [bookingId, email, expires, token]);

  const handlePassengerChange = (index: number, field: string, value: any) => {
    const updated = [...passengers];
    updated[index] = { ...updated[index], [field]: value };
    setPassengers(updated);
  };


  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();

    // Basic Validation
    const invalidPassenger = passengers.find(
      (p) => !p.full_name || !p.phone || !p.birth_date || !p.health_status
    );

    if (invalidPassenger) {
      alert("Vui lòng nhập đầy đủ thông tin bắt buộc (Họ tên, SĐT, Ngày sinh, Sức khỏe) cho tất cả thành viên.");
      return;
    }

    try {
      setSaving(true);
      await updateBookingPassengers(bookingId, email, passengers, expires, token);
      setSuccess(true);
      setSaving(false);
      window.scrollTo({ top: 0, behavior: "smooth" });
    } catch (err: any) {
      console.error(err);
      let errorMsg = err.message || "Không thể cập nhật thông tin hành khách. Vui lòng thử lại.";
      if (err.message === "link_expired") {
        errorMsg = "Liên kết cập nhật thông tin thành viên đã hết hạn trước khi chuyến đi bắt đầu.";
      } else if (err.message === "invalid_token") {
        errorMsg = "Liên kết xác thực không hợp lệ hoặc đã bị thay đổi.";
      }
      alert(errorMsg);
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex flex-col bg-gray-50">
        <Header />
        <main className="flex-grow flex items-center justify-center py-20">
          <div className="flex flex-col items-center gap-3">
            <div className="relative w-12 h-12">
              <div className="absolute inset-0 rounded-full border-4 border-emerald-100 animate-pulse"></div>
              <div className="absolute inset-0 rounded-full border-4 border-t-emerald-500 animate-spin"></div>
            </div>
            <p className="text-gray-500 font-medium">Đang tải thông tin đơn hàng...</p>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen flex flex-col bg-gray-50">
        <Header />
        <main className="flex-grow flex items-center justify-center py-16 px-4">
          <div className="bg-white rounded-2xl p-8 max-w-lg w-full shadow-sm text-center border border-gray-150">
            <div className="w-16 h-16 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-5 border border-rose-100">
              <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h2 className="text-xl font-bold text-gray-900 mb-3">Xác thực không hợp lệ</h2>
            <p className="text-gray-500 mb-6 text-sm leading-relaxed">{error}</p>
            <div className="space-y-3">
              <a
                href={`tel:${booking?.main_contact?.phone || "0961804359"}`}
                className="block w-full py-3 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-colors shadow-sm text-sm"
              >
                Liên hệ Hotline hỗ trợ
              </a>
              <button
                onClick={() => router.push("/")}
                className="block w-full py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm"
              >
                Quay lại Trang chủ
              </button>
            </div>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col bg-gray-50">
      <Header />
      <main className="flex-grow py-12 px-4 lg:py-16">
        <div className="max-w-4xl mx-auto">
          {/* Success Screen */}
          <AnimatePresence mode="wait">
            {success ? (
              <motion.div
                key="success"
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0 }}
                className="bg-white rounded-3xl p-8 lg:p-12 text-center shadow-lg border border-gray-100 max-w-2xl mx-auto"
              >
                <div className="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-100 shadow-inner">
                  <CheckIcon className="w-10 h-10" />
                </div>
                <h2 className="text-2xl font-extrabold text-gray-900 mb-3">Lưu thông tin thành công</h2>
                <p className="text-gray-500 mb-8 leading-relaxed max-w-md mx-auto text-sm">
                  Thông tin hành khách của đơn hàng <strong>{bookingId}</strong> đã được cập nhật thành công lên hệ thống. Đôi Dép Adventure sẽ liên hệ lại nếu có bất cứ thông tin gì cần làm rõ thêm.
                </p>
                <div className="flex flex-col sm:flex-row justify-center gap-4">
                  <button
                    onClick={() => router.push("/")}
                    className="px-8 py-3.5 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-colors shadow-md text-sm"
                  >
                    Về Trang chủ
                  </button>
                  <button
                    onClick={() => setSuccess(false)}
                    className="px-8 py-3.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm"
                  >
                    Xem lại biểu mẫu
                  </button>
                </div>
              </motion.div>
            ) : (
              <motion.div
                key="form"
                initial={{ opacity: 0, y: 15 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0 }}
              >
                {/* Hero / Summary header */}
                <div className="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-3xl p-6 lg:p-8 shadow-md mb-8">
                  <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                      <span className="inline-block px-3 py-1 bg-white/20 backdrop-blur-md text-xs font-semibold rounded-full uppercase tracking-wider mb-2">
                        {booking?.status === "confirmed" ? "Đã Xác Nhận" : booking?.status === "paid" ? "Đã Thanh Toán" : "Chờ Xử Lý"}
                      </span>
                      <h1 className="text-2xl lg:text-3xl font-extrabold tracking-tight">Cập Nhật Thành Viên Đi Cùng</h1>
                      <p className="text-emerald-100 mt-2 font-medium text-sm lg:text-base">
                        Tour: {booking?.tour?.name}
                      </p>
                    </div>
                    <div className="bg-white/10 backdrop-blur-md rounded-2xl p-4 text-right md:min-w-[200px] border border-white/10">
                      <p className="text-xs text-emerald-200 uppercase tracking-wider font-semibold">Mã đơn hàng</p>
                      <p className="text-xl font-bold tracking-wider mt-0.5">{bookingId}</p>
                      <p className="text-xs text-emerald-100 mt-1 font-medium">Khởi hành: {booking?.departure?.date}</p>
                    </div>
                  </div>
                </div>

                {/* Form starts */}
                <form onSubmit={handleSave} className="space-y-6">
                  {passengers.map((passenger, index) => (
                    <div
                      key={passenger.id || index}
                      className="bg-white rounded-2xl border border-gray-150 p-6 lg:p-8 shadow-sm transition-all hover:shadow-md"
                    >
                      <div className="flex items-center gap-3 border-b pb-4 mb-6">
                        <div className="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                          {index + 1}
                        </div>
                        <div>
                          <h3 className="font-bold text-gray-800">
                            Thành viên thứ {index + 1} {index === 0 && <span className="text-emerald-600 font-semibold">(Người đại diện)</span>}
                          </h3>
                        </div>
                      </div>

                      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {/* Full Name */}
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Họ và tên <span className="text-red-500">*</span>
                          </label>
                          <input
                            type="text"
                            required
                            value={passenger.full_name}
                            onChange={(e) => handlePassengerChange(index, "full_name", e.target.value)}
                            placeholder="Nhập họ và tên"
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm"
                          />
                        </div>

                        {/* Phone */}
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Số điện thoại <span className="text-red-500">*</span>
                          </label>
                          <input
                            type="tel"
                            required
                            value={passenger.phone}
                            onChange={(e) => handlePassengerChange(index, "phone", e.target.value)}
                            placeholder="Nhập số điện thoại"
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm"
                          />
                        </div>

                        {/* Email */}
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Email <span className="text-gray-400 font-normal">(Tùy chọn)</span>
                          </label>
                          <input
                            type="email"
                            value={passenger.email}
                            onChange={(e) => handlePassengerChange(index, "email", e.target.value)}
                            placeholder="example@gmail.com"
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm"
                          />
                        </div>

                        {/* Birth Date */}
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Ngày sinh <span className="text-red-500">*</span>
                          </label>
                          <input
                            type="date"
                            required
                            max={maxDate}
                            value={passenger.birth_date}
                            onChange={(e) => handlePassengerChange(index, "birth_date", e.target.value)}
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm bg-white"
                          />
                        </div>

                        {/* Pickup Point */}
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Điểm đón</label>
                          <select
                            value={passenger.pickup_point_id || 0}
                            onChange={(e) =>
                              handlePassengerChange(
                                index,
                                "pickup_point_id",
                                parseInt(e.target.value, 10) || 0
                              )
                            }
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm bg-white"
                          >
                            <option value={0}>Chọn điểm đón</option>
                            {pickupPoints.map((pp) => (
                              <option key={pp.id} value={pp.id}>
                                {pp.name}
                                {pp.address ? ` — ${pp.address}` : ""}
                                {pp.pickup_time ? ` (${pp.pickup_time})` : ""}
                              </option>
                            ))}
                          </select>
                        </div>

                        {/* Health Status */}
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Tình trạng sức khỏe / Bệnh lý <span className="text-red-500">*</span>
                          </label>
                          <input
                            type="text"
                            required
                            value={passenger.health_status}
                            onChange={(e) => handlePassengerChange(index, "health_status", e.target.value)}
                            placeholder="Mắc bệnh tim, hen suyễn... hoặc ghi 'Không'"
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm"
                          />
                        </div>
                      </div>
                    </div>
                  ))}

                  {/* Submission and back buttons */}
                  <div className="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-5 rounded-2xl border border-gray-150 shadow-sm">
                    <button
                      type="button"
                      onClick={() => router.push("/")}
                      className="flex items-center gap-2 px-6 py-3 text-gray-600 hover:text-gray-900 font-medium transition-colors text-sm"
                    >
                      <ArrowLeftIcon className="w-4 h-4" />
                      Hủy & Quay lại
                    </button>
                    <button
                      type="submit"
                      disabled={saving}
                      className="w-full sm:w-auto flex items-center justify-center gap-2 px-8 py-3.5 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                    >
                      {saving ? (
                        <>
                          <svg className="animate-spin w-4 h-4 text-white" viewBox="0 0 24 24" fill="none">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                          </svg>
                          Đang lưu...
                        </>
                      ) : (
                        <>
                          Lưu thay đổi
                          <CheckIcon className="w-4 h-4" />
                        </>
                      )}
                    </button>
                  </div>
                </form>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </main>
      <Footer />
    </div>
  );
}
