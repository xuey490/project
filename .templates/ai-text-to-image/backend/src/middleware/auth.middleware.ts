import { Request, Response, NextFunction } from 'express'
import { createClient } from '@supabase/supabase-js'
import { logger } from '../config/logger'

const SUPABASE_URL = 'https://jvyddfsojtsipmiwjcdr.supabase.co'
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imp2eWRkZnNvanRzaXBtaXdqY2RyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjIxNDE3NTAsImV4cCI6MjA3NzcxNzc1MH0.BvEXIgONC2GxAUzRIx8I7P3PsTH3p10vh68CEyRPRMg'

const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY)

// 扩展 Express Request 类型
declare global {
  namespace Express {
    interface Request {
      user?: {
        id: string
        email: string
      }
    }
  }
}

/**
 * 验证 JWT Token 的中间件
 * 从 Authorization header 中提取 Bearer token 并验证
 */
export async function requireAuth(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const authHeader = req.headers.authorization
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      res.status(401).json({ error: '未提供认证令牌' })
      return
    }

    const token = authHeader.substring(7) // 移除 "Bearer " 前缀

    // 验证 token
    const { data: { user }, error } = await supabase.auth.getUser(token)

    if (error || !user) {
      logger.warn({ error }, 'Invalid auth token')
      res.status(401).json({ error: '认证令牌无效或已过期' })
      return
    }

    // 将用户信息附加到请求对象
    req.user = {
      id: user.id,
      email: user.email || '',
    }

    next()
  } catch (error) {
    logger.error({ error }, 'Auth middleware error')
    res.status(500).json({ error: '认证服务异常' })
  }
}

/**
 * 可选认证中间件
 * 如果提供了 token 则验证，否则继续执行
 */
export async function optionalAuth(
  req: Request,
  _res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const authHeader = req.headers.authorization
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      next()
      return
    }

    const token = authHeader.substring(7)

    const { data: { user } } = await supabase.auth.getUser(token)

    if (user) {
      req.user = {
        id: user.id,
        email: user.email || '',
      }
    }

    next()
  } catch {
    // 可选认证失败不阻止请求
    next()
  }
}
