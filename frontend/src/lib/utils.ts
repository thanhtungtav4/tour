import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

// Ảnh local fallback cho tour khi WP chưa upload ảnh đại diện
const TOUR_IMAGE_MAP: Record<string, string> = {
  "langbiang": "/images/langbiang.jpg",
  "nui-chua-chan": "/images/nui-chua-chan.jpg",
  "rung-cat-tien": "/images/rung-cat-tien.jpg",
  "ta-cu-ke-ga": "/images/ta-cu-ke-ga.jpg",
  "bu-gia-map": "/images/bu-gia-map.jpg",
  "nui-dinh": "/images/nui-dinh.jpg",
  "nui-minh-dam": "/images/nui-minh-dam.jpg",
  "brahyang": "/images/brahyang.jpg",
  "yangdoan": "/images/yangdoan.jpg",
  "thac-lieng-ai": "/images/thac-lieng-ai.jpg",
  "thao-nguyen-palsol": "/images/thao-nguyen-palsol.jpg",
};

const DEFAULT_TOUR_IMAGE = "/images/banner-trekking.jpg";

/**
 * Kiểm tra xem URL ảnh có phải là ảnh thật từ WP hay chỉ là fallback logo
 */
function isRealImage(url: string): boolean {
  if (!url || url === "") return false;
  // Nếu là logo fallback hoặc path tương đối không phải ảnh tour thật
  if (url === "/images/logo.png" || url === "/images/logo.jpg") return false;
  return true;
}

/**
 * Lấy ảnh đại diện (thumbnail) cho tour.
 * Ưu tiên: ảnh từ WP API → ảnh local theo slug → ảnh mặc định
 */
export function getTourImage(thumbnail: string, slug?: string): string {
  if (isRealImage(thumbnail)) return thumbnail;
  if (slug && TOUR_IMAGE_MAP[slug]) return TOUR_IMAGE_MAP[slug];
  return DEFAULT_TOUR_IMAGE;
}

/**
 * Lấy gallery cho tour.
 * Nếu gallery từ API chỉ chứa logo fallback, thay bằng ảnh local đẹp.
 */
export function getTourGallery(gallery: string[], slug?: string): string[] {
  const filtered = gallery.filter(isRealImage);
  if (filtered.length > 0) return filtered;
  // Fallback: dùng ảnh local theo slug
  const fallback = slug && TOUR_IMAGE_MAP[slug] ? TOUR_IMAGE_MAP[slug] : DEFAULT_TOUR_IMAGE;
  return [fallback];
}
