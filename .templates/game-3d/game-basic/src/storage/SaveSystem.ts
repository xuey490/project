// 存档系统 - 游戏进度保存/加载
import { create } from 'zustand'
import { subscribeWithSelector } from 'zustand/middleware'
import { storage } from './StorageService'

// ============ 类型定义 ============

// 玩家存档数据
export interface PlayerSaveData {
  position: { x: number; y: number; z: number }
  health: number
  maxHealth: number
  inventory: string[]
  abilities: string[]
  level: number
  experience: number
}

// 世界存档数据
export interface WorldSaveData {
  currentScene: string
  unlockedLevels: string[]
  completedObjectives: string[]
  collectibles: string[]
  secrets: string[]
}

// 统计存档数据
export interface StatsSaveData {
  score: number
  highScore: number
  kills: number
  deaths: number
  totalPlaytime: number // 秒
  gamesPlayed: number
  achievements: string[]
}

// 设置存档数据
export interface SettingsSaveData {
  // 音频
  masterVolume: number
  musicVolume: number
  sfxVolume: number
  muted: boolean
  
  // 画面
  quality: 'low' | 'medium' | 'high' | 'ultra'
  shadows: boolean
  antialiasing: boolean
  postProcessing: boolean
  
  // 控制
  mouseSensitivity: number
  invertY: boolean
  
  // 其他
  showFPS: boolean
  language: string
}

// 完整存档
export interface GameSave {
  id: string
  name: string
  createdAt: number
  updatedAt: number
  playtime: number // 秒
  thumbnail?: string // base64
  
  // 存档数据
  player: PlayerSaveData
  world: WorldSaveData
  stats: StatsSaveData
}

// 存档元数据（用于列表展示）
export interface SaveMeta {
  id: string
  name: string
  createdAt: number
  updatedAt: number
  playtime: number
  scene: string
  level: number
  thumbnail?: string
}

// ============ 存储键 ============

const KEYS = {
  SAVES_INDEX: 'saves_index',
  SAVE_PREFIX: 'save_',
  SETTINGS: 'settings',
  CURRENT_SAVE_ID: 'current_save_id',
  QUICK_SAVE: 'quick_save',
} as const

// ============ 默认值 ============

const defaultPlayerData: PlayerSaveData = {
  position: { x: 0, y: 1, z: 0 },
  health: 100,
  maxHealth: 100,
  inventory: [],
  abilities: [],
  level: 1,
  experience: 0,
}

const defaultWorldData: WorldSaveData = {
  currentScene: 'game',
  unlockedLevels: ['level1'],
  completedObjectives: [],
  collectibles: [],
  secrets: [],
}

const defaultStatsData: StatsSaveData = {
  score: 0,
  highScore: 0,
  kills: 0,
  deaths: 0,
  totalPlaytime: 0,
  gamesPlayed: 0,
  achievements: [],
}

export const defaultSettings: SettingsSaveData = {
  masterVolume: 1.0,
  musicVolume: 0.5,
  sfxVolume: 1.0,
  muted: false,
  quality: 'high',
  shadows: true,
  antialiasing: true,
  postProcessing: true,
  mouseSensitivity: 1.0,
  invertY: false,
  showFPS: true,
  language: 'zh-CN',
}

// ============ Store 状态 ============

interface SaveSystemState {
  // 当前存档
  currentSaveId: string | null
  currentSave: GameSave | null
  
  // 存档列表
  savesList: SaveMeta[]
  
  // 设置（独立于存档）
  settings: SettingsSaveData
  
  // 状态
  isLoading: boolean
  isSaving: boolean
  lastError: string | null
  lastSaveTime: number | null
  
  // 自动保存
  autoSaveEnabled: boolean
  autoSaveInterval: number // 秒
}

interface SaveSystemActions {
  // 初始化（从存储加载）
  initialize: () => Promise<void>
  
  // 存档操作
  createSave: (name: string) => Promise<string>
  loadSave: (saveId: string) => Promise<boolean>
  deleteSave: (saveId: string) => Promise<boolean>
  renameSave: (saveId: string, newName: string) => Promise<boolean>
  
  // 当前存档操作
  save: () => Promise<boolean>
  quickSave: () => Promise<boolean>
  quickLoad: () => Promise<boolean>
  
  // 更新当前存档数据
  updatePlayer: (data: Partial<PlayerSaveData>) => void
  updateWorld: (data: Partial<WorldSaveData>) => void
  updateStats: (data: Partial<StatsSaveData>) => void
  
  // 设置操作
  updateSettings: (settings: Partial<SettingsSaveData>) => Promise<void>
  resetSettings: () => Promise<void>
  
  // 获取器
  getSavesList: () => Promise<SaveMeta[]>
  getSave: (saveId: string) => Promise<GameSave | null>
  
  // 自动保存
  setAutoSave: (enabled: boolean, interval?: number) => void
  
  // 导入/导出
  exportSave: (saveId: string) => Promise<string | null>
  importSave: (jsonData: string) => Promise<string | null>
  
  // 清除
  clearAllSaves: () => Promise<boolean>
}

// ============ 保存前同步回调 ============

type BeforeSaveCallback = () => void
const beforeSaveCallbacks = new Set<BeforeSaveCallback>()

// 注册保存前同步回调（用于从游戏状态同步到存档）
export function onBeforeSave(callback: BeforeSaveCallback): () => void {
  beforeSaveCallbacks.add(callback)
  return () => beforeSaveCallbacks.delete(callback)
}

// 触发保存前同步
function triggerBeforeSave() {
  beforeSaveCallbacks.forEach(cb => cb())
}

// ============ Store 创建 ============

export const useSaveSystem = create<SaveSystemState & SaveSystemActions>()(
  subscribeWithSelector((set, get) => ({
    // 初始状态
    currentSaveId: null,
    currentSave: null,
    savesList: [],
    settings: defaultSettings,
    isLoading: false,
    isSaving: false,
    lastError: null,
    lastSaveTime: null,
    autoSaveEnabled: true,
    autoSaveInterval: 60,
    
    // 初始化
    initialize: async () => {
      set({ isLoading: true, lastError: null })
      
      try {
        // 加载设置
        const savedSettings = await storage.get<SettingsSaveData>(KEYS.SETTINGS)
        if (savedSettings) {
          set({ settings: { ...defaultSettings, ...savedSettings } })
        }
        
        // 加载存档列表
        const savesIndex = await storage.get<SaveMeta[]>(KEYS.SAVES_INDEX)
        if (savesIndex) {
          set({ savesList: savesIndex })
        }
        
        // 恢复上次的存档
        const currentSaveId = await storage.get<string>(KEYS.CURRENT_SAVE_ID)
        if (currentSaveId) {
          await get().loadSave(currentSaveId)
        }
        
        set({ isLoading: false })
      } catch (error) {
        set({ 
          isLoading: false, 
          lastError: error instanceof Error ? error.message : 'Failed to initialize' 
        })
      }
    },
    
    // 创建新存档
    createSave: async (name) => {
      const id = `save_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`
      const now = Date.now()
      
      const newSave: GameSave = {
        id,
        name,
        createdAt: now,
        updatedAt: now,
        playtime: 0,
        player: { ...defaultPlayerData },
        world: { ...defaultWorldData },
        stats: { ...defaultStatsData },
      }
      
      // 保存存档
      const success = await storage.set(KEYS.SAVE_PREFIX + id, newSave)
      if (!success) {
        set({ lastError: 'Failed to create save' })
        return ''
      }
      
      // 更新索引
      const meta: SaveMeta = {
        id,
        name,
        createdAt: now,
        updatedAt: now,
        playtime: 0,
        scene: newSave.world.currentScene,
        level: newSave.player.level,
      }
      
      const newList = [meta, ...get().savesList]
      await storage.set(KEYS.SAVES_INDEX, newList)
      await storage.set(KEYS.CURRENT_SAVE_ID, id)
      
      set({
        currentSaveId: id,
        currentSave: newSave,
        savesList: newList,
        lastSaveTime: now,
      })
      
      return id
    },
    
    // 加载存档
    loadSave: async (saveId) => {
      set({ isLoading: true, lastError: null })
      
      try {
        const save = await storage.get<GameSave>(KEYS.SAVE_PREFIX + saveId)
        
        if (!save) {
          set({ isLoading: false, lastError: 'Save not found' })
          return false
        }
        
        await storage.set(KEYS.CURRENT_SAVE_ID, saveId)
        
        set({
          currentSaveId: saveId,
          currentSave: save,
          isLoading: false,
        })
        
        // 触发加载回调，让游戏同步状态
        triggerSaveLoaded(save)
        
        return true
      } catch (error) {
        set({
          isLoading: false,
          lastError: error instanceof Error ? error.message : 'Failed to load save',
        })
        return false
      }
    },
    
    // 删除存档
    deleteSave: async (saveId) => {
      const success = await storage.delete(KEYS.SAVE_PREFIX + saveId)
      if (!success) return false
      
      const newList = get().savesList.filter(s => s.id !== saveId)
      await storage.set(KEYS.SAVES_INDEX, newList)
      
      // 如果删除的是当前存档，清除当前状态
      if (get().currentSaveId === saveId) {
        await storage.delete(KEYS.CURRENT_SAVE_ID)
        set({
          currentSaveId: null,
          currentSave: null,
        })
      }
      
      set({ savesList: newList })
      return true
    },
    
    // 重命名存档
    renameSave: async (saveId, newName) => {
      const save = await storage.get<GameSave>(KEYS.SAVE_PREFIX + saveId)
      if (!save) return false
      
      save.name = newName
      save.updatedAt = Date.now()
      
      await storage.set(KEYS.SAVE_PREFIX + saveId, save)
      
      const newList = get().savesList.map(s => 
        s.id === saveId ? { ...s, name: newName, updatedAt: save.updatedAt } : s
      )
      await storage.set(KEYS.SAVES_INDEX, newList)
      
      if (get().currentSaveId === saveId) {
        set({ currentSave: save })
      }
      
      set({ savesList: newList })
      return true
    },
    
    // 保存当前存档
    save: async () => {
      const { currentSaveId, currentSave } = get()
      if (!currentSaveId || !currentSave) {
        set({ lastError: 'No active save' })
        return false
      }
      
      // 保存前触发同步，从游戏状态获取最新数据
      triggerBeforeSave()
      
      set({ isSaving: true })
      
      try {
        const now = Date.now()
        // 重新获取 currentSave，因为 triggerBeforeSave 可能更新了它
        const latestSave = get().currentSave
        if (!latestSave) {
          set({ isSaving: false, lastError: 'Save lost after sync' })
          return false
        }
        
        const updatedSave = {
          ...latestSave,
          updatedAt: now,
        }
        
        await storage.set(KEYS.SAVE_PREFIX + currentSaveId, updatedSave)
        
        // 更新索引
        const newList = get().savesList.map(s => 
          s.id === currentSaveId 
            ? { 
                ...s, 
                updatedAt: now, 
                playtime: updatedSave.playtime,
                scene: updatedSave.world.currentScene,
                level: updatedSave.player.level,
              } 
            : s
        )
        await storage.set(KEYS.SAVES_INDEX, newList)
        
        set({
          currentSave: updatedSave,
          savesList: newList,
          isSaving: false,
          lastSaveTime: now,
        })
        
        return true
      } catch (error) {
        set({
          isSaving: false,
          lastError: error instanceof Error ? error.message : 'Failed to save',
        })
        return false
      }
    },
    
    // 快速保存
    quickSave: async () => {
      const { currentSave, currentSaveId } = get()
      if (!currentSave) return false
      
      // 保存前触发同步，从游戏状态获取最新数据
      triggerBeforeSave()
      
      set({ isSaving: true })
      
      // 重新获取 currentSave，因为 triggerBeforeSave 可能更新了它
      const latestSave = get().currentSave
      if (!latestSave) {
        set({ isSaving: false })
        return false
      }
      
      const now = Date.now()
      const updatedSave = {
        ...latestSave,
        updatedAt: now,
      }
      
      // 同时保存到快速存档槽和原始存档
      const quickSaveSuccess = await storage.set(KEYS.QUICK_SAVE, updatedSave)
      
      // 如果有原始存档ID，也更新原始存档
      if (currentSaveId) {
        await storage.set(KEYS.SAVE_PREFIX + currentSaveId, updatedSave)
        
        // 更新索引
        const newList = get().savesList.map(s => 
          s.id === currentSaveId 
            ? { 
                ...s, 
                updatedAt: now, 
                playtime: updatedSave.playtime,
                scene: updatedSave.world.currentScene,
                level: updatedSave.player.level,
              } 
            : s
        )
        await storage.set(KEYS.SAVES_INDEX, newList)
        set({ savesList: newList })
      }
      
      set({ 
        currentSave: updatedSave,
        isSaving: false, 
        lastSaveTime: quickSaveSuccess ? now : get().lastSaveTime 
      })
      
      return quickSaveSuccess
    },
    
    // 快速加载
    quickLoad: async () => {
      set({ isLoading: true })
      
      const quickSave = await storage.get<GameSave>(KEYS.QUICK_SAVE)
      
      if (!quickSave) {
        set({ isLoading: false, lastError: 'No quick save found' })
        return false
      }
      
      set({
        currentSave: quickSave,
        currentSaveId: quickSave.id,
        isLoading: false,
      })
      
      // 触发加载回调，让游戏同步状态
      triggerSaveLoaded(quickSave)
      
      return true
    },
    
    // 更新玩家数据
    updatePlayer: (data) => {
      const { currentSave } = get()
      if (!currentSave) return
      
      set({
        currentSave: {
          ...currentSave,
          player: { ...currentSave.player, ...data },
        },
      })
    },
    
    // 更新世界数据
    updateWorld: (data) => {
      const { currentSave } = get()
      if (!currentSave) return
      
      set({
        currentSave: {
          ...currentSave,
          world: { ...currentSave.world, ...data },
        },
      })
    },
    
    // 更新统计数据
    updateStats: (data) => {
      const { currentSave } = get()
      if (!currentSave) return
      
      set({
        currentSave: {
          ...currentSave,
          stats: { ...currentSave.stats, ...data },
        },
      })
    },
    
    // 更新设置
    updateSettings: async (newSettings) => {
      const updated = { ...get().settings, ...newSettings }
      
      await storage.set(KEYS.SETTINGS, updated)
      set({ settings: updated })
    },
    
    // 重置设置
    resetSettings: async () => {
      await storage.set(KEYS.SETTINGS, defaultSettings)
      set({ settings: defaultSettings })
    },
    
    // 获取存档列表
    getSavesList: async () => {
      const saves = await storage.get<SaveMeta[]>(KEYS.SAVES_INDEX)
      return saves || []
    },
    
    // 获取单个存档
    getSave: async (saveId) => {
      return storage.get<GameSave>(KEYS.SAVE_PREFIX + saveId)
    },
    
    // 设置自动保存
    setAutoSave: (enabled, interval) => {
      set({ 
        autoSaveEnabled: enabled,
        autoSaveInterval: interval ?? get().autoSaveInterval,
      })
    },
    
    // 导出存档
    exportSave: async (saveId) => {
      const save = await storage.get<GameSave>(KEYS.SAVE_PREFIX + saveId)
      if (!save) return null
      
      return JSON.stringify(save, null, 2)
    },
    
    // 导入存档
    importSave: async (jsonData) => {
      try {
        const save = JSON.parse(jsonData) as GameSave
        
        // 验证数据结构
        if (!save.id || !save.name || !save.player || !save.world || !save.stats) {
          set({ lastError: 'Invalid save file format' })
          return null
        }
        
        // 生成新 ID 避免冲突
        const newId = `save_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`
        const now = Date.now()
        
        const importedSave: GameSave = {
          ...save,
          id: newId,
          name: `${save.name} (导入)`,
          createdAt: now,
          updatedAt: now,
        }
        
        await storage.set(KEYS.SAVE_PREFIX + newId, importedSave)
        
        const meta: SaveMeta = {
          id: newId,
          name: importedSave.name,
          createdAt: now,
          updatedAt: now,
          playtime: importedSave.playtime,
          scene: importedSave.world.currentScene,
          level: importedSave.player.level,
        }
        
        const newList = [meta, ...get().savesList]
        await storage.set(KEYS.SAVES_INDEX, newList)
        
        set({ savesList: newList })
        
        return newId
      } catch {
        set({ lastError: 'Failed to parse save file' })
        return null
      }
    },
    
    // 清除所有存档
    clearAllSaves: async () => {
      const { savesList } = get()
      
      // 删除所有存档
      for (const save of savesList) {
        await storage.delete(KEYS.SAVE_PREFIX + save.id)
      }
      
      // 清除索引和当前存档
      await storage.delete(KEYS.SAVES_INDEX)
      await storage.delete(KEYS.CURRENT_SAVE_ID)
      await storage.delete(KEYS.QUICK_SAVE)
      
      set({
        currentSaveId: null,
        currentSave: null,
        savesList: [],
      })
      
      return true
    },
  }))
)

// ============ 便捷 Hooks ============

export function useCurrentSave() {
  return useSaveSystem(state => state.currentSave)
}

export function useSavesList() {
  return useSaveSystem(state => state.savesList)
}

export function useGameSettings() {
  return useSaveSystem(state => state.settings)
}

export function useSaveActions() {
  return useSaveSystem(state => ({
    save: state.save,
    quickSave: state.quickSave,
    quickLoad: state.quickLoad,
    createSave: state.createSave,
    loadSave: state.loadSave,
    deleteSave: state.deleteSave,
  }))
}

// ============ 存档加载回调 ============

type SaveLoadCallback = (save: GameSave) => void
const loadCallbacks = new Set<SaveLoadCallback>()

// 注册存档加载回调
export function onSaveLoaded(callback: SaveLoadCallback): () => void {
  loadCallbacks.add(callback)
  return () => loadCallbacks.delete(callback)
}

// 触发存档加载回调（内部使用）
export function triggerSaveLoaded(save: GameSave) {
  loadCallbacks.forEach(cb => cb(save))
}
