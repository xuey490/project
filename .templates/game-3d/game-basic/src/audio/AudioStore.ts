// 音频状态 Store (Zustand) - 带持久化
import { create } from 'zustand'
import { subscribeWithSelector, persist, createJSONStorage } from 'zustand/middleware'

interface AudioState {
  // 音量设置
  masterVolume: number
  musicVolume: number
  sfxVolume: number
  muted: boolean
  
  // 状态（不持久化）
  initialized: boolean
  bgmPlaying: boolean
  
  // Actions
  setMasterVolume: (v: number) => void
  setMusicVolume: (v: number) => void
  setSfxVolume: (v: number) => void
  toggleMute: () => void
  setInitialized: (v: boolean) => void
  setBgmPlaying: (v: boolean) => void
  
  // 计算有效音量
  getEffectiveMusicVolume: () => number
  getEffectiveSfxVolume: () => number
}

export const useAudioStore = create<AudioState>()(
  subscribeWithSelector(
    persist(
      (set, get) => ({
        masterVolume: 1.0,
        musicVolume: 0.5,
        sfxVolume: 1.0,
        muted: false,
        initialized: false,
        bgmPlaying: false,
        
        setMasterVolume: (v) => set({ masterVolume: Math.max(0, Math.min(1, v)) }),
        setMusicVolume: (v) => set({ musicVolume: Math.max(0, Math.min(1, v)) }),
        setSfxVolume: (v) => set({ sfxVolume: Math.max(0, Math.min(1, v)) }),
        toggleMute: () => set((state) => ({ muted: !state.muted })),
        setInitialized: (v) => set({ initialized: v }),
        setBgmPlaying: (v) => set({ bgmPlaying: v }),
        
        getEffectiveMusicVolume: () => {
          const state = get()
          return state.muted ? 0 : state.masterVolume * state.musicVolume
        },
        getEffectiveSfxVolume: () => {
          const state = get()
          return state.muted ? 0 : state.masterVolume * state.sfxVolume
        },
      }),
      {
        name: 'game3d_audio',
        storage: createJSONStorage(() => localStorage),
        // 只持久化音量设置，不持久化运行时状态
        partialize: (state) => ({
          masterVolume: state.masterVolume,
          musicVolume: state.musicVolume,
          sfxVolume: state.sfxVolume,
          muted: state.muted,
        }),
      }
    )
  )
)
