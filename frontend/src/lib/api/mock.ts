import {
  TourListItem,
  TourDetail,
  RentalItem,
  PickupPoint,
  BookingRequest,
  BookingResponse,
  BookingDetail,
  PaymentInfo,
} from "./types";

// Mock delay to simulate network
const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));

// Mock data - transformed from existing tours.ts
const mockTours = [
  {
    id: 1,
    slug: "langbiang",
    name: "Langbiang",
    description: "Chuyến đi Trekking chinh phục đỉnh Langbiang 2163m",
    imageFilename: "langbiang.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
      "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80",
      "https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800&q=80",
      "https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800&q=80",
      "https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80",
    ],
    price: 450000,
    difficulty: "easy" as const,
    duration: "1 ngày",
    availableSpots: 21,
    departureTime: "Sáng",
    highlights: ["Đỉnh Langbiang 2163m", "Thung lũng tình yêu", "Cao nguyên hoa"],
    departureDates: [
      { date: "2026-06-14", availableSpots: 15 },
      { date: "2026-06-21", availableSpots: 21 },
      { date: "2026-06-28", availableSpots: 18 },
      { date: "2026-07-05", availableSpots: 21 },
    ],
    services: [
      { id: "transport", name: "Xe đưa đón", description: "Xe đưa đón từ TP.HCM (khứ hồi)", price: 100000, unit: "người" },
      { id: "insurance", name: "Bảo hiểm nâng cao", description: "Bảo hiểm du lịch với mức bồi thường cao hơn", price: 50000, unit: "người" },
      { id: "gear", name: "Thuê trang bị", description: "Balo, gậy trekking, giày dép", price: 80000, unit: "người" },
      { id: "meal", name: "Bữa trưa nâng cao", description: "Menu đặc biệt với các món địa phương", price: 120000, unit: "người" },
      { id: "photo", name: "Chụp ảnh chuyên nghiệp", description: "Photographer đi cùng tour", price: 150000, unit: "người" },
    ],
  },
  {
    id: 2,
    slug: "rung-cat-tien",
    name: "Rừng Cát Tiên",
    description: "Đạp xe và đi bộ khám phá rừng ngập mặn",
    imageFilename: "rung-cat-tien.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?w=800&q=80",
      "https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=800&q=80",
      "https://images.unsplash.com/photo-1542273917363-3b1817f69a8d?w=800&q=80",
      "https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&q=80",
      "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80",
    ],
    price: 380000,
    difficulty: "easy" as const,
    duration: "1 ngày",
    availableSpots: 17,
    departureTime: "Sáng",
    highlights: ["Rừng ngập mặn", "Động vật hoang dã", "Thiên nhiên nguyên sơ"],
    departureDates: [
      { date: "2026-06-15", availableSpots: 12 },
      { date: "2026-06-22", availableSpots: 17 },
      { date: "2026-06-29", availableSpots: 8 },
    ],
  },
  {
    id: 3,
    slug: "bu-gia-map",
    name: "Bù Gia Mập",
    description: "Lá phổi xanh của miền Đông nam bộ",
    imageFilename: "bu-gia-map.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1426604966848-d7adac402bff?w=800&q=80",
      "https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800&q=80",
      "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800&q=80",
      "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80",
      "https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800&q=80",
    ],
    price: 350000,
    difficulty: "easy" as const,
    duration: "1 ngày",
    availableSpots: 23,
    departureTime: "Sáng",
    highlights: ["Vườn quốc gia", "Rừng nhiệt đới", "Thác nước"],
    departureDates: [
      { date: "2026-06-16", availableSpots: 20 },
      { date: "2026-06-23", availableSpots: 15 },
      { date: "2026-06-30", availableSpots: 23 },
    ],
  },
  {
    id: 4,
    slug: "nui-dinh-ba-ria",
    name: "Núi Dinh - Bà Rịa",
    description: "Thánh địa dân trekker miền Nam",
    imageFilename: "nui-dinh.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?w=800&q=80",
      "https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800&q=80",
      "https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80",
      "https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800&q=80",
      "https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&q=80",
    ],
    price: 320000,
    difficulty: "easy" as const,
    duration: "1 ngày",
    availableSpots: 19,
    departureTime: "Sáng",
    highlights: ["Đỉnh núi Dinh", "Pháo đài chiến đấu", "Toàn cảnh vịnh"],
    departureDates: [
      { date: "2026-06-17", availableSpots: 14 },
      { date: "2026-06-24", availableSpots: 19 },
      { date: "2026-07-01", availableSpots: 10 },
    ],
  },
  {
    id: 5,
    slug: "yangdoan",
    name: "Yang Doan",
    description: "Cao nguyên với thác nước và rừng nguyên sinh",
    imageFilename: "yangdoan.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
      "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80",
      "https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800&q=80",
      "https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80",
      "https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=800&q=80",
    ],
    price: 550000,
    difficulty: "medium" as const,
    duration: "2-3 ngày",
    availableSpots: 12,
    departureTime: "Hệ thống",
    highlights: ["Đỉnh Yang Doan 2375m", "Rừng thông", "Đồi chè"],
    departureDates: [
      { date: "2026-06-20", availableSpots: 8 },
      { date: "2026-07-04", availableSpots: 12 },
    ],
  },
  {
    id: 6,
    slug: "nui-chua-chan",
    name: "Núi Chứa Chan",
    description: "Núi chứa chan huyền thoại",
    imageFilename: "nui-chua-chan.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80",
      "https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?w=800&q=80",
      "https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=800&q=80",
      "https://images.unsplash.com/photo-1426604966848-d7adac402bff?w=800&q=80",
      "https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800&q=80",
    ],
    price: 480000,
    difficulty: "medium" as const,
    duration: "2-3 ngày",
    availableSpots: 26,
    departureTime: "Hệ thống",
    highlights: ["Đỉnh Chứa Chan 850m", "Chùa Hương", "Thung lũng hoa"],
    departureDates: [
      { date: "2026-06-21", availableSpots: 18 },
      { date: "2026-07-05", availableSpots: 26 },
    ],
  },
  {
    id: 7,
    slug: "brahyang",
    name: "Brahyang",
    description: "Nơi ở của trời",
    imageFilename: "brahyang.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800&q=80",
      "https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&q=80",
      "https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800&q=80",
      "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800&q=80",
      "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80",
    ],
    price: 620000,
    difficulty: "medium" as const,
    duration: "2-3 ngày",
    availableSpots: 22,
    departureTime: "Hệ thống",
    highlights: ["Đỉnh Brahyang", "Cảnh đẹp", "Camping"],
    departureDates: [
      { date: "2026-06-27", availableSpots: 15 },
      { date: "2026-07-11", availableSpots: 22 },
    ],
  },
  {
    id: 8,
    slug: "thac-lieng-ai-bao-loc",
    name: "Thác Liêng Ài - Bảo Lộc",
    description: "Con thác hùng vĩ bậc nhất Tây Nguyên",
    imageFilename: "thac-lieng-ai.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=800&q=80",
      "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80",
      "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
      "https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&q=80",
      "https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80",
    ],
    price: 280000,
    difficulty: "easy" as const,
    duration: "1 ngày",
    availableSpots: 24,
    departureTime: "Sáng",
    highlights: ["Thác Liêng Ài", "Suối", "Rừng"],
    departureDates: [
      { date: "2026-06-18", availableSpots: 20 },
      { date: "2026-06-25", availableSpots: 24 },
      { date: "2026-07-02", availableSpots: 18 },
    ],
  },
  {
    id: 9,
    slug: "ta-cu-ke-ga",
    name: "Tà Cú - Kê Gà",
    description: "Điểm đến lý tưởng trọn vẹn",
    imageFilename: "ta-cu-ke-ga.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&q=80",
      "https://images.unsplash.com/photo-1519046904884-53103b34b206?w=800&q=80",
      "https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&q=80",
      "https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800&q=80",
      "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80",
    ],
    price: 290000,
    difficulty: "easy" as const,
    duration: "1 ngày",
    availableSpots: 24,
    departureTime: "Sáng",
    highlights: ["Núi Tà Cú", "Biển Kê Gà", "Hải đăng"],
    departureDates: [
      { date: "2026-06-19", availableSpots: 16 },
      { date: "2026-06-26", availableSpots: 24 },
      { date: "2026-07-03", availableSpots: 20 },
    ],
  },
  {
    id: 10,
    slug: "nui-minh-dam-bien-phuoc-hai",
    name: "Núi Minh Đạm - Biển Phước Hải",
    description: "Cung đường trekking với đa dạng địa hình",
    imageFilename: "nui-minh-dam.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?w=800&q=80",
      "https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800&q=80",
      "https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80",
      "https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800&q=80",
      "https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?w=800&q=80",
    ],
    price: 520000,
    difficulty: "medium" as const,
    duration: "2-3 ngày",
    availableSpots: 22,
    departureTime: "Hệ thống",
    highlights: ["Đỉnh Minh Đạm", "Biển Phước Hải", "Camping"],
    departureDates: [
      { date: "2026-06-28", availableSpots: 14 },
      { date: "2026-07-12", availableSpots: 22 },
    ],
  },
  {
    id: 11,
    slug: "thao-nguyen-palsol",
    name: "Thảo nguyên Palsol",
    description: "Thảo nguyên miền cỏ hát",
    imageFilename: "thao-nguyen-palsol.jpg",
    gallery: [
      "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
      "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80",
      "https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800&q=80",
      "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80",
      "https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=800&q=80",
    ],
    price: 890000,
    difficulty: "hard" as const,
    duration: "4+ ngày",
    availableSpots: 12,
    departureTime: "Hệ thống",
    highlights: ["Thảo nguyên", "Cỏ hát", "Camping cao"],
    departureDates: [
      { date: "2026-07-10", availableSpots: 8 },
      { date: "2026-07-24", availableSpots: 12 },
    ],
  },
];

// Transform to API list format
function toTourListItem(t: typeof mockTours[0]): TourListItem {
  return {
    id: t.id,
    slug: t.slug,
    name: t.name,
    description: t.description,
    thumbnail: t.gallery[0],
    gallery: t.gallery,
    image_filename: t.imageFilename,
    price: t.price,
    price_formatted: `${t.price.toLocaleString("vi-VN")}đ`,
    difficulty: t.difficulty,
    duration: t.duration,
    available_spots: t.availableSpots,
    departure_times: [t.departureTime],
    highlights: t.highlights,
    next_departure_date: t.departureDates[0]?.date || "",
    total_departures: t.departureDates.length,
    rating: 4.9,
    review_count: Math.floor(Math.random() * 200) + 50,
  };
}

// Transform to API detail format
function toTourDetail(t: typeof mockTours[0]): TourDetail {
  return {
    ...toTourListItem(t),
    content: `<p>${t.description}</p>`,
    itinerary: [
      { time: "05:30", activity: "Tập trung tại điểm đón" },
      { time: "06:00", activity: "Xuất phát" },
      { time: "08:00", activity: "Đến điểm trekking, khởi hành" },
      { time: "11:30", activity: "Nghỉ trưa, ăn trưa" },
      { time: "14:00", activity: "Tiếp tục hành trình" },
      { time: "16:00", activity: "Về đến điểm đón, kết thúc tour" },
    ],
    included: ["HDV", "Bảo hiểm du lịch", "Nước uống", "Bữa trưa"],
    excluded: ["Tip cho HDV", "Chi phí cá nhân", "Đồ thuê"],
    notes: "Mang giày thể thao, quần áo thoải mái, kem chống nắng",
    services: t.services || [],
    departure_dates: t.departureDates.map((d) => ({
      date: d.date,
      available_spots: d.availableSpots,
      total_spots: d.availableSpots + 10,
      status: d.availableSpots > 0 ? "available" : "full",
    })),
    pickup_points: mockPickupPoints.map(p => ({ ...p, time: p.pickup_time })),
  };
}

// Mock pickup points (matching FE hardcoded data)
const mockPickupPoints: PickupPoint[] = [
  { id: 1, name: "Bến Thành, Q.1", address: "Công trường Quách Thị Trang, Q.1, TP.HCM", latitude: 10.772371, longitude: 106.698120, pickup_time: "05:30", notes: "Tập trung trước nhà hát Thành Phố", is_active: true },
  { id: 2, name: "Phú Mỹ Hưng, Q.7", address: "Nguyễn Văn Linh, Q.7, TP.HCM", latitude: 10.728740, longitude: 106.717870, pickup_time: "05:45", notes: "Ngã tư Nguyễn Văn Linh - Nguyễn Lương Bằng", is_active: true },
  { id: 3, name: "Bến xe An Sương", address: "Quốc lộ 22, Hóc Môn, TP.HCM", latitude: 10.856780, longitude: 106.616590, pickup_time: "05:15", notes: "Cổng chính bến xe", is_active: true },
  { id: 4, name: "Bến xe Miền Tây", address: "Kinh Dương Vương, Bình Tân, TP.HCM", latitude: 10.745430, longitude: 106.623450, pickup_time: "05:00", notes: "Cổng số 1", is_active: true },
  { id: 5, name: "Thảo Điền, TP. Thủ Đức", address: "Quốc lộ 1A, TP. Thủ Đức, TP.HCM", latitude: 10.801230, longitude: 106.734560, pickup_time: "05:45", notes: "Ngã tư Thảo Điền", is_active: true },
  { id: 6, name: "Biên Hòa, Đồng Nai", address: "Nguyễn Ái Quốc, Biên Hòa, Đồng Nai", latitude: 10.951230, longitude: 106.823450, pickup_time: "04:45", notes: "Ngã tư Vũng Tàu", is_active: true },
];

// Mock rental items
const mockRentalItems: RentalItem[] = [
  { id: "trekking-pole", name: "Gậy trekking", description: "Gậy trekking chuyên dụng, giảm chấn tốt", price: 50000, price_formatted: "50.000đ", unit: "cây/ngày", category: "trekking", icon: "🥾", stock_available: 20, is_active: true },
  { id: "tent-2p", name: "Lều 2 người", description: "Lều đôi chống nước, nhẹ dễ mang", price: 200000, price_formatted: "200.000đ", unit: "lều/đêm", category: "camping", icon: "⛺", stock_available: 10, is_active: true },
  { id: "tent-3p", name: "Lều 3 người", description: "Lều ba rộng rãi, phù hợp gia đình", price: 280000, price_formatted: "280.000đ", unit: "lều/đêm", category: "camping", icon: "⛺", stock_available: 5, is_active: true },
  { id: "sleeping-bag", name: "Túi ngủ", description: "Túi ngủ ấm, chịu được 10-15°C", price: 60000, price_formatted: "60.000đ", unit: "cái/đêm", category: "camping", icon: "🛏️", stock_available: 30, is_active: true },
  { id: "backpack", name: "Balo trekking", description: "Balo 40-50L, có khung đỡ lưng", price: 80000, price_formatted: "80.000đ", unit: "cái/ngày", category: "trekking", icon: "🎒", stock_available: 15, is_active: true },
  { id: "headlamp", name: "Đèn pin đội đầu", description: "Đèn LED siêu sáng, pin bền 8h", price: 30000, price_formatted: "30.000đ", unit: "cái/ngày", category: "accessories", icon: "🔦", stock_available: 25, is_active: true },
  { id: "raincoat", name: "Áo mưa", description: "Áo mưa loại tốt, gọn nhẹ", price: 25000, price_formatted: "25.000đ", unit: "cái/ngày", category: "accessories", icon: "🧥", stock_available: 50, is_active: true },
  { id: "cooking-set", name: "Bộ nấu ăn", description: "Bếp mini + nồi + bình gas nhỏ", price: 150000, price_formatted: "150.000đ", unit: "bộ/ngày", category: "camping", icon: "🍳", stock_available: 8, is_active: true },
];

// Mock booking storage
const mockBookings = new Map<string, { request: BookingRequest; response: BookingResponse }>();

// ============ Mock API Functions ============

export async function getTours(params?: {
  page?: number;
  per_page?: number;
  search?: string;
  difficulty?: string;
  duration?: string;
  price_min?: number;
  price_max?: number;
  departure_time?: string;
  sort?: string;
}): Promise<{ data: TourListItem[]; meta: { total: number; page: number; per_page: number; total_pages: number } }> {
  await delay(300 + Math.random() * 200);

  let filtered = [...mockTours];

  if (params?.search) {
    const q = params.search.toLowerCase();
    filtered = filtered.filter((t) =>
      t.name.toLowerCase().includes(q) ||
      t.description.toLowerCase().includes(q) ||
      t.highlights.some((h) => h.toLowerCase().includes(q))
    );
  }

  if (params?.difficulty && params.difficulty !== "all") {
    filtered = filtered.filter((t) => t.difficulty === params.difficulty);
  }

  if (params?.duration) {
    if (params.duration === "1day") filtered = filtered.filter((t) => t.duration === "1 ngày");
    else if (params.duration === "multi") filtered = filtered.filter((t) => t.duration !== "1 ngày");
  }

  if (params?.departure_time && params.departure_time !== "all") {
    filtered = filtered.filter((t) => t.departureTime === params.departure_time);
  }

  if (params?.price_min) filtered = filtered.filter((t) => t.price >= params.price_min!);
  if (params?.price_max) filtered = filtered.filter((t) => t.price <= params.price_max!);

  if (params?.sort === "price-asc") filtered.sort((a, b) => a.price - b.price);
  else if (params?.sort === "price-desc") filtered.sort((a, b) => b.price - a.price);

  const page = params?.page || 1;
  const per_page = Math.min(params?.per_page || 20, 50);
  const total = filtered.length;
  const start = (page - 1) * per_page;
  const paged = filtered.slice(start, start + per_page);

  return {
    data: paged.map(toTourListItem),
    meta: {
      total,
      page,
      per_page,
      total_pages: Math.ceil(total / per_page),
    },
  };
}

export async function getTourBySlug(slug: string): Promise<TourDetail> {
  await delay(200 + Math.random() * 200);
  const tour = mockTours.find((t) => t.slug === slug);
  if (!tour) throw new Error("tour_not_found");
  return toTourDetail(tour);
}

export async function getRentalItems(): Promise<RentalItem[]> {
  await delay(100 + Math.random() * 100);
  return mockRentalItems;
}

export async function getPickupPoints(): Promise<PickupPoint[]> {
  await delay(100 + Math.random() * 100);
  return mockPickupPoints;
}

export async function createBooking(request: BookingRequest): Promise<BookingResponse> {
  await delay(800 + Math.random() * 400);

  const tour = mockTours.find((t) => t.slug === request.tour_slug);
  if (!tour) throw new Error("tour_not_found");

  const departure = tour.departureDates.find((d) => d.date === request.departure_date);
  if (!departure) throw new Error("departure_not_found");
  if (departure.availableSpots < request.participants) throw new Error("departure_full");

  // Calculate totals
  const tourPrice = tour.price * request.participants;
  const servicesTotal = (tour.services || [])
    .filter((s) => request.services.includes(s.id))
    .reduce((sum, s) => sum + s.price * request.participants, 0);

  const rentalItemsList = Object.entries(request.rental_items || {})
    .map(([id, qty]) => {
      const item = mockRentalItems.find((r) => r.id === id);
      return { id, name: item?.name || id, qty, subtotal: (item?.price || 0) * qty };
    })
    .filter((r) => r.qty > 0);

  const rentalTotal = rentalItemsList.reduce((sum, r) => sum + r.subtotal, 0);
  const total = tourPrice + servicesTotal + rentalTotal;

  // Generate booking ID
  const now = new Date();
  const dateStr = now.getFullYear().toString().slice(2) +
    (now.getMonth() + 1).toString().padStart(2, "0") +
    now.getDate().toString().padStart(2, "0");
  const random = Math.floor(Math.random() * 9000 + 1000);
  const bookingId = `NTR${dateStr}${random}`;

  const holdExpires = new Date(now.getTime() + 2 * 60 * 60 * 1000);

  const passengers = request.passengers.map((p, i) => ({
    id: 1000 + i,
    full_name: p.full_name,
    seat: request.selected_seats?.[i],
    pickup_point: mockPickupPoints.find((pp) => pp.id === (p.pickup_point_id || request.pickup_point_id))?.name,
    qr_code_url: `https://tour-api.nttung.dev/qr/${bookingId}-P${1000 + i}.png`,
  }));

  const response: BookingResponse = {
    booking_id: bookingId,
    status: "pending",
    hold_expires_at: holdExpires.toISOString(),
    total_amount: total,
    total_amount_formatted: `${total.toLocaleString("vi-VN")}đ`,
    breakdown: {
      tour_price: tourPrice,
      services_total: servicesTotal,
      rental_total: rentalTotal,
      rental_items: rentalItemsList,
    },
    deposit_amount: 0,
    remaining_amount: total,
    payment_method: request.payment_method,
    payment_status: "unpaid",
    passengers,
    next_steps: [
      "Kiểm tra email để nhận thông tin chi tiết",
      request.payment_method === "transfer" ? "Quét mã QR để thanh toán" : "Thanh toán khi gặp HDV",
      "Nhận QR code để check-in",
    ],
  };

  mockBookings.set(bookingId, { request, response });

  return response;
}

export async function getBooking(bookingId: string): Promise<BookingDetail> {
  await delay(200 + Math.random() * 200);
  const stored = mockBookings.get(bookingId);
  if (!stored) throw new Error("booking_not_found");

  const { request, response } = stored;
  const tour = mockTours.find((t) => t.slug === request.tour_slug)!;

  return {
    booking_id: bookingId,
    status: response.status,
    created_at: new Date().toISOString(),
    tour: { name: tour.name, slug: tour.slug },
    departure: { date: request.departure_date, departure_time: tour.departureTime },
    main_contact: {
      full_name: request.main_contact.full_name,
      phone: request.main_contact.phone,
      email: request.main_contact.email,
    },
    passengers: response.passengers.map((p) => ({
      id: p.id,
      full_name: p.full_name,
      seat: p.seat,
      pickup_point: p.pickup_point,
      checked_in: false,
    })),
    rental_items: response.breakdown.rental_items,
    payment: {
      method: request.payment_method,
      total: response.total_amount,
      paid: 0,
      remaining: response.remaining_amount,
      status: response.payment_status,
      bank_info: request.payment_method === "transfer" ? {
        bank_name: "MB Bank",
        bank_bin: "970422",
        account_no: "123456789",
        account_name: "DOI DEP ADVENTURE COMPANY",
        amount: response.total_amount,
        content: bookingId,
        qr_url: `https://img.vietqr.io/image/MB-123456789-compact2.png?amount=${response.total_amount}&addInfo=${bookingId}&accountName=DOI+DEP+ADVENTURE+COMPANY`,
        deeplink: `https://app.vietqr.io/...`,
      } : undefined,
    },
  };
}

export async function lookupBooking(params: { booking_id?: string; email?: string; phone?: string }) {
  await delay(200 + Math.random() * 200);

  if (params.booking_id) {
    const stored = mockBookings.get(params.booking_id);
    if (!stored) throw new Error("booking_not_found");
    const { request, response } = stored;
    const tour = mockTours.find((t) => t.slug === request.tour_slug)!;
    return [{
      booking_id: params.booking_id,
      tour_name: tour.name,
      departure_date: request.departure_date,
      status: response.status,
      passengers_count: request.participants,
      payment_method: request.payment_method,
      total_amount: response.total_amount,
      payment_status: response.payment_status,
    }];
  }

  // Search by email or phone
  const results: unknown[] = [];
  for (const [id, { request, response }] of mockBookings.entries()) {
    if (
      (params.email && request.main_contact.email === params.email) ||
      (params.phone && request.main_contact.phone === params.phone)
    ) {
      const tour = mockTours.find((t) => t.slug === request.tour_slug)!;
      results.push({
        booking_id: id,
        tour_name: tour.name,
        departure_date: request.departure_date,
        status: response.status,
        passengers_count: request.participants,
        payment_method: request.payment_method,
        total_amount: response.total_amount,
        payment_status: response.payment_status,
      });
    }
  }
  return results;
}

export async function getPaymentInfo(bookingId: string): Promise<PaymentInfo> {
  await delay(200 + Math.random() * 200);
  const stored = mockBookings.get(bookingId);
  if (!stored) throw new Error("booking_not_found");

  const { request, response } = stored;

  return {
    booking_id: bookingId,
    payment_method: request.payment_method,
    total_amount: response.total_amount,
    paid_amount: 0,
    remaining_amount: response.remaining_amount,
    payment_status: response.payment_status,
    breakdown: response.breakdown,
    bank_transfer: {
      bank_name: "MB Bank",
      bank_bin: "970422",
      account_no: "123456789",
      account_name: "DOI DEP ADVENTURE COMPANY",
      amount: response.total_amount,
      content: bookingId,
      qr_url: `https://img.vietqr.io/image/MB-123456789-compact2.png?amount=${response.total_amount}&addInfo=${bookingId}&accountName=DOI+DEP+ADVENTURE+COMPANY`,
      deeplink: `https://app.vietqr.io/...`,
    },
  };
}
