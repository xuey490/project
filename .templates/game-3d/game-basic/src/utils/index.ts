// 工具函数统一导出

// 类名合并
export { cn } from './cn'

// 数学工具
export {
  random,
  randomInt,
  randomElement,
  lerp,
  clamp,
  degToRad,
  radToDeg,
  distance2D,
  distance3D,
  mapRange,
  smoothDamp,
  angleDiff,
  normalizeAngle,
} from './math'

// 时间工具
export {
  delay,
  throttle,
  debounce,
  debounceWithCancel,
  createInterval,
  createTimer,
  createFrameLimiter,
} from './time'

// 格式化工具
export {
  uuid,
  shortId,
  formatPlaytime,
  formatPlaytimeCN,
  formatRelativeTime,
  formatNumber,
  formatCompactNumber,
  formatPercent,
  formatFileSize,
  truncate,
  capitalize,
  toKebabCase,
  toCamelCase,
} from './format'

// 光源工具
export { useLightTarget, ShadowCaster } from './useLightTarget'
