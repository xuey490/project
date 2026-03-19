# FINAL_DESIGN_SPEC.md Template

> This template is used in Mode 3 Phase 0 to document the design system and creative direction as a single source of truth.

## Template

```markdown
# FINAL DESIGN SPEC: [Project Name]

> ⚠️ **THIS IS THE SINGLE SOURCE OF TRUTH FOR CODE GENERATION**
> All code MUST use exact values from this document.

---

## 1. Creative Direction

**Tone**: [from aesthetic_direction.md - e.g., "Premium - Editorial/Magazine"]
**Memorable Element**: [from aesthetic_direction.md - the ONE thing someone will remember]
**Animation Strategy**: [e.g., "Staggered fade-in-up with 100ms delays, subtle card lifts on hover"]

---

## 2. Colors (MUST USE EXACT HEX)

| Token | Hex | Tailwind Class |
|-------|-----|----------------|
| Primary | #1E3A5F | `bg-[#1E3A5F]` `text-[#1E3A5F]` |
| Secondary | #3B82F6 | `bg-[#3B82F6]` `text-[#3B82F6]` |
| CTA/Accent | #F97316 | `bg-[#F97316]` `text-[#F97316]` |
| Background | #FFFFFF | `bg-[#FFFFFF]` |
| Surface | #F8FAFC | `bg-[#F8FAFC]` |
| Text Primary | #1E293B | `text-[#1E293B]` |
| Text Secondary | #64748B | `text-[#64748B]` |
| Border | #E2E8F0 | `border-[#E2E8F0]` |

---

## 3. Typography (MUST USE EXACT SIZES)

**Fonts:**
- Display: [Font Name] — `font-['Font_Name']`
- Body: [Font Name] — `font-['Font_Name']`

**Size Scale:**

| Element | Size | Weight | Line Height | Tailwind Class |
|---------|------|--------|-------------|----------------|
| H1 | 48px | 700 | 1.2 | `text-[48px] font-bold leading-[1.2]` |
| H2 | 36px | 600 | 1.2 | `text-[36px] font-semibold leading-[1.2]` |
| H3 | 28px | 600 | 1.3 | `text-[28px] font-semibold leading-[1.3]` |
| H4 | 24px | 600 | 1.3 | `text-[24px] font-semibold leading-[1.3]` |
| Body Large | 18px | 400 | 1.6 | `text-[18px] font-normal leading-[1.6]` |
| Body | 16px | 400 | 1.5 | `text-[16px] font-normal leading-[1.5]` |
| Small | 14px | 400 | 1.5 | `text-[14px] font-normal leading-[1.5]` |
| Caption | 12px | 500 | 1.4 | `text-[12px] font-medium leading-[1.4]` |

---

## 4. Spacing (MUST USE EXACT VALUES)

| Token | Value | Use Case | Tailwind Class |
|-------|-------|----------|----------------|
| xs | 4px | Icon gaps | `gap-[4px]` `p-[4px]` |
| sm | 8px | Tight spacing | `gap-[8px]` `p-[8px]` |
| md | 16px | Standard padding | `gap-[16px]` `p-[16px]` |
| lg | 24px | Card padding | `gap-[24px]` `p-[24px]` |
| xl | 32px | Section gaps | `gap-[32px]` `p-[32px]` |
| 2xl | 48px | Large gaps | `gap-[48px]` `p-[48px]` |
| 3xl | 64px | Section padding | `py-[64px]` `px-[64px]` |
| 4xl | 80px | Hero padding | `py-[80px]` |
| 5xl | 120px | Major sections | `py-[120px]` |

---

## 5. Border Radius (MUST USE EXACT VALUES)

| Token | Value | Use Case | Tailwind Class |
|-------|-------|----------|----------------|
| sm | 4px | Small buttons, tags | `rounded-[4px]` |
| md | 8px | Buttons, inputs | `rounded-[8px]` |
| lg | 12px | Cards | `rounded-[12px]` |
| xl | 16px | Modals, large cards | `rounded-[16px]` |
| 2xl | 24px | Hero images | `rounded-[24px]` |
| full | 9999px | Avatars, pills | `rounded-full` |

---

## 6. Shadows (MUST USE EXACT VALUES)

| Token | Value | Tailwind Class |
|-------|-------|----------------|
| sm | `0 1px 2px rgba(0,0,0,0.05)` | `shadow-[0_1px_2px_rgba(0,0,0,0.05)]` |
| md | `0 4px 6px rgba(0,0,0,0.1)` | `shadow-[0_4px_6px_rgba(0,0,0,0.1)]` |
| lg | `0 10px 15px rgba(0,0,0,0.1)` | `shadow-[0_10px_15px_rgba(0,0,0,0.1)]` |
| xl | `0 20px 25px rgba(0,0,0,0.15)` | `shadow-[0_20px_25px_rgba(0,0,0,0.15)]` |

---

## 7. Component Specs (Reference)

### Buttons
```
Primary:   bg-[CTA] text-white px-[24px] py-[12px] rounded-[8px] font-semibold
Secondary: bg-transparent border-[2px] border-[Primary] text-[Primary] px-[24px] py-[12px] rounded-[8px]
Ghost:     bg-transparent text-[Primary] px-[16px] py-[8px] rounded-[8px]
```

### Cards
```
Default:   bg-[Surface] rounded-[12px] p-[24px] shadow-md
Elevated:  bg-[Background] rounded-[16px] p-[32px] shadow-lg
```

### Inputs
```
Default:   border-[1px] border-[Border] rounded-[8px] px-[16px] py-[12px] text-[16px]
Focus:     border-[Primary] shadow-[0_0_0_3px_rgba(Primary,0.1)]
```

---

## 8. Layout Constraints

| Element | Constraint |
|---------|------------|
| Max content width | 1200px |
| Container padding | 16px (mobile) / 24px (tablet) / 32px (desktop) |
| Section vertical padding | 64px (mobile) / 80px (desktop) |
| Card grid gap | 24px |
| Nav height | 64px (desktop) / 56px (mobile) |

---

## 9. Premium Enhancement Guidelines

> **核心理念**：遵循设计系统的同时，应用拔高技巧创造高端质感。

### Baseline → Premium

| 类别 | Baseline | Premium Enhancement |
|------|----------|---------------------|
| **背景** | 纯色 `bg-white` | 微妙渐变 `bg-gradient-to-br from-white to-slate-50` + 光斑 |
| **间距** | 均匀 `py-16` | 大胆留白 `py-32 lg:py-40` + 大-小-大节奏 |
| **阴影** | 单层 `shadow-md` | 多层 + 彩色投影 `shadow-xl shadow-primary/20` |
| **卡片** | 纯色背景 | 玻璃态 `bg-white/80 backdrop-blur-xl` |
| **动效** | 简单 hover | 交错入场 + 精致过渡 |

### Enhancement Example

```tsx
// Baseline: 基础实现
<section className="py-16 bg-white">
  <h1 className="text-4xl font-bold">Title</h1>
</section>

// Premium: 拔高实现
<section className="
  py-[80px] lg:py-[120px]
  bg-gradient-to-br from-[#FFFFFF] to-[#F8FAFC]
  relative overflow-hidden
">
  {/* Atmospheric glow */}
  <div className="absolute top-20 right-0 w-[500px] h-[500px] bg-primary/10 rounded-full blur-3xl" />
  
  <h1 className="
    text-[48px] lg:text-[72px]
    font-black tracking-tight leading-none
    relative z-10
  ">
    Title
  </h1>
</section>
```
```

**Output Location**: `<project>_content/FINAL_DESIGN_SPEC.md`
