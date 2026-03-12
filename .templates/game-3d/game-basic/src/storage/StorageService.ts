// 存储服务层 - 支持 localStorage 和 IndexedDB
// 自动降级：IndexedDB 不可用时使用 localStorage

const STORAGE_PREFIX = 'game3d_'
const DB_NAME = 'game3d_storage'
const DB_VERSION = 1
const STORE_NAME = 'game_data'

// 存储值类型
export type StorageValue = string | number | boolean | object | null

// IndexedDB 实例缓存
let dbInstance: IDBDatabase | null = null
let dbInitPromise: Promise<IDBDatabase | null> | null = null

// ============ IndexedDB 操作 ============

async function openDB(): Promise<IDBDatabase | null> {
  // 如果已有实例，直接返回
  if (dbInstance) return dbInstance
  
  // 如果正在初始化，等待完成
  if (dbInitPromise) return dbInitPromise
  
  dbInitPromise = new Promise((resolve) => {
    try {
      const request = indexedDB.open(DB_NAME, DB_VERSION)
      
      request.onerror = () => {
        console.warn('IndexedDB not available, falling back to localStorage')
        resolve(null)
      }
      
      request.onsuccess = () => {
        dbInstance = request.result
        resolve(dbInstance)
      }
      
      request.onupgradeneeded = (event) => {
        const db = (event.target as IDBOpenDBRequest).result
        
        // 创建对象存储
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          db.createObjectStore(STORE_NAME, { keyPath: 'key' })
        }
      }
    } catch {
      console.warn('IndexedDB error, falling back to localStorage')
      resolve(null)
    }
  })
  
  return dbInitPromise
}

async function idbGet<T>(key: string): Promise<T | null> {
  const db = await openDB()
  if (!db) return null
  
  return new Promise((resolve) => {
    try {
      const transaction = db.transaction(STORE_NAME, 'readonly')
      const store = transaction.objectStore(STORE_NAME)
      const request = store.get(key)
      
      request.onsuccess = () => {
        resolve(request.result?.value ?? null)
      }
      request.onerror = () => resolve(null)
    } catch {
      resolve(null)
    }
  })
}

async function idbSet(key: string, value: StorageValue): Promise<boolean> {
  const db = await openDB()
  if (!db) return false
  
  return new Promise((resolve) => {
    try {
      const transaction = db.transaction(STORE_NAME, 'readwrite')
      const store = transaction.objectStore(STORE_NAME)
      const request = store.put({ key, value, updatedAt: Date.now() })
      
      request.onsuccess = () => resolve(true)
      request.onerror = () => resolve(false)
    } catch {
      resolve(false)
    }
  })
}

async function idbDelete(key: string): Promise<boolean> {
  const db = await openDB()
  if (!db) return false
  
  return new Promise((resolve) => {
    try {
      const transaction = db.transaction(STORE_NAME, 'readwrite')
      const store = transaction.objectStore(STORE_NAME)
      const request = store.delete(key)
      
      request.onsuccess = () => resolve(true)
      request.onerror = () => resolve(false)
    } catch {
      resolve(false)
    }
  })
}

async function idbGetAll(): Promise<Record<string, StorageValue>> {
  const db = await openDB()
  if (!db) return {}
  
  return new Promise((resolve) => {
    try {
      const transaction = db.transaction(STORE_NAME, 'readonly')
      const store = transaction.objectStore(STORE_NAME)
      const request = store.getAll()
      
      request.onsuccess = () => {
        const result: Record<string, StorageValue> = {}
        for (const item of request.result) {
          result[item.key] = item.value
        }
        resolve(result)
      }
      request.onerror = () => resolve({})
    } catch {
      resolve({})
    }
  })
}

async function idbClear(): Promise<boolean> {
  const db = await openDB()
  if (!db) return false
  
  return new Promise((resolve) => {
    try {
      const transaction = db.transaction(STORE_NAME, 'readwrite')
      const store = transaction.objectStore(STORE_NAME)
      const request = store.clear()
      
      request.onsuccess = () => resolve(true)
      request.onerror = () => resolve(false)
    } catch {
      resolve(false)
    }
  })
}

// ============ localStorage 操作 ============

function lsGet<T>(key: string): T | null {
  try {
    const item = localStorage.getItem(STORAGE_PREFIX + key)
    return item ? JSON.parse(item) : null
  } catch {
    return null
  }
}

function lsSet(key: string, value: StorageValue): boolean {
  try {
    localStorage.setItem(STORAGE_PREFIX + key, JSON.stringify(value))
    return true
  } catch {
    console.warn('localStorage write failed, storage may be full')
    return false
  }
}

function lsDelete(key: string): boolean {
  try {
    localStorage.removeItem(STORAGE_PREFIX + key)
    return true
  } catch {
    return false
  }
}

function lsGetAll(): Record<string, StorageValue> {
  const result: Record<string, StorageValue> = {}
  try {
    for (let i = 0; i < localStorage.length; i++) {
      const fullKey = localStorage.key(i)
      if (fullKey?.startsWith(STORAGE_PREFIX)) {
        const key = fullKey.slice(STORAGE_PREFIX.length)
        const value = localStorage.getItem(fullKey)
        if (value) {
          result[key] = JSON.parse(value)
        }
      }
    }
  } catch {
    // ignore
  }
  return result
}

function lsClear(): boolean {
  try {
    const keysToRemove: string[] = []
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i)
      if (key?.startsWith(STORAGE_PREFIX)) {
        keysToRemove.push(key)
      }
    }
    keysToRemove.forEach(key => localStorage.removeItem(key))
    return true
  } catch {
    return false
  }
}

// ============ 统一存储服务 ============

export type StorageBackend = 'indexeddb' | 'localstorage' | 'auto'

interface StorageOptions {
  backend?: StorageBackend
}

class StorageService {
  private preferIndexedDB = true
  
  // 检测 IndexedDB 可用性
  async isIndexedDBAvailable(): Promise<boolean> {
    const db = await openDB()
    return db !== null
  }
  
  // 获取数据
  async get<T = StorageValue>(key: string, options: StorageOptions = {}): Promise<T | null> {
    const { backend = 'auto' } = options
    
    // 优先使用 IndexedDB
    if (backend === 'indexeddb' || (backend === 'auto' && this.preferIndexedDB)) {
      const result = await idbGet<T>(key)
      if (result !== null) return result
    }
    
    // 降级到 localStorage
    return lsGet<T>(key)
  }
  
  // 存储数据
  async set(key: string, value: StorageValue, options: StorageOptions = {}): Promise<boolean> {
    const { backend = 'auto' } = options
    
    // 同时写入两个存储（确保数据一致性）
    if (backend === 'auto') {
      const idbSuccess = await idbSet(key, value)
      const lsSuccess = lsSet(key, value)
      return idbSuccess || lsSuccess
    }
    
    if (backend === 'indexeddb') {
      return idbSet(key, value)
    }
    
    return lsSet(key, value)
  }
  
  // 删除数据
  async delete(key: string, options: StorageOptions = {}): Promise<boolean> {
    const { backend = 'auto' } = options
    
    if (backend === 'auto') {
      const idbSuccess = await idbDelete(key)
      const lsSuccess = lsDelete(key)
      return idbSuccess || lsSuccess
    }
    
    if (backend === 'indexeddb') {
      return idbDelete(key)
    }
    
    return lsDelete(key)
  }
  
  // 获取所有数据
  async getAll(options: StorageOptions = {}): Promise<Record<string, StorageValue>> {
    const { backend = 'auto' } = options
    
    if (backend === 'indexeddb' || (backend === 'auto' && this.preferIndexedDB)) {
      const idbResult = await idbGetAll()
      if (Object.keys(idbResult).length > 0) return idbResult
    }
    
    return lsGetAll()
  }
  
  // 清除所有数据
  async clear(options: StorageOptions = {}): Promise<boolean> {
    const { backend = 'auto' } = options
    
    if (backend === 'auto') {
      const idbSuccess = await idbClear()
      const lsSuccess = lsClear()
      return idbSuccess || lsSuccess
    }
    
    if (backend === 'indexeddb') {
      return idbClear()
    }
    
    return lsClear()
  }
  
  // 检查键是否存在
  async has(key: string): Promise<boolean> {
    const value = await this.get(key)
    return value !== null
  }
  
  // 获取存储大小估算 (bytes)
  async getStorageSize(): Promise<{ used: number; quota: number } | null> {
    if ('storage' in navigator && 'estimate' in navigator.storage) {
      try {
        const estimate = await navigator.storage.estimate()
        return {
          used: estimate.usage || 0,
          quota: estimate.quota || 0,
        }
      } catch {
        return null
      }
    }
    return null
  }
}

// 单例导出
export const storage = new StorageService()

// 便捷方法
export const getItem = <T = StorageValue>(key: string) => storage.get<T>(key)
export const setItem = (key: string, value: StorageValue) => storage.set(key, value)
export const removeItem = (key: string) => storage.delete(key)
export const clearStorage = () => storage.clear()
