// 进度条组件 - 基于 Radix UI Progress
import { forwardRef } from 'react'
import * as ProgressPrimitive from '@radix-ui/react-progress'
import { cn } from '@/utils'

export interface ProgressProps extends React.ComponentPropsWithoutRef<typeof ProgressPrimitive.Root> {
  /** 进度条颜色变体 */
  variant?: 'default' | 'warning' | 'danger'
  /** 是否显示动画效果 */
  animated?: boolean
}

export const Progress = forwardRef<
  React.ElementRef<typeof ProgressPrimitive.Root>,
  ProgressProps
>(({ className, value, variant = 'default', animated = false, ...props }, ref) => {
  const indicatorClasses = {
    default: 'progress-indicator-default',
    warning: 'progress-indicator-warning',
    danger: 'progress-indicator-danger',
  }

  return (
    <ProgressPrimitive.Root
      ref={ref}
      className={cn('progress clip-progress', className)}
      {...props}
    >
      {/* 网格背景 */}
      <div className="absolute inset-0 opacity-20 grid-progress" />

      <ProgressPrimitive.Indicator
        className={cn(
          'progress-indicator',
          indicatorClasses[variant],
          animated && variant === 'danger' && 'animate-pulse'
        )}
        style={{ transform: `translateX(-${100 - (value || 0)}%)` }}
      />

      {/* 高光效果 */}
      <div className="progress-highlight" />
    </ProgressPrimitive.Root>
  )
})

Progress.displayName = 'Progress'
