import express, { Application } from 'express'
import cors from 'cors'
import compression from 'compression'
import 'express-async-errors'
import { env } from './config/env'
import { errorHandler } from './middleware/errorHandler'
import { httpLogger } from './middleware/logger'
import { systemRouter } from './modules/system'
import { generateRouter } from './modules/generate'
import { imageRouter } from './modules/image'
import { aiartRouter } from './modules/aiart'

export const createApp = (): Application => {
  const app = express()

  // HTTP request logging
  app.use(httpLogger)

  app.use(
    cors({
      origin: env.CORS_ORIGIN === '*' ? '*' : env.CORS_ORIGIN,
      credentials: env.CORS_ORIGIN !== '*',
    })
  )

  // Body parsing and compression - 增加限制以支持 base64 图片
  app.use(express.json({ limit: '50mb' }))
  app.use(express.urlencoded({ extended: true, limit: '50mb' }))
  app.use(compression())

  // API routes - System & Health
  app.use(env.API_PREFIX, systemRouter)

  // Image generation API
  app.use(`${env.API_PREFIX}/generate`, generateRouter)

  // AI Art API (query job status)
  app.use(`${env.API_PREFIX}/aiart`, aiartRouter)

  // Image management API (requires auth)
  app.use(`${env.API_PREFIX}/images`, imageRouter)

  // Error handling
  app.use(errorHandler)

  return app
}
