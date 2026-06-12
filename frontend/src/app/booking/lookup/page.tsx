"use client";

import { useState } from "react";
import Link from "next/link";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { lookupBooking, BookingLookupRow } from "@/lib/api";

const STATUS_LABELS: Record<string, { label: string; className: string }> = {
  pending: { label: "Chờ xác nhận", className: "bg-amber-100 text-amber-800" },
  confirmed: { label: "Đã xác nhận", className: "bg-emerald-100 text-emerald-800" },
  cancelled: { label: "Đã hủy", className: "bg-rose-100 text-rose-700" },
  completed: { label: "Hoàn thành", className: "bg-blue-100 text-blue-800" },
  no_show: { label: "Vắng mặt", className: "bg-gray-200 text-gray-700" },
};

const PAYMENT_STATUS_LABELS: Record<string, { label: string; className: string }> = {
  unpaid: { label: "Chưa thanh toán", className: "bg-rose-50 text-rose-700 border-rose-200" },
  partial: { label: "Đã cọc", className: "bg-amber-50 text-amber-700 border-amber-200" },
  paid: { label: "Đã thanh toán", className: "bg-emerald-50 text-emerald-700 border-emerald-200" },
  refunded: { label: "Đã hoàn tiền", className: "bg-slate-50 text-slate-600 border-slate-200" },
};

export default function BookingLookupPage() {
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [results, setResults] = useState<BookingLookupRow[] | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    const trimmedEmail = email.trim();
    const trimmedPhone = phone.trim();

    if (!trimmedEmail && !trimmedPhone) {
      setError("Vui lòng nhập email hoặc số điện thoại đã dùng khi đặt tour.");
      return;
    }

    setLoading(true);
    try {
      const data = await lookupBooking({
        email: trimmedEmail || undefined,
        phone: trimmedPhone || undefined,
      });
      setResults(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Có lỗi xảy ra, vui lòng thử lại.");
      setResults([]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col bg-gray-50">
      <Header />

      <main className="flex-grow pt-[81px] pb-16">
        <div className="container mx-auto px-4 py-10 max-w-3xl">
          <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">Tra cứu đơn đặt tour</h1>
          <p className="text-gray-600 mb-8">
            Nhập email hoặc số điện thoại bạn đã dùng khi đặt tour để xem danh sách đơn.
          </p>

          <form onSubmit={handleSubmit} className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <div className="grid sm:grid-cols-2 gap-4">
              <div>
                <label htmlFor="lookup-email" className="block text-sm font-medium text-gray-700 mb-2">
                  Email
                </label>
                <input
                  id="lookup-email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="email@example.com"
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
              <div>
                <label htmlFor="lookup-phone" className="block text-sm font-medium text-gray-700 mb-2">
                  Số điện thoại
                </label>
                <input
                  id="lookup-phone"
                  type="tel"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  placeholder="0xxx xxx xxx"
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
            </div>

            {error && (
              <p className="mt-4 text-sm text-rose-600">{error}</p>
            )}

            <button
              type="submit"
              disabled={loading}
              className="mt-6 px-6 py-3 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? "Đang tra cứu..." : "Tra cứu"}
            </button>
          </form>

          {results !== null && (
            <div>
              <h2 className="text-lg font-bold text-gray-900 mb-4">
                Kết quả tra cứu ({results.length})
              </h2>

              {results.length === 0 ? (
                <div className="bg-white rounded-2xl border border-gray-100 p-8 text-center">
                  <p className="text-gray-600">Không tìm thấy đơn đặt tour nào trùng khớp.</p>
                  <p className="text-sm text-gray-500 mt-2">Vui lòng kiểm tra lại email hoặc số điện thoại đã nhập.</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {results.map((row) => {
                    const statusInfo = STATUS_LABELS[row.status] ?? { label: row.status, className: "bg-gray-100 text-gray-700" };
                    const paymentInfo = PAYMENT_STATUS_LABELS[row.payment_status] ?? { label: row.payment_status, className: "bg-gray-50 text-gray-600 border-gray-200" };
                    return (
                      <Link
                        key={row.booking_id}
                        href={`/booking/success?bookingId=${encodeURIComponent(row.booking_id)}&email=${encodeURIComponent(row.email)}`}
                        className="block bg-white rounded-2xl border border-gray-100 hover:border-emerald-300 hover:shadow-md transition-all p-6"
                      >
                        <div className="flex items-start justify-between gap-4 mb-3">
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Mã đặt tour</p>
                            <p className="font-mono font-bold text-lg text-blue-700">{row.booking_id}</p>
                          </div>
                          <span className={`text-xs font-bold px-3 py-1 rounded-full ${statusInfo.className}`}>
                            {statusInfo.label}
                          </span>
                        </div>

                        <p className="font-semibold text-gray-900 mb-2">{row.tour_name}</p>

                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                          <div>
                            <p className="text-xs text-gray-500">Ngày khởi hành</p>
                            <p className="font-medium text-gray-800">{row.departure_date}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500">Số khách</p>
                            <p className="font-medium text-gray-800">{row.passengers_count}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500">Tổng tiền</p>
                            <p className="font-semibold text-emerald-700">{row.total_amount.toLocaleString("vi-VN")}đ</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500">Thanh toán</p>
                            <span className={`inline-block text-xs font-semibold px-2 py-0.5 rounded-md border ${paymentInfo.className}`}>
                              {paymentInfo.label}
                            </span>
                          </div>
                        </div>
                      </Link>
                    );
                  })}
                </div>
              )}
            </div>
          )}
        </div>
      </main>

      <Footer />
    </div>
  );
}
