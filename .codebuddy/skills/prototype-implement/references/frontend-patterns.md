# Frontend Implementation Patterns (React + shadcn/ui)

## 核心原则：组件库 vs 样式分离

**组件库提供结构和交互，视觉样式完全由 design_system.json 决定**

> 这样设计是为了未来可替换性：shadcn/ui → TDesign / Ant Design / 其他组件库
> 切换时只需改组件导入和 API，视觉样式与组件库解耦

### 组件库的价值 (MUST USE)

| 组件库提供 | 说明 |
|-----------|------|
| **DOM 结构** | 语义化的组件结构 (Button, Dialog, Card) |
| **交互行为** | 键盘导航、焦点管理、ESC 关闭、Tab 切换 |
| **可访问性** | ARIA 属性、屏幕阅读器支持 |
| **状态管理** | loading, disabled, open/close |

### 视觉样式来源 (design_system.json)

| 样式属性 | 来源 | 写法 |
|---------|------|------|
| **颜色** | `colors.*` | `bg-[#HEX]` `text-[#HEX]` |
| **圆角** | `border_radius.*` | `rounded-[4px]` |
| **间距** | `spacing.scale` | `px-[16px]` `py-[12px]` |
| **字体** | `typography.*` | `text-[14px]` `font-medium` |
| **阴影** | 设计稿推断 | `shadow-[0_2px_8px_rgba()]` |

---

## Component Mapping Table (CRITICAL)

| 需求 | shadcn/ui 组件 | Import From |
|------|---------------|-------------|
| 按钮 | `<Button>` | `@/components/ui/button` |
| 图标按钮 | `<Button variant="ghost" size="icon">` | `@/components/ui/button` |
| 卡片容器 | `<Card>`, `<CardHeader>`, `<CardContent>` | `@/components/ui/card` |
| 弹窗/模态框 | `<Dialog>`, `<DialogContent>`, `<DialogTrigger>` | `@/components/ui/dialog` |
| 侧边抽屉 | `<Sheet>`, `<SheetContent>`, `<SheetTrigger>` | `@/components/ui/sheet` |
| 输入框 | `<Input>` | `@/components/ui/input` |
| 文本域 | `<Textarea>` | `@/components/ui/textarea` |
| 下拉选择 | `<Select>`, `<SelectContent>`, `<SelectItem>` | `@/components/ui/select` |
| 复选框 | `<Checkbox>` | `@/components/ui/checkbox` |
| 开关 | `<Switch>` | `@/components/ui/switch` |
| 表格 | `<Table>`, `<TableBody>`, `<TableCell>`, `<TableHead>`, `<TableRow>` | `@/components/ui/table` |
| 标签页 | `<Tabs>`, `<TabsList>`, `<TabsTrigger>`, `<TabsContent>` | `@/components/ui/tabs` |
| 手风琴 | `<Accordion>`, `<AccordionItem>`, `<AccordionTrigger>`, `<AccordionContent>` | `@/components/ui/accordion` |
| 下拉菜单 | `<DropdownMenu>`, `<DropdownMenuContent>`, `<DropdownMenuItem>` | `@/components/ui/dropdown-menu` |
| 通知提示 | `toast()` | `sonner` |
| 图标 | `<IconName />` | `lucide-react` |

### Available UI Components

```
accordion, alert-dialog, avatar, badge, button, calendar, card, carousel,
checkbox, dialog, drawer, dropdown-menu, form, input, label, pagination,
popover, progress, radio-group, select, separator, sheet, skeleton, slider,
sonner, spinner, switch, table, tabs, textarea, toggle, tooltip
```

---

## ⛔ 禁止的代码模式 (CRITICAL)

| 禁止 | 原因 | 正确做法 |
|------|------|---------|
| `<button className="...">` | 丢失交互和可访问性 | `<Button>` |
| `<input className="...">` | 丢失样式一致性 | `<Input>` |
| `<div className="...modal...">` | 丢失 ESC 关闭、焦点管理 | `<Dialog>` |
| `<div className="fixed inset-0">` | 手写移动端菜单 | `<Sheet>` |
| `alert()` | 阻塞 UI | `toast()` |
| `<a href="/">` | 刷新页面 | `<Link to="/">` |
| `bg-blue-500` | Tailwind 默认色 | `bg-[#HEX]` |
| 🚀 ✨ 📧 emoji | 降低设计专业感 | `lucide-react` |

```tsx
// ❌ 禁止
<button className="px-4 py-2 bg-blue-500">Click</button>
<div className="fixed inset-0 bg-black/50">Modal</div>
<input className="border rounded px-3 py-2" />
<a href="/about">About</a>

// ✅ 正确
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Link } from "react-router-dom";

<Button className="bg-[#6366F1] text-white">Click</Button>
<Dialog><DialogContent>...</DialogContent></Dialog>
<Input placeholder="Email" />
<Link to="/about">About</Link>
```

---

## Design System Token 映射

根据 design_system.json 中的令牌，生成对应的 Tailwind 类：

| 设计令牌 | 使用场景 | Tailwind 写法 |
|---------|---------|--------------|
| `colors.primary` | 主按钮、链接、CTA | `bg-[#HEX]` `text-[#HEX]` |
| `colors.secondary` | hover 状态、次要按钮 | `hover:bg-[#HEX]` |
| `colors.background` | 页面背景 | `bg-[#HEX]` |
| `colors.surface` | 卡片背景 | `bg-[#HEX]` |
| `colors.text_primary` | 标题、正文 | `text-[#HEX]` |
| `colors.text_secondary` | 描述、次要信息 | `text-[#HEX]` |
| `colors.border` | 边框、分割线 | `border-[#HEX]` |

### Mode 2 扩展字段 (MANDATORY)

> **Mode 2 的 design_system.json 必须包含以下扩展字段：**

```json
{
  "gradients": {
    "primary": "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
    "hero_bg": "linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%)"
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

**使用方式**：
```tsx
// 渐变必须用 style 属性
<section style={{ background: 'linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%)' }}>

// 阴影必须用精确值
<Card style={{ boxShadow: '0 4px 14px 0 rgba(102,126,234,0.39)' }}>
```

---

## Navigation (React Router)

```tsx
import { Link, useNavigate, useLocation } from "react-router-dom";

// 声明式导航
<Link to="/about">About</Link>

// 编程式导航
const navigate = useNavigate();
navigate("/about");
navigate(-1); // Go back

// 获取当前路由
const location = useLocation();
console.log(location.pathname);
```

---

## Form Handling (react-hook-form + zod)

```tsx
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";

// 1. 定义 Schema
const formSchema = z.object({
  email: z.string().email("请输入有效的邮箱地址"),
  password: z.string().min(6, "密码至少6个字符"),
});

// 2. 使用 Form
function LoginForm() {
  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: { email: "", password: "" },
  });

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <FormField
          control={form.control}
          name="email"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Email</FormLabel>
              <FormControl><Input {...field} /></FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <Button type="submit">Log In</Button>
      </form>
    </Form>
  );
}
```

---

## Toast Notifications (Sonner)

```tsx
import { toast } from "sonner";

toast.success("操作成功！");
toast.error("操作失败，请重试");
toast.promise(asyncOperation(), {
  loading: "处理中...",
  success: "操作成功！",
  error: "操作失败",
});
```

---

## Mobile Menu (Sheet Component)

```tsx
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { Menu } from "lucide-react";

<Sheet>
  <SheetTrigger asChild className="md:hidden">
    <Button variant="ghost" size="icon">
      <Menu className="h-6 w-6" />
    </Button>
  </SheetTrigger>
  <SheetContent side="right">
    <nav className="flex flex-col gap-4 mt-8">
      <Link to="/">Home</Link>
      <Link to="/about">About</Link>
    </nav>
  </SheetContent>
</Sheet>
```

---

## Icons (Lucide React)

```tsx
import { Bell, User, Settings, Search, Menu, X, ChevronRight } from "lucide-react";

// 基本使用
<Bell className="h-5 w-5" />

// 在按钮中使用
<Button variant="ghost" size="icon" aria-label="Notifications">
  <Bell className="h-5 w-5" />
</Button>

// 带文字
<Button>
  <Settings className="mr-2 h-4 w-4" />
  Settings
</Button>
```

---

## Component Composition Patterns

### ⛔ 禁止 Boolean Prop 泛滥

```tsx
// ❌ WRONG - 2^4 = 16 种可能状态
<MessageComposer isThread={true} isDM={false} isEditing={true} isReply={false} />

// ✅ CORRECT - 使用明确的变体组件
<ThreadComposer />
<DMComposer />
<EditComposer threadId={id} />
```

### ✅ 使用 Compound Component 模式

```tsx
// ✅ CORRECT
<Card>
  <CardHeader>
    <CardTitle>Dashboard</CardTitle>
    <CardDescription>Overview of your metrics</CardDescription>
  </CardHeader>
  <CardContent>{/* 内容 */}</CardContent>
  <CardFooter><Button>View Details</Button></CardFooter>
</Card>

// ❌ WRONG - 通过 props 传递所有内容
<Card title="Dashboard" description="Overview" content={...} footerButton="View Details" />
```

---

## ⛔⛔⛔ Animation Components (CRITICAL — Zero Tolerance)

> **🚨 此规则被反复违反！页面切换有动画但页面内容没有动画 = 任务失败！**

模板预置了三个动画组件，**必须全部使用**：

| 层级 | Component | Import | 用途 |
|------|-----------|--------|------|
| **App 层** | `AnimatedRoutes` | `@/components/AnimatedRoutes` | 包裹 `<Routes>`，使用 `mode="popLayout"` 实现流畅页面切换 |
| **Route 层** | `PageTransition` | `@/components/PageTransition` | 包裹每个页面组件，提供进入/退出动画（0.2s） |
| **页面内** | `MotionPrimitives` | `@/components/MotionPrimitives` | 页面内部组件动画：`FadeIn`, `Stagger`, `HoverLift` |

**⛔ 三层动画缺一不可！只用前两层 = 违反规则！**

### MotionPrimitives API

| Export | 类型 | 用途 | 示例 |
|--------|------|------|------|
| `FadeIn` | Component | 内容块入场动画 | `<FadeIn><h1>...</h1></FadeIn>` |
| `Stagger` | Component | 列表/网格逐个入场 | `<Stagger>{items.map(...)}</Stagger>` |
| `HoverLift` | Component | 悬停抬升效果 | `<HoverLift><Card>...</Card></HoverLift>` |
| `motion` | Re-export | framer-motion 的 motion | `<motion.div variants={fadeUp}>` |
| `fadeUp`, `fadeIn`, `fadeLeft`, `fadeRight`, `fadeDown` | Variant | 方向性淡入 | `variants={fadeUp}` |
| `scaleUp`, `blurIn` | Variant | 缩放/模糊入场 | `variants={scaleUp}` |

### ⛔ Navbar/Header/Sidebar 布局规则 (CRITICAL)

**Navbar / Header / Sidebar / 任何共享布局组件必须放在 `<AnimatedRoutes>` 外部**，否则每次页面切换都会：
- 销毁并重新创建这些组件
- 让这些组件参与页面过渡动画（抖动、闪烁、延迟）

### Required App.tsx Structure

```tsx
import { AnimatedRoutes } from "@/components/AnimatedRoutes";
import { PageTransition } from "@/components/PageTransition";

// ✅ 简单布局 — Navbar outside AnimatedRoutes
function App() {
  return (
    <BrowserRouter>
      {/* ✅ Navbar 在 AnimatedRoutes 外部，不参与动画 */}
      <Navbar />
      
      <AnimatedRoutes>
        <Route
          path="/"
          data-genie-key="Home"
          data-genie-title="Home"
          element={<PageTransition transition="fade"><Home /></PageTransition>}
        />
        <Route
          path="/about"
          data-genie-key="About"
          data-genie-title="About Us"
          element={<PageTransition transition="slide-up"><About /></PageTransition>}
        />
      </AnimatedRoutes>
    </BrowserRouter>
  );
}

// ✅ Dashboard 布局 — Sidebar + Header outside AnimatedRoutes
function App() {
  return (
    <BrowserRouter>
      <div className="flex min-h-screen">
        {/* ✅ Sidebar 在外部 */}
        <Sidebar />
        <div className="flex-1 flex flex-col">
          {/* ✅ Header 在外部 */}
          <Header />
          <main className="flex-1">
            <AnimatedRoutes>
              <Route path="/dashboard" data-genie-key="Dashboard" data-genie-title="Dashboard" element={<PageTransition><Dashboard /></PageTransition>} />
              <Route path="/settings" data-genie-key="Settings" data-genie-title="Settings" element={<PageTransition><Settings /></PageTransition>} />
            </AnimatedRoutes>
          </main>
        </div>
      </div>
    </BrowserRouter>
  );
}
```

**⚠️ WRONG patterns:**
```tsx
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

### ✅ Complete Page Component Example (MUST follow this pattern)

```tsx
// pages/Home.tsx
import { FadeIn, Stagger, HoverLift, fadeUp, motion } from "@/components/MotionPrimitives";
import { Card, CardHeader, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

function Home() {
  const features = [
    { id: 1, title: "Feature 1", desc: "Description 1" },
    { id: 2, title: "Feature 2", desc: "Description 2" },
    { id: 3, title: "Feature 3", desc: "Description 3" },
  ];
  
  return (
    <main className="container mx-auto px-4 py-16">
      {/* ✅ Hero 区域用 FadeIn */}
      <FadeIn className="text-center mb-16">
        <h1 className="text-4xl font-bold mb-4">Welcome to Our App</h1>
        <p className="text-xl text-muted-foreground mb-8">
          The best solution for your needs
        </p>
        {/* ✅ CTA 按钮用 FadeIn + delay */}
        <FadeIn delay={0.2}>
          <Button size="lg">Get Started</Button>
        </FadeIn>
      </FadeIn>
      
      {/* ✅ 特性列表用 Stagger + HoverLift */}
      <Stagger className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {features.map((feature) => (
          {/* ✅ Stagger 子元素必须有 variants */}
          <motion.div key={feature.id} variants={fadeUp}>
            {/* ✅ 卡片用 HoverLift */}
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
        <h2 className="text-2xl font-semibold mb-4">Ready to start?</h2>
        <Button variant="outline">Contact Us</Button>
      </FadeIn>
    </main>
  );
}
```

### ❌ WRONG Example (违反规则 - 任务失败)

```tsx
// ❌ WRONG - 没有使用任何 MotionPrimitives，页面内容完全静态
function Home() {
  return (
    <main className="container mx-auto px-4 py-16">
      <div className="text-center mb-16">  {/* ❌ 应该用 <FadeIn> */}
        <h1>Welcome</h1>
        <p>Description</p>
        <Button>Get Started</Button>  {/* ❌ 没有入场动画 */}
      </div>
      
      <div className="grid grid-cols-3 gap-6">  {/* ❌ 应该用 <Stagger> */}
        {features.map((f) => (
          <Card key={f.id}>  {/* ❌ 没有 variants，没有 HoverLift */}
            <CardHeader>{f.title}</CardHeader>
          </Card>
        ))}
      </div>
    </main>
  );
}
```

### ⛔ Animation Rules (CRITICAL)

1. **三层动画缺一不可**：`AnimatedRoutes` (App层) + `PageTransition` (Route层) + `MotionPrimitives` (页面内)
2. **页面内必须使用 MotionPrimitives**：Hero 用 `<FadeIn>`，列表用 `<Stagger>`，卡片用 `<HoverLift>`
3. **Stagger 的子元素必须有 variants**：`<motion.div variants={fadeUp}>`
4. **NEVER create static components** without entrance animations — 没有动画 = 任务失败
5. **Navbar MUST be outside AnimatedRoutes**, not inside it or inside page components

### ⛔ 生成页面组件前必须自查

1. ✅ 是否导入了 `FadeIn`, `Stagger`, `HoverLift`, `fadeUp`, `motion` from MotionPrimitives?
2. ✅ Hero/标题区域是否用 `<FadeIn>` 包裹?
3. ✅ 列表/网格是否用 `<Stagger>` 包裹?
4. ✅ Stagger 子元素是否有 `<motion.div variants={fadeUp}>`?
5. ✅ 卡片/可交互元素是否用 `<HoverLift>` 包裹?
6. ✅ 其他内容块是否用 `<FadeIn>` 包裹?
7. **❌ 没有动画的静态组件 = 违反规则，必须重写！**

---

## Performance Rules

### 动画性能规范

```tsx
// ✅ 尊重用户偏好
<div className="motion-safe:animate-bounce motion-reduce:animate-none">

// ✅ 只动画 transform 和 opacity (GPU-friendly)
<div className="transition-transform duration-300 hover:scale-105">
<div className="transition-opacity duration-300 hover:opacity-80">

// ❌ 禁止 transition-all
<div className="transition-all">  // 性能问题

// ✅ 明确列出属性
<div className="transition-[transform,opacity] duration-300">
```

### 其他性能规范

```tsx
// ✅ 图片必须有尺寸
<img src="/hero.jpg" width={1200} height={600} alt="Hero" />

// ✅ Below-fold 图片 lazy loading
<img src="/feature.jpg" loading="lazy" alt="Feature" />

// ✅ 图标按钮必须有 aria-label
<Button size="icon" aria-label="Settings"><Settings /></Button>
```

---

## Recommended Third-Party Libraries

> **以下场景需要引入第三方库，不要手写实现**

### 库选择总结

| 场景 | 推荐库 | 触发条件 | 安装命令 |
|------|--------|---------|---------|
| 复杂表格 | `@tanstack/react-table` | 表格 > 10 行 + 排序/筛选 | `pnpm add @tanstack/react-table` |
| 虚拟滚动 | `virtua` | 列表 > 50 项 | `pnpm add virtua` |
| 文件上传 | `react-dropzone` | 需要拖拽上传 | `pnpm add react-dropzone` |
| 日期范围 | 内置 Calendar | - | 已包含 |
| 富文本 | `@tiptap/react` | 需要富文本编辑 | `pnpm add @tiptap/react @tiptap/starter-kit` |
| 图表 | 内置 Chart | - | 已包含 (recharts) |
| 拖拽排序 | `@dnd-kit` | 需要拖拽排序 | `pnpm add @dnd-kit/core @dnd-kit/sortable` |

### 核心用法示例

#### 数据表格 (TanStack Table)

```tsx
import { useReactTable, getCoreRowModel, getSortedRowModel, flexRender } from "@tanstack/react-table";

function DataTable({ data, columns }) {
  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
  });

  return (
    <Table>
      <TableHeader>
        {table.getHeaderGroups().map((headerGroup) => (
          <TableRow key={headerGroup.id}>
            {headerGroup.headers.map((header) => (
              <TableHead key={header.id}>
                {flexRender(header.column.columnDef.header, header.getContext())}
              </TableHead>
            ))}
          </TableRow>
        ))}
      </TableHeader>
      <TableBody>
        {table.getRowModel().rows.map((row) => (
          <TableRow key={row.id}>
            {row.getVisibleCells().map((cell) => (
              <TableCell key={cell.id}>
                {flexRender(cell.column.columnDef.cell, cell.getContext())}
              </TableCell>
            ))}
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
```

#### 虚拟滚动 (Virtua)

```tsx
import { VList } from "virtua";

function VirtualList({ items }) {
  return (
    <VList style={{ height: 400 }}>
      {items.map((item) => (
        <div key={item.id}>{item.content}</div>
      ))}
    </VList>
  );
}
```

#### 文件上传 (react-dropzone)

```tsx
import { useDropzone } from "react-dropzone";

function FileUpload({ onUpload }) {
  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    accept: { 'image/*': ['.png', '.jpg', '.jpeg'] },
    maxSize: 5 * 1024 * 1024,
    onDrop: (files) => onUpload(files),
  });

  return (
    <div {...getRootProps()} className={cn(
      "border-2 border-dashed rounded-lg p-8 text-center cursor-pointer",
      isDragActive ? "border-primary bg-primary/5" : "border-muted-foreground/25"
    )}>
      <input {...getInputProps()} />
      <p>{isDragActive ? "Drop files here" : "Drag & drop or click to browse"}</p>
    </div>
  );
}
```

#### 拖拽排序 (dnd-kit)

```tsx
import { DndContext, closestCenter } from "@dnd-kit/core";
import { SortableContext, useSortable, verticalListSortingStrategy, arrayMove } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";

function SortableItem({ id, children }) {
  const { attributes, listeners, setNodeRef, transform, transition } = useSortable({ id });
  const style = { transform: CSS.Transform.toString(transform), transition };
  
  return <div ref={setNodeRef} style={style} {...attributes} {...listeners}>{children}</div>;
}

function SortableList({ items, onReorder }) {
  return (
    <DndContext collisionDetection={closestCenter} onDragEnd={({ active, over }) => {
      if (active.id !== over.id) {
        const oldIndex = items.findIndex((i) => i.id === active.id);
        const newIndex = items.findIndex((i) => i.id === over.id);
        onReorder(arrayMove(items, oldIndex, newIndex));
      }
    }}>
      <SortableContext items={items} strategy={verticalListSortingStrategy}>
        {items.map((item) => <SortableItem key={item.id} id={item.id}>{item.content}</SortableItem>)}
      </SortableContext>
    </DndContext>
  );
}
```

### ⛔ 禁止手写以下功能

- ❌ 手写虚拟滚动逻辑
- ❌ 手写拖拽排序逻辑
- ❌ 手写文件上传 UI (`<input type="file">` + 自己写的拖拽区域)
- ❌ 手写富文本编辑器 (`contentEditable`)

---

## Pre-Completion Checklist

### 组件使用
- [ ] ✅ 所有按钮使用 `<Button>`
- [ ] ✅ 所有输入框使用 `<Input>`
- [ ] ✅ 所有弹窗使用 `<Dialog>`
- [ ] ✅ 移动端菜单使用 `<Sheet>`
- [ ] ✅ Toast 使用 `sonner`
- [ ] ✅ 图标使用 `lucide-react`

### 样式来源
- [ ] ✅ 所有颜色使用 `[#HEX]` 格式
- [ ] ❌ 没有 Tailwind 默认颜色 (blue-500, gray-700)
- [ ] ✅ 使用 `variant="ghost"` 清除按钮默认样式

### 性能
- [ ] ❌ 没有 `transition-all`
- [ ] ✅ 图片有 `width` 和 `height`
- [ ] ✅ 图标按钮有 `aria-label`
- [ ] ✅ 大列表 (>50项) 使用虚拟化

### Third-Party
- [ ] ✅ 复杂表格使用 `@tanstack/react-table`
- [ ] ✅ 长列表使用 `virtua`
- [ ] ✅ 文件上传使用 `react-dropzone`
- [ ] ✅ 拖拽排序使用 `@dnd-kit`
