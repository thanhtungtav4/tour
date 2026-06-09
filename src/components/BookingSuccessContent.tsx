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
            <span className="text-white text-xl font-bold">Z</span>
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
