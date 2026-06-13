"use client";

import Image from "next/image";
import { PhoneIcon, MailIcon } from "@/components/icons";
import Link from "next/link";
import { useEffect, useState } from "react";
import { getTours, TourListItem } from "@/lib/api";
import { getTourImage } from "@/lib/utils";
import { useSettings } from "@/hooks/useSettings";
import { useMenus } from "@/hooks/useMenus";

const policies = [
  { href: "/booking/lookup", label: "Tra cứu đơn đặt tour" },
  { href: "/chinh-sach-an-toan", label: "Chính sách an toàn" },
  { href: "/chinh-sach-huy-ve", label: "Chính sách hủy vé" },
  { href: "/chinh-sach-doi-ve-bao-luu", label: "Chính sách đổi vé, bảo lưu" },
  { href: "/chinh-sach-hoan-tien", label: "Chính sách hoàn tiền" },
];

export function Footer() {
  const { settings } = useSettings();
  const { menus } = useMenus();

  const resolvedPolicies = (menus?.footer && menus.footer.length > 0)
    ? menus.footer.map((item) => ({ href: item.url, label: item.title }))
    : policies;

  const [email, setEmail] = useState("");
  const [subscribed, setSubscribed] = useState(false);
  const [popularTours, setPopularTours] = useState<TourListItem[]>([]);

  useEffect(() => {
    let cancelled = false;
    getTours({ per_page: 4 })
      .then(({ data }) => {
        if (!cancelled) setPopularTours(data);
      })
      .catch(() => {
        if (!cancelled) setPopularTours([]);
      });
    return () => {
      cancelled = true;
    };
  }, []);

  const handleSubscribe = (e: React.FormEvent) => {
    e.preventDefault();
    if (email) {
      setSubscribed(true);
      setEmail("");
      setTimeout(() => setSubscribed(false), 3000);
    }
  };

  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-white text-gray-900 border-t border-gray-200">
      {/* Main Footer */}
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-6">
          {/* Brand Column */}
          <div className="lg:col-span-2">
            <Link href="/" className="inline-flex items-center gap-2.5 mb-4 text-[#0e1425]">
              <div className="relative w-10 h-10 rounded-full overflow-hidden flex items-center justify-center">
                <Image
                  src="/images/logo.png"
                  alt="Đôi Dép Adventure Logo"
                  fill
                  sizes="40px"
                  className="object-cover"
                />
              </div>
              <span className="text-xl font-bold">Đôi Dép Adventure</span>
            </Link>
            <p className="text-sm text-gray-500 mb-6 max-w-sm">
              Siêu tiện - Siêu vui - Siêu Tiết Kiệm. Trải nghiệm trekking, camping và phiêu lưu thiên nhiên tuyệt vời nhất Việt Nam.
            </p>

            {/* Social Links */}
            <div className="flex gap-3 mb-6">
              <a
                href={settings?.facebook_link || "#"}
                target="_blank"
                rel="noopener noreferrer"
                className="w-10 h-10 block hover:scale-110 transition-transform"
                aria-label="Facebook"
              >
                <img
                  src="/images/facebook.svg"
                  alt="Facebook"
                  className="w-full h-full object-contain"
                />
              </a>
              <a
                href="#"
                target="_blank"
                rel="noopener noreferrer"
                className="w-10 h-10 block hover:scale-110 transition-transform"
                aria-label="YouTube"
              >
                <img
                  src="/images/youtube.svg"
                  alt="YouTube"
                  className="w-full h-full object-contain"
                />
              </a>
              <a
                href={settings?.instagram_link || "#"}
                target="_blank"
                rel="noopener noreferrer"
                className="w-10 h-10 block hover:scale-110 transition-transform"
                aria-label="Instagram"
              >
                <img
                  src="/images/instagram.svg"
                  alt="Instagram"
                  className="w-full h-full object-contain"
                />
              </a>
              <a
                href={settings?.zalo_link || "https://zalo.me/0961804359"}
                target="_blank"
                rel="noopener noreferrer"
                className="w-10 h-10 block hover:scale-110 transition-transform"
                aria-label="Zalo"
              >
                <img
                  src="/images/zalo.svg"
                  alt="Zalo"
                  className="w-full h-full object-contain"
                />
              </a>
            </div>

            {/* Newsletter */}
            <div>
              <h4 className="text-sm font-semibold text-[#0e1425] mb-2">Đăng ký nhận ưu đãi</h4>
              <form onSubmit={handleSubscribe} className="flex gap-2">
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="Email của bạn"
                  className="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                  required
                />
                <button
                  type="submit"
                  className="px-4 py-2 bg-emerald-700 text-white text-sm font-medium rounded-lg hover:bg-emerald-800 transition-colors cursor-pointer"
                >
                  {subscribed ? "✓" : "Gửi"}
                </button>
              </form>
              {subscribed && (
                <p className="mt-1 text-xs text-emerald-600">Đã đăng ký thành công!</p>
              )}
            </div>
          </div>

          {/* Popular Tours */}
          <div>
            <h4 className="text-sm font-semibold text-[#0e1425] mb-4">Tour phổ biến</h4>
            <ul className="space-y-3">
              {popularTours.map((tour) => (
                <li key={tour.id}>
                  <Link href={`/routes/${tour.slug}`} className="group flex items-center gap-2">
                    <div className="relative w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-100">
                      <Image
                        src={getTourImage(tour.thumbnail || tour.gallery?.[0] || "")}
                        alt={tour.name}
                        fill
                        sizes="48px"
                        className="object-cover group-hover:scale-110 transition-transform"
                      />
                    </div>
                    <div className="min-w-0">
                      <p className="text-sm font-medium text-gray-900 truncate group-hover:text-emerald-700 transition-colors">{tour.name}</p>
                      <p className="text-xs text-gray-500">{tour.price.toLocaleString("vi-VN")}đ</p>
                    </div>
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Policies */}
          <div>
            <h4 className="text-sm font-semibold text-[#0e1425] mb-4">Chính sách</h4>
            <ul className="space-y-2">
              {resolvedPolicies.map((policy) => (
                <li key={policy.href}>
                  <Link href={policy.href} className="text-sm text-gray-600 hover:text-emerald-700 transition-colors">
                    {policy.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="text-sm font-semibold text-[#0e1425] mb-4">Liên hệ</h4>
            <ul className="space-y-3">
              <li className="flex items-start gap-2">
                <PhoneIcon className="w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0" />
                <div>
                  <a href={`tel:${settings?.hotline ? settings.hotline.replace(/\s+/g, "") : "0961804359"}`} className="text-sm font-medium text-gray-900 hover:text-emerald-700 transition-colors">
                    {settings?.hotline || "096 180 43 59"}
                  </a>
                  <p className="text-xs text-gray-500">T2-CN: 7:00 - 21:00</p>
                </div>
              </li>
              <li className="flex items-start gap-2">
                <MailIcon className="w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0" />
                <a href={`mailto:${settings?.contact_email || "doidepadventure@gmail.com"}`} className="text-sm text-gray-600 hover:text-emerald-700 transition-colors break-all">
                  {settings?.contact_email || "doidepadventure@gmail.com"}
                </a>
              </li>
              <li className="flex items-start gap-2">
                <svg className="w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                <span className="text-sm text-gray-600">{settings?.company_address || "TP. Hồ Chí Minh, Việt Nam"}</span>
              </li>
            </ul>

            {/* Payment Methods */}
            <div className="mt-6">
              <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Thanh toán</h4>
              <div className="flex gap-2">
                <div className="px-2 py-1 bg-gray-100 rounded text-xs text-gray-600">VietQR</div>
                <div className="px-2 py-1 bg-gray-100 rounded text-xs text-gray-600">MB Bank</div>
                <div className="px-2 py-1 bg-gray-100 rounded text-xs text-gray-600">Tiền mặt</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom Bar */}
      <div className="border-t border-gray-100">
        <div className="container mx-auto px-4 py-4">
          <div className="flex flex-col sm:flex-row items-center justify-between gap-3">
            <p className="text-xs text-gray-500">
              © {currentYear} Đôi Dép Adventure. All rights reserved.
            </p>
            <div className="flex items-center gap-4 text-xs text-gray-500">
              <Link href="/chinh-sach-bao-mat" className="hover:text-gray-900 transition-colors">
                Chính sách bảo mật
              </Link>
              <Link href="/dieu-khoan-su-dung" className="hover:text-gray-900 transition-colors">
                Điều khoản sử dụng
              </Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
