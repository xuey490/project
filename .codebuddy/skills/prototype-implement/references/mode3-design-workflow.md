# Mode 3: Design from Requirements Workflow

## Overview

Mode 3 是从纯文字需求开始设计和构建完整原型的流程。

**触发条件**: 用户提供文字需求，但没有 URL 或截图

**Use Cases**: 
- "Build a luxury spa landing page"
- "Create a SaaS dashboard"
- "Design an e-commerce product page"

## Critical Rule

```
⛔ STOP - 在 Mode 3 下，禁止跳过 Phase 0！

没有 URL / 没有截图 = 必须走 Mode 3
Mode 3 = 必须先进行 Design Thinking，调用 frontend-design skill 获取创意指导
不能跳过设计系统生成直接写代码！

❌ 错误: 用户需求 → 直接生成 React 代码
✅ 正确: 用户需求 → Design Thinking (frontend-design) → design_system.json → React 代码
```

---

## Phase 0: Design Thinking + Design System (MANDATORY)

**Objective**: Create comprehensive design system from user requirements with creative direction

**⚠️ 这一步是 Mode 3 的核心，必须执行！**

### Step 0.1: Invoke frontend-design Skill

**调用 SKILL `frontend-design` 获取创意指导：**

frontend-design 会指导你：
- 选择 BOLD 的美学方向（不是平庸的默认选择）
- 避免 AI 生成的通用美学（Inter 字体、紫色渐变等）
- 创造有记忆点的设计

### Step 0.2: Analyze User Requirements

Extract key information from user's text requirements:

```javascript
const requirements = {
  productType: 'luxury spa',           // e.g., SaaS, e-commerce, portfolio, service
  industry: 'wellness/beauty',         // e.g., healthcare, fintech, education
  styleKeywords: ['elegant', 'soft'],  // e.g., minimal, playful, professional
  stack: 'html-tailwind',             // default, or user-specified (or react_template)
  pageType: 'landing'                 // landing, dashboard, product, etc.
};
```

### Step 0.3: Choose BOLD Aesthetic Direction

**Pick ONE extreme direction and commit fully:**

| Category | Options | When to Use |
|----------|---------|-------------|
| **Minimal** | Brutally minimal, Swiss/clean, Japanese zen | Content-focused, professional services |
| **Maximal** | Maximalist chaos, information-dense, layered | Creative agencies, entertainment |
| **Era** | Retro-futuristic, Y2K revival, Art deco, 90s web | Nostalgic brands, creative portfolios |
| **Natural** | Organic/natural, soft/pastel, earthy tones | Wellness, sustainability, beauty |
| **Premium** | Luxury/refined, editorial/magazine, high fashion | High-end products, luxury services |
| **Raw** | Brutalist/raw, industrial/utilitarian | Tech/developer, avant-garde |
| **Playful** | Toy-like, cartoon, Memphis design, bold colors | Kids, gaming, casual apps |

### Step 0.4: Define Memorable Element

```markdown
What's the ONE thing someone will remember?
- Unique layout pattern (asymmetric hero, overlapping sections)
- Distinctive typography treatment
- Signature animation (staggered reveal, scroll-triggered)
- Bold color choice (unexpected accent, gradient mesh)
- Atmospheric background (noise texture, geometric patterns)
```

### Step 0.5: Generate Design System (AI Direct Output)

**AI 直接生成 design_system.json，不依赖外部脚本：**

```json
{
  "meta": {
    "project_name": "Serenity Spa",
    "source_mode": "requirements",
    "style": "premium_natural",
    "aesthetic_direction": "Premium - Luxury/refined with soft natural tones"
  },
  "colors": {
    "primary": "#8B7355",
    "secondary": "#E8DDD3",
    "accent": "#C9A690",
    "background": "#FFFFFF",
    "surface": "#FAFAF8",
    "text_primary": "#2D2D2D",
    "text_secondary": "#6B6B6B",
    "border": "#E5E5E5"
  },
  "typography": {
    "font_display": "Playfair Display",
    "font_body": "Source Sans Pro",
    "sizes": {
      "h1": "48px",
      "h2": "36px",
      "h3": "24px",
      "body": "16px",
      "small": "14px"
    }
  },
  "spacing": {
    "unit": 4,
    "scale": [4, 8, 16, 24, 32, 48, 64, 96]
  },
  "border_radius": {
    "sm": "8px",
    "md": "12px",
    "lg": "16px",
    "xl": "24px"
  }
}
```

### Step 0.6: Validate Design System

**Font Blacklist Check** - 如果发现以下字体，必须替换：

| Forbidden Fonts | Replacement Options |
|-----------------|---------------------|
| Inter | Plus Jakarta Sans, Figtree, DM Sans |
| Roboto | IBM Plex Sans, Source Sans 3, Noto Sans |
| Space Grotesk | Outfit, Syne, Manrope |
| Arial | Any Google Font alternative |
| System fonts | Any Google Font alternative |

**Completeness Check** - 确保所有必需值存在：

| Category | Required Fields |
|----------|-----------------|
| Typography Sizes | h1, h2, h3, h4, body, small, caption |
| Font Weights | normal, medium, semibold, bold |
| Spacing Scale | xs, sm, md, lg, xl, 2xl, 3xl |
| Border Radius | sm, md, lg, xl, full |

### Step 0.7: Create Output Files

**File 1**: `<project>_content/aesthetic_direction.md`

```markdown
# Aesthetic Direction: Serenity Spa

## Purpose
Landing page for a luxury spa service targeting high-end clientele seeking premium wellness experiences.

## Chosen Tone
**Direction**: Premium - Luxury/refined with natural undertones
**Reasoning**: Spa services require trust and relaxation - luxury visuals with soft, natural colors create the right mood.

## Memorable Element
Asymmetric hero with floating service cards that overlap the hero section, creating depth and visual interest.

## Typography Intent
- Display: Playfair Display (elegant serif for headings)
- Body: Source Sans Pro (clean, readable sans-serif)

## Color Intent
- Dominant: Warm brown (#8B7355) - earthy, grounding
- Accent: Soft gold (#C9A690) - premium feel
- Background: Clean white with warm surface tones

## Layout Intent
- Hero with full-bleed image + floating booking CTA
- Services as overlapping cards breaking out of grid

## Animation Intent
- Page load: Staggered fade-in-up with 100ms delays
- Cards: Subtle lift on hover with shadow deepening
```

**File 2**: `<project>_content/design_system.json` (validated)

**File 3**: `<project>_content/DESIGN_SYSTEM.md` (human-readable version)

### Success Criteria:

- [ ] Design direction chosen (not generic)
- [ ] design_system.json generated
- [ ] Font blacklist validated
- [ ] aesthetic_direction.md created
- [ ] All required design tokens present

---

## Phase 1: Component Decision (思考步骤)

**Objective**: 确定正确的组件选型和页面结构

**⚠️ 这是思考步骤，不输出文件**

### Step 1.1: 组件语义决策

在生成代码前，必须先思考以下问题：

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

### Step 1.2: Apply Premium Enhancements

**Every section should have premium visual treatment:**

```html
<!-- ❌ WRONG: Plain, flat design -->
<section class="py-16 bg-white">
  <h1 class="text-4xl font-bold">Welcome</h1>
</section>

<!-- ✅ CORRECT: Premium with atmosphere -->
<section class="py-[80px] lg:py-[120px] bg-gradient-to-br from-[#FFFFFF] to-[#FAFAF8] relative overflow-hidden">
  <!-- Atmospheric glow -->
  <div class="absolute top-20 right-0 w-[400px] h-[400px] bg-[#C9A690]/20 rounded-full blur-3xl" />
  
  <div class="max-w-[1200px] mx-auto px-[24px] relative z-10">
    <h1 class="text-[48px] lg:text-[64px] font-bold tracking-tight text-[#2D2D2D]"
        style="font-family: 'Playfair Display', serif;">
      Experience Pure Serenity
    </h1>
  </div>
</section>
```

### Success Criteria:

- [ ] 组件选型已确定（List vs Card vs Table）
- [ ] 容器选型已确定（Dialog vs Sheet vs 新页面）
- [ ] Premium 设计方向已确定
- [ ] 准备好直接生成 React 代码

---

## Phase 2: React Code Generation + Image Replacement

**Objective**: Generate pixel-perfect React code with real images

### Pre-Generation Checklist

```markdown
Before generating code, I MUST:
- [ ] Read docs/design_system.json (exact colors/fonts/spacing)
- [ ] Complete component decision (思考步骤)
- [ ] Prepare detailed alt descriptions for images
```

### Mandatory Rules

1. ✅ Use exact colors from design_system.json
2. ✅ Load exact fonts specified in design_system.json
3. ✅ Follow spacing scale from design_system.json
4. ✅ Apply component semantic decisions
5. ✅ NO placeholder comments - complete code only
6. ✅ Include premium enhancements (gradients, glows, shadows)

### Image Replacement (合并步骤)

```
1. 生成所有页面 .tsx 文件 (placehold.co + 详细 alt)
2. 执行: python3 scripts/search_images.py --dir frontend/src/pages
3. 验证: grep -r "placehold.co" frontend/src/  # 必须返回空
```

---

## Phase 3: Build & Preview

```bash
cd frontend && npm run build
```

---

## Phase 4: Quality Review (Optional)

**Invoke SKILL `web-design-guidelines` to audit generated code:**

Audit areas:
- Accessibility (aria-labels, keyboard navigation, semantic HTML)
- Forms (autocomplete, validation, error handling)
- Animation (prefers-reduced-motion, transform/opacity only)
- Performance (image dimensions, lazy loading)
- Typography (curly quotes, ellipsis)

---

## Complete Workflow Example

```markdown
User: "Build a landing page for a luxury spa service"

AI: Detected Mode 3 (Design from Requirements)
📍 Starting Design-from-Scratch Process...

🎨 PHASE 0: Design Thinking + Design System

Invoking frontend-design skill for creative guidance...

Analyzing requirements...
- Product: Luxury spa service
- Industry: Wellness/Beauty
- Style keywords: elegant, soft, premium

Choosing aesthetic direction...
✅ Direction: Premium - Luxury/refined with natural undertones
✅ Memorable element: Asymmetric hero with floating service cards

Generating design system (AI direct output)...
✅ Colors: #8B7355 (primary), #E8DDD3 (secondary), #C9A690 (accent)
✅ Typography: Playfair Display + Source Sans Pro
✅ Font blacklist validated: No forbidden fonts

✅ Created: docs/design_system.json

📐 PHASE 1: Component Decision (思考步骤)

确定组件选型...
✅ 服务展示：Card Grid（用户随机浏览服务）
✅ 预约确认：Dialog（简单确认操作）
✅ 筛选面板：Sheet（多条件选择）
✅ Premium 设计：渐变背景 + 光斑氛围 + 悬浮卡片

💻 PHASE 2: React Code Generation + Image Replacement

Reading design system...
✅ Read docs/design_system.json

Generating React code section by section...
📍 Hero Section (premium with atmospheric glow)
  → Write frontend/src/pages/Index.tsx
  → Run search_images.py 替换图片
📍 Services Section (Card Grid)
📍 About Section
📍 Testimonials
📍 CTA Section
📍 Footer

验证图片替换...
✅ grep -r "placehold.co" frontend/src/ 返回空

🔧 PHASE 3: Build & Preview

✅ npm run build 成功

📋 PHASE 4: Quality Review (Optional)

Would you like me to run web-design-guidelines audit?

✅ Complete! Luxury spa landing page ready.
```

---

## 🚀 From Mediocre to Premium: Upgrade Guide

> **核心理念**：以下指南教你如何将平庸的设计拔高到高端水准，创造真正有记忆点的界面。

---

### 🖥️ PC 端高端感要点 (Desktop/Web)

> PC 端侧重：**大气留白、视觉冲击、氛围营造、精致 hover 交互**

#### 颜色与氛围

| 维度 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **背景** | 纯白 `bg-white` | 微妙渐变 `bg-gradient-to-b from-slate-50 to-white` |
| **Hero 区域** | 纯色背景 | 渐变 + 装饰性光斑 `blur-3xl` |
| **深色模式** | 纯黑 `bg-black` | 深色渐变 + 微妙纹理 |
| **卡片背景** | 纯白/纯灰 | 玻璃态 `bg-white/80 backdrop-blur-xl` |

```tsx
// ✅ PC 端高端 Hero
<section className="relative py-32 lg:py-40 overflow-hidden">
  {/* 背景渐变 */}
  <div className="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50" />
  {/* 装饰光斑 */}
  <div className="absolute top-20 left-1/4 w-[600px] h-[600px] bg-blue-200/30 rounded-full blur-3xl" />
  <div className="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-purple-200/20 rounded-full blur-3xl" />
  {/* 内容 */}
  <div className="relative z-10 max-w-6xl mx-auto px-8">...</div>
</section>
```

#### 间距与留白

| 场景 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **Hero** | `py-16` (64px) | `py-32 lg:py-40` (128-160px) 大胆留白 |
| **Section** | `py-12` (48px) | `py-24 lg:py-32` (96-128px) |
| **节奏** | 所有 section 间距相同 | 大-小-大 对比节奏 |
| **内容宽度** | `max-w-7xl` 占满 | `max-w-5xl` 更多呼吸感 |

```tsx
// ✅ PC 端节奏对比
<Hero className="py-40" />           // 大：入场气势
<Features className="py-20" />       // 小：信息紧凑
<Testimonials className="py-32" />   // 大：呼吸空间
<CTA className="py-40" />            // 大：收尾留白
```

#### 光影与深度

| 元素 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **卡片** | `shadow-md` | 多层阴影 + hover 抬起效果 |
| **图片** | `shadow-lg` | 彩色投影 `shadow-2xl shadow-blue-500/20` |
| **按钮** | 无阴影 | 发光效果 `shadow-lg shadow-primary/25` |

```tsx
// ✅ PC 端高端卡片
<div className="
  group
  bg-white rounded-2xl p-8
  shadow-[0_4px_20px_rgba(0,0,0,0.08)]
  hover:shadow-[0_20px_40px_rgba(0,0,0,0.12)]
  hover:-translate-y-2
  transition-all duration-500 ease-out
">
  <h3 className="group-hover:text-primary transition-colors">Title</h3>
</div>
```

#### Hover 交互 (PC 端核心)

| 元素 | 效果 |
|------|------|
| **卡片** | 抬起 `hover:-translate-y-2` + 阴影加深 |
| **按钮** | 发光 + 微妙放大 `hover:scale-[1.02]` |
| **图片** | 容器内微放大 `group-hover:scale-105` |
| **链接** | 下划线动画或颜色渐变 |
| **导航项** | 背景渐显 + 文字颜色变化 |

```tsx
// ✅ PC 端精致 hover
<a className="
  relative overflow-hidden
  after:absolute after:bottom-0 after:left-0 
  after:h-[2px] after:w-0 after:bg-primary
  after:transition-all after:duration-300
  hover:after:w-full
">
  Learn More
</a>
```

#### 入场动效

```tsx
// ✅ PC 端交错入场
<motion.div
  initial={{ opacity: 0, y: 40 }}
  whileInView={{ opacity: 1, y: 0 }}
  viewport={{ once: true }}
  transition={{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }}
>
  {items.map((item, i) => (
    <motion.div
      key={i}
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      transition={{ delay: i * 0.1, duration: 0.6 }}
    >
      {item}
    </motion.div>
  ))}
</motion.div>
```

---

### 📱 移动端高端感要点 (Mobile/App)

> 移动端侧重：**精致光影、流畅手势、微交互反馈、层次分明**

#### 卡片与容器

| 元素 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **快捷入口** | 纯色圆角矩形 | 微妙阴影 + 内发光边框 + active 按压效果 |
| **列表卡片** | 纯白背景 | 玻璃态 + 细边框 + 微妙阴影 |
| **功能区块** | 硬边界分隔 | 圆角容器 + 阴影层次 |

```tsx
// ✅ 移动端高端快捷入口
<div className="
  flex flex-col items-center gap-2 p-4
  bg-gradient-to-br from-white to-gray-50
  rounded-2xl
  shadow-[0_2px_12px_rgba(0,0,0,0.08)]
  border border-white/50
  active:scale-95 active:shadow-inner
  transition-all duration-200
">
  <div className="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
    <Icon className="w-6 h-6 text-blue-500" />
  </div>
  <span className="text-sm font-medium">每日推荐</span>
</div>
```

#### 图片与封面

| 场景 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **歌单/专辑封面** | 直接展示 | 圆角 + 阴影 + 播放量玻璃态标签 |
| **列表缩略图** | 无处理 | 圆角 + 微妙边框 |
| **头像** | 简单圆形 | 圆形 + 边框光环 + 在线状态点 |

```tsx
// ✅ 移动端高端封面卡片
<div className="relative group">
  <img 
    src="/album.jpg" 
    className="
      w-full aspect-square object-cover
      rounded-xl
      shadow-[0_4px_16px_rgba(0,0,0,0.15)]
    "
  />
  {/* 播放量标签 - 玻璃态 */}
  <div className="
    absolute top-2 right-2
    px-2 py-1 rounded-full
    bg-black/40 backdrop-blur-sm
    text-white text-xs font-medium
    flex items-center gap-1
  ">
    <PlayIcon className="w-3 h-3" />
    12.5万
  </div>
</div>
```

#### 列表项交互

| 状态 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **默认** | 纯白背景 | 微妙背景色 + 细分隔线 |
| **按压** | 无反馈 | 背景变深 `active:bg-gray-100` |
| **滑动** | 无 | 滑动显示操作按钮 |
| **排名数字** | 普通文字 | 品牌色 + 字重区分 (1-3 特殊样式) |

```tsx
// ✅ 移动端高端列表项 - 核心 classes
<div className="flex items-center gap-4 px-4 py-3 active:bg-gray-50 transition-colors">
  <span className={index < 3 ? 'text-orange-500 font-bold' : 'text-gray-400'}>...</span>
  <img className="w-12 h-12 rounded-lg shadow-sm" />
  <button className="active:scale-90 transition-transform">...</button>
</div>
```

#### 底部导航栏

| 元素 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **背景** | 纯白 | 毛玻璃 `bg-white/80 backdrop-blur-xl` |
| **选中态** | 仅变色 | 图标填充 + 微妙发光 + 文字加粗 |
| **切换** | 无动画 | 图标弹跳 + 指示器滑动 |

```tsx
// ✅ 移动端高端底部导航 - 核心 classes
<nav className="fixed bottom-0 inset-x-0 bg-white/80 backdrop-blur-xl border-t shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
  <button className={active ? 'text-primary scale-105' : 'text-gray-400 active:scale-95'}>
    <Icon className={active ? 'fill-current' : ''} />
    <span className={active ? 'font-semibold' : ''}>Tab</span>
  </button>
</nav>
```

#### 播放控制条

```tsx
// ✅ 移动端高端播放条 - 核心 classes
<div className="fixed bottom-16 inset-x-4 p-3 rounded-2xl bg-white/90 backdrop-blur-xl shadow-[0_4px_24px_rgba(0,0,0,0.12)] border border-white/50">
  <img className="w-12 h-12 rounded-xl shadow-md animate-spin-slow" />
  <button className="w-12 h-12 rounded-full bg-primary shadow-lg shadow-primary/30 active:scale-95" />
  {/* 进度条 */}
  <div className="h-1 bg-gray-200 rounded-full">
    <div className="h-full bg-gradient-to-r from-primary to-primary-light" />
  </div>
</div>
```

#### 移动端入场动效

```tsx
// ✅ 移动端动效特点：delay: i * 0.05 (更快交错), duration: 0.25-0.3s (更短时长)
<motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: i * 0.05, duration: 0.3 }} />
```

---

### 🎯 PC vs 移动端对比总结

| 维度 | 🖥️ PC 端侧重 | 📱 移动端侧重 |
|------|-------------|--------------|
| **留白** | 大胆、戏剧性留白 | 紧凑但有呼吸感 |
| **光影** | 大面积光斑氛围 | 精致微妙阴影 |
| **交互** | hover 效果为主 | active/按压反馈为主 |
| **动效时长** | 较长 500-800ms | 较短 150-300ms |
| **动效缓动** | 优雅 ease-out | 敏捷 ease-out |
| **卡片** | 大卡片、多层阴影 | 紧凑卡片、玻璃态 |
| **背景** | 渐变 + 大光斑 | 微妙渐变 + 毛玻璃 |
| **按钮** | hover 发光 | active 缩放反馈 |
| **列表** | hover 行高亮 | 按压反馈 + 滑动操作 |

---

### 🎨 通用高端细节 (PC + 移动端共享)

#### 字体层次

| 维度 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **字号对比** | 标题 `text-3xl`，正文 `text-base` | 极致对比：标题 `text-5xl+`，正文 `text-sm` |
| **字重层次** | 统一 `font-bold` | 标题 `font-black`，副标题 `font-light` |
| **字间距** | 默认 | 大标题 `tracking-tight`，标签 `tracking-wide` |

#### 图片处理

| 场景 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **展示** | 直接 `<img>` | 容器包裹 + `overflow-hidden` + 圆角 |
| **悬停** | 无 | 图片微放大 + 叠加层渐显 |
| **阴影** | `shadow-lg` | 彩色投影 `shadow-2xl shadow-primary/20` |

```tsx
// ✅ 高端图片卡片
<div className="group relative overflow-hidden rounded-2xl">
  <img 
    src="/cover.jpg" 
    className="w-full transition-transform duration-500 group-hover:scale-105"
  />
  <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
</div>
```

#### 细节点睛

| 元素 | ❌ 平庸 | ✅ 高端 |
|------|--------|--------|
| **分隔线** | `border-t` | 渐变 `bg-gradient-to-r from-transparent via-gray-200 to-transparent h-px` |
| **徽章** | 纯色背景 | 玻璃态 `bg-white/10 backdrop-blur-sm border border-white/20` |
| **图标** | 单色 | 渐变填充或 duotone 双色调 |
