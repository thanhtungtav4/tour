// Tour-related types
export interface Tour {
  id: string;
  name: string;
  slug: string;
  description: string;
  imageFilename: string;
  gallery: string[];
  price: number;
  difficulty: "easy" | "medium" | "hard";
  duration: string;
  availableSpots: number;
  departureTime: string;
  highlights: string[];
  departureDates: {
    date: string;
    availableSpots: number;
  }[];
  services?: {
    id: string;
    name: string;
    description: string;
    price: number;
    unit: string;
  }[];
}

export interface TourFilter {
  timeOfDay?: "morning" | "afternoon" | "evening" | "all";
  priceRange?: "under500k" | "500k-1tr" | "over1tr";
  duration?: "1day" | "2-3days" | "4plus";
  difficulty?: "easy" | "medium" | "hard";
}

// Blog-related types
export interface BlogPost {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  image: string;
  author: {
    name: string;
    avatar: string;
  };
  publishedAt: string;
  category: string;
}

// Navigation types
export interface NavLink {
  label: string;
  href: string;
  isActive?: boolean;
}

// Feature/Why Choose Us types
export interface Feature {
  icon: string;
  title: string;
  description: string;
}

// Contact info types
export interface ContactInfo {
  phone: string;
  email: string;
  address?: string;
}

// Policy link types
export interface PolicyLink {
  label: string;
  href: string;
}

// Hero section types
export interface HeroStats {
  totalTrips: number;
  upcomingTrips: number;
}
