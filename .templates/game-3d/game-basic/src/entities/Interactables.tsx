import { useRef, useEffect, useState, useMemo } from 'react'
import { useFrame } from '@react-three/fiber'
import { RigidBody } from '@react-three/rapier'
import type { RapierRigidBody } from '@react-three/rapier'
import * as THREE from 'three'
import { world, Entity } from '@/engine'
import { useGameSound } from '@/audio'
import { useEffects } from '@/effects'

// 金币纹理
function useCoinTexture() {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 128
    canvas.height = 128
    const ctx = canvas.getContext('2d')!

    const gradient = ctx.createRadialGradient(64, 64, 0, 64, 64, 64)
    gradient.addColorStop(0, '#ffdd00')
    gradient.addColorStop(0.5, '#ffaa00')
    gradient.addColorStop(1, '#ff8800')
    ctx.fillStyle = gradient
    ctx.fillRect(0, 0, 128, 128)

    ctx.fillStyle = '#ffffff'
    ctx.font = 'bold 60px Arial'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.fillText('$', 64, 64)

    ctx.strokeStyle = '#ffffff66'
    ctx.lineWidth = 4
    ctx.beginPath()
    ctx.arc(64, 64, 58, 0, Math.PI * 2)
    ctx.stroke()

    return new THREE.CanvasTexture(canvas)
  }, [])
}

// 宝石纹理
function useGemTexture() {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 128
    canvas.height = 128
    const ctx = canvas.getContext('2d')!

    const gradient = ctx.createLinearGradient(0, 0, 128, 128)
    gradient.addColorStop(0, '#ff00ff')
    gradient.addColorStop(0.5, '#ff69b4')
    gradient.addColorStop(1, '#ff1493')
    ctx.fillStyle = gradient
    ctx.fillRect(0, 0, 128, 128)

    ctx.fillStyle = '#ffffff88'
    ctx.beginPath()
    ctx.moveTo(20, 40)
    ctx.lineTo(64, 20)
    ctx.lineTo(108, 40)
    ctx.lineTo(64, 64)
    ctx.closePath()
    ctx.fill()

    ctx.strokeStyle = '#ffffff44'
    ctx.lineWidth = 2
    ctx.beginPath()
    ctx.moveTo(64, 0)
    ctx.lineTo(64, 128)
    ctx.moveTo(0, 64)
    ctx.lineTo(128, 64)
    ctx.stroke()

    return new THREE.CanvasTexture(canvas)
  }, [])
}

// 能量球纹理
function usePowerupTexture() {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 128
    canvas.height = 128
    const ctx = canvas.getContext('2d')!

    const gradient = ctx.createRadialGradient(64, 64, 0, 64, 64, 64)
    gradient.addColorStop(0, '#00ff88')
    gradient.addColorStop(0.5, '#00ff00')
    gradient.addColorStop(1, '#00aa00')
    ctx.fillStyle = gradient
    ctx.fillRect(0, 0, 128, 128)

    ctx.strokeStyle = '#ffffff66'
    ctx.lineWidth = 3
    for (let r = 15; r < 60; r += 15) {
      ctx.beginPath()
      ctx.arc(64, 64, r, 0, Math.PI * 2)
      ctx.stroke()
    }

    const centerGlow = ctx.createRadialGradient(64, 64, 0, 64, 64, 20)
    centerGlow.addColorStop(0, '#ffffff')
    centerGlow.addColorStop(1, 'transparent')
    ctx.fillStyle = centerGlow
    ctx.fillRect(44, 44, 40, 40)

    return new THREE.CanvasTexture(canvas)
  }, [])
}

// 箱子纹理
function useBoxTexture() {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 256
    canvas.height = 256
    const ctx = canvas.getContext('2d')!

    const gradient = ctx.createLinearGradient(0, 0, 256, 256)
    gradient.addColorStop(0, '#4a4a6a')
    gradient.addColorStop(0.5, '#3a3a5a')
    gradient.addColorStop(1, '#4a4a6a')
    ctx.fillStyle = gradient
    ctx.fillRect(0, 0, 256, 256)

    ctx.fillStyle = '#2a2a4a'
    for (let i = 0; i < 256; i += 32) {
      ctx.fillRect(0, i, 256, 4)
    }

    ctx.strokeStyle = '#ff6b6b'
    ctx.lineWidth = 6
    ctx.strokeRect(4, 4, 248, 248)

    ctx.fillStyle = '#ff6b6b'
    const cornerSize = 40
    ctx.fillRect(0, 0, cornerSize, 8)
    ctx.fillRect(0, 0, 8, cornerSize)
    ctx.fillRect(256 - cornerSize, 0, cornerSize, 8)
    ctx.fillRect(248, 0, 8, cornerSize)
    ctx.fillRect(0, 248, cornerSize, 8)
    ctx.fillRect(0, 256 - cornerSize, 8, cornerSize)
    ctx.fillRect(256 - cornerSize, 248, cornerSize, 8)
    ctx.fillRect(248, 256 - cornerSize, 8, cornerSize)

    ctx.strokeStyle = '#ffff0088'
    ctx.lineWidth = 3
    ctx.beginPath()
    ctx.moveTo(128, 80)
    ctx.lineTo(80, 176)
    ctx.lineTo(176, 176)
    ctx.closePath()
    ctx.stroke()

    ctx.font = 'bold 40px Arial'
    ctx.fillStyle = '#ffff00'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.fillText('!', 128, 140)

    return new THREE.CanvasTexture(canvas)
  }, [])
}

interface CollectibleProps {
  position?: [number, number, number]
  type?: 'coin' | 'gem' | 'powerup'
  value?: number
  onCollect?: (value: number) => void
}

export function Collectible({
  position = [0, 1, 0],
  type = 'coin',
  value = 10,
  onCollect,
}: CollectibleProps) {
  const meshRef = useRef<THREE.Mesh>(null)
  const materialRef = useRef<THREE.MeshStandardMaterial>(null)
  const rigidBodyRef = useRef<RapierRigidBody>(null)
  const entityRef = useRef<Entity | null>(null)
  const [collected, setCollected] = useState(false)

  const coinTexture = useCoinTexture()
  const gemTexture = useGemTexture()
  const powerupTexture = usePowerupTexture()
  const { playCollect, playPowerup } = useGameSound()
  const { spawnCollectEffect, spawnSparkleEffect } = useEffects()

  const textureMap = {
    coin: coinTexture,
    gem: gemTexture,
    powerup: powerupTexture,
  }

  const colorMap = {
    coin: '#ffd700',
    gem: '#ff69b4',
    powerup: '#00ff00',
  }

  useEffect(() => {
    entityRef.current = world.add({
      id: crypto.randomUUID(),
      collectible: { type, value },
      transform: {
        position: new THREE.Vector3(...position),
        rotation: new THREE.Euler(),
        scale: new THREE.Vector3(1, 1, 1),
      },
    })

    return () => {
      if (entityRef.current) {
        world.remove(entityRef.current)
      }
    }
  }, [])

  useFrame((state) => {
    if (meshRef.current && !collected) {
      meshRef.current.rotation.y = state.clock.elapsedTime * 2
      meshRef.current.position.y = Math.sin(state.clock.elapsedTime * 3) * 0.1
    }
    if (materialRef.current && !collected) {
      materialRef.current.emissiveIntensity = 0.5 + Math.sin(state.clock.elapsedTime * 4) * 0.3
    }
  })

  const handleCollision = () => {
    if (collected) return
    setCollected(true)
    
    // 播放收集特效
    if (type === 'powerup') {
      spawnSparkleEffect(position)
      playPowerup()
    } else {
      spawnCollectEffect(position)
      playCollect()
    }
    
    onCollect?.(value)
    if (entityRef.current) {
      world.remove(entityRef.current)
      entityRef.current = null
    }
  }

  if (collected) return null

  return (
    <RigidBody
      ref={rigidBodyRef}
      type="fixed"
      position={position}
      sensor
      onIntersectionEnter={handleCollision}
    >
      <mesh ref={meshRef} castShadow>
        {type === 'coin' && <cylinderGeometry args={[0.3, 0.3, 0.1, 12]} />}
        {type === 'gem' && <octahedronGeometry args={[0.3]} />}
        {type === 'powerup' && <sphereGeometry args={[0.3, 12, 12]} />}
        <meshStandardMaterial
          ref={materialRef}
          map={textureMap[type]}
          color={colorMap[type]}
          emissive={colorMap[type]}
          emissiveIntensity={0.5}
          metalness={0.8}
          roughness={0.2}
        />
      </mesh>
    </RigidBody>
  )
}

interface InteractableBoxProps {
  position?: [number, number, number]
  color?: string
  size?: number
}

export function InteractableBox({
  position = [0, 1, 0],
  color = '#ff6b6b',
  size = 1,
}: InteractableBoxProps) {
  const rigidBodyRef = useRef<RapierRigidBody>(null)
  const boxTexture = useBoxTexture()

  return (
    <RigidBody
      ref={rigidBodyRef}
      position={position}
      colliders="cuboid"
      mass={1}
      restitution={0.3}
    >
      <mesh castShadow>
        <boxGeometry args={[size, size, size]} />
        <meshStandardMaterial
          map={boxTexture}
          emissive={color}
          emissiveIntensity={0.15}
          metalness={0.6}
          roughness={0.4}
        />
      </mesh>
    </RigidBody>
  )
}
