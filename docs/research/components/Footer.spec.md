# Footer Component Specification

## Overview
- **Target file:** `src/components/Footer.tsx`
- **Screenshot:** `docs/design-references/desktop-1440px.png` (footer section)
- **Interaction model:** Static with hover states on links

## DOM Structure
```
<footer> (bg-surface text-foreground border-t border-border/60)
  <div> (container mx-auto py-12)
    <div> (grid md:grid-cols-4 gap-8)
      <div> (brand column)
        <a> (logo + tagline)
        <p> (contact info)
      </div>
      <div> (policies column)
        <h4> "Chính sách"
        <ul>
          <li> <a> "Chính sách an toàn"
          <li> <a> "Chính sách hủy vé, Hoàn, Bảo lưu vé"
          <li> <a> "Chính sách bảo lưu, đổi vé"
          <li> <a> "Chính sách hình ảnh"
          <li> <a> "Khách ngoại quốc"
        </ul>
      </div>
      <div> (contact column)
        <h4> "Liên hệ"
        <ul>
          <li> <a> (phone with icon)
          <li> <a> (email with icon)
        </ul>
      </div>
      <div> (booking column)
        <h4> "Đặt vé"
        <a> "Đặt vé ngay →"
      </div>
    </div>
    <div> (bottom bar border-t border-border/60 pt-8 mt-8)
      <p> "© 2024 Newtrip. All rights reserved."
    </div>
  </div>
</footer>
```

## Computed Styles (exact values)

### Footer Container
- background: #ffffff (surface)
- color: #0e1425 (foreground)
- border-top: 1px solid rgba(211, 218, 228, 0.6)
- padding-top: 3rem (48px)
- padding-bottom: 3rem (48px)

### Grid Container
- display: grid
- gap: 2rem (32px)
- grid-template-columns: 1fr

### Brand Column
- grid-column: span 1

### Logo
- display: flex
- align-items: center
- gap: 0.625rem
- font-size: 1.25rem (20px)
- font-weight: 700
- color: #0e1425
- margin-bottom: 1rem

### Logo Image
- width: 40px
- height: 40px
- border-radius: 50%

### Tagline
- font-size: 0.875rem (14px)
- color: #6b7280
- margin-bottom: 1.5rem

### Column Title
- font-size: 1rem (16px)
- font-weight: 700
- color: #0e1425
- margin-bottom: 1rem

### Policy Links
- list-style: none
- padding: 0
- margin: 0

### Policy Link
- display: block
- font-size: 0.875rem (14px)
- color: #6b7280
- padding: 0.375rem 0
- transition: color 0.2s

### Policy Link Hover
- color: #16a249

### Contact Item
- display: flex
- align-items: center
- gap: 0.5rem
- font-size: 0.875rem (14px)
- color: #6b7280
- margin-bottom: 0.5rem

### Contact Link Hover
- color: #16a249

### Phone Number
- font-weight: 600
- color: #0e1425

### Booking CTA
- display: inline-flex
- align-items: center
- gap: 0.5rem
- font-size: 0.625rem (10px)
- font-weight: 700
- color: #16a249
- transition: color 0.2s

### Booking CTA Hover
- color: rgba(22, 162, 73, 0.8)

### Bottom Bar
- border-top: 1px solid rgba(211, 218, 228, 0.6)
- padding-top: 2rem
- margin-top: 2rem

### Copyright
- font-size: 0.875rem (14px)
- color: #6b7280
- text-align: center

## States& Behaviors

### Link Hover
- **Trigger:** Mouse enter
- **Effect:** Color changes to primary (#16a249)
- **Transition:** color 0.2s

## Responsive Behavior
- **Desktop (1440px):** 4-column grid
- **Tablet (768px):** 2-column grid
- **Mobile (390px):** Single column, stacked

## Text Content (verbatim)
- Logo: "Newtrip"
- Tagline: "Siêu tiện - Siêu vui - Siêu Tiết Kiệm"
- Policies:
  - "Chính sách an toàn"
  - "Chính sách hủy vé, Hoàn, Bảo lưu vé"
  - "Chính sách bảo lưu, đổi vé"
  - "Chính sách hình ảnh"
  - "Khách ngoại quốc"
- Contact:
  - Phone: "0928382087"
  - Email: "Newtrip.com.vn@gmail.com"
- Booking CTA: "Đặt vé ngay →"
- Copyright: "© 2024 Newtrip. All rights reserved."

## Assets
- Logo image: `public/images/582549528-122103437163116307-4161394095605531748-n.jpg`
- Icons: PhoneIcon, MailIcon, ArrowRightIcon from icons.tsx
