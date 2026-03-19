// 加载界面组件
import { motion } from 'framer-motion'
import { Loader2, Box, ImageIcon, Music, AlertTriangle } from 'lucide-react'
import { useAssetStore } from '@/assets'
import { useState, useEffect } from 'react'
import { Progress } from '@/components'

// ============================================
// 模拟进度 Hook
// ============================================

function useSimulatedProgress() {
  const { progress, isLoading } = useAssetStore()
  const [simProgress, setSimProgress] = useState(0)

  const hasRealProgress = progress.total > 0 || isLoading

  useEffect(() => {
    if (hasRealProgress) return

    const interval = setInterval(() => {
      setSimProgress((prev) => {
        if (prev >= 100) return 100
        if (prev < 30) return prev + 8
        if (prev < 70) return prev + 4
        if (prev < 95) return prev + 1
        return prev + 0.5
      })
    }, 50)

    return () => clearInterval(interval)
  }, [hasRealProgress])

  return hasRealProgress ? progress.percentage : Math.round(simProgress)
}

// ============================================
// 加载进度条
// ============================================

function LoadingProgress() {
  const percentage = useSimulatedProgress()
  const { progress } = useAssetStore()

  return (
    <div className="loading-progress">
      <div className="loading-progress-header">
        <span className="text-label">加载中</span>
        <span className="text-value-pink loading-progress-percentage">{percentage}%</span>
      </div>

      <div className="loading-progress-bar">
        <div className="glow-gradient" />

        <div className="loading-progress-bar-inner">
          <Progress value={percentage} className="h-3" />

          {/* 扫描线效果 */}
          <motion.div
            className="loading-progress-shine"
            animate={{ x: ['-100%', '100%'] }}
            transition={{ duration: 1.5, repeat: Infinity, ease: 'linear' }}
          />
        </div>
      </div>

      {/* 当前加载资产 */}
      <div className="loading-progress-asset text-muted font-cyber">
        {progress.currentAsset ? (
          <span className="loading-progress-asset-inner">
            <Loader2 className="loader-icon-sm animate-spin" />
            {progress.currentAsset}
          </span>
        ) : (
          <span className="loading-progress-asset-inner">
            <Loader2 className="loader-icon-sm animate-spin" />
            初始化系统...
          </span>
        )}
      </div>
    </div>
  )
}

// ============================================
// 加载统计
// ============================================

function LoadingStats() {
  const { progress } = useAssetStore()

  return (
    <div className="loading-stats font-cyber">
      <div className="loading-stats-item loading-stats-item-primary">
        <Box className="loader-icon-md" />
        <span>
          模型: {progress.loaded}/{progress.total}
        </span>
      </div>
      <div className="loading-stats-item loading-stats-item-secondary">
        <ImageIcon className="loader-icon-md" />
        <span>纹理: --</span>
      </div>
      <div className="loading-stats-item loading-stats-item-tertiary">
        <Music className="loader-icon-md" />
        <span>音频: --</span>
      </div>
    </div>
  )
}

// ============================================
// 错误列表
// ============================================

function LoadingErrors() {
  const { errors } = useAssetStore()

  if (errors.length === 0) return null

  return (
    <motion.div
      className="loading-errors notification-error"
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
    >
      <div className="loading-errors-title font-cyber">
        <AlertTriangle className="loader-icon-md" />
        加载警告
      </div>
      <ul className="loading-errors-list">
        {errors.slice(0, 3).map((error, i) => (
          <li key={i} className="truncate">
            {error}
          </li>
        ))}
        {errors.length > 3 && (
          <li className="loading-errors-more">还有 {errors.length - 3} 个错误...</li>
        )}
      </ul>
    </motion.div>
  )
}

// ============================================
// 主加载界面
// ============================================

export function LoadingScreen() {
  return (
    <motion.div
      className="loading-screen"
      initial={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      transition={{ duration: 0.5 }}
    >
      {/* 背景网格 */}
      <div className="loading-screen-bg grid-bg-sm" />

      {/* 扫描线 */}
      <div className="loading-screen-scanline scanline" />

      {/* 标题 */}
      <motion.div
        className="loading-screen-header"
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
      >
        <h1 className="title title-md mb-2">
          LOADING
        </h1>
        <p className="subtitle">
          初始化游戏资源
        </p>
      </motion.div>

      {/* 加载动画 */}
      <motion.div
        className="loading-screen-spinner"
        animate={{ rotate: 360 }}
        transition={{ duration: 3, repeat: Infinity, ease: 'linear' }}
      >
        <div className="loading-screen-spinner-box">
          {/* 外圈 */}
          <div className="absolute inset-0 loader-ring" />
          {/* 内圈动画 */}
          <motion.div
            className="absolute inset-2 loader-ring-inner"
            animate={{ rotate: -360 }}
            transition={{ duration: 1.5, repeat: Infinity, ease: 'linear' }}
          />
          {/* 中心点 */}
          <div className="absolute inset-0 flex items-center justify-center">
            <div className="loader-dot" />
          </div>
        </div>
      </motion.div>

      {/* 进度条 */}
      <LoadingProgress />

      {/* 统计 */}
      <motion.div
        className="mt-6"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 0.5 }}
      >
        <LoadingStats />
      </motion.div>

      {/* 错误 */}
      <LoadingErrors />

      {/* 底部提示 */}
      <motion.div
        className="loading-screen-footer text-hint"
        animate={{ opacity: [0.4, 0.8, 0.4] }}
        transition={{ duration: 2, repeat: Infinity }}
      >
        请稍候，正在加载游戏资源...
      </motion.div>
    </motion.div>
  )
}

// ============================================
// 简化版加载指示器
// ============================================

export function InlineLoader({ text = '加载中...' }: { text?: string }) {
  return (
    <div className="inline-loader text-muted font-cyber">
      <Loader2 className="loader-icon-md animate-spin" />
      <span>{text}</span>
    </div>
  )
}

// ============================================
// Canvas 内加载占位
// ============================================

export function CanvasLoader() {
  return (
    <mesh>
      <boxGeometry args={[1, 1, 1]} />
      <meshBasicMaterial color="#05d9e8" wireframe />
    </mesh>
  )
}
