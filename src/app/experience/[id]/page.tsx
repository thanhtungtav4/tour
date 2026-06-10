"use client";

import { use, useState, useEffect } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { ArrowLeftIcon, CalendarIcon, ClockIcon, ShareIcon, BookmarkIcon, FacebookIcon, TwitterIcon, LinkedinIcon, LinkIcon } from "@/components/icons";
import { cn } from "@/lib/utils";

const blogPosts = [
  {
    id: 1,
    title: "Mẹo Chọn Giày Khi Đi Trekking Không Bị Đau Chân – Bí Quyết Dân Trekking Cần Biết",
    excerpt: "Việc chọn đúng đôi giày trekking có thể quyết định trải nghiệm của bạn. Hãy cùng Đôi Dép Adventure khám phá những bí quyết...",
    author: "Ne",
    authorBio: "Chuyên gia trekking với 5 năm kinh nghiệm chinh phục các đỉnh núi Việt Nam. Đam mê chia sẻ kiến thức về outdoor và sinh tồn.",
    date: "30/1/2026",
    readTime: "8 phút",
    category: "Kinh nghiệm",
    tags: ["Trekking", "Giày", "Kỹ năng", "Công cụ"],
    image: "/images/blog-1.jpg",
    content: `
      <p class="lead">Việc chọn đúng đôi giày trekking có thể quyết định hoàn toàn trải nghiệm của bạn trên đường đi. Một đôi giày phù hợp không chỉ giúp bạn di chuyển thoải mái mà còn bảo vệ đôi chân khỏi những chấn thương không đáng có.</p>

      <h2>Tại sao giày trekking quan trọng?</h2>
      <p>Khác với giày thể thao thông thường, giày trekking được thiết kế đặc biệt để đối phó với địa hình phức tạp: đá sỏi, bùn lầy, suối nước... Đế giày có độ bám cao, cổ giày bảo vệ mắt cá, và chất liệu chống thấm nước.</p>
      
      <blockquote>
        "Một đôi giày tốt có thể làm cho một chuyến đi tồi tệ trở nên chấp nhận được, và một chuyến đi tốt trở nên tuyệt vời."
      </blockquote>

      <h2>Cách chọn size giày phù hợp</h2>
      <p>Một sai lầm phổ biến là chọn giày vừa khít như giày hàng ngày. Khi trekking, chân sẽ sưng lên sau nhiều giờ đi bộ. Hãy chọn giày lớn hơn 0.5-1 size so với bình thường, đặc biệt nếu bạn mang vớ dày.</p>
      
      <div class="tip-box">
        <strong>💡 Mẹo:</strong> Thử giày vào buổi chiều khi chân đã nở to nhất. Mang vớ trekking và đi thử trong cửa hàng ít nhất 15 phút.
      </div>

      <h2>Loại đế giày</h2>
      <p>Đế giày là yếu tố quan trọng nhất quyết định độ bám và độ bền của giày:</p>
      <ul>
        <li><strong>Đế Vibram:</strong> Độ bám tốt nhất, bền, phù hợp địa hình đá và bùn. Được sử dụng bởi hầu hết các thương hiệu cao cấp.</li>
        <li><strong>Đế cao su tổng hợp:</strong> Nhẹ hơn, phù hợp đường mòn dễ. Giá thành rẻ hơn nhưng độ bám kém hơn.</li>
        <li><strong>Đế có rãnh sâu:</strong> Thoát nước tốt, chống trượt trên bề mặt ẩm. Phù hợp cho mùa mưa.</li>
      </ul>

      <h2>Chất liệu giày</h2>
      <p>Mỗi loại chất liệu có ưu nhược điểm riêng:</p>
      <div class="comparison-grid">
        <div class="comparison-item">
          <h4>Da bò</h4>
          <p>Bền, chống nước tốt nhưng nặng và cần thời gian break-in 2-3 tuần.</p>
        </div>
        <div class="comparison-item">
          <h4>Vải tổng hợp</h4>
          <p>Nhẹ, thoáng khí, khô nhanh nhưng ít bền hơn và chống nước kém.</p>
        </div>
        <div class="comparison-item">
          <h4>Da + Vải kết hợp</h4>
          <p>Cân bằng giữa độ bền và trọng lượng - lựa chọn phổ biến nhất.</p>
        </div>
      </div>

      <h2>Những lỗi thường gặp</h2>
      <p>Đây là những sai lầm mà nhiều người mới mắc phải:</p>
      <ul>
        <li>Mang giày mới chưa break-in cho tour dài → Phồng rộp, đau chân</li>
        <li>Không buộc dây đúng cách → Dây tuột, chân không được cố định</li>
        <li>Chọn giày không thấm nước cho tour có vượt suối → Chân ướt, lạnh</li>
        <li>Quên mang vớ dự phòng → Vớ ướt không thay được</li>
      </ul>

      <div class="warning-box">
        <strong>⚠️ Lưu ý quan trọng:</strong> Không bao giờ đi barefoot trong giày trekking, ngay cả khi trời nóng. Vớ trekking chuyên dụng giúp hấp thụ mồ hôi và giảm ma sát.
      </div>

      <h2>Bảo quản giày sau tour</h2>
      <p>Sau mỗi chuyến đi, hãy vệ sinh giày sạch sẽ, để khô tự nhiên (không phơi nắng trực tiếp), và xịt chống thấm định kỳ 2-3 tháng/lần. Điều này giúp giày bền hơn và sẵn sàng cho chuyến đi tiếp theo.</p>
      
      <h3>Các bước vệ sinh giày trekking:</h3>
      <ol>
        <li>Tháo lớp lót và dây giày ra</li>
        <li>Dùng bàn chải mềm chải sạch bùn đất</li>
        <li>Rửa nhẹ bằng nước ấm (không dùng xà phòng mạnh)</li>
        <li>Để khô tự nhiên ở nơi thoáng mát</li>
        <li>Xịt chống thấm và bảo quản trong túi</li>
      </ol>

      <p class="conclusion"><em>Chúc bạn tìm được đôi giày trekking hoàn hảo cho hành trình sắp tới! Nếu có câu hỏi, hãy để lại bình luận bên dưới.</em></p>
    `,
  },
  {
    id: 2,
    title: "Trekking Tự Túc: Lợi Ích & Nguy Hiểm – Những Điều Cần Lưu Ý Trước Chuyến Đi",
    excerpt: "Trekking tự túc mang lại nhiều trải nghiệm độc đáo nhưng cũng tiềm ẩn không ít nguy hiểm. Cùng tìm hiểu...",
    author: "Mi",
    authorBio: "Travel blogger và hiking enthusiast. Đã khám phá hơn 50 cung đường trekking khắp Việt Nam và Đông Nam Á.",
    date: "30/1/2026",
    readTime: "10 phút",
    category: "An toàn",
    tags: ["Trekking tự túc", "An toàn", "Kinh nghiệm"],
    image: "/images/blog-2.jpg",
    content: `
      <p class="lead">Trekking tự túc đang trở thành xu hướng của nhiều bạn trẻ yêu thích khám phá. Tuy nhiên, giữa lợi ích và nguy hiểm chỉ cách nhau một ranh giới mong manh.</p>

      <h2>Lợi ích của trekking tự túc</h2>
      <p>Khi tự mình bước vào hành trình, bạn sẽ nhận được những điều mà tour không thể mang lại:</p>
      <ul>
        <li><strong>Tự do lịch trình:</strong> Bạn quyết định đi đâu, dừng đâu, ở lại bao lâu. Không bị gò bó theo lịch trình cố định.</li>
        <li><strong>Tiết kiệm chi phí:</strong> Không phải trả phí hướng dẫn viên, có thể tự nấu ăn và cắm trại.</li>
        <li><strong>Trải nghiệm thực tế:</strong> Tự mình xử lý mọi tình huống, rèn luyện kỹ năng sinh tồn.</li>
        <li><strong>Kết nối sâu hơn:</strong> Được hòa mình vào thiên nhiên một cách trọn vẹn.</li>
      </ul>

      <h2>Nguy hiểm tiềm ẩn</h2>
      <p>Bên cạnh những lợi ích, bạn cần nhận thức rõ các rủi ro:</p>
      
      <div class="danger-list">
        <div class="danger-item">
          <span class="danger-icon">🧭</span>
          <div>
            <strong>Lạc đường</strong>
            <p>Đây là rủi ro phổ biến nhất. Nhiều cung trekking không có biển báo rõ ràng, đặc biệt ở vùng núi cao.</p>
          </div>
        </div>
        <div class="danger-item">
          <span class="danger-icon">⛈️</span>
          <div>
            <strong>Thời tiết bất ngờ</strong>
            <p>Mưa rừng, sương mù, lũ quét có thể xảy ra mà không báo trước, đặc biệt vào mùa mưa.</p>
          </div>
        </div>
        <div class="danger-item">
          <span class="danger-icon">🏥</span>
          <div>
            <strong>Thiếu kỹ năng sơ cứu</strong>
            <p>Khi bị thương giữa rừng, không có HDV hỗ trợ. Bạn cần tự xử lý trong khả năng của mình.</p>
          </div>
        </div>
        <div class="danger-item">
          <span class="danger-icon">🦎</span>
          <div>
            <strong>Động vật hoang dã</strong>
            <p>Rắn, côn trùng, lợn rừng... có thể gây nguy hiểm nếu bạn không biết cách xử lý.</p>
          </div>
        </div>
      </div>

      <h2>Chuẩn bị trước khi đi</h2>
      <p>Dưới đây là checklist những thứ bạn cần chuẩn bị:</p>
      <ul>
        <li>Nghiên cứu kỹ cung đường, tải offline map (Maps.me, Gaia GPS)</li>
        <li>Thông báo lịch trình cho người thân</li>
        <li>Mang đủ nước (tối thiểu 2L/người), lương khô, bộ sơ cứu</li>
        <li>Kiểm tra thời tiết 3 ngày trước khi đi</li>
        <li>Có phương án dự phòng (số điện thoại cứu hộ, đường rút ngắn)</li>
        <li>Mang theo thiết bị liên lạc (điện thoại đã sạc pin, pin dự phòng)</li>
      </ul>

      <div class="tip-box">
        <strong>📍 Mẹo an toàn:</strong> Luôn để lại kế hoạch chi tiết (lộ trình, thời gian dự kiến) cho ai đó ở nhà. Nếu không liên lạc được sau thời gian dự kiến, họ có thể báo cứu.
      </div>

      <h2>Khi nào nên đi theo tour?</h2>
      <p>Nếu bạn là người mới, chưa có kinh nghiệm đi rừng, hoặc đi đến cung đường khó (độ cao > 2000m, địa hình hiểm trở), hãy đi theo tour có HDV. Chi phí bỏ ra xứng đáng với sự an toàn của bạn.</p>
      
      <p><em>Adventure is out there – but safety comes first!</em></p>
    `,
  },
];

interface PageProps {
  params: Promise<{ id: string }>;
}

export default function ExperienceDetailPage({ params }: PageProps) {
  const { id } = use(params);
  const [readProgress, setReadProgress] = useState(0);
  const [isBookmarked, setIsBookmarked] = useState(false);
  const [showShareMenu, setShowShareMenu] = useState(false);

  const postId = parseInt(id);
  const post = blogPosts.find(p => p.id === postId);

  useEffect(() => {
    const handleScroll = () => {
      const scrollTop = window.scrollY;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const progress = (scrollTop / docHeight) * 100;
      setReadProgress(Math.min(progress, 100));
    };

    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  if (!post) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header />
        <main className="flex-grow pt-[81px] flex items-center justify-center">
          <div className="text-center py-20">
            <h1 className="text-3xl font-bold text-gray-900 mb-4">Bài viết không tìm thấy</h1>
            <Link href="/experience" className="text-emerald-600 hover:underline inline-flex items-center gap-2">
              <ArrowLeftIcon className="w-4 h-4" />
              Quay lại danh sách blog
            </Link>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  const relatedPosts = blogPosts.filter(p => p.id !== postId).slice(0, 3);

  return (
    <div className="min-h-screen flex flex-col bg-white">
      <Header />
      
      {/* Reading Progress Bar */}
      <div className="fixed top-[81px] left-0 right-0 h-1 bg-gray-100 z-20">
        <motion.div 
          className="h-full bg-emerald-500"
          style={{ width: `${readProgress}%` }}
          transition={{ duration: 0.1 }}
        />
      </div>

      <main className="flex-grow pt-[81px]">
        {/* Hero Image */}
        <div className="relative h-[50vh] sm:h-[60vh] lg:h-[70vh] overflow-hidden">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src={post.image}
            alt={post.title}
            className="w-full h-full object-cover"
            onError={(e) => {
              const target = e.target as HTMLImageElement;
              target.style.display = 'none';
              const parent = target.parentElement;
              if (parent) parent.style.background = 'linear-gradient(135deg, #16a249 0%, #10b981 100%)';
            }}
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
          
          {/* Hero Content */}
          <div className="absolute bottom-0 left-0 right-0 p-6 lg:p-12">
            <div className="container mx-auto max-w-4xl">
              {/* Category Badge */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="mb-4"
              >
                <span className="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-500 text-white text-sm font-semibold rounded-full">
                  <span className="w-2 h-2 bg-white rounded-full animate-pulse" />
                  {post.category}
                </span>
              </motion.div>
              
              {/* Title */}
              <motion.h1
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 }}
                className="text-2xl sm:text-3xl lg:text-5xl font-bold text-white mb-6 leading-tight"
              >
                {post.title}
              </motion.h1>
              
              {/* Meta Info */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2 }}
                className="flex flex-wrap items-center gap-6 text-white/90"
              >
                {/* Author */}
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                    {post.author[0]}
                  </div>
                  <div>
                    <p className="font-semibold">{post.author}</p>
                    <p className="text-sm text-white/70">{post.authorBio.split('.')[0]}</p>
                  </div>
                </div>
                
                {/* Date & Read Time */}
                <div className="flex items-center gap-4 text-sm">
                  <div className="flex items-center gap-2">
                    <CalendarIcon className="w-4 h-4" />
                    <span>{post.date}</span>
                  </div>
                  <div className="w-1 h-1 bg-white/50 rounded-full" />
                  <div className="flex items-center gap-2">
                    <ClockIcon className="w-4 h-4" />
                    <span>{post.readTime} đọc</span>
                  </div>
                </div>
              </motion.div>
            </div>
          </div>
        </div>

        {/* Content Area */}
        <div className="container mx-auto px-4 py-8 lg:py-12">
          <div className="max-w-3xl mx-auto">
            {/* Action Bar */}
            <motion.div
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 }}
              className="flex items-center justify-between py-4 border-b border-gray-100 mb-8"
            >
              {/* Back Link */}
              <Link href="/experience" className="inline-flex items-center gap-2 text-gray-500 hover:text-emerald-600 transition-colors">
                <ArrowLeftIcon className="w-4 h-4" />
                <span className="text-sm font-medium">Quay lại</span>
              </Link>
              
              {/* Actions */}
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setIsBookmarked(!isBookmarked)}
                  className={cn(
                    "p-2 rounded-full transition-all",
                    isBookmarked ? "bg-emerald-100 text-emerald-600" : "text-gray-400 hover:bg-gray-100"
                  )}
                >
                  <BookmarkIcon className={cn("w-5 h-5", isBookmarked && "fill-current")} />
                </button>
                <div className="relative">
                  <button
                    onClick={() => setShowShareMenu(!showShareMenu)}
                    className="p-2 rounded-full text-gray-400 hover:bg-gray-100 transition-colors"
                  >
                    <ShareIcon className="w-5 h-5" />
                  </button>
                  
                  {showShareMenu && (
                    <motion.div
                      initial={{ opacity: 0, scale: 0.95 }}
                      animate={{ opacity: 1, scale: 1 }}
                      className="absolute right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-100 p-2 min-w-[160px] z-10"
                    >
                      <button className="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                        <FacebookIcon className="w-4 h-4 text-blue-600" />
                        Facebook
                      </button>
                      <button className="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                        <TwitterIcon className="w-4 h-4 text-sky-500" />
                        Twitter
                      </button>
                      <button className="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                        <LinkedinIcon className="w-4 h-4 text-blue-700" />
                        LinkedIn
                      </button>
                      <button className="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                        <LinkIcon className="w-4 h-4" />
                        Copy Link
                      </button>
                    </motion.div>
                  )}
                </div>
              </div>
            </motion.div>

            {/* Article Content */}
            <motion.article
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.4 }}
              className="prose prose-lg prose-gray max-w-none"
            >
              <style jsx>{`
                .prose {
                  --tw-prose-body: #374151;
                  --tw-prose-headings: #111827;
                  font-size: 1.125rem;
                  line-height: 1.8;
                }
                .prose :global(p) {
                  margin-bottom: 1.5rem;
                }
                .prose :global(h2) {
                  font-size: 1.75rem;
                  font-weight: 700;
                  margin-top: 3rem;
                  margin-bottom: 1.5rem;
                  color: #111827;
                }
                .prose :global(h3) {
                  font-size: 1.375rem;
                  font-weight: 600;
                  margin-top: 2rem;
                  margin-bottom: 1rem;
                  color: #111827;
                }
                .prose :global(ul), .prose :global(ol) {
                  margin-bottom: 1.5rem;
                  padding-left: 1.5rem;
                }
                .prose :global(li) {
                  margin-bottom: 0.75rem;
                }
                .prose :global(blockquote) {
                  border-left: 4px solid #16a249;
                  padding-left: 1.5rem;
                  margin: 2rem 0;
                  font-style: italic;
                  color: #6b7280;
                  font-size: 1.25rem;
                }
                .lead {
                  font-size: 1.375rem;
                  color: #374151;
                  margin-bottom: 2rem;
                  font-weight: 400;
                }
                .tip-box {
                  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
                  border: 1px solid #bbf7d0;
                  border-radius: 12px;
                  padding: 1.25rem;
                  margin: 2rem 0;
                }
                .warning-box {
                  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                  border: 1px solid #fcd34d;
                  border-radius: 12px;
                  padding: 1.25rem;
                  margin: 2rem 0;
                }
                .comparison-grid {
                  display: grid;
                  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                  gap: 1rem;
                  margin: 1.5rem 0;
                }
                .comparison-item {
                  background: #f9fafb;
                  border-radius: 12px;
                  padding: 1.25rem;
                  border: 1px solid #e5e7eb;
                }
                .comparison-item h4 {
                  font-weight: 600;
                  color: #111827;
                  margin-bottom: 0.5rem;
                }
                .comparison-item p {
                  font-size: 0.9rem;
                  color: #6b7280;
                  margin: 0;
                }
                .danger-list {
                  display: flex;
                  flex-direction: column;
                  gap: 1rem;
                  margin: 1.5rem 0;
                }
                .danger-item {
                  display: flex;
                  gap: 1rem;
                  padding: 1rem;
                  background: #fef2f2;
                  border-radius: 12px;
                  border: 1px solid #fecaca;
                }
                .danger-icon {
                  font-size: 1.5rem;
                }
                .danger-item strong {
                  display: block;
                  color: #991b1b;
                  margin-bottom: 0.25rem;
                }
                .danger-item p {
                  font-size: 0.9rem;
                  color: #7f1d1d;
                  margin: 0;
                }
                .conclusion {
                  background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
                  border-radius: 12px;
                  padding: 1.5rem;
                  margin-top: 3rem;
                }
              `}</style>
              <div dangerouslySetInnerHTML={{ __html: post.content }} />
            </motion.article>

            {/* Tags */}
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.5 }}
              className="flex flex-wrap gap-2 mt-12 pt-8 border-t border-gray-100"
            >
              {post.tags.map((tag, index) => (
                <span
                  key={index}
                  className="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-full hover:bg-emerald-100 hover:text-emerald-700 transition-colors cursor-pointer"
                >
                  #{tag}
                </span>
              ))}
            </motion.div>

            {/* Author Bio Card */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.6 }}
              className="mt-12 p-8 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl"
            >
              <div className="flex items-start gap-6">
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg flex-shrink-0">
                  {post.author[0]}
                </div>
                <div>
                  <h4 className="text-lg font-bold text-gray-900 mb-1">{post.author}</h4>
                  <p className="text-emerald-600 text-sm font-medium mb-3">Tác giả Đôi Dép Adventure</p>
                  <p className="text-gray-600 leading-relaxed">{post.authorBio}</p>
                </div>
              </div>
            </motion.div>

            {/* Related Posts */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.7 }}
              className="mt-16"
            >
              <h3 className="text-2xl font-bold text-gray-900 mb-8">Bài viết liên quan</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {relatedPosts.map((relatedPost, index) => (
                  <Link
                    key={relatedPost.id}
                    href={`/experience/${relatedPost.id}`}
                    className="group"
                  >
                    <div className="bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                      <div className="relative h-40 overflow-hidden">
                        {/* eslint-disable-next-line @next/next/no-img-element */}
                        <img
                          src={relatedPost.image}
                          alt={relatedPost.title}
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                          onError={(e) => {
                            const target = e.target as HTMLImageElement;
                            target.style.display = 'none';
                            const parent = target.parentElement;
                            if (parent) parent.style.background = 'linear-gradient(135deg, #16a249 0%, #10b981 100%)';
                          }}
                        />
                        <span className="absolute top-3 left-3 px-2 py-1 bg-emerald-500 text-white text-xs font-semibold rounded-full">
                          {relatedPost.category}
                        </span>
                      </div>
                      <div className="p-4">
                        <h4 className="font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 mb-2">
                          {relatedPost.title}
                        </h4>
                        <div className="flex items-center gap-2 text-sm text-gray-500">
                          <CalendarIcon className="w-3.5 h-3.5" />
                          <span>{relatedPost.date}</span>
                          <span className="mx-1">•</span>
                          <ClockIcon className="w-3.5 h-3.5" />
                          <span>{relatedPost.readTime}</span>
                        </div>
                      </div>
                    </div>
                  </Link>
                ))}
              </div>
            </motion.div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}