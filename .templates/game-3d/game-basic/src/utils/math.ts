// 数学工具函数

/**
 * 生成指定范围内的随机数
 */
export function random(min: number, max: number): number {
  return Math.random() * (max - min) + min
}

/**
 * 生成指定范围内的随机整数（包含 min 和 max）
 */
export function randomInt(min: number, max: number): number {
  return Math.floor(random(min, max + 1))
}

/**
 * 从数组中随机选取一个元素
 */
export function randomElement<T>(array: T[]): T {
  return array[randomInt(0, array.length - 1)]
}

/**
 * 线性插值
 * @param a 起始值
 * @param b 结束值
 * @param t 插值因子 (0-1)
 */
export function lerp(a: number, b: number, t: number): number {
  return a + (b - a) * t
}

/**
 * 限制值在指定范围内
 */
export function clamp(value: number, min: number, max: number): number {
  return Math.max(min, Math.min(max, value))
}

/**
 * 角度转弧度
 */
export function degToRad(degrees: number): number {
  return degrees * (Math.PI / 180)
}

/**
 * 弧度转角度
 */
export function radToDeg(radians: number): number {
  return radians * (180 / Math.PI)
}

/**
 * 计算二维平面上两点间的距离
 */
export function distance2D(
  x1: number,
  y1: number,
  x2: number,
  y2: number
): number {
  return Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2)
}

/**
 * 计算三维空间中两点间的距离
 */
export function distance3D(
  x1: number,
  y1: number,
  z1: number,
  x2: number,
  y2: number,
  z2: number
): number {
  return Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2 + (z2 - z1) ** 2)
}

/**
 * 将值从一个范围映射到另一个范围
 */
export function mapRange(
  value: number,
  inMin: number,
  inMax: number,
  outMin: number,
  outMax: number
): number {
  return ((value - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin
}

/**
 * 平滑阻尼（用于平滑相机跟随等）
 */
export function smoothDamp(
  current: number,
  target: number,
  velocity: { value: number },
  smoothTime: number,
  maxSpeed: number = Infinity,
  deltaTime: number
): number {
  smoothTime = Math.max(0.0001, smoothTime)
  const omega = 2 / smoothTime
  const x = omega * deltaTime
  const exp = 1 / (1 + x + 0.48 * x * x + 0.235 * x * x * x)
  let change = current - target
  const originalTo = target

  const maxChange = maxSpeed * smoothTime
  change = clamp(change, -maxChange, maxChange)
  target = current - change

  const temp = (velocity.value + omega * change) * deltaTime
  velocity.value = (velocity.value - omega * temp) * exp
  let output = target + (change + temp) * exp

  if (originalTo - current > 0 === output > originalTo) {
    output = originalTo
    velocity.value = (output - originalTo) / deltaTime
  }

  return output
}

/**
 * 计算两个角度之间的最短差值（处理角度环绕）
 */
export function angleDiff(from: number, to: number): number {
  let diff = to - from
  while (diff > Math.PI) diff -= Math.PI * 2
  while (diff < -Math.PI) diff += Math.PI * 2
  return diff
}

/**
 * 规范化角度到 [-PI, PI] 范围
 */
export function normalizeAngle(angle: number): number {
  while (angle > Math.PI) angle -= Math.PI * 2
  while (angle < -Math.PI) angle += Math.PI * 2
  return angle
}
