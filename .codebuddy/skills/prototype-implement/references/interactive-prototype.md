# React Interactive Prototype Guide

## Overview

本指南涵盖 React 原型中的交互组件实现，包括多页面导航、模态框、Toast 通知、表单验证等核心交互模式。

**适用范围**: 所有 Mode (URL Clone, Screenshot, Requirements) 生成的 React 代码

---

## Core Principles

### Multi-Page Navigation System (CRITICAL)

**要求**:
- ✅ 为导航栏的**每个链接**生成**完整的独立页面**
- ✅ 至少生成 **5-8 个功能完整的页面**
- ✅ 所有页面使用**相同的设计系统** (颜色、字体、间距)
- ✅ Header/Footer 提取为共享组件
- ✅ 使用 React Router 实现页面导航

### 设计一致性

- ✅ 所有页面使用 `design_system.json` 中的颜色
- ✅ 导航栏和 Footer 在所有页面**保持一致**
- ✅ 页面切换有**平滑的过渡动画**

### 交互完整性

- ✅ **零死链接**: 每个可点击元素都有实际功能
- ✅ **状态管理**: 当前页面在导航栏有激活状态
- ✅ **浏览器历史**: 支持前进/后退按钮

---

## Page Templates Reference

| 页面类型 | 必需区块 | 示例 |
|---------|---------|------|
| **Home** | Hero + Features + Testimonials + CTA | 首页完整内容 |
| **Solutions** | Hero + 解决方案卡片(3-4个) + CTA | 针对不同用户群体 |
| **Pricing** | Hero + 定价表(3层) + FAQ + CTA | 功能对比表 |
| **About** | Hero + 团队介绍 + 公司历史 + 价值观 | 公司故事 |
| **Contact** | Hero + 表单 + 地图/信息 + 社交链接 | 联系方式 |
| **Blog** | Hero + 文章列表 + 侧边栏 + 分页 | 博客文章卡片 |
| **Enterprise** | Hero + 功能列表 + 案例研究 + 联系表单 | 企业级功能 |

---

## React Router Setup

### App.tsx 路由配置

```tsx
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom';
import { AnimatePresence } from 'framer-motion';
import Header from './components/layout/Header';
import Footer from './components/layout/Footer';
import Index from './pages/Index';
import Solutions from './pages/Solutions';
import Pricing from './pages/Pricing';
import About from './pages/About';
import Contact from './pages/Contact';
import NotFound from './pages/NotFound';

function App() {
  return (
    <BrowserRouter>
      <Header />
      <AnimatedRoutes />
      <Footer />
    </BrowserRouter>
  );
}

function AnimatedRoutes() {
  const location = useLocation();
  
  return (
    <AnimatePresence mode="wait">
      <Routes location={location} key={location.pathname}>
        <Route path="/" data-genie-key="Home" data-genie-title="Home" element={<Index />} />
        <Route path="/solutions" data-genie-key="Solutions" data-genie-title="Solutions" element={<Solutions />} />
        <Route path="/pricing" data-genie-key="Pricing" data-genie-title="Pricing" element={<Pricing />} />
        <Route path="/about" data-genie-key="About" data-genie-title="About Us" element={<About />} />
        <Route path="/contact" data-genie-key="Contact" data-genie-title="Contact" element={<Contact />} />
        <Route path="*" data-genie-key="NotFound" data-genie-title="Not Found" element={<NotFound />} />
      </Routes>
    </AnimatePresence>
  );
}
```

### Navigation Link Component

```tsx
// components/layout/Header.tsx
import { Link, useLocation } from 'react-router-dom';
import { cn } from '@/lib/utils';

const navLinks = [
  { href: '/', label: 'Home' },
  { href: '/solutions', label: 'Solutions' },
  { href: '/pricing', label: 'Pricing' },
  { href: '/about', label: 'About' },
  { href: '/contact', label: 'Contact' },
];

export default function Header() {
  const location = useLocation();
  
  return (
    <header className="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b">
      <nav className="container mx-auto px-6 h-16 flex items-center justify-between">
        <Link to="/" className="text-xl font-bold">
          Logo
        </Link>
        
        <div className="hidden md:flex items-center gap-6">
          {navLinks.map((link) => (
            <Link
              key={link.href}
              to={link.href}
              className={cn(
                "text-gray-600 hover:text-gray-900 transition-colors relative",
                location.pathname === link.href && "text-gray-900 font-medium"
              )}
            >
              {link.label}
              {location.pathname === link.href && (
                <span className="absolute -bottom-1 left-0 right-0 h-0.5 bg-primary" />
              )}
            </Link>
          ))}
        </div>
        
        <div className="flex items-center gap-3">
          <LoginDialog />
          <SignupDialog />
        </div>
      </nav>
    </header>
  );
}
```

### Page Transition Animation

```tsx
// components/PageTransition.tsx
import { motion } from 'framer-motion';
import { ReactNode } from 'react';

interface PageTransitionProps {
  children: ReactNode;
}

export default function PageTransition({ children }: PageTransitionProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.3 }}
    >
      {children}
    </motion.div>
  );
}

// Usage in page components:
// export default function Solutions() {
//   return (
//     <PageTransition>
//       <main>...</main>
//     </PageTransition>
//   );
// }
```

---

## Modal System (shadcn/ui Dialog)

### Login Dialog

```tsx
// components/auth/LoginDialog.tsx
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { toast } from 'sonner';

const loginSchema = z.object({
  email: z.string().email('Please enter a valid email'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
});

type LoginFormValues = z.infer<typeof loginSchema>;

export function LoginDialog() {
  const [open, setOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  
  const form = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  });

  async function onSubmit(data: LoginFormValues) {
    setIsLoading(true);
    
    // Demo mode - simulate login
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    toast.success(`Welcome back! Logged in as ${data.email}`);
    setOpen(false);
    form.reset();
    setIsLoading(false);
  }

  function handleGoogleLogin() {
    setIsLoading(true);
    
    // Demo mode - simulate OAuth
    setTimeout(() => {
      toast.success('Logged in with Google (Demo mode)');
      setOpen(false);
      setIsLoading(false);
    }, 1500);
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="ghost">Log in</Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Welcome Back</DialogTitle>
        </DialogHeader>
        
        {/* Google Login */}
        <Button
          variant="outline"
          className="w-full"
          onClick={handleGoogleLogin}
          disabled={isLoading}
        >
          <svg className="w-5 h-5 mr-2" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google
        </Button>
        
        <div className="relative my-4">
          <div className="absolute inset-0 flex items-center">
            <span className="w-full border-t" />
          </div>
          <div className="relative flex justify-center text-xs uppercase">
            <span className="bg-background px-2 text-muted-foreground">Or continue with email</span>
          </div>
        </div>
        
        {/* Email Login Form */}
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="email"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Email</FormLabel>
                  <FormControl>
                    <Input placeholder="your@email.com" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="password"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Password</FormLabel>
                  <FormControl>
                    <Input type="password" placeholder="••••••••" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading ? 'Logging in...' : 'Log in'}
            </Button>
          </form>
        </Form>
        
        <p className="text-center text-sm text-muted-foreground mt-4">
          Don't have an account?{' '}
          <Button variant="link" className="p-0 h-auto" onClick={() => setOpen(false)}>
            Sign up
          </Button>
        </p>
      </DialogContent>
    </Dialog>
  );
}
```

### Signup Dialog

```tsx
// components/auth/SignupDialog.tsx
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { toast } from 'sonner';

const signupSchema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Please enter a valid email'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
  terms: z.boolean().refine(val => val === true, 'You must accept the terms'),
});

type SignupFormValues = z.infer<typeof signupSchema>;

export function SignupDialog() {
  const [open, setOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  
  const form = useForm<SignupFormValues>({
    resolver: zodResolver(signupSchema),
    defaultValues: { name: '', email: '', password: '', terms: false },
  });

  async function onSubmit(data: SignupFormValues) {
    setIsLoading(true);
    
    // Demo mode - simulate signup
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    toast.success(`Account created! Welcome, ${data.name}`);
    setOpen(false);
    form.reset();
    setIsLoading(false);
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button>Sign up</Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Create Account</DialogTitle>
        </DialogHeader>
        
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="name"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Full Name</FormLabel>
                  <FormControl>
                    <Input placeholder="John Doe" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="email"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Email</FormLabel>
                  <FormControl>
                    <Input placeholder="your@email.com" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="password"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Password</FormLabel>
                  <FormControl>
                    <Input type="password" placeholder="••••••••" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="terms"
              render={({ field }) => (
                <FormItem className="flex flex-row items-start space-x-3 space-y-0">
                  <FormControl>
                    <Checkbox checked={field.value} onCheckedChange={field.onChange} />
                  </FormControl>
                  <div className="space-y-1 leading-none">
                    <FormLabel className="text-sm font-normal">
                      I agree to the Terms of Service and Privacy Policy
                    </FormLabel>
                  </div>
                </FormItem>
              )}
            />
            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading ? 'Creating account...' : 'Create Account'}
            </Button>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}
```

---

## Mobile Menu (shadcn/ui Sheet)

```tsx
// components/layout/MobileMenu.tsx
import { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Menu } from 'lucide-react';
import { cn } from '@/lib/utils';

const navLinks = [
  { href: '/', label: 'Home' },
  { href: '/solutions', label: 'Solutions' },
  { href: '/pricing', label: 'Pricing' },
  { href: '/about', label: 'About' },
  { href: '/contact', label: 'Contact' },
];

export function MobileMenu() {
  const [open, setOpen] = useState(false);
  const location = useLocation();
  
  return (
    <Sheet open={open} onOpenChange={setOpen}>
      <SheetTrigger asChild>
        <Button variant="ghost" size="icon" className="md:hidden">
          <Menu className="h-6 w-6" />
        </Button>
      </SheetTrigger>
      <SheetContent side="right" className="w-[300px]">
        <SheetHeader>
          <SheetTitle>Menu</SheetTitle>
        </SheetHeader>
        <nav className="flex flex-col gap-4 mt-8">
          {navLinks.map((link) => (
            <Link
              key={link.href}
              to={link.href}
              onClick={() => setOpen(false)}
              className={cn(
                "text-lg py-2 border-b border-gray-100 transition-colors",
                location.pathname === link.href
                  ? "text-primary font-medium"
                  : "text-gray-600 hover:text-gray-900"
              )}
            >
              {link.label}
            </Link>
          ))}
        </nav>
      </SheetContent>
    </Sheet>
  );
}
```

---

## Toast Notifications (Sonner)

```tsx
// Usage with sonner (already configured in template)
import { toast } from 'sonner';

// Success toast
toast.success('Operation completed successfully!');

// Error toast
toast.error('Something went wrong. Please try again.');

// Info toast
toast.info('Your changes have been saved.');

// Custom toast with action
toast('New message received', {
  description: 'You have a new notification',
  action: {
    label: 'View',
    onClick: () => console.log('View clicked'),
  },
});

// Promise toast
toast.promise(fetchData(), {
  loading: 'Loading...',
  success: 'Data loaded!',
  error: 'Failed to load data',
});
```

---

## Form Validation (react-hook-form + zod)

### Contact Form Example

```tsx
// pages/Contact.tsx
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { toast } from 'sonner';

const contactSchema = z.object({
  name: z.string().min(2, 'Name is required'),
  email: z.string().email('Invalid email address'),
  subject: z.string().min(5, 'Subject must be at least 5 characters'),
  message: z.string().min(10, 'Message must be at least 10 characters'),
});

type ContactFormValues = z.infer<typeof contactSchema>;

export default function Contact() {
  const form = useForm<ContactFormValues>({
    resolver: zodResolver(contactSchema),
    defaultValues: { name: '', email: '', subject: '', message: '' },
  });

  function onSubmit(data: ContactFormValues) {
    // Demo mode
    console.log('Form submitted:', data);
    toast.success('Message sent! We\'ll get back to you soon.');
    form.reset();
  }

  return (
    <main className="py-20 px-6">
      <div className="max-w-2xl mx-auto">
        <h1 className="text-4xl font-bold mb-8">Contact Us</h1>
        
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
            <div className="grid md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="name"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Name</FormLabel>
                    <FormControl>
                      <Input placeholder="Your name" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Email</FormLabel>
                    <FormControl>
                      <Input placeholder="your@email.com" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            <FormField
              control={form.control}
              name="subject"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Subject</FormLabel>
                  <FormControl>
                    <Input placeholder="How can we help?" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="message"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Message</FormLabel>
                  <FormControl>
                    <Textarea placeholder="Your message..." rows={5} {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <Button type="submit" size="lg">
              Send Message
            </Button>
          </form>
        </Form>
      </div>
    </main>
  );
}
```

---

## Interactive Component Patterns

### Pricing Toggle (Monthly/Yearly)

```tsx
// components/PricingToggle.tsx
import { useState } from 'react';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';

interface PricingPlan {
  name: string;
  monthlyPrice: number;
  yearlyPrice: number;
  features: string[];
}

const plans: PricingPlan[] = [
  { name: 'Starter', monthlyPrice: 9, yearlyPrice: 90, features: ['5 projects', '10GB storage'] },
  { name: 'Pro', monthlyPrice: 29, yearlyPrice: 290, features: ['Unlimited projects', '100GB storage'] },
  { name: 'Enterprise', monthlyPrice: 99, yearlyPrice: 990, features: ['Everything in Pro', 'Priority support'] },
];

export function PricingSection() {
  const [isYearly, setIsYearly] = useState(false);

  return (
    <section className="py-20">
      {/* Toggle */}
      <div className="flex items-center justify-center gap-4 mb-12">
        <span className={cn(!isYearly && "font-medium")}>Monthly</span>
        <Switch checked={isYearly} onCheckedChange={setIsYearly} />
        <span className={cn(isYearly && "font-medium")}>
          Yearly <span className="text-green-600 text-sm">(Save 20%)</span>
        </span>
      </div>
      
      {/* Pricing Cards */}
      <div className="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        {plans.map((plan) => (
          <div key={plan.name} className="p-8 border rounded-2xl">
            <h3 className="text-2xl font-bold mb-4">{plan.name}</h3>
            <div className="text-4xl font-bold mb-6">
              ${isYearly ? plan.yearlyPrice : plan.monthlyPrice}
              <span className="text-lg text-gray-500">/{isYearly ? 'year' : 'month'}</span>
            </div>
            <ul className="space-y-3 mb-8">
              {plan.features.map((feature) => (
                <li key={feature} className="flex items-center gap-2">
                  <span className="text-green-500">✓</span> {feature}
                </li>
              ))}
            </ul>
            <Button className="w-full">Get Started</Button>
          </div>
        ))}
      </div>
    </section>
  );
}
```

### Favorites with localStorage

```tsx
// hooks/useFavorites.ts
import { useState, useEffect } from 'react';

export function useFavorites() {
  const [favorites, setFavorites] = useState<string[]>([]);

  useEffect(() => {
    const stored = localStorage.getItem('favorites');
    if (stored) {
      setFavorites(JSON.parse(stored));
    }
  }, []);

  const toggleFavorite = (id: string) => {
    setFavorites((prev) => {
      const newFavorites = prev.includes(id)
        ? prev.filter((f) => f !== id)
        : [...prev, id];
      
      localStorage.setItem('favorites', JSON.stringify(newFavorites));
      return newFavorites;
    });
  };

  const isFavorite = (id: string) => favorites.includes(id);

  return { favorites, toggleFavorite, isFavorite };
}

// Usage:
// const { toggleFavorite, isFavorite } = useFavorites();
// <Button onClick={() => toggleFavorite(item.id)}>
//   <Heart className={cn(isFavorite(item.id) && "fill-red-500 text-red-500")} />
// </Button>
```

### Accordion/FAQ

```tsx
// components/FAQ.tsx
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';

const faqs = [
  { question: 'How do I get started?', answer: 'Sign up for a free account...' },
  { question: 'What payment methods do you accept?', answer: 'We accept all major credit cards...' },
  { question: 'Can I cancel anytime?', answer: 'Yes, you can cancel your subscription...' },
];

export function FAQ() {
  return (
    <section className="py-20 px-6">
      <div className="max-w-3xl mx-auto">
        <h2 className="text-3xl font-bold text-center mb-12">Frequently Asked Questions</h2>
        <Accordion type="single" collapsible className="space-y-4">
          {faqs.map((faq, index) => (
            <AccordionItem key={index} value={`item-${index}`} className="border rounded-lg px-6">
              <AccordionTrigger className="text-left">{faq.question}</AccordionTrigger>
              <AccordionContent className="text-gray-600">{faq.answer}</AccordionContent>
            </AccordionItem>
          ))}
        </Accordion>
      </div>
    </section>
  );
}
```

---

## Checklist

### Navigation System (CRITICAL)
- [ ] 导航栏中的**每个链接都能点击**
- [ ] 每个导航链接都有对应的**完整页面**
- [ ] 所有页面使用**相同的设计系统**
- [ ] 当前页面在导航栏中有**激活状态**
- [ ] Logo 点击能**返回首页**
- [ ] 浏览器前进/后退按钮**正常工作**

### Page Completeness
- [ ] 至少生成 **5-8 个独立页面**
- [ ] 每个页面包含 **3-5 个内容区块**
- [ ] 所有页面都有 **Hero section**
- [ ] 所有页面都有 **CTA**

### Interactivity
- [ ] Login/Signup 使用 **shadcn/ui Dialog**
- [ ] 表单验证使用 **react-hook-form + zod**
- [ ] Toast 使用 **sonner**
- [ ] 移动端菜单使用 **shadcn/ui Sheet**
- [ ] 所有按钮有 **hover/active 状态**

### Responsive Design
- [ ] 所有页面在**移动端**正常显示
- [ ] 移动端有**汉堡菜单**

### Code Quality
- [ ] 没有 TypeScript 错误
- [ ] 没有 console 错误
- [ ] 没有"死链接"

---

## Success Criteria

React 原型完成标准：

- ✅ 每个按钮/链接都有功能（无死链接）
- ✅ 导航在桌面端和移动端都能工作
- ✅ Login/Signup Dialog 能正常打开和关闭
- ✅ 表单有验证和反馈
- ✅ 页面切换有过渡动画
- ✅ 所有主要页面都有完整内容
- ✅ 无 TypeScript/控制台错误

**Golden Rule**: *原型应该感觉像真正的应用，即使没有后端。*
