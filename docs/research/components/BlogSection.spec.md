# BlogSection Component Specification

## Overview
- **Target file:** `src/components/BlogSection.tsx`
- **Screenshot:** `docs/design-references/desktop-1440px.png` (blog section)
- **Interaction model:** Hover-driven, click navigates to blog post

## DOM Structure
```
<section> (section-padding overflow-hidden)
  <div> (container mx-auto)
    <div> (section header text-center mb-12)
      <span> (subtitle badge: "Blogs & Stories")
      <h2> "Kinh nghiệm & Chia sẻ"
      <p> "Những câu chuyện và trải nghiệm thực tế từ cộng đồng Newtrip"
    </div>
    <div> (grid md:grid-cols-2 gap-8)
      <article> (blog card)
        <a> (block h-full outline-none group)
          <div> (relative h-52 overflow-hidden)
            <img> (blog image)
          </div>
          <div> (p-6 bg-white)
            <div> (meta flex items-center gap-3 mb-3)
              <span> (author initial)
              <span> (date)
            </div>
            <h3> (blog title)
          </div>
        </a>
      </article>
      ... (2 blog cards)
    </div>
    <div> (cta text-center mt-8)
      <a> "Xem thêm"
    </div>
  </div>
</section>
```

## Computed Styles (exact values)

### Section
- padding-top: 4rem (mobile), 6rem (desktop)
- padding-bottom: 4rem (mobile), 6rem (desktop)
- overflow: hidden

### Badge
- display: inline-block
- padding: 0.25rem 0.75rem
- font-size: 0.875rem (14px)
- font-weight: 600
- color: #16a249
- background: rgba(22, 162, 73, 0.1)
- border-radius: 9999px
- margin-bottom: 1rem

### Section Title
- font-size: 2rem (32px) mobile, 2.5rem (40px) desktop
- font-weight: 800
- color: #0e1425
- margin-bottom: 0.75rem

### Section Subtitle
- font-size: 1rem (16px)
- color: #6b7280
- text-align: center

### Blog Grid
- display: grid
- gap: 2rem (32px)
- grid-template-columns: 1fr

### Blog Card
- background: #ffffff
- border-radius: 1rem (16px)
- overflow: hidden
- transition: transform 0.3s ease, box-shadow 0.3s ease

### Blog Card Hover
- transform: translateY(-4px)
- box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1)

### Blog Image Container
- height: 13rem (208px)
- overflow: hidden

### Blog Image
- width: 100%
- height: 100%
- object-fit: cover
- transition: transform 0.3s ease

### Blog Card Hover Image
- transform: scale(1.05)

### Blog Content
- padding: 1.5rem (24px)
- background: #ffffff

### Author Meta
- display: flex
- align-items: center
- gap: 0.75rem
- margin-bottom: 0.75rem

### Author Initial
- width: 2rem (32px)
- height: 2rem (32px)
- border-radius: 50%
- background: linear-gradient(135deg, #16a249, #10b981)
- color: #ffffff
- font-size: 0.875rem (14px)
- font-weight: 600
- display: flex
- align-items: center
- justify-content: center

### Post Date
- font-size: 0.75rem (12px)
- color: #6b7280

### Blog Title
- font-size: 1rem (16px)
- font-weight: 700
- color: #0e1425
- line-height: 1.4
- display: -webkit-box
- -webkit-line-clamp: 2
- -webkit-box-orient: vertical
- overflow: hidden

### View More Link
- display: inline-flex
- align-items: center
- gap: 0.5rem
- font-size: 0.875rem (14px)
- font-weight: 700
- color: #16a249
- transition: gap 0.3s ease

### View More Hover
- gap: 0.75rem

## States& Behaviors

### Card Hover
- **Trigger:** Mouse enter
- **Effect:** Card lifts up, shadow increases, image scales
- **Transition:** all 0.3s ease

### View More Link Hover
- **Trigger:** Mouse enter
- **Effect:** Arrow gap increases
- **Transition:** gap 0.3s ease

## Responsive Behavior
- **Desktop (1440px):** 2-column grid
- **Tablet (768px):** 2-column grid
- **Mobile (390px):** Single column

## Text Content (verbatim)
- Badge: "Blogs & Stories"
- Title: "Kinh nghiệm & Chia sẻ"
- Subtitle: "Những câu chuyện và trải nghiệm thực tế từ cộng đồng Newtrip"
- Blog posts:
  1. "Mẹo Chọn Giày Khi Đi Trekking Không Bị Đau Chân – Bí Quyết Dân Trekking Cần Biết" by "Ne" on 30/1/2026
  2. "Trekking Tự Túc: Lợi Ích & Nguy Hiểm – Những Điều Cần Lưu Ý Trước Chuyến Đi" by "Mi" on 30/1/2026
- CTA: "Xem thêm"

## Assets
- Blog images from postimg.cc
- Icons: ArrowRightIcon from icons.tsx
