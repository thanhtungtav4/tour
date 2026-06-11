"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { ArrowRightIcon, CalendarIcon } from "@/components/icons";
import { cn } from "@/lib/utils";
import { getBlogPosts, ApiBlogPost } from "@/lib/api";

export function BlogSection() {
  const [posts, setPosts] = useState<ApiBlogPost[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getBlogPosts()
      .then((data) => {
        setPosts(data.slice(0, 3));
        setLoading(false);
      })
      .catch((err) => {
        console.error(err);
        setLoading(false);
      });
  }, []);

  return (
    <section className="py-16 lg:py-24 bg-gray-50">
      <div className="container mx-auto px-4 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-12 gap-4">
          <div>
            <span className="inline-flex items-center gap-2 px-4 py-1.5 text-sm font-medium text-[#16a249] bg-[rgba(22,162,73,0.1)] rounded-full mb-4">
              <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
              </svg>
              Blogs & Stories
            </span>
            <h2 className="text-3xl lg:text-4xl font-bold text-gray-900">
              Kinh nghiệm & <span className="text-[#16a249]">Chia sẻ</span>
            </h2>
            <p className="text-gray-500 mt-2 max-w-xl">
              Những câu chuyện và trải nghiệm thực tế từ cộng đồng Đôi Dép Adventure
            </p>
          </div>
          <Link
            href="/experience"
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#16a249] hover:text-emerald-800 hover:gap-3 transition-all flex-shrink-0"
          >
            Xem tất cả bài viết
            <ArrowRightIcon className="w-4 h-4" />
          </Link>
        </div>

        {/* Blog Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          {loading ? (
            /* Skeletons */
            [...Array(3)].map((_, i) => (
              <div key={i} className="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 animate-pulse">
                <div className="h-48 bg-gray-200" />
                <div className="p-6">
                  <div className="flex items-center gap-3 mb-4">
                    <div className="w-9 h-9 rounded-full bg-gray-200" />
                    <div className="h-4 bg-gray-200 rounded w-24" />
                  </div>
                  <div className="h-6 bg-gray-200 rounded w-full mb-3" />
                  <div className="h-4 bg-gray-200 rounded w-full mb-2" />
                  <div className="h-4 bg-gray-200 rounded w-5/6 mb-4" />
                  <div className="h-4 bg-gray-200 rounded w-16" />
                </div>
              </div>
            ))
          ) : posts.length === 0 ? (
            <div className="col-span-full text-center py-8 text-gray-500 bg-white rounded-2xl border border-gray-100">
              Chưa có bài viết nào được đăng tải.
            </div>
          ) : (
            posts.map((post) => {
              const cleanColor = post.color || "from-emerald-500 to-emerald-600";
              return (
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
                  <div className="relative h-48 overflow-hidden bg-gray-100">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={post.image}
                      alt={post.title}
                      className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                      onError={(e) => {
                        const target = e.target as HTMLImageElement;
                        target.onerror = null;
                        target.src = '/images/default-tour.jpg';
                      }}
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
                        "w-9 h-9 rounded-full bg-gradient-to-br flex items-center justify-center text-white text-xs font-bold",
                        cleanColor
                      )}>
                        <span>{post.author ? post.author[0] : "A"}</span>
                      </div>
                      <div className="flex items-center gap-1.5 text-xs text-gray-500">
                        <span className="font-medium text-gray-700">{post.author}</span>
                        <span>•</span>
                        <CalendarIcon className="w-3.5 h-3.5" />
                        <span>{post.date}</span>
                      </div>
                    </div>

                    {/* Title */}
                    <h3 className="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-[#16a249] transition-colors">
                      {post.title}
                    </h3>

                    {/* Excerpt */}
                    <p className="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                      {post.excerpt}
                    </p>

                    {/* Read More */}
                    <div className="flex items-center gap-2 text-sm font-semibold text-[#16a249] group-hover:gap-3 transition-all">
                      Đọc tiếp
                      <ArrowRightIcon className="w-4 h-4" />
                    </div>
                  </div>
                </Link>
              );
            })
          )}
        </div>
      </div>
    </section>
  );
}
