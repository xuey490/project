# MiniProgram Frontend

Taro-based miniprogram frontend template with bottom tab navigation.

## Features

- 🚀 Built with Taro 4.x for cross-platform support
- 🎨 Taro UI component library for consistent UI
- 📱 Support WeChat, Alipay, ByteDance, and more mini-programs
- 🧭 Bottom TabBar navigation (3-5 tabs recommended)
- 💎 TypeScript support
- 🎯 Modern React architecture
- 🔧 Easy to customize and extend

## Important: TabBar Navigation

**MiniPrograms require 3-5 bottom tab navigation items for optimal user experience.**

The default template includes only 1 tab. You should expand this to 3-5 tabs based on your application needs:

- **E-commerce**: Home, Categories, Cart, Orders, Profile
- **Social**: Feed, Discover, Publish, Messages, Profile
- **Service**: Home, Services, Appointments, Mine
- **Content**: Home, Explore, Favorites, Profile

Configure in `src/app.config.ts` tabBar section.

## Project Structure

```
frontend/
├── src/
│   ├── pages/
│   │   └── index/           # Home page
│   │       ├── index.tsx
│   │       ├── index.scss
│   │       └── index.config.ts
│   ├── components/          # Reusable components
│   ├── services/            # API services
│   │   └── api-client.ts    # HTTP client
│   ├── utils/               # Utility functions
│   ├── assets/              # Static assets
│   │   └── images/          # Images (logo, icons, etc.)
│   ├── types/               # TypeScript type definitions
│   ├── app.tsx              # Root component
│   ├── app.config.ts        # App configuration
│   └── app.scss             # Global styles
├── config/                  # Build configuration
│   ├── index.ts             # Base config
│   ├── dev.ts               # Development config
│   └── prod.ts              # Production config
└── project.config.json      # WeChat miniprogram configuration
```

## Getting Started

### Prerequisites

- Node.js >= 18
- npm or yarn

### Installation

```bash
npm install
```

### Development

**WeChat MiniProgram:**
```bash
npm run dev:weapp
```

**Alipay MiniProgram:**
```bash
npm run dev:alipay
```

**ByteDance MiniProgram:**
```bash
npm run dev:tt
```

**H5:**
```bash
npm run dev:h5
```

### Build

**WeChat MiniProgram:**
```bash
npm run build:weapp
```

**Other platforms:**
```bash
npm run build:alipay    # Alipay
npm run build:tt        # ByteDance
npm run build:swan      # Baidu
npm run build:qq        # QQ
npm run build:jd        # JD
npm run build:h5        # H5
```

## Configuration

### Environment Variables

Copy `.env.example` to `.env` and configure:

```bash
TARO_APP_API_URL=http://localhost:3000/api
```

### App Configuration

Edit `src/app.config.ts` to configure:
- Pages routing
- Window style
- TabBar
- And more...

### Platform Configuration

Edit `project.config.json` for WeChat miniprogram specific settings:
- AppID
- Project name
- Compile settings

## API Integration

The template includes a ready-to-use API client (`src/services/api-client.ts`) with:
- Automatic auth token handling
- Request/response interceptors
- Error handling
- TypeScript support

Example usage:

```typescript
import apiClient from '@/services/api-client';

// GET request
const data = await apiClient.get('/users');

// POST request with JSON body
const result = await apiClient.post('/users/list', {
  page: 1,
  limit: 10,
  filters: { published: true }
});
```

## Adding New Pages

1. Create page directory in `src/pages/`:
```bash
mkdir -p src/pages/my-page
```

2. Create page files:
```
src/pages/my-page/
├── index.tsx        # Page component
├── index.scss       # Page styles
└── index.config.ts  # Page configuration
```

3. Register page in `src/app.config.ts`:
```typescript
export default defineAppConfig({
  pages: [
    'pages/index/index',
    'pages/my-page/index'  // Add new page
  ],
  // ...
});
```

4. If adding as a tab page, update tabBar configuration:
```typescript
tabBar: {
  list: [
    {
      pagePath: 'pages/index/index',
      text: 'Home',
      iconPath: './assets/images/home.png',
      selectedIconPath: './assets/images/home-active.png'
    },
    {
      pagePath: 'pages/my-page/index',  // New tab
      text: 'My Page',
      iconPath: './assets/images/mypage.png',
      selectedIconPath: './assets/images/mypage-active.png'
    }
    // Add 3-5 tabs total for complete navigation
  ]
}
```

## TabBar Navigation Setup

**Required: Configure 3-5 bottom tabs for your miniprogram**

1. **Plan your main sections** (e.g., 首页, 分类, 购物车, 我的)
2. **Create pages** for each tab in `src/pages/`
3. **Generate icons** using available skills/tools:
   - Request simple, recognizable icon designs
   - Common types: home, grid, cart, user, heart, message, search
   - Generate two versions: normal state (gray #666) and selected state (brand color)
   - Format: PNG/JPG/JPEG only (81x81px)
   - Save to `src/assets/images/`
4. **Configure tabBar** in `src/app.config.ts`:

```typescript
tabBar: {
  color: '#666',              // Normal state color
  selectedColor: '#1890ff',   // Selected state color
  backgroundColor: '#fafafa',
  borderStyle: 'black',
  list: [
    {
      pagePath: 'pages/index/index',
      text: '首页',  // Use Chinese text (2-4 chars)
      iconPath: './assets/images/home.png',
      selectedIconPath: './assets/images/home-active.png'
    },
    {
      pagePath: 'pages/category/index',
      text: '分类',
      iconPath: './assets/images/category.png',
      selectedIconPath: './assets/images/category-active.png'
    },
    {
      pagePath: 'pages/cart/index',
      text: '购物车',
      iconPath: './assets/images/cart.png',
      selectedIconPath: './assets/images/cart-active.png'
    },
    {
      pagePath: 'pages/profile/index',
      text: '我的',
      iconPath: './assets/images/profile.png',
      selectedIconPath: './assets/images/profile-active.png'
    }
  ]
}
```

**Common TabBar Patterns (Chinese Labels):**
- **E-commerce**: 首页, 分类, 购物车, 订单, 我的
- **Social**: 首页, 发现, 发布, 消息, 我的  
- **Service**: 首页, 服务, 预约, 我的
- **Content**: 首页, 探索, 收藏, 我的

**Icon Requirements:**
- **Format**: PNG/JPG/JPEG only (SVG is NOT supported by miniprogram platforms)
- **Size**: 81x81px recommended
- **States**: Two versions - normal (gray) and selected (brand color)
- **Generation**: Use available skills/tools to create simple, recognizable icons

**Note**: All pages listed in tabBar.list must be registered in the pages array first.

## Project Configuration

**IMPORTANT: .cloudstudio File**
- `.cloudstudio` is a **FILE** at project root, NOT a directory
- Format: TOML configuration for service startup
- Already provided in template - DO NOT modify or recreate as directory
- Common mistake: Creating `.cloudstudio/` folder instead of `.cloudstudio` file

## Environment Configuration

**IMPORTANT: Avoid Node.js APIs**

Taro runs in browser/miniprogram environments. Do NOT use Node.js APIs like `process.env`, `fs`, `path`, etc.

**Wrong:**
```typescript
// ❌ ERROR: process is not defined
const API_URL = process.env.TARO_APP_API_URL;
```

**Correct:**
```typescript
// ✅ Use proxy configuration
const API_URL = '/api';  // H5 dev server proxies to backend

// ✅ Or define constants in config/index.ts
export default {
  defineConstants: {
    API_BASE_URL: JSON.stringify('http://localhost:3000/api')
  }
};
```

**Use Taro APIs instead of Node.js:**
- Storage: `Taro.setStorageSync()` instead of `localStorage`
- Network: `Taro.request()` instead of `fetch`/`axios`
- File System: `Taro.getFileSystemManager()` instead of `fs`

## Styling

- Use Sass for styling (`.scss` files)
- Design width: 750px (configurable in `config/index.ts`)
- Automatic px to rpx conversion
- Global styles in `src/app.scss`

## TypeScript

The project is fully typed with TypeScript:
- Strict mode enabled
- Path alias: `@/*` maps to `src/*`
- Type definitions in `src/types/`

## Learn More

- [Taro Documentation](https://taro-docs.jd.com/)
- [React Documentation](https://react.dev/)
- [WeChat MiniProgram Documentation](https://developers.weixin.qq.com/miniprogram/dev/framework/)
