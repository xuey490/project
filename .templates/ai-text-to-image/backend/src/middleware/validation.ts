import { Request, Response, NextFunction } from 'express'
import { AnyZodObject } from 'zod'

export const validate = (schema: AnyZodObject) => {
  return async (req: Request, _res: Response, next: NextFunction) => {
    try {
      await schema.parseAsync({
        body: req.body,
        query: req.query,
        params: req.params,
      })
      next()
    } catch (error) {
      next(error)
    }
  }
}
