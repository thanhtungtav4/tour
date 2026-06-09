// API Response types matching API Documentation

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  meta?: {
    total: number;
    page: number;
    per_page: number;
    total_pages: number;
    has_next?: boolean;
    has_prev?: boolean;
  };
}

export interface ApiError {
  success: false;
  error: {
    code: string;
    message: string;
    data?: Record<string, unknown>;
  };
}

// Tour types
export interface TourService {
  id: string;
  name: string;
  description: string;
  price: number;
  unit: string;
}

export interface TourDeparture {
  date: string;
  available_spots: number;
}

export interface TourListItem {
  id: number;
  slug: string;
  name: string;
  description: string;
  thumbnail: string;
  gallery: string[];
  image_filename: string;
  price: number;
  price_formatted: string;
  difficulty: "easy" | "medium" | "hard";
  duration: string;
  available_spots: number;
  departure_times: string[];
  highlights: string[];
  next_departure_date: string;
  total_departures: number;
  rating: number;
  review_count: number;
}

export interface TourDetail extends TourListItem {
  content: string;
  itinerary: { time: string; activity: string }[];
  included: string[];
  excluded: string[];
  notes: string;
  services: TourService[];
  departure_dates: {
    date: string;
    available_spots: number;
    total_spots: number;
    status: "available" | "full" | "cancelled";
  }[];
  pickup_points: {
    id: number;
    name: string;
    address: string;
    pickup_time: string;
    time: string;
  }[];
}

// Rental Item types
export interface RentalItem {
  id: string;
  name: string;
  description: string;
  price: number;
  price_formatted: string;
  unit: string;
  category: "trekking" | "camping" | "accessories";
  icon: string;
  stock_available: number;
  is_active: boolean;
}

// Pickup Point types
export interface PickupPoint {
  id: number;
  name: string;
  address: string;
  latitude: number;
  longitude: number;
  pickup_time: string;
  notes: string;
  is_active: boolean;
}

// Booking types
export interface BookingRequest {
  tour_slug: string;
  departure_date: string;
  pickup_point_id: number;
  participants: number;
  services: string[];
  rental_items: Record<string, number>;
  payment_method: "cash" | "transfer";
  main_contact: {
    full_name: string;
    phone: string;
    email: string;
  };
  passengers: {
    full_name: string;
    phone: string;
    email?: string;
    birth_year: string;
    id_number?: string;
    pickup_point_id?: number;
  }[];
  selected_seats?: string[];
  notes?: string;
  agree_terms: boolean;
}

export interface BookingResponse {
  booking_id: string;
  status: "pending" | "confirmed" | "cancelled" | "completed" | "no_show";
  hold_expires_at: string;
  total_amount: number;
  total_amount_formatted: string;
  breakdown: {
    tour_price: number;
    services_total: number;
    rental_total: number;
    rental_items: { id: string; name: string; qty: number; subtotal: number }[];
  };
  deposit_amount: number;
  remaining_amount: number;
  payment_method: "cash" | "transfer";
  payment_status: "unpaid" | "partial" | "paid" | "refunded";
  passengers: {
    id: number;
    full_name: string;
    seat?: string;
    pickup_point?: string;
    qr_code_url: string;
  }[];
  next_steps: string[];
}

export interface BookingDetail {
  booking_id: string;
  status: string;
  created_at: string;
  tour: { name: string; slug: string };
  departure: { date: string; departure_time: string };
  main_contact: { full_name: string; phone: string; email: string };
  passengers: {
    id: number;
    full_name: string;
    email?: string;
    seat?: string;
    pickup_point?: string;
    checked_in: boolean;
  }[];
  rental_items?: { id: string; name: string; qty: number; subtotal: number }[];
  payment: {
    method: "cash" | "transfer";
    total: number;
    paid: number;
    remaining: number;
    status: string;
    bank_info?: {
      bank_name: string;
      bank_bin: string;
      account_no: string;
      account_name: string;
      amount: number;
      content: string;
      qr_url: string;
      deeplink: string;
    };
  };
}

// Payment types
export interface PaymentInfo {
  booking_id: string;
  payment_method: "cash" | "transfer";
  total_amount: number;
  paid_amount: number;
  remaining_amount: number;
  payment_status: string;
  breakdown: {
    tour_price: number;
    services_total: number;
    rental_total: number;
  };
  bank_transfer: {
    bank_name: string;
    bank_bin: string;
    account_no: string;
    account_name: string;
    amount: number;
    content: string;
    qr_url: string;
    deeplink: string;
  };
}
