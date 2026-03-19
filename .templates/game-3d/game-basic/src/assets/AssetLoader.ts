// 资产加载器 - 管理 GLTF 模型、纹理等资源的预加载
import * as THREE from 'three'
import { GLTFLoader, GLTF } from 'three/examples/jsm/loaders/GLTFLoader.js'
import { DRACOLoader } from 'three/examples/jsm/loaders/DRACOLoader.js'
import { KTX2Loader } from 'three/examples/jsm/loaders/KTX2Loader.js'

// 资产类型
export type AssetType = 'gltf' | 'texture' | 'cubeTexture' | 'audio' | 'hdr'

// 资产配置
export interface AssetConfig {
  type: AssetType
  path: string
  // GLTF 特定选项
  useDraco?: boolean
  // 纹理特定选项
  colorSpace?: THREE.ColorSpace
  flipY?: boolean
  wrapS?: THREE.Wrapping
  wrapT?: THREE.Wrapping
  // LOD 配置
  lod?: {
    distances: number[]
    paths: string[]
  }
}

// 资产清单
export interface AssetManifest {
  [key: string]: AssetConfig
}

// 加载进度
export interface LoadProgress {
  loaded: number
  total: number
  percentage: number
  currentAsset: string
  errors: string[]
}

// 加载后的资产
export interface LoadedAssets {
  gltf: Map<string, GLTF>
  textures: Map<string, THREE.Texture>
  cubeTextures: Map<string, THREE.CubeTexture>
}

// 进度回调
type ProgressCallback = (progress: LoadProgress) => void

class AssetLoader {
  private gltfLoader: GLTFLoader
  private textureLoader: THREE.TextureLoader
  private cubeTextureLoader: THREE.CubeTextureLoader
  private dracoLoader: DRACOLoader | null = null
  private ktx2Loader: KTX2Loader | null = null
  
  private loadedAssets: LoadedAssets = {
    gltf: new Map(),
    textures: new Map(),
    cubeTextures: new Map(),
  }
  
  private loadingPromises: Map<string, Promise<unknown>> = new Map()
  
  constructor() {
    this.gltfLoader = new GLTFLoader()
    this.textureLoader = new THREE.TextureLoader()
    this.cubeTextureLoader = new THREE.CubeTextureLoader()
  }
  
  // 初始化 Draco 解码器 (用于压缩模型)
  initDraco(decoderPath = '/draco/') {
    if (!this.dracoLoader) {
      this.dracoLoader = new DRACOLoader()
      this.dracoLoader.setDecoderPath(decoderPath)
      this.dracoLoader.setDecoderConfig({ type: 'js' })
      this.gltfLoader.setDRACOLoader(this.dracoLoader)
    }
    return this
  }
  
  // 初始化 KTX2 加载器 (用于压缩纹理)
  initKTX2(renderer: THREE.WebGLRenderer, transcoderPath = '/basis/') {
    if (!this.ktx2Loader) {
      this.ktx2Loader = new KTX2Loader()
      this.ktx2Loader.setTranscoderPath(transcoderPath)
      this.ktx2Loader.detectSupport(renderer)
      this.gltfLoader.setKTX2Loader(this.ktx2Loader)
    }
    return this
  }
  
  // 预加载资产清单
  async preload(manifest: AssetManifest, onProgress?: ProgressCallback): Promise<LoadedAssets> {
    const entries = Object.entries(manifest)
    const total = entries.length
    let loaded = 0
    const errors: string[] = []
    
    const updateProgress = (currentAsset: string) => {
      onProgress?.({
        loaded,
        total,
        percentage: Math.round((loaded / total) * 100),
        currentAsset,
        errors,
      })
    }
    
    // 并行加载所有资产
    const loadPromises = entries.map(async ([key, config]) => {
      try {
        updateProgress(key)
        await this.loadAsset(key, config)
        loaded++
        updateProgress(key)
      } catch (error) {
        const errorMsg = `Failed to load ${key}: ${error}`
        console.error(errorMsg)
        errors.push(errorMsg)
        loaded++
        updateProgress(key)
      }
    })
    
    await Promise.all(loadPromises)
    
    return this.loadedAssets
  }
  
  // 加载单个资产
  async loadAsset(key: string, config: AssetConfig): Promise<unknown> {
    // 检查是否已加载
    if (this.isLoaded(key, config.type)) {
      return this.getAsset(key, config.type)
    }
    
    // 检查是否正在加载
    if (this.loadingPromises.has(key)) {
      return this.loadingPromises.get(key)
    }
    
    // 开始加载
    const promise = this.doLoad(key, config)
    this.loadingPromises.set(key, promise)
    
    try {
      const result = await promise
      return result
    } finally {
      this.loadingPromises.delete(key)
    }
  }
  
  private async doLoad(key: string, config: AssetConfig): Promise<unknown> {
    switch (config.type) {
      case 'gltf':
        return this.loadGLTF(key, config)
      case 'texture':
        return this.loadTexture(key, config)
      case 'cubeTexture':
        return this.loadCubeTexture(key, config)
      default:
        throw new Error(`Unsupported asset type: ${config.type}`)
    }
  }
  
  // 加载 GLTF 模型
  private loadGLTF(key: string, config: AssetConfig): Promise<GLTF> {
    return new Promise((resolve, reject) => {
      this.gltfLoader.load(
        config.path,
        (gltf) => {
          // 处理模型
          gltf.scene.traverse((child) => {
            if ((child as THREE.Mesh).isMesh) {
              const mesh = child as THREE.Mesh
              mesh.castShadow = true
              mesh.receiveShadow = true
            }
          })
          
          this.loadedAssets.gltf.set(key, gltf)
          resolve(gltf)
        },
        undefined,
        reject
      )
    })
  }
  
  // 加载纹理
  private loadTexture(key: string, config: AssetConfig): Promise<THREE.Texture> {
    return new Promise((resolve, reject) => {
      this.textureLoader.load(
        config.path,
        (texture) => {
          // 应用纹理设置
          if (config.colorSpace) {
            texture.colorSpace = config.colorSpace
          }
          if (config.flipY !== undefined) {
            texture.flipY = config.flipY
          }
          if (config.wrapS) {
            texture.wrapS = config.wrapS
          }
          if (config.wrapT) {
            texture.wrapT = config.wrapT
          }
          
          this.loadedAssets.textures.set(key, texture)
          resolve(texture)
        },
        undefined,
        reject
      )
    })
  }
  
  // 加载立方体贴图
  private loadCubeTexture(key: string, config: AssetConfig): Promise<THREE.CubeTexture> {
    return new Promise((resolve, reject) => {
      // 期望 path 是目录，包含 px, nx, py, ny, pz, nz 六张图
      const faces = ['px', 'nx', 'py', 'ny', 'pz', 'nz']
      const urls = faces.map(face => `${config.path}/${face}.jpg`)
      
      this.cubeTextureLoader.load(
        urls,
        (texture) => {
          this.loadedAssets.cubeTextures.set(key, texture)
          resolve(texture)
        },
        undefined,
        reject
      )
    })
  }
  
  // 检查资产是否已加载
  isLoaded(key: string, type: AssetType): boolean {
    switch (type) {
      case 'gltf':
        return this.loadedAssets.gltf.has(key)
      case 'texture':
        return this.loadedAssets.textures.has(key)
      case 'cubeTexture':
        return this.loadedAssets.cubeTextures.has(key)
      default:
        return false
    }
  }
  
  // 获取已加载的资产
  getAsset(key: string, type: AssetType): unknown {
    switch (type) {
      case 'gltf':
        return this.loadedAssets.gltf.get(key)
      case 'texture':
        return this.loadedAssets.textures.get(key)
      case 'cubeTexture':
        return this.loadedAssets.cubeTextures.get(key)
      default:
        return undefined
    }
  }
  
  // 获取 GLTF 模型
  getGLTF(key: string): GLTF | undefined {
    return this.loadedAssets.gltf.get(key)
  }
  
  // 获取纹理
  getTexture(key: string): THREE.Texture | undefined {
    return this.loadedAssets.textures.get(key)
  }
  
  // 获取立方体贴图
  getCubeTexture(key: string): THREE.CubeTexture | undefined {
    return this.loadedAssets.cubeTextures.get(key)
  }
  
  // 克隆 GLTF 场景 (用于实例化)
  cloneGLTF(key: string): THREE.Group | undefined {
    const gltf = this.loadedAssets.gltf.get(key)
    if (!gltf) return undefined
    
    const clone = gltf.scene.clone()
    
    // 深度克隆材质
    clone.traverse((child) => {
      if ((child as THREE.Mesh).isMesh) {
        const mesh = child as THREE.Mesh
        if (Array.isArray(mesh.material)) {
          mesh.material = mesh.material.map(m => m.clone())
        } else {
          mesh.material = mesh.material.clone()
        }
      }
    })
    
    return clone
  }
  
  // 卸载资产
  unload(key: string, type: AssetType) {
    switch (type) {
      case 'gltf': {
        const gltf = this.loadedAssets.gltf.get(key)
        if (gltf) {
          gltf.scene.traverse((child) => {
            if ((child as THREE.Mesh).isMesh) {
              const mesh = child as THREE.Mesh
              mesh.geometry?.dispose()
              if (Array.isArray(mesh.material)) {
                mesh.material.forEach(m => m.dispose())
              } else {
                mesh.material?.dispose()
              }
            }
          })
          this.loadedAssets.gltf.delete(key)
        }
        break
      }
      case 'texture': {
        const texture = this.loadedAssets.textures.get(key)
        texture?.dispose()
        this.loadedAssets.textures.delete(key)
        break
      }
      case 'cubeTexture': {
        const cubeTexture = this.loadedAssets.cubeTextures.get(key)
        cubeTexture?.dispose()
        this.loadedAssets.cubeTextures.delete(key)
        break
      }
    }
  }
  
  // 卸载所有资产
  unloadAll() {
    // 卸载 GLTF
    this.loadedAssets.gltf.forEach((_, key) => this.unload(key, 'gltf'))
    
    // 卸载纹理
    this.loadedAssets.textures.forEach((_, key) => this.unload(key, 'texture'))
    
    // 卸载立方体贴图
    this.loadedAssets.cubeTextures.forEach((_, key) => this.unload(key, 'cubeTexture'))
    
    // 清理 Draco
    this.dracoLoader?.dispose()
    this.dracoLoader = null
  }
  
  // 获取加载统计
  getStats() {
    return {
      gltfCount: this.loadedAssets.gltf.size,
      textureCount: this.loadedAssets.textures.size,
      cubeTextureCount: this.loadedAssets.cubeTextures.size,
    }
  }
}

// 单例导出
export const assetLoader = new AssetLoader()
