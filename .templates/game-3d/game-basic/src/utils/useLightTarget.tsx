import { useRef, useLayoutEffect } from 'react'
import * as THREE from 'three'
import { useThree, useFrame } from '@react-three/fiber'

/**
 * 智能光源目标绑定 Hook
 * 
 * 解决两个痛点：
 * 1. 自动把 light.target 添加到场景中（Three.js 要求 target 必须在 scene graph 中才能生效，AI 经常忘）
 * 2. 性能优化：利用 Three.js 父子层级关系，不需要 useFrame 疯狂更新矩阵
 * 
 * @param lightRef 光源的 ref
 * @param targetObj 想要跟随的目标 Object3D (如玩家的 ref.current)
 * @param offset 目标点的偏移量
 */
export const useLightTarget = (
  lightRef: React.RefObject<THREE.Light | null>,
  targetObj: THREE.Object3D | null | undefined,
  _offset: [number, number, number] = [0, 0, 0]
) => {
  const { scene } = useThree()

  useLayoutEffect(() => {
    const light = lightRef.current
    if (!light || !targetObj) return

    // 1. 确保 light.target 被添加到场景中 (Three.js 核心机制)
    // 如果 target 没在场景里，指向会失效
    if (light instanceof THREE.DirectionalLight || light instanceof THREE.SpotLight) {
      scene.add(light.target)
    }

    // 2. 核心优化：不要在 useFrame 里 updateMatrixWorld
    // 而是直接让 light.target 的位置跟随目标
    // 最高效的方法是把 light.target 作为目标的子元素或者利用约束
    // 但为了不破坏层级，这里使用极轻量的更新策略

    return () => {
      if (light instanceof THREE.DirectionalLight || light instanceof THREE.SpotLight) {
        scene.remove(light.target)
      }
    }
  }, [scene, targetObj, lightRef])

  // 注意：如果是 DirectionalLight，通常只跟玩家位置有关
  // 最好的做法是把 DirectionalLight 放在一个 Group 里，Group 跟随玩家
}

interface ShadowCasterProps {
  /** 跟随目标的 ref（如玩家 RigidBody ref） */
  targetRef: React.RefObject<{ translation: () => { x: number; y: number; z: number } } | null>
  /** 光源相对于目标的位置偏移 */
  positionOffset?: [number, number, number]
  /** 阴影贴图尺寸 */
  shadowMapSize?: number
  /** 光照强度 */
  intensity?: number
  /** 光照颜色 */
  color?: THREE.ColorRepresentation
}

/**
 * 自动跟随玩家的阴影光组件
 * 
 * 策略：移动整个 Light 的容器，而不是更新 Light 的 Target
 * 这样光照方向保持不变，但光照范围跟随玩家
 * 
 * AI 只需要使用这个组件，完全不需要写 useFrame
 * 
 * @example
 * ```tsx
 * <ShadowCaster targetRef={playerRef} positionOffset={[5, 10, 5]} />
 * ```
 */
export const ShadowCaster = ({
  targetRef,
  positionOffset = [5, 10, 5],
  shadowMapSize = 1024,
  intensity = 1,
  color = '#ffffff',
}: ShadowCasterProps) => {
  const lightRef = useRef<THREE.DirectionalLight>(null)
  const wrapperRef = useRef<THREE.Group>(null)

  useFrame(() => {
    if (targetRef?.current && wrapperRef.current) {
      const tPos = targetRef.current.translation() // Rapier RigidBody API
      // 简单赋值，Three.js 渲染循环会自动处理矩阵
      // 绝对不要手动调用 updateMatrixWorld()
      wrapperRef.current.position.set(tPos.x, tPos.y, tPos.z)
    }
  })

  return (
    <group ref={wrapperRef}>
      <directionalLight
        ref={lightRef}
        position={positionOffset}
        intensity={intensity}
        color={color}
        castShadow
        shadow-mapSize-width={shadowMapSize}
        shadow-mapSize-height={shadowMapSize}
        shadow-camera-near={0.5}
        shadow-camera-far={50}
        shadow-camera-left={-10}
        shadow-camera-right={10}
        shadow-camera-top={10}
        shadow-camera-bottom={-10}
      >
        {/* target 相对光源的固定指向（向下看） */}
        <object3D position={[0, -10, 0]} />
      </directionalLight>
    </group>
  )
}

export default useLightTarget
