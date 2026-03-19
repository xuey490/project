// 卡片组件 - 赛博朋克风格
import { forwardRef, HTMLAttributes, ReactNode } from 'react'
import { motion, HTMLMotionProps } from 'framer-motion'
import { soundManager } from '@/audio'
import { cn } from '@/utils'

export interface CardProps extends Omit<HTMLMotionProps<'div'>, 'children'> {
  children?: ReactNode
  /** 是否选中 */
  selected?: boolean
  /** 是否可交互（显示 hover 效果） */
  interactive?: boolean
  /** 是否启用音效 */
  enableSound?: boolean
  /** 变体 */
  variant?: 'default' | 'glow'
}

export const Card = forwardRef<HTMLDivElement, CardProps>(
  (
    {
      className,
      children,
      selected = false,
      interactive = false,
      enableSound = true,
      variant = 'default',
      onClick,
      ...props
    },
    ref
  ) => {
    const handleClick = (e: React.MouseEvent<HTMLDivElement>) => {
      if (enableSound && interactive) {
        soundManager.playClick()
      }
      onClick?.(e)
    }

    const handleMouseEnter = () => {
      if (enableSound && interactive) {
        soundManager.playHover()
      }
    }

    return (
      <motion.div
        ref={ref}
        className={cn(
          'relative group',
          interactive && 'cursor-pointer',
          selected && 'ring-2 ring-cyan-400',
          className
        )}
        onClick={handleClick}
        onMouseEnter={handleMouseEnter}
        layout
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        exit={{ opacity: 0, scale: 0.9 }}
        {...props}
      >
        {/* 背景光效 */}
        <div
          className={cn(
            'card-glow',
            selected && 'card-glow-active',
            interactive && 'card-glow-hover'
          )}
        />

        {/* 卡片主体 */}
        <div className="card relative w-full p-4">
          {/* 角标装饰 */}
          <div className="corner corner-tl" />
          <div className="corner corner-tr" />
          <div className="corner corner-bl" />
          <div className="corner corner-br" />

          {children}
        </div>
      </motion.div>
    )
  }
)

Card.displayName = 'Card'

// 卡片标题
export const CardTitle = forwardRef<
  HTMLHeadingElement,
  HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn('text-lg font-bold text-cyan-300 mb-2 truncate', className)}
    {...props}
  />
))
CardTitle.displayName = 'CardTitle'

// 卡片内容
export const CardContent = forwardRef<
  HTMLDivElement,
  HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn('space-y-1 text-sm text-slate-400', className)}
    {...props}
  />
))
CardContent.displayName = 'CardContent'

// 卡片操作区
export const CardActions = forwardRef<
  HTMLDivElement,
  HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn('flex gap-2 mt-3 pt-3 border-t border-slate-700', className)}
    {...props}
  />
))
CardActions.displayName = 'CardActions'
