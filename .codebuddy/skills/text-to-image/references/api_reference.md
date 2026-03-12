# 腾讯混元文生图 API 参考文档

## API 基本信息

- **接口域名**: https://staging-copilot.tencent.com
- **接口路径**: /v2/images/generations
- **请求方法**: POST
- **Content-Type**: application/json
- **Authorization**: Bearer API_KEY (从环境变量 COPILOT_API_KEY 获取)

## 请求参数

### 必填参数

#### prompt (string)
图片描述文本，用于生成图片的提示词。

**示例**: "一只可爱的橘猫坐在窗台上"

#### model (string)
使用的AI模型名称。

**推荐值**: "hunyuan-image-v3.0"

### 可选参数

#### background (string)
背景透明度设置。

- **可选值**: 
  - `transparent` - 透明背景
  - `opaque` - 不透明背景
- **默认值**: opaque

#### moderation (string)
内容审核级别。

**示例**: "strict"

#### n (int32)
生成图片数量。

- **取值范围**: 1-10
- **默认值**: 1

#### output_compression (int32)
图片压缩级别。

- **取值范围**: 0-100
  - 0 - 无压缩
  - 100 - 最大压缩

#### output_format (string)
输出格式。

- **可选值**: png / webp / jpeg
- **默认值**: png

#### partial_images (int32)
部分图片数量，用于流式响应。

- **取值范围**: 0-3
- **默认值**: 0

#### quality (string)
图片质量。

- **可选值**:
  - `low` - 低质量
  - `medium` - 中等质量
  - `high` - 高质量
- **默认值**: medium

#### response_format (string)
响应格式。

- **可选值**:
  - `url` - 返回图片URL
  - `b64_json` - 返回base64编码
- **默认值**: url

#### size (string)
图片尺寸。

- **可选值**:
  - `640x1408` - 超高竖版
  - `704x1344` - 非常高竖版
  - `768x1280` - 高竖版
  - `832x1216` - 竖版
  - `896x1152` - 竖版
  - `960x1088` - 竖版
  - `1024x1024` - 正方形
  - `1088x960` - 横版
  - `1152x896` - 横版
  - `1216x832` - 横版
  - `1280x768` - 宽横版
  - `1344x704` - 非常宽横版
  - `1408x640` - 超宽横版
- **默认值**: 1024x1024

#### stream (bool)
是否启用流式模式。

- **默认值**: false
- **注意**: 目前仅支持非流式

#### style (string)
图片风格。

**示例**: "vivid"

#### user (string)
终端用户唯一标识符，用于监控和滥用检测。

**示例**: "user-12345"

#### footnote (string)
业务自定义水印内容（混元模型支持）。

- **限制**: 16个字符

#### revise (object)
是否对prompt进行改写优化（混元模型支持）。

- **revise.value** (bool): 是否改写prompt
  - **默认值**: false

## 响应结构

### CreateImageResponse

```json
{
  "code": 0,
  "msg": "OK",
  "requestId": "string",
  "data": {
    "created": 1761902312,
    "data": [
      {
        "revised_prompt": "string",
        "url": "string",
        "b64_json": "string"
      }
    ],
    "background": "string",
    "output_format": "string",
    "quality": "string",
    "size": "string",
    "usage": {
      "input_tokens": 0,
      "input_tokens_details": {
        "text_tokens": 0,
        "image_tokens": 0
      },
      "output_tokens": 0,
      "total_tokens": 0
    }
  }
}
```

### 响应字段说明

#### code (int)
响应状态码。
- 0 表示成功

#### msg (string)
响应消息。

#### requestId (string)
请求唯一标识符。

#### data.created (int64)
图片创建时间的Unix时间戳（秒）。

#### data.data (ImageData[])
生成的图片列表。

##### ImageData 字段

- **b64_json** (string): base64编码的图片数据（当response_format为b64_json时返回）
- **revised_prompt** (string): 修订后的提示词（当revise为true时返回）
- **url** (string): 图片URL（当response_format为url时返回）

#### data.background (string)
图片生成使用的背景参数。

#### data.output_format (string)
图片输出格式。

#### data.quality (string)
生成的图片质量。

#### data.size (string)
生成的图片尺寸。

#### data.usage (ImageUsage)
Token使用信息。

- **input_tokens** (int32): 输入提示词中的token数量
- **input_tokens_details** (object): 输入token详细信息
  - **text_tokens** (int32): 文本token数量
  - **image_tokens** (int32): 图片token数量
- **output_tokens** (int32): 模型生成的输出token数量
- **total_tokens** (int32): 总token数量

## 请求示例

```bash
curl -X POST "https://staging-copilot.tencent.com/v2/images/generations" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{
    "prompt": "未来城市",
    "model": "hunyuan-image-v3.0",
    "footnote": "版权所有",
    "revise": {
        "value": true
    },
    "n": 1,
    "size": "768x1280",
    "quality": "high"
}'
```

## 错误处理

当请求失败时，响应中的 `code` 字段将为非零值，`msg` 字段将包含错误描述。
