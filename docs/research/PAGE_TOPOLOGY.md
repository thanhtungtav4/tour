# PAGE_TOPOLOGY.md - Page Structure for newtrip.com.vn

## Overview
Newtrip is a Vietnamese adventure travel company website featuring trekking, camping, and nature exploration tours. The site uses a single-page layout with multiple sections.

## Color Palette (Extracted)
- **Primary Green:** `rgb(22, 162, 73)` / `#16a249` - Main brand color
- **Secondary Green:** `rgb(16, 185, 129)` / `#10b981` - Lighter accent
- **Dark Navy:** `rgb(14, 20, 37)` / `#0e1425` - Text and dark backgrounds
- **Amber Accent:** `rgb(251, 191, 36)` / `#fbbf24` - Highlights
- **Purple Accent:** `rgb(139, 92, 246)` / `#8b5cf6` - Secondary accent
- **Background Light:** `rgb(245, 247, 250)` / `#f5f7fa` - Light sections
- **Background Muted:** `rgb(211, 218, 228)` / `#d3dae4` - Borders and muted areas
- **White:** `rgb(255, 255, 255)` / `#ffffff` - Cards and content areas

## Typography
- **Font Family:** `Poppins, Inter, sans-serif`
- **Primary:** System UI sans-serif fallback

## Page Sections (Top to Bottom)

### 1. Header / Navigation (Fixed, z-50)
- **Position:** Fixed at top, full width
- **Height:** 81px
- **Background:** `rgba(255, 255, 255, 0.85)` with `backdrop-filter: blur(18px)`
- **Shadow:** `0px 10px 15px -3px rgba(0, 0, 0, 0.1), 0px 4px 6px -4px`
- **Behavior:** Scrolls with page, gains glassmorphism effect
- **Components:**
  - Logo: "Newtrip" with tagline "Siêu tiện - Siêu vui - Siêu Tiết Kiệm"
  - Navigation links: Trang chủ, Tuyến đường, Về chúng tôi, Liên hệ
  - CTA Button: "Đặt vé ngay" (hidden on mobile)
  - Mobile menu toggle (hamburger icon)

### 2. Hero Section - "Lịch trình sắp tới" (Upcoming Schedule)
- **Position:** Below header
- **Background:** Gradient from background to background-accent
- **Content:**
  - Section title: "Lịch trình sắp tới"
  - Subtitle: "Những trải nghiệm tuyệt vời đang chờ đón bạn trong thời gian tới"
  - Stats: "12 chuyến" / "Sắp khởi hành"
- **Interaction:** Contains filter buttons (pill-style tabs)

### 3. Tour Cards Section
- **Layout:** Grid of tour cards (aspect-ratio 4/3)
- **Cards:**11+ tour cards with:
  - Background image
  - Overlay gradient
  - Difficulty badge (Dễ, Trung bình, Khó)
  - Availability: "Còn X chỗ"
  - Tour name
  - Short description
  - Starting price
- **Filter Pills:**
  - Time of day: Sáng, Hệ thống, Tối
  - Price: Dưới 500k, 500k - 1tr, Trên 1tr
  - Duration: 1 ngày, 2-3 ngày, 4+ ngày
  - Difficulty: Dễ, Trung bình, Khó
- **CTA:** "Xem thêm" button

### 4. About Section - "Câu chuyện của chúng tôi"
- **Position:** After tour cards
- **Layout:** Two-column grid
- **Content:**
  - Left: Large image (Unsplash adventure photo)
  - Right: "Vì sao chọn Newtrip?" with feature list
- **Features:**
  - Đội ngũ hướng dẫn viên nhiệt huyết, am hiểu từng địa danh
  - An toàn làưu tiên số một
  - Trải nghiệm được thiết kế riêng cho từng nhóm
  - Giá cả minh bạch, không phí ẩn

### 5. Blog Section - "Blogs & Stories"
- **Position:** After about section
- **Layout:** Two blog cards
- **Content:**
  - Section title: "Blogs & Stories"
  - Subtitle: "Kinh nghiệm & Chia sẻ"
  - Blog cards with images, author, date, title
- **CTA:** "Xem thêm" link

### 6. Footer
- **Background:** `bg-surface` with top border
- **Content:**
  - Logo and tagline
  - Policy links: Chính sách an toàn, Chính sách hủy vé, etc.
  - Contact info: Phone (0928382087), Email (Newtrip.com.vn@gmail.com)
  - Booking CTA

## Interaction Models

### Header
- **Type:** Static with glassmorphism
- **Scroll behavior:** No visual change on scroll (glass effect always active)
- **Mobile:** Hamburger menu appears at lg breakpoint

### Filter Pills
- **Type:** Click-driven state switching
- **Behavior:** Clicking a pill filters the tour cards
- **Transition:** CSS transition on background/border color

### Tour Cards
- **Type:** Hover-driven
- **Hover effects:** Scale transform, shadow enhancement
- **Click:** Navigates to booking page with route parameter

### Navigation Links
- **Type:** Hover-driven
- **Hover effects:** Color change to primary
- **Click:** Page navigation

## Responsive Breakpoints
- **Mobile:** 390px - Single column, hamburger menu
- **Tablet:** 768px - 2-column grids
- **Desktop:** 1440px - Full layout with all columns

## Assets Identified
- 17 images (logos, tour photos, blog images)
- 0 videos
- 56 background images (gradients, decorative)
- 46 SVG elements (icons)
- 1 Unsplash image for about section

## Smooth Scroll
- No Lenis or Locomotive Scroll detected
- Native CSS smooth scroll behavior
