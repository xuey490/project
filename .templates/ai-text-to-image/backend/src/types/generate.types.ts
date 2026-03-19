import { z } from 'zod'

export const GenerateImageSchema = z.object({
  prompt: z.string().min(1, '描述词不能为空').max(500, '描述词最多500字符'),
  aspectRatio: z.enum(['1:1', '4:3', '16:9']).default('1:1'),
  quality: z.enum(['low', 'medium', 'high']).default('medium'),
  style: z.string().default('auto'),
})

export type GenerateImageInput = z.infer<typeof GenerateImageSchema>

export interface GeneratedImage {
  id: string
  url: string
  prompt: string
  aspectRatio: string
  quality: string
  style: string
  createdAt: Date
}
