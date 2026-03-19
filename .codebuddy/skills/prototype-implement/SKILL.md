---
name: prototype-implement
description: Implement prototype with best practices, start's with "prototype" template, built for frontend-only prototypes WITHOUT backend/database. Use this skill for: URL cloning, screenshot-to-code, design mockups, landing pages, and UI prototypes. This skill will init codebase and provide useful template information. NOT for full-stack applications - use web-implement instead.
_meta_type: template
_meta_template_name: prototype
---

## Prerequisites

**Initialize Project Template** (MANDATORY):
```bash
REPO_ROOT/.genie/scripts/bash/setup-project.sh prototype
```

---

## ⛔ Mode 判断规则（最高优先级）

> **🚨 在做任何事之前，先判断 Mode！判断错误会导致整个流程错误！**

```
用户输入包含 URL (http/https)？ ──Yes──→ Mode 1 (URL Clone)
                                  │
                                  No
                                  ↓
用户提供了截图/图片？ ──────────Yes──→ Mode 2 (Screenshot)
                                  │
                                  No
                                  ↓
                              Mode 3 (Requirements)
```

**⛔ 关键规则**：
- **有 URL 就是 Mode 1**，不管用户怎么描述（"做原型"、"帮我设计"、"复刻"都不影响）
- URL 检测优先级 > 图片检测 > 关键词匹配

---

## Core Principles

- **WHEN** the user is greeting, asking questions, or having a conversation, **THEN**:
  - Do NOT rush into development
  - Respond with brief, helpful answers
  - Wait for clear development requests before invoking the agent
- **Foundation First, Then Premium** (分层设计理念):
  1. 🏗️ **Foundation Layer**: Structure must be correct - semantic HTML, proper layout, accessibility, interaction states
  2. 🎨 **Premium Layer**: Then pursue design excellence - distinctive aesthetics, atmospheric effects, premium polish
  - ⚠️ Never sacrifice structural correctness for visual effects
- **Design Excellence**: Leverage SKILL `frontend-design` for creative, distinctive interfaces that avoid generic AI aesthetics
- **Code Quality** (Optional): Use SKILL `web-design-guidelines` to audit generated code for accessibility, performance, best practices
- **⛔ NO UNICODE EMOJI**: NEVER use Unicode emoji in generated code. This is a HARD RULE with ZERO tolerance.
- **Parallel Tool Calls**: Execute independent operations simultaneously
- **Less is More**: Quality over quantity, focus on pixel-perfect accuracy
- **Multi-Page First**: Proactively break down into multiple pages and ensure proper navigation

📖 **Detailed UI/UX Rules**: [uiux-foundation.md](references/uiux-foundation.md)

---

## ⛔ Hard Rules - Zero Tolerance

### ⛔⛔⛔ Tailwind CSS v4 Compatibility (CRITICAL)

> **🚨 模板使用 Tailwind v4，语法与 v3 有重大差异！**
>
> **使用 v3 语法会导致样式完全不生效！**

**⛔ v4 废弃/重命名的类名**：

| ❌ v3 (不要用) | ✅ v4 替代 | 说明 |
|--------------|----------|------|
| `flex-grow` | `grow` | 重命名 |
| `flex-shrink` | `shrink` | 重命名 |
| `overflow-ellipsis` | `text-ellipsis` | 重命名 |
| `shadow-sm` | `shadow-xs` | 阴影缩放调整 |
| `rounded-sm` | `rounded-xs` | 圆角缩放调整 |
| `blur-sm` | `blur-xs` | 模糊缩放调整 |
| `outline-none` | `outline-hidden` | 语义更清晰 |
| `bg-opacity-50` | `bg-black/50` | 使用颜色修饰符 |
| `text-opacity-50` | `text-black/50` | 使用颜色修饰符 |

**⛔ v4 默认值变化 (常见坑)**：

| 类名 | v3 默认 | v4 默认 | 迁移方案 |
|-----|--------|--------|---------|
| `ring` | 3px, blue-500 | 1px, currentColor | 用 `ring-3 ring-blue-500` |
| `border` | gray-200 | currentColor | 显式指定 `border-gray-200` |
| `outline` | 无宽度 | 1px | 无需再加 `outline-1` |

**⛔ v4 语法变化**：

```tsx
// ❌ Important 修饰符位置 (v3)
<div className="!flex !bg-red-500">
// ✅ v4: 感叹号放后面
<div className="flex! bg-red-500!">

// ❌ CSS 变量引用 (v3)
<div className="bg-[--brand-color]">
// ✅ v4: 用圆括号
<div className="bg-(--brand-color)">

// ❌ Grid 逗号分隔 (v3)
<div className="grid-cols-[max-content,auto]">
// ✅ v4: 用下划线
<div className="grid-cols-[max-content_auto]">
```

**生成代码前检查**：
1. 不使用已废弃/重命名的类名
2. `ring`/`border` 显式指定颜色和宽度
3. Important 修饰符放类名后面 (`flex!` 不是 `!flex`)

### ⛔⛔⛔ Web Content Extraction (Mode 1 CRITICAL)

> **必须使用 `web_content_fetcher.py`，禁止使用 WebFetch 工具！**

```bash
# ✅ CORRECT
python3 scripts/web_content_fetcher.py <url>

# ❌ FORBIDDEN - WebFetch 工具无法处理 SPA/动态页面
```

### ⛔⛔⛔ Unicode Emoji Prohibition (CRITICAL - 零容忍)

> **🚨 这是硬性规则，违反即为严重错误！此规则已被反复违反，必须特别注意！**
>
> **生成的代码中严禁出现任何 Unicode emoji 字符（如 🚀 ✨ 📧 🎨 等）**
>
> **违反此规则 = 任务失败，必须重新生成！**

**Unicode emoji 会降低设计的专业感和高端感，必须使用 lucide-react 图标或 SVG 替代：**

| ❌ FORBIDDEN (严禁) | ✅ CORRECT Replacement |
|---------------------|----------------------|
| 🚀 | `<Rocket />` from lucide-react |
| ✨ | `<Sparkles />` from lucide-react |
| 📧 | `<Mail />` from lucide-react |
| ✅ | `<Check />` or `<CheckCircle />` from lucide-react |
| 🎨 | `<Palette />` from lucide-react |
| 📱 | `<Smartphone />` from lucide-react |
| 💡 | `<Lightbulb />` from lucide-react |
| ⭐ | `<Star />` from lucide-react |
| 🔒 | `<Lock />` from lucide-react |
| 📊 | `<BarChart />` from lucide-react |
| 🎯 | `<Target />` from lucide-react |
| 🔥 | `<Flame />` from lucide-react |
| ❤️ | `<Heart />` from lucide-react |
| 👍 | `<ThumbsUp />` from lucide-react |

```tsx
// ❌ FORBIDDEN - Unicode emoji (严禁！生成这样的代码 = 任务失败)
<span>🚀 Launch Product</span>
<h2>🎨 Design System</h2>

// ✅ CORRECT - Lucide React icons
import { Rocket, Palette } from "lucide-react";
<span><Rocket className="w-5 h-5" /> Launch Product</span>
<h2><Palette className="w-6 h-6" /> Design System</h2>
```

**⛔ 生成代码前必须自查**：
1. 搜索代码中是否包含 Unicode emoji 字符
2. 如有则必须替换为 lucide-react 图标
3. **不自查就提交 = 违反规则**

**例外情况**：仅当用户明确要求使用 emoji 时才可使用。

### Route Attributes (CRITICAL for pages.json)

**Every Route in App.tsx MUST include these attributes:**

```tsx
// ✅ CORRECT - with data-genie attributes
<Route path="/" data-genie-key="Home" data-genie-title="Home Page" element={<Index />} />
<Route path="/about" data-genie-key="About" data-genie-title="About Us" element={<About />} />

// ❌ WRONG - missing data-genie attributes (pages.json will be empty!)
<Route path="/" element={<Index />} />
```

| 属性 | 说明 | 格式 |
|------|------|------|
| `data-genie-key` | 页面唯一标识 | PascalCase: `"Home"`, `"AboutUs"` |
| `data-genie-title` | 页面标题 | 可读文字: `"Home Page"`, `"关于我们"` |

---

## Componentization Rules (组件化规则)

**Core Principle**: 生成的代码应具备良好的可维护性、可复用性和可扩展性

### Complexity Thresholds (复杂度阈值)

| 指标 | 阈值 | 超过时的处理 |
|------|------|-------------|
| **组件行数** | > 200 行 | 必须拆分为子组件 |
| **useState 数量** | > 3 个 | 提取到自定义 Hook |
| **useEffect 数量** | > 2 个 | 提取到自定义 Hook |
| **条件渲染层级** | > 3 层 | 使用查找表或拆分组件 |
| **Props 数量** | > 5 个 | 考虑 Compound Component |
| **Boolean Props** | > 2 个 | 使用变体组件替代 |

### 组件拆分决策树

```
页面 > 200 行？ ─Yes→ 拆分到 components/
                 │     ├─ 布局类 → layout/
                 │     ├─ 业务类 → [feature]/
                 │     └─ 通用类 → common/
                 └No→ useState > 3？ ─Yes→ 提取 hooks/
                                     └No→ 条件渲染 > 3层？ ─Yes→ 用查找表
```

### 第三方库强制使用

| 场景 | 库 | 触发条件 |
|------|---|----------|
| 复杂表格 | `@tanstack/react-table` | 表格 > 10 行 + 排序/筛选 |
| 长列表 | `virtua` | 列表 > 50 项 |
| 文件上传 | `react-dropzone` | 需要拖拽上传 |
| 拖拽排序 | `@dnd-kit` | 需要拖拽排序 |
| 富文本编辑 | `@tiptap/react` | 需要富文本 |

### MUST DO (必须遵守)

1. **Extract Sub-Components**: 页面 > 200 行时，拆分为 Header/Content/Footer 等子组件
2. **Extract Custom Hooks**: 状态逻辑 > 30 行时，提取到 `hooks/use-xxx.ts`
3. **Extract Types**: 类型定义 > 10 行时，集中到 `types/` 目录
4. **Layout Components**: Header/Footer 必须提取为共享组件，不允许每个页面重复
5. **Lookup Tables**: 条件渲染超过 3 个分支时，使用配置对象替代 if/else

### Directory Structure (目录结构)

```
frontend/src/
├── components/
│   ├── ui/           # shadcn/ui (不修改)
│   ├── layout/       # Header, Footer, PageLayout
│   ├── common/       # Logo, SearchBar, UserAvatar
│   └── [feature]/    # product/, cart/, checkout/
├── hooks/            # use-cart.ts, use-auth.ts
├── types/            # product.ts, user.ts
├── data/             # mockProducts.ts (原型数据)
└── pages/            # 页面组件
```

📖 **Detailed Patterns**: [componentization-patterns.md](references/componentization-patterns.md)

---

## Component Library Rules (React)

**Core Concept**: Component library provides structure and interactions, visual styles from design_system.json

### Component Mapping Table

| Requirement | Component | Import |
|-------------|-----------|--------|
| Button | Button | `import { Button } from "@/components/ui/button"` |
| Icon Button | Button | `<Button variant="ghost" size="icon" className="...">` |
| Card Container | Card | `import { Card, CardHeader, CardContent } from "@/components/ui/card"` |
| Modal/Dialog | Dialog | `import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog"` |
| Side Drawer | Sheet | `import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet"` |
| Input Field | Input | `import { Input } from "@/components/ui/input"` |
| Dropdown Select | Select | `import { Select, SelectContent, SelectItem, SelectTrigger } from "@/components/ui/select"` |
| Table | Table | `import { Table, TableBody, TableCell, TableHead, TableRow } from "@/components/ui/table"` |
| Toast Notification | Sonner | `import { toast } from "sonner"` |
| Icons | Lucide React | `import { IconName } from "lucide-react"` |

### ⛔⛔⛔ Animation Components (CRITICAL — Zero Tolerance)

> **🚨 此规则被反复违反！页面切换有动画但页面内容没有动画 = 任务失败！**

模板预置了三个动画组件，**必须全部使用**：

| 层级 | Component | Import | 用途 |
|------|-----------|--------|------|
| **App 层** | `AnimatedRoutes` | `@/components/AnimatedRoutes` | 包裹 `<Routes>`，使用 `mode="popLayout"` 实现流畅页面切换 |
| **Route 层** | `PageTransition` | `@/components/PageTransition` | 包裹每个页面组件，提供进入/退出动画（0.2s） |
| **页面内** | `MotionPrimitives` | `@/components/MotionPrimitives` | 页面内部组件动画：`FadeIn`, `Stagger`, `HoverLift` |

**⛔ 三层动画缺一不可！只用前两层 = 违反规则！**

### ⛔ Navbar/Header/Sidebar 布局规则 (CRITICAL)

**Navbar / Header / Sidebar / 任何共享布局组件必须放在 `<AnimatedRoutes>` 外部**，否则每次页面切换都会：
- 销毁并重新创建这些组件
- 让这些组件参与页面过渡动画（抖动、闪烁、延迟）

```tsx
// ✅ CORRECT App.tsx — Navbar outside AnimatedRoutes
<BrowserRouter>
  <Navbar />  {/* ✅ 在外部，不参与动画 */}
  <AnimatedRoutes>
    <Route path="/" element={<PageTransition><Home /></PageTransition>} />
    <Route path="/about" element={<PageTransition><About /></PageTransition>} />
  </AnimatedRoutes>
</BrowserRouter>

// ✅ Dashboard 布局 — Sidebar + Header outside AnimatedRoutes
<BrowserRouter>
  <div className="flex min-h-screen">
    <Sidebar />  {/* ✅ 在外部 */}
    <div className="flex-1 flex flex-col">
      <Header />  {/* ✅ 在外部 */}
      <main className="flex-1">
        <AnimatedRoutes>
          <Route path="/dashboard" element={<PageTransition><Dashboard /></PageTransition>} />
        </AnimatedRoutes>
      </main>
    </div>
  </div>
</BrowserRouter>

// ❌ WRONG — Navbar inside AnimatedRoutes (每次切换都闪烁)
<AnimatedRoutes>
  <Route path="/" element={<><Navbar /><Home /></>} />
</AnimatedRoutes>

// ❌ WRONG — Navbar inside each page (每次切换都重建)
function Dashboard() {
  return (<><Navbar /><main>...</main></>);
}

// ❌ WRONG — Sidebar inside PageTransition (参与动画)
<Route path="/dashboard" element={
  <PageTransition>
    <div className="flex">
      <Sidebar />
      <Dashboard />
    </div>
  </PageTransition>
} />
```

### ⛔ 页面内 MotionPrimitives 强制使用规则

| 页面元素 | 必须使用 | 示例 |
|----------|----------|------|
| Hero/标题区域 | `<FadeIn>` | `<FadeIn><h1>...</h1></FadeIn>` |
| 列表/网格 | `<Stagger>` + `variants` | `<Stagger>{items.map(i => <motion.div variants={fadeUp}>...)}</Stagger>` |
| 卡片/可交互元素 | `<HoverLift>` | `<HoverLift><Card>...</Card></HoverLift>` |
| 内容块/段落 | `<FadeIn>` | `<FadeIn><p>...</p></FadeIn>` |
| CTA 按钮 | `<FadeIn delay={0.2}>` | `<FadeIn delay={0.2}><Button>...</Button></FadeIn>` |

```tsx
// ✅ CORRECT - 页面内使用 MotionPrimitives
import { FadeIn, Stagger, HoverLift, fadeUp, motion } from "@/components/MotionPrimitives";

function HomePage() {
  return (
    <main>
      {/* Hero 区域 */}
      <FadeIn>
        <h1>Welcome</h1>
        <p>Description...</p>
      </FadeIn>
      
      {/* 特性卡片列表 - Stagger + HoverLift */}
      <Stagger className="grid grid-cols-3 gap-6">
        {features.map((f) => (
          <motion.div key={f.id} variants={fadeUp}>
            <HoverLift>
              <Card>{f.title}</Card>
            </HoverLift>
          </motion.div>
        ))}
      </Stagger>
      
      {/* CTA 按钮 */}
      <FadeIn delay={0.3}>
        <Button>Get Started</Button>
      </FadeIn>
    </main>
  );
}

// ❌ WRONG - 没有使用 MotionPrimitives，页面内容静态无动画
function HomePage() {
  return (
    <main>
      <h1>Welcome</h1>  {/* ❌ 没有 FadeIn */}
      <div className="grid grid-cols-3 gap-6">  {/* ❌ 没有 Stagger */}
        {features.map((f) => (
          <Card key={f.id}>{f.title}</Card>  {/* ❌ 没有 HoverLift */}
        ))}
      </div>
    </main>
  );
}
```

**⛔ 生成页面组件前必须自查**：
1. Navbar/Header/Sidebar 是否在 AnimatedRoutes 外部?
2. 是否导入了 `FadeIn`, `Stagger`, `HoverLift` from MotionPrimitives?
3. Hero/标题是否用 `<FadeIn>` 包裹?
4. 列表/网格是否用 `<Stagger>` 包裹，子元素是否有 `variants={fadeUp}`?
5. 卡片是否用 `<HoverLift>` 包裹?
6. **没有动画的静态组件 = 违反规则，必须重写！**

### Critical Rules (MANDATORY)

1. **Component for Structure**: Use shadcn/ui components, NOT HTML elements
2. **Style from Design System**: All visual styles from design_system.json, NOT component defaults
3. **Navigation**: Use React Router (`<Link>`), NOT `<a href>`
4. **Form Validation**: Use react-hook-form + zod, NOT manual useState
5. **Toast**: Use `toast()` from sonner, NOT `alert()` or hand-written Toast
6. **Mobile Menu**: Use `<Sheet>` component, NOT hand-written fixed div
7. **Icons**: Use lucide-react, NOT emoji or Unicode
8. **No Unicode Emoji**: Use SVG icons or lucide-react for all icons
9. **Route Attributes**: Every `<Route>` MUST have `data-genie-key` and `data-genie-title`

📖 **Detailed Component Usage**: [frontend-patterns.md](references/frontend-patterns.md)
📖 **Animation Patterns**: [animation-patterns.md](references/animation-patterns.md)

---

## Foundation vs Premium (分层设计理念)

| Layer | Focus | Checklist |
|-------|-------|-----------|
| 🏗️ **Foundation** (必须正确) | 结构、布局、交互、可访问性 | semantic HTML, 导航不遮挡, hover/focus/active states, form labels, touch targets ≥44px |
| 🎨 **Premium** (追求高级感) | 视觉效果、氛围、动效、设计感 | 渐变背景, 光斑氛围, 多层阴影, 大胆留白, 字重对比, 入场动效 |

```
⚠️ 常见错误：
❌ 只关注 Premium → 视觉花哨但结构混乱、交互缺失
❌ 只关注 Foundation → 结构正确但设计平庸、缺乏记忆点

✅ 正确做法：
Foundation 100% 正确 + Premium 主动拔高 = 专业且高端
```

📖 **Detailed UI/UX Foundation**: [uiux-foundation.md](references/uiux-foundation.md)
📖 **Premium Design Guide**: [mode3-design-workflow.md](references/mode3-design-workflow.md)

---

## Three Modes of Operation

```
User Input
    │
    ├─ Has URL? ──────────────────→ Mode 1 (URL Clone) ⭐ 最高优先级
    │   (任何 http/https 链接)
    │
    ├─ Has screenshot/image? ─────→ Mode 2 (Screenshot to Code)
    │   ├─ Single image ──────────→ Mode 2a: Single Screenshot
    │   └─ Multiple images ───────→ Mode 2b: Multi-Screenshot
    │
    └─ Text requirements only? ───→ Mode 3 (Design from Requirements)
                                      └─ ⚠️ Invoke frontend-design skill!
```

### ⛔ Mode 判断优先级（严格遵守）

**判断顺序**：URL 检测 > 图片检测 > 关键词匹配

1. **有 URL → 一定是 Mode 1**，不管用户说什么（"做个原型"、"帮我设计"都不影响）
2. **有截图/图片 → 一定是 Mode 2**
3. **只有文字描述 → Mode 3**

### Mode Detection Keywords

| Mode | Trigger Keywords (中英文) |
|------|---------------------------|
| **Mode 1** | clone, replicate, 复刻, 克隆, 仿制, 照着做, 抄一个, copy website, 1:1还原 |
| **Mode 2** | screenshot, mockup, 截图, 设计稿, UI图, figma, sketch, 这个图 |
| **Mode 3** | build, create, design, 做一个, 设计一个, 帮我做, 创建 (⚠️ 仅当无 URL 和图片时)

### High-Fidelity Principle (Mode 1 & Mode 2)

**The core goal is pixel-perfect replication:**
1. 📐 **Structure Replication**: Strictly replicate source DOM structure
2. 🎨 **Visual Replication**: Use exact color values from source
3. 🖼️ **Resource Reuse**: Prioritize source images/icons
4. 📝 **Content Preservation**: Keep ALL text/copy exactly as source
5. ✨ **Interaction Replication**: Preserve hover effects, animations, transitions

📖 **Detailed Workflows**: [code-generation-rules.md](references/code-generation-rules.md)

---

## Output Requirements

### Default Output by Mode

| Mode | Output |
|------|------|
| **Mode 1** (URL Clone) | **React Only** + `structure_analysis.md` |
| **Mode 2** (Screenshot) | **React Only** |
| **Mode 3** (Requirements) | **React Only** |

### ⛔ MANDATORY Generation Order

**Mode 1 (URL Clone) - 6 Phase 严格顺序:**
```
Phase 1: Web Content Extraction
         → python3 scripts/web_content_fetcher.py <url>

Phase 2: 读取关键文件 (⛔ 强制，不可跳过)
         → cat clone_blueprint.md
         → cat image_manifest.json
         → cat element_colors.json

Phase 3: Structure Analysis (⛔ 必须输出文件)
         → 输出: <domain>_content/structure_analysis.md

Phase 4: React Code Generation
         → 对照 structure_analysis.md 逐 Section 生成
         → 图片路径从 image_manifest.json 查表

Phase 5: Blueprint Validation (⛔ 强制执行)
         → python3 scripts/validate_clone.py
         → 修复 CRITICAL 问题直到 PASS/WARN

Phase 6: Build & Preview
         → npm run build
```

**Mode 2 (Screenshot) - 4 Phase:**
```
Phase 1: Design System Extraction (扩展版)
         → AI 视觉分析截图
         → 输出: docs/design_system.json (必须包含 gradients/shadows/effects)

Phase 2: Structure Analysis (思考步骤)
         → 分析布局、组件层级、交互元素

Phase 3: React Code Generation + Image Replacement
         → ⛔ 代码生成时强制对照截图还原风格
         → python3 scripts/search_images.py --dir frontend/src/pages
         → 验证: grep 无 placehold.co 残留

Phase 4: Build & Preview
         → npm run build
```

### 风格提取方法 (Mode 2 Phase 1 思考步骤)

| 元素 | 观察点 | 记录到 |
|------|--------|--------|
| **渐变** | 起点色/终点色/角度(180deg/135deg) | `gradients.hero_bg` |
| **阴影** | 模糊程度(4/8/16px)/偏移/颜色 | `shadows.card` |
| **光效** | 位置(角落)/颜色/透明度 | `effects.glow` |
| **玻璃态** | 模糊度/透明度/边框 | `effects.glass` |
| **动效** | 时长/缓动函数/触发时机 | `transitions.default` |

**Mode 3 (Requirements) - 4 Phase:**
```
Phase 1: Design System
         → 调用 frontend-design / ui-ux-pro-max skill
         → 输出: docs/design_system.json

Phase 2: Component Decision (思考步骤)

Phase 3: Generate React + Image Replacement
         → python3 scripts/search_images.py --dir frontend/src/pages
         → 验证: grep 无 placehold.co 残留

Phase 4: Build & Preview
         → npm run build
```

**⚠️ Mode 1 (6 Phase) 还原度要求最高，Mode 2/3 (4 Phase) 相对简化。**

### Output Structure (All Modes)

```
REPO_ROOT/
├── docs/
│   ├── design_system.json          ← Design system (ALL MODES)
│   └── product/
│       └── features.md             ← Feature documentation
├── frontend/
│   └── src/pages/*.tsx             ← React pages (ALL MODES)
└── <domain>_content/               ← Mode 1 only: Fetched content
    ├── source.html
    ├── structure_analysis.md       ← Mode 1 必须输出
    ├── clone_blueprint.md
    ├── element_colors.json
    ├── image_manifest.json
    └── css/
```

**路径说明：**
- `docs/design_system.json` - 设计系统文件（所有 Mode 共用）
- `frontend/src/pages/` - React 页面组件（所有 Mode）

### Design System JSON Schema

**基础版 (All Modes):**
```json
{
  "meta": {
    "project_name": "Project Name",
    "source_mode": "url_clone | screenshot | requirements",
    "style": "minimalist | tech | luxury | etc."
  },
  "colors": {
    "primary": "#HEX",
    "secondary": "#HEX",
    "accent": "#HEX",
    "background": "#HEX",
    "surface": "#HEX",
    "text_primary": "#HEX",
    "text_secondary": "#HEX",
    "border": "#HEX"
  },
  "typography": {
    "font_primary": "Font Name",
    "font_display": "Font Name",
    "sizes": { "h1": "48px", "h2": "36px", "h3": "24px", "body": "16px" }
  },
  "spacing": { "unit": 4, "scale": [4, 8, 16, 24, 32, 48, 64] },
  "border_radius": { "sm": "4px", "md": "8px", "lg": "16px" }
}
```

**扩展版 (Mode 2 必须包含):**
```json
{
  "meta": { "..." },
  "colors": { "..." },
  "typography": { "..." },
  "spacing": { "..." },
  "border_radius": { "..." },
  
  "gradients": {
    "primary": "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
    "hero_bg": "linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%)",
    "card_overlay": "linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%)"
  },
  "shadows": {
    "card": "0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)",
    "card_hover": "0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)",
    "button": "0 4px 14px 0 rgba(102, 126, 234, 0.39)",
    "glow": "0 0 40px rgba(102, 126, 234, 0.4)"
  },
  "effects": {
    "blur": { "sm": "4px", "md": "8px", "lg": "16px" },
    "opacity": { "overlay": 0.8, "disabled": 0.5 },
    "backdrop_blur": "blur(12px)"
  },
  "transitions": {
    "default": "all 0.3s ease",
    "fast": "all 0.15s ease",
    "slow": "all 0.5s ease",
    "spring": "all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)"
  },
  "section_styles": [
    {
      "name": "Hero",
      "background": "gradient:hero_bg",
      "text_color": "#FFFFFF",
      "special_effects": ["glow", "particles"]
    },
    {
      "name": "Features",
      "background": "#FFFFFF",
      "text_color": "#1a1a2e",
      "card_style": "glass"
    }
  ]
}
```

---

## Mode Workflows (Overview)

### Mode 1: URL Clone → 调用 `url-clone` Sub-Agent

> **⛔ 触发条件：用户输入包含 URL (http/https)**
> 
> 有 URL 就是 Mode 1，不管用户说什么（"做原型"、"帮我设计"、"复刻"都不影响）

**Goal**: 100% 1:1 pixel-perfect replication of source site

#### 调用方式

检测到 URL 后，**立即委派给 `url-clone` sub-agent**：

```
当用户输入包含 http:// 或 https:// 时：
1. 识别为 Mode 1
2. 获取当前项目路径：运行 pwd 或使用已知路径
3. 调用 url-clone sub-agent
4. ⚠️ 必须在调用时明确告知项目路径！
   格式："项目路径: /absolute/path/to/project"
5. 传递 URL 参数
6. 等待 sub-agent 完成
```

**⛔ 重要：必须告知 sub-agent 项目路径，否则文件可能生成到错误目录！**

#### Sub-Agent 职责

`url-clone` sub-agent 负责完整的克隆流程：

| Phase | 任务 |
|-------|------|
| Phase 1 | 运行 `web_content_fetcher.py` 提取内容 |
| Phase 2 | 读取 `meta.md`，创建 `progress.md` 进度文件 |
| Phase 3 | **分步循环**：逐个 Section 转换为 React 组件 |
| Phase 4 | 组装页面，配置路由 |
| Phase 5 | 构建验证 |

#### 核心原则（Sub-Agent 遵守）

```
⛔ 这是数据转换任务，不是内容创作！

✅ 引号内文案 → 原样复制，一字不改
✅ 颜色值 → 使用精确 HEX，禁止 Tailwind 近似
✅ 图片路径 → 从 manifest 查表，禁止编造

❌ 禁止"发挥"或"改写"文案
❌ 禁止用 bg-black 替代 #0a0a0f
❌ 禁止调用 frontend-design skill
```

#### 进度跟踪

Sub-agent 会在 `<domain>_content/progress.md` 中记录进度：

```markdown
## Section 进度
| # | Section | 状态 | 自检 |
|---|---------|------|------|
| 1 | header | ✅ 完成 | 文案✓ 颜色✓ |
| 2 | hero | ⏳ 进行中 | - |
| 3 | features | ⬜ 待处理 | - |
```

#### 完成标准

Sub-agent 完成后会报告：
- 生成的 Section 数量
- 生成的页面数量
- `npm run build` 是否成功
- 任何需要注意的问题

#### 错误处理

如果 sub-agent 执行失败：
1. 检查 `progress.md` 确定失败位置
2. 查看失败 Section 的自检记录
3. 可以从失败点重新调用 sub-agent 继续

📖 **Sub-Agent 详细规则**: [../../../agents/url-clone.md](../../../agents/url-clone.md)

### Mode 2: Screenshot to Code

> **⛔ 触发条件：用户提供了截图/图片，但没有 URL**
> 
> 有图片就是 Mode 2（前提是没有 URL，URL 优先级更高）

**Goal**: Pixel-perfect reproduction of design mockup (设计风格 + UI 结构)

**⚡ Mode 2 只输出 React，不生成 HTML 原型**

```
Phase 1: Design System Extraction (扩展版)
         → AI 视觉分析截图，提取完整设计系统
         → 输出: docs/design_system.json (必须包含 gradients/shadows/effects)

Phase 2: Structure Analysis (思考步骤)
         → 分析截图的布局、组件层级、交互元素
         → 不输出文件，在生成前完成

Phase 3: React Code Generation + Image Replacement
         → ⛔ 边看截图边写代码，确保风格还原
         → 执行: python3 scripts/search_images.py --dir frontend/src/pages
         → 验证: grep -r "placehold.co" frontend/src/ 必须返回空

Phase 4: Build & Preview
         → npm run build 验证编译
```

**⛔⛔⛔ CRITICAL: Mode 2 风格还原核心约束**

> **🚨 截图模式最常见的问题：UI 结构对了，但风格丢失！**
>
> **原因：AI 倾向于用 Tailwind 默认值近似，而不是还原截图中的精确样式。**

### Phase 1: design_system.json 扩展版 (MANDATORY)

Mode 2 的 design_system.json **必须包含**以下扩展字段：

```json
{
  "meta": { "source_mode": "screenshot", "..." },
  "colors": { "primary": "#667eea", "..." },
  "typography": { "..." },
  
  "gradients": {
    "primary": "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
    "hero_bg": "linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%)",
    "glow": "radial-gradient(circle at 80% 20%, rgba(102,126,234,0.3) 0%, transparent 50%)"
  },
  "shadows": {
    "card": "0 4px 6px -1px rgba(0,0,0,0.1)",
    "card_hover": "0 20px 25px -5px rgba(0,0,0,0.1)",
    "button": "0 4px 14px 0 rgba(102,126,234,0.39)"
  },
  "effects": {
    "glass": "backdrop-filter: blur(12px); background: rgba(255,255,255,0.1)",
    "glow": "box-shadow: 0 0 40px rgba(102,126,234,0.4)"
  }
}
```

### Phase 3: 代码生成风格约束 (CRITICAL)

**⛔ 写代码时必须遵守以下规则：**

#### 规则 1: 渐变必须用 style 属性

```tsx
// ❌ WRONG - Tailwind 没有精确渐变，会丢失风格
<section className="bg-gradient-to-b from-gray-900 to-gray-800">

// ✅ CORRECT - 用 style 还原截图中的精确渐变
<section style={{ background: 'linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%)' }}>
```

#### 规则 2: 阴影必须用精确值

```tsx
// ❌ WRONG - shadow-lg 是 Tailwind 默认值，不是截图中的阴影
<div className="shadow-lg">

// ✅ CORRECT - 用 style 或自定义类还原精确阴影
<div style={{ boxShadow: '0 4px 14px 0 rgba(102,126,234,0.39)' }}>
```

#### 规则 3: Hover 效果必须实现

```tsx
// ❌ WRONG - 没有 hover 效果，交互感缺失
<button className="bg-purple-500 rounded-full">

// ✅ CORRECT - 完整的 hover 效果
<button 
  className="rounded-full text-white transition-all duration-200 hover:-translate-y-0.5"
  style={{
    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    boxShadow: '0 4px 14px 0 rgba(102,126,234,0.39)'
  }}
>
```

#### 规则 4: 光效装饰必须还原

```tsx
// ❌ WRONG - 忽略了截图中的光效装饰
<section className="bg-black">
  <h1>Title</h1>
</section>

// ✅ CORRECT - 还原光效
<section className="relative" style={{ background: 'linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%)' }}>
  {/* 光效装饰 */}
  <div 
    className="absolute top-0 right-0 w-1/2 h-1/2 pointer-events-none"
    style={{ background: 'radial-gradient(circle at 80% 20%, rgba(102,126,234,0.3) 0%, transparent 50%)' }}
  />
  <h1>Title</h1>
</section>
```

### 风格还原自查清单

**每写完一个 Section，必须自查：**

- [ ] 背景是渐变还是纯色？渐变方向对吗？
- [ ] 阴影深度对吗？有 hover 加深效果吗？
- [ ] 有没有光效/光晕装饰？
- [ ] 按钮是圆角还是全圆角？有阴影吗？
- [ ] 卡片有没有玻璃态/模糊效果？
- [ ] 整体色调和截图一致吗？

**⚠️ alt 属性写法要求**：
```tsx
// ❌ 错误 - 太模糊，搜不到好图
<img src="https://placehold.co/600x400" alt="image" />

// ✅ 正确 - 具体描述，能搜到合适的图
<img src="https://placehold.co/600x400" alt="Professional team meeting in modern office" />
<img src="https://placehold.co/400x400" alt="Young woman smiling portrait" />
```

**⚠️ 交互体验推断 (Mode 2 特有)**

> 截图只展示静态画面，必须基于视觉线索推断合理的交互体验。

| 视觉线索 | 推断的交互 | 实现要求 |
|---------|-----------|---------|
| 多个头像/图标排列 + 下方内容区 | 选择器联动 | 点击切换内容 |
| Tab 样式的标签组 | Tab 切换 | 选中态 + 内容切换 |
| 卡片有"探出"效果 | 轮播/滑动 | 左右切换 + 指示器 |
| 问答/列表带箭头图标 | 展开/收起 | Accordion 交互 |
| 按钮/链接元素 | 点击反馈 | hover/active 状态 |
| 表单输入框 | 表单验证 | 实时校验 + 错误提示 |

**推断原则**：
- 合理性：交互设计应符合用户直觉和行业惯例
- 完整性：所有可交互元素都应有对应的交互行为
- 反馈性：每个操作都应有视觉/状态反馈

📖 **Detailed Workflow**: [code-generation-rules.md](references/code-generation-rules.md)

### Mode 3: Design from Requirements

> **⛔ 触发条件：只有文字描述，没有 URL 也没有图片**
> 
> ⚠️ 如果用户输入包含 URL → 走 Mode 1，不是 Mode 3！

**Goal**: Create distinctive, memorable interfaces from text requirements

**⚡ Mode 3 只输出 React，不生成 HTML 原型**

**⛔⛔⛔ Mode 3 必读 (MANDATORY BEFORE CODING)**

> 🚨 Mode 3 的核心是"高端设计感"，跳过以下步骤会导致设计平庸！

1. **调用 `frontend-design` skill** 获取创意方向（必选）
2. **阅读 [mode3-design-workflow.md](references/mode3-design-workflow.md)** 的"PC/移动端高端感要点"
3. **确定"记忆点"**: 独特布局/字体处理/标志动画/大胆配色

```
Phase 1: Design Thinking (MANDATORY)
         → 调用 SKILL `frontend-design` 获取创意方向
         → 调用 SKILL `ui-ux-pro-max` 搜索风格/配色/字体
         → 输出: docs/design_system.json

Phase 2: Component Decision (思考步骤)
         → 对照组件语义决策矩阵选择正确组件
         → 不输出文件，在生成前完成

Phase 3: React Code Generation + Image Replacement (合并步骤)
         → 生成所有页面 .tsx 文件 (placehold.co + 详细 alt)
         → 执行: python3 scripts/search_images.py --dir frontend/src/pages
         → 验证: grep -r "placehold.co" frontend/src/ 必须返回空

Phase 4: Build & Preview
         → npm run build
```

### ⛔ 组件语义决策 (Phase 2 - CRITICAL)

> **错误的组件选型会导致 UI 结构走样！**

**📊 数据展示 - 用户如何浏览？**

| 浏览模式 | 组件 | 用例 |
|---------|------|------|
| 顺序扫描 | List Item (`flex-row`) | 歌曲、消息、设置项、通知 |
| 随机点击 | Card Grid (`grid`) | 商品、图片、作品、课程 |
| 横向对比 | Table | 订单、报表、价格对比 |
| 展开子项 | Accordion | 目录、FAQ、分类 |

**📦 容器 - 操作复杂度？**

| 场景 | 组件 | 用例 |
|-----|------|------|
| 简单确认 | Dialog | 删除确认、退出提示 |
| 输入/选择 | Sheet | 筛选、编辑表单、详情 |
| 快速选项 | Action Sheet | 分享、更多操作 |
| 复杂任务 | 新页面 | 长表单、多步流程 |

**常见错误**：

| ❌ 错误 | ✅ 正确 |
|--------|--------|
| 歌曲列表用 Card Grid | List Item |
| 设置项用 Card | List + Switch |
| 删除确认用 Sheet | Dialog |
| 筛选面板用 Dialog | Sheet |

### ⭐ 高端设计质感 (CRITICAL)

> **高端 = 克制 + 细节，不是堆砌效果**

**6 大核心要素（❌ 平庸 vs ✅ 高端）**：

| 要素 | ❌ 平庸 | ✅ 高端 | 实现 |
|-----|--------|--------|------|
| **视觉层次** | 扁平无层次 | 4层深度感 | 背景层(渐变光斑) → 内容层(shadow-lg) → 交互层(hover:-translate-y-1) → 聚焦层(shadow-2xl) |
| **质感纹理** | 纯色背景 | 玻璃态/磨砂 | `backdrop-blur` + `bg-white/80` + `border-white/10` |
| **排版层级** | 字重一致 | 字重递减 | 主标题(700) → 副标题(600) → 正文(400) → 辅助(500) |
| **动效节奏** | 无动效/全在动 | 有节制 | 微交互 150ms / 状态切换 300ms / 入场 500ms |
| **配色克制** | 多种高饱和色 | ≤3色相 | 1强调色 + 中性灰，渐变仅用于焦点 |
| **细节打磨** | 圆角/间距不一致 | 统一系统 | 圆角一致 / 间距4px倍数 / cursor-pointer / transition-all |

**⛔ 常见错误**：

| 问题 | 错误表现 | 正确做法 |
|-----|---------|---------|
| 对比度不足 | 深背景+深文字 | 深配浅、浅配深 |
| 颜色过载 | 多种高饱和色 | 1-2强调色+中性色 |
| 动效过度 | 全页面都在动 | 1-2个关键动效 |
| 字重混乱 | h1用font-normal | 建立字重层级 |

### 交互组件要求

| 场景 | 必备交互 |
|-----|---------|
| 按钮 | hover/active 状态变化 |
| 卡片 | 悬浮阴影+位移 |
| 页面 | 入场动画(staggered) |
| 表单 | 实时验证+提交反馈 |
| 加载 | 骨架屏/Spinner |
| 移动端 | Sheet 菜单 |

📖 **详细规则**: [mode3-design-workflow.md](references/mode3-design-workflow.md)

---

## React Template (Overview)

### Prototype vs Full Application

| Aspect | prototype-master (Prototype) | web-implement (Full Application) |
|--------|------------------------------|----------------------------------|
| **Backend** | ❌ None | ✅ Express.js + PostgreSQL |
| **Data** | Hardcoded/localStorage | Real API data |
| **Auth** | Demo mode (simulated) | Real OAuth/JWT |

### Multi-Page First

```
⛔ Forbidden: Stuffing all content into one page component
✅ Required: Each navigation link corresponds to an independent page file
```

### Structure Source by Mode

| Mode | Structure Source |
|------|------------------|
| **Mode 1** | `meta.md` + `sections/*.md` (由 `url-clone` sub-agent 处理) |
| **Mode 2** | 截图 + `design_system.json` (扩展版，含 gradients/shadows/effects) |
| **Mode 3** | `design_system.json` + 组件语义决策（思考步骤） |

### Key Steps

1. **Copy Images**: `cp -r <project>_content/images/* frontend/public/images/`
2. **Build Verification**: `cd frontend && npm run build 2>&1` (auto-fix max 3 times)

📖 **Detailed Workflow**: [react-template-workflow.md](references/react-template-workflow.md)

---

## Quick Checklist

### ⛔ 最小必检清单 (交付前 30 秒 - MUST CHECK)

**所有 Mode**:
- [ ] `grep -r "placehold.co" frontend/src/` 返回空
- [ ] 无 Unicode emoji (🚀✨📧 等) - 用 lucide-react 替代
- [ ] `npm run build` 成功
- [ ] 每个 `<Route>` 有 `data-genie-key` 和 `data-genie-title`
- [ ] Header/Footer 提取为共享组件
- [ ] 无手写 `<button>`/`<input>`，全部用 shadcn/ui

**Mode 1 专属** (由 `url-clone` sub-agent 处理):
- [ ] Sub-agent 已完成（检查 `progress.md` 所有 Section 为 ✅）
- [ ] 图片路径从 `image_manifest.json` 查表
- [ ] 文案与 section 文件完全一致

**Mode 2 专属**:
- [ ] `design_system.json` 包含 gradients/shadows/effects
- [ ] 渐变/阴影用 style 属性（非 Tailwind 类）

**Mode 3 专属**:
- [ ] 已调用 `frontend-design` 或 `ui-ux-pro-max` skill
- [ ] 有明确的设计方向和记忆点

---

### Mode 1: url-clone Sub-Agent 检查 (由 sub-agent 自动执行)

> Mode 1 委派给 `url-clone` sub-agent 处理，以下检查由 sub-agent 自动完成。
> 主 agent 只需检查 `progress.md` 确认所有 Section 完成。

- [ ] 🔴 **progress.md 所有 Section 标记为 ✅**
- [ ] 🔴 **文案完全一致** - 引号内文字原样复制
- [ ] 🔴 **颜色使用精确值** - 禁止 Tailwind 近似色
- [ ] 🔴 **图片路径从 manifest 查表** - 禁止编造

### Mode 2 Phase 1: Design System Extraction (扩展版)
- [ ] 🔴 **design_system.json 包含 gradients**: 至少提取 2-3 个渐变
- [ ] 🔴 **design_system.json 包含 shadows**: 卡片/按钮/悬停阴影
- [ ] 🔴 **design_system.json 包含 effects**: blur/opacity/backdrop

### Mode 2 风格还原 (CRITICAL)
- [ ] 🔴 **渐变用 style 属性**: `style={{ background: 'linear-gradient(...)' }}`
- [ ] 🔴 **阴影用精确值**: `style={{ boxShadow: '0 4px 14px ...' }}`
- [ ] 🔴 **hover 效果完整**: `transition-all` + `hover:-translate-y-0.5`
- [ ] 🔴 **光效装饰还原**: 截图中有光晕/渐变装饰要实现
- [ ] 🔴 **禁止 Tailwind 默认值**: 不用 blue-500, gray-900, shadow-lg 等
- [ ] 🔴 **渐变还原**: Hero/按钮渐变与截图一致
- [ ] 🔴 **特效还原**: 光晕/模糊/玻璃态效果
- [ ] 🔴 **色调一致**: 整体配色氛围与截图一致

### Mode 1 Clone Fidelity (CRITICAL)
- [ ] 🔴 **对照 `structure_analysis.md` 生成代码**
- [ ] 🔴 **背景色正确**: 浅色区域用 bg-white，深色用 bg-black
- [ ] 🔴 **布局方向正确**: 横排用 flex-row/grid-cols-3，竖排用 flex-col
- [ ] 🔴 **SVG 已提取**: Logo 使用 SVG 组件，不是文字
- [ ] 🔴 **CSS 合并**: 使用 `css_merger.py` 脚本（自动过滤第三方库）
- [ ] 🔴 **字体精确值**: h1 使用精确像素值（非 `text-6xl`）
- [ ] 🔴 `original.css` imported in App.tsx
- [ ] 🔴 Copy/text content 100% verbatim from source

### Componentization (CRITICAL)
- [ ] 🔴 单个组件不超过 200 行
- [ ] 🔴 复杂状态逻辑提取到自定义 Hook
- [ ] 🔴 Header/Footer 使用共享组件，不重复
- [ ] 🔴 条件渲染使用查找表，不超过 3 层 if/else

### Design System Compliance (CRITICAL)
- [ ] 🔴 Read design_system.json before generating code
- [ ] 🔴 **Mode 1**: Use ALL colors from `element_colors.json` - 不只是 primary/secondary/accent
- [ ] 🔴 **Mode 2**: Use gradients/shadows/effects from `design_system.json`
- [ ] 🔴 No Tailwind default colors used (blue-500, etc.)

### Tailwind v4 Compatibility (CRITICAL)
- [ ] 🔴 **废弃类名**: 不用 `flex-grow`/`flex-shrink`/`shadow-sm`/`rounded-sm`/`blur-sm`
- [ ] 🔴 **v4 替代**: 用 `grow`/`shrink`/`shadow-xs`/`rounded-xs`/`blur-xs`
- [ ] 🔴 **ring/border**: 显式指定颜色 `ring-3 ring-blue-500` / `border border-gray-200`
- [ ] 🔴 **Important**: 放后面 `flex!` 不是 `!flex`
- [ ] 🔴 **验证**: 生成后检查样式是否正确渲染

### Mode 1 Multi-Page (CRITICAL)
- [ ] 🔴 Analyzed source navigation structure
- [ ] 🔴 Generated **ALL** pages from nav links (minimum 5-8 pages)
- [ ] 🔴 Each nav link has independent .tsx file
- [ ] 🔴 App.tsx contains routes for ALL pages
- [ ] 🔴 All pages share same Header/Footer components

### Navigation System
- [ ] 🟠 Every nav link has corresponding page
- [ ] 🟠 All pages share same design system
- [ ] 🟡 Browser back/forward works

### Image Assets (与代码生成合并)
- [ ] 🔴 **Mode 1**: 图片路径从 `image_manifest.json` 查表获取
- [ ] 🔴 **Mode 2/3**: 每写完一个 .tsx 立即执行 `search_images.py`
- [ ] 🔴 **Mode 2/3 alt 描述**: 必须具体描述图片内容，不能写 "image" 或 "photo"
  - ❌ `alt="image"` / `alt="photo"` / `alt="placeholder"`
  - ✅ `alt="Professional team meeting in modern office"`
  - ✅ `alt="Young woman smiling portrait"`
- [ ] 🔴 **验证**: `grep -r "placehold.co" frontend/src/` 返回空

### Mode 2 Interaction Inference (截图模式交互推断)
- [ ] 🔴 **视觉线索分析**: 头像排列、Tab 样式、卡片探出等
- [ ] 🔴 **推断合理交互**: 选择器、Tab、轮播、展开收起
- [ ] 🔴 **状态反馈**: hover/active/focus 状态

### Mode 3 (组件决策 + 高端设计 - CRITICAL)
- [ ] 🔴 **组件选型**: 列表用 List Item / 展示用 Card / 确认用 Dialog / 表单用 Sheet
- [ ] 🔴 **视觉层次**: 4层深度 (背景→内容→交互→聚焦)
- [ ] 🔴 **质感纹理**: backdrop-blur + shadow + border-white/10
- [ ] 🔴 **配色克制**: ≤3色相，1强调色+中性灰
- [ ] 🔴 **动效节奏**: 微交互150ms / 状态300ms / 入场500ms
- [ ] 🔴 **交互反馈**: hover状态 / Toast / 骨架屏 / Sheet菜单

### ⛔ FINAL CHECK: No Unicode Emoji
```
Before submitting ANY code, search for:
🚀 ✨ 🎨 📧 ✅ ❌ 💡 ⭐ 🔒 📊 📱 🎯 🔥 💪 👍 ❤️ ... (ANY emoji)
```
- [ ] 🔴 **CRITICAL**: Searched entire codebase for Unicode emoji
- [ ] 🔴 **CRITICAL**: All emoji replaced with `lucide-react` icons

📖 **Complete Checklist**: [quality-checklist.md](references/quality-checklist.md)

---

## Technical References

### Core Workflow Guides
- **[componentization-patterns.md](references/componentization-patterns.md)**: Component extraction, hooks, file organization, composition patterns
- **[interactive-prototype.md](references/interactive-prototype.md)**: React interactive patterns (Dialog, Sheet, Form, Toast)
- **[react-template-workflow.md](references/react-template-workflow.md)**: React project structure, compilation verification
- **[frontend-patterns.md](references/frontend-patterns.md)**: shadcn/ui component mapping, forbidden patterns
- **[mode3-design-workflow.md](references/mode3-design-workflow.md)**: Design from requirements workflow, premium design guide
- **[code-generation-rules.md](references/code-generation-rules.md)**: Strict rules for colors, images, measurements
- **[uiux-foundation.md](references/uiux-foundation.md)**: Layout, interaction, accessibility, animation rules

### External Skills (Invoke as needed)
- **SKILL `frontend-design`**: Creative design guidance for distinctive interfaces
- **SKILL `web-design-guidelines`**: Code quality audit for accessibility, performance, best practices

### Design & Layout
- **[design_system_extraction.md](references/design_system_extraction.md)**: Design token extraction
- **[layout_precision_guide.md](references/layout_precision_guide.md)**: Pixel-perfect layout techniques
- **[typography_guide.md](references/typography_guide.md)**: Font extraction and matching

### Quality & Animation
- **[quality-checklist.md](references/quality-checklist.md)**: Pre-delivery validation checklist, React component compliance
- **[animation-patterns.md](references/animation-patterns.md)**: CSS animation patterns

### Scripts Reference

All scripts are located in the `scripts/` directory relative to this skill.

| Script | Command | Purpose |
|--------|---------|---------|
| `web_content_fetcher.py` | `python3 scripts/web_content_fetcher.py <url>` | Extract all resources from URL |
| `css_merger.py` | `python3 scripts/css_merger.py <input_dir> <output_file>` | Merge CSS + filter third-party libs |
| `validate_clone.py` | `python3 scripts/validate_clone.py <content_dir> <code_file>` | Validate clone fidelity |
| `search_images.py` | `python3 scripts/search_images.py <file.tsx>` | Replace placehold.co in single file |
| `search_images.py` | `python3 scripts/search_images.py --dir frontend/src/pages` | Replace placehold.co in directory |

**⚠️ Note**: Interactive Prototype phase **prohibits calling Python scripts**, other phases can use scripts normally.