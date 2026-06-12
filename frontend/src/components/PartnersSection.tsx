import { cn } from "@/lib/utils";
import { CheckCircleIcon, ShieldCheckIcon, PhoneIcon, StarIcon, UsersIcon, MountainIcon } from "@/components/icons";

const ecosystem = [
  {
    icon: "🛡️",
    name: "Bảo hiểm du lịch",
    desc: "Bảo hiểm toàn diện đến 200 triệu đồng/người, hợp tác với PVI & Bảo Việt",
    color: "from-blue-500 to-blue-600",
    bg: "bg-blue-50",
  },
  {
    icon: "🚗",
    name: "Vận chuyển",
    desc: "Đội xe 16-45 chỗ đời mới, tài xế giàu kinh nghiệm đường núi",
    color: "from-emerald-500 to-emerald-600",
    bg: "bg-emerald-50",
  },
  {
    icon: "🏨",
    name: "Lưu trú",
    desc: "Homestay, khách sạn 3-4 sao tại các điểm đến phổ biến",
    color: "from-purple-500 to-purple-600",
    bg: "bg-purple-50",
  },
  {
    icon: "🎒",
    name: "Thiết bị trekking",
    desc: "Cho thuê gậy, lều, túi ngủ, balo chính hãng với giá ưu đãi",
    color: "from-orange-500 to-orange-600",
    bg: "bg-orange-50",
  },
  {
    icon: "📸",
    name: "Chụp ảnh chuyên nghiệp",
    desc: "Photographer đi cùng tour, trả ảnh trong 48h",
    color: "from-pink-500 to-pink-600",
    bg: "bg-pink-50",
  },
  {
    icon: "🏥",
    name: "Y tế & Sơ cứu",
    desc: "HDV được đào tạo sơ cứu, bộ y tế đầy đủ trên mỗi tour",
    color: "from-red-500 to-red-600",
    bg: "bg-red-50",
  },
  {
    icon: "🍽️",
    name: "Ẩm thực địa phương",
    desc: "Bữa ăn đặc sản vùng miền, thực phẩm tươi sạch",
    color: "from-amber-500 to-amber-600",
    bg: "bg-amber-50",
  },
  {
    icon: "📱",
    name: "Công nghệ & App",
    desc: "App theo dõi tour real-time, GPS tracking, check-in QR",
    color: "from-cyan-500 to-cyan-600",
    bg: "bg-cyan-50",
  },
];

const colorToBgMap: Record<string, string> = {
  "from-blue-500 to-blue-600": "bg-blue-50",
  "from-emerald-500 to-emerald-600": "bg-emerald-50",
  "from-purple-500 to-purple-600": "bg-purple-50",
  "from-orange-500 to-orange-600": "bg-orange-50",
  "from-pink-500 to-pink-600": "bg-pink-50",
  "from-red-500 to-red-600": "bg-red-50",
  "from-amber-500 to-amber-600": "bg-amber-50",
  "from-cyan-500 to-cyan-600": "bg-cyan-50",
};

interface PartnersSectionProps {
  data?: {
    badge?: string;
    title?: string;
    subtitle?: string;
    items?: {
      icon: string;
      name: string;
      desc: string;
      color: string;
    }[] | null;
  };
}

export function PartnersSection({ data }: PartnersSectionProps) {
  const badge = data?.badge || "Đối tác chiến lược";
  const subtitle = data?.subtitle || "Kết nối đa dạng dịch vụ để mang đến trải nghiệm trekking trọn vẹn nhất";
  
  const rawTitle = data?.title || "Hệ sinh thái |Đôi Dép Adventure";
  const titleParts = rawTitle.split("|");

  const resolvedEcosystem = (data?.items && data.items.length > 0)
    ? data.items.map((item) => ({
        icon: item.icon || "🛡️",
        name: item.name,
        desc: item.desc,
        color: item.color || "from-blue-500 to-blue-600",
        bg: colorToBgMap[item.color] || "bg-blue-50",
      }))
    : ecosystem;

  return (
    <section className="py-16 lg:py-24 bg-gradient-to-b from-gray-50 to-white">
      <div className="container mx-auto px-4 lg:px-8">
        {/* Header */}
        <div className="text-center mb-14">
          <span className="inline-flex items-center gap-2 px-4 py-1.5 text-sm font-medium text-emerald-700 bg-emerald-100 rounded-full mb-4">
            <ShieldCheckIcon className="w-4 h-4" />
            {badge}
          </span>
          <h2 className="text-3xl lg:text-5xl font-bold text-gray-900 mb-4">
            {titleParts[0]}
            {titleParts[1] && (
              <span className="text-emerald-600">{titleParts[1]}</span>
            )}
          </h2>
          <p className="text-gray-500 max-w-2xl mx-auto text-lg">
            {subtitle}
          </p>
        </div>

        {/* Ecosystem Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6">
          {resolvedEcosystem.map((item, i) => (
            <div
              key={i}
              className={cn(
                "group relative p-6 rounded-2xl border border-gray-100",
                "hover:border-transparent hover:shadow-xl transition-all duration-300",
                "hover:-translate-y-1",
                item.bg
              )}
            >
              {/* Icon */}
              <div className="flex items-center gap-4 mb-4">
                <div className={cn(
                  "w-14 h-14 rounded-xl flex items-center justify-center text-2xl",
                  "bg-gradient-to-br", item.color,
                  "shadow-lg group-hover:scale-110 transition-transform duration-300"
                )}>
                  {item.icon}
                </div>
                <div>
                  <h3 className="font-bold text-gray-900 text-lg">{item.name}</h3>
                  <div className="flex items-center gap-1 mt-0.5">
                    <StarIcon className="w-3.5 h-3.5 text-yellow-400 fill-current" />
                    <span className="text-xs text-gray-500">Đối tác tin cậy</span>
                  </div>
                </div>
              </div>

              {/* Description */}
              <p className="text-sm text-gray-600 leading-relaxed">
                {item.desc}
              </p>

              {/* Hover Arrow */}
              <div className="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div className={cn("w-8 h-8 rounded-full flex items-center justify-center", "bg-gradient-to-br", item.color)}>
                  <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Trust Badges */}
        <div className="mt-16 p-8 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div className="flex items-center gap-4 justify-center sm:justify-start">
              <div className="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                <CheckCircleIcon className="w-6 h-6 text-emerald-600" />
              </div>
              <div>
                <p className="font-bold text-gray-900">50+ Đối tác</p>
                <p className="text-sm text-gray-500">Trên toàn quốc</p>
              </div>
            </div>
            <div className="flex items-center gap-4 justify-center sm:justify-start">
              <div className="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <ShieldCheckIcon className="w-6 h-6 text-blue-600" />
              </div>
              <div>
                <p className="font-bold text-gray-900">An toàn tuyệt đối</p>
                <p className="text-sm text-gray-500">Tiêu chuẩn quốc tế</p>
              </div>
            </div>
            <div className="flex items-center gap-4 justify-center sm:justify-start">
              <div className="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                <PhoneIcon className="w-6 h-6 text-purple-600" />
              </div>
              <div>
                <p className="font-bold text-gray-900">Hỗ trợ 24/7</p>
                <p className="text-sm text-gray-500">Hotline & Zalo</p>
              </div>
            </div>
          </div>
        </div>

        {/* CTA */}
        <div className="mt-12 text-center">
          <p className="text-gray-500 mb-4">Bạn là doanh nghiệp muốn hợp tác cùng Đôi Dép Adventure?</p>
          <a
            href="/contact"
            className="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition-colors"
          >
            Liên hệ hợp tác
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </a>
        </div>
      </div>
    </section>
  );
}
