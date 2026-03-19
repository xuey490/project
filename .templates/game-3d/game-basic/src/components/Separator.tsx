// 分隔线组件 - 基于 Radix UI Separator
import { forwardRef } from 'react'
import * as SeparatorPrimitive from '@radix-ui/react-separator'
import { cn } from '@/utils'

export const Separator = forwardRef<
  React.ElementRef<typeof SeparatorPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof SeparatorPrimitive.Root>
>(({ className, orientation = 'horizontal', decorative = true, ...props }, ref) => (
  <SeparatorPrimitive.Root
    ref={ref}
    decorative={decorative}
    orientation={orientation}
    className={cn(
      'separator',
      orientation === 'horizontal' ? 'separator-horizontal' : 'separator-vertical',
      className
    )}
    {...props}
  />
))
Separator.displayName = 'Separator'
