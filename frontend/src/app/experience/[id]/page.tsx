"use client";

import { use, useState, useEffect } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { ArrowLeftIcon, CalendarIcon, ClockIcon, ShareIcon, BookmarkIcon, FacebookIcon, TwitterIcon, LinkedinIcon, LinkIcon } from "@/components/icons";
import { cn } from "@/lib/utils";
import { getBlogPost, getBlogPosts, ApiBlogPost } from "@/lib/api";

interface PageProps {
  params: Promise<{ id: string }>;
}

export default function ExperienceDetailPage({ params }: PageProps) {
  const { id } = use(params);
  const [post, setPost] = useState<ApiBlogPost | null>(null);
  const [relatedPosts, setRelatedPosts] = useState<ApiBlogPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [readProgress, setReadProgress] = useState(0);
  const [isBookmarked, setIsBookmarked] = useState(false);
  const [showShareMenu, setShowShareMenu] = useState(false);

  useEffect(() => {
    if (!id) return;
    setLoading(true);
    setError(null);

    Promise.all([getBlogPost(id), getBlogPosts()])
      .then(([detailData, listData]) => {
        setPost(detailData);
        // Related posts: filter out current post
        const filtered = listData.filter(p => p.id !== detailData.id).slice(0, 3);
        setRelatedPosts(filtered);
        setLoading(false);
      })
      .catch((err) => {
        console.error(err);
        setError("Không thể tải thông tin bài viết. Vui lòng thử lại sau.");
        setLoading(false);
      });
  }, [id]);

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

  if (loading) {
    return (
      <div className="min-h-screen flex flex-col bg-white">
        <Header />
        <main className="flex-grow pt-[81px]">
          {/* Skeleton Hero */}
          <div className="relative h-[50vh] bg-gray-200 animate-pulse" />
          <div className="container mx-auto px-4 py-8 lg:py-12 max-w-3xl animate-pulse">
            <div className="h-4 bg-gray-200 rounded w-24 mb-4" />
            <div className="h-10 bg-gray-200 rounded w-full mb-6" />
            <div className="h-6 bg-gray-200 rounded w-2/3 mb-8" />
            <div className="space-y-4">
              <div className="h-4 bg-gray-200 rounded w-full animate-pulse" />
              <div className="h-4 bg-gray-200 rounded w-full animate-pulse" />
              <div className="h-4 bg-gray-200 rounded w-5/6 animate-pulse" />
              <div className="h-4 bg-gray-200 rounded w-4/5 animate-pulse" />
            </div>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  if (error || !post) {
    return (
      <div className="min-h-screen flex flex-col bg-white">
        <Header />
        <main className="flex-grow pt-[81px] flex items-center justify-center">
          <div className="text-center py-20 px-4 max-w-md">
            <h1 className="text-3xl font-bold text-gray-900 mb-4">Bài viết không tìm thấy</h1>
            <p className="text-gray-500 mb-6">{error || "Bài viết bạn yêu cầu không tồn tại hoặc đã bị xóa."}</p>
            <Link href="/experience" className="text-emerald-600 hover:underline inline-flex items-center gap-2 font-semibold">
              <ArrowLeftIcon className="w-4 h-4" />
              Quay lại danh sách blog
            </Link>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

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
              target.onerror = null;
              target.src = '/images/logo.png';
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
                    {post.author ? post.author[0] : "A"}
                  </div>
                  <div>
                    <p className="font-semibold">{post.author}</p>
                    <p className="text-sm text-white/70">{post.author_bio ? post.author_bio.split('.')[0] : "Tác giả Doi Dep Adventure"}</p>
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
                    <span>{post.read_time} đọc</span>
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
                      <button 
                        onClick={() => {
                          navigator.clipboard.writeText(window.location.href);
                          setShowShareMenu(false);
                          alert("Đã sao chép liên kết bài viết!");
                        }}
                        className="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg"
                      >
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
            {post.tags && post.tags.length > 0 && (
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
            )}

            {/* Author Bio Card */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.6 }}
              className="mt-12 p-8 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl"
            >
              <div className="flex items-start gap-6">
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg flex-shrink-0">
                  {post.author ? post.author[0] : "A"}
                </div>
                <div>
                  <h4 className="text-lg font-bold text-gray-900 mb-1">{post.author}</h4>
                  <p className="text-emerald-600 text-sm font-medium mb-3">Tác giả Đôi Dép Adventure</p>
                  <p className="text-gray-600 leading-relaxed">{post.author_bio || "Đội ngũ biên tập viên Đôi Dép Adventure."}</p>
                </div>
              </div>
            </motion.div>

            {/* Related Posts */}
            {relatedPosts.length > 0 && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.7 }}
                className="mt-16"
              >
                <h3 className="text-2xl font-bold text-gray-900 mb-8">Bài viết liên quan</h3>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                  {relatedPosts.map((relatedPost) => (
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
                              target.onerror = null;
                              target.src = '/images/logo.png';
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
                            <span>{relatedPost.read_time}</span>
                          </div>
                        </div>
                      </div>
                    </Link>
                  ))}
                </div>
              </motion.div>
            )}
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}