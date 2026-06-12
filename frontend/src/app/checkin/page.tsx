"use client";

import { useState, useEffect, useMemo } from "react";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { getCheckinPassengers, toggleCheckin, getTours, CheckinPassenger, TourListItem } from "@/lib/api";
import { SearchIcon, CloseIcon } from "@/components/icons";

export default function CheckinPage() {
  const [passengers, setPassengers] = useState<CheckinPassenger[]>([]);
  const [tours, setTours] = useState<TourListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [updatingId, setUpdatingId] = useState<string | null>(null);

  // Authentication State
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [pinCode, setPinCode] = useState("");
  const [authError, setAuthError] = useState("");

  // Filters State
  const [selectedTourId, setSelectedTourId] = useState<number | "">("");
  const [searchQuery, setSearchQuery] = useState("");
  const [boardingFilter, setBoardingFilter] = useState<"all" | "checked" | "unchecked">("all");
  const [gatheringFilter, setGatheringFilter] = useState<"all" | "checked" | "unchecked">("all");

  // Load configuration and data
  useEffect(() => {
    // Check localStorage for saved PIN authentication
    const savedAuth = localStorage.getItem("staff_checkin_auth");
    if (savedAuth === "true") {
      setIsAuthenticated(true);
    }
  }, []);

  useEffect(() => {
    if (!isAuthenticated) return;

    async function fetchData() {
      try {
        setLoading(true);
        setError("");
        const [passengersData, toursData] = await Promise.all([
          getCheckinPassengers(selectedTourId ? Number(selectedTourId) : undefined),
          getTours({ per_page: 100 }), // Load all active tours
        ]);
        setPassengers(passengersData);
        setTours(toursData.data);
      } catch (err: any) {
        console.error(err);
        setError("Không thể tải danh sách dữ liệu. Vui lòng thử lại sau.");
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, [isAuthenticated, selectedTourId]);

  // Handle PIN code validation
  const handleAuthSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (pinCode === "123456") {
      setIsAuthenticated(true);
      setAuthError("");
      localStorage.setItem("staff_checkin_auth", "true");
    } else {
      setAuthError("Mã PIN không chính xác. Vui lòng thử lại.");
    }
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    setPinCode("");
    localStorage.removeItem("staff_checkin_auth");
  };

  // Toggle check-in status (boarding or gathering)
  const handleToggleCheckin = async (
    passenger: CheckinPassenger,
    type: "boarding" | "gathering"
  ) => {
    const uniqueId = `${passenger.booking_id}_${passenger.passenger_index}`;
    setUpdatingId(`${uniqueId}_${type}`);

    try {
      const currentValue = type === "boarding" ? passenger.checked_in : passenger.checked_in_gathering;
      const targetValue = !currentValue;

      await toggleCheckin(
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
      setUpdatingId(null);
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
        <main className="flex-grow flex items-center justify-center px-4 py-12">
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
                className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg hover:shadow-xl transition-all active:scale-[0.98]"
              >
                Đăng nhập hệ thống
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
      <main className="flex-grow py-8 px-4 sm:px-6 lg:px-8">
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
              <div className="md:col-span-4">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Chọn Tour</label>
                <select
                  value={selectedTourId}
                  onChange={(e) => setSelectedTourId(e.target.value ? Number(e.target.value) : "")}
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

              {/* Text search filter */}
              <div className="md:col-span-4 relative">
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
              <div className="md:col-span-2">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Lên xe (Bus)</label>
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
                          <span className="font-medium">{p.phone || "Không có"}</span>
                        </div>
                        <div>
                          <span className="block text-[10px] text-slate-400 font-semibold uppercase">Điểm đón</span>
                          <span className="font-medium truncate block max-w-full" title={p.pickup_point}>
                            {p.pickup_point || "Tự túc"}
                          </span>
                        </div>
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
                          disabled={updatingId !== null}
                          className={`w-full py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.98] ${
                            p.checked_in
                              ? "bg-emerald-600 text-white hover:bg-emerald-700"
                              : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                          }`}
                        >
                          {updatingId === `${uniqueId}_boarding` ? (
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
                          disabled={updatingId !== null}
                          className={`w-full py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.98] ${
                            p.checked_in_gathering
                              ? "bg-indigo-600 text-white hover:bg-indigo-700"
                              : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                          }`}
                        >
                          {updatingId === `${uniqueId}_gathering` ? (
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
                            <div className="text-sm font-medium text-slate-700">{p.phone || "Không có SĐT"}</div>
                            <div className="text-xs text-slate-400 mt-0.5 truncate max-w-[180px]" title={p.pickup_point}>
                              Đón: {p.pickup_point || "Tự túc"}
                            </div>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-center">
                            <button
                              onClick={() => handleToggleCheckin(p, "boarding")}
                              disabled={updatingId !== null}
                              className={`inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-[0.98] w-36 ${
                                p.checked_in
                                  ? "bg-emerald-600 text-white hover:bg-emerald-700"
                                  : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                              }`}
                            >
                              {updatingId === `${uniqueId}_boarding` ? (
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
                              disabled={updatingId !== null}
                              className={`inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-[0.98] w-36 ${
                                p.checked_in_gathering
                                  ? "bg-indigo-600 text-white hover:bg-indigo-700"
                                  : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                              }`}
                            >
                              {updatingId === `${uniqueId}_gathering` ? (
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
