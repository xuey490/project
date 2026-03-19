import { World } from 'miniplex'
import * as THREE from 'three'
import type { RapierRigidBody } from '@react-three/rapier'

// 定义实体组件类型
export interface Entity {
  // 基础标识
  id?: string
  name?: string
  tags?: string[]

  // 变换组件
  transform?: {
    position: THREE.Vector3
    rotation: THREE.Euler
    scale: THREE.Vector3
  }

  // Three.js 对象引用
  object3D?: THREE.Object3D
  
  // 物理刚体引用
  rigidBody?: RapierRigidBody

  // 玩家组件
  player?: {
    speed: number
    jumpForce: number
    health: number
    maxHealth: number
  }

  // 可控制标记
  controllable?: boolean

  // 敌人组件
  enemy?: {
    type: string
    damage: number
    detectionRange: number
  }

  // 可收集物品组件
  collectible?: {
    type: string
    value: number
  }

  // 生命周期组件
  lifetime?: {
    duration: number
    elapsed: number
  }

  // 速度组件
  velocity?: THREE.Vector3

  // 渲染相关
  model?: {
    url: string
    loaded: boolean
  }

  // 动画组件
  animation?: {
    mixer?: THREE.AnimationMixer
    actions: Map<string, THREE.AnimationAction>
    currentAction?: string
  }

  // 音频组件
  audio?: {
    sounds: Map<string, THREE.Audio>
  }
}

// 创建 ECS World
export const world = new World<Entity>()

// 创建常用查询
export const queries = {
  // 所有玩家
  players: world.with('player'),
  
  // 可控制的实体
  controllable: world.with('controllable'),
  
  // 所有敌人
  enemies: world.with('enemy'),
  
  // 所有可收集物品
  collectibles: world.with('collectible'),
  
  // 有物理刚体的实体
  withRigidBody: world.with('rigidBody'),
  
  // 有生命周期的实体
  withLifetime: world.with('lifetime'),
  
  // 有变换的实体
  withTransform: world.with('transform'),
  
  // 有速度的实体
  withVelocity: world.with('velocity'),
  
  // 有动画的实体
  withAnimation: world.with('animation'),
}

// 实体工厂函数
export const createEntity = (components: Partial<Entity>): Entity => {
  const entity = world.add({
    id: crypto.randomUUID(),
    ...components,
  })
  return entity
}

// 移除实体
export const removeEntity = (entity: Entity): void => {
  world.remove(entity)
}
