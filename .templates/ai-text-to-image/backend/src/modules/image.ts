import { Router, Request, Response } from 'express'
import { prisma } from '../config/database'
import { requireAuth } from '../middleware/auth.middleware'
import { SaveImageSchema, GetImagesQuerySchema, DeleteImageSchema } from '../types/image.types'
import { logger } from '../config/logger'

export const imageRouter = Router()

// 所有路由都需要认证
imageRouter.use(requireAuth)

/**
 * GET /api/images
 * 获取用户的所有图片
 */
imageRouter.get('/', async (req: Request, res: Response) => {
  const userId = req.user!.id

  // 验证查询参数
  const parseResult = GetImagesQuerySchema.safeParse(req.query)
  if (!parseResult.success) {
    return res.status(400).json({
      error: '参数错误',
      details: parseResult.error.flatten().fieldErrors,
    })
  }

  const { page, limit } = parseResult.data
  const skip = (page - 1) * limit

  try {
    // 确保用户存在
    await ensureUserExists(userId, req.user!.email)

    // 获取图片列表和总数
    const [images, total] = await Promise.all([
      prisma.image.findMany({
        where: { userId },
        orderBy: { createdAt: 'desc' },
        skip,
        take: limit,
        select: {
          id: true,
          url: true,
          prompt: true,
          aspectRatio: true,
          quality: true,
          style: true,
          createdAt: true,
        },
      }),
      prisma.image.count({ where: { userId } }),
    ])

    return res.json({
      images,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    })
  } catch (error) {
    logger.error({ error, userId }, 'Failed to get images')
    return res.status(500).json({ error: '获取图片列表失败' })
  }
})

/**
 * POST /api/images
 * 保存图片到用户作品
 */
imageRouter.post('/', async (req: Request, res: Response) => {
  const userId = req.user!.id

  // 验证请求数据
  const parseResult = SaveImageSchema.safeParse(req.body)
  if (!parseResult.success) {
    return res.status(400).json({
      error: '参数错误',
      details: parseResult.error.flatten().fieldErrors,
    })
  }

  const { url, prompt, aspectRatio, quality, style } = parseResult.data

  try {
    // 确保用户存在
    await ensureUserExists(userId, req.user!.email)

    // 创建图片记录
    const image = await prisma.image.create({
      data: {
        userId,
        url,
        prompt,
        aspectRatio,
        quality,
        style,
      },
      select: {
        id: true,
        url: true,
        prompt: true,
        aspectRatio: true,
        quality: true,
        style: true,
        createdAt: true,
      },
    })

    logger.info({ imageId: image.id, userId }, 'Image saved')

    return res.status(201).json(image)
  } catch (error) {
    logger.error({ error, userId }, 'Failed to save image')
    return res.status(500).json({ error: '保存图片失败' })
  }
})

/**
 * DELETE /api/images/:id
 * 删除图片
 */
imageRouter.delete('/:id', async (req: Request, res: Response) => {
  const userId = req.user!.id

  // 验证参数
  const parseResult = DeleteImageSchema.safeParse({ id: req.params.id })
  if (!parseResult.success) {
    return res.status(400).json({
      error: '参数错误',
      details: parseResult.error.flatten().fieldErrors,
    })
  }

  const { id } = parseResult.data

  try {
    // 检查图片是否存在且属于当前用户
    const image = await prisma.image.findFirst({
      where: { id, userId },
    })

    if (!image) {
      return res.status(404).json({ error: '图片不存在' })
    }

    // 删除图片
    await prisma.image.delete({ where: { id } })

    logger.info({ imageId: id, userId }, 'Image deleted')

    return res.json({ success: true })
  } catch (error) {
    logger.error({ error, userId }, 'Failed to delete image')
    return res.status(500).json({ error: '删除图片失败' })
  }
})

/**
 * 确保用户记录存在（首次访问时自动创建）
 */
async function ensureUserExists(userId: string, email: string): Promise<void> {
  const user = await prisma.user.findUnique({ where: { id: userId } })
  
  if (!user) {
    await prisma.user.create({
      data: {
        id: userId,
        email,
      },
    })
    logger.info({ userId, email }, 'User created')
  }
}
