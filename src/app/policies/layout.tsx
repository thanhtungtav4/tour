import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import Link from "next/link";

const policyLinks = [
  { href: "/policies/safety", label: "Chính sách an toàn" },
  { href: "/policies/cancel", label: "Chính sách hủy vé" },
  { href: "/policies/exchange", label: "Chính sách đổi vé, bảo lưu" },
  { href: "/policies/refund", label: "Chính sách hoàn tiền" },
  { href: "/policies/privacy", label: "Chính sách bảo mật" },
  { href: "/policies/terms", label: "Điều khoản sử dụng" },
];

export default function PoliciesLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero */}
        <section className="py-12 px-4 bg-gradient-to-b from-emerald-50 to-white">
          <div className="container mx-auto text-center">
            <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Chính sách</h1>
            <p className="text-gray-500">Thông tin chi tiết về các chính sách của Đôi Dép Adventure</p>
          </div>
        </section>

        <div className="container mx-auto px-4 py-8">
          <div className="grid lg:grid-cols-4 gap-8">
            {/* Sidebar */}
            <aside className="lg:col-span-1">
              <nav className="sticky top-[120px] bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3 px-2">
                  Danh mục
                </h3>
                <ul className="space-y-1">
                  {policyLinks.map((link) => (
                    <li key={link.href}>
                      <Link
                        href={link.href}
                        className="block px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 transition-colors"
                      >
                        {link.label}
                      </Link>
                    </li>
                  ))}
                </ul>
              </nav>
            </aside>

            {/* Content */}
            <div className="lg:col-span-3">
              <article className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-10 prose prose-gray max-w-none">
                {children}
              </article>
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
}
