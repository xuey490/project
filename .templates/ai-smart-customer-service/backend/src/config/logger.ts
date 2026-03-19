import pino from 'pino'

const isDevelopment = process.env.NODE_ENV === 'development'

export const logger = pino({
  level: process.env.LOG_LEVEL || 'warn', // Changed from 'info' to 'warn' for silence on success
  transport: isDevelopment
    ? {
        target: 'pino-pretty',
        options: {
          colorize: true,
          translateTime: 'HH:MM:ss',
          ignore: 'pid,hostname',
          messageFormat: '{msg}',
        },
      }
    : undefined,
  timestamp: false, // Disable timestamp in production for cleaner logs
  serializers: {
    err: pino.stdSerializers.err,
  },
})

/**
 * Create a child logger with context
 * @param context - Context name for the logger (e.g., 'UserController', 'SystemController')
 * @returns Child logger instance with context
 */
export function createLogger(context: string) {
  return logger.child({ context })
}
