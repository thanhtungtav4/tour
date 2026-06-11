"use client";

import { useState, useEffect } from "react";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { ArrowRightIcon } from "@/components/icons";
import { getBlogPosts, ApiBlogPost } from "@/lib/api";

export default function ExperiencePage() {
  const [posts, setPosts] = useState<ApiBlogPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getBlogPosts()
      .then((data) => {
        setPosts(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error(err);
        setError("Không thể tải danh sách bài viết. Vui lòng thử lại sau.");
        setLoading(false);
      });
  }, []);

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
            {loading ? (
              /* Loading Skeletons */
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {[...Array(6)].map((_, i) => (
                  <div
                    key={i}
                    className="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 animate-pulse"
                  >
                    <div className="h-52 bg-gray-200" />
                    <div className="p-6">
                      <div className="flex items-center gap-3 mb-4">
                        <div className="w-8 h-8 rounded-full bg-gray-200" />
                        <div className="h-4 bg-gray-200 rounded w-24" />
                      </div>
                      <div className="h-6 bg-gray-200 rounded w-full mb-3" />
                      <div className="h-6 bg-gray-200 rounded w-2/3 mb-4" />
                      <div className="h-4 bg-gray-200 rounded w-full mb-2" />
                      <div className="h-4 bg-gray-200 rounded w-5/6 mb-4" />
                      <div className="h-4 bg-gray-200 rounded w-16" />
                    </div>
                  </div>
                ))}
              </div>
            ) : error ? (
              /* Error message */
              <div className="text-center py-12 bg-red-50/50 rounded-2xl border border-red-100 max-w-lg mx-auto">
                <p className="text-red-600 font-medium">{error}</p>
                <button
                  onClick={() => {
                    setLoading(true);
                    setError(null);
                    getBlogPosts()
                      .then((data) => {
                        setPosts(data);
                        setLoading(false);
                      })
                      .catch((err) => {
                        console.error(err);
                        setError("Không thể tải danh sách bài viết. Vui lòng thử lại sau.");
                        setLoading(false);
                      });
                  }}
                  className="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors"
                >
                  Tử lại
                </button>
              </div>
            ) : posts.length === 0 ? (
              /* Empty state */
              <div className="text-center py-12">
                <p className="text-gray-500">Chưa có bài viết nào được đăng tải.</p>
              </div>
            ) : (
              /* Posts Grid */
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {posts.map((post) => (
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
                          target.onerror = null;
                          target.src = '/images/default-tour.jpg';
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
                          <span className="text-white text-xs font-semibold">
                            {post.author ? post.author[0] : "A"}
                          </span>
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
            )}
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}