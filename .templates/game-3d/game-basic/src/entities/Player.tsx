import { useRef, useEffect, forwardRef } from 'react'
import { useFrame } from '@react-three/fiber'
import { RigidBody, CapsuleCollider, useRapier } from '@react-three/rapier'
import type { RapierRigidBody } from '@react-three/rapier'
import { Ray } from '@dimforge/rapier3d-compat'
import * as THREE from 'three'
import { useInputStore } from '@/engine'
import { world, Entity } from '@/engine'
import { useGameSound } from '@/audio'
import { useEffects } from '@/effects'

interface PlayerProps {
  position?: [number, number, number]
  speed?: number
  jumpForce?: number
}

// 动画状态
type AnimState = 'idle' | 'walk' | 'jump' | 'fall'

export const Player = forwardRef<RapierRigidBody | null, PlayerProps>(function Player({
  position = [0, 2, 0],
  speed = 5,
  jumpForce = 8,
}, forwardedRef) {
  const rigidBodyRef = useRef<RapierRigidBody>(null)
  const meshRef = useRef<THREE.Group>(null)
  const entityRef = useRef<Entity | null>(null)
  const isGrounded = useRef(true)
  const wasGrounded = useRef(true)
  const isMoving = useRef(false)
  const targetRotation = useRef(0)
  
  // 暴露 RigidBody 引用给父组件
  useEffect(() => {
    if (typeof forwardedRef === 'function') {
      forwardedRef(rigidBodyRef.current)
    } else if (forwardedRef) {
      forwardedRef.current = rigidBodyRef.current
    }
  })
  
  // 动画相关
  const animState = useRef<AnimState>('idle')
  const animTime = useRef(0)
  const jumpPhase = useRef(0) // 跳跃阶段：0=准备, 1=上升, 2=下降
  
  // 肢体引用
  const leftArmRef = useRef<THREE.Mesh>(null)
  const rightArmRef = useRef<THREE.Mesh>(null)
  const leftLegRef = useRef<THREE.Mesh>(null)
  const rightLegRef = useRef<THREE.Mesh>(null)
  const bodyRef = useRef<THREE.Mesh>(null)
  const headRef = useRef<THREE.Mesh>(null)
  
  const { playJump, playLand } = useGameSound()
  const { spawnDustEffect } = useEffects()
  const { world: rapierWorld } = useRapier()

  const getMovementInput = useInputStore((state) => state.getMovementInput)
  const isActionPressed = useInputStore((state) => state.isActionPressed)

  // 创建 ECS 实体
  useEffect(() => {
    entityRef.current = world.add({
      id: 'player',
      name: 'Player',
      player: {
        speed,
        jumpForce,
        health: 100,
        maxHealth: 100,
      },
      controllable: true,
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

  // 更新刚体引用
  useEffect(() => {
    if (entityRef.current && rigidBodyRef.current) {
      entityRef.current.rigidBody = rigidBodyRef.current
    }
  }, [rigidBodyRef.current])

  // 预分配向量避免每帧创建
  const forwardVec = useRef(new THREE.Vector3())
  const rightVec = useRef(new THREE.Vector3())

  // 动画更新函数
  const updateAnimation = (delta: number, velocityY: number) => {
    animTime.current += delta
    
    const t = animTime.current
    
    // 根据状态播放动画
    switch (animState.current) {
      case 'idle': {
        // 待机：轻微呼吸动画
        const breathe = Math.sin(t * 2) * 0.02
        
        if (bodyRef.current) {
          bodyRef.current.position.y = breathe
        }
        if (headRef.current) {
          headRef.current.position.y = 0.7 + breathe * 1.5
        }
        
        // 手臂轻微摆动
        if (leftArmRef.current) {
          leftArmRef.current.rotation.x = Math.sin(t * 1.5) * 0.05
          leftArmRef.current.rotation.z = 0.2
        }
        if (rightArmRef.current) {
          rightArmRef.current.rotation.x = Math.sin(t * 1.5 + Math.PI) * 0.05
          rightArmRef.current.rotation.z = -0.2
        }
        
        // 腿部保持静止
        if (leftLegRef.current) {
          leftLegRef.current.rotation.x = 0
          leftLegRef.current.position.set(0, -0.3, 0)
        }
        if (rightLegRef.current) {
          rightLegRef.current.rotation.x = 0
          rightLegRef.current.position.set(0, -0.3, 0)
        }
        break
      }
      
      case 'walk': {
        // 行走：腿部和手臂摆动
        const walkSpeed = 12
        const legSwing = Math.sin(t * walkSpeed) * 0.5
        const armSwing = Math.sin(t * walkSpeed) * 0.4
        const bounce = Math.abs(Math.sin(t * walkSpeed)) * 0.05
        
        // 身体上下弹跳
        if (bodyRef.current) {
          bodyRef.current.position.y = bounce
        }
        if (headRef.current) {
          headRef.current.position.y = 0.7 + bounce
          // 头部轻微左右摇摆
          headRef.current.rotation.z = Math.sin(t * walkSpeed) * 0.03
        }
        
        // 手臂与对侧腿同步摆动
        if (leftArmRef.current) {
          leftArmRef.current.rotation.x = -armSwing
          leftArmRef.current.rotation.z = 0.2 + Math.abs(armSwing) * 0.1
        }
        if (rightArmRef.current) {
          rightArmRef.current.rotation.x = armSwing
          rightArmRef.current.rotation.z = -0.2 - Math.abs(armSwing) * 0.1
        }
        
        // 腿部摆动
        if (leftLegRef.current) {
          leftLegRef.current.rotation.x = legSwing
          leftLegRef.current.position.z = Math.sin(t * walkSpeed) * 0.1
        }
        if (rightLegRef.current) {
          rightLegRef.current.rotation.x = -legSwing
          rightLegRef.current.position.z = -Math.sin(t * walkSpeed) * 0.1
        }
        break
      }
      
      case 'jump': {
        // 跳跃动画 - 上升阶段：手臂向上，腿部收起
        if (velocityY > 0.5) {
          // 手臂向上伸展
          if (leftArmRef.current) {
            leftArmRef.current.rotation.x = -2.5 // 向上
            leftArmRef.current.rotation.z = 0.5
          }
          if (rightArmRef.current) {
            rightArmRef.current.rotation.x = -2.5
            rightArmRef.current.rotation.z = -0.5
          }
          
          // 腿部略微收起
          if (leftLegRef.current) {
            leftLegRef.current.rotation.x = -0.3
            leftLegRef.current.position.y = -0.25
          }
          if (rightLegRef.current) {
            rightLegRef.current.rotation.x = -0.3
            rightLegRef.current.position.y = -0.25
          }
          
          // 身体略微后仰
          if (bodyRef.current) {
            bodyRef.current.rotation.x = -0.1
          }
        }
        break
      }
      
      case 'fall': {
        // 下落动画：手臂张开保持平衡，腿部向下伸展准备落地
        if (leftArmRef.current) {
          leftArmRef.current.rotation.x = -0.5
          leftArmRef.current.rotation.z = 1.0 // 向外张开
        }
        if (rightArmRef.current) {
          rightArmRef.current.rotation.x = -0.5
          rightArmRef.current.rotation.z = -1.0
        }
        
        // 腿部向下伸展
        if (leftLegRef.current) {
          leftLegRef.current.rotation.x = 0.2
          leftLegRef.current.position.y = -0.3
        }
        if (rightLegRef.current) {
          rightLegRef.current.rotation.x = 0.2
          rightLegRef.current.position.y = -0.3
        }
        
        // 身体略微前倾
        if (bodyRef.current) {
          bodyRef.current.rotation.x = 0.1
        }
        break
      }
    }
  }

  // 每帧更新
  useFrame((state, delta) => {
    if (!rigidBodyRef.current) return

    const rb = rigidBodyRef.current
    const movement = getMovementInput()

    // 获取当前速度
    const velocity = rb.linvel()

    // 从相机获取前进和右侧方向（投影到水平面）
    const camera = state.camera
    
    // 获取相机前进方向并投影到XZ平面
    camera.getWorldDirection(forwardVec.current)
    forwardVec.current.y = 0
    forwardVec.current.normalize()
    
    // 计算右侧方向（前进方向叉乘上向量）
    rightVec.current.crossVectors(forwardVec.current, camera.up).normalize()
    
    // 基于相机方向计算世界空间移动向量
    const worldX = (rightVec.current.x * movement.x + forwardVec.current.x * movement.y) * speed
    const worldZ = (rightVec.current.z * movement.x + forwardVec.current.z * movement.y) * speed
    
    // 平滑插值移动速度
    const lerpFactor = 1 - Math.exp(-15 * delta)
    const moveX = velocity.x + (worldX - velocity.x) * lerpFactor
    const moveZ = velocity.z + (worldZ - velocity.z) * lerpFactor

    // 应用移动
    rb.setLinvel({ x: moveX, y: velocity.y, z: moveZ }, true)

    // 获取当前位置
    const pos = rb.translation()

    // 跳跃
    if (isActionPressed('jump') && isGrounded.current) {
      rb.setLinvel({ x: moveX, y: jumpForce, z: moveZ }, true)
      isGrounded.current = false
      jumpPhase.current = 0
      spawnDustEffect([pos.x, pos.y - 0.5, pos.z])
      playJump()
    }

    // 地面检测
    wasGrounded.current = isGrounded.current
    
    const rayOrigin = { x: pos.x, y: pos.y, z: pos.z }
    const rayDirection = { x: 0, y: -1, z: 0 }
    const ray = new Ray(rayOrigin, rayDirection)
    
    const maxToi = 0.95
    const hit = rapierWorld.castRay(ray, maxToi, true, undefined, undefined, undefined, rb)
    
    const wasOnGround = isGrounded.current
    isGrounded.current = hit !== null
    
    // 落地检测
    if (!wasOnGround && isGrounded.current) {
      spawnDustEffect([pos.x, pos.y - 0.5, pos.z])
      playLand()
    }
    
    // 检测是否在移动
    isMoving.current = Math.abs(worldX) > 0.1 || Math.abs(worldZ) > 0.1

    // 更新动画状态
    const prevState = animState.current
    if (!isGrounded.current) {
      if (velocity.y > 0.5) {
        animState.current = 'jump'
        jumpPhase.current += delta * 3
      } else {
        animState.current = 'fall'
      }
    } else if (isMoving.current) {
      animState.current = 'walk'
    } else {
      animState.current = 'idle'
    }
    
    // 状态切换时重置动画时间
    if (prevState !== animState.current) {
      if (animState.current === 'idle' || animState.current === 'walk') {
        // 重置身体旋转
        if (bodyRef.current) {
          bodyRef.current.rotation.x = 0
        }
      }
    }

    // 更新人物朝向
    targetRotation.current = Math.atan2(forwardVec.current.x, forwardVec.current.z)
    
    if (meshRef.current) {
      let currentRotation = meshRef.current.rotation.y
      let diff = targetRotation.current - currentRotation
      
      while (diff > Math.PI) diff -= Math.PI * 2
      while (diff < -Math.PI) diff += Math.PI * 2
      
      meshRef.current.rotation.y += diff * Math.min(1, 10 * delta)
    }

    // 更新动画
    updateAnimation(delta, velocity.y)

    // 更新 ECS 实体的变换
    if (entityRef.current?.transform) {
      entityRef.current.transform.position.set(pos.x, pos.y, pos.z)
    }
  })

  return (
    <RigidBody
      ref={rigidBodyRef}
      position={position}
      enabledRotations={[false, false, false]}
      linearDamping={0.5}
      mass={1}
      type="dynamic"
      colliders={false}
    >
      <CapsuleCollider args={[0.5, 0.3]} />
      
      {/* 人形模型 - 向上偏移使腿部在碰撞器内 */}
      <group ref={meshRef} position={[0, 0.35, 0]}>
        {/* 身体 */}
        <mesh ref={bodyRef} castShadow position={[0, 0, 0]}>
          <capsuleGeometry args={[0.25, 0.6, 8, 16]} />
          <meshStandardMaterial
            color="#2a2a5e"
            emissive="#00ffff"
            emissiveIntensity={0.2}
            metalness={0.7}
            roughness={0.3}
          />
        </mesh>
        
        {/* 头部 */}
        <mesh ref={headRef} castShadow position={[0, 0.7, 0]}>
          <sphereGeometry args={[0.2, 16, 16]} />
          <meshStandardMaterial
            color="#1a1a3e"
            emissive="#ff00ff"
            emissiveIntensity={0.3}
            metalness={0.8}
            roughness={0.2}
          />
        </mesh>
        
        {/* 眼睛/面罩发光 */}
        <mesh position={[0, 0.7, 0.15]}>
          <boxGeometry args={[0.25, 0.06, 0.05]} />
          <meshBasicMaterial color="#00ffff" />
        </mesh>
        
        {/* 左臂 */}
        <group position={[-0.35, 0.1, 0]}>
          <mesh ref={leftArmRef} castShadow rotation={[0, 0, 0.2]}>
            <capsuleGeometry args={[0.08, 0.4, 8, 8]} />
            <meshStandardMaterial
              color="#2a2a5e"
              emissive="#00ffff"
              emissiveIntensity={0.15}
              metalness={0.6}
              roughness={0.4}
            />
          </mesh>
        </group>
        
        {/* 右臂 */}
        <group position={[0.35, 0.1, 0]}>
          <mesh ref={rightArmRef} castShadow rotation={[0, 0, -0.2]}>
            <capsuleGeometry args={[0.08, 0.4, 8, 8]} />
            <meshStandardMaterial
              color="#2a2a5e"
              emissive="#00ffff"
              emissiveIntensity={0.15}
              metalness={0.6}
              roughness={0.4}
            />
          </mesh>
        </group>
        
        {/* 左腿 */}
        <group position={[-0.12, -0.35, 0]}>
          <mesh ref={leftLegRef} castShadow position={[0, -0.3, 0]}>
            <capsuleGeometry args={[0.1, 0.4, 8, 8]} />
            <meshStandardMaterial
              color="#1a1a3e"
              emissive="#ff00ff"
              emissiveIntensity={0.1}
              metalness={0.6}
              roughness={0.4}
            />
          </mesh>
        </group>
        
        {/* 右腿 */}
        <group position={[0.12, -0.35, 0]}>
          <mesh ref={rightLegRef} castShadow position={[0, -0.3, 0]}>
            <capsuleGeometry args={[0.1, 0.4, 8, 8]} />
            <meshStandardMaterial
              color="#1a1a3e"
              emissive="#ff00ff"
              emissiveIntensity={0.1}
              metalness={0.6}
              roughness={0.4}
            />
          </mesh>
        </group>
        
        {/* 背部装饰/背包 */}
        <mesh castShadow position={[0, 0.1, -0.2]}>
          <boxGeometry args={[0.3, 0.4, 0.15]} />
          <meshStandardMaterial
            color="#1a1a3e"
            emissive="#00ffff"
            emissiveIntensity={0.2}
            metalness={0.8}
            roughness={0.2}
          />
        </mesh>
        
        {/* 腰带发光条 */}
        <mesh position={[0, -0.2, 0]} rotation={[Math.PI / 2, 0, 0]}>
          <torusGeometry args={[0.26, 0.03, 8, 32]} />
          <meshBasicMaterial color="#ff00ff" />
        </mesh>
      </group>
    </RigidBody>
  )
})
