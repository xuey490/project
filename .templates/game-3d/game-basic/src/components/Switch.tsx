// 开关组件 - 基于 Radix UI Switch
import { forwardRef } from 'react'
import * as SwitchPrimitive from '@radix-ui/react-switch'
import { soundManager } from '@/audio'
import { cn } from '@/utils'

export interface SwitchProps extends React.ComponentPropsWithoutRef<typeof SwitchPrimitive.Root> {
  /** 是否启用音效，默认 true */
  enableSound?: boolean
}

export const Switch = forwardRef<
  React.ElementRef<typeof SwitchPrimitive.Root>,
  SwitchProps
>(({ className, enableSound = true, onCheckedChange, ...props }, ref) => {
  const handleCheckedChange = (checked: boolean) => {
    if (enableSound) {
      soundManager.playClick()
    }
    onCheckedChange?.(checked)
  }

  return (
    <SwitchPrimitive.Root
      ref={ref}
      className={cn('switch', className)}
      onCheckedChange={handleCheckedChange}
      {...props}
    >
      <SwitchPrimitive.Thumb className="switch-thumb" />
    </SwitchPrimitive.Root>
  )
})

Switch.displayName = 'Switch'
