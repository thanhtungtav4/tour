"use client";

import { useState, useEffect, useMemo } from "react";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { getCheckinPassengers, toggleCheckin, getTours, checkinAuthenticate, remindGatherPassenger } from "@/lib/api/client";
import { CheckinPassenger, TourListItem } from "@/lib/api/types";
import { SearchIcon, CloseIcon } from "@/components/icons";

const getGatherEmailLink = (p: CheckinPassenger) => {
  const subject = encodeURIComponent(`[Đôi Dép Adventure] Nhắc nhở tập trung - Tour ${p.tour_name}`);
  const body = encodeURIComponent(
    `Xin chào ${p.full_name},\n\n` +
    `Đây là thông báo nhắc nhở từ Đôi Dép Adventure dành cho hành trình tour "${p.tour_name}" khởi hành ngày ${p.departure_date}.\n\n` +
    `Vui lòng có mặt tại điểm tập trung "${p.pickup_point || "Điểm hẹn"}" đúng giờ để chúng ta chuẩn bị xuất phát.\n\n` +
    `Nếu gặp bất kỳ khó khăn hoặc cần hỗ trợ gấp trên đường đi, anh/chị vui lòng liên hệ trực tiếp với Hướng dẫn viên qua số hotline.\n\n` +
    `Chúc anh/chị có một chuyến hành trình trải nghiệm thật tuyệt vời!\n\n` +
    `Trân trọng,\n` +
    `Đội ngũ Đôi Dép Adventure`
  );
  return `mailto:${p.email || ""}?subject=${subject}&body=${body}`;
};

export default function CheckinPage() {
  const [passengers, setPassengers] = useState<CheckinPassenger[]>([]);
  const [tours, setTours] = useState<TourListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [updatingIds, setUpdatingIds] = useState<Record<string, boolean>>({});
  const [sendingRemindIds, setSendingRemindIds] = useState<Record<string, boolean>>({});

  // Authentication State
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [authToken, setAuthToken] = useState("");
  const [pinCode, setPinCode] = useState("");
  const [authError, setAuthError] = useState("");
  const [authLoading, setAuthLoading] = useState(false);

  // Filters State
  const [selectedTourId, setSelectedTourId] = useState<number | "">("");
  const [selectedDepartureDate, setSelectedDepartureDate] = useState<string>("");
  const [availableDepartureDates, setAvailableDepartureDates] = useState<string[]>([]);
  const [searchQuery, setSearchQuery] = useState("");
  const [boardingFilter, setBoardingFilter] = useState<"all" | "checked" | "unchecked">("all");
  const [gatheringFilter, setGatheringFilter] = useState<"all" | "checked" | "unchecked">("all");

  // Load configuration and data
  useEffect(() => {
    // Check localStorage for saved token authentication
    const savedToken = localStorage.getItem("staff_checkin_token");
    if (savedToken) {
      setAuthToken(savedToken);
      setIsAuthenticated(true);
    }
  }, []);

  useEffect(() => {
    if (!isAuthenticated || !authToken) return;

    async function fetchData() {
      try {
        setLoading(true);
        setError("");
        const [passengersData, toursData] = await Promise.all([
          getCheckinPassengers(authToken, {
            tourId: selectedTourId ? Number(selectedTourId) : undefined,
            departureDate: selectedDepartureDate || undefined,
          }),
          getTours({ per_page: 100 }),
        ]);
        setPassengers(passengersData.passengers);
        setTours(toursData.data);
        setAvailableDepartureDates(passengersData.departureDates);
      } catch (err: any) {
        console.error(err);
        if (err.status === 401) {
          handleLogout();
          setError("Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.");
        } else {
          setError("Không thể tải danh sách dữ liệu. Vui lòng thử lại sau.");
        }
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, [isAuthenticated, authToken, selectedTourId, selectedDepartureDate]);

  // Handle PIN code validation via API
  const handleAuthSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setAuthLoading(true);
    setAuthError("");

    try {
      const result = await checkinAuthenticate(pinCode);
      setAuthToken(result.token);
      setIsAuthenticated(true);
      localStorage.setItem("staff_checkin_token", result.token);
    } catch (err: any) {
      setAuthError(err.message || "Mã PIN không chính xác. Vui lòng thử lại.");
    } finally {
      setAuthLoading(false);
    }
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    setAuthToken("");
    setPinCode("");
    localStorage.removeItem("staff_checkin_token");
  };

  // Toggle check-in status (boarding or gathering) - per-row disable
  const handleToggleCheckin = async (
    passenger: CheckinPassenger,
    type: "boarding" | "gathering"
  ) => {
    const uniqueId = `${passenger.booking_id}_${passenger.passenger_index}_${type}`;
    setUpdatingIds((prev) => ({ ...prev, [uniqueId]: true }));

    try {
      const currentValue = type === "boarding" ? passenger.checked_in : passenger.checked_in_gathering;
      const targetValue = !currentValue;

      await toggleCheckin(
        authToken,
        passenger.booking_id,
        passenger.passenger_index,
        type,
        targetValue
      );

      // Update state locally
      setPassengers((prev) =>
        prev.map((p) => {
          if (p.booking_id === passenger.booking_id && p.passenger_index === passenger.passenger_index) {
            return {
              ...p,
              checked_in: type === "boarding" ? targetValue : p.checked_in,
              checked_in_gathering: type === "gathering" ? targetValue : p.checked_in_gathering,
            };
          }
          return p;
        })
      );
    } catch (err: any) {
      alert(err.message || "Cập nhật check-in thất bại");
    } finally {
      setUpdatingIds((prev) => {
        const next = { ...prev };
        delete next[uniqueId];
        return next;
      });
    }
  };

  // Handle manual gathering email reminder
  const handleRemindGather = async (passenger: CheckinPassenger) => {
    if (!passenger.email) {
      alert("Hành khách này không có email. Hệ thống sẽ tự động gửi tới email của người đại diện nếu có.");
    }
    const uniqueId = `${passenger.booking_id}_${passenger.passenger_index}`;
    setSendingRemindIds((prev) => ({ ...prev, [uniqueId]: true }));
    try {
      const res = await remindGatherPassenger(authToken, passenger.booking_id, passenger.passenger_index);
      alert(res.message || "Đã gửi email nhắc nhở tập trung thành công!");
    } catch (err: any) {
      alert(err.message || "Gửi email nhắc nhở thất bại");
    } finally {
      setSendingRemindIds((prev) => {
        const next = { ...prev };
        delete next[uniqueId];
        return next;
      });
    }
  };

  // Filter passengers based on search and selected statuses
  const filteredPassengers = useMemo(() => {
    return passengers.filter((p) => {
      // 1. Search Query filter (name, phone, booking code)
      const q = searchQuery.toLowerCase().trim();
      if (q) {
        const matchesName = p.full_name?.toLowerCase().includes(q);
        const matchesPhone = p.phone?.includes(q);
        const matchesCode = p.booking_code?.toLowerCase().includes(q);
        if (!matchesName && !matchesPhone && !matchesCode) {
          return false;
        }
      }

      // 2. Boarding filter
      if (boardingFilter === "checked" && !p.checked_in) return false;
      if (boardingFilter === "unchecked" && p.checked_in) return false;

      // 3. Gathering filter
      if (gatheringFilter === "checked" && !p.checked_in_gathering) return false;
      if (gatheringFilter === "unchecked" && p.checked_in_gathering) return false;

      return true;
    });
  }, [passengers, searchQuery, boardingFilter, gatheringFilter]);

  // Statistics calculations
  const stats = useMemo(() => {
    const total = filteredPassengers.length;
    const boarded = filteredPassengers.filter((p) => p.checked_in).length;
    const gathered = filteredPassengers.filter((p) => p.checked_in_gathering).length;
    return { total, boarded, gathered };
  }, [filteredPassengers]);

  // Auth Screen component
  if (!isAuthenticated) {
    return (
      <div className="min-h-screen bg-slate-50 flex flex-col justify-between">
        <Header />
        <main className="flex-grow flex items-center justify-center px-4 py-12 pt-[81px]">
          <div className="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 border border-slate-100 transition-all hover:shadow-2xl">
            <div className="text-center mb-8">
              <div className="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h2 className="text-2xl font-bold text-slate-800">Xác thực nhân viên</h2>
              <p className="text-sm text-slate-500 mt-1">Vui lòng nhập mã PIN nhân viên để truy cập công cụ check-in</p>
            </div>

            <form onSubmit={handleAuthSubmit} className="space-y-6">
              <div>
                <label className="block text-sm font-semibold text-slate-700 mb-2">Mã PIN nhân viên</label>
                <input
                  type="password"
                  value={pinCode}
                  onChange={(e) => setPinCode(e.target.value)}
                  placeholder="Nhập mã PIN"
                  className="w-full px-4 py-3.5 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-center font-bold tracking-widest text-lg"
                  required
                />
              </div>

              {authError && (
                <div className="p-3 bg-red-50 text-red-600 rounded-xl text-sm font-medium text-center border border-red-100">
                  {authError}
                </div>
              )}

              <button
                type="submit"
                disabled={authLoading}
                className="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg hover:shadow-xl transition-all active:scale-[0.98] flex items-center justify-center gap-2"
              >
                {authLoading ? (
                  <>
                    <svg className="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    Đang xác thực...
                  </>
                ) : (
                  "Đăng nhập hệ thống"
                )}
              </button>
            </form>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col justify-between">
      <Header />
      <main className="flex-grow pt-[113px] pb-8 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          {/* Header section */}
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
              <h1 className="text-3xl font-extrabold text-slate-900 tracking-tight">Điểm danh Check-in</h1>
              <p className="text-slate-500 mt-1">Dành cho Hướng dẫn viên và Điều hành xe Đôi Dép Adventure</p>
            </div>
            <div>
              <button
                onClick={handleLogout}
                className="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 text-sm font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-sm"
              >
                Đăng xuất nhân viên
              </button>
            </div>
          </div>

          {/* Quick Statistics */}
          <div className="grid grid-cols-3 gap-4 mb-6">
            <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 text-center">
              <span className="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng số khách</span>
              <span className="block text-2xl sm:text-3xl font-extrabold text-slate-800 mt-1">{stats.total}</span>
            </div>
            <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 text-center">
              <span className="block text-xs font-semibold text-emerald-500 uppercase tracking-wider">Đã lên xe</span>
              <span className="block text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1">{stats.boarded}</span>
            </div>
            <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 text-center">
              <span className="block text-xs font-semibold text-indigo-500 uppercase tracking-wider">Đã tập trung</span>
              <span className="block text-2xl sm:text-3xl font-extrabold text-indigo-600 mt-1">{stats.gathered}</span>
            </div>
          </div>

          {/* Filter Bar */}
          <div className="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-8 space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-12 gap-4">
              {/* Tour select filter */}
              <div className="md:col-span-3">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Chọn Tour</label>
                <select
                  value={selectedTourId}
                  onChange={(e) => {
                    setSelectedTourId(e.target.value ? Number(e.target.value) : "");
                    setSelectedDepartureDate(""); // Reset departure date when tour changes
                  }}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                >
                  <option value="">Tất cả các tour</option>
                  {tours.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* Departure date filter */}
              <div className="md:col-span-3">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ngày khởi hành</label>
                <select
                  value={selectedDepartureDate}
                  onChange={(e) => setSelectedDepartureDate(e.target.value)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                >
                  <option value="">Tất cả ngày</option>
                  {availableDepartureDates.map((date) => (
                    <option key={date} value={date}>
                      {new Date(date).toLocaleDateString("vi-VN", { weekday: "short", day: "numeric", month: "numeric" })}
                    </option>
                  ))}
                </select>
              </div>

              {/* Text search filter */}
              <div className="md:col-span-3 relative">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tìm kiếm hành khách</label>
                <div className="relative">
                  <SearchIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Họ tên, SĐT, mã đơn..."
                    className="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                  />
                  {searchQuery && (
                    <button
                      onClick={() => setSearchQuery("")}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                    >
                      <CloseIcon className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>
              </div>

              {/* Boarding state filter */}
              <div className="md:col-span-1">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Lên xe</label>
                <select
                  value={boardingFilter}
                  onChange={(e) => setBoardingFilter(e.target.value as any)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                >
                  <option value="all">Tất cả</option>
                  <option value="checked">Đã lên xe</option>
                  <option value="unchecked">Chưa lên xe</option>
                </select>
              </div>

              {/* Gathering state filter */}
              <div className="md:col-span-2">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Điểm tập trung</label>
                <select
                  value={gatheringFilter}
                  onChange={(e) => setGatheringFilter(e.target.value as any)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                >
                  <option value="all">Tất cả</option>
                  <option value="checked">Đã tập trung</option>
                  <option value="unchecked">Chưa tập trung</option>
                </select>
              </div>
            </div>
          </div>

          {/* Main List */}
          {error && (
            <div className="p-4 bg-red-50 text-red-700 border border-red-100 rounded-2xl text-center mb-6">
              {error}
            </div>
          )}

          {loading ? (
            <div className="flex flex-col items-center justify-center py-20">
              <svg className="animate-spin h-10 w-10 text-emerald-600 mb-3" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              <p className="text-slate-500 text-sm font-medium">Đang tải danh sách thành viên...</p>
            </div>
          ) : filteredPassengers.length === 0 ? (
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center text-slate-500">
              <svg className="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <p className="font-semibold text-slate-700">Không tìm thấy thành viên nào</p>
              <p className="text-sm text-slate-400 mt-1">Thử đổi từ khóa tìm kiếm hoặc chọn lọc bộ lọc khác</p>
            </div>
          ) : (
            <div className="space-y-4">
              {/* Mobile Card list */}
              <div className="block sm:hidden space-y-4">
                {filteredPassengers.map((p) => {
                  const uniqueId = `${p.booking_id}_${p.passenger_index}`;
                  return (
                    <div
                      key={p.id}
                      className="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3 relative overflow-hidden"
                    >
                      {/* Booking Code tag */}
                      <div className="flex justify-between items-start">
                        <span className="inline-block text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                          {p.booking_code}
                        </span>
                        {p.seat && (
                          <span className="inline-block text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100 px-2 py-0.5 rounded-md">
                            Ghế: {p.seat}
                          </span>
                        )}
                      </div>

                      {/* Info lines */}
                      <div>
                        <h3 className="font-bold text-slate-800 text-base">{p.full_name}</h3>
                        <p className="text-xs text-slate-500 mt-0.5">{p.tour_name}</p>
                      </div>

                      <div className="grid grid-cols-2 gap-2 text-xs text-slate-600 pt-1 border-t border-slate-50">
                        <div>
                          <span className="block text-[10px] text-slate-400 font-semibold uppercase">Số điện thoại</span>
                          <span className="font-medium">
                            {p.phone ? (
                              <a href={`tel:${p.phone}`} className="font-bold text-emerald-600 hover:text-emerald-700 underline decoration-dotted">
                                {p.phone}
                              </a>
                            ) : (
                              <span className="text-slate-400">Không có</span>
                            )}
                          </span>
                        </div>
                        <div>
                          <span className="block text-[10px] text-slate-400 font-semibold uppercase">Điểm đón</span>
                          <span className="font-medium truncate block max-w-full" title={p.pickup_point}>
                            {p.pickup_point || "Tự túc"}
                          </span>
                        </div>
                      </div>

                      {/* Quick Contact & Remind Row */}
                      <div className="flex gap-2">
                        {p.phone && (
                          <a
                            href={`tel:${p.phone}`}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-semibold transition-colors"
                          >
                            <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                              <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Gọi điện
                          </a>
                        )}
                        <button
                          onClick={() => handleRemindGather(p)}
                          disabled={sendingRemindIds[`${p.booking_id}_${p.passenger_index}`]}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 disabled:opacity-50 rounded-lg text-xs font-semibold transition-all active:scale-[0.98]"
                        >
                          {sendingRemindIds[`${p.booking_id}_${p.passenger_index}`] ? (
                            <>
                              <svg className="animate-spin h-3.5 w-3.5 text-current" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                              </svg>
                              Đang gửi...
                            </>
                          ) : (
                            <>
                              <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                              </svg>
                              Nhắc tập trung
                            </>
                          )}
                        </button>
                      </div>

                      {p.health_status && (
                        <div className="bg-red-50/50 border border-red-100/50 p-2 rounded-xl text-xs text-red-700">
                          <span className="font-semibold">Bệnh lý:</span> {p.health_status}
                        </div>
                      )}

                      {/* Action buttons */}
                      <div className="grid grid-cols-2 gap-3 pt-3 border-t border-slate-50">
                        <button
                          onClick={() => handleToggleCheckin(p, "boarding")}
                          disabled={updatingIds[`${uniqueId}_boarding`]}
                          className={`w-full py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.98] disabled:opacity-50 ${
                            p.checked_in
                              ? "bg-emerald-600 text-white hover:bg-emerald-700"
                              : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                          }`}
                        >
                          {updatingIds[`${uniqueId}_boarding`] ? (
                            <svg className="animate-spin h-3.5 w-3.5 text-current" fill="none" viewBox="0 0 24 24">
                              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                          ) : p.checked_in ? (
                            <>
                              <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                              </svg>
                              Đã lên xe
                            </>
                          ) : (
                            "Check-in Lên xe"
                          )}
                        </button>

                        <button
                          onClick={() => handleToggleCheckin(p, "gathering")}
                          disabled={updatingIds[`${uniqueId}_gathering`]}
                          className={`w-full py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.98] disabled:opacity-50 ${
                            p.checked_in_gathering
                              ? "bg-indigo-600 text-white hover:bg-indigo-700"
                              : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                          }`}
                        >
                          {updatingIds[`${uniqueId}_gathering`] ? (
                            <svg className="animate-spin h-3.5 w-3.5 text-current" fill="none" viewBox="0 0 24 24">
                              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                          ) : p.checked_in_gathering ? (
                            <>
                              <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                              </svg>
                              Đã tập trung
                            </>
                          ) : (
                            "Check-in Tập trung"
                          )}
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Desktop Table List */}
              <div className="hidden sm:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <table className="min-w-full divide-y divide-slate-100">
                  <thead className="bg-slate-50">
                    <tr>
                      <th className="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Họ và Tên</th>
                      <th className="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Mã đơn / Ghế</th>
                      <th className="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Thông tin Tour</th>
                      <th className="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Điểm đón / SĐT</th>
                      <th className="px-6 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-wider w-44">Check-in Lên xe</th>
                      <th className="px-6 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-wider w-44">Check-in Tập trung</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-slate-100">
                    {filteredPassengers.map((p) => {
                      const uniqueId = `${p.booking_id}_${p.passenger_index}`;
                      return (
                        <tr key={p.id} className="hover:bg-slate-50/50 transition-colors">
                          <td className="px-6 py-4 whitespace-nowrap">
                            <div className="font-bold text-slate-900">{p.full_name}</div>
                            {p.health_status && (
                              <div className="text-xs text-red-600 mt-1 flex items-center gap-1">
                                <span className="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0 animate-ping"></span>
                                Bệnh lý: {p.health_status}
                              </div>
                            )}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className="inline-block text-[11px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                              {p.booking_code}
                            </span>
                            {p.seat && (
                              <div className="text-xs font-bold text-amber-700 mt-1">
                                Ghế: {p.seat}
                              </div>
                            )}
                          </td>
                          <td className="px-6 py-4">
                            <div className="text-sm font-semibold text-slate-700 max-w-xs truncate" title={p.tour_name}>
                              {p.tour_name}
                            </div>
                            <div className="text-xs text-slate-400 mt-0.5">Ngày đi: {p.departure_date}</div>
                          </td>
                          <td className="px-6 py-4">
                            <div className="text-sm flex flex-col gap-1.5">
                              {p.phone ? (
                                <a
                                  href={`tel:${p.phone}`}
                                  className="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:text-emerald-700 transition-colors w-fit"
                                >
                                  <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                  </svg>
                                  {p.phone}
                                </a>
                              ) : (
                                <span className="text-slate-400 text-xs">Không có SĐT</span>
                              )}
                              <button
                                onClick={() => handleRemindGather(p)}
                                disabled={sendingRemindIds[`${p.booking_id}_${p.passenger_index}`]}
                                className="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 disabled:opacity-50 transition-colors w-fit"
                              >
                                {sendingRemindIds[`${p.booking_id}_${p.passenger_index}`] ? (
                                  <>
                                    <svg className="animate-spin h-3 w-3 text-current" fill="none" viewBox="0 0 24 24">
                                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    Đang gửi...
                                  </>
                                ) : (
                                  <>
                                    <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                      <path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Nhắc tập trung
                                  </>
                                )}
                              </button>
                            </div>
                            <div className="text-xs text-slate-400 mt-1.5 truncate max-w-[180px]" title={p.pickup_point}>
                              Đón: {p.pickup_point || "Tự túc"}
                            </div>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-center">
                            <button
                              onClick={() => handleToggleCheckin(p, "boarding")}
                              disabled={updatingIds[`${uniqueId}_boarding`]}
                              className={`inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-[0.98] w-36 disabled:opacity-50 ${
                                p.checked_in
                                  ? "bg-emerald-600 text-white hover:bg-emerald-700"
                                  : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                              }`}
                            >
                              {updatingIds[`${uniqueId}_boarding`] ? (
                                <svg className="animate-spin h-3.5 w-3.5 text-current" fill="none" viewBox="0 0 24 24">
                                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                              ) : p.checked_in ? (
                                <>
                                  <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                  </svg>
                                  Đã lên xe
                                </>
                              ) : (
                                "Lên xe (Bus)"
                              )}
                            </button>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-center">
                            <button
                              onClick={() => handleToggleCheckin(p, "gathering")}
                              disabled={updatingIds[`${uniqueId}_gathering`]}
                              className={`inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-[0.98] w-36 disabled:opacity-50 ${
                                p.checked_in_gathering
                                  ? "bg-indigo-600 text-white hover:bg-indigo-700"
                                  : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                              }`}
                            >
                              {updatingIds[`${uniqueId}_gathering`] ? (
                                <svg className="animate-spin h-3.5 w-3.5 text-current" fill="none" viewBox="0 0 24 24">
                                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                              ) : p.checked_in_gathering ? (
                                <>
                                  <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                  </svg>
                                  Đã tập trung
                                </>
                              ) : (
                                "Đã tập trung"
                              )}
                            </button>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      </main>
      <Footer />
    </div>
  );
}
