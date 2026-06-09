"use client";

import Image from "next/image";
import Link from "next/link";
import { ArrowRightIcon, CalendarIcon } from "@/components/icons";
import { cn } from "@/lib/utils";

const blogPosts = [
  {
    id: 1,
    title: "Mẹo Chọn Giày Khi Đi Trekking Không Bị Đau Chân",
    excerpt: "Việc chọn đúng đôi giày trekking có thể quyết định trải nghiệm của bạn. Hãy cùng khám phá những bí quyết...",
    author: "Ne",
    date: "30/1/2026",
    category: "Kinh nghiệm",
    image: "/images/blog-1.jpg",
    color: "from-emerald-500 to-emerald-600",
  },
  {
    id: 2,
    title: "Trekking Tự Túc: Lợi Ích & Nguy Hiểm Cần Biết",
    excerpt: "Trekking tự túc mang lại nhiều trải nghiệm độc đáo nhưng cũng tiềm ẩn không ít nguy hiểm...",
    author: "Mi",
    date: "30/1/2026",
    category: "An toàn",
    image: "/images/blog-2.jpg",
    color: "from-red-500 to-red-600",
  },
  {
    id: 3,
    title: "Hành Trình Lên Đỉnh Langbiang – Cẩm Nang Cho Người Mới",
    excerpt: "Chinh phục đỉnh Langbiang 2163m là trải nghiệm đáng nhớ. Cùng lắng nghe chia sẻ từ dân trekking...",
    author: "An",
    date: "28/1/2026",
    category: "Địa điểm",
    image: "/images/blog-3.jpg",
    color: "from-blue-500 to-blue-600",
  },
];

export function BlogSection() {
  return (
    <section className="py-16 lg:py-24 bg-gray-50">
      <div className="container mx-auto px-4 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-12 gap-4">
          <div>
            <span className="inline-flex items-center gap-2 px-4 py-1.5 text-sm font-medium text-purple-700 bg-purple-100 rounded-full mb-4">
              <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
              </svg>
              Blogs & Stories
            </span>
            <h2 className="text-3xl lg:text-4xl font-bold text-gray-900">
              Kinh nghiệm & <span className="text-emerald-600">Chia sẻ</span>
            </h2>
            <p className="text-gray-500 mt-2 max-w-xl">
              Những câu chuyện và trải nghiệm thực tế từ cộng đồng Đôi Dép Adventure
            </p>
          </div>
          <Link
            href="/experience"
            className="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700 hover:gap-3 transition-all flex-shrink-0"
          >
            Xem tất cả bài viết
            <ArrowRightIcon className="w-4 h-4" />
          </Link>
        </div>

        {/* Blog Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          {blogPosts.map((post) => (
            <Link
              key={post.id}
              href={`/experience/${post.id}`}
              className={cn(
                "group block bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100",
                "hover:shadow-xl hover:border-transparent transition-all duration-300",
                "hover:-translate-y-1"
              )}
            >
              {/* Image */}
              <div className="relative h-48 overflow-hidden">
                <Image
                  src={post.image}
                  alt={post.title}
                  fill
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw"
                  className="object-cover transition-transform duration-500 group-hover:scale-110"
                />
                {/* Category Badge */}
                <div className="absolute top-4 left-4">
                  <span className={cn(
                    "px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold",
                    "text-gray-700"
                  )}>
                    {post.category}
                  </span>
                </div>
              </div>

              {/* Content */}
              <div className="p-6">
                {/* Author & Date */}
                <div className="flex items-center gap-3 mb-4">
                  <div className={cn(
                    "w-9 h-9 rounded-full bg-gradient-to-br flex items-center justify-center",
                    post.color
                  )}>
                    <span className="text-white text-xs font-bold">{post.author[0]}</span>
                  </div>
                  <div className="flex items-center gap-1.5 text-xs text-gray-500">
                    <span className="font-medium text-gray-700">{post.author}</span>
                    <span>•</span>
                    <CalendarIcon className="w-3.5 h-3.5" />
                    <span>{post.date}</span>
                  </div>
                </div>

                {/* Title */}
                <h3 className="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-emerald-600 transition-colors">
                  {post.title}
                </h3>

                {/* Excerpt */}
                <p className="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                  {post.excerpt}
                </p>

                {/* Read More */}
                <div className="flex items-center gap-2 text-sm font-semibold text-emerald-600 group-hover:gap-3 transition-all">
                  Đọc tiếp
                  <ArrowRightIcon className="w-4 h-4" />
                </div>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
