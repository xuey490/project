import { queries } from '@/engine'

// 生命周期系统 - 处理有限生命周期的实体
export const lifetimeSystem = (delta: number): void => {
  for (const entity of queries.withLifetime) {
    if (entity.lifetime) {
      entity.lifetime.elapsed += delta
      if (entity.lifetime.elapsed >= entity.lifetime.duration) {
        if (entity.tags) {
          entity.tags.push('destroy')
        } else {
          entity.tags = ['destroy']
        }
      }
    }
  }
}

// 动画更新系统
export const animationSystem = (delta: number): void => {
  for (const entity of queries.withAnimation) {
    entity.animation?.mixer?.update(delta)
  }
}

// 速度系统 - 根据速度更新位置
export const velocitySystem = (delta: number): void => {
  for (const entity of queries.withVelocity) {
    if (entity.transform && entity.velocity) {
      entity.transform.position.add(
        entity.velocity.clone().multiplyScalar(delta)
      )
    }
  }
}

// 系统运行器
export interface SystemRunner {
  update: (delta: number) => void
}

export const createSystemRunner = (
  systems: Array<(delta: number) => void>
): SystemRunner => {
  return {
    update: (delta: number) => {
      for (const system of systems) {
        system(delta)
      }
    },
  }
}

// 默认系统运行器
export const defaultSystems = createSystemRunner([
  lifetimeSystem,
  animationSystem,
  velocitySystem,
])
