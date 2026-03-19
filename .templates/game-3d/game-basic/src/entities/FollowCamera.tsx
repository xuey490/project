import { useRef, useEffect } from 'react'
import { useFrame, useThree } from '@react-three/fiber'
import * as THREE from 'three'
import { queries, useInputStore, useGameStore, GameState } from '@/engine'

interface FollowCameraProps {
  offset?: [number, number, number]
  smoothness?: number
  lookAhead?: number // 视角前瞻
  sensitivity?: number // 鼠标灵敏度
  minPolarAngle?: number // 最小垂直角度
  maxPolarAngle?: number // 最大垂直角度
}

export function FollowCamera({
  offset = [0, 8, 12],
  smoothness = 8,
  lookAhead = 0.5,
  sensitivity = 0.002,
  minPolarAngle = 0.2, // 约 11 度
  maxPolarAngle = Math.PI / 2.2, // 约 82 度
}: FollowCameraProps) {
  const { camera, gl } = useThree()
  const gameState = useGameStore((state) => state.gameState)
  const isPaused = useGameStore((state) => state.isPaused)
  
  // 球面坐标（用于鼠标旋转控制）
  const spherical = useRef({
    radius: Math.sqrt(offset[0] ** 2 + offset[1] ** 2 + offset[2] ** 2),
    theta: Math.atan2(offset[0], offset[2]), // 水平角度
    phi: Math.acos(offset[1] / Math.sqrt(offset[0] ** 2 + offset[1] ** 2 + offset[2] ** 2)), // 垂直角度
  })
  
  // 预分配向量，避免每帧创建
  const targetPosition = useRef(new THREE.Vector3())
  const currentPosition = useRef(new THREE.Vector3())
  const lookAtTarget = useRef(new THREE.Vector3())
  const playerVelocity = useRef(new THREE.Vector3())
  const cameraOffset = useRef(new THREE.Vector3())
  
  // Pointer Lock 状态
  const isLocked = useRef(false)
  
  // 初始化标志
  const initialized = useRef(false)

  // 处理指针锁定
  useEffect(() => {
    const canvas = gl.domElement
    
    const handleClick = () => {
      if (gameState === GameState.PLAYING && !isPaused && !isLocked.current) {
        canvas.requestPointerLock()
      }
    }
    
    const handlePointerLockChange = () => {
      isLocked.current = document.pointerLockElement === canvas
    }
    
    const handlePointerLockError = () => {
      console.warn('Pointer lock error')
    }
    
    canvas.addEventListener('click', handleClick)
    document.addEventListener('pointerlockchange', handlePointerLockChange)
    document.addEventListener('pointerlockerror', handlePointerLockError)
    
    return () => {
      canvas.removeEventListener('click', handleClick)
      document.removeEventListener('pointerlockchange', handlePointerLockChange)
      document.removeEventListener('pointerlockerror', handlePointerLockError)
      
      // 退出时解锁
      if (document.pointerLockElement === canvas) {
        document.exitPointerLock()
      }
    }
  }, [gl, gameState, isPaused])
  
  // 暂停时退出指针锁定
  useEffect(() => {
    if (isPaused && isLocked.current) {
      document.exitPointerLock()
    }
  }, [isPaused])
  
  // 游戏状态变化时处理指针锁定
  useEffect(() => {
    if (gameState !== GameState.PLAYING && isLocked.current) {
      document.exitPointerLock()
    }
  }, [gameState])

  useFrame((_, delta) => {
    // 获取鼠标增量
    const mouseDelta = useInputStore.getState().mouseDelta
    
    // 只在锁定状态下响应鼠标移动
    if (isLocked.current && gameState === GameState.PLAYING && !isPaused) {
      // 更新水平角度（左右旋转）
      spherical.current.theta -= mouseDelta.x * sensitivity
      
      // 更新垂直角度（上下旋转），限制范围
      spherical.current.phi -= mouseDelta.y * sensitivity
      spherical.current.phi = Math.max(minPolarAngle, Math.min(maxPolarAngle, spherical.current.phi))
    }
    
    // 更新相机水平角度到 Input Store（用于基于相机的移动）
    useInputStore.getState().setCameraYaw(spherical.current.theta)
    
    // 重置鼠标增量（避免累积）
    useInputStore.getState().setMouseDelta(0, 0)
    
    // 获取玩家实体
    const playerQuery = queries.players
    let player = null
    for (const p of playerQuery) {
      player = p
      break
    }
    
    if (!player?.rigidBody) return

    // 获取玩家位置
    const pos = player.rigidBody.translation()
    const vel = player.rigidBody.linvel()
    
    // 计算玩家速度向量（用于视角前瞻）
    playerVelocity.current.set(vel.x, 0, vel.z)
    
    // 从球面坐标计算相机偏移
    const { radius, theta, phi } = spherical.current
    cameraOffset.current.set(
      radius * Math.sin(phi) * Math.sin(theta),
      radius * Math.cos(phi),
      radius * Math.sin(phi) * Math.cos(theta)
    )
    
    // 目标相机位置 = 玩家位置 + 相机偏移 + 速度前瞻
    targetPosition.current.set(
      pos.x + cameraOffset.current.x + playerVelocity.current.x * lookAhead,
      pos.y + cameraOffset.current.y,
      pos.z + cameraOffset.current.z + playerVelocity.current.z * lookAhead
    )

    // 首次初始化时直接跳到目标位置
    if (!initialized.current) {
      currentPosition.current.copy(targetPosition.current)
      camera.position.copy(currentPosition.current)
      initialized.current = true
    }

    // 使用指数平滑插值（帧率无关）
    const lerpFactor = 1 - Math.exp(-smoothness * delta)
    
    currentPosition.current.x += (targetPosition.current.x - currentPosition.current.x) * lerpFactor
    currentPosition.current.y += (targetPosition.current.y - currentPosition.current.y) * lerpFactor
    currentPosition.current.z += (targetPosition.current.z - currentPosition.current.z) * lerpFactor

    // 更新相机位置
    camera.position.copy(currentPosition.current)
    
    // 平滑注视点
    lookAtTarget.current.set(pos.x, pos.y + 1, pos.z)
    camera.lookAt(lookAtTarget.current)
  })

  return null
}
