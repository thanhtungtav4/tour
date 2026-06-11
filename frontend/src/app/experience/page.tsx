"use client";

import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { ArrowRightIcon } from "@/components/icons";

const blogPosts = [
  {
    id: 1,
    title: "Mẹo Chọn Giày Khi Đi Trekking Không Bị Đau Chân – Bí Quyết Dân Trekking Cần Biết",
    excerpt: "Việc chọn đúng đôi giày trekking có thể quyết định trải nghiệm của bạn. Hãy cùng Đôi Dép Adventure khám phá những bí quyết...",
    author: "Ne",
    date: "30/1/2026",
    category: "Kinh nghiệm",
    image: "/images/blog-1.jpg",
  },
  {
    id: 2,
    title: "Trekking Tự Túc: Lợi Ích & Nguy Hiểm – Những Điều Cần Lưu Ý Trước Chuyến Đi",
    excerpt: "Trekking tự túc mang lại nhiều trải nghiệm độc đáo nhưng cũng tiềm ẩn không ít nguy hiểm. Cùng tìm hiểu...",
    author: "Mi",
    date: "30/1/2026",
    category: "An toàn",
    image: "/images/blog-2.jpg",
  },
  {
    id: 3,
    title: "Top 10 Đỉnh Núi Trekking Đẹp Nhất Việt Nam",
    excerpt: "Việt Nam có vô số đỉnh núi đẹp mê hồn, từ Bắc vào Nam. Cùng Đôi Dép Adventure khám phá top 10 đỉnh núi không thể bỏ qua...",
    author: "Admin",
    date: "25/1/2026",
    category: "Địa điểm",
    image: "/images/langbiang.jpg",
  },
  {
    id: 4,
    title: "Camping 101: Hướng Dẫn Cho Người Mới Bắt Đầu",
    excerpt: "Bạn mới bắt đầu với camping? Đừng lo lắng! Đôi Dép Adventure sẽ hướng dẫn bạn từ A đến Z để có một chuyến camping hoàn hảo...",
    author: "Hoàng Nam",
    date: "20/1/2026",
    category: "Hướng dẫn",
    image: "/images/bu-gia-map.jpg",
  },
  {
    id: 5,
    title: "Những Sai Lầm Thường Gặp Khi Đi Trekking Mùa Mưa",
    excerpt: "Đi trekking mùa mưa có những rủi ro riêng. Hãy tránh những sai lầm phổ biến để chuyến đi của bạn an toàn hơn...",
    author: "Thu Hà",
    date: "15/1/2026",
    category: "An toàn",
    image: "/images/rung-cat-tien.jpg",
  },
  {
    id: 6,
    title: "Trải Nghiệm Trekking Đỉnh Langbiang - Ký Ức Không Quên",
    excerpt: "Chinh phục đỉnh Langbiang 2163m là một trong những trải nghiệm đáng nhớ nhất của nhiều trekker. Cùng lắng nghe...",
    author: "Văn Đức",
    date: "10/1/2026",
    category: "Trải nghiệm",
    image: "/images/ta-cu-ke-ga.jpg",
  },
];

export default function ExperiencePage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero */}
        <section className="py-16 px-4 bg-gradient-to-b from-white to-[#f5f7fa]">
          <div className="container mx-auto text-center">
            <span className="inline-block px-4 py-2 text-sm font-semibold text-[#16a249] bg-[rgba(22,162,73,0.1)] rounded-full mb-4">
              Kinh nghiệm & Chia sẻ
            </span>
            <h1 className="text-4xl lg:text-6xl font-extrabold text-[#0e1425] mb-4">
              Blogs & Stories
            </h1>
            <p className="text-lg text-[#6b7280] max-w-2xl mx-auto">
              Những câu chuyện và trải nghiệm thực tế từ cộng đồng Đôi Dép Adventure
            </p>
          </div>
        </section>

        {/* Blog Grid */}
        <section className="section-padding">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {blogPosts.map((post) => (
                <article
                  key={post.id}
                  className="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all group"
                >
                  <div className="relative h-52 overflow-hidden">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={post.image}
                      alt={post.title}
                      className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                      onError={(e) => {
                        const target = e.target as HTMLImageElement;
                        target.style.display = 'none';
                        const parent = target.parentElement;
                        if (parent) parent.style.background = 'linear-gradient(135deg, #16a249 0%, #10b981 100%)';
                      }}
                    />
                    <div className="absolute top-4 left-4">
                      <span className="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-[#16a249]">
                        {post.category}
                      </span>
                    </div>
                  </div>
                  <div className="p-6">
                    <div className="flex items-center gap-3 mb-4">
                      <div className="w-8 h-8 rounded-full bg-gradient-to-br from-[#16a249] to-[#10b981] flex items-center justify-center">
                        <span className="text-white text-xs font-semibold">{post.author[0]}</span>
                      </div>
                      <div className="text-sm text-[#6b7280]">
                        <span className="font-medium text-[#0e1425]">{post.author}</span>
                        <span className="mx-2">•</span>
                        <span>{post.date}</span>
                      </div>
                    </div>
                    <h3 className="text-lg font-bold text-[#0e1425] mb-3 line-clamp-2 group-hover:text-[#16a249] transition-colors">
                      {post.title}
                    </h3>
                    <p className="text-sm text-[#6b7280] mb-4 line-clamp-2">
                      {post.excerpt}
                    </p>
                    <a
                      href={`/experience/${post.id}`}
                      className="inline-flex items-center gap-2 text-sm font-semibold text-[#16a249] hover:gap-3 transition-all"
                    >
                      Đọc tiếp
                      <ArrowRightIcon className="w-4 h-4" />
                    </a>
                  </div>
                </article>
              ))}
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}