// 场景管理模块导出

// 场景状态管理
export {
  useSceneStore,
  useCurrentScene,
  useTransition,
  useSceneSwitch,
  useSceneData,
  type SceneType,
  type SceneMeta,
  type TransitionType,
  type TransitionState,
} from './SceneStore'

// 场景渲染
export {
  registerScene,
  registerScenes,
  preloadScene,
  getSceneComponent,
  SceneRenderer,
  KeepAliveScene,
  SceneSwitchButton,
  BackButton,
  type SceneProps,
  type SceneConfig,
} from './SceneRenderer'

// 场景过渡
export {
  TransitionOverlay,
  SceneTransitionWrapper,
  Scene3DTransition,
  SceneLoadingIndicator,
} from './SceneTransition'
