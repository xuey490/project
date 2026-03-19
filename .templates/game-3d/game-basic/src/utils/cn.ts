// 类名合并工具
import { clsx, type ClassValue } from 'clsx'

/**
 * 合并 className，支持条件类名
 * @example
 * cn('base', isActive && 'active', { 'hover': isHovered })
 */
export function cn(...inputs: ClassValue[]) {
  return clsx(inputs)
}
