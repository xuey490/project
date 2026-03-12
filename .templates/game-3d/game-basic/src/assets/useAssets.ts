// React Hooks 封装资产加载
import { useState, useEffect, useCallback, useMemo } from 'react'
import { useThree } from '@react-three/fiber'
import { useGLTF, useTexture } from '@react-three/drei'
import * as THREE from 'three'
import { GLTF } from 'three/examples/jsm/loaders/GLTFLoader.js'
import { assetLoader, AssetManifest, LoadProgress } from './AssetLoader'
import { create } from 'zustand'

// ============ 资产状态 Store ============

interface AssetState {
  isLoading: boolean
  isLoaded: boolean
  progress: LoadProgress
  errors: string[]
  
  setLoading: (loading: boolean) => void
  setLoaded: (loaded: boolean) => void
  setProgress: (progress: LoadProgress) => void
  addError: (error: string) => void
  reset: () => void
}

export const useAssetStore = create<AssetState>((set) => ({
  isLoading: false,
  isLoaded: false,
  progress: {
    loaded: 0,
    total: 0,
    percentage: 0,
    currentAsset: '',
    errors: [],
  },
  errors: [],
  
  setLoading: (loading) => set({ isLoading: loading }),
  setLoaded: (loaded) => set({ isLoaded: loaded }),
  setProgress: (progress) => set({ progress }),
  addError: (error) => set((state) => ({ errors: [...state.errors, error] })),
  reset: () => set({
    isLoading: false,
    isLoaded: false,
    progress: { loaded: 0, total: 0, percentage: 0, currentAsset: '', errors: [] },
    errors: [],
  }),
}))

// ============ 预加载 Hook ============

/**
 * 预加载资产清单
 * @param manifest 资产清单
 * @param autoStart 是否自动开始加载
 */
export function usePreloader(manifest: AssetManifest, autoStart = true) {
  const { setLoading, setLoaded, setProgress, addError, reset } = useAssetStore()
  const [ready, setReady] = useState(false)
  
  const load = useCallback(async () => {
    reset()
    setLoading(true)
    
    try {
      await assetLoader.preload(manifest, (progress) => {
        setProgress(progress)
        progress.errors.forEach(addError)
      })
      
      setLoaded(true)
      setReady(true)
    } catch (error) {
      addError(`Preload failed: ${error}`)
    } finally {
      setLoading(false)
    }
  }, [manifest])
  
  useEffect(() => {
    if (autoStart) {
      load()
    }
  }, [autoStart])
  
  return { ready, load }
}

// ============ GLTF Hook 增强 ============

interface UseModelOptions {
  // 是否复用场景（克隆）
  clone?: boolean
  // 阴影设置
  castShadow?: boolean
  receiveShadow?: boolean
}

/**
 * 增强的 GLTF 模型加载 Hook
 * 支持从 assetLoader 缓存获取或直接加载
 */
export function useModel(
  pathOrKey: string,
  options: UseModelOptions = {}
): { scene: THREE.Group; animations: THREE.AnimationClip[] } {
  const { clone = false, castShadow = true, receiveShadow = true } = options
  
  // 首先检查 assetLoader 缓存
  const cachedGLTF = useMemo(() => {
    return assetLoader.getGLTF(pathOrKey)
  }, [pathOrKey])
  
  // 如果缓存中没有，使用 drei 的 useGLTF
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const dreiGLTF = useGLTF(cachedGLTF ? '' : pathOrKey) as any as GLTF
  
  // 选择数据源
  const gltf = cachedGLTF || dreiGLTF
  
  // 处理场景
  const scene = useMemo(() => {
    if (!gltf?.scene) return new THREE.Group()
    
    const resultScene = clone ? gltf.scene.clone() : gltf.scene
    
    // 应用阴影设置
    resultScene.traverse((child) => {
      if ((child as THREE.Mesh).isMesh) {
        const mesh = child as THREE.Mesh
        mesh.castShadow = castShadow
        mesh.receiveShadow = receiveShadow
        
        // 克隆时也克隆材质
        if (clone) {
          if (Array.isArray(mesh.material)) {
            mesh.material = mesh.material.map(m => m.clone())
          } else {
            mesh.material = mesh.material.clone()
          }
        }
      }
    })
    
    return resultScene
  }, [gltf, clone, castShadow, receiveShadow])
  
  return {
    scene,
    animations: gltf?.animations || [],
  }
}

// ============ 纹理 Hook 增强 ============

interface UseTextureOptions {
  colorSpace?: THREE.ColorSpace
  wrapS?: THREE.Wrapping
  wrapT?: THREE.Wrapping
  repeat?: [number, number]
  anisotropy?: number
}

/**
 * 增强的纹理加载 Hook
 */
export function useGameTexture(
  pathOrKey: string,
  options: UseTextureOptions = {}
): THREE.Texture {
  const { gl } = useThree()
  
  // 首先检查 assetLoader 缓存
  const cachedTexture = useMemo(() => {
    return assetLoader.getTexture(pathOrKey)
  }, [pathOrKey])
  
  // 如果缓存中没有，使用 drei 的 useTexture
  const dreiTexture = useTexture(cachedTexture ? '' : pathOrKey) as THREE.Texture
  
  // 选择数据源
  const texture = cachedTexture || dreiTexture
  
  // 应用选项
  useEffect(() => {
    if (!texture) return
    
    if (options.colorSpace) {
      texture.colorSpace = options.colorSpace
    }
    if (options.wrapS) {
      texture.wrapS = options.wrapS
    }
    if (options.wrapT) {
      texture.wrapT = options.wrapT
    }
    if (options.repeat) {
      texture.repeat.set(...options.repeat)
    }
    if (options.anisotropy) {
      texture.anisotropy = Math.min(options.anisotropy, gl.capabilities.getMaxAnisotropy())
    }
    
    texture.needsUpdate = true
  }, [texture, options, gl])
  
  return texture
}

// ============ 多纹理加载 ============

interface TextureSet {
  map?: THREE.Texture
  normalMap?: THREE.Texture
  roughnessMap?: THREE.Texture
  metalnessMap?: THREE.Texture
  aoMap?: THREE.Texture
  displacementMap?: THREE.Texture
  emissiveMap?: THREE.Texture
}

/**
 * 加载 PBR 纹理集
 */
export function usePBRTextures(basePath: string, options: {
  maps?: ('map' | 'normal' | 'roughness' | 'metalness' | 'ao' | 'displacement' | 'emissive')[]
  extension?: string
} = {}): TextureSet {
  const { maps = ['map', 'normal', 'roughness'], extension = 'jpg' } = options
  
  const paths: string[] = []
  const mapTypes: string[] = []
  
  maps.forEach(mapType => {
    const suffix = mapType === 'map' ? 'diffuse' : mapType
    paths.push(`${basePath}_${suffix}.${extension}`)
    mapTypes.push(mapType)
  })
  
  const textures = useTexture(paths)
  const textureArray = Array.isArray(textures) ? textures : [textures]
  
  const result: TextureSet = {}
  
  mapTypes.forEach((type, index) => {
    const texture = textureArray[index]
    if (!texture) return
    
    switch (type) {
      case 'map':
        result.map = texture
        break
      case 'normal':
        result.normalMap = texture
        break
      case 'roughness':
        result.roughnessMap = texture
        break
      case 'metalness':
        result.metalnessMap = texture
        break
      case 'ao':
        result.aoMap = texture
        break
      case 'displacement':
        result.displacementMap = texture
        break
      case 'emissive':
        result.emissiveMap = texture
        break
    }
  })
  
  return result
}

// ============ 资产预热 ============

/**
 * 在 Canvas 外部预热 Drei 缓存
 */
export function preloadModels(paths: string[]) {
  paths.forEach(path => {
    useGLTF.preload(path)
  })
}

export function preloadTextures(paths: string[]) {
  paths.forEach(path => {
    useTexture.preload(path)
  })
}

// ============ 清理 ============

/**
 * 清理资产缓存
 */
export function clearAssetCache(path?: string) {
  assetLoader.unloadAll()
  if (path) {
    useGLTF.clear(path)
  }
  // useTexture 没有 clear 方法，由 Three.js 自动管理
}
