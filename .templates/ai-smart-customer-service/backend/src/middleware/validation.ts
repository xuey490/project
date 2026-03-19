import { Request, Response, NextFunction } from 'express'
import { AnyZodObject } from 'zod'

export const validate = (schema: AnyZodObject) => {
  return async (req: Request, _res: Response, next: NextFunction) => {
    try {
      await schema.parseAsync(req.body)
      next()
    } catch (error) {
      next(error)
    }
  }
}
