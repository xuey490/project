import { useMemo } from 'react'
import { RigidBody } from '@react-three/rapier'
import * as THREE from 'three'

// 程序化生成地面纹理
function useGroundTexture() {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 512
    canvas.height = 512
    const ctx = canvas.getContext('2d')!

    ctx.fillStyle = '#1a1a2e'
    ctx.fillRect(0, 0, 512, 512)

    ctx.strokeStyle = '#00ffff'
    ctx.lineWidth = 2
    for (let i = 0; i <= 512; i += 32) {
      ctx.beginPath()
      ctx.moveTo(i, 0)
      ctx.lineTo(i, 512)
      ctx.stroke()
      ctx.beginPath()
      ctx.moveTo(0, i)
      ctx.lineTo(512, i)
      ctx.stroke()
    }

    ctx.strokeStyle = '#ff00ff33'
    ctx.lineWidth = 1
    for (let i = 16; i <= 512; i += 32) {
      ctx.beginPath()
      ctx.moveTo(i, 0)
      ctx.lineTo(i, 512)
      ctx.stroke()
      ctx.beginPath()
      ctx.moveTo(0, i)
      ctx.lineTo(512, i)
      ctx.stroke()
    }

    for (let i = 0; i < 20; i++) {
      const x = Math.random() * 512
      const y = Math.random() * 512
      const gradient = ctx.createRadialGradient(x, y, 0, x, y, 15)
      gradient.addColorStop(0, '#00ffff88')
      gradient.addColorStop(1, 'transparent')
      ctx.fillStyle = gradient
      ctx.fillRect(x - 15, y - 15, 30, 30)
    }

    const texture = new THREE.CanvasTexture(canvas)
    texture.wrapS = THREE.RepeatWrapping
    texture.wrapT = THREE.RepeatWrapping
    texture.repeat.set(10, 10)
    return texture
  }, [])
}

// 程序化生成平台纹理
function usePlatformTexture(color: string = '#ff00ff') {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 256
    canvas.height = 256
    const ctx = canvas.getContext('2d')!

    ctx.fillStyle = '#2a2a4a'
    ctx.fillRect(0, 0, 256, 256)

    ctx.strokeStyle = color
    ctx.lineWidth = 4
    ctx.strokeRect(4, 4, 248, 248)

    ctx.strokeStyle = color + '66'
    ctx.lineWidth = 2
    ctx.strokeRect(20, 20, 216, 216)

    const cornerSize = 30
    ctx.fillStyle = color
    ctx.fillRect(0, 0, cornerSize, 4)
    ctx.fillRect(0, 0, 4, cornerSize)
    ctx.fillRect(256 - cornerSize, 0, cornerSize, 4)
    ctx.fillRect(252, 0, 4, cornerSize)
    ctx.fillRect(0, 252, cornerSize, 4)
    ctx.fillRect(0, 256 - cornerSize, 4, cornerSize)
    ctx.fillRect(256 - cornerSize, 252, cornerSize, 4)
    ctx.fillRect(252, 256 - cornerSize, 4, cornerSize)

    ctx.strokeStyle = color + '44'
    ctx.lineWidth = 1
    for (let i = 40; i < 216; i += 20) {
      ctx.beginPath()
      ctx.moveTo(i, 40)
      ctx.lineTo(i, 216)
      ctx.stroke()
    }

    return new THREE.CanvasTexture(canvas)
  }, [color])
}

// 程序化生成墙壁纹理
function useWallTexture() {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 256
    canvas.height = 256
    const ctx = canvas.getContext('2d')!

    ctx.fillStyle = '#1a1a2e'
    ctx.fillRect(0, 0, 256, 256)

    const brickHeight = 32
    const brickWidth = 64
    ctx.strokeStyle = '#00ffff44'
    ctx.lineWidth = 2

    for (let row = 0; row < 8; row++) {
      const offset = row % 2 === 0 ? 0 : brickWidth / 2
      for (let col = -1; col < 5; col++) {
        const x = col * brickWidth + offset
        const y = row * brickHeight
        ctx.strokeRect(x, y, brickWidth, brickHeight)

        if (Math.random() > 0.85) {
          const gradient = ctx.createLinearGradient(x, y, x + brickWidth, y + brickHeight)
          gradient.addColorStop(0, '#ff00ff22')
          gradient.addColorStop(1, '#00ffff22')
          ctx.fillStyle = gradient
          ctx.fillRect(x + 2, y + 2, brickWidth - 4, brickHeight - 4)
        }
      }
    }

    ctx.fillStyle = '#00ffff08'
    for (let i = 0; i < 256; i += 4) {
      ctx.fillRect(i, 0, 2, 256)
    }

    const texture = new THREE.CanvasTexture(canvas)
    texture.wrapS = THREE.RepeatWrapping
    texture.wrapT = THREE.RepeatWrapping
    return texture
  }, [])
}

// 程序化生成斜坡纹理
function useRampTexture() {
  return useMemo(() => {
    const canvas = document.createElement('canvas')
    canvas.width = 256
    canvas.height = 256
    const ctx = canvas.getContext('2d')!

    const gradient = ctx.createLinearGradient(0, 0, 256, 256)
    gradient.addColorStop(0, '#3a3a5a')
    gradient.addColorStop(0.5, '#2a2a4a')
    gradient.addColorStop(1, '#3a3a5a')
    ctx.fillStyle = gradient
    ctx.fillRect(0, 0, 256, 256)

    ctx.strokeStyle = '#ffff00'
    ctx.lineWidth = 6
    for (let i = -256; i < 512; i += 40) {
      ctx.beginPath()
      ctx.moveTo(i, 0)
      ctx.lineTo(i + 256, 256)
      ctx.stroke()
    }

    ctx.strokeStyle = '#1a1a2e'
    ctx.lineWidth = 20
    for (let i = -256; i < 512; i += 40) {
      ctx.beginPath()
      ctx.moveTo(i + 10, 0)
      ctx.lineTo(i + 266, 256)
      ctx.stroke()
    }

    ctx.strokeStyle = '#00ffff'
    ctx.lineWidth = 3
    ctx.strokeRect(2, 2, 252, 252)

    const texture = new THREE.CanvasTexture(canvas)
    texture.wrapS = THREE.RepeatWrapping
    texture.wrapT = THREE.RepeatWrapping
    return texture
  }, [])
}

interface GroundProps {
  size?: [number, number]
}

export function Ground({ size = [50, 50] }: GroundProps) {
  const texture = useGroundTexture()

  return (
    <RigidBody type="fixed" colliders="cuboid">
      <mesh receiveShadow rotation-x={-Math.PI / 2} position={[0, 0, 0]}>
        <planeGeometry args={size} />
        <meshStandardMaterial
          map={texture}
          emissive="#00ffff"
          emissiveIntensity={0.1}
          metalness={0.3}
          roughness={0.7}
        />
      </mesh>
    </RigidBody>
  )
}

interface PlatformProps {
  position?: [number, number, number]
  size?: [number, number, number]
  color?: string
}

export function Platform({
  position = [0, 0, 0],
  size = [4, 0.5, 4],
  color = '#ff00ff',
}: PlatformProps) {
  const texture = usePlatformTexture(color)

  return (
    <RigidBody type="fixed" position={position} colliders="cuboid">
      <mesh castShadow receiveShadow>
        <boxGeometry args={size} />
        <meshStandardMaterial
          map={texture}
          emissive={color}
          emissiveIntensity={0.2}
          metalness={0.8}
          roughness={0.3}
        />
      </mesh>
    </RigidBody>
  )
}

interface WallProps {
  position?: [number, number, number]
  size?: [number, number, number]
  rotation?: [number, number, number]
  color?: string
}

export function Wall({
  position = [0, 0, 0],
  size = [10, 5, 0.5],
  rotation = [0, 0, 0],
  color = '#00ffff',
}: WallProps) {
  const texture = useWallTexture()

  return (
    <RigidBody type="fixed" position={position} rotation={rotation} colliders="cuboid">
      <mesh castShadow receiveShadow>
        <boxGeometry args={size} />
        <meshStandardMaterial
          map={texture}
          emissive={color}
          emissiveIntensity={0.1}
          metalness={0.5}
          roughness={0.5}
        />
      </mesh>
    </RigidBody>
  )
}

interface RampProps {
  position?: [number, number, number]
  rotation?: [number, number, number]
  size?: [number, number, number]
  color?: string
}

export function Ramp({
  position = [0, 0, 0],
  rotation = [0, 0, 0],
  size = [4, 0.5, 8],
  color = '#ffff00',
}: RampProps) {
  const texture = useRampTexture()

  return (
    <RigidBody type="fixed" position={position} rotation={rotation} colliders="cuboid">
      <mesh castShadow receiveShadow>
        <boxGeometry args={size} />
        <meshStandardMaterial
          map={texture}
          emissive={color}
          emissiveIntensity={0.15}
          metalness={0.6}
          roughness={0.4}
        />
      </mesh>
    </RigidBody>
  )
}
