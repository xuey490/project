# 文生图技能 (Text-to-Image Skill)

基于腾讯混元大模型的文生图技能,支持从文本描述生成高质量图片。

## 功能特性

- 🎨 **多种风格**: 支持多种图片风格和质量设置
- 📐 **灵活尺寸**: 支持13种尺寸,从超高竖版到超宽横版
- 🎯 **智能优化**: 可选的 prompt 自动改写功能
- 🔧 **高度可配置**: 支持压缩级别、输出格式、水印等参数
- 🚀 **易于使用**: 提供命令行工具和完整的 API 文档

## 前置条件

### Python 依赖

需要安装 `requests` 库:

```bash
pip install requests
```

## 快速开始

### 基础用法

```bash
# 生成一张图片
python3 scripts/generate_image.py "一只可爱的橘猫坐在窗台上"

# 高质量图片
python3 scripts/generate_image.py "未来城市" --quality high

# 自定义尺寸
python3 scripts/generate_image.py "山水画" --size 768x1280

# 启用 prompt 改写
python3 scripts/generate_image.py "夕阳" --revise
```

### 高级用法

```bash
# 生成多张图片
python3 scripts/generate_image.py "抽象艺术" --n 3

# 透明背景
python3 scripts/generate_image.py "logo设计" --background transparent

# 添加水印
python3 scripts/generate_image.py "产品照片" --footnote "品牌名称"

# 保存响应到文件
python3 scripts/generate_image.py "风景" --output result.json

# 组合使用
python3 scripts/generate_image.py "专业产品摄影" \
  --size 1280x768 \
  --quality high \
  --output-format webp \
  --revise \
  --footnote "我的品牌"
```

## 参数说明

### 必填参数

- `prompt`: 图片描述文本

### 可选参数

| 参数 | 说明 | 可选值 | 默认值 |
|------|------|--------|--------|
| `--model` | 模型名称 | - | hunyuan-image-v3.0 |
| `--size` | 图片尺寸 | 640x1408, 704x1344, 768x1280, 832x1216, 896x1152, 960x1088, 1024x1024, 1088x960, 1152x896, 1216x832, 1280x768, 1344x704, 1408x640 | 1024x1024 |
| `--quality` | 图片质量 | low, medium, high | medium |
| `--output-format` | 输出格式 | png, webp, jpeg | png |
| `--n` | 生成数量 | 1-10 | 1 |
| `--background` | 背景透明度 | transparent, opaque | opaque |
| `--revise` | 改写 prompt | - | false |
| `--footnote` | 自定义水印 | 最多16字符 | - |
| `--output-compression` | 压缩级别 | 0-100 | - |
| `--response-format` | 响应格式 | url, b64_json | url |
| `--output` | 保存响应到文件 | 文件路径 | - |

## 技能结构

```
text-to-image/
├── SKILL.md                    # 技能主文档
├── README.md                   # 本文件
├── scripts/
│   └── generate_image.py       # 图片生成脚本
└── references/
    └── api_reference.md        # API 参考文档
```

## 使用示例

### 示例 1: 生成网站横幅

```bash
python3 scripts/generate_image.py \
  "壮丽的山脉风景,清晨的薄雾,柔和的光线" \
  --size 1280x768 \
  --quality high \
  --revise \
  --output-format webp
```

### 示例 2: 生成多个 logo 变体

```bash
python3 scripts/generate_image.py \
  "现代简约的科技公司logo,蓝色和白色配色" \
  --n 3 \
  --background transparent \
  --revise
```

### 示例 3: 生成带水印的产品照片

```bash
python3 scripts/generate_image.py \
  "专业的产品摄影,白色背景,柔和的工作室灯光" \
  --quality high \
  --footnote "品牌名称" \
  --revise
```

## 最佳实践

### Prompt 编写建议

✅ **好的 prompt**:
- 具体且详细
- 包含风格、构图、光线细节
- 使用描述性形容词
- 指定期望的氛围或情绪

❌ **不好的 prompt**:
- 过于简单和模糊
- 缺少细节描述

**示例对比**:
- ❌ "一只猫"
- ✅ "一只橘色的短毛猫坐在窗台上,温暖的阳光从窗外照进来,背景是模糊的室内环境"

### 参数选择建议

**生产环境**:
- 使用 `--quality high` 获得最佳质量
- 使用 `--revise` 改善 prompt 理解
- 使用 `--output-format webp` 优化网页加载
- 使用 `--output` 保存响应记录

**测试/迭代**:
- 使用 `--quality medium` 加快生成速度
- 使用 `--n` 生成多个变体
- 尝试不同尺寸找到最佳效果

## 错误处理

### API Key 未设置

```
错误: 环境变量 COPILOT_API_KEY 未设置。
请设置环境变量: export COPILOT_API_KEY=your_api_key
```

**解决方案**: 设置环境变量后重试

### API 请求失败

检查:
- 网络连接
- API Key 有效性
- API 端点可用性
- 错误响应消息

### 参数无效

确保:
- size 是: 640x1408, 704x1344, 768x1280, 832x1216, 896x1152, 960x1088, 1024x1024, 1088x960, 1152x896, 1216x832, 1280x768, 1344x704, 1408x640 之一
- quality 是: low, medium, high 之一
- format 是: png, webp, jpeg 之一
- footnote ≤ 16 字符
- n 在 1-10 之间

## 技能安装

### 方式 1: 从 zip 包安装

```bash
# 解压到 .codebuddy/skills 目录
unzip text-to-image.zip -d ~/.codebuddy/skills/
```

### 方式 2: 直接复制

```bash
# 复制整个目录
cp -r text-to-image ~/.codebuddy/skills/
```

## 在 CodeBuddy 中使用

安装技能后,在 CodeBuddy Code 中:

1. 技能会自动加载
2. 当用户请求生成图片时自动触发
3. 触发短语包括:
   - "generate an image"
   - "create a picture"
   - "draw me"
   - "make an image of"
   - "生成一张图片"
   - "创建图片"
   - "画一个"

## API 文档

详细的 API 文档请参考 `references/api_reference.md`,包括:
- 完整的参数规范
- 响应结构详情
- 字段描述和约束
- 请求/响应示例
- 错误处理指南

## 技术支持

如有问题或建议,请联系技能维护者。

## 许可证

本技能遵循项目许可证。
