import { create } from 'zustand'

// 输入键位映射
export interface KeyBindings {
  forward: string[]
  backward: string[]
  left: string[]
  right: string[]
  jump: string[]
  sprint: string[]
  interact: string[]
  pause: string[]
}

// 输入状态
interface InputState {
  // 按键状态
  keys: Set<string>
  
  // 鼠标状态
  mousePosition: { x: number; y: number }
  mouseDelta: { x: number; y: number }
  mouseButtons: Set<number>
  
  // 游戏手柄状态
  gamepadConnected: boolean
  gamepadAxes: number[]
  gamepadButtons: boolean[]
  
  // 键位绑定
  bindings: KeyBindings
  
  // 相机水平角度（用于基于相机方向的移动）
  cameraYaw: number
}

interface InputStore extends InputState {
  // 按键操作
  setKeyDown: (key: string) => void
  setKeyUp: (key: string) => void
  isKeyDown: (key: string) => boolean
  
  // 鼠标操作
  setMousePosition: (x: number, y: number) => void
  setMouseDelta: (x: number, y: number) => void
  setMouseButton: (button: number, pressed: boolean) => void
  
  // 手柄操作
  setGamepadState: (axes: number[], buttons: boolean[]) => void
  setGamepadConnected: (connected: boolean) => void
  
  // 输入查询
  getMovementInput: () => { x: number; y: number }
  isActionPressed: (action: keyof KeyBindings) => boolean
  
  // 重置
  resetInput: () => void
  
  // 更新键位绑定
  updateBindings: (bindings: Partial<KeyBindings>) => void
  
  // 相机角度
  setCameraYaw: (yaw: number) => void
}

// 默认键位
const defaultBindings: KeyBindings = {
  forward: ['KeyW', 'ArrowUp'],
  backward: ['KeyS', 'ArrowDown'],
  left: ['KeyA', 'ArrowLeft'],
  right: ['KeyD', 'ArrowRight'],
  jump: ['Space'],
  sprint: ['ShiftLeft', 'ShiftRight'],
  interact: ['KeyE', 'KeyF'],
  pause: ['Escape'],
}

export const useInputStore = create<InputStore>((set, get) => ({
  // 初始状态
  keys: new Set<string>(),
  mousePosition: { x: 0, y: 0 },
  mouseDelta: { x: 0, y: 0 },
  mouseButtons: new Set<number>(),
  gamepadConnected: false,
  gamepadAxes: [],
  gamepadButtons: [],
  bindings: defaultBindings,
  cameraYaw: 0,

  // 按键操作
  setKeyDown: (key) =>
    set((state) => {
      const newKeys = new Set(state.keys)
      newKeys.add(key)
      return { keys: newKeys }
    }),

  setKeyUp: (key) =>
    set((state) => {
      const newKeys = new Set(state.keys)
      newKeys.delete(key)
      return { keys: newKeys }
    }),

  isKeyDown: (key) => get().keys.has(key),

  // 鼠标操作
  setMousePosition: (x, y) =>
    set({ mousePosition: { x, y } }),

  setMouseDelta: (x, y) =>
    set({ mouseDelta: { x, y } }),

  setMouseButton: (button, pressed) =>
    set((state) => {
      const newButtons = new Set(state.mouseButtons)
      if (pressed) {
        newButtons.add(button)
      } else {
        newButtons.delete(button)
      }
      return { mouseButtons: newButtons }
    }),

  // 手柄操作
  setGamepadState: (axes, buttons) =>
    set({ gamepadAxes: axes, gamepadButtons: buttons }),

  setGamepadConnected: (connected) =>
    set({ gamepadConnected: connected }),

  // 获取移动输入
  getMovementInput: () => {
    const state = get()
    let x = 0
    let y = 0

    // 键盘输入
    if (state.isActionPressed('right')) x += 1
    if (state.isActionPressed('left')) x -= 1
    if (state.isActionPressed('forward')) y += 1
    if (state.isActionPressed('backward')) y -= 1

    // 手柄输入（如果连接）
    if (state.gamepadConnected && state.gamepadAxes.length >= 2) {
      const deadzone = 0.15
      const gx = Math.abs(state.gamepadAxes[0]) > deadzone ? state.gamepadAxes[0] : 0
      const gy = Math.abs(state.gamepadAxes[1]) > deadzone ? state.gamepadAxes[1] : 0
      x += gx
      y -= gy // Y轴通常是反转的
    }

    // 归一化
    const length = Math.sqrt(x * x + y * y)
    if (length > 1) {
      x /= length
      y /= length
    }

    return { x, y }
  },

  // 检查动作是否按下
  isActionPressed: (action) => {
    const state = get()
    const keys = state.bindings[action]
    return keys.some((key) => state.keys.has(key))
  },

  // 重置输入
  resetInput: () =>
    set({
      keys: new Set<string>(),
      mouseButtons: new Set<number>(),
      mouseDelta: { x: 0, y: 0 },
    }),

  // 更新键位绑定
  updateBindings: (newBindings) =>
    set((state) => ({
      bindings: { ...state.bindings, ...newBindings },
    })),

  // 设置相机水平角度
  setCameraYaw: (yaw) => set({ cameraYaw: yaw }),
}))
