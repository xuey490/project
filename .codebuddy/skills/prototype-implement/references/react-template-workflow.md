# React Template Workflow Guide

## Overview

`react_template` 是 prototype-master 的**默认输出格式**，基于项目的 frontend template 生成生产级 React 代码。

**所有三种模式 (URL Clone, Screenshot, Requirements) 都只输出 React 代码。**

**⚠️ 重要**: 生成的代码必须与 `web-implement` skill 的产物格式完全对齐，确保能够无缝融入完整的 Web 项目。

**📖 组件使用规范**: 详见 [frontend-patterns.md](frontend-patterns.md)

---

## 🎯 核心开发理念: 多页面优先 + 组件化

### 多页面优先原则

```
┌──────────────────────────────────────────────────────────────────┐
│  ⛔ 禁止: 将所有内容塞进一个巨大的页面组件                        │
│  ✅ 必须: 每个导航链接对应 pages/ 下的独立 .tsx 文件              │
│  ✅ 必须: 提取共享组件到 components/ 目录                        │
└──────────────────────────────────────────────────────────────────┘
```

**为什么多页面优先？**
- 代码可维护性更高
- 路由清晰，用户体验好
- 便于后续扩展和迭代

**页面数量要求**:
| 项目规模 | 最少页面数 | 典型页面 |
|---------|----------|---------|
| 小型网站 | 5 页 | Home, About, Contact, Pricing, Features |
| 中型网站 | 8 页 | + Blog, Solutions, Team, FAQ |
| 大型网站 | 10+ 页 | + 子页面、详情页 |

### 组件化开发思想

**组件提取决策树**:
```
组件是否在多个页面使用？
    │
    ├─ Yes → 提取到 components/
    │         │
    │         ├─ 布局组件 (Header/Footer) → components/layout/
    │         ├─ 业务组件 (Card/List) → components/[domain]/
    │         └─ 通用组件 (Button变体) → components/shared/
    │
    └─ No → 保留在页面文件内部
```

**必须提取的组件**:
| 组件 | 目录 | 原因 |
|------|------|------|
| Header | `components/layout/` | 所有页面共享导航栏 |
| Footer | `components/layout/` | 所有页面共享页脚 |
| MobileMenu | `components/layout/` | 移动端菜单 |
| AuthDialogs | `components/auth/` | Login/Signup 模态框 |

---

## ⚠️ CRITICAL: 与 Interactive Prototype 相同的约束

**react_template 和 Interactive Prototype 核心约束一致，仅产物形式不同：**

| 约束 | Interactive Prototype | react_template |
|------|----------------------|----------------|
| **产物** | React 组件 + 路由 | React 组件 + 路由 |
| **页面数量** | 至少 5-8 页 | 至少 5-8 页 |
| **导航系统** | JavaScript navigateTo() | React Router |
| **交互组件** | 原生 JS Modal/Toast | shadcn/ui Dialog/Toast |
| **禁止脚本** | ✅ 禁止 Python 脚本 | ✅ 禁止 Python 脚本 |

### 必须满足

```markdown
- [ ] 每个导航链接都有对应的完整页面 (至少 5-8 页)
- [ ] Header/Footer 提取为共享组件
- [ ] 所有页面使用 `design_system.json` 中的设计系统
- [ ] Login/Signup 使用 shadcn/ui Dialog 组件
- [ ] Toast 通知使用 shadcn/ui Toast/Sonner 组件
- [ ] 表单使用 react-hook-form + zod 验证
- [ ] 移动端响应式布局
- [ ] 无 TypeScript/控制台错误
```

### 交互组件映射

**常见交互组件的 shadcn/ui 实现：**

| 交互需求 | shadcn/ui 组件 |
|---------|---------------|
| Login/Signup 弹窗 | `<Dialog>` |
| Toast 通知 | `toast()` from sonner |
| 移动端菜单 | `<Sheet>` |
| 表单验证 | `react-hook-form` + `zod` |
| 按钮交互 | `<Button variant="...">` |

**📖 完整组件映射和用法**: [frontend-patterns.md](frontend-patterns.md)

---

## Technology Stack

- React 19 + TypeScript
- Vite 7 构建工具
- Tailwind CSS 4
- shadcn/ui 组件库 (53+ 组件)
- react-router-dom v7 路由
- TanStack Query v5 状态管理
- react-hook-form + zod 表单验证
- framer-motion 动画
- lucide-react 图标

---

## Directory Structure (ALIGNED WITH web-implement)

**⚠️ CRITICAL**: prototype-master 生成的 React 代码必须遵循以下目录结构：

```
frontend/
├── package.json
├── vite.config.ts
├── components.json             # shadcn/ui config
├── index.html
├── public/
│   └── images/                 # 提取的图片资源
└── src/
    ├── main.tsx                # Entry point
    ├── App.tsx                 # Root component with providers + 路由配置
    ├── index.css               # Global styles + Tailwind theme
    ├── components/
    │   ├── layout/             # ⭐ 布局组件 (必须提取)
    │   │   ├── Header.tsx      # 导航栏 - 所有页面共享
    │   │   ├── Footer.tsx      # 页脚 - 所有页面共享
    │   │   └── MobileMenu.tsx  # 移动端菜单
    │   ├── auth/               # ⭐ 认证组件
    │   │   ├── LoginDialog.tsx
    │   │   └── SignupDialog.tsx
    │   ├── shared/             # ⭐ 通用业务组件
    │   ├── [domain]/           # ⭐ 领域特定组件 (按业务领域)
    │   └── ui/                 # shadcn/ui (53 components, 不要修改)
    ├── hooks/
    │   └── use-mobile.ts       # 移动端检测 (已存在)
    ├── lib/
    │   └── utils.ts            # cn utility
    ├── pages/                  # ⭐ 页面组件 (每个路由一个文件)
    │   ├── Index.tsx           # 首页 /
    │   ├── About.tsx           # 关于 /about
    │   ├── Pricing.tsx         # 定价 /pricing
    │   └── NotFound.tsx        # 404页面 (已存在)
    └── types/                  # ⭐ 类型定义目录
```

---

## Prototype vs Full Web Application

**⚠️ 重要区分**: prototype-master 生成的是**纯前端原型**，不包含后端功能。

### 原型场景 (prototype-master)

**原型特点**:
- ❌ 无后端 API 调用
- ❌ 无数据库
- ❌ 无真实认证
- ✅ 纯前端交互 (模态框、表单验证、页面导航)
- ✅ 模拟数据 (硬编码或 localStorage)
- ✅ Demo 模式登录 (无真实 OAuth)

### 从原型升级到完整应用

当需要将原型升级为完整 Web 应用时，使用 `web-implement` skill：

1. **保留前端代码** - prototype-master 生成的 pages、components 可直接复用
2. **添加后端** - web-implement 会创建 `backend/` 目录
3. **添加 API 层** - 创建 `lib/api-client.ts` 和数据 hooks
4. **替换模拟数据** - 将硬编码数据替换为 API 调用

---

## Output Files

When using react_template, produce:
- `frontend/src/pages/*.tsx` - React pages (shadcn/ui components)

---

## Workflow Diagram

```
Mode 1 (URL Clone):
Phase 1: web_content_fetcher.py ──┐
                                  │
                                  ▼
             ┌──────────────────────┐
             │  source.html (结构)   │
             │  design_system (样式) │
             │  images (资源)        │
             └───────────┬──────────┘
                         │
                         ▼
             ┌──────────────────────┐
             │   React 代码生成      │
             │   source.html 结构    │
             │   + shadcn 组件       │
             │   + 精确样式          │
             └──────────────────────┘

Mode 2/3 (Screenshot/Requirements):
Phase 1: AI 分析 ─────────────────┐
                                  │
                                  ▼
             ┌──────────────────────┐
             │  design_system.json  │
             │  (AI 生成)           │
             └───────────┬──────────┘
                         │
                         ▼
             ┌──────────────────────┐
             │   React 代码生成      │
             │   + shadcn 组件       │
             │   + 图片替换          │
             └──────────────────────┘
```

---

## Phase T0: Environment Preparation (CRITICAL)

**目标**: 确保 frontend 项目从模板正确初始化

### Step T0.1: Detect and Initialize Frontend Project

```bash
# 检测 frontend/ 目录是否存在
if [ ! -d "frontend" ]; then
    echo "🔧 Initializing frontend from template..."
    .genie/scripts/bash/setup-project.sh frontend
    cd frontend && npm install
    cd ..
fi
```

### Step T0.2: Copy Image Assets

```bash
# ⚠️ 图片放在 public/images/ (与 web-implement 对齐)
mkdir -p frontend/public/images
cp -r <project>_content/images/* frontend/public/images/

# React 中引用
<img src="/images/hero.jpg" />
```

**⚠️ 重要**: 不要使用 `src/assets/`，统一使用 `public/images/`

---

## Phase T1: Analyze Structure Source (CRITICAL)

**目标**: 从结构来源文件提取 DOM 结构，作为 React 组件的蓝图

### ⚠️ CRITICAL: Structure Source by Mode

| Mode | 结构来源 | 说明 |
|------|---------|------|
| **Mode 1** (URL Clone) | `source.html` + `structure_analysis.md` | 必须先输出 structure_analysis.md 文件 |
| **Mode 2** (Screenshot) | 截图 + `design_system.json` (扩展版) | design_system.json 必须包含 gradients/shadows/effects |
| **Mode 3** (Requirements) | 组件语义决策（思考步骤） | 基于 design_system.json + 组件选型 |

```
⚠️ Mode 1 特别注意:
- 必须先读取 clone_blueprint.md, image_manifest.json, element_colors.json
- 必须输出 structure_analysis.md 文件
- 图片路径必须从 image_manifest.json 查表获取
- 必须运行 validate_clone.py 验证

⚠️ Mode 2 特别注意:
- 只生成 React，不生成 HTML
- design_system.json 必须包含 gradients/shadows/effects 扩展字段
- 代码生成时：渐变用 style 属性，阴影用精确值，hover 效果必须实现
- 禁止使用 Tailwind 默认值近似（shadow-lg, bg-gray-900 等）

⚠️ Mode 3 特别注意:
- 只生成 React，不生成 HTML
- 基于 design_system.json 和组件语义决策
- 组件选型是思考步骤，不输出文件
```

### Step T1.1: Read Design System Data

```bash
# 读取精确颜色值
cat <project>_content/design_system.json

# 读取图片映射
cat <project>_content/image_manifest.json
```

---

## Phase T2: Component Mapping & Page Generation

**目标**: 将 source.html 的结构转换为 React + shadcn/ui 组件

**核心原则**: 结构来自 source.html，交互组件用 shadcn 替换

### Source → React 转换规则

| source.html 元素 | React 替换 | 何时替换 |
|-----------------|-----------|---------|
| `<button>` | `<Button>` | 总是替换 |
| `<input>` | `<Input>` | 总是替换 |
| `<select>` | `<Select>` | 总是替换 |
| `<dialog>` / modal | `<Dialog>` | 总是替换 |
| `<a href>` | `<Link to>` | SPA 内部导航时 |
| `<header>` | 保持 `<header>` | 不替换，保持语义 |
| `<section>` | 保持 `<section>` | 不替换，保持语义 |
| `<div>` 布局容器 | 保持 `<div>` | 不替换，保持结构 |

**📖 完整组件映射表和代码示例**: [frontend-patterns.md](frontend-patterns.md)

### Structure Conversion Example

**source.html**:
```html
<header class="sticky top-0 z-50 bg-white border-b">
  <nav class="container mx-auto px-6 h-16 flex items-center justify-between">
    <a href="/">Logo</a>
    <div class="flex gap-4">
      <button class="text-gray-600">Sign In</button>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Get Started</button>
    </div>
  </nav>
</header>
```

**React + shadcn (组件 + design_system.json 样式)**:
```tsx
<header className="sticky top-0 z-50 bg-[#FFFFFF] border-b border-[#E5E7EB]">
  <nav className="container mx-auto px-[24px] h-[64px] flex items-center justify-between">
    <Link to="/">Logo</Link>
    <div className="flex gap-[16px]">
      <Button variant="ghost" className="text-[#6B7280]">Sign In</Button>
      <Button variant="ghost" className="bg-[#6366F1] text-white px-[16px] py-[8px] rounded-[8px]">
        Get Started
      </Button>
    </div>
  </nav>
</header>
```

**关键点**:
- ✅ 结构保持不变
- ✅ 所有颜色使用 `[#HEX]` 格式，来自 design_system.json
- ✅ `<a>` → `<Link>`, `<button>` → `<Button>`
- ❌ 不使用 Tailwind 默认颜色

---

## Phase T4: Build Verification & Auto-Fix (MANDATORY)

**⚠️ 此阶段为强制性步骤，未通过编译验证的代码禁止交付**

### Step T4.1: 运行生产构建验证

```bash
cd frontend && npm run build 2>&1
```

### Step T4.2: 自动修复循环 (最多 3 次)

```
Attempt 1: npm run build
    │
    ├─ 成功 → ✅ 验证通过
    │
    └─ 失败 → 分析错误 → 修复代码 → 重试 (最多 3 次)
```

### 常见错误修复

| 错误类型 | 示例 | 修复方式 |
|---------|------|---------|
| **Import Error** | `Cannot find module '@/components/ui/xxx'` | 检查组件是否存在，修正路径 |
| **Type Error** | `Property 'xxx' does not exist` | 添加正确的类型定义 |
| **JSX Error** | `'xxx' is not defined` | 添加缺失的 import |
| **Image 404** | `GET /images/hero.jpg 404` | 确保图片已复制到 `public/images/` |

### 验证通过标准

- [ ] `npm run build` 退出码为 0
- [ ] 无 TypeScript 编译错误
- [ ] `frontend/dist/` 目录包含 `index.html`

---

## Phase T5: Route Configuration

更新 `frontend/src/App.tsx`:

```tsx
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Index from "./pages/Index";
import About from "./pages/About";
import Pricing from "./pages/Pricing";
import NotFound from "./pages/NotFound";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" data-genie-key="Home" data-genie-title="Home Page" element={<Index />} />
        <Route path="/about" data-genie-key="About" data-genie-title="About Us" element={<About />} />
        <Route path="/pricing" data-genie-key="Pricing" data-genie-title="Pricing" element={<Pricing />} />
        <Route path="*" data-genie-key="NotFound" data-genie-title="Not Found" element={<NotFound />} />
      </Routes>
    </BrowserRouter>
  );
}
```

**⚠️ CRITICAL: Route 属性要求**

每个 Route 必须包含以下属性，用于生成 `docs/pages.json`：

| 属性 | 说明 | 示例 |
|------|------|------|
| `data-genie-key` | 页面唯一标识 (PascalCase) | `"Home"`, `"AboutUs"`, `"Pricing"` |
| `data-genie-title` | 页面标题 (用于导航/SEO) | `"Home Page"`, `"About Us"`, `"定价方案"` |

---

## Checklist

### 多页面优先 (CRITICAL)

- [ ] 🔴 是否为每个导航链接创建了独立的页面文件？
- [ ] 🔴 页面数量是否达到 5-8 个？
- [ ] 🔴 `App.tsx` 是否配置了所有页面的路由？

### 组件化 (CRITICAL)

- [ ] 🔴 Header 是否提取到 `components/layout/Header.tsx`？
- [ ] 🔴 Footer 是否提取到 `components/layout/Footer.tsx`？
- [ ] 🔴 Login/Signup Dialog 是否提取到 `components/auth/`？

### Structure Source Selection

- [ ] 🔴 **Mode 1**: 是否读取了 `source.html`？
- [ ] 🔴 **Mode 2**: 是否分析了截图的布局和组件结构？
- [ ] 🔴 **Mode 3**: 是否完成了组件语义决策？

### Style Compliance

- [ ] 🔴 所有颜色使用 `[#HEX]` 格式，来自 design_system.json
- [ ] 🔴 **没有** Tailwind 默认颜色（blue-500, gray-700 等）
- [ ] 🔴 图片路径使用 `/images/xxx` (来自 public/images/)

### Build Verification

- [ ] 🔴 `npm run build` 无错误
- [ ] 🔴 `frontend/dist/` 包含 `index.html`

**📖 完整验证清单**: [quality-checklist.md](quality-checklist.md)
**📖 组件使用规范**: [frontend-patterns.md](frontend-patterns.md)
