// 对话框组件 - 基于 Radix UI Dialog
import { forwardRef } from 'react'
import * as DialogPrimitive from '@radix-ui/react-dialog'
import { motion } from 'framer-motion'
import { X } from 'lucide-react'
import { Button } from './Button'
import { cn } from '@/utils'

// 根组件
export const Dialog = DialogPrimitive.Root
export const DialogTrigger = DialogPrimitive.Trigger
export const DialogPortal = DialogPrimitive.Portal
export const DialogClose = DialogPrimitive.Close

// 遮罩层
export const DialogOverlay = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Overlay>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Overlay>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Overlay ref={ref} asChild {...props}>
    <motion.div
      className={cn('dialog-overlay', className)}
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
    />
  </DialogPrimitive.Overlay>
))
DialogOverlay.displayName = 'DialogOverlay'

// 内容区域
export interface DialogContentProps
  extends React.ComponentPropsWithoutRef<typeof DialogPrimitive.Content> {
  /** 是否显示关闭按钮 */
  showClose?: boolean
}

export const DialogContent = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Content>,
  DialogContentProps
>(({ className, children, showClose = true, ...props }, ref) => (
  <DialogPortal>
    <DialogOverlay />
    <DialogPrimitive.Content ref={ref} asChild {...props}>
      <motion.div
        className="dialog-content"
        initial={{ opacity: 0, scale: 0.9 }}
        animate={{ opacity: 1, scale: 1 }}
        exit={{ opacity: 0, scale: 0.9 }}
      >
        {/* 外发光 */}
        <div className="glow-purple" />

        {/* 主体 */}
        <div className={cn('dialog-panel scanline clip-corner-xl', className)}>
          {/* 顶部渐变线 */}
          <div className="line-top" />
          {/* 底部渐变线 */}
          <div className="line-bottom" />

          {children}

          {showClose && (
            <DialogClose asChild>
              <Button className="absolute top-4 right-4 p-1 text-cyber-pink hover:text-cyber-yellow transition-colors">
                <X className="w-5 h-5" />
              </Button>
            </DialogClose>
          )}
        </div>
      </motion.div>
    </DialogPrimitive.Content>
  </DialogPortal>
))
DialogContent.displayName = 'DialogContent'

// 标题
export const DialogTitle = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Title>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Title>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Title
    ref={ref}
    className={cn('dialog-title', className)}
    {...props}
  />
))
DialogTitle.displayName = 'DialogTitle'

// 描述
export const DialogDescription = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Description>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Description>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Description
    ref={ref}
    className={cn('dialog-description', className)}
    {...props}
  />
))
DialogDescription.displayName = 'DialogDescription'
