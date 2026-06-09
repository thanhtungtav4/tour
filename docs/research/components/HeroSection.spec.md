# HeroSection Component Specification

## Overview
- **Target file:** `src/components/HeroSection.tsx`
- **Screenshot:** `docs/design-references/desktop-1440px.png` (first section below header)
- **Interaction model:** Static with filter pills

## DOM Structure
```
<section> (section-padding bg-gradient-to-b from-background to-background-accent)
  <div> (container mx-auto)
    <div> (text-center mb-12)
      <h1> "Lịch trình sắp tới"
      <p> "Những trải nghiệm tuyệt vời đang chờ đón bạn trong thời gian tới"
    </div>
    <div> (stats flex justify-center gap-8 mb-8)
      <div> (stat)
        <span> "12"
        <span> "chuyến"
      </div>
      <div> (stat)
        <span> "Sắp khởi hành"
      </div>
    </div>
    <div> (filter pills container)
      <div> (filter group: Thời gian)
        <button> "Sáng"
        <button> "Hệ thống"
        <button> "Tối"
      </div>
      <div> (filter group: Giá)
        <button> "Dưới 500k"
        <button> "500k - 1tr"
        <button> "Trên 1tr"
      </div>
      <div> (filter group: Thời lượng)
        <button> "1 ngày"
        <button> "2-3 ngày"
        <button> "4+ ngày"
      </div>
      <div> (filter group: Độ khó)
        <button> "Dễ"
        <button> "Trung bình"
        <button> "Khó"
      </div>
    </div>
  </div>
</section>
```

## Computed Styles (exact values)

### Section
- padding-top: 4rem (mobile), 6rem (desktop)
- padding-bottom: 4rem (mobile), 6rem (desktop)
- background: linear-gradient(to bottom, #ffffff, #f5f7fa)
- overflow: hidden

### Title
- font-size: 2.5rem (40px) mobile,3.5rem (56px) desktop
- font-weight: 800
- color: #0e1425
- margin-bottom: 1rem
- text-align: center

### Subtitle
- font-size: 1.125rem (18px)
- color: #6b7280
- text-align: center
- margin-bottom: 3rem

### Stats Container
- display: flex
- justify-content: center
- gap: 2rem (32px)
- margin-bottom: 2rem

### Stat Number
- font-size: 3rem (48px)
- font-weight: 800
- color: #16a249 (primary)
- line-height: 1

### Stat Label
- font-size: 1rem (16px)
- color: #6b7280
- margin-top: 0.25rem

### Filter Pills Container
- display: flex
- flex-wrap: wrap
- gap: 1rem
- justify-content: center

### Filter Group
- display: flex
- gap: 0.5rem
- flex-wrap: wrap

### Filter Pill (inactive)
- padding: 0.375rem 0.75rem (px-3 py-1.5)
- font-size: 0.75rem (12px)
- font-weight: 500
- background: #ffffff
- border: 1px solid #d3dae4
- border-radius: 9999px (full)
- transition: all 0.2s

### Filter Pill (active)
- background: #16a249
- color: #ffffff
- border-color: #16a249

### Filter Pill Hover
- border-color: #16a249
- color: #16a249

## States& Behaviors

### Filter Pills
- **Type:** Click-driven state switching
- **Behavior:** Clicking a pill toggles its active state
- **Multiple selection:** Yes, within each group
- **Transition:** background 0.2s, color 0.2s, border-color 0.2s

## Responsive Behavior
- **Desktop (1440px):** Larger title (56px), more spacing
- **Tablet (768px):** Same as desktop
- **Mobile (390px):** Smaller title (40px), stacked filters

## Text Content (verbatim)
- Title: "Lịch trình sắp tới"
- Subtitle: "Những trải nghiệm tuyệt vời đang chờ đón bạn trong thời gian tới"
- Stats: "12" + "chuyến", "Sắp khởi hành"
- Filter labels: "Sáng", "Hệ thống", "Tối", "Dưới 500k", "500k - 1tr", "Trên 1tr", "1 ngày", "2-3 ngày", "4+ ngày", "Dễ", "Trung bình", "Khó"

## Assets
- No images required
- Icons: None specific to this section
