// 场景过渡动画组件
import { motion, AnimatePresence, Variants } from 'framer-motion'
import { useSceneStore, TransitionType, useTransition } from './SceneStore'
import { ReactNode } from 'react'

// ============ 过渡动画变体 ============

const fadeVariants: Variants = {
  initial: { opacity: 0 },
  animate: { opacity: 1 },
  exit: { opacity: 0 },
}

const slideVariants: Variants = {
  initial: { x: '100%', opacity: 0 },
  animate: { x: 0, opacity: 1 },
  exit: { x: '-100%', opacity: 0 },
}

const zoomVariants: Variants = {
  initial: { scale: 0.8, opacity: 0 },
  animate: { scale: 1, opacity: 1 },
  exit: { scale: 1.2, opacity: 0 },
}

const glitchVariants: Variants = {
  initial: { 
    opacity: 0,
    filter: 'blur(10px) hue-rotate(90deg)',
    transform: 'translateX(-10px) skewX(5deg)',
  },
  animate: { 
    opacity: 1,
    filter: 'blur(0px) hue-rotate(0deg)',
    transform: 'translateX(0px) skewX(0deg)',
  },
  exit: { 
    opacity: 0,
    filter: 'blur(10px) hue-rotate(-90deg)',
    transform: 'translateX(10px) skewX(-5deg)',
  },
}

const noneVariants: Variants = {
  initial: {},
  animate: {},
  exit: {},
}

// 获取过渡变体
function getTransitionVariants(type: TransitionType): Variants {
  switch (type) {
    case 'fade': return fadeVariants
    case 'slide': return slideVariants
    case 'zoom': return zoomVariants
    case 'glitch': return glitchVariants
    case 'none': return noneVariants
    default: return fadeVariants
  }
}

// ============ 过渡遮罩组件 ============

interface TransitionOverlayProps {
  className?: string
}

/**
 * 过渡遮罩
 * 在场景切换时显示全屏遮罩
 */
export function TransitionOverlay({ className }: TransitionOverlayProps) {
  const { state, type, progress } = useTransition()
  
  const isVisible = state === 'exiting' || state === 'loading'
  
  return (
    <AnimatePresence>
      {isVisible && (
        <motion.div
          className={`fixed inset-0 z-[200] pointer-events-none ${className || ''}`}
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.2 }}
        >
          {/* 根据过渡类型渲染不同效果 */}
          {type === 'fade' && <FadeOverlay progress={progress} />}
          {type === 'slide' && <SlideOverlay progress={progress} />}
          {type === 'zoom' && <ZoomOverlay progress={progress} />}
          {type === 'glitch' && <GlitchOverlay progress={progress} />}
        </motion.div>
      )}
    </AnimatePresence>
  )
}

// ============ 不同过渡效果的遮罩 ============

function FadeOverlay({ progress }: { progress: number }) {
  return (
    <div 
      className="absolute inset-0 bg-cyber-darker"
      style={{ opacity: progress }}
    />
  )
}

function SlideOverlay({ progress }: { progress: number }) {
  return (
    <div 
      className="absolute inset-0 bg-gradient-to-r from-cyber-darker via-cyber-purple/50 to-cyber-darker"
      style={{ 
        transform: `translateX(${(1 - progress) * -100}%)`,
      }}
    />
  )
}

function ZoomOverlay({ progress }: { progress: number }) {
  return (
    <div 
      className="absolute inset-0 bg-cyber-darker"
      style={{ 
        opacity: progress,
        transform: `scale(${1 + (1 - progress) * 0.2})`,
      }}
    />
  )
}

function GlitchOverlay({ progress }: { progress: number }) {
  return (
    <div className="absolute inset-0 overflow-hidden">
      {/* 主遮罩 */}
      <div 
        className="absolute inset-0 bg-cyber-darker"
        style={{ opacity: progress }}
      />
      
      {/* 故障线条 */}
      {progress > 0 && progress < 1 && (
        <>
          {[...Array(5)].map((_, i) => (
            <motion.div
              key={i}
              className="absolute h-1 bg-cyber-pink"
              style={{
                top: `${20 + i * 15}%`,
                left: 0,
                right: 0,
              }}
              animate={{
                x: ['-100%', '100%'],
                opacity: [0, 1, 0],
              }}
              transition={{
                duration: 0.3,
                delay: i * 0.05,
                repeat: 1,
              }}
            />
          ))}
          
          {/* RGB 分离效果 */}
          <div 
            className="absolute inset-0 mix-blend-screen"
            style={{
              background: `linear-gradient(
                90deg,
                rgba(255, 0, 0, ${progress * 0.3}) 0%,
                transparent 30%,
                transparent 70%,
                rgba(0, 255, 255, ${progress * 0.3}) 100%
              )`,
            }}
          />
        </>
      )}
    </div>
  )
}

// ============ 场景容器包装器 ============

interface SceneTransitionWrapperProps {
  children: ReactNode
  className?: string
}

/**
 * 场景过渡包装器
 * 包裹场景内容，提供进入/退出动画
 */
export function SceneTransitionWrapper({ 
  children, 
  className 
}: SceneTransitionWrapperProps) {
  const currentScene = useSceneStore(state => state.currentScene)
  const transitionType = useSceneStore(state => state.transitionType)
  const transitionDuration = useSceneStore(state => state.transitionDuration)
  
  const variants = getTransitionVariants(transitionType)
  
  return (
    <AnimatePresence mode="wait">
      <motion.div
        key={currentScene}
        className={className}
        variants={variants}
        initial="initial"
        animate="animate"
        exit="exit"
        transition={{ 
          duration: transitionDuration / 1000,
          ease: [0.4, 0, 0.2, 1],
        }}
      >
        {children}
      </motion.div>
    </AnimatePresence>
  )
}

// ============ 3D 场景过渡效果 ============

interface Scene3DTransitionProps {
  children: ReactNode
}

/**
 * 3D 场景过渡效果
 * 用于 Canvas 内的 3D 场景过渡
 */
export function Scene3DTransition({ children }: Scene3DTransitionProps) {
  const { state, progress } = useTransition()
  
  // 计算透明度和缩放
  const opacity = state === 'exiting' 
    ? 1 - progress 
    : state === 'entering' 
      ? progress 
      : 1
  
  const scale = state === 'exiting'
    ? 1 - progress * 0.1
    : state === 'entering'
      ? 0.9 + progress * 0.1
      : 1
  
  return (
    <group 
      scale={[scale, scale, scale]}
      // 注意：Three.js group 不支持 opacity，需要在材质中处理
      // 这里仅作为示例结构
      visible={opacity > 0}
    >
      {children}
    </group>
  )
}

// ============ 加载指示器 ============

interface SceneLoadingIndicatorProps {
  className?: string
}

/**
 * 场景加载指示器
 * 在场景切换时显示加载状态
 */
export function SceneLoadingIndicator({ className }: SceneLoadingIndicatorProps) {
  const { state } = useTransition()
  const isLoading = state === 'loading'
  
  return (
    <AnimatePresence>
      {isLoading && (
        <motion.div
          className={`fixed inset-0 z-[201] flex items-center justify-center ${className || ''}`}
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
        >
          <motion.div
            className="w-16 h-16 border-4 border-cyber-blue/30 border-t-cyber-pink rounded-full"
            animate={{ rotate: 360 }}
            transition={{ duration: 1, repeat: Infinity, ease: 'linear' }}
          />
        </motion.div>
      )}
    </AnimatePresence>
  )
}
