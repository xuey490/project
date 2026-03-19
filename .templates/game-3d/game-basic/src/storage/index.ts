// 存储模块导出
export { storage, getItem, setItem, removeItem, clearStorage } from './StorageService'
export type { StorageValue, StorageBackend } from './StorageService'

export { 
  useSaveSystem, 
  useCurrentSave, 
  useSavesList, 
  useGameSettings,
  useSaveActions,
  defaultSettings,
  onSaveLoaded,
  triggerSaveLoaded,
  onBeforeSave,
} from './SaveSystem'

export type {
  GameSave,
  SaveMeta,
  PlayerSaveData,
  WorldSaveData,
  StatsSaveData,
  SettingsSaveData,
} from './SaveSystem'

export { useAutoSave, usePlaytimeTracker } from './useAutoSave'
