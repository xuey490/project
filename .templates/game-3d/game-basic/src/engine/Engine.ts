import { create } from 'zustand'
import { subscribeWithSelector, persist, createJSONStorage } from 'zustand/middleware'

// 游戏状态枚举
export enum GameState {
  MENU = 'menu',
  PLAYING = 'playing',
  PAUSED = 'paused',
  GAME_OVER = 'gameover',
  LOADING = 'loading',
}

// 游戏设置
export interface GameSettings {
  musicVolume: number
  sfxVolume: number
  mouseSensitivity: number
  invertY: boolean
  showFPS: boolean
}

// 游戏统计
export interface GameStats {
  score: number
  kills: number
  deaths: number
  playtime: number
}

// Store 接口
interface GameStore {
  // 游戏状态
  gameState: GameState
  setGameState: (state: GameState) => void

  // 加载状态
  isLoading: boolean
  loadingProgress: number
  setLoading: (loading: boolean, progress?: number) => void

  // 游戏设置
  settings: GameSettings
  updateSettings: (settings: Partial<GameSettings>) => void

  // 游戏统计
  stats: GameStats
  updateStats: (stats: Partial<GameStats>) => void
  resetStats: () => void

  // 时间相关
  deltaTime: number
  elapsedTime: number
  setTime: (delta: number, elapsed: number) => void

  // 暂停/恢复
  isPaused: boolean
  togglePause: () => void
  pause: () => void
  resume: () => void

  // 游戏流程控制
  startGame: () => void
  endGame: () => void
  restartGame: () => void
}

// 默认设置
const defaultSettings: GameSettings = {
  musicVolume: 0.7,
  sfxVolume: 1.0,
  mouseSensitivity: 1.0,
  invertY: false,
  showFPS: true,
}

// 默认统计
const defaultStats: GameStats = {
  score: 0,
  kills: 0,
  deaths: 0,
  playtime: 0,
}

// 创建 Store - 设置部分使用 persist 中间件持久化
export const useGameStore = create<GameStore>()(
  subscribeWithSelector(
    persist(
      (set, get) => ({
        // 初始状态
        gameState: GameState.MENU,
        isLoading: false,
        loadingProgress: 0,
        settings: defaultSettings,
        stats: defaultStats,
        deltaTime: 0,
        elapsedTime: 0,
        isPaused: false,

        // 设置游戏状态
        setGameState: (state) => set({ gameState: state }),

        // 设置加载状态
        setLoading: (loading, progress = 0) =>
          set({ isLoading: loading, loadingProgress: progress }),

        // 更新设置
        updateSettings: (newSettings) =>
          set((state) => ({
            settings: { ...state.settings, ...newSettings },
          })),

        // 更新统计
        updateStats: (newStats) =>
          set((state) => ({
            stats: { ...state.stats, ...newStats },
          })),

        // 重置统计
        resetStats: () => set({ stats: defaultStats }),

        // 设置时间
        setTime: (delta, elapsed) =>
          set({ deltaTime: delta, elapsedTime: elapsed }),

        // 切换暂停
        togglePause: () => {
          const { isPaused, gameState } = get()
          if (gameState === GameState.PLAYING || gameState === GameState.PAUSED) {
            set({
              isPaused: !isPaused,
              gameState: isPaused ? GameState.PLAYING : GameState.PAUSED,
            })
          }
        },

        pause: () =>
          set({
            isPaused: true,
            gameState: GameState.PAUSED,
          }),

        resume: () =>
          set({
            isPaused: false,
            gameState: GameState.PLAYING,
          }),

        // 开始游戏
        startGame: () => {
          set({
            gameState: GameState.PLAYING,
            isPaused: false,
            stats: defaultStats,
          })
        },

        // 结束游戏
        endGame: () => {
          set({
            gameState: GameState.GAME_OVER,
            isPaused: false,
          })
        },

        // 重新开始
        restartGame: () => {
          set({
            gameState: GameState.PLAYING,
            isPaused: false,
            stats: defaultStats,
          })
        },
      }),
      {
        name: 'game3d_settings',
        storage: createJSONStorage(() => localStorage),
        // 只持久化设置，不持久化游戏状态
        partialize: (state) => ({
          settings: state.settings,
        }),
      }
    )
  )
)

// 选择器
export const selectGameState = (state: GameStore) => state.gameState
export const selectSettings = (state: GameStore) => state.settings
export const selectStats = (state: GameStore) => state.stats
export const selectIsPaused = (state: GameStore) => state.isPaused
