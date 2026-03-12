// UI 音效与背景乐管理器
// 使用程序化合成音效 + Howler.js (用于 BGM)
import { Howl, Howler } from 'howler'
import { useAudioStore } from './AudioStore'
import { synthSounds } from './SynthSounds'

// BGM 类型
type BgmName = 'menu' | 'gameplay' | 'boss'

// BGM 配置（如果有音频文件）
const BGM_CONFIG: Record<BgmName, { src: string; volume?: number }> = {
  menu: { src: '/music/menu.mp3', volume: 0.6 },
  gameplay: { src: '/music/gameplay.mp3', volume: 0.5 },
  boss: { src: '/music/boss.mp3', volume: 0.7 },
}

class SoundManager {
  private bgm: Map<BgmName, Howl> = new Map()
  private currentBgm: BgmName | null = null
  private initialized = false
  private initListenerAdded = false
  
  constructor() {
    // 在页面任意位置点击时初始化音频
    this.setupInitListener()
  }
  
  // 监听用户首次交互以初始化音频
  private setupInitListener() {
    if (this.initListenerAdded || typeof window === 'undefined') return
    this.initListenerAdded = true
    
    const initOnInteraction = () => {
      if (!this.initialized) {
        this.init()
      }
      // 初始化后移除监听器
      window.removeEventListener('click', initOnInteraction)
      window.removeEventListener('keydown', initOnInteraction)
      window.removeEventListener('touchstart', initOnInteraction)
    }
    
    window.addEventListener('click', initOnInteraction, { once: false })
    window.addEventListener('keydown', initOnInteraction, { once: false })
    window.addEventListener('touchstart', initOnInteraction, { once: false })
  }
  
  // 初始化音频系统（需要用户交互后调用）
  async init() {
    if (this.initialized) return
    
    // 恢复 AudioContext（解决浏览器自动播放策略）
    if (Howler.ctx?.state === 'suspended') {
      Howler.ctx.resume()
    }
    
    // 初始化合成器音效（预创建对象池）
    await synthSounds.init()
    
    // 预加载 BGM（如果文件存在）
    // 注意：BGM 文件是可选的，如果不存在不会报错
    Object.entries(BGM_CONFIG).forEach(([name, config]) => {
      const bgm = new Howl({
        src: [config.src],
        volume: config.volume ?? 0.5,
        html5: true,
        loop: true,
        preload: true,
        onloaderror: () => {
          console.warn(`BGM "${name}" not found at ${config.src}, skipping...`)
        }
      })
      this.bgm.set(name as BgmName, bgm)
    })
    
    // 订阅音量变化
    this.setupVolumeSubscription()
    
    this.initialized = true
    useAudioStore.getState().setInitialized(true)
  }
  
  // 订阅 Store 音量变化
  private setupVolumeSubscription() {
    const store = useAudioStore
    
    // 监听音效音量变化
    store.subscribe(
      (state) => state.getEffectiveSfxVolume(),
      (volume) => {
        synthSounds.setVolume(volume)
      }
    )
    
    // 监听音乐音量变化
    store.subscribe(
      (state) => state.getEffectiveMusicVolume(),
      (volume) => {
        if (this.currentBgm) {
          const bgm = this.bgm.get(this.currentBgm)
          bgm?.volume(volume * (BGM_CONFIG[this.currentBgm]?.volume ?? 1))
        }
      }
    )
    
    // 监听静音状态
    store.subscribe(
      (state) => state.muted,
      (muted) => {
        Howler.mute(muted)
        synthSounds.setVolume(muted ? 0 : useAudioStore.getState().getEffectiveSfxVolume())
      }
    )
  }
  
  // === 程序化音效 (即时合成，无需文件) ===
  
  playJump() {
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playJump(store.getEffectiveSfxVolume())
  }
  
  playLand() {
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playLand(store.getEffectiveSfxVolume())
  }
  
  playCollect() {
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playCollect(store.getEffectiveSfxVolume())
  }
  
  playPowerup() {
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playPowerup(store.getEffectiveSfxVolume())
  }
  
  playClick() {
    if (!this.initialized) return
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playClick(store.getEffectiveSfxVolume())
  }
  
  playHover() {
    if (!this.initialized) return
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playHover(store.getEffectiveSfxVolume())
  }
  
  playHit() {
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playHit(store.getEffectiveSfxVolume())
  }
  
  playSuccess() {
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playSuccess(store.getEffectiveSfxVolume())
  }
  
  playFail() {
    const store = useAudioStore.getState()
    if (store.muted) return
    synthSounds.playFail(store.getEffectiveSfxVolume())
  }
  
  // === 背景音乐 (需要音频文件) ===
  
  playBgm(name: BgmName, fadeIn = 1000) {
    const store = useAudioStore.getState()
    
    // 如果同一曲目已在播放，跳过
    if (this.currentBgm === name) {
      const bgm = this.bgm.get(name)
      if (bgm?.playing()) return
    }
    
    // 停止当前 BGM
    if (this.currentBgm) {
      this.stopBgm(500)
    }
    
    const bgm = this.bgm.get(name)
    if (bgm) {
      const baseVolume = BGM_CONFIG[name]?.volume ?? 0.5
      const targetVolume = store.getEffectiveMusicVolume() * baseVolume
      
      bgm.volume(0)
      bgm.play()
      bgm.fade(0, targetVolume, fadeIn)
      
      this.currentBgm = name
      store.setBgmPlaying(true)
    }
  }
  
  stopBgm(fadeOut = 500) {
    if (!this.currentBgm) return
    
    const bgm = this.bgm.get(this.currentBgm)
    if (bgm) {
      bgm.fade(bgm.volume(), 0, fadeOut)
      bgm.once('fade', () => {
        bgm.stop()
      })
    }
    
    this.currentBgm = null
    useAudioStore.getState().setBgmPlaying(false)
  }
  
  pauseBgm() {
    if (this.currentBgm) {
      this.bgm.get(this.currentBgm)?.pause()
    }
  }
  
  resumeBgm() {
    if (this.currentBgm) {
      this.bgm.get(this.currentBgm)?.play()
    }
  }
  
  isInitialized() {
    return this.initialized
  }
  
  getCurrentBgm() {
    return this.currentBgm
  }
}

// 单例导出
export const soundManager = new SoundManager()

// React Hook
export function useGameSound() {
  return {
    // 初始化（用户首次交互时调用）
    init: () => soundManager.init(),
    
    // 程序化合成音效 (无需文件)
    playClick: () => soundManager.playClick(),
    playHover: () => soundManager.playHover(),
    playCollect: () => soundManager.playCollect(),
    playJump: () => soundManager.playJump(),
    playLand: () => soundManager.playLand(),
    playHit: () => soundManager.playHit(),
    playPowerup: () => soundManager.playPowerup(),
    playSuccess: () => soundManager.playSuccess(),
    playFail: () => soundManager.playFail(),
    
    // 背景音乐 (需要文件)
    playMenuBgm: () => soundManager.playBgm('menu'),
    playGameplayBgm: () => soundManager.playBgm('gameplay'),
    playBossBgm: () => soundManager.playBgm('boss'),
    stopBgm: () => soundManager.stopBgm(),
    pauseBgm: () => soundManager.pauseBgm(),
    resumeBgm: () => soundManager.resumeBgm(),
    
    // 状态
    isInitialized: () => soundManager.isInitialized(),
  }
}
