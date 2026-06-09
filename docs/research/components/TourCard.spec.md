# TourCard Component Specification

## Overview
- **Target file:** `src/components/TourCard.tsx`
- **Screenshot:** `docs/design-references/desktop-1440px.png` (tour cards section)
- **Interaction model:** Hover-driven, click navigates to booking

## DOM Structure
```
<article> (card-hover group cursor-pointer)
  <a> (block h-full)
    <div> (relative aspect-[4/3] overflow-hidden)
      <img> (tour image, cover)
      <div> (absolute inset-0 gradient overlay)
      <div> (absolute top-3 left-3)
        <span> (difficulty badge)
      </div>
      <div> (absolute bottom-3 left-3)
        <span> (availability: "Còn X chỗ")
      </div>
    </div>
    <div> (p-4 bg-white)
      <h3> (tour name)
      <p> (description)
      <div> (flex items-center justify-between)
        <span> (price)
        <span> (duration)
 </div>
  </a>
</article>
```

## Computed Styles (exact values)

### Card Container
- border-radius: 1rem (16px)
- overflow: hidden
- background: #ffffff
- transition: transform 0.3s ease, box-shadow 0.3s ease

### Card Hover
- transform: translateY(-4px)
- box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)

### Image Container
- aspect-ratio: 4/3
- overflow: hidden

### Tour Image
- width: 100%
- height: 100%
- object-fit: cover
- transition: transform 0.3s ease

### Card Hover Image
- transform: scale(1.05)

### Gradient Overlay
- position: absolute
- inset: 0
- background: linear-gradient(to top, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0), rgba(0, 0, 0, 0))
- pointer-events: none

### Difficulty Badge
- padding: 0.25rem 0.5rem
- font-size: 0.75rem (12px)
- font-weight: 600
- border-radius: 0.375rem (6px)

### Badge Easy (Dễ)
- background: #dcfce7 (emerald-100)
- color: #166534 (emerald-800)

### Badge Medium (Trung bình)
- background: #fef3c7 (amber-100)
- color: #92400e (amber-800)

### Badge Hard (Khó)
- background: #fee2e2 (red-100)
- color: #991b1b (red-800)

### Availability Badge
- font-size: 0.75rem (12px)
- font-weight: 500
- color: #ffffff
- text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3)

### Card Content
- padding: 1rem (16px)
- background: #ffffff

### Tour Name
- font-size: 1.125rem (18px)
- font-weight: 700
- color: #0e1425
- margin-bottom: 0.5rem

### Description
- font-size: 0.875rem (14px)
- color: #6b7280
- line-height: 1.5
- margin-bottom: 1rem
- display: -webkit-box
- -webkit-line-clamp: 2
- -webkit-box-orient: vertical
- overflow: hidden

### Price
- font-size: 1rem (16px)
- font-weight: 700
- color: #16a249

### Price Label
- font-size: 0.75rem (12px)
- font-weight: 400
- color: #6b7280

### Duration
- font-size: 0.875rem (14px)
- color: #6b7280

## States& Behaviors

### Card Hover
- **Trigger:** Mouse enter
- **Effect:** Card lifts up (translateY -4px), shadow increases, image scales (1.05)
- **Transition:** all 0.3s ease

### Card Click
- **Trigger:** Click on card
- **Behavior:** Navigate to `/booking?route={slug}`

## Responsive Behavior
- **Desktop (1440px):** 3-4 column grid
- **Tablet (768px):** 2 column grid
- **Mobile (390px):** Single column, full width

## Text Content (from extracted data)
- Tour data:
  - Langbiang: "Chuyến đi Trekking chinh phục đỉnh Langbiang 2163m", "Còn 21 chỗ", Dễ
  - Rừng Cát Tiên: "Đạp xe và đi bộ khám phá rừng ngập mặn", "Còn 17 chỗ", Dễ
  - Bù Gia Mập: "Lá phổi xanh của miền Đông nam bộ", "Còn 23 chỗ", Dễ
  - Núi Dinh - Bà Rịa: "Thánh địa dân trekker miền Nam", "Còn 19 chỗ", Dễ
  - YangDoan: "Ngọn núi cao thứ 2 cao Đà Lạt", "Còn 12 chỗ", Trung bình
  - Núi Chứa Chan: "Núi chứa chan huyền thoại", "Còn 26 chỗ", Trung bình
  - Brahyang: "Nơi ở của trời", "Còn 22 chỗ", Trung bình
  - Thác Liêng Ài: "Con thác hùng vĩ bậc nhất Tây Nguyên", "Còn 24 chỗ", Dễ
  - Tà Cú - Kê Gà: "Điểm đến lý tưởng trọn vẹn", "Còn 24 chỗ", Dễ
  - Núi Minh Đạm: "Cung đường trekking với đa dạng địa hình", "Còn 22 chỗ", Trung bình
  - Thảo nguyên Palsol: "Thảo nguyên miền cỏ hát", "Còn 12 chỗ", Khó

## Assets
- Tour images from postimg.cc (see global-extraction.json for full list)
- Placeholder: Use actual images or gradient placeholder
