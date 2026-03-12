# 腾讯混元图生图 API 参考文档

## API 概述

腾讯混元图生图 API 允许用户基于文本描述对现有图片进行智能编辑和变换。支持风格转换、背景替换、色彩调整等多种图片处理功能。

## 基础信息

- **API 端点**: `https://staging-copilot.tencent.com/v2/images/edits`
- **接口路径**: /v2/images/edits
- **请求方法**: POST
- **认证方式**: Bearer Token
- **模型**: `hunyuan-image-v2.0-general-edit-v1.0.0`

#### model (string)
使用的AI模型名称。

**推荐值**: "hunyuan-image-v2.0-general-edit-v1.0.0"

## 请求格式

### 请求头

```http
Content-Type: application/json
Authorization: Bearer {your_api_key}
```

### 请求体

```json
{
  "prompt": "给这个人的嘴里P一根黄瓜",
  "image": "base64_encoded_image_data",
  "model": "hunyuan-image-v2.0-general-edit-v1.0.0",
  "n": 1,
  "output_format": "png",
  "partial_images": 0,
  "quality": "medium",
  "response_format": "url",
  "size": "1024x1024",
  "stream": false,
  "strength": 0.8,
  "revise":{
    "value": true
  },
  "moderation": {
    "value": false
  }
}
```

## 参数详细说明

### 必填参数

| 参数 | 类型 | 说明 |
|------|------|------|
| `prompt` | string | 编辑描述文本,描述希望对图片进行的修改 |
| `image` | string | 输入图片的 base64 编码字符串 |
| `image_url` | string | 上传图片url，大小不超过 10M, 与 image 参数二选一 |

### 可选参数

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `model` | string | 默认"hunyuan-image-v2.0-general-edit-v1.0.0" | 使用的模型名称 |
| `background` | string | null | 背景透明度设置 ("transparent", "opaque") |
| `moderation` | string | null | 内容审核级别 |
| `n` | integer | 1 | 生成图片数量 (1-10) |
| `output_compression` | integer | null | 图片压缩级别 (0-100) |
| `output_format` | string | "png" | 输出格式 ("png", "webp", "jpeg") |
| `partial_images` | integer | 0 | 部分图片数量 (0-3) |
| `quality` | string | "medium" | 图片质量 ("low", "medium", "high") |
| `response_format` | string | "url" | 响应格式 ("url", "b64_json") |
| `size` | string | "1024x1024" | 输出图片尺寸 |
| `stream` | boolean | false | 是否启用流式模式 |
| `style` | string | null | 图片风格 |
| `user` | string | null | 用户唯一标识符 |
| `footnote` | string | null | 自定义水印 (最多16个字符) |
| `revise` | object | null | 是否改写prompt ({"value": true}) |
| `strength` | float | 0.8 | 编辑强度 (0.0-1.0) |

### 支持的图片尺寸

| 尺寸 | 宽高比 | 用途 |
|------|--------|------|
| 640x1408 | 5:11 | 超高竖版 |
| 704x1344 | 11:21 | 很高竖版 |
| 768x1280 | 3:5 | 高竖版 |
| 832x1216 | 13:19 | 竖版 |
| 896x1152 | 7:9 | 竖版 |
| 960x1088 | 15:17 | 竖版 |
| 1024x1024 | 1:1 | 正方形 (默认) |
| 1088x960 | 17:15 | 横版 |
| 1152x896 | 9:7 | 横版 |
| 1216x832 | 19:13 | 横版 |
| 1280x768 | 5:3 | 宽横版 |
| 1344x704 | 21:11 | 很宽横版 |
| 1408x640 | 11:5 | 超宽横版 |

## 响应格式

### 成功响应

```json
{
  "code": 0,
  "msg": "OK",
  "requestId": "req_1234567890abcdef",
  "data": {
    "created": 1761902312,
    "data": [
      {
        "url": "https://example.com/edited_image.png",
        "revised_prompt": "增强后的编辑描述..."
      }
    ]
  }
}
```

### 响应字段说明

| 字段 | 类型 | 说明 |
|------|------|------|
| `code` | integer | 响应状态码 (0表示成功) |
| `msg` | string | 响应消息 |
| `requestId` | string | 请求唯一标识符 |
| `data.created` | integer | 创建时间戳 |
| `data.data` | array | 编辑结果数组 |
| `data.data[].url` | string | 编辑后图片的 URL |
| `data.data[].revised_prompt` | string | 改写后的编辑描述 (如果启用了 revise) |

### 错误响应

```json
{
  "code": 40001,
  "msg": "Invalid parameter: prompt is required",
  "requestId": "req_1234567890abcdef"
}
```

## 常见错误码

| 错误码 | 说明 | 解决方案 |
|--------|------|----------|
| 40001 | 参数错误 | 检查必填参数是否提供 |
| 40002 | 图片格式不支持 | 使用支持的图片格式 (PNG, JPEG, WebP) |
| 40003 | 图片大小超限 | 减小图片文件大小 |
| 40004 | prompt 过长 | 缩短编辑描述文本 |
| 40101 | API Key 无效 | 检查 API Key 是否正确 |
| 40301 | 配额不足 | 检查账户余额或配额 |
| 50001 | 服务器内部错误 | 稍后重试 |
| 50002 | 服务暂时不可用 | 稍后重试 |

## 使用限制

### 输入图片限制

- **格式**: PNG, JPEG, WebP
- **大小**: 建议 1MB - 10MB
- **分辨率**: 最小 64x64, 最大根据 API 限制
- **编码**: 必须转换为 base64 格式

### 请求限制

- **并发**: 不支持并行请求,必须顺序调用
- **频率**: 根据账户配额限制
- **超时**: 请求超时时间 120 秒

### 内容限制

- **prompt**: 最大长度限制
- **footnote**: 最多 16 个字符
- **生成数量**: 1-10 张图片

## 最佳实践

### 图片预处理

1. **格式转换**: 确保图片为支持的格式
2. **大小优化**: 压缩图片到合适大小
3. **质量检查**: 确保图片清晰无损坏

### Prompt 编写

1. **具体描述**: 详细说明要进行的编辑
2. **包含细节**: 指定风格、色彩、光线等要求
3. **使用动词**: 用动作词描述具体操作
4. **避免歧义**: 使用清晰明确的表达

### 参数选择

1. **编辑强度**: 根据编辑需求选择合适的 strength 值
2. **质量设置**: 生产环境使用 high,测试使用 medium
3. **格式选择**: 网页使用 webp,印刷使用 png
4. **尺寸选择**: 根据用途选择合适的尺寸

## 示例代码

### Python 示例

```python
import requests
import base64
import json

def edit_image(prompt, image_path, api_key):
    # 读取并编码图片
    with open(image_path, 'rb') as f:
        image_data = base64.b64encode(f.read()).decode('utf-8')
    
    # 构建请求
    url = "https://staging-copilot.tencent.com/v2/images/edits"
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {api_key}"
    }
    payload = {
        "prompt": prompt,
        "image": image_data,
        "model": "hunyuan-image-v2.0-general-edit-v1.0.0",
        "quality": "high",
        "strength": 0.7
    }
    
    # 发送请求
    response = requests.post(url, headers=headers, json=payload)
    return response.json()

# 使用示例
result = edit_image(
    "转换为油画风格", 
    "input.jpg", 
    "your_api_key"
)
print(json.dumps(result, indent=2, ensure_ascii=False))
```

### cURL 示例

```bash
curl -X POST "https://staging-copilot.tencent.com/v2/images/edits" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_api_key" \
  -d '{
    "prompt": "增强色彩饱和度,改善光线",
    "image": "base64_encoded_image_data",
    "model": "hunyuan-image-v2.0-general-edit-v1.0.0",
    "quality": "high",
    "strength": 0.6,
    "revise": {"value": true}
  }'
```

## 性能优化

### 请求优化

1. **顺序调用**: 避免并行请求
2. **合理超时**: 设置适当的超时时间
3. **错误重试**: 实现指数退避重试机制

### 图片优化

1. **预处理**: 在上传前优化图片大小
2. **格式选择**: 根据用途选择最佳格式
3. **缓存结果**: 缓存编辑结果避免重复请求

### 成本控制

1. **批量处理**: 合理规划批量编辑任务
2. **参数优化**: 选择合适的质量和数量参数
3. **结果复用**: 保存和复用编辑结果

## 故障排除

### 常见问题

1. **图片编码错误**: 确保 base64 编码正确
2. **参数验证失败**: 检查所有参数格式和范围
3. **网络超时**: 增加超时时间或检查网络连接
4. **API 限制**: 检查配额和频率限制

### 调试建议

1. **日志记录**: 记录请求和响应详情
2. **错误处理**: 实现完善的错误处理机制
3. **状态监控**: 监控 API 调用状态和性能
4. **测试验证**: 使用小图片进行功能测试

## 更新日志

### v2.0 (当前版本)
- 支持图生图编辑功能
- 新增编辑强度控制
- 优化图片处理算法
- 增强错误处理机制

### 未来计划
- 支持更多图片格式
- 增加批量处理功能
- 优化处理速度
- 扩展编辑功能

## 技术支持

如需技术支持,请提供:
1. 完整的错误信息
2. 请求参数和响应
3. 输入图片信息
4. 环境配置详情

联系方式: [技术支持邮箱或链接]