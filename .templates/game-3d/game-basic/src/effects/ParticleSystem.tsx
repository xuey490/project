// 粒子特效系统
import { useRef, useMemo, useCallback } from 'react'
import { useFrame } from '@react-three/fiber'
import * as THREE from 'three'
import { create } from 'zustand'

// ============ 特效类型定义 ============

export type EffectType = 'collect' | 'dust' | 'impact' | 'sparkle' | 'trail'

interface ParticleConfig {
  count: number
  size: number
  color: string | string[]
  lifetime: number
  speed: number
  spread: number
  gravity: number
  fadeOut: boolean
  rotationSpeed?: number
}

interface ActiveEffect {
  id: string
  type: EffectType
  position: THREE.Vector3
  startTime: number
  config: ParticleConfig
}

// ============ 特效 Store ============

interface EffectsState {
  activeEffects: ActiveEffect[]
  spawnEffect: (type: EffectType, position: [number, number, number], customConfig?: Partial<ParticleConfig>) => void
  removeEffect: (id: string) => void
  clearEffects: () => void
}

// 预设配置
const effectPresets: Record<EffectType, ParticleConfig> = {
  collect: {
    count: 20,
    size: 0.15,
    color: ['#ffff00', '#ffd700', '#ffaa00', '#ffffff'],
    lifetime: 0.8,
    speed: 4,
    spread: 1.5,
    gravity: -2,
    fadeOut: true,
    rotationSpeed: 5,
  },
  dust: {
    count: 12,
    size: 0.1,
    color: ['#8b7355', '#a0926a', '#c4b99a'],
    lifetime: 0.6,
    speed: 2,
    spread: 0.8,
    gravity: 1,
    fadeOut: true,
  },
  impact: {
    count: 15,
    size: 0.12,
    color: ['#ff4444', '#ff6666', '#ffffff'],
    lifetime: 0.5,
    speed: 5,
    spread: 2,
    gravity: 3,
    fadeOut: true,
  },
  sparkle: {
    count: 8,
    size: 0.08,
    color: ['#00ffff', '#ff00ff', '#ffffff'],
    lifetime: 1.2,
    speed: 1,
    spread: 0.5,
    gravity: -0.5,
    fadeOut: true,
    rotationSpeed: 3,
  },
  trail: {
    count: 5,
    size: 0.06,
    color: ['#00ffff', '#0088ff'],
    lifetime: 0.4,
    speed: 0.5,
    spread: 0.2,
    gravity: 0,
    fadeOut: true,
  },
}

export const useEffectsStore = create<EffectsState>((set, get) => ({
  activeEffects: [],
  
  spawnEffect: (type, position, customConfig) => {
    const id = `effect_${Date.now()}_${Math.random().toString(36).slice(2, 6)}`
    const baseConfig = effectPresets[type]
    const config = { ...baseConfig, ...customConfig }
    
    const effect: ActiveEffect = {
      id,
      type,
      position: new THREE.Vector3(...position),
      startTime: performance.now() / 1000,
      config,
    }
    
    set(state => ({
      activeEffects: [...state.activeEffects, effect],
    }))
    
    // 自动移除
    setTimeout(() => {
      get().removeEffect(id)
    }, config.lifetime * 1000 + 100)
  },
  
  removeEffect: (id) => {
    set(state => ({
      activeEffects: state.activeEffects.filter(e => e.id !== id),
    }))
  },
  
  clearEffects: () => {
    set({ activeEffects: [] })
  },
}))

// ============ 单个粒子系统组件 ============

interface ParticleEffectProps {
  effect: ActiveEffect
}

function ParticleEffect({ effect }: ParticleEffectProps) {
  const meshRef = useRef<THREE.Points>(null)
  const { config, position, startTime } = effect
  
  // 创建粒子数据
  const { positions, velocities, colors, sizes } = useMemo(() => {
    const positions = new Float32Array(config.count * 3)
    const velocities: THREE.Vector3[] = []
    const colors = new Float32Array(config.count * 3)
    const sizes = new Float32Array(config.count)
    
    const colorArray = Array.isArray(config.color) ? config.color : [config.color]
    
    for (let i = 0; i < config.count; i++) {
      // 初始位置（在原点附近随机）
      positions[i * 3] = (Math.random() - 0.5) * 0.2
      positions[i * 3 + 1] = (Math.random() - 0.5) * 0.2
      positions[i * 3 + 2] = (Math.random() - 0.5) * 0.2
      
      // 随机速度方向
      const theta = Math.random() * Math.PI * 2
      const phi = Math.random() * Math.PI
      const speed = config.speed * (0.5 + Math.random() * 0.5)
      
      velocities.push(new THREE.Vector3(
        Math.sin(phi) * Math.cos(theta) * speed * config.spread,
        Math.cos(phi) * speed + Math.random() * speed * 0.5,
        Math.sin(phi) * Math.sin(theta) * speed * config.spread
      ))
      
      // 随机颜色
      const color = new THREE.Color(colorArray[Math.floor(Math.random() * colorArray.length)])
      colors[i * 3] = color.r
      colors[i * 3 + 1] = color.g
      colors[i * 3 + 2] = color.b
      
      // 随机大小
      sizes[i] = config.size * (0.5 + Math.random() * 0.5)
    }
    
    return { positions, velocities, colors, sizes }
  }, [config])
  
  // 创建着色器材质
  const material = useMemo(() => {
    return new THREE.ShaderMaterial({
      uniforms: {
        uTime: { value: 0 },
        uOpacity: { value: 1 },
      },
      vertexShader: `
        attribute float size;
        varying vec3 vColor;
        
        void main() {
          vColor = color;
          vec4 mvPosition = modelViewMatrix * vec4(position, 1.0);
          gl_PointSize = size * (300.0 / -mvPosition.z);
          gl_Position = projectionMatrix * mvPosition;
        }
      `,
      fragmentShader: `
        uniform float uOpacity;
        varying vec3 vColor;
        
        void main() {
          float dist = length(gl_PointCoord - vec2(0.5));
          if (dist > 0.5) discard;
          
          float alpha = 1.0 - smoothstep(0.3, 0.5, dist);
          gl_FragColor = vec4(vColor, alpha * uOpacity);
        }
      `,
      transparent: true,
      vertexColors: true,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
    })
  }, [])
  
  // 更新粒子
  useFrame(() => {
    if (!meshRef.current) return
    
    const elapsed = performance.now() / 1000 - startTime
    const progress = elapsed / config.lifetime
    
    if (progress >= 1) return
    
    const geometry = meshRef.current.geometry
    const posAttr = geometry.getAttribute('position') as THREE.BufferAttribute
    
    for (let i = 0; i < config.count; i++) {
      const vel = velocities[i]
      
      // 更新位置
      posAttr.array[i * 3] += vel.x * 0.016
      posAttr.array[i * 3 + 1] += vel.y * 0.016
      posAttr.array[i * 3 + 2] += vel.z * 0.016
      
      // 应用重力
      vel.y -= config.gravity * 0.016
    }
    
    posAttr.needsUpdate = true
    
    // 淡出效果
    if (config.fadeOut && material.uniforms) {
      material.uniforms.uOpacity.value = 1 - progress
    }
  })
  
  return (
    <points ref={meshRef} position={position} material={material}>
      <bufferGeometry>
        <bufferAttribute
          attach="attributes-position"
          count={config.count}
          array={positions}
          itemSize={3}
        />
        <bufferAttribute
          attach="attributes-color"
          count={config.count}
          array={colors}
          itemSize={3}
        />
        <bufferAttribute
          attach="attributes-size"
          count={config.count}
          array={sizes}
          itemSize={1}
        />
      </bufferGeometry>
    </points>
  )
}

// ============ 特效渲染器 ============

export function EffectsRenderer() {
  const activeEffects = useEffectsStore(state => state.activeEffects)
  
  return (
    <>
      {activeEffects.map(effect => (
        <ParticleEffect key={effect.id} effect={effect} />
      ))}
    </>
  )
}

// ============ 便捷 Hooks ============

export function useEffects() {
  const spawnEffect = useEffectsStore(state => state.spawnEffect)
  
  const spawnCollectEffect = useCallback((position: [number, number, number]) => {
    spawnEffect('collect', position)
  }, [spawnEffect])
  
  const spawnDustEffect = useCallback((position: [number, number, number]) => {
    spawnEffect('dust', position)
  }, [spawnEffect])
  
  const spawnImpactEffect = useCallback((position: [number, number, number]) => {
    spawnEffect('impact', position)
  }, [spawnEffect])
  
  const spawnSparkleEffect = useCallback((position: [number, number, number]) => {
    spawnEffect('sparkle', position)
  }, [spawnEffect])
  
  return {
    spawnEffect,
    spawnCollectEffect,
    spawnDustEffect,
    spawnImpactEffect,
    spawnSparkleEffect,
  }
}
