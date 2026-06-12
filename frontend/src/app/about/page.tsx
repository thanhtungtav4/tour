import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import {
  UsersIcon,
  ShieldCheckIcon,
  SparklesIcon,
  WalletIcon,
  MapPinIcon,
  CalendarIcon,
  CompassIcon,
  MountainIcon,
  TreeIcon
} from "@/components/icons";
import { cn } from "@/lib/utils";
import { getAboutPageData } from "@/lib/api";
import type { Metadata } from "next";
import { seoToMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  try {
    const data = await getAboutPageData();
    const fallback: Metadata = {
      title: "Giới thiệu | Đôi Dép Adventure",
      description: "Câu chuyện của Đôi Dép Adventure - Đối tác tin cậy cho hành trình trekking đáng nhớ.",
    };
    return seoToMetadata(data.seo, fallback);
  } catch {
    return {
      title: "Giới thiệu | Đôi Dép Adventure",
      description: "Câu chuyện của Đôi Dép Adventure - Đối tác tin cậy cho hành trình trekking đáng nhớ.",
    };
  }
}


const iconMap: Record<string, React.ComponentType<any>> = {
  users: UsersIcon,
  shield: ShieldCheckIcon,
  sparkles: SparklesIcon,
  wallet: WalletIcon,
  "map-pin": MapPinIcon,
  calendar: CalendarIcon,
};

const rightIconMap: Record<string, React.ComponentType<any>> = {
  "map-pin": MapPinIcon,
  mountain: MountainIcon,
  compass: CompassIcon,
  tree: TreeIcon,
};

const defaultStats = [
  { number: "500+", label: "Chuyến đã tổ chức" },
  { number: "3000+", label: "Khách hàng" },
  { number: "50+", label: "Tuyến đường" },
  { number: "5", label: "Năm kinh nghiệm" },
];

const defaultTeam: { name: string; role: string; avatar_text?: string; avatar_image?: string; }[] = [
  { name: "Minh Anh", role: "Founder & CEO", avatar_text: "MA" },
  { name: "Hoàng Nam", role: "Head Guide", avatar_text: "HN" },
  { name: "Thu Hà", role: "Operations Manager", avatar_text: "TH" },
  { name: "Văn Đức", role: "Lead Trekker", avatar_text: "VD" },
];

const defaultPoints: { icon: string; title: string; desc: string; }[] = [
  {
    icon: "users",
    title: "Đội ngũ chuyên nghiệp",
    desc: "Hướng dẫn viên giàu kinh nghiệm, được đào tạo bài bản",
  },
  {
    icon: "shield",
    title: "An toàn là ưu tiên số 1",
    desc: "Trang thiết bị chất lượng cao, quy trình an toàn nghiêm ngặt",
  },
];

const pointGradients = [
  "from-[#16a249] to-[#10b981]",
  "from-[#8b5cf6] to-[#a855f7]",
  "from-[#f43f5e] to-[#fb7185]",
];

export default async function AboutPage() {
  let aboutData = null;
  try {
    aboutData = await getAboutPageData();
  } catch (err) {
    console.error("Failed to load about page data in Server Component:", err);
  }

  // Hero Section
  const heroBadge = aboutData?.hero?.badge || "Về chúng tôi";
  const heroTitle = aboutData?.hero?.title || "Câu chuyện của Đôi Dép Adventure";
  const heroSubtitle = aboutData?.hero?.subtitle || "Đôi Dép Adventure được thành lập với niềm đam mê khám phá thiên nhiên Việt Nam. Chúng tôi tin rằng mỗi người đều xứng đáng được trải nghiệm những điều tuyệt vời nhất mà thiên nhiên mang lại.";

  // Stats Section
  const resolvedStats = (aboutData?.stats && aboutData.stats.length > 0) ? aboutData.stats : defaultStats;

  // Mission Section
  const missionBadge = aboutData?.mission?.badge || "Sứ mệnh";
  const missionTitle = aboutData?.mission?.title || "Mang thiên nhiên đến gần hơn với mọi người";
  const missionDesc = aboutData?.mission?.description || "Chúng tôi không chỉ tổ chức các chuyến đi - chúng tôi tạo ra những trải nghiệm đáng nhớ, an toàn và phù hợp với mọi lứa tuổi. Từ những buổi dã ngoại đơn giản đến những chuyến trekking đầy thử thách, Đôi Dép Adventure luôn đồng hành cùng bạn.";
  const resolvedPoints = (aboutData?.mission?.points && aboutData.mission.points.length > 0) ? aboutData.mission.points : defaultPoints;
  const missionRightTitle = aboutData?.mission?.right_title || "Khám phá Việt Nam";
  const missionRightSubtitle = aboutData?.mission?.right_subtitle || "Từ Bắc vào Nam";
  const RightIcon = rightIconMap[aboutData?.mission?.right_icon || ""] || MapPinIcon;

  // Team Section
  const teamBadge = aboutData?.team?.badge || "Đội ngũ";
  const teamTitle = aboutData?.team?.title || "Những người đam mê khám phá";
  const resolvedTeam = (aboutData?.team?.members && aboutData.team.members.length > 0) ? aboutData.team.members : defaultTeam;

  return (
    <div className="min-h-screen flex flex-col">
      {aboutData?.seo?.schema && (
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(aboutData.seo.schema) }}
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
            <p className="text-lg text-[#6b7280] max-w-3xl mx-auto">
              {heroSubtitle}
            </p>
          </div>
        </section>

        {/* Stats */}
        <section className="py-12 px-4 bg-primary">
          <div className="container mx-auto">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
              {resolvedStats.map((stat, i) => (
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
                  {missionBadge}
                </span>
                <h2 className="text-3xl lg:text-4xl font-extrabold text-[#0e1425] mb-6">
                  {missionTitle}
                </h2>
                <p className="text-[#6b7280] mb-6 leading-relaxed">
                  {missionDesc}
                </p>
                <div className="space-y-4">
                  {resolvedPoints.map((point, index) => {
                    const PointIcon = iconMap[point.icon] || UsersIcon;
                    const gradient = pointGradients[index % pointGradients.length];
                    return (
                      <div key={index} className="flex items-start gap-4">
                        <div className={cn("w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center flex-shrink-0", gradient)}>
                          <PointIcon className="w-5 h-5 text-white" />
                        </div>
                        <div>
                          <h3 className="font-bold text-[#0e1425] mb-1">{point.title}</h3>
                          <p className="text-sm text-[#6b7280]">{point.desc}</p>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
              <div className="relative rounded-3xl overflow-hidden min-h-[400px] bg-gradient-to-br from-[#16a249] to-[#10b981]">
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="text-center text-white p-8">
                    <RightIcon className="w-16 h-16 mx-auto mb-4 opacity-50" />
                    <p className="text-2xl font-bold">{missionRightTitle}</p>
                    <p className="text-white/80 mt-2">{missionRightSubtitle}</p>
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
                {teamBadge}
              </span>
              <h2 className="text-3xl lg:text-4xl font-extrabold text-[#0e1425]">
                {teamTitle}
              </h2>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {resolvedTeam.map((member, i) => {
                const initialText = member.avatar_text || member.name.split(" ").map((n) => n[0]).join("").substring(0, 2).toUpperCase();
                return (
                  <div key={i} className="text-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <div className="w-20 h-20 rounded-full overflow-hidden bg-gradient-to-br from-[#16a249] to-[#10b981] flex items-center justify-center mx-auto mb-4 relative">
                      {member.avatar_image ? (
                        <img
                          src={member.avatar_image}
                          alt={member.name}
                          className="w-full h-full object-cover"
                        />
                      ) : (
                        <span className="text-white text-xl font-bold">{initialText}</span>
                      )}
                    </div>
                    <h3 className="font-bold text-[#0e1425]">{member.name}</h3>
                    <p className="text-sm text-[#6b7280]">{member.role}</p>
                  </div>
                );
              })}
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}