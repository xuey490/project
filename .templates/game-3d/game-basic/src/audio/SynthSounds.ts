// 程序化合成音效 - 使用 Tone.js 生成赛博朋克风格音效
// 优化版本：使用对象池和预创建合成器

import * as Tone from 'tone'

// 合成器池配置
interface SynthPool<T extends Tone.ToneAudioNode> {
  synths: T[]
  index: number
  create: () => T
}

class SynthSoundEngine {
  private initialized = false
  private masterVolume: Tone.Volume | null = null
  
  // 预创建的合成器池
  private jumpSynths: SynthPool<Tone.Synth> | null = null
  private clickSynths: SynthPool<Tone.Synth> | null = null
  private collectSynths: SynthPool<Tone.PolySynth> | null = null
  private noiseSynths: SynthPool<Tone.NoiseSynth> | null = null
  private generalSynths: SynthPool<Tone.Synth> | null = null
  private polySynths: SynthPool<Tone.PolySynth> | null = null
  
  // 脚步声节流
  private lastFootstepTime = 0
  private footstepCooldown = 150 // ms

  async init() {
    if (this.initialized) return
    
    await Tone.start()
    this.masterVolume = new Tone.Volume(0).toDestination()
    
    // 预创建合成器池
    this.jumpSynths = this.createPool(3, () => 
      new Tone.Synth({
        oscillator: { type: 'square' },
        envelope: { attack: 0.01, decay: 0.1, sustain: 0, release: 0.05 },
      }).connect(this.masterVolume!)
    )
    
    this.clickSynths = this.createPool(4, () =>
      new Tone.Synth({
        oscillator: { type: 'square' },
        envelope: { attack: 0.001, decay: 0.05, sustain: 0, release: 0.01 },
      }).connect(this.masterVolume!)
    )
    
    this.collectSynths = this.createPool(4, () =>
      new Tone.PolySynth(Tone.Synth, {
        oscillator: { type: 'sine' },
        envelope: { attack: 0.01, decay: 0.2, sustain: 0, release: 0.1 },
      }).connect(this.masterVolume!)
    )
    
    this.noiseSynths = this.createPool(4, () =>
      new Tone.NoiseSynth({
        noise: { type: 'brown' },
        envelope: { attack: 0.005, decay: 0.05, sustain: 0, release: 0.02 },
      }).connect(this.masterVolume!)
    )
    
    this.generalSynths = this.createPool(4, () =>
      new Tone.Synth({
        oscillator: { type: 'sine' },
        envelope: { attack: 0.01, decay: 0.2, sustain: 0, release: 0.1 },
      }).connect(this.masterVolume!)
    )
    
    this.polySynths = this.createPool(3, () =>
      new Tone.PolySynth(Tone.Synth, {
        oscillator: { type: 'sine' },
        envelope: { attack: 0.05, decay: 0.3, sustain: 0, release: 0.1 },
      }).connect(this.masterVolume!)
    )
    
    this.initialized = true
  }

  private createPool<T extends Tone.ToneAudioNode>(
    size: number, 
    create: () => T
  ): SynthPool<T> {
    return {
      synths: Array.from({ length: size }, create),
      index: 0,
      create,
    }
  }

  private getFromPool<T extends Tone.ToneAudioNode>(pool: SynthPool<T>): T {
    const synth = pool.synths[pool.index]
    pool.index = (pool.index + 1) % pool.synths.length
    return synth
  }

  setVolume(volume: number) {
    if (this.masterVolume) {
      this.masterVolume.volume.value = volume <= 0 ? -Infinity : Tone.gainToDb(volume)
    }
  }

  // 跳跃音效 - 上升的电子音
  playJump(volume = 0.5) {
    if (!this.initialized || !this.jumpSynths) return

    const synth = this.getFromPool(this.jumpSynths)
    synth.volume.value = Tone.gainToDb(volume * 0.3)

    const now = Tone.now()
    synth.triggerAttack('G3', now)
    synth.frequency.exponentialRampTo('D5', 0.1, now)
    synth.triggerRelease(now + 0.12)
  }

  // 落地音效 - 低沉的冲击声
  playLand(volume = 0.4) {
    if (!this.initialized || !this.generalSynths || !this.noiseSynths) return

    const synth = this.getFromPool(this.generalSynths)
    synth.set({
      oscillator: { type: 'sine' },
      envelope: { attack: 0.005, decay: 0.1, sustain: 0, release: 0.02 },
    })
    synth.volume.value = Tone.gainToDb(volume * 0.5)

    const now = Tone.now()
    synth.triggerAttack('D2', now)
    synth.frequency.exponentialRampTo('G1', 0.1, now)
    synth.triggerRelease(now + 0.1)

    // 噪音层
    const noise = this.getFromPool(this.noiseSynths)
    noise.volume.value = Tone.gainToDb(volume * 0.15)
    noise.triggerAttackRelease('16n')
  }

  // 收集金币音效 - 明亮的叮当声
  playCollect(volume = 0.5) {
    if (!this.initialized || !this.collectSynths) return

    const synth = this.getFromPool(this.collectSynths)
    synth.volume.value = Tone.gainToDb(volume * 0.3)

    const now = Tone.now()
    synth.triggerAttackRelease('A5', '16n', now)
    synth.triggerAttackRelease('E6', '8n', now + 0.05)
  }

  // 能量提升音效 - 上升的合成器音
  playPowerup(volume = 0.6) {
    if (!this.initialized || !this.generalSynths) return

    const synth = this.getFromPool(this.generalSynths)
    synth.set({
      oscillator: { type: 'sawtooth' },
      envelope: { attack: 0.02, decay: 0.15, sustain: 0, release: 0.05 },
    })
    synth.volume.value = Tone.gainToDb(volume * 0.2)

    const notes = ['G3', 'B3', 'D4', 'G4']
    const now = Tone.now()

    notes.forEach((note, i) => {
      synth.triggerAttackRelease(note, '16n', now + i * 0.08)
    })
  }

  // UI 点击音效 - 短促的电子脉冲
  playClick(volume = 0.3) {
    if (!this.initialized || !this.clickSynths) return

    const synth = this.getFromPool(this.clickSynths)
    synth.volume.value = Tone.gainToDb(volume * 0.4)

    const now = Tone.now()
    synth.triggerAttack('G5', now)
    synth.frequency.exponentialRampTo('G4', 0.05, now)
    synth.triggerRelease(now + 0.05)
  }

  // UI Hover 音效 - 轻柔的电子提示音
  playHover(volume = 0.2) {
    if (!this.initialized || !this.clickSynths) return

    const synth = this.getFromPool(this.clickSynths)
    synth.volume.value = Tone.gainToDb(volume * 0.25)

    const now = Tone.now()
    synth.triggerAttack('E5', now)
    synth.triggerRelease(now + 0.03)
  }

  // 失败/受伤音效
  playHit(volume = 0.5) {
    if (!this.initialized || !this.generalSynths || !this.noiseSynths) return

    const synth = this.getFromPool(this.generalSynths)
    synth.set({
      oscillator: { type: 'sawtooth' },
      envelope: { attack: 0.01, decay: 0.2, sustain: 0, release: 0.05 },
    })
    synth.volume.value = Tone.gainToDb(volume * 0.5)

    const now = Tone.now()
    synth.triggerAttack('G3', now)
    synth.frequency.exponentialRampTo('G1', 0.2, now)
    synth.triggerRelease(now + 0.2)

    // 噪音
    const noise = this.getFromPool(this.noiseSynths)
    noise.set({
      noise: { type: 'white' },
      envelope: { attack: 0.01, decay: 0.12, sustain: 0, release: 0.03 },
    })
    noise.volume.value = Tone.gainToDb(volume * 0.3)
    noise.triggerAttackRelease('8n')
  }

  // 成功音效 - 上升的和弦
  playSuccess(volume = 0.5) {
    if (!this.initialized || !this.polySynths) return

    const synth = this.getFromPool(this.polySynths)
    synth.volume.value = Tone.gainToDb(volume * 0.25)

    const notes = ['C5', 'E5', 'G5', 'C6']
    const now = Tone.now()

    notes.forEach((note, i) => {
      synth.triggerAttackRelease(note, '4n', now + i * 0.1)
    })
  }

  // 失败音效 - 下降的不和谐音
  playFail(volume = 0.5) {
    if (!this.initialized || !this.polySynths) return

    const synth = this.getFromPool(this.polySynths)
    synth.set({
      oscillator: { type: 'sawtooth' },
      envelope: { attack: 0.01, decay: 0.5, sustain: 0, release: 0.1 },
    })
    synth.volume.value = Tone.gainToDb(volume * 0.3)

    const now = Tone.now()
    synth.triggerAttackRelease(['G4', 'Ab4'], '4n', now)
  }

  // 脚步声 - 带节流
  playFootstep(volume = 0.2) {
    if (!this.initialized || !this.noiseSynths) return
    
    // 节流：避免过于频繁的调用
    const now = performance.now()
    if (now - this.lastFootstepTime < this.footstepCooldown) return
    this.lastFootstepTime = now

    const noise = this.getFromPool(this.noiseSynths)
    noise.volume.value = Tone.gainToDb(volume * 0.3)
    noise.triggerAttackRelease('32n')
  }

  // 宝石收集音效 - 更华丽的版本
  playGem(volume = 0.6) {
    if (!this.initialized || !this.collectSynths) return

    const synth = this.getFromPool(this.collectSynths)
    synth.set({
      oscillator: { type: 'triangle' },
      envelope: { attack: 0.02, decay: 0.3, sustain: 0.1, release: 0.2 },
    })
    synth.volume.value = Tone.gainToDb(volume * 0.25)

    const now = Tone.now()
    synth.triggerAttackRelease(['C6', 'E6'], '8n', now)
    synth.triggerAttackRelease(['G6', 'C7'], '8n', now + 0.1)
  }

  // 清理资源
  dispose() {
    if (!this.initialized) return
    
    this.jumpSynths?.synths.forEach(s => s.dispose())
    this.clickSynths?.synths.forEach(s => s.dispose())
    this.collectSynths?.synths.forEach(s => s.dispose())
    this.noiseSynths?.synths.forEach(s => s.dispose())
    this.generalSynths?.synths.forEach(s => s.dispose())
    this.polySynths?.synths.forEach(s => s.dispose())
    this.masterVolume?.dispose()
    
    this.initialized = false
  }
}

export const synthSounds = new SynthSoundEngine()
