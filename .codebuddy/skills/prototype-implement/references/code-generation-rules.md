# Code Generation Rules

## Overview

This document contains the strict rules and constraints for code generation across all modes (URL Clone, Screenshot, Requirements).

---

## ⚠️ Mode 1 & Mode 2: 高保真度原则 (CRITICAL)

**Mode 1 (URL Clone) 和 Mode 2 (Screenshot) 的核心目标是像素级还原。**

```
🎯 保真度要求:

1. 结构还原: 严格复刻源站/设计稿的 DOM 结构，禁止自行设计
2. 视觉还原: 使用精确颜色值 (#HEX)，禁止近似色或 Tailwind 默认色
3. 资源复用: 使用源站提取的图片资源，而非替换为占位图
4. 内容保留: 完整复刻所有文本，禁止省略或简化
5. 细节还原: 保留悬停效果、动画、阴影、圆角等所有视觉细节

❌ 禁止行为:
- 简化或"优化"源站结构
- 用 placeholder 替代源站图片  
- 省略重复内容
- 用通用 Tailwind 色替代精确色值

✅ 与 Mode 3 的区别:
- Mode 3 是创意设计模式，可以自由发挥
- Mode 1/2 是还原模式，必须忠实于源站/设计稿
```

---

## Shared Constraints (HTML & react_template)

**⚠️ 重要**: 以下约束条件适用于**所有输出格式**，确保不同格式的输出达到**100% 相同的还原度**。

### Structure Source by Mode

**⚠️ CRITICAL**: 不同 Mode 的结构来源不同

| Mode | 结构来源 | 说明 |
|------|---------|------|
| **Mode 1** (URL Clone) | `source.html` | 从网站提取的原始 HTML |
| **Mode 2** (Screenshot) | 截图分析（思考步骤） | AI 分析截图后直接生成 React |
| **Mode 3** (Requirements) | `design_system.json` + 组件语义决策 | 设计系统 + 组件选型思考 |

### Mode 2 特殊说明 (Screenshot to Code)

```
⚠️ Mode 2 只生成 React，不生成 HTML

Screenshot → [AI 分析截图] → design_system.json
                  ↓
            [结构分析 - 思考步骤]
                  ↓
              React 代码

分析要点：
- 布局结构（flex/grid 方向、嵌套层级）
- 组件识别（卡片、列表、表单等）
- 交互推断（Tab、轮播、展开收起等）
- 颜色/字体/间距提取到 design_system.json
```

### Mode 3 特殊说明 (Requirements to Code)

```
⚠️ Mode 3 只生成 React，不生成 HTML

用户需求 → [Design Thinking] → design_system.json
                  ↓
         [组件语义决策 - 思考步骤]
                  ↓
              React 代码

决策要点：
- 数据展示：List Item vs Card vs Table
- 容器选择：Dialog vs Sheet vs 新页面
- 高端设计：视觉层次、质感纹理、动效节奏
```

### Shared Input Resources

无论生成 HTML 还是 react_template，都必须基于以下**相同的输入资源**：

**Mode 1 (URL Clone)**:
```
<project>_content/
├─ source.html             ← ⭐ DOM 结构来源（最重要！）
├─ screenshot.png          ← 视觉参考
├─ design_system.json      ← 精确颜色、字体、间距
├─ DESIGN_SYSTEM.md        ← 精确尺寸测量
├─ image_manifest.json     ← 真实图片URL映射
├─ images/                 ← 下载的图片文件
└─ css/                    ← 原始样式表
```

**Mode 2 (Screenshot)**:
```
docs/
├─ design_system.json      ← 推断的设计令牌

输入:
├─ screenshot.png          ← 原始截图（AI 直接分析）
```

**Mode 3 (Requirements)**:
```
docs/
├─ design_system.json      ← 设计令牌 (Single Source of Truth)

输入:
├─ 用户需求文本            ← 功能和设计要求
```

---

## Rule 0: Structure Constraint (MOST CRITICAL)

```
❌ 禁止: 自己随意设计布局
✅ 必须: 严格参照结构来源

结构来源 (Single Source of Truth)：
- Mode 1: source.html（提取的原始 HTML）
- Mode 2: 截图分析（思考步骤，不输出文件）
- Mode 3: 组件语义决策（思考步骤，不输出文件）

规则：
- 读取结构来源，分析其 DOM 层级结构
- 保持相同的嵌套层级和元素顺序
- 保持相同的 class 命名模式（可转换为 Tailwind）
- 保持相同的语义化标签 (header, nav, main, section, footer)
```

**示例**:

source.html (Mode 1) 或截图分析结果 (Mode 2):
```html
<header class="sticky top-0 z-50">
  <nav class="container mx-auto flex items-center justify-between h-16">
    <div class="logo">...</div>
    <div class="nav-links flex gap-6">...</div>
    <div class="nav-actions">...</div>
  </nav>
</header>
```

HTML 输出: 保持完全相同的结构

React 输出: 
```tsx
<header className="sticky top-0 z-50">
  <nav className="container mx-auto flex items-center justify-between h-16">
    <div className="logo">...</div>
    <NavigationMenu>...</NavigationMenu>  // 用 shadcn 组件替换
    <div className="nav-actions">
      <Button>...</Button>  // 用 shadcn 组件替换
    </div>
  </nav>
</header>
```

---

## Rule 1: Color Constraint

```
❌ 禁止: 近似或推测颜色值
✅ 必须: 使用 design_system.json 中的精确 HEX 值
```

**示例**:
- design_system.json 中 primary: "#6366F1"
- HTML: `style="color: #6366F1"` 或 `class="text-[#6366F1]"`
- React: `className="text-[#6366F1]"` 或 `style={{ color: '#6366F1' }}`

**Code Example**:
```javascript
// ✅ CORRECT: Load from design_system.json
const colors = require('./codebuddy_content/design_system.json').colors;

// In Tailwind config:
tailwind.config = {
  theme: {
    extend: {
      colors: {
        'brand-primary': '#3B82F6',    // From design_system.json
        'brand-secondary': '#8B5CF6',  // From design_system.json
      }
    }
  }
}

// ❌ WRONG: Approximating colors
colors: {
  primary: '#3b82f6',  // Close but not exact
  primary: 'blue-500', // Generic Tailwind color
}
```

---

## Rule 2: Size Constraint

```
❌ 禁止: 使用模糊的 Tailwind 类 (p-4, text-lg)
✅ 必须: 使用 DESIGN_SYSTEM.md 或 source.html 中的精确 px 值
```

**示例**:
- source.html 中 `style="padding: 80px"` 或 `class="py-20"`
- HTML: `class="py-[80px]"` 或 `style="padding: 80px"`
- React: `className="py-[80px]"`

---

## Rule 3: Image Constraint (CRITICAL)

```
❌ 禁止: 使用占位图 (placehold.co, source.unsplash.com/random)
✅ 必须: 使用 image_manifest.json 中的真实图片 URL
```

**示例**:
- image_manifest.json 中 hero.jpg → "images/hero.jpg"
- HTML: `<img src="<project>_content/images/hero.jpg">`
- React: `<img src="/images/hero.jpg">` (需先复制到 public/)

**Image Mapping Process** (MANDATORY):
1. **BEFORE generating any code**: Read `<content_dir>/image_manifest.json`
2. The manifest maps: `original_url` → `local_path`
3. **IMPORTANT**: Local paths are relative to content_dir, so prefix them with `<content_dir>/`
4. Example:
   ```json
   {
     "original_url": "https://example.com/hero.jpg",
     "local_path": "images/img_abc123.jpg"
   }
   ```
   **Use**: `<img src="codebuddy_content/images/img_abc123.jpg" />`

**Common Mistake to Avoid**:
```html
<!-- ❌ WRONG: Missing content_dir -->
<img src="images/img_abc123.jpg" />

<!-- ✅ CORRECT: Include content_dir -->
<img src="codebuddy_content/images/img_abc123.jpg" />
```

---

## Rule 4: Font Constraint

```
❌ 禁止: 使用默认字体或随意选择
✅ 必须: 使用 design_system.json 中指定的字体
```

**示例**:
- design_system.json 中 fontFamily: "Inter"
- HTML: `font-family: 'Inter', sans-serif`
- React: 在 index.css 中配置相同字体

**Code Example**:
```html
<!-- ✅ CORRECT: Load from design_system.json -->
<!-- Primary font: Inter, weights: 400,600,700,800 -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}
</style>

<!-- ❌ WRONG: System default -->
<style>
body {
  font-family: sans-serif;
}
</style>
```

---

## Rule 5: Content Constraint

```
❌ 禁止: 修改或"优化"原始文案
✅ 必须: 从 source.html 提取并完全保留原网站的文案内容
```

---

## Rule 6: Deep Analysis Data (Precise Measurements)

**MANDATORY**: When generating input/textarea elements, buttons, or containers:

1. **Read `DESIGN_SYSTEM.md`** to find "🔬 Precise Element Measurements" section
2. **Use the exact measurements provided**, don't guess from screenshot

**Example from DESIGN_SYSTEM.md**:
```markdown
### Hero Input/Textarea
- Element: TEXTAREA, 700×80px
- Padding: 8px (py-2)
- Min Height: 80px (min-h-[80px])
- Border Radius: 20px (rounded-[20px])
- Container: padding 24px (p-6), radius 28px (rounded-[28px])
```

**Correct Implementation**:
```html
<!-- ✅ CORRECT: Using deep analysis data -->
<div class="p-6 rounded-[28px] bg-white shadow-lg">
  <textarea 
    class="w-full min-h-[80px] py-2 px-4 rounded-[20px] border-0 resize-none"
    placeholder="Enter your prompt..."
  ></textarea>
  <button class="px-6 py-3 rounded-full">Submit</button>
</div>

<!-- ❌ WRONG: Guessing from screenshot -->
<div class="p-8 rounded-xl bg-white">
  <input type="text" class="w-full py-5" />
</div>
```

**Why This Matters**:
- Screenshot-based guessing: ~60% accuracy
- Deep analysis data: ~98% accuracy
- Prevents height/padding mismatches

---

## Rule 7: Complete Code - NO Placeholders

```html
<!-- ❌ WRONG: Placeholder comments -->
<div class="features">
  <div class="feature">Feature 1</div>
  <!-- Add more features here -->
</div>

<!-- ✅ CORRECT: Complete implementation -->
<div class="features">
  <div class="feature">Feature 1</div>
  <div class="feature">Feature 2</div>
  <div class="feature">Feature 3</div>
  <div class="feature">Feature 4</div>
</div>
```

---

## Rule 8: Modular Implementation

**⚠️ IMPORTANT**: Generate code section by section, not all at once.

**Process**:
1. Generate **Header** section → User reviews
2. Generate **Hero** section → User reviews  
3. Generate **Features** section → User reviews
4. Generate **CTA** section → User reviews
5. Generate **Footer** section → User reviews

**Benefits**:
- User can provide feedback after each section
- Easier to match original layout precisely
- Prevents overwhelming single large code block
- Allows iterative refinement

---

## Pre-Generation Checklist

**MUST complete BEFORE writing ANY code**:

```markdown
Before generating code, I MUST read:
- [ ] screenshot.png (understand layout)
- [ ] source.html (get DOM structure) ⭐ CRITICAL
- [ ] design_system.json (get exact colors/fonts/spacing)
- [ ] DESIGN_SYSTEM.md (get deep analysis measurements) ⭐ CRITICAL
- [ ] image_manifest.json (get image path mappings)

Deep Analysis Data Location:
- File: <content_dir>/DESIGN_SYSTEM.md
- Section: "🔬 Precise Element Measurements"
- Contains: Exact px values for inputs, containers, buttons
```

---

## Code Generation Template

**For each section, follow this exact process**:

```markdown
## Section: Hero

### 1. Read Required Data
- ✅ Read DESIGN_SYSTEM.md → Found: Hero Input is TEXTAREA, 700×80px, padding 8px, min-height 80px
- ✅ Read image_manifest.json → Found: hero background = images/img_abc123.jpg
- ✅ Read design_system.json → Primary color: #3B82F6

### 2. Generate Code
```html
<section class="relative min-h-screen flex items-center justify-center"
         style="background-image: url('codebuddy_content/images/img_abc123.jpg')">
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-[28px] shadow-xl">
    <h1 class="text-4xl font-bold mb-6" style="color: #3B82F6">
      Welcome to CodeBuddy
    </h1>
    
    <!-- Using deep analysis data: min-h-[80px], py-2 -->
    <textarea 
      class="w-full min-h-[80px] py-2 px-4 rounded-[20px] border border-gray-300 resize-none"
      placeholder="Enter your prompt..."
    ></textarea>
    
    <button class="mt-4 px-6 py-3 rounded-full font-semibold"
            style="background-color: #3B82F6; color: white">
      Generate
    </button>
  </div>
</section>
```

### 3. Explain Decisions
- ✅ Used TEXTAREA (not input) based on deep analysis
- ✅ Used min-h-[80px] and py-2 from measurements
- ✅ Used local image path with content_dir prefix
- ✅ Used exact hex color #3B82F6 from design_system.json
```

---

## Common Mistakes to Avoid

```html
<!-- ❌ MISTAKE 1: Wrong image path -->
<div style="background-image: url('images/img_abc123.jpg')">
  <!-- Missing content_dir prefix! -->
</div>

<!-- ❌ MISTAKE 2: Guessing dimensions -->
<input type="text" class="py-5">
  <!-- Should be textarea with min-h-[80px] py-2 -->

<!-- ❌ MISTAKE 3: Generic colors -->
<button class="bg-blue-500">
  <!-- Should use exact hex from design_system.json -->

<!-- ✅ CORRECT VERSION -->
<div style="background-image: url('codebuddy_content/images/img_abc123.jpg')">
  <textarea class="min-h-[80px] py-2"></textarea>
  <button style="background-color: #3B82F6"></button>
</div>
```

---

## Stack-Specific Guidelines

### For HTML + Tailwind:
```html
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
```

### For React + Tailwind:
```html
<script src="https://cdn.jsdelivr.net/npm/react@18.0.0/umd/react.development.js"></script>
<script src="https://cdn.jsdelivr.net/npm/react-dom@18.0.0/umd/react-dom.development.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@babel/standalone/babel.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
```

### For Vue + Tailwind:
```html
<script src="https://registry.npmmirror.com/vue/3.3.11/files/dist/vue.global.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
```

---

## Vision Analysis Checklist (Mode 2 Only)

> **注意**: 此清单仅适用于 **Mode 2 (Screenshot)** 模式。Mode 1 使用 source.html，Mode 3 基于组件语义决策。

Before writing any code, carefully analyze the screenshot for:

### Layout & Structure
- [ ] Overall page layout (header, main content, sidebar, footer)
- [ ] Grid system or flexbox patterns
- [ ] Component hierarchy and nesting
- [ ] Sectioning and grouping of elements

### Typography
- [ ] Font families (serif, sans-serif, monospace)
- [ ] Font sizes (headings, body, small text)
- [ ] Font weights (normal, bold, light)
- [ ] Line heights and letter spacing
- [ ] Text alignment (left, center, right, justify)

### Colors & Styling
- [ ] Background colors (solid, gradients)
- [ ] Text colors and contrast ratios
- [ ] Border colors and styles
- [ ] Shadow effects
- [ ] Hover states (if visible in screenshot)

### Spacing & Sizing
- [ ] Padding (internal spacing)
- [ ] Margins (external spacing)
- [ ] Gap between elements
- [ ] Element widths and heights
- [ ] Border radius for rounded corners

### Visual Elements
- [ ] Images (size, aspect ratio, placement)
- [ ] Icons (type, size, color)
- [ ] Buttons (style, size, hover effects)
- [ ] Forms (inputs, labels, validation)
- [ ] Cards and containers
- [ ] Navigation elements

---

## Common Layout Patterns

> **注意**: 以下示例使用 `[#HEX]` 语法作为教学参考。**实际项目中必须使用 design_system.json 中的精确颜色值**。

### Centered Content
```html
<div class="flex items-center justify-center min-h-screen">
  <div class="max-w-4xl mx-auto px-4">
    <!-- Centered content -->
  </div>
</div>
```

### Card Grid Layout
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <div class="bg-white rounded-lg shadow-md p-6">
    <!-- Card content -->
  </div>
  <!-- More cards -->
</div>
```

### Navigation Bar
```html
<nav class="bg-[#1a1a2e] text-white">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <div class="flex items-center">
        <span class="text-xl font-bold">Logo</span>
      </div>
      <div class="hidden md:flex space-x-4">
        <a href="#" class="hover:bg-[#2a2a3e] px-3 py-2 rounded">Home</a>
        <a href="#" class="hover:bg-[#2a2a3e] px-3 py-2 rounded">About</a>
      </div>
    </div>
  </div>
</nav>
```

### Hero Section
```html
<section class="bg-gradient-to-r from-[#6366f1] to-[#8b5cf6] text-white py-20">
  <div class="max-w-4xl mx-auto text-center px-4">
    <h1 class="text-5xl font-bold mb-4">Hero Title</h1>
    <p class="text-xl mb-8">Compelling subtitle text</p>
    <button class="bg-white text-[#6366f1] px-8 py-3 rounded-full font-semibold">
      Call to Action
    </button>
  </div>
</section>
```

---

## Validation Checklist

After generating code, verify:

### Structure
- [ ] DOM 层级结构与 source.html 一致
- [ ] 元素顺序与 source.html 一致
- [ ] 语义标签保持

### Styling
- [ ] 颜色使用精确 HEX 值
- [ ] 尺寸使用精确 px 值
- [ ] 字体正确加载

### Assets
- [ ] 图片使用本地路径
- [ ] 图片路径包含 content_dir 前缀
- [ ] 无占位图 URL

### Content
- [ ] 文案完全保留
- [ ] 无 placeholder 注释
- [ ] 所有区块完整实现
