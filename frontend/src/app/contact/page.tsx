import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { PhoneIcon, MailIcon, MapPinIcon, ClockIcon } from "@/components/icons";
import { getSettings, getContactPageData } from "@/lib/api";
import type { Metadata } from "next";
import { seoToMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  try {
    const data = await getContactPageData();
    const fallback: Metadata = {
      title: "Liên hệ | Đôi Dép Adventure",
      description: "Kết nối với Đôi Dép Adventure - Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn.",
    };
    return seoToMetadata(data.seo, fallback);
  } catch {
    return {
      title: "Liên hệ | Đôi Dép Adventure",
      description: "Kết nối với Đôi Dép Adventure - Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn.",
    };
  }
}

export default async function ContactPage() {
  let settings = null;
  let contactData = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Error fetching general settings for contact page:", err);
  }
  try {
    contactData = await getContactPageData();
  } catch (err) {
    console.error("Error fetching contact page data:", err);
  }

  const hotline = settings?.hotline || "096 180 43 59";
  const email = settings?.contact_email || "doidepadventure@gmail.com";
  const address = settings?.company_address || "TP. Hồ Chí Minh";

  // Hero Section
  const heroBadge = contactData?.hero?.badge || "Liên hệ";
  const heroTitle = contactData?.hero?.title || "Kết nối với Đôi Dép Adventure";
  const heroSubtitle = contactData?.hero?.subtitle || "Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Liên hệ ngay để được tư vấn về các chuyến đi.";

  // Form & Hours
  const formTitle = contactData?.form_title || "Gửi tin nhắn cho chúng tôi";
  const hours = contactData?.working_hours?.hours || "8:00 - 20:00";
  const days = contactData?.working_hours?.days || "Thứ 2 - Chủ nhật";

  return (
    <div className="min-h-screen flex flex-col">
      {contactData?.seo?.schema && (
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(contactData.seo.schema) }}
        />
      )}
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero */}
        <section className="py-16 px-4 bg-gradient-to-b from-white to-[#f5f7fa]">
          <div className="container mx-auto text-center">
            <span className="inline-block px-4 py-2 text-sm font-semibold text-[#16a249] bg-[rgba(22,162,73,0.1)] rounded-full mb-4">
              {heroBadge}
            </span>
            <h1 className="text-4xl lg:text-6xl font-extrabold text-[#0e1425] mb-4">
              {heroTitle}
            </h1>
            <p className="text-lg text-[#6b7280] max-w-2xl mx-auto">
              {heroSubtitle}
            </p>
          </div>
        </section>

        {/* Contact Info */}
        <section className="section-padding">
          <div className="container mx-auto px-4">
            <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
              <div className="text-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                <div className="w-14 h-14 rounded-full bg-gradient-to-br from-[#16a249] to-[#10b981] flex items-center justify-center mx-auto mb-4">
                  <PhoneIcon className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-bold text-[#0e1425] mb-2">Điện thoại</h3>
                <a href={`tel:${hotline.replace(/\s+/g, "")}`} className="text-[#16a249] font-semibold hover:underline">
                  {hotline}
                </a>
                <p className="text-sm text-[#6b7280] mt-1">Hỗ trợ 24/7</p>
              </div>

              <div className="text-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                <div className="w-14 h-14 rounded-full bg-gradient-to-br from-[#8b5cf6] to-[#a855f7] flex items-center justify-center mx-auto mb-4">
                  <MailIcon className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-bold text-[#0e1425] mb-2">Email</h3>
                <a href={`mailto:${email}`} className="text-[#16a249] font-semibold hover:underline">
                  {email}
                </a>
                <p className="text-sm text-[#6b7280] mt-1">Phản hồi trong 24h</p>
              </div>

              <div className="text-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                <div className="w-14 h-14 rounded-full bg-gradient-to-br from-[#f43f5e] to-[#fb7185] flex items-center justify-center mx-auto mb-4">
                  <MapPinIcon className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-bold text-[#0e1425] mb-2">Địa chỉ</h3>
                <p className="text-[#6b7280]">{address}</p>
                <p className="text-sm text-[#6b7280] mt-1">Việt Nam</p>
              </div>

              <div className="text-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                <div className="w-14 h-14 rounded-full bg-gradient-to-br from-[#f59e0b] to-[#fbbf24] flex items-center justify-center mx-auto mb-4">
                  <ClockIcon className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-bold text-[#0e1425] mb-2">Giờ làm việc</h3>
                <p className="text-[#6b7280]">{hours}</p>
                <p className="text-sm text-[#6b7280] mt-1">{days}</p>
              </div>
            </div>

            {/* Contact Form */}
            <div className="max-w-2xl mx-auto bg-white rounded-3xl shadow-lg p-8">
              <h2 className="text-2xl font-bold text-[#0e1425] mb-6 text-center">
                {formTitle}
              </h2>
              <form className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-[#0e1425] mb-2">
                    Họ và tên
                  </label>
                  <input
                    type="text"
                    placeholder="Nhập họ và tên của bạn"
                    className="w-full px-4 py-3 rounded-xl border border-[#d3dae4] focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]/20 outline-none transition-all"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0e1425] mb-2">
                    Email
                  </label>
                  <input
                    type="email"
                    placeholder="Nhập email của bạn"
                    className="w-full px-4 py-3 rounded-xl border border-[#d3dae4] focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]/20 outline-none transition-all"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0e1425] mb-2">
                    Số điện thoại
                  </label>
                  <input
                    type="tel"
                    placeholder="Nhập số điện thoại của bạn"
                    className="w-full px-4 py-3 rounded-xl border border-[#d3dae4] focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]/20 outline-none transition-all"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0e1425] mb-2">
                    Nội dung
                  </label>
                  <textarea
                    rows={4}
                    placeholder="Nhập nội dung tin nhắn của bạn"
                    className="w-full px-4 py-3 rounded-xl border border-[#d3dae4] focus:border-[#16a249] focus:ring-2 focus:ring-[#16a249]/20 outline-none transition-all resize-none"
                  />
                </div>
                <button
                  type="submit"
                  className="w-full py-3 bg-gradient-to-r from-[#16a249] to-[#10b981] text-white font-semibold rounded-xl hover:opacity-90 transition-opacity"
                >
                  Gửi tin nhắn
                </button>
              </form>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}