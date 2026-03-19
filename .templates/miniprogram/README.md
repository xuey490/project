# MiniProgram Template

A full-stack miniprogram template built with Taro framework and Express.js backend.

## Features

- 🚀 **Taro 4.x** - Cross-platform miniprogram framework supporting WeChat, Alipay, ByteDance, and more
- 🧭 **Bottom TabBar Navigation** - 3-5 tabs for complete user experience (default: 1 tab, expand as needed)
- 🎨 **React 18** - Modern React with TypeScript support
- 💎 **Express.js Backend** - RESTful API with Prisma ORM
- 🔐 **Authentication Ready** - JWT token handling built-in
- 📦 **TypeScript** - Full type safety across frontend and backend
- 🎯 **JSON-based API Protocol** - Clean and consistent API design

## Important: TabBar Navigation

**MiniPrograms require 3-5 bottom tab navigation items for optimal user experience.**

The template includes 1 default tab. You should expand this to 3-5 tabs based on your app type:
- **E-commerce**: Home, Categories, Cart, Orders, Profile
- **Social**: Feed, Discover, Publish, Messages, Profile
- **Service**: Home, Services, Appointments, Mine

Configure in `frontend/src/app.config.ts` and create corresponding pages.

## Quick Start

### Prerequisites

- Node.js >= 18
- PostgreSQL (for local development)

### Initialization

**For local development:**
```bash
git clone https://cnb.woa.com/cloudstudio/spec-template /tmp/spec-template && \
/tmp/spec-template/src/init.sh miniprogram && \
rm -rf /tmp/spec-template
```

**For CloudStudio environment:**
```bash
git clone https://cnb.woa.com/cloudstudio/spec-template /tmp/spec-template && \
/tmp/spec-template/src/init.sh miniprogram --cloudstudio && \
rm -rf /tmp/spec-template
```

The initialization script will:
1. Copy all template files to your project
2. Install dependencies for both backend and frontend
3. Set up the database
4. Start the services automatically

## Project Structure

```
/
├── backend/         # Express.js backend (symlinked to templates/backend)
│   ├── src/
│   ├── prisma/
│   └── package.json
├── frontend/        # Taro miniprogram frontend
│   ├── src/
│   │   ├── pages/
│   │   │   └── index/     # Home page
│   │   ├── components/
│   │   ├── services/
│   │   ├── utils/
│   │   └── app.tsx
│   ├── config/
│   └── package.json
└── docs/            # Documentation and design files
```

## Development

### Backend

```bash
cd backend
npm run dev              # Start development server
npm run prisma:migrate   # Run database migrations
npm run test             # Run tests
```

### Frontend

**WeChat MiniProgram:**
```bash
cd frontend
npm run dev:weapp        # Start WeChat development
npm run build:weapp      # Build for production
```

**Other Platforms:**
```bash
npm run dev:alipay       # Alipay MiniProgram
npm run dev:tt           # ByteDance MiniProgram
npm run dev:h5           # H5 (Browser)
```

## TabBar Navigation

**Required: 3-5 bottom tabs for complete user experience**

Generate tab icons using available skills/tools:
- Request simple, recognizable designs: home, grid, cart, user, heart, etc.
- Format: PNG/JPG/JPEG only (SVG not supported)
- Create normal state (gray) and selected state (colored) versions  
- Export as 81x81px PNG files
- Use Chinese labels (2-4 characters)

Common patterns:
- **E-commerce**: 首页, 分类, 购物车, 订单, 我的
- **Social**: 首页, 发现, 发布, 消息, 我的

Configure in `frontend/src/app.config.ts` tabBar section.

## Important Notes

**Project Configuration:**
- **`.cloudstudio` is a FILE, not a directory** - Common mistake to avoid
- This TOML file configures service startup
- Located at project root
- Do NOT create `.cloudstudio/` folder

**Avoid Node.js APIs in Taro:**
- ❌ Don't use `process.env` (causes "process is not defined" error)
- ✅ Use `/api` proxy or define constants in `config/index.ts`
- ✅ Use Taro APIs: `Taro.request()`, `Taro.setStorageSync()`, etc.

## API Integration

The frontend includes a ready-to-use API client that:
- Automatically adds auth tokens to requests
- Handles authentication errors (401)
- Provides type-safe request/response interfaces
- Uses JSON-based protocol for all data operations

Example usage:
```typescript
import apiClient from '@/services/api-client';

// List users with filters
const users = await apiClient.post('/users/list', {
  page: 1,
  limit: 10,
  filters: { active: true }
});
```

## Tech Stack

### Frontend
- **Taro 4.x** - Cross-platform miniprogram framework
- **Taro UI** - Multi-platform UI component library
- **React 18** - UI library
- **TypeScript** - Type safety
- **Sass** - CSS preprocessor
- **Taro Components** - Platform-agnostic components

### Backend
- **Express.js** - Web framework
- **Prisma** - ORM and database toolkit
- **PostgreSQL** - Database
- **Zod** - Schema validation
- **Pino** - Logging
- **Jest** - Testing

## Platform Support

This miniprogram can run on:
- WeChat MiniProgram (微信小程序)
- Alipay MiniProgram (支付宝小程序)
- ByteDance MiniProgram (抖音小程序)
- Baidu Smart Program (百度智能小程序)
- QQ MiniProgram (QQ小程序)
- JD MiniProgram (京东小程序)
- H5 (Mobile Web)

## Learn More

- [Taro Documentation](https://taro-docs.jd.com/)
- [Frontend README](./frontend/README.md) - Detailed frontend documentation
- [Backend README](./backend/README.md) - Detailed backend documentation
- [CODEBUDDY.md](./CODEBUDDY.md) - AI development guidelines
