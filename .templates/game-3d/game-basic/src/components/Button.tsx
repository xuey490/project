// 基础按钮组件 - 基于 Radix UI Slot，自带 hover/click 音效
import { forwardRef, useCallback, ButtonHTMLAttributes } from 'react'
import { Slot } from '@radix-ui/react-slot'
import { soundManager } from '@/audio'
import { cn } from '@/utils'
import { cva, type VariantProps } from 'class-variance-authority'

const buttonVariants = cva(
  'inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-cyan-400 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        default: '', // 无默认样式，允许自定义
        primary: 'bg-cyan-600 text-white shadow hover:bg-cyan-500',
        destructive: 'bg-red-600 text-white shadow-sm hover:bg-red-500',
        outline: 'border border-cyan-500/50 bg-transparent hover:bg-cyan-500/10 hover:text-cyan-400',
        secondary: 'bg-slate-700 text-slate-100 shadow-sm hover:bg-slate-600',
        ghost: 'hover:bg-cyan-500/10 hover:text-cyan-400',
        link: 'text-cyan-400 underline-offset-4 hover:underline',
        cyber: 'relative bg-slate-900/80 border border-cyan-500/30 text-cyan-400 hover:bg-cyan-500/20 hover:border-cyan-400/50 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)]',
      },
      size: {
        default: '', // 无默认尺寸，允许自定义
        sm: 'h-8 px-3 text-xs',
        md: 'h-9 px-4 py-2',
        lg: 'h-10 px-8',
        xl: 'h-12 px-10 text-lg',
        icon: 'h-9 w-9',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  }
)

export interface ButtonProps
  extends ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  /** 使用 Radix Slot 让子元素成为按钮 */
  asChild?: boolean
  /** 是否启用 hover 音效，默认 true */
  enableHoverSound?: boolean
  /** 是否启用 click 音效，默认 true */
  enableClickSound?: boolean
}

/**
 * 基础按钮组件
 * 基于 Radix UI Slot，支持 asChild 模式
 * 自动添加 hover 和 click 音效
 */
export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant,
      size,
      asChild = false,
      enableHoverSound = true,
      enableClickSound = true,
      onMouseEnter,
      onClick,
      children,
      ...props
    },
    ref
  ) => {
    const handleMouseEnter = useCallback(
      (e: React.MouseEvent<HTMLButtonElement>) => {
        if (enableHoverSound) {
          soundManager.playHover()
        }
        onMouseEnter?.(e)
      },
      [enableHoverSound, onMouseEnter]
    )

    const handleClick = useCallback(
      (e: React.MouseEvent<HTMLButtonElement>) => {
        if (enableClickSound) {
          soundManager.playClick()
        }
        onClick?.(e)
      },
      [enableClickSound, onClick]
    )

    const Comp = asChild ? Slot : 'button'

    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        onMouseEnter={handleMouseEnter}
        onClick={handleClick}
        {...props}
      >
        {children}
      </Comp>
    )
  }
)

Button.displayName = 'Button'

export { buttonVariants }
export default Button
