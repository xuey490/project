# Quality Checklist - 原型质量验证清单

> **快速入口**: 基础检查项请查看 [SKILL.md → Quick Checklist](../SKILL.md#quick-checklist)
>
> **本文档专注于**: Scoring System、Build Validation、Component Compliance 的详细说明

---

## Scoring System

### 计算方式

```
总分 = (颜色分 × 0.25) + (布局分 × 0.25) + (内容分 × 0.20) + (图片分 × 0.20) + (字体分 × 0.10)
```

### 评分标准

| 分数区间 | 状态 | 行动 |
|---------|------|------|
| 85-100 | ✅ PASS | 可交付 |
| 70-84 | ⚠️ WARN | 修复 HIGH 优先级问题后重新验证 |
| < 70 | ❌ FAIL | 需要重大修改，审查 Phase 1-3 数据 |

### 各类别评分要点

| Category | 权重 | 评分要点 |
|----------|------|---------|
| **Colors** | 25% | 精确 hex 值、自定义颜色、无通用类、渐变正确 |
| **Layout** | 25% | 所有 section 存在、顺序正确、间距匹配、响应式 |
| **Content** | 20% | 无 placeholder、标题正确、按钮标签、完整复制 |
| **Images** | 20% | 本地图片、无外部 URL、数量匹配、Alt 正确 |
| **Typography** | 10% | 字体加载、weight 可用、字号/行高匹配 |

### 最低通过标准

必须满足以下所有条件才能 PASS：
- [ ] 无 Unsplash/placehold.co 图片 URL
- [ ] 无通用 Tailwind 颜色 (必须使用自定义)
- [ ] 无 placeholder 文本 ("Lorem ipsum")
- [ ] 所有主要 section 存在
- [ ] 正确字体已加载
- [ ] 导航系统完整（至少 5 页）
- [ ] 无 JavaScript 控制台错误
- [ ] **所有交互组件已实现**（Tab、轮播、选择器等不能静态化）
- [ ] **交互状态正确切换**（.active/.selected 样式生效）

---

## React Template Build Validation (阻断性)

**⚠️ 此项为阻断性检查，未通过则整体 FAIL，不计入评分系统**

### 验证流程

```
┌─────────────────────────────────────────┐
│  Attempt 1: npm run build               │
└──────────────────┬──────────────────────┘
                   │
         ┌─────────┴─────────┐
         │ 构建成功?          │
         └─────────┬─────────┘
                   │
         ┌────────┴────────┐
        Yes               No
         │                 │
         ▼                 ▼
   ✅ 验证通过      分析错误 → 修复代码
                          │
                  Attempt < 3?
                          │
                  ┌──────┴──────┐
                 Yes           No
                  │             │
                  ▼             ▼
             重新 build    ❌ 报告错误给用户
```

### 验证命令

```bash
# 完整构建验证 (必须执行)
cd frontend && npm run build 2>&1

# 仅类型检查 (可用于快速诊断)
cd frontend && npx tsc --noEmit

# 检查构建产物
ls -la frontend/dist/
```

### 常见编译错误及修复

| 错误 | 原因 | 修复 |
|------|------|------|
| Cannot find module '@/components/ui/xxx' | 引用了不存在的组件 | 检查 ui/ 目录，确认组件存在 |
| Property 'xxx' does not exist on type | TypeScript 类型错误 | 添加类型定义或使用 `?.` |
| JSX element has no closing tag | JSX 标签未闭合 | 检查标签闭合和嵌套 |
| Module not found | 缺少依赖或路径错误 | 检查 import 路径，运行 npm install |

### 通过标准

- [ ] `npm run build` 退出码为 0
- [ ] 无 TypeScript 编译错误 (error TS...)
- [ ] 无致命 ESLint 错误 (warnings 可接受)
- [ ] `frontend/dist/` 目录包含 `index.html`

---

## React Component Compliance

### Component Usage (Structure and Interaction)

| 元素类型 | 必须使用 | 禁止使用 |
|---------|---------|---------|
| Button | `<Button>` | 手写 `<button>` |
| Input | `<Input>` | 手写 `<input>` |
| Card | `<Card>` | 手写 div 容器 |
| Dialog | `<Dialog>` | 手写 modal div |
| Mobile Menu | `<Sheet>` | 手写 fixed div |
| Toast | `sonner.toast()` | `alert()` |
| Icons | `lucide-react` | Unicode emoji |
| Navigation | `<Link>` | `<a href="/">` |
| Forms | `react-hook-form` + `zod` | 手写 useState |

### Style Source (design_system.json)

- ✅ 所有颜色使用 `[#HEX]` 格式
- ❌ 禁止 Tailwind 默认颜色 (blue-500, gray-700, etc.)
- ✅ 组件样式通过 className 覆盖以匹配设计
- ✅ Border-radius、spacing、shadows 匹配设计

### Layout Compliance

- ✅ Header 提取为 `components/layout/Header.tsx`
- ✅ Footer 提取为 `components/layout/Footer.tsx`
- ✅ 每个页面使用 React Router `<Link>` 导航
- ✅ **每个 `<Route>` 必须有 `data-genie-key` 和 `data-genie-title`**

### Third-Party Libraries (强制场景)

| 场景 | 库 | 触发条件 |
|------|---|---------|
| 复杂表格 | `@tanstack/react-table` | 表格 > 10 行 + 排序/筛选 |
| 长列表 | `virtua` | 列表 > 50 项 |
| 文件上传 | `react-dropzone` | 需要拖拽上传 |
| 拖拽排序 | `@dnd-kit` | 需要拖拽排序 |

---

## 交互完整性验证 (Interaction Fidelity)

### Mode 1: 还原源站交互

> 必须读取 `interactions.json`，基于提取的交互信息进行验证

**检查要点**:
- 所有交互组件已识别（Tab、轮播、选择器、展开收起）
- 状态管理已实现（useState/useReducer）
- 事件绑定完整（onClick/onHover）
- 状态样式正确切换（.active/.selected）
- 与源站行为一致

### Mode 2: 推断合理交互

**从视觉线索推断**:

| 视觉线索 | 推断的交互 |
|---------|-----------|
| 多个头像排列 + 下方内容区 | 选择器联动 |
| Tab 样式的标签组 | Tab 切换 |
| 卡片有"探出"效果 | 轮播/滑动 |
| 问答/列表带箭头图标 | 展开/收起 |

### Mode 3: 设计高端交互

**必备交互组件**: Tab / 轮播 / Accordion / 表单验证 / Toast / Sheet

**交互质感要求**:
- 精致动效：按钮 hover 渐变、卡片悬浮阴影、入场动画
- 状态丰富：default/hover/active/focus/disabled 全覆盖
- 反馈及时：点击涟漪、加载骨架屏、成功提示
- 过渡流畅：ease-out 缓动、300ms 内完成

### 常见错误 - 交互丢失

```tsx
// ❌ WRONG - 将交互组件实现为静态布局
// 源站：9个头像可点击切换，一次显示3张评价卡片
// 错误实现：9张卡片全部静态显示
const Testimonials = () => {
  return (
    <div className="grid grid-cols-3 gap-4">
      {testimonials.map(t => <Card key={t.id}>...</Card>)}
    </div>
  );
};

// ✅ CORRECT - 保留交互行为
const Testimonials = () => {
  const [activeIndex, setActiveIndex] = useState(0);
  
  return (
    <>
      {/* 头像选择器 */}
      <div className="flex gap-2">
        {testimonials.map((t, i) => (
          <button
            key={t.id}
            onClick={() => setActiveIndex(i)}
            className={cn("rounded-full", activeIndex === i && "ring-2 ring-primary")}
          >
            <img src={t.avatar} />
          </button>
        ))}
      </div>
      
      {/* 内容区 - 显示选中的及相邻的 */}
      <div className="flex gap-4">
        {getVisibleItems(activeIndex).map(t => <Card key={t.id}>...</Card>)}
      </div>
    </>
  );
};
```

---

## Quick Validation Commands

```bash
# 颜色验证 - 检查是否有通用 Tailwind 颜色
grep -rn "bg-blue-\|bg-gray-\|text-blue-\|text-gray-" frontend/src/pages/

# 图片验证 - 检查是否有 placehold.co 残留
grep -rn "placehold.co" frontend/src/

# 构建验证
cd frontend && npm run build

# 检查产物
ls frontend/dist/index.html
```

---

## Validation Report Template

```markdown
# Prototype Validation Report

## Project: [项目名称]
## Date: [日期]

## Scores

| Category | Score | Status |
|----------|-------|--------|
| Colors | XX/100 | ✅/⚠️/❌ |
| Layout | XX/100 | ✅/⚠️/❌ |
| Content | XX/100 | ✅/⚠️/❌ |
| Images | XX/100 | ✅/⚠️/❌ |
| Typography | XX/100 | ✅/⚠️/❌ |
| **Overall** | **XX/100** | **✅/⚠️/❌** |

## Critical Issues

1. [HIGH] 描述问题 - 行号/位置
2. [HIGH] 描述问题 - 行号/位置

## Recommendations

1. [HIGH] 具体修复建议
2. [MEDIUM] 具体修复建议
```

**📖 基础检查项**: 请参考 [SKILL.md → Quick Checklist](../SKILL.md#quick-checklist)
