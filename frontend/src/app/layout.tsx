import type { Metadata } from "next";
import { Be_Vietnam_Pro } from "next/font/google";
import "./globals.css";

const beVietnamPro = Be_Vietnam_Pro({
  subsets: ["latin", "vietnamese"],
  weight: ["300", "400", "500", "600", "700", "800"],
  display: "swap",
  variable: "--font-be-vietnam-pro",
  preload: true,
  fallback: ["system-ui", "sans-serif"],
});

export const metadata: Metadata = {
  title: "Đôi Dép Adventure - Trải nghiệm phiêu lưu thiên nhiên",
  description: "Đôi Dép Adventure chuyên cung cấp các tour trekking, camping phiêu lưu an toàn và đáng nhớ. Trải nghiệm thiên nhiên cùng đội ngũ chuyên nghiệp.",
  keywords: "trekking, camping, adventure, travel, Vietnam tours",
  openGraph: {
    title: "Đôi Dép Adventure - Trải nghiệm phiêu lưu thiên nhiên",
    description: "Đôi Dép Adventure chuyên cung cấp các tour trekking, camping phiêu lưu an toàn và đáng nhớ.",
    images: ["https://i.postimg.cc/pr3jTYMs/582549528-122103437163116307-4161394095605531748-n.jpg"],
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="vi" className={`${beVietnamPro.variable} h-full antialiased`}>
      <body className={`${beVietnamPro.className} min-h-full flex flex-col`}>
        {children}
      </body>
    </html>
  );
}
