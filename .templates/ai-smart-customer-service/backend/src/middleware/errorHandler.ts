import { Request, Response, NextFunction } from 'express'
import { ZodError } from 'zod'
import { Prisma } from '@prisma/client'
import { createLogger } from '../config/logger'

const logger = createLogger('ErrorHandler')

export class AppError extends Error {
  constructor(
    public statusCode: number,
    public message: string,
    public isOperational = true
  ) {
    super(message)
    Object.setPrototypeOf(this, AppError.prototype)
  }
}

export const errorHandler = (err: Error, req: Request, res: Response, _next: NextFunction) => {
  // Zod validation errors
  if (err instanceof ZodError) {
    logger.warn(
      {
        method: req.method,
        url: req.url,
        errors: err.errors,
      },
      'Validation error'
    )

    return res.status(400).json({
      status: 'error',
      message: 'Validation failed',
      errors: err.errors.map(e => ({
        field: e.path.join('.'),
        message: e.message,
      })),
    })
  }

  // Prisma errors
  if (err instanceof Prisma.PrismaClientKnownRequestError) {
    logger.error(
      {
        method: req.method,
        url: req.url,
        code: err.code,
      },
      'Database error'
    )

    if (err.code === 'P2002') {
      return res.status(409).json({
        status: 'error',
        message: 'Resource already exists',
      })
    }
    if (err.code === 'P2025') {
      return res.status(404).json({
        status: 'error',
        message: 'Resource not found',
      })
    }
  }

  // Application errors
  if (err instanceof AppError) {
    logger.warn(
      {
        method: req.method,
        url: req.url,
        statusCode: err.statusCode,
      },
      err.message
    )

    return res.status(err.statusCode).json({
      status: 'error',
      message: err.message,
    })
  }

  // Unknown errors
  logger.error(
    {
      err,
      method: req.method,
      url: req.url,
    },
    'Unhandled error'
  )

  return res.status(500).json({
    status: 'error',
    message: 'Internal server error',
  })
}
