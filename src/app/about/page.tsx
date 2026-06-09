import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { UsersIcon, ShieldCheckIcon, SparklesIcon, WalletIcon, MapPinIcon, CalendarIcon } from "@/components/icons";
import { cn } from "@/lib/utils";

export default function AboutPage() {
  const teamMembers = [
    { name: "Minh Anh", role: "Founder & CEO", avatar: "MA" },
    { name: "Hoàng Nam", role: "Head Guide", avatar: "HN" },
    { name: "Thu Hà", role: "Operations Manager", avatar: "TH" },
    { name: "Văn Đức", role: "Lead Trekker", avatar: "VD" },
  ];

  const stats = [
    { number: "500+", label: "Chuyến đã tổ chức" },
    { number: "3000+", label: "Khách hàng" },
    { number: "50+", label: "Tuyến đường" },
    { number: "5", label: "Năm kinh nghiệm" },
  ];

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero */}
        <section className="py-16 px-4 bg-gradient-to-b from-white to-[#f5f7fa]">
          <div className="container mx-auto text-center">
            <span className="inline-block px-4 py-2 text-sm font-semibold text-[#16a249] bg-[rgba(22,162,73,0.1)] rounded-full mb-4">
              Về chúng tôi
            </span>
            <h1 className="text-4xl lg:text-6xl font-extrabold text-[#0e1425] mb-4">
              Câu chuyện của Đôi Dép Adventure
            </h1>
            <p className="text-lg text-[#6b7280] max-w-3xl mx-auto">
              Đôi Dép Adventure được thành lập với niềm đam mê khám phá thiên nhiên Việt Nam. 
              Chúng tôi tin rằng mỗi người đều xứng đáng được trải nghiệm những điều tuyệt vời nhất mà thiên nhiên mang lại.
            </p>
          </div>
        </section>

        {/* Stats */}
        <section className="py-12 px-4 bg-primary">
          <div className="container mx-auto">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
              {stats.map((stat, i) => (
                <div key={i} className="text-center">
                  <span className="block text-4xl lg:text-5xl font-extrabold text-white mb-2">
                    {stat.number}
                  </span>
                  <span className="text-white/80 text-sm">{stat.label}</span>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Mission */}
        <section className="section-padding">
          <div className="container mx-auto px-4">
            <div className="grid lg:grid-cols-2 gap-12 items-center">
              <div>
                <span className="inline-block px-4 py-2 text-sm font-semibold text-[#16a249] bg-[rgba(22,162,73,0.1)] rounded-full mb-4">
                  Sứ mệnh
                </span>
                <h2 className="text-3xl lg:text-4xl font-extrabold text-[#0e1425] mb-6">
                  Mang thiên nhiên đến gần hơn với mọi người
                </h2>
                <p className="text-[#6b7280] mb-6 leading-relaxed">
                  Chúng tôi không chỉ tổ chức các chuyến đi - chúng tôi tạo ra những trải nghiệm 
                  đáng nhớ, an toàn và phù hợp với mọi lứa tuổi. Từ những buổi dã ngoại đơn giản 
                  đến những chuyến trekking đầy thử thách, Đôi Dép Adventure luôn đồng hành cùng bạn.
                </p>
                <div className="space-y-4">
                  <div className="flex items-start gap-4">
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-[#16a249] to-[#10b981] flex items-center justify-center flex-shrink-0">
                      <UsersIcon className="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <h4 className="font-bold text-[#0e1425] mb-1">Đội ngũ chuyên nghiệp</h4>
                      <p className="text-sm text-[#6b7280]">Hướng dẫn viên giàu kinh nghiệm, được đào tạo bài bản</p>
                    </div>
                  </div>
                  <div className="flex items-start gap-4">
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-[#8b5cf6] to-[#a855f7] flex items-center justify-center flex-shrink-0">
                      <ShieldCheckIcon className="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <h4 className="font-bold text-[#0e1425] mb-1">An toàn là ưu tiên số 1</h4>
                      <p className="text-sm text-[#6b7280]">Trang thiết bị chất lượng cao, quy trình an toàn nghiêm ngặt</p>
                    </div>
                  </div>
                </div>
              </div>
              <div className="relative rounded-3xl overflow-hidden min-h-[400px] bg-gradient-to-br from-[#16a249] to-[#10b981]">
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="text-center text-white p-8">
                    <MapPinIcon className="w-16 h-16 mx-auto mb-4 opacity-50" />
                    <p className="text-2xl font-bold">Khám phá Việt Nam</p>
                    <p className="text-white/80 mt-2">Từ Bắc vào Nam</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Team */}
        <section className="section-padding bg-[#f5f7fa]">
          <div className="container mx-auto px-4">
            <div className="text-center mb-12">
              <span className="inline-block px-4 py-2 text-sm font-semibold text-[#16a249] bg-[rgba(22,162,73,0.1)] rounded-full mb-4">
                Đội ngũ
              </span>
              <h2 className="text-3xl lg:text-4xl font-extrabold text-[#0e1425]">
                Những người đam mê khám phá
              </h2>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {teamMembers.map((member, i) => (
                <div key={i} className="text-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                  <div className="w-20 h-20 rounded-full bg-gradient-to-br from-[#16a249] to-[#10b981] flex items-center justify-center mx-auto mb-4">
                    <span className="text-white text-xl font-bold">{member.avatar}</span>
                  </div>
                  <h4 className="font-bold text-[#0e1425]">{member.name}</h4>
                  <p className="text-sm text-[#6b7280]">{member.role}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}