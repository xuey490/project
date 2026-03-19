// 自动保存 Hook - 定时保存游戏进度
import { useEffect, useRef } from 'react'
import { useSaveSystem } from './SaveSystem'

interface AutoSaveOptions {
  interval?: number // 秒，默认 60
  enabled?: boolean
  onSave?: () => void
  onError?: (error: string) => void
}

export function useAutoSave(options: AutoSaveOptions = {}) {
  const { 
    interval = 60, 
    enabled = true,
    onSave,
    onError,
  } = options
  
  const { 
    currentSaveId, 
    save, 
    isSaving, 
    lastError,
    autoSaveEnabled,
  } = useSaveSystem(state => ({
    currentSaveId: state.currentSaveId,
    save: state.save,
    isSaving: state.isSaving,
    lastError: state.lastError,
    autoSaveEnabled: state.autoSaveEnabled,
  }))
  
  const lastSaveRef = useRef<number>(Date.now())
  const errorHandledRef = useRef<string | null>(null)
  
  // 自动保存逻辑
  useEffect(() => {
    if (!enabled || !autoSaveEnabled || !currentSaveId) return
    
    const timer = setInterval(async () => {
      if (isSaving) return
      
      const now = Date.now()
      if (now - lastSaveRef.current >= interval * 1000) {
        const success = await save()
        if (success) {
          lastSaveRef.current = now
          onSave?.()
        }
      }
    }, 5000) // 每 5 秒检查一次
    
    return () => clearInterval(timer)
  }, [enabled, autoSaveEnabled, currentSaveId, interval, save, isSaving, onSave])
  
  // 错误回调
  useEffect(() => {
    if (lastError && lastError !== errorHandledRef.current) {
      errorHandledRef.current = lastError
      onError?.(lastError)
    }
  }, [lastError, onError])
  
  // 页面关闭前保存
  useEffect(() => {
    if (!enabled || !currentSaveId) return
    
    const handleBeforeUnload = () => {
      // 使用同步 localStorage 作为最后手段
      const saveSystem = useSaveSystem.getState()
      if (saveSystem.currentSave) {
        try {
          localStorage.setItem(
            'game3d_emergency_save',
            JSON.stringify(saveSystem.currentSave)
          )
        } catch {
          // 忽略错误
        }
      }
    }
    
    window.addEventListener('beforeunload', handleBeforeUnload)
    return () => window.removeEventListener('beforeunload', handleBeforeUnload)
  }, [enabled, currentSaveId])
  
  return {
    isSaving,
    lastError,
    forceSave: save,
  }
}

// 游戏时间追踪 Hook
export function usePlaytimeTracker() {
  const { currentSave, updateStats } = useSaveSystem(state => ({
    currentSave: state.currentSave,
    updateStats: state.updateStats,
  }))
  
  const startTimeRef = useRef<number>(Date.now())
  
  useEffect(() => {
    if (!currentSave) return
    
    startTimeRef.current = Date.now()
    
    // 每 10 秒更新一次游戏时间
    const timer = setInterval(() => {
      const elapsed = Math.floor((Date.now() - startTimeRef.current) / 1000)
      startTimeRef.current = Date.now()
      
      updateStats({
        totalPlaytime: (currentSave.stats.totalPlaytime || 0) + elapsed,
      })
    }, 10000)
    
    return () => {
      clearInterval(timer)
      // 最后一次更新
      const elapsed = Math.floor((Date.now() - startTimeRef.current) / 1000)
      if (elapsed > 0) {
        updateStats({
          totalPlaytime: (currentSave.stats.totalPlaytime || 0) + elapsed,
        })
      }
    }
  }, [currentSave?.id]) // eslint-disable-line react-hooks/exhaustive-deps
  
  return currentSave?.stats.totalPlaytime || 0
}
