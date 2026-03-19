# 文生图技能安装指南

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

技能已经位于 `.codebuddy/skills/text-to-image` 目录,无需额外安装步骤。

## 验证安装

运行以下命令测试技能是否正常工作:

```bash
python3 .codebuddy/skills/text-to-image/scripts/generate_image.py "测试图片" --help
```

如果看到帮助信息,说明技能已正确安装。

## 测试生成

```bash
python3 .codebuddy/skills/text-to-image/scripts/generate_image.py "一只可爱的小猫"
```

如果成功,将看到:
- JSON 响应
- 生成的图片 URL
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

### 问题 3: 权限问题

**错误**: `Permission denied`

**解决方案**:
```bash
chmod +x .codebuddy/skills/text-to-image/scripts/generate_image.py
```

## 在 CodeBuddy 中使用

安装完成后,在 CodeBuddy Code 对话中:

1. 直接请求生成图片:
   - "生成一张图片: 夕阳下的海滩"
   - "帮我画一个未来城市"
   - "create an image of a cat"

2. 技能会自动触发并调用脚本

3. 查看生成的图片 URL

## 更多信息

详细使用说明请参考:
- `README.md` - 完整的使用指南
- `SKILL.md` - 技能文档
- `references/api_reference.md` - API 参考
