// 3D 空间音效组件 (基于 Drei 的 PositionalAudio)
import { useRef, useEffect, useState } from 'react'
import { useFrame } from '@react-three/fiber'
import { PositionalAudio as DreiPositionalAudio } from '@react-three/drei'
import * as THREE from 'three'
import { useAudioStore } from './AudioStore'

interface GameAudioProps {
  url: string
  loop?: boolean
  distance?: number        // 声音开始衰减的距离
  playTrigger?: boolean | number  // 触发播放信号（布尔值或时间戳）
  autoplay?: boolean
  volume?: number         // 基础音量 0-1
}

/**
 * 3D 空间音效组件
 * 用于挂载到游戏实体上，实现空间音效
 */
export function GameAudio({
  url,
  loop = false,
  distance = 10,
  playTrigger = false,
  autoplay = false,
  volume = 1,
}: GameAudioProps) {
  const soundRef = useRef<THREE.PositionalAudio>(null)
  const { muted, getEffectiveSfxVolume } = useAudioStore()
  const lastTrigger = useRef<boolean | number>(playTrigger)
  
  // 响应音量/静音变化
  useEffect(() => {
    if (soundRef.current) {
      const effectiveVolume = muted ? 0 : getEffectiveSfxVolume() * volume
      soundRef.current.setVolume(effectiveVolume)
    }
  }, [muted, volume])
  
  // 订阅音量变化
  useEffect(() => {
    const unsubscribe = useAudioStore.subscribe(
      (state) => state.getEffectiveSfxVolume(),
      (sfxVolume) => {
        if (soundRef.current) {
          const effectiveVolume = useAudioStore.getState().muted ? 0 : sfxVolume * volume
          soundRef.current.setVolume(effectiveVolume)
        }
      }
    )
    return unsubscribe
  }, [volume])
  
  // 响应触发信号
  useEffect(() => {
    if (playTrigger !== lastTrigger.current && playTrigger) {
      if (soundRef.current) {
        // 如果正在播放，先停止
        if (soundRef.current.isPlaying) {
          soundRef.current.stop()
        }
        soundRef.current.play()
      }
    }
    lastTrigger.current = playTrigger
  }, [playTrigger])

  return (
    <DreiPositionalAudio
      ref={soundRef}
      url={url}
      distance={distance}
      loop={loop}
      autoplay={autoplay || loop}
    />
  )
}

interface AmbientSoundProps {
  url: string
  position?: [number, number, number]
  distance?: number
  volume?: number
}

/**
 * 环境音效组件
 * 用于场景中固定位置的环境音效（如瀑布、机械声等）
 */
export function AmbientSound({
  url,
  position = [0, 0, 0],
  distance = 20,
  volume = 0.5,
}: AmbientSoundProps) {
  return (
    <group position={position}>
      <GameAudio
        url={url}
        loop
        autoplay
        distance={distance}
        volume={volume}
      />
    </group>
  )
}

interface FootstepAudioProps {
  isMoving: boolean
  isGrounded: boolean
  interval?: number  // 脚步声间隔 (ms)
}

/**
 * 脚步声组件
 * 根据移动状态自动播放脚步声
 */
export function FootstepAudio({
  isMoving,
  isGrounded,
  interval = 400,
}: FootstepAudioProps) {
  const [playTrigger, setPlayTrigger] = useState(0)
  const lastStepTime = useRef(0)
  
  useFrame((state) => {
    if (isMoving && isGrounded) {
      const now = state.clock.elapsedTime * 1000
      if (now - lastStepTime.current > interval) {
        setPlayTrigger(now)
        lastStepTime.current = now
      }
    }
  })
  
  return (
    <GameAudio
      url="/sounds/footstep.mp3"
      playTrigger={playTrigger}
      distance={5}
      volume={0.4}
    />
  )
}

interface ImpactAudioProps {
  onImpact?: number  // 碰撞时间戳
  intensity?: number // 碰撞强度 0-1
}

/**
 * 碰撞音效组件
 */
export function ImpactAudio({
  onImpact = 0,
  intensity = 1,
}: ImpactAudioProps) {
  return (
    <GameAudio
      url="/sounds/impact.mp3"
      playTrigger={onImpact}
      distance={15}
      volume={Math.min(1, intensity)}
    />
  )
}
