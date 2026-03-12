import { z } from 'zod'

export const SaveImageSchema = z.object({
  url: z.string().min(1, '图片URL不能为空'),
  prompt: z.string().min(1, '描述词不能为空'),
  aspectRatio: z.string().default('1:1'),
  quality: z.string().default('medium'),
  style: z.string().default('auto'),
})

export type SaveImageInput = z.infer<typeof SaveImageSchema>

export const GetImagesQuerySchema = z.object({
  page: z.coerce.number().min(1).default(1),
  limit: z.coerce.number().min(1).max(50).default(20),
})

export type GetImagesQuery = z.infer<typeof GetImagesQuerySchema>

export const DeleteImageSchema = z.object({
  id: z.string().uuid('无效的图片ID'),
})

export type DeleteImageInput = z.infer<typeof DeleteImageSchema>
