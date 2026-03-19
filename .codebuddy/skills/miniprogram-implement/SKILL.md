---
name: miniprogram-implement
description: Implement miniprogram application with best practices, start's with "miniprogram" template, built with a fixed technology stack Taro, express, tailwindcss, typescript, postgreSQL, and DOES NOT support framework switching. This skill will init codebase and provide useful template information.
_meta_type: template
_meta_template_name: miniprogram
---

## Prerequisites

Initialize Project (if needed):
```bash
REPO_ROOT/.genie/scripts/bash/setup-project.sh miniprogram
```

## Core Principles

Carefully consider what the user wants, fully utilize your skills, find the right skills, and plan it.
- leverage the best design by SKILL: `ui-ux-pro-max`
- Use SKILL `text-to-image` for generating images, icons, or illustrations when needed.
- use the SKILL `*-integrator` to fully realize the user's needs.

## Best Practices

### General Principles

- **Perfect Architecture**: Refactor when needed, eliminate duplication, maintain clean separation
- **Parallel Tool Calls**: Execute independent operations simultaneously
- **Less is More**: Quality over quantity unless enterprise landing page requested
- **Leverage Existing Dependencies**: Prefer existing libraries over new ones

### MiniProgram-Specific (Automatic for All Pages)

- Chinese UI text for all user-facing content
- Mobile-first design (750px design width)
- Use px unit (28px body text, 88px button height) - auto-converted to rpx/rem
- Touch targets minimum 88px (44px physical)
- WeChat Green (#07C160) for primary actions
- Card-based layout with vertical scroll

## MiniProgram Template Architecture

```
/
├── backend/                        # Express.js + TypeScript + PostgreSQL
│   ├── package.json
│   ├── tsconfig.json
│   ├── jest.config.js
│   ├── .env.example / .env
│   ├── prisma/
│   │   ├── schema.prisma           # Database schema
│   │   └── migrations/
│   └── src/
│       ├── index.ts                # Server entry point
│       ├── app.ts                  # Express configuration
│       ├── config/
│       │   ├── env.ts              # Environment validation (Zod)
│       │   ├── database.ts         # Prisma client singleton
│       │   └── logger.ts           # Pino logger factory
│       ├── modules/                # Feature modules (routes + handlers)
│       │   └── [resource].ts
│       ├── types/
│       │   └── [resource].types.ts # Zod schemas
│       ├── middleware/
│       │   ├── errorHandler.ts
│       │   ├── validation.ts
│       │   └── logger.ts
│       └── __tests__/
│
└── frontend/                       # Taro 4.x + React 18
    ├── package.json
    ├── tsconfig.json
    ├── project.config.json         # WeChat miniprogram config
    ├── babel.config.js
    ├── config/                     # Taro build configuration
    │   ├── index.ts                # Base config (esnextModules: ['taro-ui'])
    │   ├── dev.ts
    │   └── prod.ts
    └── src/
        ├── app.tsx                 # Root component (Taro UI styles import)
        ├── app.config.ts           # App config (pages, window, tabBar)
        ├── app.scss                # Global styles
        ├── pages/
        │   └── [page]/
        │       ├── index.tsx
        │       ├── index.scss
        │       └── index.config.ts
        ├── components/             # Reusable components
        ├── services/
        │   └── api-client.ts       # Taro.request wrapper
        ├── assets/
        │   ├── images/
        │   └── tabbar/             # TabBar icons (PNG)
        └── types/
```


## Technical References

Detailed implementation patterns in `references/`:

- **[api-protocol.md](references/api-protocol.md)**: JSON-based API standard (CRITICAL - no URL parameters for data operations)
- **[frontend-patterns.md](references/frontend-patterns.md)**: Taro component mapping, navigation, storage, styling
- **[architecture.md](references/architecture.md)**: Complete monorepo structure, key files, commands
- **[publish-guide.md](references/publish-guide.md)**: 小程序发布流程指引（AppID/密钥获取、体验版、审核上架）

## Quick Reference

### Adding New Resources

**Frontend:**
1. Define types in `src/types/[resource].ts`
2. Create API functions in `src/services/[resource].ts` using JSON-based calls
3. Build Page/Components using Taro UI components

**Backend:**
1. Define model in `prisma/schema.prisma`
2. Create types in `src/types/[resource].types.ts`
3. Create module in `src/modules/[resource].ts`
4. Register router in `src/app.ts`

### Key Commands

```bash
# Backend
npm run prisma:migrate -- --name init

# Frontend
npm run dev:h5       # H5 development (browser testing)
npm run dev:weapp    # WeChat MiniProgram development

# Debugging
python3 "$PROJECT_ROOT/.genie/scripts/python/fetch_monitor_errors.py"
```

### Critical Rules

1. **JSON Protocol**: All data operations use POST with JSON body, not URL parameters
2. **Component Mapping**: Use Taro components (`View`, `Text`, `Image`), NOT HTML elements
3. **Navigation**: Use Taro APIs (`Taro.navigateTo`, `Taro.switchTab`), NOT React Router
4. **Storage**: Use `Taro.setStorageSync`/`getStorageSync`, NOT localStorage
5. **Taro UI Components**: Use existing components, DO NOT create custom versions
6. **AtInput onChange**: Receives value directly, NOT event object
7. **⚠️ NEVER USE process.env**: Causes "ReferenceError: process is not defined". Use `Taro.getEnv()` for environment detection
8. **ScrollView Padding**: Wrap content in View container, padding on ScrollView doesn't work
9. **Fixed Bottom**: Use `bottom: 100px` directly, SCSS conditional compilation doesn't work
10. **⚠️ 750px Design Width**: Use px unit based on 750px design (28px=14px physical, 88px=44px physical). Taro auto-converts to rpx/rem
11. **⚠️ API Client Import**: Use `apiClient.post()` NOT `{ post }` - see below
12. **⚠️ Zod Schema Structure**: Validation middleware expects `{ body, query, params }` wrapper - see below

### ⚠️ Common Runtime Errors - MUST AVOID

**Error 1: process is not defined**
```
ReferenceError: process is not defined
```
**Cause**: Using `process.env.XXX` anywhere in frontend code
**Solution**: Use `Taro.getEnv()` instead. Use existing `src/services/api-client.ts`.

**Error 2: post is not a function**
```
TypeError: (0 , _api_client__WEBPACK_IMPORTED_MODULE_2__.post) is not a function
```
**Cause**: Wrong import - trying to destructure non-existent named exports
**Solution**: 
```typescript
// ✅ CORRECT - use instance methods
import { apiClient } from '@/services/api-client';
// or
import apiClient from '@/services/api-client';

// Use full path with /api prefix
const data = await apiClient.post<ResponseType>('/api/users/list', body);
const data = await apiClient.get<ResponseType>('/api/health');

// ❌ WRONG - no named exports exist
import { post, get } from '@/services/api-client';  // ERROR!
```

**Error 3: Zod validation fails (400 Bad Request)**
```
400 Bad Request - Validation failed
```
**Cause**: Zod schema missing `body` wrapper
**Solution**:
```typescript
// ❌ WRONG - flat structure
export const createUserSchema = z.object({
  name: z.string(),
  phone: z.string()
});

// ✅ CORRECT - must wrap with body
export const createUserSchema = z.object({
  body: z.object({
    name: z.string(),
    phone: z.string()
  })
});
```

### Launch Configuration

`.cloudstudio` file defines startup - DO NOT modify. Always use unified process script.

---

## 小程序发布指引

### 发布流程

```
H5 预览开发 → 配置 AppID/密钥 → 生成体验版二维码 → 提交审核 → 正式发布
```

### 用户意图识别

当用户表达以下意图时，引导配置发布：
- "想发布小程序" / "生成体验版" / "上线小程序" / "怎么让别人用"

**响应**：
```
要生成体验版小程序，需要先配置 AppID 和代码上传密钥。

请点击上方「发布」按钮，在发布面板中填写配置信息。
```

### AppID/密钥获取指引

当用户询问如何获取 AppID 或密钥时：

```
获取 AppID 和密钥的步骤：

1. 登录微信公众平台 mp.weixin.qq.com
2. 进入「开发管理」→「开发设置」
3. AppID 在「开发者ID」栏目
4. 代码上传密钥在「小程序代码上传」栏目：
   - 先关闭「IP白名单」（必须关闭，否则云端上传会失败）
   - 点击「生成」下载私钥文件（.key）

密钥格式说明：用文本编辑器打开 .key 文件，完整复制内容（包括 -----BEGIN RSA PRIVATE KEY----- 和 -----END RSA PRIVATE KEY-----）。

请按照官网流程操作，获取后在发布面板中填写。
```

### 重复询问处理

当用户重复询问相同问题（如发截图问下一步）时：

```
请查看上文指引，以微信公众平台官网流程为准。

如果已获取到 AppID 和密钥，请在发布面板中填写配置信息。
```

### 详细指引

完整的发布流程、常见问题解答见 **[publish-guide.md](references/publish-guide.md)**

---

## ⛔ Final Phase: Build & Verify (强制执行)

**每次完成小程序功能开发后，必须执行此阶段。未通过验证的代码禁止交付。**

### Step 1: 后端编译验证（如果有后端改动）

```bash
cd backend && npx tsc --noEmit 2>&1
```

- 成功 → 继续 Step 2
- 失败 → 分析错误 → 修复代码 → 重试（最多 3 次）

### Step 2: 运行微信小程序构建

```bash
cd frontend && npm run build:weapp 2>&1
```

```
Attempt 1: npm run build:weapp
    │
    ├─ 成功 → ✅ 构建通过，/workspace/weapp/ 目录已生成最新微信小程序代码
    │
    └─ 失败 → 分析错误 → 修复代码 → 重试 (最多 3 次)
```

### 验证通过标准

- [ ] 后端 `npx tsc --noEmit` 无错误（如有后端改动）
- [ ] 前端 `npm run build:weapp` 退出码为 0
- [ ] 无 TypeScript 编译错误
- [ ] `/workspace/weapp/` 目录包含微信小程序产物

### Step 3: 构建成功后提示发布

构建通过后，**必须**输出以下两段提示，用明确标题区分：

```
小程序构建完成！🎉

**📱 【发布体验版】— 用于开发测试**

1. 获取 AppID：登录微信公众平台 (mp.weixin.qq.com) →「开发与服务」→「开发管理」→「开发设置」→「开发者ID」栏目中复制 AppID
2. 获取代码上传密钥：在同页面「小程序代码上传」栏目 → 先关闭「IP白名单」（必须关闭，否则上传会失败）→ 点击「生成」下载私钥文件（.key），用文本编辑器打开并复制完整内容
3. 点击上方「发布」按钮，在发布面板中填写 AppID 和代码上传密钥，点击发布
4. 发布成功后，面板上会展示体验版二维码和服务器域名。请复制服务器域名 → 前往微信公众平台同页面「服务器域名」栏目 → 点击「开始配置」→ 在 request 合法域名中粘贴添加（必须为 https 开头），否则小程序无法请求后端服务
5. 在微信公众平台「管理」→「版本管理」→「开发版本」中点击「选为体验版」
6. 在「管理」→「成员管理」中添加体验人员微信号，体验者扫描体验版二维码即可使用

**🚀 【上架正式版】— 提交审核上线**

1. 确认体验版功能测试无误
2. 登录微信公众平台 →「管理」→「版本管理」→「开发版本」
3. 点击「提交审核」，填写版本描述和功能页面信息
4. 等待微信官方审核通过（通常 1-3 个工作日，可申请加急 2 小时内）
5. 审核通过后，在「审核版本」中点击「全量发布」即可正式上线
```
