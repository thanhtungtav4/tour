import Link from "next/link";
import { ChevronRightIcon } from "@/components/icons";
import { getSettings } from "@/lib/api";

const policies = [
  {
    href: "/policies/safety",
    title: "Chính sách an toàn",
    description: "Cam kết an toàn, trang thiết bị, bảo hiểm du lịch và quy định cho khách hàng.",
    icon: "🛡️",
  },
  {
    href: "/policies/cancel",
    title: "Chính sách hủy vé",
    description: "Quy định hủy tour, mức phí và quy trình hoàn tiền chi tiết.",
    icon: "❌",
  },
  {
    href: "/policies/exchange",
    title: "Chính sách đổi vé, bảo lưu",
    description: "Đổi ngày khởi hành, bảo lưu tour và chuyển nhượng vé.",
    icon: "🔄",
  },
  {
    href: "/policies/refund",
    title: "Chính sách hoàn tiền",
    description: "Điều kiện, thời gian và quy trình hoàn tiền cho khách hàng.",
    icon: "💰",
  },
  {
    href: "/policies/privacy",
    title: "Chính sách bảo mật",
    description: "Thu thập, sử dụng và bảo vệ thông tin cá nhân của khách hàng.",
    icon: "🔒",
  },
  {
    href: "/policies/terms",
    title: "Điều khoản sử dụng",
    description: "Quy định chung về đăng ký, thanh toán, trách nhiệm và giải quyết tranh chấp.",
    icon: "📋",
  },
];

export default async function PoliciesIndexPage() {
  let settings = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Failed to load settings in PoliciesIndexPage", err);
  }

  const hotline = settings?.hotline || "096 180 43 59";
  const hotlineClean = hotline.replace(/\s+/g, "");
  const email = settings?.contact_email || "doidepadventure@gmail.com";

  return (
    <>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Chính sách & Điều khoản</h1>
      <p className="text-gray-600 leading-relaxed mb-8">
        Đôi Dép Adventure minh bạch trong mọi chính sách để khách hàng yên tâm trải nghiệm. 
        Dưới đây là toàn bộ chính sách và điều khoản sử dụng dịch vụ.
      </p>

      <div className="grid sm:grid-cols-2 gap-4">
        {policies.map((policy) => (
          <Link
            key={policy.href}
            href={policy.href}
            className="group flex items-start gap-4 p-5 bg-white border border-gray-200 rounded-xl hover:border-emerald-300 hover:shadow-md transition-all"
          >
            <span className="text-3xl flex-shrink-0">{policy.icon}</span>
            <div className="flex-1 min-w-0">
              <h3 className="font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors">
                {policy.title}
              </h3>
              <p className="text-sm text-gray-500 mt-1">{policy.description}</p>
            </div>
            <ChevronRightIcon className="w-5 h-5 text-gray-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all flex-shrink-0 mt-1" />
          </Link>
        ))}
      </div>

      <div className="mt-8 p-6 bg-emerald-50 border border-emerald-200 rounded-xl">
        <h3 className="font-semibold text-emerald-900 mb-2">Cần hỗ trợ?</h3>
        <p className="text-sm text-emerald-700 mb-3">
          Nếu bạn có thắc mắc về bất kỳ chính sách nào, đừng ngần ngại liên hệ với chúng tôi.
        </p>
        <div className="flex flex-wrap gap-4 text-sm">
          <a href={`tel:${hotlineClean}`} className="font-medium text-emerald-700 hover:text-emerald-900">
            📞 {hotline}
          </a>
          <a href={`mailto:${email}`} className="font-medium text-emerald-700 hover:text-emerald-900">
            ✉️ {email}
          </a>
        </div>
      </div>
    </>
  );
}
