"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { CheckIcon, CalendarIcon, UsersIcon, PhoneIcon, ArrowRightIcon } from "@/components/icons";

interface BookingSuccessContentProps {
  bookingId: string;
  tourName: string;
  date: string;
  participants: string;
  total: string;
}

export function BookingSuccessContent({
  bookingId,
  tourName,
  date,
  participants,
  total,
}: BookingSuccessContentProps) {
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

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-6"
      >
        <h3 className="font-bold text-gray-900 mb-4">Những bước tiếp theo</h3>
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
          href="https://zalo.me/0909123456"
          target="_blank"
          rel="noopener noreferrer"
          className="flex items-center justify-center gap-3 p-5 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-200 transition-all group"
        >
          <div className="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
            <svg className="w-7 h-7 text-white" viewBox="0 0 256 282" fill="currentColor">
              <path d="M128.001 0C57.317 0 0 57.317 0 128.001s57.317 128.001 128.001 128.001S256.001 198.685 256.001 128.001 198.685 0 128.001 0zm47.411 188.851c-2.619 5.895-9.688 9.687-15.585 9.687-4.543 0-8.754-2.282-11.374-4.546l-12.427 9.688c.001 12.079 9.688 21.768 21.768 21.768h21.768c12.079 0 21.768-9.689 21.768-21.768V144.465l-18.378-11.373v-12.079c0-12.08-9.689-21.769-21.769-21.769h-13.314c-1.644-4.545-5.898-7.826-11.018-7.826-5.12 0-9.374 3.281-11.018 7.826h-13.314c-12.08 0-21.769 9.689-21.769 21.769v53.846c-19.378 0-35.536 15.586-35.536 34.964 0 11.373 5.12 21.768 13.313 29.23v15.585c0 8.755 5.12 16.837 13.314 21.768v8.754c0 4.545 3.637 8.181 8.182 8.181 1.282 0 2.564-.321 3.819-.965l9.688 2.955h.643c12.08 0 21.769-9.688 21.769-21.768v-7.826c7.825-4.93 13.313-13.314 13.313-21.768v-15.585c8.193-3.928 13.313-11.373 13.313-19.23v-34.964c0-4.545-3.638-8.181-8.182-8.181s-8.182 3.636-8.182 8.181v35.536c0 8.755-5.12 16.837-13.313 21.768v15.585c0 12.08-9.689 21.768-21.768 21.768-1.644 0-3.281-.321-4.93-.965-2.619 5.12-6.874 9.374-11.994 9.374-5.12 0-9.375-3.637-11.995-8.754l-11.995 4.93c-3.282 1.282-7.19-.323-8.472-3.637-1.282-3.282.321-7.19 3.637-8.472l11.373-4.93c-.965-3.282-.965-7.19.321-10.474l12.431-9.688c-1.282-3.282-.965-6.874.965-9.688 2.619-2.955 6.234-4.546 9.688-4.546 1.282 0 2.564.323 3.819.965l5.898 1.927c.643-1.282 1.927-2.282 3.282-3.282 3.281-1.927 7.189-2.619 11.018-1.927 3.281.965 6.233 2.955 8.193 5.898 1.927 2.955 3.281 6.874 2.619 10.473l16.519 10.15v23.625c0 12.08 9.689 21.768 21.769 21.768h21.768c12.08 0 21.769-9.689 21.769-21.768v-53.846c19.376 0 35.535-15.586 35.535-34.964 0-19.378-15.585-34.964-34.962-34.964z" />
            </svg>
          </div>
          <div className="text-left">
            <p className="font-semibold text-gray-900">Liên hệ Zalo</p>
            <p className="text-sm text-gray-500">0909 123 456</p>
          </div>
          <ArrowRightIcon className="w-5 h-5 text-gray-400 group-hover:text-emerald-500 transition-colors" />
        </a>

        <a
          href="tel:0909123456"
          className="flex items-center justify-center gap-3 p-5 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-200 transition-all group"
        >
          <div className="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center">
            <PhoneIcon className="w-6 h-6 text-white" />
          </div>
          <div className="text-left">
            <p className="font-semibold text-gray-900">Gọi trực tiếp</p>
            <p className="text-sm text-gray-500">0909 123 456</p>
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
