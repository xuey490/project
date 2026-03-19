# Architecture Reference

## Backend Architecture

### Module Pattern

Routes + handlers in single file, each exports Router:

```typescript
// src/modules/user.ts
import { Router } from 'express';
import { prisma } from '../config/database';
import { validate } from '../middleware/validation';
import { listUsersSchema } from '../types/user.types';

const router = Router();

router.post('/list', validate(listUsersSchema), async (req, res) => {
  const { page, limit, filters } = req.body;
  // Implementation
});

export default router;
```

### Key Backend Files

| File | Purpose |
|------|---------|
| `src/index.ts` | Server entry, graceful shutdown |
| `src/app.ts` | Middleware setup, route mounting |
| `src/config/env.ts` | Zod-validated environment |
| `src/config/database.ts` | Prisma singleton |
| `src/config/logger.ts` | Pino logger factory |
| `src/middleware/errorHandler.ts` | AppError class, global handler |
| `src/middleware/validation.ts` | Generic Zod middleware |

**Error Handler Import:**
```typescript
import { AppError } from '../middleware/errorHandler';
```

### Commands

```bash
npm run prisma:migrate -- --name init    # Migration (MUST specify --name)
npm run test                             # Run tests
```

## Frontend Architecture (Taro MiniProgram)

### Key Files

| File | Purpose |
|------|---------|
| `src/app.tsx` | Root component, Taro UI styles import |
| `src/app.config.ts` | Pages, window, tabBar configuration |
| `src/app.scss` | Global styles |
| `config/index.ts` | Taro build config, H5 proxy, esnextModules |
| `src/services/api-client.ts` | Taro.request wrapper with auth |
| `project.config.json` | WeChat MiniProgram settings |

### Commands

```bash
npm run dev:h5       # H5 development (browser testing)
npm run dev:weapp    # WeChat MiniProgram development
npm run build:h5     # Build for H5
npm run build:weapp  # Build for WeChat MiniProgram
```

### App Configuration (app.config.ts)

```typescript
export default defineAppConfig({
  pages: [
    'pages/index/index',
    'pages/list/index',
    'pages/mine/index'
  ],
  window: {
    backgroundTextStyle: 'light',
    navigationBarBackgroundColor: '#fff',
    navigationBarTitleText: 'MiniProgram',
    navigationBarTextStyle: 'black'
  },
  tabBar: {
    color: '#999999',
    selectedColor: '#07C160',
    backgroundColor: '#ffffff',
    list: [
      { pagePath: 'pages/index/index', text: '首页', iconPath: 'assets/tabbar/home.png', selectedIconPath: 'assets/tabbar/home-active.png' },
      { pagePath: 'pages/mine/index', text: '我的', iconPath: 'assets/tabbar/user.png', selectedIconPath: 'assets/tabbar/user-active.png' }
    ]
  }
})
```

### TabBar Icons

**PNG format required** - Use `tabbar-icon-generator` skill:

```bash
node .codebuddy/skills/tabbar-icon-generator/scripts/generate_icons.js -o frontend/src/assets/tabbar
```

- Available icons: home, list, user, settings, search, message, cart, favorite
- Custom colors: `--normal-color "#999999" --active-color "#07C160"`
- Tab count: 2-5 tabs, labels in Chinese 2-4 chars (首页、分类、购物车、我的)

### Taro UI Components

**Documentation**: https://taro-ui.jd.com/ | **GitHub**: https://github.com/jd-opensource/taro-ui

53+ components available. DO NOT create custom versions.

| Category | Components |
|----------|------------|
| **Basic** | AtButton, AtIcon, AtAvatar, AtBadge |
| **Form** | AtInput, AtInputNumber, AtTextarea, AtRadio, AtCheckbox, AtSwitch, AtRate, AtPicker, AtSearchBar, AtSlider, AtImagePicker, AtRange |
| **Layout** | AtList, AtListItem, AtCard, AtFloatLayout, AtAccordion, AtGrid, AtFlex |
| **Navigation** | AtNavBar, AtTabBar, AtTabs, AtTabsPane, AtSegmentedControl, AtPagination, AtDrawer, AtIndexes |
| **Feedback** | AtModal, AtToast, AtMessage, AtProgress, AtActivityIndicator, AtSwipeAction, AtActionSheet, AtCurtain |
| **Display** | AtTag, AtTimeline, AtDivider, AtSteps, AtNoticebar, AtCountdown, AtLoadMore, AtCalendar |

### API Client

**IMPORTANT**: Always use the existing `src/services/api-client.ts`. DO NOT create new api.ts files.

**Correct Import & Usage:**
```typescript
// ✅ CORRECT - import the instance
import { apiClient } from '@/services/api-client';
// or
import apiClient from '@/services/api-client';

// Use full path with /api prefix (same as web template)
const users = await apiClient.post<User[]>('/api/users/list', { page: 1, limit: 10 });
const health = await apiClient.get<HealthResponse>('/api/health');

// ❌ WRONG - no named function exports
import { post, get } from '@/services/api-client';  // ERROR: post is not a function
```

### ⚠️ CRITICAL: process.env Causes Runtime Error

```
ReferenceError: process is not defined
  at src/services/api.ts:19:16
```

**DO NOT USE** any of these in frontend code:
- ❌ `process.env.TARO_ENV`
- ❌ `process.env.NODE_ENV`
- ❌ `process.env.TARO_APP_XXX`
- ❌ Any `process.env.*` reference

**USE INSTEAD**:
- ✅ `Taro.getEnv()` for environment detection
- ✅ Hardcoded values or build-time config for other settings

### Toast & Loading

```typescript
import Taro from '@tarojs/taro';

// Loading
Taro.showLoading({ title: '加载中...' });
Taro.hideLoading();

// Toast
Taro.showToast({ title: '成功', icon: 'success' });

// Modal
Taro.showModal({ title: '提示', content: '确认删除吗？' });
```

### Performance Guidelines

- Bundle size: < 2MB for MiniProgram
- Lazy load images
- Use skeleton screens for loading states
- Minimize re-renders

## System API Routes

**GET (No Parameters):**
- `/api/` - Welcome
- `/api/health` - Health check
- `/api/health/ready` - Readiness (includes DB)
- `/api/health/live` - Liveness
- `/api/version` - Version info
- `/api/status` - System status

**POST (JSON Body):**
- `/api/[resource]/list` - List with pagination
- `/api/[resource]/search` - Complex search
- `/api/[resource]` - Create
