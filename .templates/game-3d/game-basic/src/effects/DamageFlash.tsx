// 受伤闪烁特效
import { useRef, useCallback } from 'react'
import { useFrame } from '@react-three/fiber'
import * as THREE from 'three'
import { create } from 'zustand'

// ============ 受伤状态 Store ============

interface DamageState {
  isFlashing: boolean
  flashIntensity: number
  flashColor: THREE.Color
  flashStartTime: number
  flashDuration: number
  
  triggerFlash: (duration?: number, color?: string) => void
  stopFlash: () => void
}

export const useDamageStore = create<DamageState>((set) => ({
  isFlashing: false,
  flashIntensity: 0,
  flashColor: new THREE.Color('#ff0000'),
  flashStartTime: 0,
  flashDuration: 0.3,
  
  triggerFlash: (duration = 0.3, color = '#ff0000') => {
    set({
      isFlashing: true,
      flashStartTime: performance.now() / 1000,
      flashDuration: duration,
      flashColor: new THREE.Color(color),
    })
  },
  
  stopFlash: () => {
    set({
      isFlashing: false,
      flashIntensity: 0,
    })
  },
}))

// ============ 角色受伤闪烁材质 Hook ============

interface UseDamageFlashOptions {
  originalColor?: string
  flashColor?: string
  blinkCount?: number
}

export function useDamageFlash(
  materialRef: React.RefObject<THREE.MeshStandardMaterial | null>,
  options: UseDamageFlashOptions = {}
) {
  const {
    originalColor = '#00ffff',
    flashColor = '#ff0000',
    blinkCount = 3,
  } = options
  
  const isFlashing = useRef(false)
  const flashStartTime = useRef(0)
  const flashDuration = useRef(0.3)
  
  const triggerFlash = useCallback((duration = 0.3) => {
    isFlashing.current = true
    flashStartTime.current = performance.now() / 1000
    flashDuration.current = duration
  }, [])
  
  useFrame(() => {
    if (!materialRef.current || !isFlashing.current) return
    
    const elapsed = performance.now() / 1000 - flashStartTime.current
    const progress = elapsed / flashDuration.current
    
    if (progress >= 1) {
      isFlashing.current = false
      materialRef.current.emissive.set(originalColor)
      materialRef.current.emissiveIntensity = 0.3
      return
    }
    
    // 闪烁效果：在红色和原色之间快速切换
    const blinkPhase = Math.floor(progress * blinkCount * 2) % 2
    
    if (blinkPhase === 0) {
      materialRef.current.emissive.set(flashColor)
      materialRef.current.emissiveIntensity = 1.5
    } else {
      materialRef.current.emissive.set(originalColor)
      materialRef.current.emissiveIntensity = 0.3
    }
  })
  
  return { triggerFlash, isFlashing: isFlashing.current }
}

// ============ 全屏受伤闪烁效果 (屏幕边缘变红) ============

export function DamageVignette() {
  const meshRef = useRef<THREE.Mesh>(null)
  const { isFlashing, flashStartTime, flashDuration, flashColor } = useDamageStore()
  
  // 创建全屏四边形的着色器
  const material = useRef(
    new THREE.ShaderMaterial({
      uniforms: {
        uIntensity: { value: 0 },
        uColor: { value: new THREE.Color('#ff0000') },
      },
      vertexShader: `
        varying vec2 vUv;
        void main() {
          vUv = uv;
          gl_Position = vec4(position.xy, 0.0, 1.0);
        }
      `,
      fragmentShader: `
        uniform float uIntensity;
        uniform vec3 uColor;
        varying vec2 vUv;
        
        void main() {
          vec2 center = vUv - 0.5;
          float dist = length(center);
          
          // 边缘晕影效果
          float vignette = smoothstep(0.2, 0.8, dist);
          
          // 脉冲效果
          float pulse = sin(uIntensity * 10.0) * 0.3 + 0.7;
          
          float alpha = vignette * uIntensity * pulse;
          
          gl_FragColor = vec4(uColor, alpha * 0.6);
        }
      `,
      transparent: true,
      depthTest: false,
      depthWrite: false,
    })
  ).current
  
  useFrame(() => {
    if (!isFlashing) {
      material.uniforms.uIntensity.value = 0
      return
    }
    
    const elapsed = performance.now() / 1000 - flashStartTime
    const progress = elapsed / flashDuration
    
    if (progress >= 1) {
      useDamageStore.getState().stopFlash()
      return
    }
    
    // 强度曲线：快速上升，缓慢下降
    const intensity = progress < 0.2 
      ? progress / 0.2  // 快速上升
      : 1 - (progress - 0.2) / 0.8  // 缓慢下降
    
    material.uniforms.uIntensity.value = intensity
    material.uniforms.uColor.value.copy(flashColor)
  })
  
  return (
    <mesh ref={meshRef} renderOrder={999} frustumCulled={false}>
      <planeGeometry args={[2, 2]} />
      <primitive object={material} attach="material" />
    </mesh>
  )
}

// ============ 便捷 Hook ============

export function useDamageEffect() {
  const triggerFlash = useDamageStore(state => state.triggerFlash)
  
  const triggerDamage = useCallback((duration = 0.3, color = '#ff0000') => {
    triggerFlash(duration, color)
  }, [triggerFlash])
  
  const triggerHeal = useCallback((duration = 0.3) => {
    triggerFlash(duration, '#00ff00')
  }, [triggerFlash])
  
  return { triggerDamage, triggerHeal }
}
