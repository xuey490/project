// 滑块组件 - 基于 Radix UI Slider
import { forwardRef } from 'react'
import * as SliderPrimitive from '@radix-ui/react-slider'
import { cn } from '@/utils'

export interface SliderProps extends React.ComponentPropsWithoutRef<typeof SliderPrimitive.Root> {
  /** 滑块颜色变体 */
  variant?: 'pink' | 'blue' | 'purple'
}

export const Slider = forwardRef<
  React.ElementRef<typeof SliderPrimitive.Root>,
  SliderProps
>(({ className, variant = 'pink', ...props }, ref) => {
  const rangeClasses = {
    pink: 'slider-range-pink',
    blue: 'slider-range-blue',
    purple: 'slider-range-purple',
  }

  const thumbClasses = {
    pink: 'slider-thumb-pink',
    blue: 'slider-thumb-blue',
    purple: 'slider-thumb-purple',
  }

  return (
    <SliderPrimitive.Root
      ref={ref}
      className={cn(
        'relative flex w-full touch-none select-none items-center h-6 cursor-pointer',
        className
      )}
      {...props}
    >
      <SliderPrimitive.Track className="slider-track">
        <SliderPrimitive.Range className={rangeClasses[variant]} />
      </SliderPrimitive.Track>
      <SliderPrimitive.Thumb className={cn('slider-thumb', thumbClasses[variant])} />
    </SliderPrimitive.Root>
  )
})

Slider.displayName = 'Slider'
