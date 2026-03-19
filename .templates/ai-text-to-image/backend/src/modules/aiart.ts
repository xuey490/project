import { Router, Request, Response } from 'express'
import { z } from 'zod'
import { logger } from '../config/logger'
import { createClient } from '../lib/tencent-aiart'

export const aiartRouter = Router()

// Create AI Art client
const aiartClient = createClient()

// Query job schema
const queryJobSchema = z.object({
  jobId: z.string().min(1, 'jobId is required'),
})

/**
 * POST /api/aiart/query
 * Query the status of a generation job
 */
aiartRouter.post('/query', async (req: Request, res: Response) => {
  const validation = queryJobSchema.safeParse(req.body)
  
  if (!validation.success) {
    return res.status(400).json({
      success: false,
      error: validation.error.errors[0].message,
    })
  }

  const { jobId } = validation.data

  try {
    const result = await aiartClient.queryJob(jobId)

    return res.json({
      success: true,
      data: {
        status: result.status,
        imageUrl: result.imageUrl,
        errorMessage: result.errorMessage,
      },
    })
  } catch (error: any) {
    logger.error({ error: error.message, jobId }, 'Failed to query job')
    return res.status(500).json({
      success: false,
      error: error.message || 'Failed to query job status',
    })
  }
})
