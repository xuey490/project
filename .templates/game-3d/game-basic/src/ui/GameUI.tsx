import { motion, AnimatePresence } from 'framer-motion'
import { useGameStore, GameState } from '@/engine'
import { useAudioStore, useGameSound } from '@/audio'
import { useSceneSwitch } from '@/scenes'
import { useSaveSystem } from '@/storage'
import {
  Button,
  Switch,
  Slider,
  Progress,
  Dialog,
  DialogContent,
  DialogTitle,
  Separator,
  TooltipProvider,
} from '@/components'
import { SaveManager, QuickSaveButtons } from './SaveManager'
import { useState, useEffect } from 'react'
import { cn } from '@/utils'
import {
  Play,
  Pause,
  Settings,
  RotateCcw,
  Volume2,
  Music,
  Monitor,
  Gamepad2,
  MoveUp,
  Heart,
  Star,
  Zap,
  Skull,
  ChevronRight,
  Layers,
  ArrowLeft,
  Save,
} from 'lucide-react'

// ============================================
// 赛博朋克血条
// ============================================
interface HealthBarProps {
  current: number
  max: number
}

function HealthBar({ current, max }: HealthBarProps) {
  const percentage = (current / max) * 100
  const isLow = percentage <= 30
  const isCritical = percentage <= 15

  const variant = isCritical ? 'danger' : isLow ? 'warning' : 'default'

  return (
    <div className="w-56">
      <div className="flex justify-between items-center mb-1">
        <span className="text-label flex items-center gap-1">
          <Heart className="w-3 h-3 text-cyber-pink" /> HP
        </span>
        <span
          className={cn(
            'text-xs font-cyber tracking-wider',
            isCritical ? 'text-cyber-pink animate-flicker' : 'text-cyber-yellow'
          )}
        >
          {current}/{max}
        </span>
      </div>

      <div className="hud-bar">
        <div className="hud-bar-glow" />
        <Progress value={percentage} variant={variant} animated={isCritical} />
        <div className="hud-corner-tl" />
        <div className="hud-corner-br" />
      </div>
    </div>
  )
}

// ============================================
// 赛博朋克分数显示
// ============================================
function ScoreDisplay({ score }: { score: number }) {
  return (
    <motion.div
      className="relative"
      initial={{ scale: 1 }}
      animate={{ scale: [1, 1.05, 1] }}
      key={score}
      transition={{ duration: 0.2 }}
    >
      <div className="glow-pink" />

      <div className="hud-panel clip-corner">
        <div className="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-cyber-pink to-transparent" />
        <div className="absolute bottom-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-cyber-blue to-transparent" />

        <div className="flex items-center gap-3">
          <Star className="w-5 h-5 text-cyber-yellow fill-cyber-yellow" />
          <div>
            <div className="text-[10px] text-muted tracking-[0.3em] uppercase">
              Score
            </div>
            <div className="text-2xl text-value-pink">
              {score.toString().padStart(6, '0')}
            </div>
          </div>
        </div>
      </div>
    </motion.div>
  )
}

// ============================================
// FPS 计数器
// ============================================
function FPSCounter() {
  const deltaTime = useGameStore((state) => state.deltaTime)
  const fps = deltaTime > 0 ? Math.round(1 / deltaTime) : 0

  return (
    <div className="px-3 py-1 bg-cyber-darker/80 border border-cyber-blue/30 text-xs font-cyber text-cyber-blue tabular-nums flex items-center gap-2">
      <Monitor className="w-3 h-3 text-cyber-pink" /> FPS: {fps}
    </div>
  )
}

// ============================================
// 赛博朋克按钮
// ============================================
interface CyberButtonProps {
  children: React.ReactNode
  onClick?: () => void
  variant?: 'primary' | 'secondary' | 'ghost'
  icon?: React.ReactNode
  className?: string
}

const MotionButton = motion(Button)

function CyberButton({
  children,
  onClick,
  variant = 'primary',
  icon,
  className = '',
}: CyberButtonProps) {
  const isPrimary = variant === 'primary'
  const isGhost = variant === 'ghost'

  return (
    <MotionButton
      className={cn('btn group', className)}
      onClick={onClick}
      whileHover={{ scale: 1.02 }}
      whileTap={{ scale: 0.98 }}
    >
      {!isGhost && (
        <div
          className={cn(
            isPrimary ? 'btn-glow-pink' : 'btn-glow-blue'
          )}
        />
      )}

      <div
        className={cn(
          'relative flex items-center justify-center gap-2 clip-corner',
          isGhost
            ? 'btn-ghost'
            : isPrimary
            ? 'btn-primary'
            : 'btn-secondary'
        )}
      >
        <span
          className={cn(
            'btn-corner',
            isGhost
              ? 'btn-corner-tl-purple'
              : isPrimary
              ? 'btn-corner-tl-pink'
              : 'btn-corner-tl-blue'
          )}
        />
        <span
          className={cn(
            'btn-corner',
            isGhost
              ? 'btn-corner-br-purple'
              : isPrimary
              ? 'btn-corner-br-pink'
              : 'btn-corner-br-blue'
          )}
        />

        {icon && <span className="w-5 h-5">{icon}</span>}

        <span className="relative z-10">{children}</span>
      </div>
    </MotionButton>
  )
}

// ============================================
// 设置项组件
// ============================================
interface SettingItemProps {
  label: string
  icon?: React.ReactNode
  children: React.ReactNode
}

function SettingItem({ label, icon, children }: SettingItemProps) {
  return (
    <div className="flex items-center justify-between py-2">
      <span className="text-sm font-cyber text-cyber-blue tracking-wider flex items-center gap-2">
        {icon}
        {label}
      </span>
      {children}
    </div>
  )
}

// ============================================
// 设置对话框
// ============================================
function SettingsDialog({
  open,
  onOpenChange,
}: {
  open: boolean
  onOpenChange: (open: boolean) => void
}) {
  const settings = useGameStore((state) => state.settings)
  const updateSettings = useGameStore((state) => state.updateSettings)
  const { musicVolume, sfxVolume, muted, setMusicVolume, setSfxVolume, toggleMute } =
    useAudioStore()

  useEffect(() => {
    updateSettings({ musicVolume, sfxVolume })
  }, [musicVolume, sfxVolume, updateSettings])

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <AnimatePresence>
        {open && (
          <DialogContent className="w-[420px]">
            <DialogTitle>
              <Settings className="w-5 h-5 text-cyber-pink" /> 系统设置
            </DialogTitle>

            <div className="space-y-6">
              {/* 音乐音量 */}
              <div>
                <div className="flex justify-between text-sm font-cyber mb-2">
                  <span className="text-cyber-blue tracking-wider flex items-center gap-2">
                    <Music className="w-4 h-4" /> 音乐音量
                  </span>
                  <span className="text-cyber-pink">
                    {Math.round(musicVolume * 100)}%
                  </span>
                </div>
                <Slider
                  value={[musicVolume]}
                  onValueChange={([v]) => setMusicVolume(v)}
                  max={1}
                  step={0.01}
                  variant="pink"
                />
              </div>

              {/* 音效音量 */}
              <div>
                <div className="flex justify-between text-sm font-cyber mb-2">
                  <span className="text-cyber-blue tracking-wider flex items-center gap-2">
                    <Volume2 className="w-4 h-4" /> 音效音量
                  </span>
                  <span className="text-cyber-pink">
                    {Math.round(sfxVolume * 100)}%
                  </span>
                </div>
                <Slider
                  value={[sfxVolume]}
                  onValueChange={([v]) => setSfxVolume(v)}
                  max={1}
                  step={0.01}
                  variant="blue"
                />
              </div>

              <Separator />

              {/* 静音开关 */}
              <SettingItem label="静音" icon={<Volume2 className="w-4 h-4" />}>
                <Switch checked={muted} onCheckedChange={toggleMute} />
              </SettingItem>

              <Separator />

              {/* 显示 FPS */}
              <SettingItem label="显示 FPS" icon={<Monitor className="w-4 h-4" />}>
                <Switch
                  checked={settings.showFPS}
                  onCheckedChange={(checked) => updateSettings({ showFPS: checked })}
                />
              </SettingItem>
            </div>
          </DialogContent>
        )}
      </AnimatePresence>
    </Dialog>
  )
}

// ============================================
// HUD 组件
// ============================================
export function HUD() {
  const stats = useGameStore((state) => state.stats)
  const settings = useGameStore((state) => state.settings)
  const { quickSave, quickLoad, currentSaveId } = useSaveSystem()

  useEffect(() => {
    const handleKeyDown = async (e: KeyboardEvent) => {
      if (e.key === 'F5') {
        e.preventDefault()
        if (currentSaveId) {
          await quickSave()
        }
      } else if (e.key === 'F9') {
        e.preventDefault()
        await quickLoad()
      }
    }

    window.addEventListener('keydown', handleKeyDown)
    return () => window.removeEventListener('keydown', handleKeyDown)
  }, [quickSave, quickLoad, currentSaveId])

  return (
    <TooltipProvider>
      <motion.div
        className="absolute top-0 left-0 right-0 p-4 flex justify-between items-start"
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        exit={{ opacity: 0, y: -20 }}
      >
        <div className="flex flex-col gap-3">
          <HealthBar current={100} max={100} />
          {settings.showFPS && <FPSCounter />}
          <QuickSaveButtons />
        </div>
        <ScoreDisplay score={stats.score} />
      </motion.div>
    </TooltipProvider>
  )
}

// ============================================
// 主菜单
// ============================================
export function MainMenu() {
  const startGame = useGameStore((state) => state.startGame)
  const [showSettings, setShowSettings] = useState(false)
  const [showLevelSelect, setShowLevelSelect] = useState(false)
  const [showSaveManager, setShowSaveManager] = useState(false)
  const { init, playGameplayBgm } = useGameSound()
  const { switchScene } = useSceneSwitch()
  const { initialize: initSaveSystem } = useSaveSystem()

  useEffect(() => {
    initSaveSystem()
  }, [initSaveSystem])

  const handleStartGame = async (sceneId: 'game' | 'level1' | 'level2' = 'game') => {
    init()
    playGameplayBgm()
    await switchScene(sceneId, { transition: 'fade', duration: 600 })
    startGame()
  }

  const handleSaveLoaded = () => {
    init()
    playGameplayBgm()
    startGame()
  }

  return (
    <motion.div
      className="absolute inset-0 flex flex-col items-center justify-center menu-bg scanline"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
    >
      {/* 网格背景 */}
      <div className="absolute inset-0 opacity-20 grid-bg" />

      <motion.div
        className="relative text-center z-10"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
      >
        {/* 标题 */}
        <div className="relative mb-2">
          <div className="flex items-center justify-center gap-4 mb-4">
            <Zap className="w-10 h-10 text-cyber-pink animate-pulse" />
            <h1
              className="title title-lg glitch-text"
              data-text="3D 游戏模板"
            >
              3D 游戏模板
            </h1>
            <Zap className="w-10 h-10 text-cyber-blue animate-pulse" />
          </div>
          <div className="line-gradient mt-2" />
        </div>

        <p className="subtitle mb-10">
          [ R3F + Drei + Rapier + Zustand + Miniplex ]
        </p>

        {/* 菜单按钮 */}
        <AnimatePresence mode="wait">
          {!showLevelSelect ? (
            <motion.div
              key="main-buttons"
              className="flex flex-col gap-4 items-center mb-12"
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
            >
              <CyberButton
                onClick={() => handleStartGame('game')}
                icon={<Play className="w-5 h-5" />}
              >
                开始游戏
              </CyberButton>
              <CyberButton
                variant="secondary"
                onClick={() => setShowSaveManager(true)}
                icon={<Save className="w-5 h-5" />}
              >
                存档管理
              </CyberButton>
              <CyberButton
                variant="secondary"
                onClick={() => setShowLevelSelect(true)}
                icon={<Layers className="w-5 h-5" />}
              >
                选择关卡
              </CyberButton>
              <CyberButton
                variant="secondary"
                onClick={() => setShowSettings(true)}
                icon={<Settings className="w-5 h-5" />}
              >
                系统设置
              </CyberButton>
            </motion.div>
          ) : (
            <motion.div
              key="level-select"
              className="flex flex-col gap-4 items-center mb-12"
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 20 }}
            >
              <CyberButton
                onClick={() => setShowLevelSelect(false)}
                variant="ghost"
                icon={<ArrowLeft className="w-5 h-5" />}
              >
                返回
              </CyberButton>
              <div className="h-2" />
              <CyberButton
                onClick={() => handleStartGame('game')}
                icon={<Play className="w-5 h-5" />}
              >
                默认场景
              </CyberButton>
              <CyberButton
                onClick={() => handleStartGame('level1')}
                variant="secondary"
                icon={<Star className="w-5 h-5" />}
              >
                关卡 1 - 基础
              </CyberButton>
              <CyberButton
                onClick={() => handleStartGame('level2')}
                variant="secondary"
                icon={<Zap className="w-5 h-5" />}
              >
                关卡 2 - 进阶
              </CyberButton>
            </motion.div>
          )}
        </AnimatePresence>

        {/* 操作指南 */}
        <motion.div
          className="relative max-w-sm mx-auto"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.4 }}
        >
          <div className="glow-blue" />
          <div className="menu-panel clip-corner-lg">
            <h3 className="menu-title">
              <Gamepad2 className="w-4 h-4" /> 操作指南
            </h3>

            <div className="space-y-3 text-sm font-cyber">
              <div className="menu-item">
                <span className="menu-item-label">
                  <ChevronRight className="w-3 h-3" /> 移动
                </span>
                <span className="menu-item-value">WASD / ↑↓←→</span>
              </div>
              <div className="menu-item">
                <span className="menu-item-label">
                  <MoveUp className="w-3 h-3" /> 跳跃
                </span>
                <span className="menu-item-value">SPACE</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="menu-item-label">
                  <Pause className="w-3 h-3" /> 暂停
                </span>
                <span className="menu-item-value">ESC</span>
              </div>
            </div>
          </div>
        </motion.div>
      </motion.div>

      <AnimatePresence>
        {showSettings && (
          <SettingsDialog open={showSettings} onOpenChange={setShowSettings} />
        )}
      </AnimatePresence>

      <SaveManager
        open={showSaveManager}
        onOpenChange={setShowSaveManager}
        onSaveLoaded={handleSaveLoaded}
      />
    </motion.div>
  )
}

// ============================================
// 暂停菜单
// ============================================
export function PauseMenu() {
  const resume = useGameStore((state) => state.resume)
  const restartGame = useGameStore((state) => state.restartGame)
  const setGameState = useGameStore((state) => state.setGameState)
  const [showSettings, setShowSettings] = useState(false)
  const { pauseBgm, resumeBgm, stopBgm } = useGameSound()

  useEffect(() => {
    pauseBgm()
    return () => resumeBgm()
  }, [pauseBgm, resumeBgm])

  const handleBackToMenu = () => {
    stopBgm()
    setGameState(GameState.MENU)
  }

  return (
    <motion.div
      className="absolute inset-0 flex flex-col items-center justify-center menu-bg"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
    >
      <motion.div
        className="text-center"
        initial={{ opacity: 0, scale: 0.9 }}
        animate={{ opacity: 1, scale: 1 }}
        exit={{ opacity: 0, scale: 0.9 }}
      >
        <div className="flex items-center justify-center gap-3 mb-8">
          <Pause className="w-8 h-8 text-cyber-blue" />
          <h2 className="title-md font-cyber font-bold text-cyber-blue tracking-widest uppercase text-neon-blue">
            系统暂停
          </h2>
          <Pause className="w-8 h-8 text-cyber-blue" />
        </div>
        <div className="flex flex-col gap-4 items-center">
          <CyberButton onClick={resume} icon={<Play className="w-5 h-5" />}>
            继续游戏
          </CyberButton>
          <CyberButton
            variant="secondary"
            onClick={() => setShowSettings(true)}
            icon={<Settings className="w-5 h-5" />}
          >
            系统设置
          </CyberButton>
          <CyberButton
            variant="secondary"
            onClick={restartGame}
            icon={<RotateCcw className="w-5 h-5" />}
          >
            重新开始
          </CyberButton>
          <CyberButton
            variant="secondary"
            onClick={handleBackToMenu}
            icon={<ArrowLeft className="w-5 h-5" />}
          >
            返回首页
          </CyberButton>
        </div>
      </motion.div>

      <AnimatePresence>
        {showSettings && (
          <SettingsDialog open={showSettings} onOpenChange={setShowSettings} />
        )}
      </AnimatePresence>
    </motion.div>
  )
}

// ============================================
// 游戏结束
// ============================================
export function GameOverMenu() {
  const stats = useGameStore((state) => state.stats)
  const restartGame = useGameStore((state) => state.restartGame)
  const { stopBgm, playFail, playGameplayBgm } = useGameSound()

  useEffect(() => {
    stopBgm()
    playFail()
  }, [stopBgm, playFail])

  const handleRestart = () => {
    playGameplayBgm()
    restartGame()
  }

  return (
    <motion.div
      className="absolute inset-0 flex flex-col items-center justify-center bg-cyber-darker/90 backdrop-blur-md"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
    >
      <motion.div
        className="text-center"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
      >
        <motion.div
          className="flex items-center justify-center gap-3 mb-6"
          initial={{ scale: 0.5 }}
          animate={{ scale: 1 }}
          transition={{ type: 'spring', damping: 10 }}
        >
          <Skull className="w-10 h-10 text-cyber-pink animate-pulse" />
          <h2 className="title-lg font-cyber font-black text-cyber-pink tracking-widest uppercase text-neon-pink animate-flicker">
            系统崩溃
          </h2>
          <Skull className="w-10 h-10 text-cyber-pink animate-pulse" />
        </motion.div>

        <motion.div
          className="relative mb-8"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.3 }}
        >
          <div className="absolute -inset-2 bg-cyber-pink/20 blur-xl" />
          <div className="panel-pink clip-corner-lg relative p-8">
            <div className="text-xs font-cyber text-muted tracking-[0.3em] uppercase mb-2 flex items-center justify-center gap-2">
              <Star className="w-3 h-3" /> Final Score <Star className="w-3 h-3" />
            </div>
            <div className="text-5xl text-value-pink">
              {stats.score.toString().padStart(6, '0')}
            </div>
          </div>
        </motion.div>

        <CyberButton onClick={handleRestart} icon={<RotateCcw className="w-5 h-5" />}>
          重新连接
        </CyberButton>
      </motion.div>
    </motion.div>
  )
}

// ============================================
// 主 UI 组件
// ============================================
export function GameUI() {
  const gameState = useGameStore((state) => state.gameState)

  return (
    <div className="fixed inset-0 z-50 pointer-events-none">
      <div className="pointer-events-auto">
        <AnimatePresence mode="wait">
          {gameState === GameState.PLAYING && <HUD key="hud" />}
          {gameState === GameState.MENU && <MainMenu key="menu" />}
          {gameState === GameState.PAUSED && <PauseMenu key="pause" />}
          {gameState === GameState.GAME_OVER && <GameOverMenu key="gameover" />}
        </AnimatePresence>
      </div>
    </div>
  )
}
