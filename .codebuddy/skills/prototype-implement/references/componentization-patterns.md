# Componentization Patterns (组件化模式指南)

## Core Principle

**目标**：生成的代码应具备良好的可维护性、可复用性和可扩展性

> **复杂度阈值**：详见 [SKILL.md → Componentization Rules](../SKILL.md#componentization-rules-组件化规则)

---

## 1. Component Extraction Patterns

### 1.1 提取子组件 (Extract Sub-Components)

**使用场景**：单个组件有多个 UI 部分、条件渲染块或重复模式

```tsx
// ❌ WRONG - 单体组件 (所有内容塞在一个文件)
function ProductPage() {
  return (
    <div>
      {/* 100 行 Header UI */}
      {/* 100 行 Product Grid */}
      {/* 100 行 Footer */}
    </div>
  );
}

// ✅ CORRECT - 拆分为专注的子组件
// components/layout/Header.tsx
export function Header() {
  return (
    <header>
      <Logo />
      <Navigation />
      <SearchBar />
      <UserMenu />
    </header>
  );
}

// components/product/ProductCard.tsx
export function ProductCard({ product }: { product: Product }) {
  return (
    <Card>
      <CardContent>
        <img src={product.image} alt={product.name} />
        <CardTitle>{product.name}</CardTitle>
        <Button>Add to Cart</Button>
      </CardContent>
    </Card>
  );
}

// pages/Products.tsx - 组件编排
function ProductPage() {
  return (
    <PageLayout>
      <ProductGrid products={products} />
    </PageLayout>
  );
}
```

### 1.2 提取自定义 Hooks (Extract Custom Hooks)

**使用场景**：组件有复杂状态管理、多个 useState/useEffect、或业务逻辑混合 UI

```tsx
// ❌ WRONG - 状态逻辑与 UI 混杂
function ShoppingCart() {
  const [items, setItems] = useState<CartItem[]>([]);
  const [coupon, setCoupon] = useState("");
  const [discount, setDiscount] = useState(0);
  
  // 50+ 行状态管理逻辑
  useEffect(() => { /* localStorage */ }, []);
  useEffect(() => { /* 保存 */ }, [items]);
  const addItem = (product: Product) => { /* 复杂逻辑 */ };
  // ...然后才是 UI
  return <div>...</div>;
}

// ✅ CORRECT - 提取到自定义 Hook
// hooks/use-cart.ts
export function useCart() {
  const [items, setItems] = useState<CartItem[]>([]);
  
  useEffect(() => {
    const saved = localStorage.getItem("cart");
    if (saved) setItems(JSON.parse(saved));
  }, []);
  
  useEffect(() => {
    localStorage.setItem("cart", JSON.stringify(items));
  }, [items]);
  
  const addItem = useCallback((product: Product) => {
    setItems(prev => {
      const existing = prev.find(i => i.id === product.id);
      if (existing) {
        return prev.map(i => 
          i.id === product.id ? { ...i, quantity: i.quantity + 1 } : i
        );
      }
      return [...prev, { ...product, quantity: 1 }];
    });
  }, []);
  
  const total = useMemo(() => 
    items.reduce((sum, item) => sum + item.price * item.quantity, 0),
    [items]
  );
  
  return { items, addItem, removeItem, total };
}

// components/cart/ShoppingCart.tsx - 组件只负责 UI
function ShoppingCart() {
  const { items, removeItem, total } = useCart();
  
  return (
    <Card>
      <CardHeader><CardTitle>Shopping Cart</CardTitle></CardHeader>
      <CardContent>
        <CartItemList items={items} onRemove={removeItem} />
        <CartSummary total={total} />
      </CardContent>
    </Card>
  );
}
```

### 1.3 提取类型定义 (Extract Type Definitions)

**使用场景**：组件间共享类型、类型定义超过 10 行

```tsx
// ❌ WRONG - 类型定义散落在组件中
function ProductCard({ product }: { 
  product: { id: string; name: string; price: number; image: string; } 
}) {}

// ✅ CORRECT - 集中类型定义
// types/product.ts
export interface Product {
  id: string;
  name: string;
  price: number;
  image: string;
  category: string;
}

export interface CartItem extends Product {
  quantity: number;
}

// components/product/ProductCard.tsx
import { Product } from "@/types/product";
function ProductCard({ product }: { product: Product }) {}
```

---

## 2. File Organization

### 推荐的目录结构

```
frontend/src/
├── components/
│   ├── ui/                    # shadcn/ui 组件 (不修改)
│   ├── layout/                # Header, Footer, PageLayout
│   ├── common/                # Logo, SearchBar, UserAvatar
│   └── [feature]/             # product/, cart/, checkout/
├── hooks/                     # use-cart.ts, use-auth.ts
├── types/                     # product.ts, user.ts
├── lib/                       # utils.ts, format.ts
├── data/                      # mockProducts.ts (原型数据)
└── pages/                     # 页面组件
```

### 命名规范

| 类型 | 命名规范 | 示例 |
|------|---------|------|
| 组件文件 | PascalCase | `ProductCard.tsx` |
| Hook 文件 | kebab-case + use- | `use-cart.ts` |
| 类型文件 | kebab-case | `product.ts` |

---

## 3. Complexity Management

### 简化条件逻辑

```tsx
// ❌ WRONG - 深层嵌套条件
function StatusBadge({ status }: { status: string }) {
  if (status === "pending") {
    return <Badge className="bg-yellow-500">Pending</Badge>;
  } else if (status === "processing") {
    return <Badge className="bg-blue-500">Processing</Badge>;
  } // ... 更多 if/else
}

// ✅ CORRECT - 使用查找表
const STATUS_CONFIG = {
  pending: { label: "Pending", className: "bg-[#EAB308] text-white" },
  processing: { label: "Processing", className: "bg-[#3B82F6] text-white" },
  delivered: { label: "Delivered", className: "bg-[#22C55E] text-white" },
} as const;

function StatusBadge({ status }: { status: keyof typeof STATUS_CONFIG }) {
  const config = STATUS_CONFIG[status] ?? { label: "Unknown", className: "bg-[#6B7280]" };
  return <Badge className={config.className}>{config.label}</Badge>;
}
```

### 避免 Boolean Prop 泛滥

```tsx
// ❌ WRONG - 2^4 = 16 种状态组合
<MessageComposer isThread={true} isDM={false} isEditing={true} isReply={false} />

// ✅ CORRECT - 使用明确的变体组件
<ThreadComposer />
<DMComposer />
<EditComposer threadId={id} />

// 或使用 variant prop
<MessageComposer variant="thread" />
```

---

## 4. Composition Patterns

### Compound Components (复合组件)

**使用场景**：组件有多个相关的子部分，需要灵活组合

```tsx
// ✅ CORRECT - Compound Component 模式
<Card variant="elevated">
  <Card.Header>
    <Card.Title>Dashboard</Card.Title>
  </Card.Header>
  <Card.Content>
    <p>Your metrics overview</p>
  </Card.Content>
  <Card.Footer>
    <Button>View Details</Button>
  </Card.Footer>
</Card>

// ❌ WRONG - 通过 props 传递所有内容
<Card 
  title="Dashboard" 
  content={<p>Your metrics</p>} 
  footerButton="View Details"
/>
```

### Render Props Pattern

**使用场景**：需要共享逻辑但 UI 由调用方决定

```tsx
interface DataFetcherProps<T> {
  url: string;
  children: (data: T | null, isLoading: boolean, error: Error | null) => React.ReactNode;
}

function DataFetcher<T>({ url, children }: DataFetcherProps<T>) {
  const [data, setData] = useState<T | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  // ... fetch logic
  return <>{children(data, isLoading, error)}</>;
}

// 使用
<DataFetcher<Product[]> url="/api/products">
  {(products, isLoading) => {
    if (isLoading) return <Skeleton />;
    return <ProductGrid products={products!} />;
  }}
</DataFetcher>
```

---

## 5. State Management Patterns

### 状态分类与处理

| 状态类型 | 处理方式 | 示例 |
|---------|---------|------|
| **UI 状态** | useState | 模态框开关、展开/折叠 |
| **共享状态** | Context + useReducer | 用户信息、主题、购物车 |
| **表单状态** | react-hook-form | 表单值、验证 |
| **URL 状态** | React Router | 筛选条件、分页 |

### Context 使用模式

```tsx
// contexts/auth-context.tsx
interface AuthContextValue {
  user: User | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  
  const login = useCallback(async (email: string, password: string) => {
    // Demo mode - 模拟登录
    const mockUser = { id: "1", email, name: "Demo User" };
    setUser(mockUser);
    localStorage.setItem("user", JSON.stringify(mockUser));
  }, []);
  
  const value = useMemo(() => ({
    user,
    isAuthenticated: !!user,
    login,
    logout: () => { setUser(null); localStorage.removeItem("user"); },
  }), [user, login]);
  
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
}
```

---

## 6. Performance Patterns

### Memoization

```tsx
// ✅ 昂贵的计算 - 应该 memo
const filteredProducts = useMemo(() => {
  return products
    .filter(p => filters.category ? p.category === filters.category : true)
    .sort((a, b) => a.price - b.price);
}, [products, filters]);

// ✅ 传递给子组件的回调 - 应该 memo
const handleAdd = useCallback(() => {
  onAddToCart(product);
}, [product, onAddToCart]);

// ❌ 简单操作不需要 memo
const buttonLabel = useMemo(() => label, [label]); // 不必要
```

### 组件懒加载

```tsx
import { lazy, Suspense } from "react";

const ProductDetail = lazy(() => import("@/pages/ProductDetail"));
const Checkout = lazy(() => import("@/pages/Checkout"));

function AppRoutes() {
  return (
    <Suspense fallback={<PageLoader />}>
      <Routes>
        <Route path="/product/:id" element={<ProductDetail />} />
        <Route path="/checkout" element={<Checkout />} />
      </Routes>
    </Suspense>
  );
}
```

---

## 7. Prototype-Specific Patterns

### Mock Data 组织

```tsx
// data/products.ts
export const mockProducts: Product[] = [
  {
    id: "1",
    name: "Wireless Headphones",
    price: 199.99,
    image: "/images/products/headphones.jpg",
    category: "Electronics",
  },
  // ... more products
];

// hooks/use-products.ts
export function useProducts(filters?: ProductFilters) {
  const [products] = useState(mockProducts);
  const [isLoading, setIsLoading] = useState(true);
  
  useEffect(() => {
    setIsLoading(true);
    const timer = setTimeout(() => setIsLoading(false), 300);
    return () => clearTimeout(timer);
  }, [filters]);
  
  return { products, isLoading };
}
```

### Demo Mode 交互

```tsx
// ✅ 原型中的表单提交
function ContactForm() {
  const onSubmit = (data: ContactFormValues) => {
    // Demo mode - 不实际发送，只显示成功提示
    console.log("Form data:", data);
    toast.success("Message sent successfully!");
    form.reset();
  };
  // ...
}

// ✅ 原型中的登录
function LoginForm() {
  const { login } = useAuth();
  
  const onSubmit = async (data: LoginFormValues) => {
    // Demo mode - 任何邮箱/密码都可以登录
    await login(data.email, data.password);
    toast.success("Welcome back!");
  };
  
  return (
    <Form {...form}>
      <p className="text-sm text-muted-foreground">
        Demo mode: Use any email and password to login
      </p>
      {/* form fields */}
    </Form>
  );
}
```

---

## Quick Reference Checklist

### 拆分组件时机

- [ ] 组件超过 200 行
- [ ] 有 3+ 个 useState
- [ ] 有 2+ 个 useEffect
- [ ] 条件渲染超过 3 层
- [ ] 相同的 UI 模式在多处重复

### 文件组织检查

- [ ] 布局组件在 `components/layout/`
- [ ] 功能组件按模块分组
- [ ] Hooks 在 `hooks/` 目录
- [ ] 类型定义在 `types/` 目录
- [ ] Mock 数据在 `data/` 目录

### 性能检查

- [ ] 昂贵计算使用 useMemo
- [ ] 传递给子组件的回调使用 useCallback
- [ ] 页面级组件使用懒加载
- [ ] 大列表使用虚拟化 (virtua)
