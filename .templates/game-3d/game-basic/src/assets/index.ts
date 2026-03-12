// 资产模块导出
export { assetLoader } from './AssetLoader'
export type { AssetConfig, AssetManifest, AssetType, LoadProgress, LoadedAssets } from './AssetLoader'

export {
  useAssetStore,
  usePreloader,
  useModel,
  useGameTexture,
  usePBRTextures,
  preloadModels,
  preloadTextures,
  clearAssetCache,
} from './useAssets'
