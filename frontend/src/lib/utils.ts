import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

const DEFAULT_TOUR_IMAGE = "/images/default-tour.jpg";

/**
 * Kiểm tra xem URL ảnh có phải là ảnh thật từ WP hay chỉ là fallback logo
 */
function isRealImage(url: string): boolean {
  if (!url || url === "") return false;
  if (url === "/images/logo.png" || url === "/images/logo.jpg" || url === "/images/default-tour.jpg") return false;
  return true;
}

/**
 * Lấy ảnh đại diện (thumbnail) cho tour.
 * Ưu tiên: ảnh từ WP API → ảnh mặc định Đôi Dép
 */
export function getTourImage(thumbnail: string): string {
  if (isRealImage(thumbnail)) return thumbnail;
  return DEFAULT_TOUR_IMAGE;
}

/**
 * Lấy gallery cho tour.
 * Nếu gallery từ API chỉ chứa logo fallback, thay bằng ảnh mặc định.
 */
export function getTourGallery(gallery: string[]): string[] {
  const filtered = gallery.filter(isRealImage);
  if (filtered.length > 0) return filtered;
  return [DEFAULT_TOUR_IMAGE];
}

