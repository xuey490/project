# Animation Patterns - 动画模式参考

## ⛔⛔⛔ Animation Components (CRITICAL — Zero Tolerance)

> **🚨 此规则被反复违反！页面切换有动画但页面内容没有动画 = 任务失败！**

模板预置了三个动画组件，**必须全部使用**：

| 层级 | Component | Import | 用途 |
|------|-----------|--------|------|
| **App 层** | `AnimatedRoutes` | `@/components/AnimatedRoutes` | 包裹 `<Routes>`，使用 `mode="popLayout"` 实现流畅页面切换 |
| **Route 层** | `PageTransition` | `@/components/PageTransition` | 包裹每个页面组件，提供进入/退出动画（0.2s） |
| **页面内** | `MotionPrimitives` | `@/components/MotionPrimitives` | 页面内部组件动画：`FadeIn`, `Stagger`, `HoverLift` |

**⛔ 三层动画缺一不可！只用前两层 = 违反规则！**

### AnimatePresence Mode: popLayout

使用 `mode="popLayout"` 而非 `"wait"`：
- **"wait"** 会等待退出动画完成才开始进入动画（总延迟 ~0.6s，体验卡顿）
- **"popLayout"** 允许新页面立即进入，旧页面同时退出（更流畅，~0.2s）

### ⛔ Navbar/Header/Sidebar 布局规则 (CRITICAL)

**Navbar / Header / Sidebar / 任何共享布局组件必须放在 `<AnimatedRoutes>` 外部**，否则每次页面切换都会：
- 销毁并重新创建这些组件
- 让这些组件参与页面过渡动画（抖动、闪烁、延迟）

```tsx
// ✅ CORRECT — Navbar outside AnimatedRoutes
<BrowserRouter>
  <Navbar />  {/* ✅ 在外部 */}
  <AnimatedRoutes>
    <Route path="/" element={<PageTransition><Home /></PageTransition>} />
  </AnimatedRoutes>
</BrowserRouter>

// ❌ WRONG — Navbar inside AnimatedRoutes
<AnimatedRoutes>
  <Route path="/" element={<><Navbar /><Home /></>} />
</AnimatedRoutes>
```

### MotionPrimitives API Reference

**Components (use these in JSX):**

| Component | Props | Usage |
|-----------|-------|-------|
| `FadeIn` | `variants?`, `delay?`, `duration?`, `once?`, `amount?` | Wrap any content for viewport-triggered entrance animation |
| `Stagger` | `stagger?=0.1`, `delay?`, `once?`, `amount?` | Wrap lists/grids for staggered children animation |
| `HoverLift` | `lift?=-4` | Wrap cards/buttons for hover lift effect |

**Variant Presets (for custom animations):**

| Variant | Effect |
|---------|--------|
| `fadeUp` | Opacity 0→1 + Y 32→0 (default for `FadeIn`) |
| `fadeDown` | Opacity 0→1 + Y -24→0 |
| `fadeIn` | Opacity only 0→1 |
| `fadeLeft` | Opacity + X -32→0 |
| `fadeRight` | Opacity + X 32→0 |
| `scaleUp` | Opacity + scale 0.92→1 |
| `blurIn` | Opacity + blur 12px→0 |

### ⛔ 页面内 MotionPrimitives 强制使用规则

| 页面元素 | 必须使用 | 示例 |
|----------|----------|------|
| Hero/标题区域 | `<FadeIn>` | `<FadeIn><h1>...</h1></FadeIn>` |
| 列表/网格 | `<Stagger>` + `variants` | `<Stagger>{items.map(i => <motion.div variants={fadeUp}>...)}</Stagger>` |
| 卡片/可交互元素 | `<HoverLift>` | `<HoverLift><Card>...</Card></HoverLift>` |
| 内容块/段落 | `<FadeIn>` | `<FadeIn><p>...</p></FadeIn>` |
| CTA 按钮 | `<FadeIn delay={0.2}>` | `<FadeIn delay={0.2}><Button>...</Button></FadeIn>` |

### ✅ Complete Page Example (MUST follow)

```tsx
import { FadeIn, Stagger, HoverLift, fadeUp, motion } from "@/components/MotionPrimitives";
import { Card, CardHeader, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

function Home() {
  const features = [
    { id: 1, title: "Feature 1", desc: "Description" },
    { id: 2, title: "Feature 2", desc: "Description" },
    { id: 3, title: "Feature 3", desc: "Description" },
  ];
  
  return (
    <main className="container mx-auto px-4 py-16">
      {/* ✅ Hero 区域用 FadeIn */}
      <FadeIn className="text-center mb-16">
        <h1 className="text-4xl font-bold mb-4">Welcome</h1>
        <p className="text-xl text-muted-foreground mb-8">Description</p>
        <FadeIn delay={0.2}>
          <Button size="lg">Get Started</Button>
        </FadeIn>
      </FadeIn>
      
      {/* ✅ 列表用 Stagger + HoverLift */}
      <Stagger className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {features.map((feature) => (
          <motion.div key={feature.id} variants={fadeUp}>
            <HoverLift>
              <Card>
                <CardHeader>{feature.title}</CardHeader>
                <CardContent>{feature.desc}</CardContent>
              </Card>
            </HoverLift>
          </motion.div>
        ))}
      </Stagger>
      
      {/* ✅ 其他内容块用 FadeIn */}
      <FadeIn className="mt-16 text-center">
        <h2 className="text-2xl font-semibold">Ready?</h2>
        <Button variant="outline">Contact Us</Button>
      </FadeIn>
    </main>
  );
}
```

### ❌ WRONG Example (违反规则 - 任务失败)

```tsx
// ❌ 没有使用 MotionPrimitives，页面内容完全静态
function Home() {
  return (
    <main>
      <div className="text-center">  {/* ❌ 应该用 <FadeIn> */}
        <h1>Welcome</h1>
        <Button>Get Started</Button>  {/* ❌ 没有入场动画 */}
      </div>
      
      <div className="grid grid-cols-3 gap-6">  {/* ❌ 应该用 <Stagger> */}
        {features.map((f) => (
          <Card key={f.id}>  {/* ❌ 没有 variants，没有 HoverLift */}
            {f.title}
          </Card>
        ))}
      </div>
    </main>
  );
}
```

### ⛔ Animation Rules (CRITICAL)

1. **三层动画缺一不可**：`AnimatedRoutes` + `PageTransition` + `MotionPrimitives`
2. **页面内必须使用 MotionPrimitives**：Hero 用 `<FadeIn>`，列表用 `<Stagger>`，卡片用 `<HoverLift>`
3. **Stagger 的子元素必须有 variants**：`<motion.div variants={fadeUp}>`
4. **NEVER create static components** — 没有动画 = 任务失败
5. **Navbar MUST be outside AnimatedRoutes**

### ⛔ 生成页面前必须自查

1. ✅ 是否导入了 `FadeIn`, `Stagger`, `HoverLift`, `fadeUp`, `motion`?
2. ✅ Hero/标题是否用 `<FadeIn>` 包裹?
3. ✅ 列表/网格是否用 `<Stagger>` 包裹?
4. ✅ Stagger 子元素是否有 `<motion.div variants={fadeUp}>`?
5. ✅ 卡片是否用 `<HoverLift>` 包裹?
6. **❌ 没有动画 = 违反规则，必须重写！**

---

## Animation Timing Reference

| 动画类型 | 时长 | Easing | 说明 |
|---------|------|--------|------|
| 页面切换 | 0.3-0.4s | ease-out | 进入时减速 |
| Modal 打开 | 0.2-0.3s | ease-out | 背景淡入 + 内容上滑 |
| Modal 关闭 | 0.15-0.2s | ease-in | 比打开更快 |
| Toast | 0.3s | ease-out | 滑入效果 |
| Hover 效果 | 0.15-0.2s | ease | 快速响应 |
| Focus 状态 | 0.2s | ease | 边框/阴影变化 |
| Dropdown | 0.2s | ease | 淡入 + 下移 |
| Loading | 0.6-1s | linear | 无限循环 |

---

## Keyframes Summary

| 动画名 | 效果 | 适用场景 |
|-------|------|---------|
| `fadeIn` | 透明度 0→1 | 页面切换、通用淡入 |
| `fadeInUp` | 透明度 + Y轴上移 | 元素入场 |
| `fadeInDown` | 透明度 + Y轴下移 | 下拉菜单 |
| `slideUp` | Y轴上移 | Modal 内容 |
| `slideIn` | X轴滑入 | Toast、侧边栏 |
| `spin` | 360° 旋转 | Loading |
| `skeleton` | 背景位移 | 骨架屏 |
| `underlineGrow` | X轴缩放 | 导航指示器 |

---

## Core Animation Patterns

### 1. 页面过渡 (Fade In Up)

```css
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.page-enter { animation: fadeInUp 0.4s ease-out; }
```

### 2. Modal 动画

```css
/* 背景淡入 */
@keyframes modalFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* 内容上滑 */
@keyframes modalSlideUp {
  from { transform: translateY(50px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.modal.active { animation: modalFadeIn 0.2s ease-out; }
.modal-content { animation: modalSlideUp 0.3s ease-out; }
```

### 3. Toast 滑入

```css
@keyframes toastSlideIn {
  from { transform: translateX(400px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

.toast.active { animation: toastSlideIn 0.3s ease-out; }
```

### 4. Card Hover

```css
.card {
  /* ✅ 指定具体属性，避免 transition: all */
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-4px);  /* 基础: -4px, Premium: -8px */
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
```

### 5. Button States

```css
.btn {
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn:active {
  transform: translateY(0);
}
```

### 6. Loading Spinner

```css
@keyframes spin {
  to { transform: rotate(360deg); }
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
```

### 7. Skeleton Loading

```css
@keyframes skeleton-loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
}
```

### 8. Navigation Indicator

```css
@keyframes underlineGrow {
  from { transform: scaleX(0); }
  to { transform: scaleX(1); }
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--brand-primary);
  animation: underlineGrow 0.2s ease-out;
}
```

### 9. Input Focus

```css
.input {
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.input:focus {
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
```

### 10. Dropdown Menu

```css
.dropdown-menu {
  opacity: 0;
  visibility: hidden;
  transform: translateY(10px);
  transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease;
}

.dropdown:hover .dropdown-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}
```

---

## Performance Rules

### ✅ 正确做法

```css
/* 只动画 transform 和 opacity (GPU-friendly) */
.good {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

/* will-change 提示浏览器 */
.animated {
  will-change: transform, opacity;
}
```

### ❌ 禁止的做法

```css
/* 禁止 transition: all */
.bad {
  transition: all 0.3s;  /* ❌ 性能问题 */
}

/* 禁止动画 layout 属性 */
.bad {
  transition: width 0.3s, height 0.3s;  /* ❌ 触发 reflow */
}
```

---

## Reduced Motion Support

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Tailwind 写法**:
```tsx
<div className="motion-safe:animate-bounce motion-reduce:animate-none">
```

---

## Quick Copy Reference

| 效果 | Tailwind 类 |
|------|------------|
| Hover 上移 | `hover:-translate-y-1` |
| Hover 缩放 | `hover:scale-105` |
| 过渡效果 | `transition-transform duration-300` |
| 入场动画 | `animate-fadeIn` (需自定义) |
| Loading | `animate-spin` |
