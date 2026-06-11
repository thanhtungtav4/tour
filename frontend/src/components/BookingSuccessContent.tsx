"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { CheckIcon, CalendarIcon, UsersIcon, PhoneIcon, ArrowRightIcon } from "@/components/icons";
import { getBooking, BookingDetail } from "@/lib/api";
import { useSettings } from "@/hooks/useSettings";

interface BookingSuccessContentProps {
  bookingId: string;
  tourName: string;
  date: string;
  participants: string;
  total: string;
}

const CopyIcon = ({ className }: { className?: string }) => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="24"
    height="24"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    className={className}
  >
    <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
  </svg>
);

const CheckCircleMiniIcon = ({ className }: { className?: string }) => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="24"
    height="24"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    className={className}
  >
    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
    <path d="m9 11 3 3L22 4" />
  </svg>
);

export function BookingSuccessContent({
  bookingId,
  tourName,
  date,
  participants,
  total,
}: BookingSuccessContentProps) {
  const [booking, setBooking] = useState<BookingDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [copiedField, setCopiedField] = useState<string | null>(null);
  const { settings } = useSettings();

  const handleCopy = (text: string, field: string) => {
    navigator.clipboard.writeText(text);
    setCopiedField(field);
    setTimeout(() => setCopiedField(null), 2000);
  };

  useEffect(() => {
    getBooking(bookingId)
      .then((data) => {
        setBooking(data);
      })
      .catch((err) => {
        console.error("Error fetching booking details:", err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [bookingId]);

  const formattedDate = date
    ? new Date(date).toLocaleDateString("vi-VN", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      })
    : "Đang cập nhật";

  return (
    <div className="container mx-auto px-4 py-8 max-w-3xl">
      <motion.div
        initial={{ scale: 0 }}
        animate={{ scale: 1 }}
        transition={{ type: "spring", duration: 0.6 }}
        className="text-center mb-8"
      >
        <div className="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <div className="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center">
            <CheckIcon className="w-10 h-10 text-white" />
          </div>
        </div>
        <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
          Đặt tour thành công!
        </h1>
        <p className="text-gray-600">
          Cảm ơn bạn đã đặt tour. Chúng tôi sẽ liên hệ xác nhận trong ít phút.
        </p>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="bg-white rounded-2xl shadow-lg p-6 lg:p-8 mb-6"
      >
        <div className="flex items-center justify-between mb-6 pb-4 border-b">
          <div>
            <p className="text-sm text-gray-500">Mã đặt tour</p>
            <p className="text-xl font-bold text-emerald-600">{bookingId}</p>
          </div>
          <div className="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold">
            Đang xử lý
          </div>
        </div>

        <div className="space-y-4">
          <div className="flex items-start gap-4">
            <div className="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
              <CalendarIcon className="w-5 h-5 text-gray-600" />
            </div>
            <div>
              <p className="text-sm text-gray-500">Tour</p>
              <p className="font-semibold text-gray-900">{decodeURIComponent(tourName)}</p>
            </div>
          </div>

          <div className="flex items-start gap-4">
            <div className="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
              <CalendarIcon className="w-5 h-5 text-gray-600" />
            </div>
            <div>
              <p className="text-sm text-gray-500">Ngày khởi hành</p>
              <p className="font-semibold text-gray-900">{formattedDate}</p>
            </div>
          </div>

          <div className="flex items-start gap-4">
            <div className="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
              <UsersIcon className="w-5 h-5 text-gray-600" />
            </div>
            <div>
              <p className="text-sm text-gray-500">Số người</p>
              <p className="font-semibold text-gray-900">{participants} người</p>
            </div>
          </div>

          <div className="pt-4 border-t">
            <div className="flex items-center justify-between">
              <span className="text-gray-500">Tổng chi phí</span>
              <span className="text-2xl font-bold text-emerald-600">
                {parseInt(total).toLocaleString("vi-VN")}đ
              </span>
            </div>
          </div>
        </div>
      </motion.div>

      {booking && booking.payment.method === "transfer" && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.25 }}
          className="bg-white rounded-2xl shadow-lg p-6 lg:p-8 mb-6 border border-emerald-100"
        >
          {booking.payment.status === "paid" ? (
            <div className="text-center py-6">
              <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <CheckIcon className="w-8 h-8 text-emerald-600" />
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Thanh toán hoàn tất!</h3>
              <p className="text-gray-600">Hệ thống đã xác nhận thanh toán tự động cho đơn hàng này.</p>
            </div>
          ) : !booking.payment.bank_info ? (
            <div className="rounded-2xl border border-amber-200 bg-amber-50 p-6">
              <h3 className="font-bold text-amber-900 mb-2 text-lg flex items-center gap-2">
                <span className="w-2.5 h-6 bg-amber-500 rounded-full inline-block"></span>
                Thông tin thanh toán đang được chuẩn bị
              </h3>
              <p className="text-sm text-amber-800">
                Quản trị viên chưa cấu hình thông tin ngân hàng. Vui lòng liên hệ tổng đài để nhận hướng dẫn chuyển khoản cho đơn này.
              </p>
            </div>
          ) : (
            <div>
              <h3 className="font-bold text-gray-900 mb-2 text-lg lg:text-xl border-b pb-3 border-gray-100 flex items-center gap-2">
                <span className="w-2.5 h-6 bg-emerald-500 rounded-full inline-block"></span>
                Hướng dẫn Thanh toán Chuyển khoản
              </h3>

              <div className="grid md:grid-cols-2 gap-8 items-center mt-6">
                {/* QR Code Container */}
                <div className="flex flex-col items-center justify-center bg-gradient-to-br from-emerald-50/50 to-blue-50/50 p-6 rounded-2xl border border-emerald-100/50 text-center">
                  <p className="text-sm font-semibold text-emerald-800 mb-3">Mở App Ngân hàng để Quét mã QR</p>
                  <div className="bg-white p-4 rounded-2xl shadow-md border border-emerald-100 relative group overflow-hidden transition-all duration-300 hover:shadow-lg">
                    {booking.payment.bank_info.qr_url ? (
                      <img
                        src={booking.payment.bank_info.qr_url}
                        alt={`Mã QR VietQR ${booking.payment.bank_info.bank_name}`}
                        className="w-56 h-56 object-contain"
                      />
                    ) : (
                      <div className="w-56 h-56 bg-gray-100 flex items-center justify-center text-gray-400">
                        Đang tạo mã QR...
                      </div>
                    )}
                  </div>
                  <p className="text-xs text-gray-500 mt-3 max-w-[240px]">
                    Quét mã này bằng bất kỳ ứng dụng ngân hàng nào để tự động điền STK, số tiền & nội dung.
                  </p>

                  {booking.payment.bank_info.deeplink && (
                    <a
                      href={booking.payment.bank_info.deeplink}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow"
                    >
                      <span>Thanh toán qua App</span>
                      <ArrowRightIcon className="w-4 h-4" />
                    </a>
                  )}
                </div>

                {/* Account Details */}
                <div className="space-y-4">
                  <p className="text-sm font-semibold text-gray-700">Hoặc chuyển khoản thủ công:</p>

                  <div className="space-y-3">
                    {/* Bank Name */}
                    <div className="flex justify-between items-center py-2.5 px-3 bg-gray-50 rounded-xl border border-gray-100">
                      <div>
                        <span className="text-xs text-gray-500 block">Ngân hàng</span>
                        <span className="font-semibold text-gray-900 text-sm">{booking.payment.bank_info.bank_name}</span>
                      </div>
                    </div>

                    {/* Account Number */}
                    <div className="flex justify-between items-center py-2.5 px-3 bg-gray-50 rounded-xl border border-gray-100">
                      <div>
                        <span className="text-xs text-gray-500 block">Số tài khoản</span>
                        <span className="font-mono font-bold text-gray-900 text-base">{booking.payment.bank_info.account_no}</span>
                      </div>
                      <button
                        onClick={() => handleCopy(booking.payment.bank_info!.account_no, "account")}
                        className="p-1.5 hover:bg-gray-200 rounded-lg text-gray-500 hover:text-gray-800 transition-colors flex items-center gap-1 text-xs"
                      >
                        {copiedField === "account" ? (
                          <>
                            <CheckCircleMiniIcon className="w-4 h-4 text-emerald-600" />
                            <span className="text-emerald-600 font-medium">Đã chép</span>
                          </>
                        ) : (
                          <>
                            <CopyIcon className="w-4 h-4" />
                            <span>Sao chép</span>
                          </>
                        )}
                      </button>
                    </div>

                    {/* Account Name */}
                    <div className="flex justify-between items-center py-2.5 px-3 bg-gray-50 rounded-xl border border-gray-100">
                      <div>
                        <span className="text-xs text-gray-500 block">Tên chủ tài khoản</span>
                        <span className="font-semibold text-gray-900 text-sm uppercase">{booking.payment.bank_info.account_name}</span>
                      </div>
                    </div>

                    {/* Amount */}
                    <div className="flex justify-between items-center py-2.5 px-3 bg-gray-50 rounded-xl border border-gray-100">
                      <div>
                        <span className="text-xs text-gray-500 block">Số tiền</span>
                        <span className="font-bold text-emerald-600 text-lg">
                          {booking.payment.bank_info.amount.toLocaleString("vi-VN")}đ
                        </span>
                      </div>
                      <button
                        onClick={() => handleCopy(String(booking.payment.bank_info!.amount), "amount")}
                        className="p-1.5 hover:bg-gray-200 rounded-lg text-gray-500 hover:text-gray-800 transition-colors flex items-center gap-1 text-xs"
                      >
                        {copiedField === "amount" ? (
                          <>
                            <CheckCircleMiniIcon className="w-4 h-4 text-emerald-600" />
                            <span className="text-emerald-600 font-medium">Đã chép</span>
                          </>
                        ) : (
                          <>
                            <CopyIcon className="w-4 h-4" />
                            <span>Sao chép</span>
                          </>
                        )}
                      </button>
                    </div>

                    {/* Content */}
                    <div className="flex justify-between items-center py-2.5 px-3 bg-emerald-50 rounded-xl border border-emerald-100">
                      <div>
                        <span className="text-xs text-emerald-700 block">Nội dung chuyển khoản</span>
                        <span className="font-mono font-bold text-blue-600 text-base">{booking.payment.bank_info.content}</span>
                      </div>
                      <button
                        onClick={() => handleCopy(booking.payment.bank_info!.content, "content")}
                        className="p-1.5 hover:bg-emerald-100 rounded-lg text-emerald-700 hover:text-emerald-950 transition-colors flex items-center gap-1 text-xs"
                      >
                        {copiedField === "content" ? (
                          <>
                            <CheckCircleMiniIcon className="w-4 h-4 text-emerald-600" />
                            <span className="text-emerald-600 font-medium">Đã chép</span>
                          </>
                        ) : (
                          <>
                            <CopyIcon className="w-4 h-4" />
                            <span>Sao chép</span>
                          </>
                        )}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {/* Notice Banner */}
              <div className="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex gap-3 items-start">
                <span className="text-lg leading-none">⚠️</span>
                <p className="text-xs text-amber-800 leading-relaxed">
                  <strong>Lưu ý cực kỳ quan trọng:</strong> Vui lòng quét mã QR hoặc chuyển khoản thủ công bằng cách nhập <strong>chính xác số tiền</strong> và <strong>nội dung chuyển khoản</strong> ở trên để hệ thống tự động xác nhận báo có. Bất kỳ sự sai lệch nào sẽ dẫn đến chậm trễ hoặc cần đối soát thủ công.
                </p>
              </div>
            </div>
          )}
        </motion.div>
      )}

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-6"
      >
        <h2 className="font-bold text-gray-900 mb-4">Những bước tiếp theo</h2>
        <div className="space-y-3">
          <div className="flex items-start gap-3">
            <div className="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">1</div>
            <p className="text-gray-700">Nhân viên Đôi Dép Adventure sẽ liên hệ xác nhận qua điện thoại trong 15-30 phút</p>
          </div>
          <div className="flex items-start gap-3">
            <div className="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">2</div>
            <p className="text-gray-700">Bạn sẽ nhận được thông tin thanh toán và hướng dẫn qua Zalo</p>
          </div>
          <div className="flex items-start gap-3">
            <div className="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">3</div>
            <p className="text-gray-700">Đến ngày khởi hành, HDV sẽ liên hệ bạn trước 30 phút</p>
          </div>
        </div>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.4 }}
        className="grid sm:grid-cols-2 gap-4 mb-8"
      >
        <a
          href={settings?.zalo_link || "https://zalo.me/0961804359"}
          target="_blank"
          rel="noopener noreferrer"
          className="flex items-center justify-center gap-3 p-5 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-200 transition-all group"
        >
          <div className="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
            <span className="text-white text-xl font-bold">Z</span>
          </div>
          <div className="text-left">
            <p className="font-semibold text-gray-900">Liên hệ Zalo</p>
            <p className="text-sm text-gray-500">{settings?.hotline || "096 180 43 59"}</p>
          </div>
          <ArrowRightIcon className="w-5 h-5 text-gray-400 group-hover:text-emerald-500 transition-colors" />
        </a>

        <a
          href={`tel:${settings?.hotline ? settings.hotline.replace(/\s+/g, "") : "0961804359"}`}
          className="flex items-center justify-center gap-3 p-5 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-200 transition-all group"
        >
          <div className="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center">
            <PhoneIcon className="w-6 h-6 text-white" />
          </div>
          <div className="text-left">
            <p className="font-semibold text-gray-900">Gọi trực tiếp</p>
            <p className="text-sm text-gray-500">{settings?.hotline || "096 180 43 59"}</p>
          </div>
          <ArrowRightIcon className="w-5 h-5 text-gray-400 group-hover:text-emerald-500 transition-colors" />
        </a>
      </motion.div>

      <div className="text-center">
        <Link
          href="/"
          className="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium transition-colors"
        >
          ← Quay về trang chủ
        </Link>
      </div>
    </div>
  );
}
