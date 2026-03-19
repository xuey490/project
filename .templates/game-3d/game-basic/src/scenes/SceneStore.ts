// 场景管理状态
import { create } from 'zustand'
import { useShallow } from 'zustand/react/shallow'

// 场景类型
export type SceneType = 
  | 'menu'      // 主菜单
  | 'game'      // 游戏场景
  | 'level1'    // 关卡1
  | 'level2'    // 关卡2
  | 'boss'      // Boss关卡
  | string      // 自定义场景

// 场景元数据
export interface SceneMeta {
  id: SceneType
  name: string
  description?: string
  // 场景特有配置
  config?: Record<string, unknown>
}

// 过渡类型
export type TransitionType = 
  | 'fade'      // 淡入淡出
  | 'slide'     // 滑动
  | 'zoom'      // 缩放
  | 'glitch'    // 故障风格
  | 'none'      // 无过渡

// 过渡状态
export type TransitionState = 'idle' | 'exiting' | 'loading' | 'entering'

// 场景历史记录
interface SceneHistoryEntry {
  scene: SceneType
  timestamp: number
  meta?: SceneMeta
}

// 场景管理状态
interface SceneState {
  // 当前场景
  currentScene: SceneType
  previousScene: SceneType | null
  
  // 场景元数据
  sceneMeta: SceneMeta | null
  
  // 过渡状态
  transitionState: TransitionState
  transitionType: TransitionType
  transitionProgress: number // 0-1
  transitionDuration: number // 毫秒
  
  // 场景历史
  history: SceneHistoryEntry[]
  maxHistoryLength: number
  
  // 场景数据（用于场景间传递数据）
  sceneData: Record<string, unknown>
  
  // 加载状态
  isLoading: boolean
  loadProgress: number
}

// 场景管理操作
interface SceneActions {
  // 切换场景
  switchScene: (
    scene: SceneType, 
    options?: {
      transition?: TransitionType
      duration?: number
      data?: Record<string, unknown>
      meta?: SceneMeta
    }
  ) => Promise<void>
  
  // 返回上一场景
  goBack: (options?: { transition?: TransitionType }) => Promise<boolean>
  
  // 重新加载当前场景
  reloadScene: () => Promise<void>
  
  // 更新过渡进度
  setTransitionProgress: (progress: number) => void
  setTransitionState: (state: TransitionState) => void
  
  // 设置场景数据
  setSceneData: (key: string, value: unknown) => void
  getSceneData: <T>(key: string) => T | undefined
  clearSceneData: () => void
  
  // 加载状态
  setLoading: (loading: boolean, progress?: number) => void
  
  // 注册场景元数据
  registerSceneMeta: (meta: SceneMeta) => void
  getSceneMeta: (sceneId: SceneType) => SceneMeta | undefined
  
  // 清除历史
  clearHistory: () => void
}

// 场景元数据注册表
const sceneRegistry = new Map<SceneType, SceneMeta>()

// 默认场景元数据
const defaultScenes: SceneMeta[] = [
  { id: 'menu', name: '主菜单', description: '游戏主菜单' },
  { id: 'game', name: '游戏', description: '主游戏场景' },
]

// 初始化默认场景
defaultScenes.forEach(meta => sceneRegistry.set(meta.id, meta))

// 创建场景管理 Store
export const useSceneStore = create<SceneState & SceneActions>((set, get) => ({
  // 初始状态
  currentScene: 'game',
  previousScene: null,
  sceneMeta: sceneRegistry.get('game') || null,
  
  transitionState: 'idle',
  transitionType: 'fade',
  transitionProgress: 0,
  transitionDuration: 500,
  
  history: [],
  maxHistoryLength: 20,
  
  sceneData: {},
  
  isLoading: false,
  loadProgress: 0,
  
  // 切换场景
  switchScene: async (scene, options = {}) => {
    const { 
      transition = 'fade', 
      duration = 500, 
      data,
      meta 
    } = options
    
    const { currentScene, history, maxHistoryLength } = get()
    
    // 如果已经在目标场景，跳过
    if (currentScene === scene) return
    
    // 注册新场景元数据
    if (meta) {
      sceneRegistry.set(scene, meta)
    }
    
    // 开始退出过渡
    set({
      transitionState: 'exiting',
      transitionType: transition,
      transitionDuration: duration,
      transitionProgress: 0,
      isLoading: true,
    })
    
    // 退出动画
    await animateTransition(duration / 2, (progress) => {
      set({ transitionProgress: progress })
    })
    
    // 记录历史
    const newHistory = [
      { scene: currentScene, timestamp: Date.now(), meta: get().sceneMeta || undefined },
      ...history
    ].slice(0, maxHistoryLength)
    
    // 切换场景
    set({
      transitionState: 'loading',
      previousScene: currentScene,
      currentScene: scene,
      sceneMeta: sceneRegistry.get(scene) || null,
      history: newHistory,
      sceneData: data ? { ...get().sceneData, ...data } : get().sceneData,
    })
    
    // 进入动画
    set({ transitionState: 'entering', transitionProgress: 0 })
    
    await animateTransition(duration / 2, (progress) => {
      set({ transitionProgress: progress })
    })
    
    // 完成
    set({
      transitionState: 'idle',
      transitionProgress: 1,
      isLoading: false,
    })
  },
  
  // 返回上一场景
  goBack: async (options = {}) => {
    const { history } = get()
    
    if (history.length === 0) return false
    
    const [previous, ...rest] = history
    
    // 移除历史记录
    set({ history: rest })
    
    // 切换到上一场景
    await get().switchScene(previous.scene, {
      transition: options.transition || 'fade',
      meta: previous.meta,
    })
    
    return true
  },
  
  // 重新加载当前场景
  reloadScene: async () => {
    const { currentScene, sceneMeta } = get()
    
    // 先退出
    set({ transitionState: 'exiting', transitionProgress: 0 })
    
    await animateTransition(300, (progress) => {
      set({ transitionProgress: progress })
    })
    
    // 强制重新挂载（通过临时设置为 null）
    set({ 
      transitionState: 'loading',
      currentScene: '' as SceneType 
    })
    
    // 短暂延迟后恢复
    await new Promise(resolve => setTimeout(resolve, 50))
    
    set({ 
      currentScene,
      sceneMeta,
      transitionState: 'entering',
      transitionProgress: 0,
    })
    
    await animateTransition(300, (progress) => {
      set({ transitionProgress: progress })
    })
    
    set({ transitionState: 'idle', transitionProgress: 1 })
  },
  
  // 过渡控制
  setTransitionProgress: (progress) => set({ transitionProgress: progress }),
  setTransitionState: (state) => set({ transitionState: state }),
  
  // 场景数据
  setSceneData: (key, value) => set(state => ({
    sceneData: { ...state.sceneData, [key]: value }
  })),
  
  getSceneData: <T,>(key: string) => get().sceneData[key] as T | undefined,
  
  clearSceneData: () => set({ sceneData: {} }),
  
  // 加载状态
  setLoading: (loading, progress = 0) => set({ 
    isLoading: loading, 
    loadProgress: progress 
  }),
  
  // 场景元数据
  registerSceneMeta: (meta) => {
    sceneRegistry.set(meta.id, meta)
  },
  
  getSceneMeta: (sceneId) => sceneRegistry.get(sceneId),
  
  // 清除历史
  clearHistory: () => set({ history: [] }),
}))

// 过渡动画辅助函数
async function animateTransition(
  duration: number, 
  onProgress: (progress: number) => void
): Promise<void> {
  return new Promise(resolve => {
    const startTime = performance.now()
    
    function update() {
      const elapsed = performance.now() - startTime
      const progress = Math.min(elapsed / duration, 1)
      
      // 使用 easeInOutCubic
      const eased = progress < 0.5
        ? 4 * progress * progress * progress
        : 1 - Math.pow(-2 * progress + 2, 3) / 2
      
      onProgress(eased)
      
      if (progress < 1) {
        requestAnimationFrame(update)
      } else {
        resolve()
      }
    }
    
    requestAnimationFrame(update)
  })
}

// ============ 便捷 Hooks ============

// 获取当前场景
export function useCurrentScene() {
  return useSceneStore(state => state.currentScene)
}

// 获取过渡状态
export function useTransition() {
  return useSceneStore(useShallow(state => ({
    state: state.transitionState,
    type: state.transitionType,
    progress: state.transitionProgress,
    isTransitioning: state.transitionState !== 'idle',
  })))
}

// 场景切换 Hook
export function useSceneSwitch() {
  const switchScene = useSceneStore(state => state.switchScene)
  const goBack = useSceneStore(state => state.goBack)
  const reloadScene = useSceneStore(state => state.reloadScene)
  
  return { switchScene, goBack, reloadScene }
}

// 场景数据 Hook
export function useSceneData<T>(key: string): [T | undefined, (value: T) => void] {
  const value = useSceneStore(state => state.sceneData[key] as T | undefined)
  const setSceneData = useSceneStore(state => state.setSceneData)
  
  return [value, (v: T) => setSceneData(key, v)]
}
