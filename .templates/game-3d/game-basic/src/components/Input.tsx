// 输入框组件 - 赛博朋克风格
import { forwardRef, InputHTMLAttributes } from 'react'
import { cn } from '@/utils'

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ className, type = 'text', ...props }, ref) => {
    return (
      <input
        type={type}
        ref={ref}
        className={cn('input', className)}
        {...props}
      />
    )
  }
)
Input.displayName = 'Input'
