import { Router, Request, Response } from 'express'
import { prisma } from '../config/database'

export const systemRouter = Router()

// ============================================
// System & Health Routes
// ============================================

/**
 * API root - welcome message
 */
systemRouter.get('/', async (_req: Request, res: Response) => {
  res.json({
    message: 'Welcome to the API',
    version: '1.0.0',
    timestamp: new Date().toISOString(),
    documentation: '/api/docs', // TODO: Add API documentation
  })
})

/**
 * Basic health check - always returns OK
 */
systemRouter.get('/health', async (_req: Request, res: Response) => {
  res.json({
    status: 'ok',
    timestamp: new Date().toISOString(),
  })
})

/**
 * Readiness check - verifies database connection
 */
systemRouter.get('/health/ready', async (_req: Request, res: Response) => {
  try {
    // Check database connection silently
    await prisma.$queryRaw`SELECT 1`

    res.json({
      status: 'ready',
      timestamp: new Date().toISOString(),
      checks: {
        database: 'connected',
      },
    })
  } catch (_error) {
    // Return error status without logging (handled by HTTP logger)
    res.status(503).json({
      status: 'not_ready',
      timestamp: new Date().toISOString(),
      checks: {
        database: 'disconnected',
      },
    })
  }
})

/**
 * Liveness check - verifies service is alive
 */
systemRouter.get('/health/live', async (_req: Request, res: Response) => {
  res.json({
    status: 'alive',
    timestamp: new Date().toISOString(),
  })
})

/**
 * API version information
 */
systemRouter.get('/version', async (_req: Request, res: Response) => {
  res.json({
    version: '1.0.0',
    apiVersion: 'v1',
    nodeVersion: process.version,
    environment: process.env.NODE_ENV || 'development',
    timestamp: new Date().toISOString(),
  })
})

/**
 * Simple ping endpoint
 */
systemRouter.get('/ping', async (_req: Request, res: Response) => {
  res.json({
    message: 'pong',
    timestamp: new Date().toISOString(),
  })
})

/**
 * System status with uptime and memory info
 */
systemRouter.get('/status', async (_req: Request, res: Response) => {
  const memoryUsage = process.memoryUsage()

  res.json({
    status: 'operational',
    uptime: process.uptime(),
    timestamp: new Date().toISOString(),
    memory: {
      rss: `${Math.round(memoryUsage.rss / 1024 / 1024)}MB`,
      heapTotal: `${Math.round(memoryUsage.heapTotal / 1024 / 1024)}MB`,
      heapUsed: `${Math.round(memoryUsage.heapUsed / 1024 / 1024)}MB`,
      external: `${Math.round(memoryUsage.external / 1024 / 1024)}MB`,
    },
    process: {
      pid: process.pid,
      platform: process.platform,
      nodeVersion: process.version,
    },
  })
})
