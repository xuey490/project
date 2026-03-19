// 时间相关工具函数

/**
 * 异步延迟
 * @param ms 毫秒数
 */
export function delay(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

/**
 * 节流函数 - 限制函数在指定时间内只执行一次
 * @param fn 要节流的函数
 * @param wait 等待时间（毫秒）
 */
export function throttle<T extends (...args: unknown[]) => unknown>(
  fn: T,
  wait: number
): (...args: Parameters<T>) => void {
  let lastCall = 0
  return (...args: Parameters<T>) => {
    const now = Date.now()
    if (now - lastCall >= wait) {
      lastCall = now
      fn(...args)
    }
  }
}

/**
 * 防抖函数 - 延迟执行，重复调用会重置计时器
 * @param fn 要防抖的函数
 * @param wait 等待时间（毫秒）
 */
export function debounce<T extends (...args: unknown[]) => unknown>(
  fn: T,
  wait: number
): (...args: Parameters<T>) => void {
  let timeoutId: ReturnType<typeof setTimeout>
  return (...args: Parameters<T>) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), wait)
  }
}

/**
 * 带取消功能的防抖函数
 */
export function debounceWithCancel<T extends (...args: unknown[]) => unknown>(
  fn: T,
  wait: number
): {
  (...args: Parameters<T>): void
  cancel: () => void
} {
  let timeoutId: ReturnType<typeof setTimeout>

  const debounced = (...args: Parameters<T>) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), wait)
  }

  debounced.cancel = () => {
    clearTimeout(timeoutId)
  }

  return debounced
}

/**
 * 创建一个间隔执行器，自带暂停/恢复功能
 */
export function createInterval(callback: () => void, intervalMs: number) {
  let timerId: ReturnType<typeof setInterval> | null = null
  let isRunning = false

  return {
    start: () => {
      if (!isRunning) {
        isRunning = true
        timerId = setInterval(callback, intervalMs)
      }
    },
    stop: () => {
      if (timerId) {
        clearInterval(timerId)
        timerId = null
        isRunning = false
      }
    },
    isRunning: () => isRunning,
  }
}

/**
 * 性能计时工具
 */
export function createTimer() {
  let startTime = 0

  return {
    start: () => {
      startTime = performance.now()
    },
    elapsed: () => performance.now() - startTime,
    elapsedSeconds: () => (performance.now() - startTime) / 1000,
    reset: () => {
      startTime = performance.now()
    },
  }
}

/**
 * 帧率限制器
 * @param fps 目标帧率
 */
export function createFrameLimiter(fps: number) {
  const interval = 1000 / fps
  let lastTime = 0

  return {
    shouldUpdate: () => {
      const now = performance.now()
      if (now - lastTime >= interval) {
        lastTime = now
        return true
      }
      return false
    },
    reset: () => {
      lastTime = 0
    },
  }
}
