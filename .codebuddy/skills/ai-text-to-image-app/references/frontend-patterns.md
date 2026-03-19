# Frontend Implementation Patterns

## Import Management (CRITICAL)

### Import Checklist

Before completing any file, verify:
- [ ] All components used in JSX have corresponding imports
- [ ] All types/interfaces have `import type` statements
- [ ] All utility functions are imported (cn, toast, useNavigate)
- [ ] All icon components from `lucide-react` are imported
- [ ] All hooks are imported (useState, useEffect, custom hooks)

### Common Import Mistakes

```tsx
// ❌ WRONG - Using component without importing
function MyPage() {
  return <ProductCard product={data} />;
}

// ✅ CORRECT
import { ProductCard } from '@/components/product/ProductCard';

function MyPage() {
  return <ProductCard product={data} />;
}
```

### Type Import Rules

```tsx
// ✅ CORRECT - Use 'type' keyword for type-only imports
import type { Product, Review } from "../types/product";
```

## React Router Compatibility

This project uses `BrowserRouter`, NOT the Data Router API.

### Compatible Features
- ✅ `useLocation`, `useNavigate`, `useParams`, `useSearchParams`
- ✅ `<Link>`, `<Routes>`, `<Route>`, `<Navigate>`
- ✅ Custom scroll-to-top implementation

### NOT Compatible (DO NOT USE)
- ❌ `ScrollRestoration` - Data Router only
- ❌ `useMatches` - Data Router only
- ❌ `useLoaderData`, `useActionData` - Data Router only
- ❌ `loader`, `action` props - Data Router only

### Scroll-to-Top Pattern

```tsx
import { useLocation } from 'react-router-dom';
import { useEffect } from 'react';

function ScrollToTop() {
  const { pathname } = useLocation();
  
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);
  
  return null;
}

// Use in App.tsx:
<BrowserRouter>
  <ScrollToTop />
  <Routes>{/* routes */}</Routes>
</BrowserRouter>
```

### Router Parameters vs. JSON API Protocol

**Router Parameters (for routing only):**
```tsx
const { id } = useParams();                // /users/:id → /users/123
const [searchParams] = useSearchParams();  // for UI state only
navigate('/users/123');                    // navigation
```

**JSON API Protocol (for data operations):**
```tsx
const response = await apiClient.post('/api/users/list', {
  page: 1,
  limit: 10,
  filters: { category: 'tech' }
});
```

## Route Configuration (CRITICAL — framer-motion AnimatePresence Required)

Routes are defined in `App.tsx`. **Every `<Routes>` block MUST be wrapped with the pre-built `<AnimatedRoutes>` component** for smooth page-switch animations. There is NO exception — never render `<Routes>` without it.

**How to add a new route:**
1. Create page in `src/pages/PageName.tsx`
2. Import at top of `src/App.tsx`
3. Add route BEFORE catch-all `*` route
4. Wrap the page component with `<PageTransition>` using the appropriate transition mode

### Pre-built Animation Components (already in template — DO NOT recreate)

The template includes three animation components ready to use:

| Component | Import | Purpose |
|-----------|--------|---------|
| `AnimatedRoutes` | `@/components/AnimatedRoutes` | Wraps `<Routes>` with `<AnimatePresence mode="wait">` + `key={location.pathname}` |
| `PageTransition` | `@/components/PageTransition` | Wraps page content with `motion.div` (modes: `fade` / `slide-up` / `slide-fade` / `scale`) |
| `MotionPrimitives` | `@/components/MotionPrimitives` | In-page animation building blocks: `FadeIn`, `Stagger`, `HoverLift`, variant presets |

### Required App.tsx Structure (the ONLY correct pattern)

**CRITICAL layout rules:**
- **Navbar / shared layout MUST be OUTSIDE `<AnimatePresence>`** — so it never participates in page-switch animations.
- **`<AnimatedRoutes>` internally uses `useLocation()` + `key={location.pathname}`** to let `AnimatePresence` detect route changes and play exit/enter animations.
- **Each page component MUST be wrapped with `<PageTransition>`** so that framer-motion can animate it.
- **Each page component must NOT contain `<Navbar />`** — Navbar lives only in App.tsx, above AnimatedRoutes.

```tsx
import { AnimatedRoutes } from "@/components/AnimatedRoutes";
import { PageTransition } from "@/components/PageTransition";

function App() {
  return (
    <BrowserRouter>
      <ScrollToTop />
      <Navbar />
      <AnimatedRoutes>
        <Route path="/" element={<PageTransition transition="slide-up"><Index /></PageTransition>} />
        <Route path="/about" element={<PageTransition transition="slide-up"><About /></PageTransition>} />
        {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
        <Route path="*" element={<PageTransition transition="fade"><NotFound /></PageTransition>} />
      </AnimatedRoutes>
    </BrowserRouter>
  );
}
```

**Available transition modes:** `fade` (default) | `slide-up` | `slide-fade` | `scale`

**⚠️ WRONG — never do these:**
```tsx
// ❌ WRONG — Navbar inside AnimatedRoutes (flickers on page switch)
<AnimatedRoutes>
  <Route path="/" element={<><Navbar /><Index /></>} />
</AnimatedRoutes>

// ❌ WRONG — Navbar inside each page component (re-mounts on switch)
function Dashboard() {
  return (<><Navbar /><main>...</main></>);
}

// ❌ WRONG — Missing PageTransition wrapper (no animation)
<AnimatedRoutes>
  <Route path="/" element={<Index />} />
</AnimatedRoutes>

// ❌ WRONG — Routes without AnimatedRoutes / AnimatePresence
<BrowserRouter>
  <Routes>
    <Route path="/" element={<Index />} />
  </Routes>
</BrowserRouter>
```

### Component Animation Patterns (framer-motion)

All page components should use framer-motion to add micro-animations for a polished feel. Below are reusable patterns.

#### MotionPrimitives (PREFERRED — use these first)

The project ships with `@/components/MotionPrimitives` — a set of **ready-made animation wrappers** so you don't need to write raw `motion.div` + variants every time.

**Always prefer MotionPrimitives over raw `motion.div` for common patterns:**

```tsx
import {
  FadeIn,           // viewport-triggered fade wrapper (default: fadeUp)
  Stagger,          // stagger-children container
  HoverLift,        // card with hover-lift effect
  fadeUp, fadeDown, fadeIn, fadeLeft, fadeRight, scaleUp, blurIn,  // variant presets
  motion,           // re-exported for advanced use
  springBounce,     // spring config token
} from "@/components/MotionPrimitives";
```

**Scroll-triggered fade-in section:**
```tsx
<FadeIn>
  <h2 className="text-3xl font-bold">Features</h2>
  <p>Content fades up into view when scrolled into viewport.</p>
</FadeIn>

{/* Use a different variant */}
<FadeIn variants={fadeLeft} delay={0.2}>
  <SidePanel />
</FadeIn>
```

**Staggered grid / list:**
```tsx
<Stagger className="grid grid-cols-3 gap-6" stagger={0.08}>
  {items.map((item) => (
    <FadeIn key={item.id}>
      <Card item={item} />
    </FadeIn>
  ))}
</Stagger>
```

**Hover-lift card:**
```tsx
<Stagger className="grid grid-cols-3 gap-6">
  {features.map((f) => (
    <HoverLift key={f.id}>
      <FeatureCard feature={f} />
    </HoverLift>
  ))}
</Stagger>
```

#### Available Variant Presets

| Variant | Effect |
|---------|--------|
| `fadeUp` (default) | Fade + slide up 32px |
| `fadeDown` | Fade + slide down 24px |
| `fadeIn` | Fade only |
| `fadeLeft` | Fade + slide from left 32px |
| `fadeRight` | Fade + slide from right 32px |
| `scaleUp` | Fade + scale from 0.92 |
| `blurIn` | Fade + blur(12px) to clear |

#### Raw motion.div (for advanced / custom needs only)

When MotionPrimitives don't cover your use case, fall back to raw `motion.div`:

## WebSocket Connection

```tsx
// ✅ CORRECT - Dynamic protocol and host, /api prefix required
const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:'
const wsUrl = `${protocol}//${window.location.host}/api/ws`
const socket = new WebSocket(wsUrl)

// ❌ WRONG
const socket = new WebSocket('ws://localhost:3000/ws')  // hardcoded
const socket = new WebSocket(`${protocol}//host/ws`)   // missing /api
```

## Forms

```tsx
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Form, FormField } from "@/components/ui/form";

const schema = z.object({ email: z.string().email() });
const form = useForm({ resolver: zodResolver(schema) });
```

## Pre-Completion Verification

Before marking any file complete:

1. **Imports**: Every component/function has import, no undefined references
2. **Router**: Only BrowserRouter-compatible features used
3. **Syntax**: No errors, all JSX tags closed, correct function arguments
4. **Types**: `import type` for interfaces, props interfaces defined
