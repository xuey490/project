---
name: web-implement
description: Implement web application with best practices, start's with "web" template, built with a fixed technology stack React, express, tailwindcss, typescript, postgreSQL, and DOES NOT support framework switching. This skill will init codebase and provide useful template information.
_meta_type: template
_meta_template_name: web
---

## Prerequisites

1. Initialize Project (if needed):
```bash
REPO_ROOT/.genie/scripts/bash/setup-project.sh web
```
2. **Requirement Analysis using UX Five Planes** — Think like a product designer, not a form filler. Analyze the user's request through the five planes of user experience, and output a complete product document to `docs/product/features.md`.

**CRITICAL: Each plane MUST be a narrative derivation, NOT a bullet-point checklist.** Write as if you're a senior product designer explaining your reasoning to the team. Each layer's conclusion must naturally flow into the next layer's decisions.

### Plane 1: Strategy (战略层) —— 骨子里是什么？

Don't just list "business goals". Instead, **get inside the user's head**:
- **Who exactly are these users?** Paint a vivid picture — what frustrates them about existing solutions? What would make them say "finally, someone gets it"?
- **What's the core emotional need?** (e.g., "control over chaos", "look professional effortlessly", "feel like a hacker")
- **What does success look like?** Pick ONE primary metric — DAU, conversion rate, time-on-site, etc.
- **Core tone in 3-4 words** that will cascade through every subsequent decision (e.g., "real, geeky, restrained, hardcore" or "calm, breathe, control, reward")

### Plane 2: Scope (范围层) —— 需要放什么料？

Based on the Strategy tone, **make bold cuts**. Use the "NOT / MUST" framework:
- **NOT (大胆砍掉):** Name specific features/content you're intentionally excluding and WHY. (e.g., "No lengthy company vision section — our users hate marketing fluff", "No complex Gantt charts — that's scope creep for a zen todo app")
- **MUST (必须要有):** List only the components that directly serve the Strategy. Describe each one concretely with its interaction behavior, not just a feature name. (e.g., "A live IDE demo with real typing animation — not a screenshot, not a video", "A global Quick Add input that's always one keystroke away")

### Plane 3: Structure (结构层) —— 故事怎么讲？

Don't just list routes. **Design the narrative flow** — how does the user's attention move?
- **Page architecture:** Single-page scroll? Multi-page app? Dashboard with sidebar? Derive this from Strategy tone.
- **Attention flow:** Describe the user's eye journey step by step (e.g., "Hero wow → Feature intrigue → Social proof trust → Pricing action" or "Input focus → Type → Enter → Task drops into list → Checkbox → Task flies to completed section")
- **Information hierarchy:** What's above the fold? What earns its scroll depth?
- **Data model & API:** Entity names, key fields, relationships, and endpoints — keep this section structured (table format is fine here).

### Plane 4: Skeleton (框架层) —— 骨架怎么搭？

Based on Structure's narrative flow, **describe concrete layouts** with spatial language:
- **Navigation form:** Sticky topbar? Side drawer? Bottom tabs? Tab bar? WHY this choice for THIS product.
- **Key page layouts:** Use spatial descriptions (e.g., "Left: giant two-line headline. Right: fullscreen dark code editor widget", "Extreme single-column, centered, massive whitespace — every task row wide enough to breathe")
- **Signature layout pattern:** Name the pattern (e.g., "Bento Box grid for feature showcase — high density, engineer-approved", "Card stack with generous padding — feels unhurried")
- **Component inventory table:** List key UI components, their purpose, and which pages use them.

### Plane 5: Surface (表现层) —— 穿什么衣服？

This is where all 4 previous layers converge into a visual conclusion. **The style should feel inevitable, not arbitrary:**
- **Color scheme:** Derive from Strategy tone + Skeleton layout. (e.g., "Programmers live in dark IDEs → must be Dark Mode. Background: deep carbon gray. Accent: syntax-highlight electric purple", "Stress reduction → must be Light Mode. Background: not pure white — pearl/warm gray like premium paper")
- **Typography:** Which fonts and WHY. (e.g., "Headlines in Inter/Geist — clean engineering feel. Code blocks in monospace — non-negotiable", "Rounded sans-serif for warmth — sharp geometric fonts would feel corporate")
- **Signature visual detail:** The ONE micro-interaction or visual element that defines the product's personality. (e.g., "Checkbox completion: text fades to gray with strikethrough + subtle checkmark animation", "Copy-to-clipboard button on install command with satisfying click feedback")
- **Final style verdict in one line** (e.g., "Geek minimalism / Cyber-clean" or "Breathing whitespace / Paper-tactile")

Write the **Design System Query** derived from Surface conclusions:
```
Design System Query: "<product_type> <industry> <brand_personality_keywords> <color_direction> <visual_style>"
```

3. **Generate Design System** — **CRITICAL: You MUST use the Design System Query from the Surface plane as the search query. DO NOT use the user's raw request or a generic product name like "personal blog" or "todo app".**
```bash
# ❌ WRONG — generic product name leads to wrong colors (always matches teal/green):
python3 .codebuddy/skills/ui-ux-pro-max/scripts/search.py "personal blog" --design-system -p "My Blog"

# ✅ CORRECT — use the Surface plane Design System Query:
python3 .codebuddy/skills/ui-ux-pro-max/scripts/search.py "blog content elegant warm serif minimalism orange" --design-system -p "My Blog"
```
The Surface plane analysis ensures the color palette, typography, and style are **driven by the product's actual identity** rather than a generic BM25 text match that always returns teal/green.

4. Use SKILL `text-to-image` for generating images, icons, or illustrations when needed.
5. Use the SKILL `*-integrator` to fully realize the user's needs.
6. **Keep `docs/product/features.md` updated** as requirements evolve during development.

## Best Practices

### General Principles

- **Perfect Architecture**: Refactor when needed, eliminate duplication, maintain clean separation
- **Less is More**: Quality over quantity unless enterprise landing page requested
- **Leverage Existing Dependencies**: Prefer existing libraries over new ones
- **Animations Bring Life**: Add appropriate animations to make the UI feel polished and alive. Use the design system's `--duration-*` and `--ease-*` tokens. Common patterns:
  - **Scroll reveal**: Fade-in / slide-up elements as they enter viewport (Intersection Observer + CSS class)
  - **Staggered entrance**: Lists and grids animate items sequentially with incremental delay
  - **Hero entrance**: Title, subtitle, and CTA animate in with staggered timing on page load
  - **Hover & active states**: Cards lift with shadow, buttons scale subtly
  - **Loading transitions**: Skeleton shimmer → content crossfade
  - **Number count-up**: Animate statistics/KPIs from 0 to target value
  - Always include `@media (prefers-reduced-motion: reduce)` fallback

### SEO (Automatic for All Pages)

- Title tags under 60 chars with keyword
- Meta description max 160 chars
- Single H1 matching page intent
- Semantic HTML (`<header>`, `<nav>`, `<main>`, `<footer>`, `<article>`, `<section>`)
- Image alt attributes with keywords
- JSON-LD for products/articles/FAQs
- Lazy loading for images
- Canonical tags

## Web Template Architecture

```
/
├── backend/                        # Express.js + TypeScript + PostgreSQL
│   ├── package.json
│   ├── tsconfig.json
│   ├── jest.config.js
│   ├── .env.example / .env
│   ├── prisma/
│   │   ├── schema.prisma           # Database schema
│   │   └── migrations/
│   └── src/
│       ├── index.ts                # Server entry point
│       ├── app.ts                  # Express configuration
│       ├── config/
│       │   ├── env.ts              # Environment validation (Zod)
│       │   ├── database.ts         # Prisma client singleton
│       │   └── logger.ts           # Pino logger factory
│       ├── modules/                # Feature modules (routes + handlers)
│       │   └── [resource].ts
│       ├── types/
│       │   └── [resource].types.ts # Zod schemas
│       ├── middleware/
│       │   ├── error-handler.ts
│       │   ├── validation.ts
│       │   └── logger.ts
│       └── __tests__/
│
└── frontend/                       # React 19 + Vite
    ├── package.json
    ├── vite.config.ts
    ├── components.json             # shadcn/ui config
    ├── index.html
    └── src/
        ├── main.tsx                # Entry point
        ├── App.tsx                 # Root component with providers
        ├── index.css               # Global styles + Tailwind theme
        ├── assets/                 # Static assets
        ├── components/
        │   ├── AnimatedRoutes.tsx   # Pre-built: AnimatePresence route wrapper
        │   ├── PageTransition.tsx   # Pre-built: page enter/exit animation
        │   ├── MotionPrimitives.tsx # Pre-built: FadeIn, Stagger, HoverLift, variants
        │   ├── [domain]/           # Domain-specific components
        │   └── ui/                 # shadcn/ui (53 components)
        ├── hooks/
        │   ├── use-mobile.ts
        │   └── use-[resource].ts
        ├── lib/
        │   ├── api-client.ts       # Axios with interceptors
        │   └── utils.ts            # cn utility
        ├── pages/
        │   ├── Index.tsx
        │   ├── NotFound.tsx
        │   └── [Page].tsx
        └── types/
```


## Technical References

Detailed implementation patterns in `references/`:

- **[api-protocol.md](references/api-protocol.md)**: JSON-based API standard (CRITICAL - no URL parameters for data operations)
- **[frontend-patterns.md](references/frontend-patterns.md)**: Import management, React Router compatibility, WebSocket
- **[architecture.md](references/architecture.md)**: Complete monorepo structure, key files, commands
- **[design-system.md](references/design-system.md)**: CSS variables, design tokens, theming guide

## Design System

The project uses a CSS variable-based design system in `frontend/src/index.css`. A **design system editor** lets users adjust these variables at runtime — any value you hardcode will be invisible to the editor.

| Category | Variables |
|----------|-----------|
| **Colors** | primary, secondary, muted, accent, destructive, success, warning, info |
| **Typography** | font-family, font-size-*, font-weight-*, line-height |
| **Spacing** | spacing-xs through spacing-3xl |
| **Shadows** | shadow-sm, shadow-md, shadow-lg, shadow-xl |
| **Radius** | radius-sm through radius-xl |
| **Animation** | duration-*, ease-*, framer-motion page transitions (4 modes: fade / slide-up / slide-fade / scale) |


### Workflow: Adjust Variables First, Then Code

When implementing a new page or feature:
1. **Read the design requirements** — identify what visual style is needed
2. **Check `index.css`** — review existing CSS variable values to see if they already match
3. **Adjust CSS variables if needed** — modify `:root` values in `index.css` to match the design intent (e.g., change `--primary` color, increase `--spacing-md`)
4. **Write page/component code** — use CSS variables and Tailwind classes mapped to variables, never hardcode

This "variables first" approach ensures the design system editor can fine-tune the visual result after code is written.

### Variable Categories

| Category | Variables | How to use |
|----------|-----------|------------|
| **Colors** | primary, secondary, muted, accent, destructive, success, warning, info | Tailwind classes: `bg-primary`, `text-muted-foreground` |
| **Font sizes** | font-size-body, font-size-label, font-size-small | `text-base`, `text-sm`, `text-xs` (bridged via `@theme`) |
| **Font sizes (large)** | font-size-display, font-size-headline, font-size-title | `style={{ fontSize: 'var(--font-size-headline)' }}` |
| **Spacing** | spacing-xs through spacing-3xl | `style={{ padding: 'var(--spacing-md)', gap: 'var(--spacing-sm)' }}` |
| **Shadows** | ds-shadow-sm, ds-shadow-md, ds-shadow-lg, ds-shadow-xl | Tailwind classes: `shadow-sm`, `shadow-md` (bridged via `@theme`) |
| **Radius** | radius-sm through radius-xl | Tailwind classes: `rounded-sm`, `rounded-lg` (bridged via `@theme`) |
| **Animation** | duration-*, ease-* | `style={{ transition: 'var(--duration-normal) var(--ease-default)' }}` |

### Two-Layer Strategy: shadcn/ui vs Business Code

| Layer | Scope | Rule |
|-------|-------|------|
| **shadcn/ui components** | `components/ui/` | Keep original Tailwind classes. `@theme` bridge handles `text-xs/sm/base`, `shadow-*`, `rounded-*` automatically. Do NOT modify these files. |
| **Business code** | `pages/`, `components/[domain]/`, `hooks/`, `App.tsx` | **Must use CSS variables** for colors, font sizes, spacing, shadows. This is the code the editor needs to control. |

See [references/design-system.md](references/design-system.md) for complete variable list, usage examples, and hardcoded-to-variable mapping table.

### Page Transition & Component Animations (CRITICAL)

The template **already includes** `framer-motion` and three pre-built animation components. **DO NOT recreate them — import and use directly:**

| Component | Path | Purpose |
|-----------|------|---------|
| `AnimatedRoutes` | `@/components/AnimatedRoutes` | Wraps `<Routes>` with `<AnimatePresence mode="popLayout">` (新旧页面同时过渡，更流畅) |
| `PageTransition` | `@/components/PageTransition` | Page-level enter/exit animation (4 modes, 0.2s duration) |
| `MotionPrimitives` | `@/components/MotionPrimitives` | In-page animation building blocks (`FadeIn`, `Stagger`, `HoverLift`, variant presets) |

**Setup checklist:**
1. Update `--duration-*` and `--ease-*` variables in `:root` if the design system spec provides different values
2. In `App.tsx`: Place **Navbar / Header / Sidebar / 任何共享布局组件 OUTSIDE `<AnimatedRoutes>`**，wrap each route's `element` with `<PageTransition>`. Choose the transition mode based on the design system style:
   - **Glassmorphism / Spatial** → `fade` or `scale`
   - **Neumorphism / Claymorphism** → `scale`
   - **Parallax / Storytelling / Blog** → `slide-up`
   - **Motion-Driven / Dashboard** → `slide-fade`
   - **Default / Minimalism** → `fade`

```tsx
// ✅ CORRECT App.tsx — Navbar outside, each route wrapped with PageTransition
<BrowserRouter>
  <ScrollToTop />
  <Navbar />
  <AnimatedRoutes>
    <Route path="/" element={<PageTransition transition="slide-up"><Index /></PageTransition>} />
    <Route path="*" element={<PageTransition transition="fade"><NotFound /></PageTransition>} />
  </AnimatedRoutes>
</BrowserRouter>

// ✅ Dashboard 布局 — Sidebar + Header outside AnimatedRoutes
<BrowserRouter>
  <div className="flex min-h-screen">
    <Sidebar />
    <div className="flex-1 flex flex-col">
      <Header />
      <main className="flex-1">
        <AnimatedRoutes>
          <Route path="/dashboard" element={<PageTransition><Dashboard /></PageTransition>} />
        </AnimatedRoutes>
      </main>
    </div>
  </div>
</BrowserRouter>
```

See [references/design-system.md](references/design-system.md) § "Page Transitions" for available modes, and [references/frontend-patterns.md](references/frontend-patterns.md) for MotionPrimitives usage patterns.

## Quick Reference

### Adding New Resources

**Frontend:**
1. Define types in `src/types/[resource].ts`
2. Create hooks in `src/hooks/use-[resources].ts`
3. Build components using JSON-based API calls

**Backend:**
1. Define model in `prisma/schema.prisma`
2. Create types in `src/types/[resource].types.ts`
3. Create module in `src/modules/[resource].ts`
4. Register router in `src/app.ts`

### Key Commands

```bash
# Backend
npm run prisma:migrate -- --name init

# Debugging
python3 "$PROJECT_ROOT/.genie/scripts/python/fetch_monitor_errors.py"
```

### Critical Rules

1. **JSON Protocol**: All data operations use POST with JSON body, not URL parameters
2. **BrowserRouter Only**: No Data Router features (ScrollRestoration, useMatches, etc.)
<<<<<<< HEAD
3. **framer-motion Animation Components Pre-built**: `AnimatedRoutes`, `PageTransition`, `MotionPrimitives` are already in the template — import and use them directly. `<Routes>` MUST always be wrapped in `<AnimatedRoutes>`, each route element MUST be wrapped with `<PageTransition>`, Navbar MUST be outside AnimatedRoutes. Use `<FadeIn>`, `<Stagger>`, `<HoverLift>` from MotionPrimitives for in-page animations.
4. **Animations Required**: ALL components MUST use MotionPrimitives for animations. Wrap lists/grids with `<Stagger>`, content blocks with `<FadeIn>`, interactive elements with `<HoverLift>`. Never create static components without entrance animations.
5. **Tailwind v4 Container**: The `container` class does NOT auto-center in v4. The template `index.css` already includes `@utility container` with `margin-inline: auto` — do NOT remove it. If missing, add it.
6. **Import Everything**: Every component/function used must be imported
7. **53 shadcn/ui Components**: Use existing, DO NOT create custom versions
8. **WebSocket**: Must use `/api` prefix and dynamic protocol/host
9. **Design Tokens**: Always use CSS variables via Tailwind classes, never hardcode colors/sizes
10. **Light Mode Only**: In business code (`pages/`, `components/[domain]/`), ALL visual properties must use CSS variables — this includes:
   - **Colors**: Use Tailwind semantic classes (`bg-primary`, `text-muted-foreground`) — never `bg-gray-*`, `text-[#xxx]`
   - **Font sizes**: Use `text-base`/`text-sm`/`text-xs` (bridged to variables) or `style={{ fontSize: 'var(--font-size-*)' }}` for display/headline/title — never hardcoded `text-lg`, `text-xl`, `text-4xl`
   - **Spacing**: Use `style={{ padding: 'var(--spacing-md)', gap: 'var(--spacing-sm)' }}` — never `p-4`, `gap-6` in business components
   - **Shadows**: Use `shadow-sm`/`shadow-md`/`shadow-lg`/`shadow-xl` (bridged) — never `shadow-[...]`
   - **Radius**: Use `rounded-sm`/`rounded-md`/`rounded-lg`/`rounded-xl` (bridged) — never `rounded-[...]`
   - **Animation**: Use `var(--duration-normal)`, `var(--ease-default)` — never `duration-200`
   - Exception: `components/ui/` (shadcn/ui) keeps its original classes — the `@theme` bridge handles the mapped properties automatically
11. **oklch Color Format**: ALL color CSS variables in `index.css` MUST use `oklch()` format. NEVER use bare HSL (`220 14% 96%`), hex (`#f0f0f0`), hsl(), or rgb(). See [design-system.md](references/design-system.md) for details.

### Launch Configuration

`.cloudstudio` file defines startup - DO NOT modify. Always use unified process script.
