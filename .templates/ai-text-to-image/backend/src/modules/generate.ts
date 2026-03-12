import { Router, Request, Response } from 'express'
import { GenerateImageSchema } from '../types/generate.types'
import { logger } from '../config/logger'
import { createClient } from '../lib/tencent-aiart'

export const generateRouter = Router()

// Create AI Art client
const aiartClient = createClient()

// 尺寸映射 - TextToImageLite 支持的分辨率
// 支持: 768:768, 768:1024, 1024:768, 1024:1024, 720:1280, 1280:720, 768:1280, 1280:768, 1080:1920, 1920:1080
const sizeMap: Record<string, string> = {
  '1:1': '1024:1024',
  '4:3': '1024:768',
  '16:9': '1280:720',
}

// 风格映射到 prompt 后缀
const stylePromptMap: Record<string, string> = {
  auto: '',
  realistic: ', realistic photography, high detail, professional lighting',
  illustration: ', digital illustration, clean lines, vibrant colors',
  flat: ', flat design, minimalist, vector art style',
  anime: ', anime style, Japanese animation, detailed character design',
  watercolor: ', watercolor painting, soft edges, artistic brushstrokes',
}

/**
 * POST /api/generate
 * 生成图片
 */
generateRouter.post('/', async (req: Request, res: Response) => {
  // 验证请求数据
  const parseResult = GenerateImageSchema.safeParse(req.body)
  
  if (!parseResult.success) {
    return res.status(400).json({
      error: '参数错误',
      details: parseResult.error.flatten().fieldErrors,
    })
  }

  const { prompt, aspectRatio, quality, style } = parseResult.data
  
  logger.info({ prompt, aspectRatio, quality, style }, 'Generating image')

  try {
    // 构建完整 prompt
    const stylePrompt = stylePromptMap[style] || ''
    const fullPrompt = prompt + stylePrompt

    // 获取尺寸
    const resolution = sizeMap[aspectRatio] || '768:768'

    // 调用混元 API 生成图片
    const hunyuanResponse = await aiartClient.textToImage(fullPrompt, {
      resolution,
      logoAdd: 0,
    })

    if (!hunyuanResponse.jobId) {
      throw new Error('图片生成失败')
    }

    logger.info({ jobId: hunyuanResponse.jobId }, 'Image generation job created')
    
    // 返回 jobId，前端通过 /api/aiart/query 轮询查询结果
    return res.json({
      success: true,
      data: {
        jobId: hunyuanResponse.jobId,
        prompt,
        aspectRatio,
        quality,
        style,
      },
    })
  } catch (error) {
    logger.error({ error, prompt }, 'Failed to generate image')
    return res.status(500).json({
      error: '图片生成失败，请稍后重试',
    })
  }
})