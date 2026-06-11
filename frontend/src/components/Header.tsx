"use client";

import { useState, useEffect } from "react";
import { usePathname } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { cn } from "@/lib/utils";
import { MenuIcon, CloseIcon, PhoneIcon } from "@/components/icons";
import { useSettings } from "@/hooks/useSettings";

const navLinks = [
  { href: "/", label: "Trang chủ" },
  { href: "/booking", label: "Tuyến đường" },
  { href: "/experience", label: "Trải nghiệm" },
  { href: "/about", label: "Về chúng tôi" },
  { href: "/contact", label: "Liên hệ" },
];

export function Header() {
  const pathname = usePathname();
  const { settings } = useSettings();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const [scrollDir, setScrollDir] = useState<"up" | "down">("up");
  const [lastScrollY, setLastScrollY] = useState(0);

  useEffect(() => {
    let ticking = false;
    const handleScroll = () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          const currentY = window.scrollY;
          setScrollDir(currentY > lastScrollY ? "down" : "up");
          setIsScrolled(currentY > 20);
          setLastScrollY(currentY);
          ticking = false;
        });
        ticking = true;
      }
    };
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, [lastScrollY]);

  const isHidden = scrollDir === "down" && !isMobileMenuOpen && lastScrollY > 100;

  return (
    <>
      <header
        className={cn(
          "fixed top-0 left-0 right-0 z-50 h-[81px] transition-all duration-300",
          "bg-white/85 backdrop-blur-[18px]",
          isScrolled ? "shadow-md" : "shadow-sm"
        )}
      >
        <nav className="container mx-auto px-4 sm:px-6 lg:px-8 h-full">
          <div className="flex items-center justify-between h-full">
            {/* Logo */}
            <Link
              href="/"
              className="flex items-center gap-2.5 text-xl font-bold text-[#0e1425] transition-all duration-300"
            >
              <div className="relative w-9 h-9 rounded-full overflow-hidden flex items-center justify-center">
                <Image
                  src="/images/logo.png"
                  alt="Đôi Dép Adventure Logo"
                  fill
                  sizes="36px"
                  className="object-cover"
                />
              </div>
              <span className="font-bold">Đôi Dép Adventure</span>
            </Link>

            {/* Desktop Navigation */}
            <div className="hidden lg:flex items-center gap-1">
              {navLinks.map((link) => {
                const isActive = pathname === link.href ||
                  (link.href !== "/" && pathname?.startsWith(link.href));
                return (
                  <Link
                    key={link.href}
                    href={link.href}
                    className={cn(
                      "px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200",
                      isActive
                        ? "text-emerald-700 bg-emerald-50"
                        : "text-gray-600 hover:text-emerald-600 hover:bg-gray-50"
                    )}
                  >
                    {link.label}
                  </Link>
                );
              })}
            </div>

            {/* Desktop Actions */}
            <div className="hidden md:flex items-center gap-3">
              <a
                href={`tel:${settings?.hotline ? settings.hotline.replace(/\s+/g, "") : "0961804359"}`}
                className="flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600 transition-colors"
              >
                <PhoneIcon className="w-4 h-4" />
                <span className="font-medium">{settings?.hotline || "096 180 43 59"}</span>
              </a>
              <Link
                href="/booking"
                className="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-all duration-200 shadow-sm hover:shadow-md"
              >
                Đặt vé ngay
              </Link>
            </div>

            {/* Hamburger Button */}
            <button
              type="button"
              onClick={() => setIsMobileMenuOpen(true)}
              className="block lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
              aria-label="Open menu"
            >
              <MenuIcon className="w-6 h-6" />
            </button>
          </div>
        </nav>
      </header>

      {/* Mobile Menu Overlay */}
      <div
        className={cn(
          "fixed inset-0 z-[60] transition-all duration-300",
          isMobileMenuOpen ? "visible" : "invisible"
        )}
      >
        {/* Backdrop */}
        <div
          className={cn(
            "absolute inset-0 bg-black/30 backdrop-blur-sm transition-opacity duration-300",
            isMobileMenuOpen ? "opacity-100" : "opacity-0"
          )}
          onClick={() => setIsMobileMenuOpen(false)}
        />

        {/* Slide Panel */}
        <div
          className={cn(
            "absolute right-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl transition-transform duration-300",
            isMobileMenuOpen ? "translate-x-0" : "translate-x-full"
          )}
          onClick={(e) => e.stopPropagation()}
        >
          <div className="flex flex-col h-full">
            {/* Header */}
            <div className="flex items-center justify-between p-4 border-b border-gray-100">
              <Link href="/" onClick={() => setIsMobileMenuOpen(false)} className="flex items-center gap-2">
                <div className="relative w-8 h-8 rounded-full overflow-hidden flex items-center justify-center">
                  <Image
                    src="/images/logo.png"
                    alt="Đôi Dép Adventure Logo"
                    fill
                    sizes="32px"
                    className="object-cover"
                  />
                </div>
                <span className="font-bold text-lg">Đôi Dép Adventure</span>
              </Link>
              <button
                onClick={() => setIsMobileMenuOpen(false)}
                className="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                aria-label="Close menu"
              >
                <CloseIcon className="w-5 h-5" />
              </button>
            </div>

            {/* Nav Links */}
            <nav className="flex-1 overflow-y-auto py-4">
              <div className="px-4 space-y-1">
                {navLinks.map((link) => {
                  const isActive = pathname === link.href ||
                    (link.href !== "/" && pathname?.startsWith(link.href));
                  return (
                    <Link
                      key={link.href}
                      href={link.href}
                      onClick={() => setIsMobileMenuOpen(false)}
                      className={cn(
                        "flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium transition-all",
                        isActive
                          ? "bg-emerald-50 text-emerald-700"
                          : "text-gray-700 hover:bg-gray-50"
                      )}
                    >
                      {link.label}
                    </Link>
                  );
                })}
              </div>

              {/* CTA Section */}
              <div className="mt-6 px-4">
                <Link
                  href="/booking"
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors"
                >
                  Đặt vé ngay
                </Link>
              </div>

              {/* Contact Section */}
              <div className="mt-6 px-4">
                <h4 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Liên hệ nhanh</h4>
                <div className="space-y-3">
                  <a href={`tel:${settings?.hotline ? settings.hotline.replace(/\s+/g, "") : "0961804359"}`} className="flex items-center gap-3 text-gray-700 hover:text-emerald-600">
                    <PhoneIcon className="w-5 h-5 text-emerald-600" />
                    <span className="font-medium">{settings?.hotline || "096 180 43 59"}</span>
                  </a>
                  <a href={`mailto:${settings?.contact_email || "doidepadventure@gmail.com"}`} className="flex items-center gap-3 text-gray-700 hover:text-emerald-600">
                    <svg className="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <rect width="20" height="16" x="2" y="4" rx="2" />
                      <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                    </svg>
                    <span className="text-sm">{settings?.contact_email || "doidepadventure@gmail.com"}</span>
                  </a>
                  <a href={settings?.zalo_link || "https://zalo.me/0961804359"} target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 text-gray-700 hover:text-emerald-600">
                    <svg className="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                      <circle cx="12" cy="12" r="10"/>
                    </svg>
                    <span>Zalo: {settings?.hotline || "096 180 43 59"}</span>
                  </a>
                </div>
              </div>

              {/* Social */}
              <div className="mt-6 px-4">
                <h4 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Theo dõi</h4>
                <div className="flex gap-3">
                  <a href="#" className="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors">
                    <span className="text-sm font-bold">f</span>
                  </a>
                  <a href="#" className="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center hover:bg-red-700 transition-colors">
                    <span className="text-sm font-bold">▶</span>
                  </a>
                  <a href="#" className="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 text-white flex items-center justify-center hover:opacity-90 transition-opacity">
                    <span className="text-sm font-bold">📷</span>
                  </a>
                </div>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </>
  );
}
