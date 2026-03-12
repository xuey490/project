// 场景渲染器 - 负责渲染当前场景和过渡效果
import { Suspense, lazy, ComponentType, ReactNode, useMemo } from 'react'
import { useSceneStore, SceneType, useTransition } from './SceneStore'

// 场景组件类型
export interface SceneProps {
  onReady?: () => void
  onUnload?: () => void
  sceneData?: Record<string, unknown>
}

// 场景配置
export interface SceneConfig {
  component: ComponentType<SceneProps> | (() => Promise<{ default: ComponentType<SceneProps> }>)
  preload?: boolean // 是否预加载
  keepAlive?: boolean // 是否保持状态
  fallback?: ReactNode // 加载占位
}

// 场景注册表
const sceneComponents = new Map<SceneType, SceneConfig>()

// 已预加载的场景
const preloadedScenes = new Map<SceneType, ComponentType<SceneProps>>()

// ============ 场景注册 API ============

/**
 * 注册场景组件
 * @param sceneId 场景ID
 * @param config 场景配置
 * 
 * @example
 * // 同步注册
 * registerScene('menu', { component: MenuScene })
 * 
 * // 懒加载
 * registerScene('level1', { 
 *   component: () => import('./scenes/Level1'),
 *   preload: true 
 * })
 */
export function registerScene(sceneId: SceneType, config: SceneConfig) {
  sceneComponents.set(sceneId, config)
  
  // 预加载
  if (config.preload && typeof config.component === 'function') {
    preloadScene(sceneId)
  }
}

/**
 * 批量注册场景
 */
export function registerScenes(scenes: Record<SceneType, SceneConfig>) {
  Object.entries(scenes).forEach(([id, config]) => {
    registerScene(id as SceneType, config)
  })
}

/**
 * 预加载场景
 */
export async function preloadScene(sceneId: SceneType): Promise<void> {
  const config = sceneComponents.get(sceneId)
  if (!config) return
  
  if (typeof config.component === 'function' && !('prototype' in config.component)) {
    try {
      const module = await (config.component as () => Promise<{ default: ComponentType<SceneProps> }>)()
      preloadedScenes.set(sceneId, module.default)
    } catch (error) {
      console.error(`Failed to preload scene: ${sceneId}`, error)
    }
  }
}

/**
 * 获取场景组件
 */
export function getSceneComponent(sceneId: SceneType): ComponentType<SceneProps> | null {
  // 检查预加载缓存
  const preloaded = preloadedScenes.get(sceneId)
  if (preloaded) return preloaded
  
  const config = sceneComponents.get(sceneId)
  if (!config) return null
  
  // 如果是函数（懒加载），包装为 lazy 组件
  if (typeof config.component === 'function' && !('prototype' in config.component)) {
    return lazy(config.component as () => Promise<{ default: ComponentType<SceneProps> }>)
  }
  
  return config.component as ComponentType<SceneProps>
}

// ============ 场景渲染组件 ============

interface SceneRendererProps {
  fallback?: ReactNode
  children?: ReactNode
}

/**
 * 场景渲染器组件
 * 根据当前场景状态渲染对应的场景组件
 */
export function SceneRenderer({ fallback, children }: SceneRendererProps) {
  const currentScene = useSceneStore(state => state.currentScene)
  const sceneData = useSceneStore(state => state.sceneData)
  const { state: transitionState } = useTransition()
  
  // 获取当前场景组件
  const SceneComponent = useMemo(() => {
    if (!currentScene) return null
    return getSceneComponent(currentScene)
  }, [currentScene])
  
  // 如果没有注册场景，显示 children（默认内容）
  if (!SceneComponent) {
    return <>{children}</>
  }
  
  // 渲染场景
  return (
    <Suspense fallback={fallback || null}>
      {transitionState !== 'loading' && (
        <SceneComponent 
          sceneData={sceneData}
        />
      )}
    </Suspense>
  )
}

// ============ Keep-Alive 场景容器 ============

interface KeepAliveSceneProps {
  sceneId: SceneType
  active: boolean
  children: ReactNode
}

/**
 * Keep-Alive 场景容器
 * 保持非活动场景的状态，但不渲染
 */
export function KeepAliveScene({ sceneId, active, children }: KeepAliveSceneProps) {
  const config = sceneComponents.get(sceneId)
  
  // 如果不是 keepAlive 场景，只在活动时渲染
  if (!config?.keepAlive && !active) {
    return null
  }
  
  return (
    <div style={{ display: active ? 'contents' : 'none' }}>
      {children}
    </div>
  )
}

// ============ 场景切换按钮 ============

interface SceneSwitchButtonProps {
  to: SceneType
  transition?: 'fade' | 'slide' | 'zoom' | 'glitch' | 'none'
  duration?: number
  data?: Record<string, unknown>
  className?: string
  children: ReactNode
  disabled?: boolean
  onClick?: () => void
}

/**
 * 场景切换按钮组件
 */
export function SceneSwitchButton({
  to,
  transition = 'fade',
  duration = 500,
  data,
  className,
  children,
  disabled,
  onClick,
}: SceneSwitchButtonProps) {
  const switchScene = useSceneStore(state => state.switchScene)
  const isTransitioning = useSceneStore(state => state.transitionState !== 'idle')
  
  const handleClick = async () => {
    if (disabled || isTransitioning) return
    
    onClick?.()
    await switchScene(to, { transition, duration, data })
  }
  
  return (
    <button
      className={className}
      onClick={handleClick}
      disabled={disabled || isTransitioning}
    >
      {children}
    </button>
  )
}

// ============ 返回按钮 ============

interface BackButtonProps {
  transition?: 'fade' | 'slide' | 'zoom' | 'glitch' | 'none'
  className?: string
  children?: ReactNode
  fallbackScene?: SceneType
}

/**
 * 返回上一场景按钮
 */
export function BackButton({
  transition = 'fade',
  className,
  children = '返回',
  fallbackScene = 'menu',
}: BackButtonProps) {
  const goBack = useSceneStore(state => state.goBack)
  const switchScene = useSceneStore(state => state.switchScene)
  const history = useSceneStore(state => state.history)
  const isTransitioning = useSceneStore(state => state.transitionState !== 'idle')
  
  const handleClick = async () => {
    if (isTransitioning) return
    
    const success = await goBack({ transition })
    
    // 如果没有历史，跳转到 fallback 场景
    if (!success && fallbackScene) {
      await switchScene(fallbackScene, { transition })
    }
  }
  
  return (
    <button
      className={className}
      onClick={handleClick}
      disabled={isTransitioning || (history.length === 0 && !fallbackScene)}
    >
      {children}
    </button>
  )
}
