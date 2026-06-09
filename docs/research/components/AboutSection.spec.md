# AboutSection Component Specification

## Overview
- **Target file:** `src/components/AboutSection.tsx`
- **Screenshot:** `docs/design-references/desktop-1440px.png` (about section)
- **Interaction model:** Static

## DOM Structure
```
<section> (section-padding overflow-hidden)
  <div> (container mx-auto)
    <div> (grid lg:grid-cols-2 gap-12 items-center)
      <div> (image-wrapper lg:col-span-2 relative rounded-3xl overflow-hidden min-h-[420px] group)
        <img> (about us image)
      </div>
      <div> (content)
        <span> (subtitle badge: "Câu chuyện của chúng tôi")
        <h2> "Vì sao chọn Newtrip?"
        <div> (features list)
          <div> (feature item)
            <div> (icon wrapper)
            <div> (feature text)
              <h4> (feature title)
              <p> (feature description)
          ... (4 features total)
        </div>
      </div>
    </div>
  </div>
</section>
```

## Computed Styles (exact values)

### Section
- padding-top: 4rem (mobile), 6rem (desktop)
- padding-bottom: 4rem (mobile), 6rem (desktop)
- overflow: hidden

### Grid Container
- display: grid
- gap: 3rem (48px)
- align-items: center

### Image Container
- position: relative
- border-radius: 1.5rem (24px)
- overflow: hidden
- min-height: 420px
- group-hover: subtle scale effect

### About Image
- width: 100%
- height: 100%
- object-fit: cover
- position: absolute
- inset: 0

### Subtitle Badge
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
- margin-bottom: 2rem

### Feature Item
- display: flex
- gap: 1rem
- margin-bottom: 1.5rem

### Feature Icon Wrapper
- width: 3rem (48px)
- height: 3rem (48px)
- border-radius: 0.75rem (12px)
- display: flex
- align-items: center
- justify-content: center
- flex-shrink: 0

### Feature Icon 1 (Green gradient)
- background: linear-gradient(135deg, #16a249, #10b981)

### Feature Icon 2 (Purple gradient)
- background: linear-gradient(135deg, #8b5cf6, #a855f7)

### Feature Icon 3 (Red gradient)
- background: linear-gradient(135deg, #f43f5e, #fb7185)

### Feature Icon 4 (Amber gradient)
- background: linear-gradient(135deg, #f59e0b, #fbbf24)

### Feature Title
- font-size: 1rem (16px)
- font-weight: 700
- color: #0e1425
- margin-bottom: 0.25rem

### Feature Description
- font-size: 0.875rem (14px)
- color: #6b7280
- line-height: 1.5

## States& Behaviors
- Static section, no interactive states
- Image has subtle hover scale effect (group-hover)

## Responsive Behavior
- **Desktop (1440px):** 2-column grid, image on left
- **Tablet (768px):** 2-column grid
- **Mobile (390px):** Single column, image on top

## Text Content (verbatim)
- Badge: "Câu chuyện của chúng tôi"
- Title: "Vì sao chọn Newtrip?"
- Features:
  1. "Đội ngũ hướng dẫn viên nhiệt huyết, am hiểu từng địa danh"
  2. "An toàn làưu tiên số một"
  3. "Trải nghiệm được thiết kế riêng cho từng nhóm"
  4. "Giá cả minh bạch, không phí ẩn"

## Assets
- About image: Unsplash adventure photo (https://images.unsplash.com/photo-1551632811-561732d1e306)
- Icons: UsersIcon, ShieldCheckIcon, SparklesIcon, WalletIcon from icons.tsx
