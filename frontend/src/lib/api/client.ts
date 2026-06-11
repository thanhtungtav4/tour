import {
  TourListItem,
  TourDetail,
  RentalItem,
  PickupPoint,
  BookingRequest,
  BookingResponse,
  BookingDetail,
  BookingLookupRow,
  BookingStatusUpdate,
  PaymentInfo,
  ApiBlogPost,
  GeneralSettings,
  ApiPage
} from "./types";

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || "https://tour-api.nttung.dev/wp-json/newtrip/v1";

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
  const query = new URLSearchParams();
  if (params) {
    if (params.page) query.append("page", params.page.toString());
    if (params.per_page) query.append("per_page", params.per_page.toString());
    if (params.search) query.append("search", params.search);
    if (params.difficulty) query.append("difficulty", params.difficulty);
    if (params.duration) query.append("duration", params.duration);
    if (params.price_min) query.append("price_min", params.price_min.toString());
    if (params.price_max) query.append("price_max", params.price_max.toString());
    if (params.departure_time) query.append("departure_time", params.departure_time);
    if (params.sort) query.append("sort", params.sort);
  }

  const url = `${API_BASE_URL}/tours${query.toString() ? `?${query.toString()}` : ""}`;
  const res = await fetch(url, {
    next: { revalidate: 60 }, // Cache response for 60 seconds
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể tải danh sách tour");
  }
  return {
    data: json.data,
    meta: json.meta || {
      total: json.data.length,
      page: 1,
      per_page: json.data.length,
      total_pages: 1
    }
  };
}

export async function getTourBySlug(slug: string): Promise<TourDetail> {
  const url = `${API_BASE_URL}/tours/${slug}`;
  const res = await fetch(url, {
    next: { revalidate: 10 }, // Cache response for 10 seconds (useful during active bookings)
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.code || json.error?.message || "tour_not_found");
  }
  return json.data;
}

export async function getRentalItems(): Promise<RentalItem[]> {
  const url = `${API_BASE_URL}/rental-items`;
  const res = await fetch(url, {
    next: { revalidate: 60 },
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể tải danh sách đồ thuê");
  }
  return json.data;
}

export async function getPickupPoints(): Promise<PickupPoint[]> {
  const url = `${API_BASE_URL}/pickup-points`;
  const res = await fetch(url, {
    next: { revalidate: 300 }, // Pickup points rarely change
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể tải danh sách điểm đón");
  }
  return json.data;
}

export async function createBooking(request: BookingRequest): Promise<BookingResponse> {
  const url = `${API_BASE_URL}/booking`;
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(request),
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.code || json.error?.message || "Không thể đặt tour");
  }
  return json.data;
}

export async function getBooking(bookingId: string, email?: string): Promise<BookingDetail> {
  const query = email ? `?email=${encodeURIComponent(email)}` : "";
  const url = `${API_BASE_URL}/booking/${bookingId}${query}`;
  const res = await fetch(url, {
    cache: "no-store", // Do not cache single booking lookup
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.code || json.error?.message || "booking_not_found");
  }
  return json.data;
}

export async function lookupBooking(params: { email?: string; phone?: string }): Promise<BookingLookupRow[]> {
  const query = new URLSearchParams();
  if (params.email) query.append("email", params.email);
  if (params.phone) query.append("phone", params.phone);

  if (!query.toString()) return [];

  const url = `${API_BASE_URL}/booking/lookup?${query.toString()}`;
  const res = await fetch(url, { cache: "no-store" });
  const json = await res.json();
  if (!res.ok || !json.success) {
    return [];
  }
  return json.data as BookingLookupRow[];
}

export async function updateBookingStatus(
  bookingId: string,
  update: BookingStatusUpdate,
  authHeader: string
): Promise<{ booking_id: string; status: string; payment_status: string; paid_amount: number; updated_at: string }> {
  const url = `${API_BASE_URL}/booking/${bookingId}/status`;
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: authHeader,
    },
    body: JSON.stringify(update),
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.code || json.error?.message || "Không cập nhật được trạng thái");
  }
  return json.data;
}

export async function getPaymentInfo(bookingId: string): Promise<PaymentInfo> {
  const booking = await getBooking(bookingId);
  return {
    booking_id: booking.booking_id,
    payment_method: booking.payment.method,
    total_amount: booking.payment.total,
    paid_amount: booking.payment.paid,
    remaining_amount: booking.payment.remaining,
    payment_status: booking.payment.status,
    breakdown: {
      tour_price: booking.payment.total,
      services_total: 0,
      rental_total: 0,
    },
    bank_transfer: booking.payment.bank_info,
  };
}

export async function getBlogPosts(): Promise<ApiBlogPost[]> {
  const url = `${API_BASE_URL}/posts`;
  const res = await fetch(url, {
    next: { revalidate: 60 }, // Cache response for 60 seconds
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể tải danh sách bài viết");
  }
  return json.data;
}

export async function getBlogPost(id: string | number): Promise<ApiBlogPost> {
  const url = `${API_BASE_URL}/posts/${id}`;
  const res = await fetch(url, {
    next: { revalidate: 60 },
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể tải chi tiết bài viết");
  }
  return json.data;
}

export async function getSettings(): Promise<GeneralSettings> {
  const url = `${API_BASE_URL}/settings`;
  const res = await fetch(url, {
    next: { revalidate: 60 }, // Cache settings for 60 seconds
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể tải cài đặt");
  }
  return json.data;
}

export async function getPageBySlug(slug: string): Promise<ApiPage> {
  const url = `${API_BASE_URL}/pages/${slug}`;
  const res = await fetch(url, {
    next: { revalidate: 60 }, // Cache pages for 60 seconds
  });
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể tải trang chính sách");
  }
  return json.data;
}

export async function uploadFile(file: File): Promise<{ id: number; url: string }> {
  const url = `${API_BASE_URL}/upload`;
  const formData = new FormData();
  formData.append("file", file);

  const res = await fetch(url, {
    method: "POST",
    body: formData,
  });

  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Lỗi tải ảnh lên");
  }
  return json.data;
}

export async function updateBookingPassengers(
  bookingId: string,
  email: string,
  passengers: any[]
): Promise<{ success: boolean; message: string }> {
  const url = `${API_BASE_URL}/booking/${bookingId}/update-passengers`;
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email, passengers }),
  });

  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error?.message || "Không thể cập nhật danh sách hành khách");
  }
  return json;
}
