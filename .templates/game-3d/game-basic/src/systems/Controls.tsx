import { useEffect } from 'react'
import { useFrame } from '@react-three/fiber'
import { useInputStore, useGameStore } from '@/engine'
import { defaultSystems } from './GameSystems'

// 键盘输入控制器
export function KeyboardControls() {
  const setKeyDown = useInputStore((state) => state.setKeyDown)
  const setKeyUp = useInputStore((state) => state.setKeyUp)
  const togglePause = useGameStore((state) => state.togglePause)

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      setKeyDown(e.code)
      if (e.code === 'Escape') {
        togglePause()
      }
    }

    const handleKeyUp = (e: KeyboardEvent) => {
      setKeyUp(e.code)
    }

    window.addEventListener('keydown', handleKeyDown)
    window.addEventListener('keyup', handleKeyUp)

    return () => {
      window.removeEventListener('keydown', handleKeyDown)
      window.removeEventListener('keyup', handleKeyUp)
    }
  }, [setKeyDown, setKeyUp, togglePause])

  return null
}

// 鼠标输入控制器
export function MouseControls() {
  const setMousePosition = useInputStore((state) => state.setMousePosition)
  const setMouseDelta = useInputStore((state) => state.setMouseDelta)
  const setMouseButton = useInputStore((state) => state.setMouseButton)

  useEffect(() => {
    const handleMouseMove = (e: MouseEvent) => {
      setMousePosition(e.clientX, e.clientY)
      setMouseDelta(e.movementX, e.movementY)
    }

    const handleMouseDown = (e: MouseEvent) => {
      setMouseButton(e.button, true)
    }

    const handleMouseUp = (e: MouseEvent) => {
      setMouseButton(e.button, false)
    }

    window.addEventListener('mousemove', handleMouseMove)
    window.addEventListener('mousedown', handleMouseDown)
    window.addEventListener('mouseup', handleMouseUp)

    return () => {
      window.removeEventListener('mousemove', handleMouseMove)
      window.removeEventListener('mousedown', handleMouseDown)
      window.removeEventListener('mouseup', handleMouseUp)
    }
  }, [setMousePosition, setMouseDelta, setMouseButton])

  return null
}

// 游戏手柄控制器
export function GamepadControls() {
  const setGamepadState = useInputStore((state) => state.setGamepadState)
  const setGamepadConnected = useInputStore((state) => state.setGamepadConnected)

  useEffect(() => {
    const handleGamepadConnected = () => {
      setGamepadConnected(true)
    }

    const handleGamepadDisconnected = () => {
      setGamepadConnected(false)
    }

    window.addEventListener('gamepadconnected', handleGamepadConnected)
    window.addEventListener('gamepaddisconnected', handleGamepadDisconnected)

    return () => {
      window.removeEventListener('gamepadconnected', handleGamepadConnected)
      window.removeEventListener('gamepaddisconnected', handleGamepadDisconnected)
    }
  }, [setGamepadConnected])

  useFrame(() => {
    const gamepads = navigator.getGamepads()
    const gamepad = gamepads[0]

    if (gamepad) {
      const axes = [...gamepad.axes]
      const buttons = gamepad.buttons.map((b) => b.pressed)
      setGamepadState(axes, buttons)
    }
  })

  return null
}

// ECS 系统更新器
export function SystemsUpdater() {
  const isPaused = useGameStore((state) => state.isPaused)
  const setTime = useGameStore((state) => state.setTime)

  useFrame((state, delta) => {
    setTime(delta, state.clock.elapsedTime)

    if (!isPaused) {
      defaultSystems.update(delta)
    }
  })

  return null
}

// 所有控制器组合
export function GameControls() {
  return (
    <>
      <KeyboardControls />
      <MouseControls />
      <GamepadControls />
      <SystemsUpdater />
    </>
  )
}
