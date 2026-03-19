import { pinoHttp } from 'pino-http'
import { logger } from '../config/logger'

export const httpLogger = pinoHttp({
  logger,
  // Only log errors (4xx, 5xx) and warnings, skip successful requests
  autoLogging: {
    ignore: req => {
      // Ignore health check endpoints
      return req.url?.startsWith('/health') || req.url === '/ping'
    },
  },
  customLogLevel: (_req, res, err) => {
    if (res.statusCode >= 400 && res.statusCode < 500) {
      return 'warn'
    } else if (res.statusCode >= 500 || err) {
      return 'error'
    }
    return 'silent' // Silent for successful requests
  },
  customSuccessMessage: (req, res) => {
    // Only show message if it's an error
    if (res.statusCode >= 400) {
      return `${req.method} ${req.url} ${res.statusCode}`
    }
    return '' // Empty for success
  },
  customErrorMessage: (req, _res, err) => {
    return `${req.method} ${req.url} - ${err.message}`
  },
})
