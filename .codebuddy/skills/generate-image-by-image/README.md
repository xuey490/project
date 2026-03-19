# 图生图技能 (Generate-Image-By-Image Skill)

基于腾讯混元大模型的图生图技能,支持对现有图片进行编辑和变换。

## 功能特性

- 🎨 **图片编辑**: 基于图片对图片进行智能编辑
- 📐 **灵活尺寸**: 支持13种尺寸,从超高竖版到超宽横版,最大支持 10MB 大小图片
- 🎯 **智能优化**: 可选的 prompt 自动改写功能
- 🔧 **高度可配置**: 支持编辑强度、压缩级别、输出格式、水印等参数
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
# 编辑一张图片
python3 scripts/edit_image.py "把这只猫变成橘色" --image-url cat.jpg

# 高质量编辑
python3 scripts/edit_image.py "添加夕阳背景" --image-url landscape.png --quality high

# 自定义尺寸
python3 scripts/edit_image.py "转换为油画风格" --image-url portrait.jpg --size 768x1280

# 启用 prompt 改写
python3 scripts/edit_image.py "增强色彩饱和度" --image-url photo.jpg --revise
```

### 多图片生成用法

```bash
# 使用相同描述生成多个版本
python3 scripts/edit_image.py "人像美化" --image-url selfie.jpg --count 5

# 使用多个不同描述
python3 scripts/edit_image.py --image-url portrait.jpg --prompts "转换为油画风格" "增强色彩饱和度" "添加艺术滤镜"

# 使用不同编辑强度
python3 scripts/edit_image.py "风景美化" --image-url landscape.jpg --strengths 0.3 0.5 0.7 0.9

# 组合多个描述和强度
python3 scripts/edit_image.py --image-url photo.jpg --prompts "专业修图" "艺术化处理" "色彩增强" --strengths 0.4 0.6 0.8
```

### 高级用法

```bash
# 透明背景处理
python3 scripts/edit_image.py "去除背景" --image-url product.jpg --background transparent

# 添加水印
python3 scripts/edit_image.py "专业修图" --image-url photo.jpg --footnote "品牌名称"

# 调整编辑强度
python3 scripts/edit_image.py "轻微美化" --image-url selfie.jpg --strength 0.3

# 保存响应到文件
python3 scripts/edit_image.py "艺术化处理" --image-url artwork.png --output result.json

# 组合使用 - 生成多个高质量版本
python3 scripts/edit_image.py --image-url product.jpg \
  --prompts "专业产品摄影后期处理" "增强产品细节" "优化光线效果" \
  --strengths 0.5 0.6 0.7 \
  --size 1280x768 \
  --quality high \
  --output-format webp \
  --revise \
  --footnote "我的品牌"
```

## 参数说明

### 必填参数

- `--image-url`: 输入图片路径
- `image`: 输入图片的 base64 编码字符串
- `prompt`: 文本prompt，token数限制为256以下。

### 编辑描述参数 (三选一)

| 参数 | 说明 | 示例 |
|------|------|------|
| `prompt` | 单个编辑描述 | `"转换为油画风格"` |
| `--prompts` | 多个编辑描述 | `--prompts "美化" "艺术化" "增强色彩"` |
| `--count` | 相同描述生成多个版本 | `--count 5` (需配合单个prompt) |

### 编辑强度参数

| 参数 | 说明 | 可选值 | 默认值 |
|------|------|--------|--------|
| `--strength` | 单个编辑强度 | 0.0-1.0 | 0.8 |
| `--strengths` | 多个编辑强度 | 0.0-1.0的多个值 | - |

### 其他可选参数

| 参数 | 说明 | 可选值 | 默认值 |
|------|------|--------|--------|
| `--model` | 模型名称 | - | hunyuan-image-v2.0-general-edit-v1.0.0 |
| `--size` | 图片尺寸 | 640x1408, 704x1344, 768x1280, 832x1216, 896x1152, 960x1088, 1024x1024, 1088x960, 1152x896, 1216x832, 1280x768, 1344x704, 1408x640 | 1024x1024 |
| `--quality` | 图片质量 | low, medium, high | medium |
| `--output-format` | 输出格式 | png, webp, jpeg | png |
| `--background` | 背景透明度 | transparent, opaque | opaque |
| `--revise` | 改写 prompt | - | false |
| `--footnote` | 自定义水印 | 最多16字符 | - |
| `--output-compression` | 压缩级别 | 0-100 | - |
| `--response-format` | 响应格式 | url, b64_json | url |
| `--output` | 保存响应到文件 | 文件路径 | - |

## 技能结构

```
generate-image-by-image/
├── SKILL.md                    # 技能主文档
├── README.md                   # 本文件
├── INSTALL.md                  # 安装指南
├── scripts/
│   └── edit_image.py           # 图片编辑脚本
└── references/
    └── api_reference.md        # API 参考文档
```

## 使用示例

### 示例 1: 单一风格转换

```bash
python3 scripts/edit_image.py \
  "转换为梵高风格的油画" \
  --image-url landscape.jpg \
  --quality high \
  --strength 0.7 \
  --revise
```

### 示例 2: 多种风格转换

```bash
python3 scripts/edit_image.py \
  --image-url portrait.jpg \
  --prompts "转换为油画风格" "转换为水彩画风格" "转换为素描风格" \
  --quality high \
  --revise
```

### 示例 3: 不同强度的相同编辑

```bash
python3 scripts/edit_image.py \
  "人像美化" \
  --image-url selfie.jpg \
  --count 4 \
  --strengths 0.2 0.4 0.6 0.8 \
  --quality high
```

### 示例 4: 产品图片多版本优化

```bash
python3 scripts/edit_image.py \
  --image-url product.jpg \
  --prompts "专业产品摄影后期处理" "增强产品细节和质感" "优化光线和阴影" \
  --strengths 0.5 0.6 0.7 \
  --quality high \
  --footnote "品牌名称" \
  --output-format webp
```

### 示例 5: 风景照多效果处理

```bash
python3 scripts/edit_image.py \
  --image-url landscape.png \
  --prompts "增强色彩饱和度" "添加戏剧性光线" "转换为HDR效果" "添加艺术滤镜" \
  --count 4 \
  --quality high \
  --revise
```

## 最佳实践

### Prompt 编写建议

✅ **好的 prompt**:
- 具体描述要进行的编辑
- 包含风格、色彩、光线等细节
- 使用动词描述具体操作
- 指定期望的视觉效果

❌ **不好的 prompt**:
- 过于模糊的描述
- 缺少具体的编辑指令

**示例对比**:
- ❌ "改善这张图片"
- ✅ "增强色彩饱和度,调整曝光使图片更明亮,添加温暖的色调"

### 参数选择建议

**编辑强度 (strength)**:
- `0.1-0.3` - 轻微调整 (色彩校正、轻微美化)
- `0.4-0.6` - 中等编辑 (风格调整、背景修改)
- `0.7-0.9` - 强烈变换 (风格转换、大幅修改)
- `1.0` - 最大变换 (完全重新生成)

**生产环境**:
- 使用 `--quality high` 获得最佳质量
- 使用 `--revise` 改善 prompt 理解
- 使用 `--output-format webp` 优化网页加载
- 使用 `--output` 保存响应记录

**测试/迭代**:
- 使用 `--quality medium` 加快生成速度
- 使用 `--n` 生成多个变体
- 调整 `--strength` 找到最佳编辑强度

## 错误处理

### API Key 未设置

```
错误: 环境变量 COPILOT_API_KEY 未设置。
请设置环境变量: export COPILOT_API_KEY=your_api_key
```

**解决方案**: 设置环境变量后重试

### 图片文件不存在

```
文件错误: 图片文件不存在: /path/to/image.jpg
```

**解决方案**: 检查图片路径是否正确

### API 请求失败

检查:
- 网络连接
- API Key 有效性
- 图片文件格式是否支持
- 图片文件大小是否超限

### 参数无效

确保:
- size 是支持的尺寸之一
- quality 是: low, medium, high 之一
- format 是: png, webp, jpeg 之一
- footnote ≤ 16 字符
- n 在 1-10 之间
- strength 在 0.0-1.0 之间

## 技能安装

### 方式 1: 从 zip 包安装

```bash
# 解压到 .codebuddy/skills 目录
unzip generate-image-by-image.zip -d ~/.codebuddy/skills/
```

### 方式 2: 直接复制

```bash
# 复制整个目录
cp -r generate-image-by-image ~/.codebuddy/skills/
```

## 在 CodeBuddy 中使用

安装技能后,在 CodeBuddy Code 中:

1. 技能会自动加载
2. 当用户请求编辑图片时自动触发
3. 触发短语包括:
   - "edit this image"
   - "modify the picture"
   - "change the photo"
   - "transform this image"
   - "编辑这张图片"
   - "修改图片"
   - "改变照片"
   - "转换图像"

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