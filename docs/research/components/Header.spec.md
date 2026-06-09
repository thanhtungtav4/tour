# Header Component Specification

## Overview
- **Target file:** `src/components/Header.tsx`
- **Screenshot:** `docs/design-references/desktop-1440px.png` (top section)
- **Interaction model:** Static with glassmorphism, mobile hamburger menu

## DOM Structure
```
<header> (fixed, z-50, glass-nav)
  <nav> (container mx-auto px-4 sm:px-6 lg:px-8)
<div> (flex items-center justify-between)
<a> (logo: Newtrip + tagline)
      <div> (nav links: desktop only)
        <a> Trang chủ
        <a> Tuyến đường
        <a> Về chúng tôi
        <a> Liên hệ
</div>
      <a> (CTA: Đặt vé ngay - desktop only)
      <button> (hamburger: lg:hidden)
</div>
  </nav>
</header>
```

## Computed Styles (exact values)

### Header Container
- position: fixed
- top: 0
- left: 0
- right: 0
- z-index: 50
- height: 81px
- background: rgba(255, 255, 255, 0.85)
- backdrop-filter: blur(18px)
- -webkit-backdrop-filter: blur(18px)
- box-shadow: 0px 10px 15px -3px rgba(0, 0, 0, 0.1), 0px 4px 6px -4px
- transition: all 0.3s duration

### Logo
- font-size: 1.25rem (20px)
- font-weight: 700
- color: #0e1425
- display: flex
- align-items: center
- gap: 0.625rem (10px)

### Logo Image
- width: 40px
- height: 40px
- border-radius: 50%

### Nav Links
- font-size: 0.875rem (14px)
- font-weight: 500
- color: #6b7280 (muted-foreground)
- transition: all 0.3s duration
- padding: 0.5rem 0
- position: relative

### Nav Link Hover
- color: #16a249 (primary)
- text-decoration: none

### Active Nav Link
- color: #16a249 (primary)

### CTA Button
- display: none (md:inline-flex)
- padding: 0.5rem 1rem (px-4 py-2)
- font-size: 0.875rem (14px)
- font-weight: 500
- background: linear-gradient(135deg, #16a249 0%, rgba(22, 162, 73, 0.8) 100%)
- color: white
- border-radius: 0.5rem (8px)
- hover: opacity 90%

### Hamburger Button
- display: block (lg:hidden)
- padding: 0.5rem
- border-radius: 0.5rem
- hover: background: #f5f7fa

## States& Behaviors

### Mobile Menu Toggle
- **Trigger:** Click on hamburger button
- **Behavior:** Opens/closes mobile menu overlay
- **Transition:** Fade in/out 0.2s

### Mobile Menu
- **Position:** Fixed overlay, full screen
- **Background:** rgba(255, 255, 255, 0.95) with backdrop blur
- **Links:** Full-width, stacked vertically
- **Close button:** X icon in top-right

## Responsive Behavior
- **Desktop (1440px):** Full nav links visible, CTA visible
- **Tablet (768px):** Same as desktop
- **Mobile (390px):** Hamburger menu, no CTA
- **Breakpoint:** lg (1024px) - nav collapses to hamburger

## Text Content (verbatim)
- Logo text: "Newtrip"
- Tagline: "Siêu tiện - Siêu vui - Siêu Tiết Kiệm"
- Nav: "Trang chủ", "Tuyến đường", "Về chúng tôi", "Liên hệ"
- CTA: "Đặt vé ngay"

## Assets
- Logo image: `public/images/582549528-122103437163116307-4161394095605531748-n.jpg`
- Icons: MenuIcon, CloseIcon from icons.tsx
