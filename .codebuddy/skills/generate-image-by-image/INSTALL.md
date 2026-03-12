# 图生图技能安装指南

## 快速安装

### 前置条件

1. **设置 API Key 环境变量**:
   ```bash
   export COPILOT_API_KEY=your_api_key
   ```

2. **安装 Python 依赖**:
   ```bash
   pip install requests
   ```

### 安装技能

技能已经位于 `.codebuddy/skills/generate-image-by-image` 目录,无需额外安装步骤。

## 验证安装

运行以下命令测试技能是否正常工作:

```bash
python3 .codebuddy/skills/generate-image-by-image/scripts/edit_image.py --help
```

如果看到帮助信息,说明技能已正确安装。

## 测试编辑

准备一张测试图片,然后运行:

```bash
python3 .codebuddy/skills/generate-image-by-image/scripts/edit_image.py "增强色彩" --image-url https://hunyuan-base-prod-1258344703.cos.ap-guangzhou.myqcloud.com/text2image/test/text2video/0.jpg
```

或者

```bash
python3 .codebuddy/skills/generate-image-by-image/scripts/edit_image.py "去除背景" --image-url /Users/huailehang/Desktop/Giene/skill_test/frontend/public/edited_r84wiz_1.png
```

如果成功,将看到:
- JSON 响应
- 编辑后的图片 URL
- 改写后的 prompt (如果使用了 --revise)

## 故障排除

### 问题 1: 环境变量未设置

**错误**: `环境变量 COPILOT_API_KEY 未设置`

**解决方案**:
```bash
export COPILOT_API_KEY=your_api_key
```

### 问题 2: requests 模块未找到

**错误**: `ModuleNotFoundError: No module named 'requests'`

**解决方案**:
```bash
pip install requests
```

### 问题 3: 图片文件不存在

**错误**: `图片文件不存在: /path/to/image.jpg`

**解决方案**:
- 检查图片路径是否正确
- 确保图片文件存在
- 使用绝对路径或相对于当前目录的路径

### 问题 4: 权限问题

**错误**: `Permission denied`

**解决方案**:
```bash
chmod +x .codebuddy/skills/generate-image-by-image/scripts/edit_image.py
```

### 问题 5: 图片格式不支持

**错误**: API 返回格式错误

**解决方案**:
- 确保输入图片是 PNG、JPEG 或 WebP 格式
- 检查图片文件是否损坏
- 尝试转换图片格式

## 支持的图片格式

### 输入格式
- PNG (.png)
- JPEG (.jpg, .jpeg)
- WebP (.webp)

### 输出格式
- PNG (默认,最高质量)
- WebP (平衡质量和大小)
- JPEG (较小文件大小)

## 图片大小限制

- 建议图片大小: 1MB - 10MB
- 最大分辨率: 根据 API 限制
- 最小分辨率: 64x64 像素

## 在 CodeBuddy 中使用

安装完成后,在 CodeBuddy Code 对话中:

1. 直接请求编辑图片:
   - "编辑这张图片: 增强色彩"
   - "把这张照片转换为油画风格"
   - "edit this image to remove background"

2. 技能会自动触发并调用脚本

3. 查看编辑后的图片 URL

## 性能优化建议

### 快速测试
- 使用 `--quality medium`
- 使用较小的 `--strength` 值 (0.3-0.5)
- 避免使用 `--revise` (除非需要)

### 生产使用
- 使用 `--quality high`
- 根据需求调整 `--strength`
- 启用 `--revise` 获得更好效果
- 使用 `--output-format webp` 优化文件大小

### 批量处理
- 避免并行调用 API (不支持)
- 使用脚本循环处理多张图片
- 考虑添加延迟避免频率限制

## 更多信息

详细使用说明请参考:
- `README.md` - 完整的使用指南
- `SKILL.md` - 技能文档
- `references/api_reference.md` - API 参考

## 技术支持

如遇到问题:
1. 检查网络连接
2. 验证 API Key 有效性
3. 确认图片格式和大小符合要求
4. 查看错误日志获取详细信息
5. 联系技能维护者获取支持