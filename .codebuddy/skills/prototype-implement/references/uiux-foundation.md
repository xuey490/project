# UI/UX Foundation - Structure Before Style

> **大原则：先保证结构合理、布局正常、交互正确，再追求设计感和高级感。**
> 
> 没有坚实的基础，再华丽的视觉效果也会显得业余。

---

## 📐 Layout & Spacing Rules

| Rule | Do | Don't |
|------|----|----- |
| **Sticky Navigation** | Add `pt-20` (padding-top) to body equal to nav height | Let nav overlap first section content |
| **Floating Navbar** | Add `top-4 left-4 right-4` spacing from edges | Stick navbar to `top-0 left-0 right-0` |
| **Container Width** | Use consistent `max-w-6xl` or `max-w-7xl` | Mix different container widths |
| **Content Max Width** | Limit text to 65-75ch (`max-w-prose`) | Full viewport width paragraphs |
| **Z-Index Scale** | Define scale system: z-10, z-20, z-30, z-50 | Arbitrary large z-index values like z-[9999] |
| **Viewport Units** | Use `min-h-dvh` or `min-h-screen` | Use `h-screen` for full-screen mobile layouts |
| **Horizontal Scroll** | Ensure content fits viewport width | Allow content wider than viewport |

```tsx
// ✅ CORRECT - Proper sticky nav with content offset
<nav className="fixed top-4 left-4 right-4 h-16 z-50 bg-white/80 backdrop-blur-xl rounded-2xl">
  ...
</nav>
<main className="pt-24"> {/* Account for nav height + spacing */}
  <section>Content here</section>
</main>

// ❌ WRONG - Nav overlapping content
<nav className="fixed top-0 left-0 right-0 h-16">...</nav>
<main> {/* Content hidden behind nav! */}
  <section>This will be covered</section>
</main>
```

---

## 👆 Interaction & Feedback Rules

| Rule | Do | Don't |
|------|----|----- |
| **Cursor Pointer** | Add `cursor-pointer` to all clickable elements | Leave default cursor on interactive cards |
| **Hover States** | Provide visual feedback (color, shadow, border) | No indication element is interactive |
| **Focus States** | Use `focus-visible:ring-2` for keyboard navigation | Remove focus outline without replacement |
| **Active States** | Add pressed/active visual change `active:scale-95` | No feedback during interaction |
| **Disabled States** | Use `opacity-50 cursor-not-allowed` | Same style as enabled state |
| **Loading States** | Show spinner/skeleton for operations > 300ms | Leave UI frozen with no feedback |
| **Smooth Transitions** | Use `transition-colors duration-200` | Instant changes or too slow (>500ms) |

```tsx
// ✅ CORRECT - Complete interaction states
<Card className="
  cursor-pointer
  transition-all duration-200
  hover:shadow-lg hover:-translate-y-1
  active:scale-[0.98]
  focus-visible:ring-2 focus-visible:ring-primary
">
  <CardContent>Interactive Card</CardContent>
</Card>

// ❌ WRONG - No interaction feedback
<Card>
  <CardContent>Card with no hover/active states</CardContent>
</Card>
```

---

## ✅ Accessibility Essentials

| Rule | Do | Don't |
|------|----|----- |
| **Icon Button Labels** | Add `aria-label="Close menu"` | Icon button without accessible name |
| **Form Labels** | Use `<label htmlFor="email">` or wrap input | Placeholder as only label |
| **Keyboard Handlers** | Add `onKeyDown` alongside `onClick` for custom elements | Click-only interaction |
| **Semantic HTML** | Use `<nav>`, `<main>`, `<section>`, `<article>` | `<div>` for everything |
| **Heading Hierarchy** | Use sequential h1 → h2 → h3 | Skip levels or misuse for styling |
| **Color Contrast** | Minimum 4.5:1 ratio for text | Low contrast text (#999 on white) |
| **Image Alt Text** | Descriptive alt for meaningful images | Empty or missing alt attributes |

```tsx
// ✅ CORRECT - Accessible icon button
<Button variant="ghost" size="icon" aria-label="Close menu">
  <X className="w-5 h-5" />
</Button>

// ❌ WRONG - No accessible name
<Button variant="ghost" size="icon">
  <X className="w-5 h-5" />
</Button>
```

---

## 📋 Form Best Practices

| Rule | Do | Don't |
|------|----|----- |
| **Input Types** | Use `type="email"`, `type="tel"`, `type="url"` | `type="text"` for everything |
| **Autocomplete** | Add `autocomplete="email"` attribute | Missing autocomplete |
| **Error Placement** | Show error below related input field | Single error message at top of form |
| **Inline Validation** | Validate on blur for most fields | Validate only on submit |
| **Submit Button** | Show loading spinner, keep enabled | Disable button during submit |
| **Required Fields** | Mark with asterisk or "(required)" | No indication of required fields |
| **Password Visibility** | Provide show/hide toggle | Password always hidden |
| **Never Block Paste** | Allow paste on all inputs | `onPaste={e => e.preventDefault()}` |

```tsx
// ✅ CORRECT - Proper form field
<div className="space-y-2">
  <label htmlFor="email" className="text-sm font-medium">
    Email <span className="text-red-500">*</span>
  </label>
  <Input 
    id="email"
    type="email" 
    autoComplete="email"
    placeholder="you@example.com"
  />
  {error && <p className="text-sm text-red-500">{error}</p>}
</div>

// ❌ WRONG - Missing label, wrong type
<Input placeholder="Email" type="text" />
```

---

## 🎬 Animation Rules

| Rule | Do | Don't |
|------|----|----- |
| **Reduced Motion** | Respect `prefers-reduced-motion` | Ignore accessibility motion settings |
| **Transform Performance** | Animate `transform` and `opacity` only | Animate `width`, `height`, `top`, `left` |
| **Duration Timing** | Use 150-300ms for micro-interactions | Animations longer than 500ms for UI |
| **Easing Functions** | Use `ease-out` for entering, `ease-in` for exiting | Use `linear` for UI transitions |
| **Excessive Motion** | Animate 1-2 key elements per view | Animate everything that moves |
| **Continuous Animation** | Use only for loading indicators | Use `animate-bounce` on decorative icons |

```tsx
// ✅ CORRECT - Performant animation
<div className="transition-transform duration-200 hover:scale-105" />

// ❌ WRONG - transition-all is slow
<div className="transition-all duration-200" />  // Never use 'all'
```

---

## 🖼️ Visual Quality Rules

| Rule | Do | Don't |
|------|----|----- |
| **No Emoji Icons** | Use SVG icons (Lucide, Heroicons) | Use emojis like 🎨 🚀 as UI icons |
| **Consistent Icon Sizing** | Use fixed viewBox (24x24) with `w-6 h-6` | Mix different icon sizes randomly |
| **Stable Hover States** | Use color/opacity transitions | Scale transforms that shift layout |
| **Glass Cards (Light Mode)** | Use `bg-white/80` or higher opacity | Use `bg-white/10` (too transparent) |
| **Text Contrast (Light)** | Use `text-slate-900` for body text | Use `text-slate-400` (too light) |
| **Border Visibility** | Use `border-gray-200` in light mode | Use `border-white/10` (invisible) |

---

## 📱 Responsive & Touch

| Rule | Do | Don't |
|------|----|----- |
| **Mobile First** | Start with mobile styles, then add `md:`, `lg:` | Desktop-first causing mobile issues |
| **Touch Targets** | Minimum 44x44px touch targets | Tiny clickable areas (`w-6 h-6` buttons) |
| **Touch Spacing** | Minimum 8px gap between touch targets | Tightly packed clickable elements |
| **Readable Font Size** | Minimum 16px body text on mobile | Tiny text (`text-xs` for body) |
| **Test Breakpoints** | Test at 320, 375, 414, 768, 1024, 1440 | Only test on your device |

---

## 🎯 Pre-Generation Structure Checklist

**Before writing any code, verify the structure plan includes:**

- [ ] Proper semantic HTML hierarchy (`nav`, `main`, `section`, `article`)
- [ ] Consistent container widths across sections
- [ ] Navigation that doesn't overlap content
- [ ] All interactive elements have hover/focus/active states
- [ ] Form fields have labels and proper input types
- [ ] Touch targets are at least 44x44px on mobile
- [ ] Z-index uses defined scale (10, 20, 30, 50)
- [ ] No horizontal scroll on any viewport

---

**📖 Reference**: These rules are derived from [web-design-guidelines](../web-design-guidelines/SKILL.md) best practices.
