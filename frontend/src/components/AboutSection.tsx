"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { cn } from "@/lib/utils";
import { UsersIcon, SparklesIcon, FlameIcon, WalletIcon } from "@/components/icons";

const features = [
  {
    icon: UsersIcon,
    gradient: "from-[#16a249] to-[#10b981]",
    title: "Đội ngũ hướng dẫn viên chuyên nghiệp",
    description: "Đội ngũ hướng dẫn viên nhiệt huyết, am hiểu từng địa danh",
  },
  {
    icon: SparklesIcon,
    gradient: "from-[#8b5cf6] to-[#a855f7]",
    title: "An toàn là ưu tiên số một",
    description: "An toàn luôn là ưu tiên hàng đầu trong mọi chuyến đi",
  },
  {
    icon: FlameIcon,
    gradient: "from-[#f43f5e] to-[#fb7185]",
    title: "Trải nghiệm cá nhân hóa",
    description: "Mỗi hành trình đều được thiết kế riêng theo nhu cầu của bạn",
  },
  {
    icon: WalletIcon,
    gradient: "from-[#f59e0b] to-[#fbbf24]",
    title: "Giá cả minh bạch",
    description: "Cam kết giá cả rõ ràng, không phí ẩn hay chi phí phát sinh",
  },
];

export function AboutSection() {
  return (
    <section className="py-16 lg:py-24 overflow-hidden bg-gradient-to-b from-[#f8fafc] to-white">
      <div className="container mx-auto px-4">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          {/* Image Container */}
          <motion.div
            className="relative rounded-3xl overflow-hidden min-h-[420px] group"
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
          >
            <Image
              src="/images/about-adventure.jpg"
              alt="Đôi Dép Adventure adventure team"
              fill
              sizes="(max-width: 1024px) 100vw, 50vw"
              className="object-cover transition-transform duration-500 group-hover:scale-105"
            />
            {/* Overlay gradient */}
            <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent" />
          </motion.div>

          {/* Content */}
          <motion.div
            className="content"
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            <span className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[#16a249] bg-[rgba(22,162,73,0.1)] rounded-full mb-6">
              <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
              </svg>
              Tại sao chọn Đôi Dép Adventure
            </span>

            <h2 className="text-3xl lg:text-4xl font-extrabold text-[#0e1425] mb-8 leading-tight">
              Đối tác tin cậy cho
              <span className="text-[#16a249]"> hành trình đáng nhớ</span>
            </h2>

            <div className="features-list space-y-4">
              {features.map((feature, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.5, delay: index * 0.1 }}
                  className="group flex gap-5 p-4 rounded-2xl bg-white shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100"
                >
                  <div
                    className={cn(
                      "w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0",
                      "bg-gradient-to-br shadow-lg group-hover:scale-110 transition-transform duration-300",
                      feature.gradient
                    )}
                  >
                    <feature.icon className="w-6 h-6 text-white" />
                  </div>
                  <div className="feature-text py-1">
                    <h3 className="text-lg font-bold text-[#0e1425] mb-1 group-hover:text-[#16a249] transition-colors">
                      {feature.title}
                    </h3>
                    <p className="text-sm text-[#6b7280] leading-relaxed">
                      {feature.description}
                    </p>
                  </div>
                </motion.div>
              ))}
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}