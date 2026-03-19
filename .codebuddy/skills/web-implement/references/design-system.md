# Design System Reference

This project uses a CSS variable-based design system defined in `frontend/src/index.css`.


## Tailwind v4 Container Fix (CRITICAL)

Tailwind CSS v4 `container` class **no longer auto-centers** (unlike v3). The template `index.css` includes a `@utility container` rule that restores `margin-inline: auto`, responsive `max-width` breakpoints, and `padding-inline: 1rem`. 
=======
## Why CSS Variables Matter

This project includes a **design system editor** that lets users adjust visual styles (colors, font sizes, spacing, shadows, border radius) at runtime. When the editor modifies a CSS variable on `:root`, every element referencing that variable updates automatically.

**If you hardcode values instead of using CSS variables, the editor cannot control those elements.** This is the single most important rule in this design system.

The design system editor controls variables across these categories:

| Category | Editor controls | How it works |
|----------|----------------|--------------|
| Colors | `--primary`, `--background`, `--accent`, etc. | Tailwind classes like `bg-primary` already map to these |
| Font sizes | `--font-size-body`, `--font-size-label`, `--font-size-small` | Mapped to `text-base`, `text-sm`, `text-xs` via `@theme` |
| Shadows | `--ds-shadow-sm/md/lg/xl` | Mapped to `shadow-sm`, `shadow-md`, etc. via `@theme` |
| Spacing | `--spacing-xs` ~ `--spacing-3xl` | Use `var()` in inline styles or custom classes |
| Border radius | `--radius` | Mapped to `rounded-sm/md/lg/xl` via `@theme` |
>>>>>>> main

---

## Dark Mode Policy

**DO NOT implement dark mode unless user explicitly requests it.**

Rationale:
- Reduces development complexity
- Avoids unnecessary style debugging
- Keeps deliverables simple and focused

If user explicitly requests dark mode, use the `.dark` class on `<html>` element and reference the dark mode variables defined in index.css.

---

## Design Variables Overview

### Color System

#### Core Colors
| Variable | Purpose | Tailwind Class |
|----------|---------|----------------|
| `--background` | Page background | `bg-background` |
| `--foreground` | Primary text | `text-foreground` |
| `--card` | Card/container background | `bg-card` |
| `--card-foreground` | Card text | `text-card-foreground` |
| `--popover` | Popover/dropdown background | `bg-popover` |
| `--popover-foreground` | Popover text | `text-popover-foreground` |

#### Semantic Colors
| Variable | Purpose | Tailwind Class |
|----------|---------|----------------|
| `--primary` | Primary actions, CTAs | `bg-primary`, `text-primary` |
| `--primary-foreground` | Text on primary | `text-primary-foreground` |
| `--secondary` | Secondary actions | `bg-secondary` |
| `--secondary-foreground` | Text on secondary | `text-secondary-foreground` |
| `--muted` | Muted backgrounds | `bg-muted` |
| `--muted-foreground` | Secondary text, placeholders | `text-muted-foreground` |
| `--accent` | Hover states, highlights | `bg-accent` |
| `--accent-foreground` | Text on accent | `text-accent-foreground` |

#### Functional Colors
| Variable | Purpose | Tailwind Class |
|----------|---------|----------------|
| `--destructive` | Error, delete actions | `bg-destructive` |
| `--destructive-foreground` | Text on destructive | `text-destructive-foreground` |
| `--success` | Success states | `bg-success` |
| `--success-foreground` | Text on success | `text-success-foreground` |
| `--warning` | Warning states | `bg-warning` |
| `--warning-foreground` | Text on warning | `text-warning-foreground` |
| `--info` | Info states | `bg-info` |
| `--info-foreground` | Text on info | `text-info-foreground` |

#### Theme Accent Colors
| Variable | Purpose |
|----------|---------|
| `--theme-red` | Red accent |
| `--theme-green` | Green accent |
| `--theme-gold` | Gold accent |
| `--theme-blue` | Blue accent |

#### Border & Input
| Variable | Purpose | Tailwind Class |
|----------|---------|----------------|
| `--border` | Default borders | `border-border` |
| `--input` | Input borders | `border-input` |
| `--ring` | Focus rings | `ring-ring` |

### Typography

| Variable | Default Value | Purpose | Tailwind Mapping |
|----------|---------------|---------|------------------|
| `--font-family` | Inter, system fonts | Base font stack | - |
| `--font-size-display` | 3.5rem | Hero headlines | `style={{ fontSize: 'var(--font-size-display)' }}` |
| `--font-size-headline` | 2rem | Page titles | `style={{ fontSize: 'var(--font-size-headline)' }}` |
| `--font-size-title` | 1.5rem | Section titles | `style={{ fontSize: 'var(--font-size-title)' }}` |
| `--font-size-body` | 1rem | Body text | `text-base` |
| `--font-size-label` | 0.875rem | Labels, captions | `text-sm` |
| `--font-size-small` | 0.75rem | Small text, hints | `text-xs` |
| `--font-weight-bold` | 700 | Bold text | - |
| `--font-weight-semibold` | 600 | Semi-bold text | - |
| `--font-weight-medium` | 500 | Medium text | - |
| `--font-weight-normal` | 400 | Normal text | - |
| `--line-height` | 1.5 | Default line height | - |

> **Note:** `text-base`, `text-sm`, `text-xs` are bridged to design system variables via `@theme inline` in `index.css`. Changing `--font-size-body` at runtime will automatically update all `text-base` elements. For display/headline/title sizes, use inline styles with `var()` because they are not mapped to standard Tailwind classes.

### Spacing

| Variable | Default Value | Use Case |
|----------|---------------|----------|
| `--spacing-xs` | 0.5rem (8px) | Tight gaps (icon-to-label, badge padding) |
| `--spacing-sm` | 0.75rem (12px) | Element gaps (button groups, form fields) |
| `--spacing-md` | 1rem (16px) | Default spacing (card padding, list items) |
| `--spacing-lg` | 1.5rem (24px) | Section padding |
| `--spacing-xl` | 2rem (32px) | Large section gaps |
| `--spacing-2xl` | 3rem (48px) | Page section separators |
| `--spacing-3xl` | 4rem (64px) | Hero section padding |

> **Note:** Spacing variables are NOT mapped to Tailwind's `p-*` / `m-*` / `gap-*` classes. Use `style={{ padding: 'var(--spacing-md)' }}` or `style={{ gap: 'var(--spacing-sm)' }}` for design-system-controlled spacing.

### Shadows

| Variable | Use Case | Tailwind Class |
|----------|----------|----------------|
| `--ds-shadow-sm` | Subtle elevation (cards) | `shadow-sm` |
| `--ds-shadow-md` | Medium elevation (dropdowns) | `shadow-md` |
| `--ds-shadow-lg` | High elevation (modals) | `shadow-lg` |
| `--ds-shadow-xl` | Maximum elevation (popovers) | `shadow-xl` |

> **Note:** Shadow Tailwind classes (`shadow-sm/md/lg/xl`) are bridged to `--ds-shadow-*` variables via `@theme inline`. Backward-compatible aliases `--shadow-sm/md/lg/xl` are also available for raw CSS `var()` usage.

### Border Radius

| Variable | Value | Tailwind Class |
|----------|-------|----------------|
| `--radius` | Base radius (0.625rem) | - |
| `--radius-sm` | `calc(var(--radius) - 4px)` | `rounded-sm` |
| `--radius-md` | `calc(var(--radius) - 2px)` | `rounded-md` |
| `--radius-lg` | `var(--radius)` | `rounded-lg` |
| `--radius-xl` | `calc(var(--radius) + 4px)` | `rounded-xl` |

### Animation

| Variable | Default Value | Use Case |
|----------|---------------|----------|
| `--duration-fast` | 150ms | Hover states |
| `--duration-normal` | 200ms | Standard transitions |
| `--duration-slow` | 300ms | Complex animations |
| `--ease-default` | cubic-bezier(0.4, 0, 0.2, 1) | General purpose |
| `--ease-in` | cubic-bezier(0.4, 0, 1, 1) | Enter animations |
| `--ease-out` | cubic-bezier(0, 0, 0.2, 1) | Exit animations |
| `--ease-bounce` | cubic-bezier(0.34, 1.56, 0.64, 1) | Playful/bouncy interactions |

**These variables are pre-defined in `index.css` `:root`.** When the design system spec (MASTER.md) provides different duration/easing values, update them accordingly.

---

## Page Transitions (framer-motion)

The design system spec (generated by `search.py --design-system`) outputs a **"Page Transitions"** section with a recommended `transition` mode. **The template already includes all necessary animation components — DO NOT recreate them.**

### Pre-built Animation Components (already in template)

| Component | Import | Purpose |
|-----------|--------|---------|
| `AnimatedRoutes` | `@/components/AnimatedRoutes` | Wraps `<Routes>` with `<AnimatePresence mode="popLayout">` + `key={location.pathname}` |
| `PageTransition` | `@/components/PageTransition` | Page-level enter/exit animation wrapper (0.2s duration) |
| `MotionPrimitives` | `@/components/MotionPrimitives` | In-page animation building blocks (`FadeIn`, `Stagger`, `HoverLift`, variant presets) |

### AnimatePresence Mode: popLayout

使用 `mode="popLayout"` 而非 `"wait"`：
- **"wait"** 会等待退出动画完成才开始进入动画（总延迟 ~0.6s，体验卡顿）
- **"popLayout"** 允许新页面立即进入，旧页面同时退出（更流畅，~0.2s）

### Available Transition Modes

| Mode | Effect | Best For |
|------|--------|----------|
| `fade` | Opacity only (default) | General purpose, safest option |
| `slide-up` | Opacity + translateY upward | Content-heavy pages, blogs, storytelling |
| `slide-fade` | Opacity + translateX horizontal | Multi-step flows, wizards, dashboards |
| `scale` | Opacity + scale | Neumorphism, soft UI, modals |

**Choose the mode that matches the design system style.** The MASTER.md spec will recommend one; you may also choose based on page type.

### Integration Steps

1. **Update animation token variables** in `:root` if the spec provides different duration/easing values:
```css
:root {
  --duration-normal: 200ms;
  --ease-default: cubic-bezier(0.4, 0, 0.2, 1);
}
```

2. **Layout in `App.tsx`** — Navbar/Header/Sidebar OUTSIDE AnimatedRoutes:

**⛔ CRITICAL: Navbar / Header / Sidebar / 任何共享布局组件必须放在 `<AnimatedRoutes>` 外部**，否则每次页面切换都会：
- 销毁并重新创建这些组件
- 让这些组件参与页面过渡动画（抖动、闪烁）

```tsx
import { AnimatedRoutes } from "@/components/AnimatedRoutes";
import { PageTransition } from "@/components/PageTransition";

function App() {
  return (
    <BrowserRouter>
      <ScrollToTop />
      {/* ✅ Navbar 在 AnimatedRoutes 外部 */}
      <Navbar />
      <AnimatedRoutes>
        <Route path="/" element={<PageTransition transition="slide-up"><Index /></PageTransition>} />
        {/* other routes */}
        <Route path="*" element={<PageTransition transition="fade"><NotFound /></PageTransition>} />
      </AnimatedRoutes>
    </BrowserRouter>
  );
}

// ✅ 带 Sidebar 的 Dashboard 布局
function App() {
  return (
    <BrowserRouter>
      <div className="flex">
        {/* ✅ Sidebar 在 AnimatedRoutes 外部 */}
        <Sidebar />
        <main className="flex-1">
          <Header />
          <AnimatedRoutes>
            <Route path="/dashboard" element={<PageTransition><Dashboard /></PageTransition>} />
            <Route path="/settings" element={<PageTransition><Settings /></PageTransition>} />
          </AnimatedRoutes>
        </main>
      </div>
    </BrowserRouter>
  );
}
```

**⚠️ WRONG patterns:**
```tsx
// ❌ WRONG — Navbar inside AnimatedRoutes (flickers on page switch)
<AnimatedRoutes>
  <Route path="/" element={<><Navbar /><Index /></>} />
</AnimatedRoutes>

// ❌ WRONG — Navbar inside each page component (re-mounts on switch)
function Dashboard() {
  return (<><Navbar /><main>...</main></>);
}

// ❌ WRONG — Sidebar inside PageTransition (re-mounts and animates)
<Route path="/dashboard" element={
  <PageTransition>
    <div className="flex">
      <Sidebar />
      <Dashboard />
    </div>
  </PageTransition>
} />
```

### Reduced Motion

The `prefers-reduced-motion` fallback is already included in the CSS — `transition: none` ensures instant page swaps for users who prefer reduced motion.

---

## Hardcoded Value to CSS Variable Mapping

When you see hardcoded Tailwind classes, replace them with the design system equivalents:

### Font Size

| Hardcoded (avoid) | Design System (use) | When to use |
|--------------------|---------------------|-------------|
| `text-xs` | `text-xs` | Badges, hints, footnotes (already bridged) |
| `text-sm` | `text-sm` | Labels, captions, form descriptions (already bridged) |
| `text-base` | `text-base` | Body text, paragraphs (already bridged) |
| `text-lg` | `style={{ fontSize: 'var(--font-size-title)' }}` | Section titles |
| `text-xl` | `style={{ fontSize: 'var(--font-size-headline)' }}` | Page titles |
| `text-4xl` | `style={{ fontSize: 'var(--font-size-display)' }}` | Hero headlines |

### Spacing (in page/business components)

| Hardcoded (avoid) | Design System (use) |
|--------------------|---------------------|
| `p-2` | `style={{ padding: 'var(--spacing-xs)' }}` |
| `p-3` | `style={{ padding: 'var(--spacing-sm)' }}` |
| `p-4` | `style={{ padding: 'var(--spacing-md)' }}` |
| `p-6` | `style={{ padding: 'var(--spacing-lg)' }}` |
| `p-8` | `style={{ padding: 'var(--spacing-xl)' }}` |
| `gap-2` | `style={{ gap: 'var(--spacing-xs)' }}` |
| `gap-3` | `style={{ gap: 'var(--spacing-sm)' }}` |
| `gap-4` | `style={{ gap: 'var(--spacing-md)' }}` |
| `gap-6` | `style={{ gap: 'var(--spacing-lg)' }}` |
| `mb-4` | `style={{ marginBottom: 'var(--spacing-md)' }}` |

### Shadows

| Hardcoded (avoid) | Design System (use) |
|--------------------|---------------------|
| `shadow-sm` | `shadow-sm` (already bridged via `@theme`) |
| `shadow-md` | `shadow-md` (already bridged) |
| `shadow-lg` | `shadow-lg` (already bridged) |
| `shadow-xl` | `shadow-xl` (already bridged) |

> Shadows are automatically bridged -- `shadow-md` already resolves to `var(--ds-shadow-md)`. No changes needed.

### Border Radius

| Hardcoded (avoid) | Design System (use) |
|--------------------|---------------------|
| `rounded-sm` | `rounded-sm` (already bridged via `@theme`) |
| `rounded-md` | `rounded-md` (already bridged) |
| `rounded-lg` | `rounded-lg` (already bridged) |

> Radius is automatically bridged -- `rounded-lg` already resolves to `var(--radius)`. No changes needed.

---

## Scope Rules: shadcn/ui vs Business Code

### `components/ui/` (shadcn/ui components)

These are upstream-generated components. **Keep their original Tailwind classes.** Do not replace `text-sm`, `p-4`, etc. inside shadcn/ui components. The `@theme` bridge handles the mapped properties (text-xs/sm/base, shadow-*, rounded-*) automatically.

### `pages/`, `components/[domain]/`, and all business code

This is where you write new code. **Always use CSS variables** for typography, spacing, and colors. These are the files the design system editor needs to control.

---

## Usage Guidelines

### Correct Usage

```tsx
// Colors - use Tailwind classes mapped to CSS variables
<button className="bg-primary text-primary-foreground rounded-lg shadow-md">
  Submit
</button>

// Card with design system spacing and shadow
<div
  className="bg-card border border-border rounded-lg shadow-md"
  style={{ padding: 'var(--spacing-lg)' }}
>
  <h2 style={{ fontSize: 'var(--font-size-title)', marginBottom: 'var(--spacing-sm)' }}>
    Card Title
  </h2>
  <p className="text-sm text-muted-foreground">
    Description text uses text-sm (bridged to --font-size-label).
  </p>
</div>

// Section with design system layout spacing
<section style={{ padding: 'var(--spacing-xl) 0', display: 'flex', flexDirection: 'column', gap: 'var(--spacing-lg)' }}>
  <h1 style={{ fontSize: 'var(--font-size-headline)' }}>Page Title</h1>
  <p className="text-base text-muted-foreground">Body text uses text-base (bridged to --font-size-body).</p>
</section>

// Functional colors for states
<span className="bg-success text-success-foreground">Completed</span>
<span className="bg-destructive text-destructive-foreground">Error</span>
```

### Incorrect Usage

```tsx
// WRONG - Hardcoded colors
<button className="bg-black text-white">Submit</button>
<div className="bg-gray-100 border-gray-200">...</div>

// WRONG - Inline styles with hardcoded values
<div style={{ backgroundColor: '#f5f5f5', color: '#333' }}>...</div>

// WRONG - Arbitrary color values
<div className="bg-[#1a1a1a] text-[#ffffff]">...</div>

// WRONG - Hardcoded font sizes for display/headline/title (not bridged to @theme)
<h1 className="text-4xl">Title</h1>  // Use style={{ fontSize: 'var(--font-size-display)' }}

// WRONG - Hardcoded spacing in business components
<div className="p-6 gap-4">...</div>  // Use style={{ padding: 'var(--spacing-lg)', gap: 'var(--spacing-md)' }}
```

### When to Use Each Color

| Scenario | Use |
|----------|-----|
| Page background | `bg-background` |
| Card/container | `bg-card` |
| Primary buttons | `bg-primary text-primary-foreground` |
| Secondary buttons | `bg-secondary text-secondary-foreground` |
| Disabled/muted text | `text-muted-foreground` |
| Error states | `bg-destructive` or `text-destructive` |
| Success states | `bg-success` or `text-success` |
| Borders | `border-border` |
| Focus rings | `ring-ring` |

---

## CRITICAL: Color Value Format

**All CSS color variables MUST use `oklch()` format. This is non-negotiable.**

```css
/* ✅ CORRECT - oklch format */
--primary: oklch(0.6 0.2 250);
--background: oklch(0.97 0.01 165);
--foreground: oklch(0.2 0.04 166);

/* ❌ WRONG - Bare HSL values (shadcn v1 legacy, NOT supported) */
--primary: 175 80% 29%;
--background: 165 76% 97%;

/* ❌ WRONG - hsl() function */
--primary: hsl(175, 80%, 29%);

/* ❌ WRONG - Hex values */
--primary: #0f766e;

/* ❌ WRONG - rgb() values */
--primary: rgb(15, 118, 110);
```

When converting from Hex to oklch, use this mapping approach:
- Lightness (L): 0-1 range (0 = black, 1 = white)
- Chroma (C): 0-0.4 range (0 = gray, higher = more saturated)
- Hue (H): 0-360 degrees

**If the design system spec provides Hex colors, you MUST convert them to oklch() format before writing to index.css.**

---

## Customizing Theme

To customize the theme, modify variables in `frontend/src/index.css`:

```css
:root {
  /* Change primary color */
  --primary: oklch(0.6 0.2 250);  /* Blue tone */
  --primary-foreground: oklch(0.985 0 0);
  
  /* Change border radius */
  --radius: 0.5rem;  /* More rounded */
  
  /* Change body font size */
  --font-size-body: 1.125rem;  /* Slightly larger body text */
  
  /* Change spacing scale */
  --spacing-md: 1.25rem;  /* Increase default spacing */
  
  /* Change shadow intensity */
  --ds-shadow-md: 0 4px 16px oklch(0.205 0 0 / 0.15), 0 2px 6px oklch(0.205 0 0 / 0.08);
}
```

All components using these variables will automatically update.
