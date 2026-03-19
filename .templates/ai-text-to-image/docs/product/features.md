# ImageGen 产品需求文档 (PRD)

## 1. 产品概述

### 1.1 产品名称
ImageGen - AI 文生图创作工具

### 1.2 产品定位
ImageGen 是一款基于腾讯混元大模型的 AI 图片生成 Web 应用，用户可以通过输入文字描述词，快速生成高质量的 AI 图片。

### 1.3 目标用户
- 设计师：快速生成设计素材和灵感参考
- 内容创作者：为文章、社交媒体生成配图
- 普通用户：满足日常图片创作需求
- 开发者：测试和体验 AI 图片生成能力

### 1.4 技术栈
- **前端**: React + TypeScript + TailwindCSS + Vite
- **后端**: Node.js + Express + TypeScript
- **数据库**: PostgreSQL + Prisma ORM
- **认证**: Supabase Auth
- **AI 服务**: 腾讯云混元 TextToImageLite API

---

## 2. 功能模块

### 2.1 图片生成模块

#### 2.1.1 核心功能
| 功能 | 描述 | 优先级 |
|------|------|--------|
| 文字描述输入 | 支持中文描述词输入，最大 256 字符 | P0 |
| AI 图片生成 | 调用混元 API 根据描述词生成图片 | P0 |
| 实时预览 | 左右布局，右侧实时展示生成结果 | P0 |
| 生成状态 | 显示加载动画和进度提示 | P0 |

#### 2.1.2 高级设置
| 设置项 | 选项 | 说明 |
|--------|------|------|
| 图片尺寸 | 1:1 (1024x1024)、4:3 (1024x768)、16:9 (1280x720) | 控制生成图片的宽高比 |
| 清晰度 | 低、中、高 | 影响图片质量和生成时间 |
| 风格倾向 | 自动、写实、插画、扁平、二次元、水彩 | 控制图片的艺术风格 |

#### 2.1.3 风格说明
| 风格 | 描述 | Prompt 后缀 |
|------|------|-------------|
| 自动 | 由 AI 自动判断最合适的风格 | 无 |
| 写实 | 高清摄影风格，细节丰富 | realistic photography, high detail, professional lighting |
| 插画 | 数字插画风格，线条清晰 | digital illustration, clean lines, vibrant colors |
| 扁平 | 简约扁平设计风格 | flat design, minimalist, vector art style |
| 二次元 | 日式动漫风格 | anime style, Japanese animation, detailed character design |
| 水彩 | 艺术水彩画风格 | watercolor painting, soft edges, artistic brushstrokes |

#### 2.1.4 操作按钮
- **生成图片**: 根据当前设置生成图片
- **重新生成**: 使用相同参数重新生成
- **下载**: 将图片下载到本地
- **保存**: 保存到用户作品库（需登录）

---

### 2.2 用户认证模块

#### 2.2.1 登录方式
| 方式 | 描述 | 状态 |
|------|------|------|
| Google OAuth | 使用 Google 账号快速登录 | 已实现 |
| 邮箱密码 | 传统邮箱密码登录 | 已实现 |

#### 2.2.2 用户状态
- 游客模式：可以生成和下载图片，但不能保存到作品库
- 登录用户：享有完整功能，包括作品保存和管理

---

### 2.3 作品管理模块

#### 2.3.1 我的作品
| 功能 | 描述 | 优先级 |
|------|------|--------|
| 作品列表 | 网格布局展示已保存的图片 | P0 |
| 图片预览 | 点击图片查看大图 | P0 |
| 下载图片 | 将作品下载到本地 | P0 |
| 删除图片 | 移除不需要的作品 | P0 |
| 编辑描述词 | 复用描述词重新生成 | P1 |
| 分页加载 | 支持分页浏览大量作品 | P1 |

#### 2.3.2 数据存储
- 图片数据以 Base64 格式存储在 PostgreSQL 数据库
- 每张图片保存：URL、描述词、尺寸、质量、风格、创建时间
- 用户与图片关联，支持级联删除

---

### 2.4 帮助中心

#### 2.4.1 FAQ 内容
1. **描述词怎么写？** - 提供写作技巧和示例
2. **生成失败原因** - 常见错误分析和解决方案
3. **如何提高生成质量？** - 优化建议
4. **图片保存在哪里？** - 存储说明

---

### 2.5 法律合规页面

| 页面 | 路由 | 描述 |
|------|------|------|
| 隐私政策 | /privacy | 数据收集、使用、共享说明 |
| 用户协议 | /terms | 服务条款、使用规范、免责声明 |

---

## 3. 页面结构

### 3.1 页面路由
| 路由 | 页面 | 说明 |
|------|------|------|
| / | 首页 | 图片生成主界面 |
| /gallery | 我的作品 | 已保存图片管理 |
| /help | 帮助中心 | FAQ 和使用指南 |
| /login | 登录页 | 用户登录 |
| /register | 注册页 | 新用户注册 |
| /privacy | 隐私政策 | 隐私条款 |
| /terms | 用户协议 | 服务条款 |

### 3.2 页面布局
- **Header**: Logo、导航菜单（我的作品、帮助）、用户头像/登录按钮
- **Main**: 页面主体内容
- **Footer**: 版权信息、隐私政策、用户协议链接

### 3.3 首页布局（左右结构）
```
+------------------+------------------+
|    输入区域      |    结果区域      |
|  - 描述词输入    |  - 图片预览      |
|  - 高级设置      |  - 操作按钮      |
|  - 生成按钮      |  - 描述词显示    |
+------------------+------------------+
```

---

## 4. API 接口

### 4.1 图片生成 API
```
POST /api/generate
```
**请求参数**:
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| prompt | string | 是 | 描述词，最大 256 字符 |
| aspectRatio | string | 是 | 尺寸比例：1:1、4:3、16:9 |
| quality | string | 是 | 清晰度：low、medium、high |
| style | string | 是 | 风格：auto、realistic、illustration、flat、anime、watercolor |

**响应**:
```json
{
  "id": "uuid",
  "url": "data:image/png;base64,...",
  "prompt": "描述词",
  "aspectRatio": "1:1",
  "quality": "medium",
  "style": "auto",
  "createdAt": "2026-01-29T00:00:00.000Z"
}
```

### 4.2 图片管理 API

#### 获取图片列表
```
GET /api/images?page=1&limit=20
```
**响应**:
```json
{
  "images": [...],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 100,
    "totalPages": 5
  }
}
```

#### 保存图片
```
POST /api/images
```
**请求参数**:
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| url | string | 是 | 图片 Base64 URL |
| prompt | string | 是 | 描述词 |
| aspectRatio | string | 否 | 尺寸比例 |
| quality | string | 否 | 清晰度 |
| style | string | 否 | 风格 |

#### 删除图片
```
DELETE /api/images/:id
```

### 4.3 认证说明
- 图片生成 API 无需认证，任何用户都可使用
- 图片管理 API 需要 Bearer Token 认证
- Token 通过 Supabase Auth 获取

---

## 5. 数据库设计

### 5.1 User 表
| 字段 | 类型 | 说明 |
|------|------|------|
| id | string | 主键，与 Supabase Auth user.id 对应 |
| email | string | 用户邮箱，唯一 |
| name | string? | 用户昵称 |
| createdAt | datetime | 创建时间 |
| updatedAt | datetime | 更新时间 |

### 5.2 Image 表
| 字段 | 类型 | 说明 |
|------|------|------|
| id | uuid | 主键 |
| userId | string | 外键，关联 User |
| url | text | 图片 Base64 数据 |
| prompt | string | 描述词 |
| aspectRatio | string | 尺寸比例，默认 1:1 |
| quality | string | 清晰度，默认 medium |
| style | string | 风格，默认 auto |
| createdAt | datetime | 创建时间 |

### 5.3 索引
- Image 表: userId 索引（查询用户图片）
- Image 表: createdAt 索引（按时间排序）

---

## 6. 非功能需求

### 6.1 性能要求
- 图片生成响应时间：< 30 秒
- 页面首次加载时间：< 3 秒
- API 响应时间：< 500ms（不含图片生成）

### 6.2 安全要求
- 所有 API 通过 HTTPS 传输
- 用户密码通过 Supabase Auth 安全存储
- JWT Token 用于 API 认证
- 敏感配置通过环境变量管理

### 6.3 兼容性
- 浏览器：Chrome、Firefox、Safari、Edge 最新版本
- 设备：桌面端、平板、移动端自适应
- 小屏幕下左右布局自动变为上下布局

---

## 7. 未来规划

### 7.1 短期计划
- [ ] 图片历史记录（无需登录）
- [ ] 批量下载功能
- [ ] 图片分享功能
- [ ] 更多风格选项

### 7.2 长期计划
- [ ] 图片编辑功能（裁剪、滤镜）
- [ ] 图生图功能
- [ ] 会员订阅系统
- [ ] API 开放平台

---

## 8. 版本记录

| 版本 | 日期 | 更新内容 |
|------|------|----------|
| v1.0.0 | 2026-01-29 | 初始版本发布 |

---

## 附录

### A. 环境变量配置
```env
# 数据库
DATABASE_URL=postgresql://...

# Supabase
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_ANON_KEY=xxx

# 腾讯云
TENCENT_SECRET_ID=xxx
TENCENT_SECRET_KEY=xxx
TENCENT_REGION=ap-guangzhou
```

### B. 项目目录结构
```
├── frontend/                 # 前端项目
│   ├── src/
│   │   ├── components/       # 组件
│   │   │   ├── generator/    # 生成器组件
│   │   │   ├── layout/       # 布局组件
│   │   │   └── ui/           # UI 基础组件
│   │   ├── contexts/         # React Context
│   │   ├── hooks/            # 自定义 Hooks
│   │   └── pages/            # 页面组件
│   └── ...
├── backend/                  # 后端项目
│   ├── src/
│   │   ├── config/           # 配置
│   │   ├── middleware/       # 中间件
│   │   ├── modules/          # 业务模块
│   │   └── types/            # 类型定义
│   └── prisma/               # 数据库 Schema
└── docs/                     # 文档
    └── product/              # 产品文档
```
