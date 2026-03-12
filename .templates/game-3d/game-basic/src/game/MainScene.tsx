import { Suspense, useCallback, useEffect, useState, useRef } from 'react'
import { Canvas } from '@react-three/fiber'
import { Sky, Stars, OrbitControls, PerspectiveCamera, Bvh } from '@react-three/drei'
import { Physics } from '@react-three/rapier'
import type { RapierRigidBody } from '@react-three/rapier'
import { useGameStore, GameState } from '@/engine'
import {
  Player,
  Ground,
  Platform,
  Wall,
  Ramp,
  Collectible,
  InteractableBox,
  FollowCamera,
} from '@/entities'
import { GameControls } from '@/systems'
import { GameUI, LoadingScreen, CanvasLoader } from '@/ui'
import { usePreloader, AssetManifest } from '@/assets'
import { 
  useCurrentScene, 
  TransitionOverlay,
  SceneLoadingIndicator,
} from '@/scenes'
import { EffectsRenderer, GamePostProcessing } from '@/effects'
import { ShadowCaster } from '@/utils/useLightTarget'

// ============ 资产清单配置 ============
// 在这里定义需要预加载的资产
const GAME_ASSETS: AssetManifest = {
  // 示例：取消注释以预加载模型
  // player: { type: 'gltf', path: '/models/player.glb' },
  // enemy: { type: 'gltf', path: '/models/enemy.glb', useDraco: true },
  // coin: { type: 'gltf', path: '/models/coin.glb' },
  
  // 示例：预加载纹理
  // groundTexture: { type: 'texture', path: '/textures/ground.jpg' },
  // skybox: { type: 'cubeTexture', path: '/textures/skybox' },
}

// 是否有资产需要预加载
const HAS_ASSETS_TO_LOAD = Object.keys(GAME_ASSETS).length > 0

// 最小加载时间（毫秒），确保 Loading 页面可见
const MIN_LOADING_TIME = 1500

// 游戏场景
function GameScene({ playerRef }: { playerRef: React.RefObject<RapierRigidBody | null> }) {
  const gameState = useGameStore((state) => state.gameState)
  const updateStats = useGameStore((state) => state.updateStats)

  // 使用 ref 避免每次分数变化时重新创建回调
  const handleCollect = useCallback((value: number) => {
    updateStats({ score: useGameStore.getState().stats.score + value })
  }, [updateStats])

  if (gameState === GameState.MENU) {
    return null
  }

  return (
    <Physics gravity={[0, -20, 0]} debug={false}>
      {/* 跟随相机 */}
      <FollowCamera offset={[0, 8, 12]} smoothness={5} />

      {/* 玩家 */}
      <Player ref={playerRef} position={[0, 3, 0]} />

      {/* 地面 */}
      <Ground size={[100, 100]} />

      {/* 平台 */}
      <Platform position={[5, 1, 0]} size={[4, 0.5, 4]} />
      <Platform position={[10, 2.5, 0]} size={[4, 0.5, 4]} />
      <Platform position={[15, 4, 0]} size={[4, 0.5, 4]} color="#5a4a3a" />
      <Platform position={[-5, 1.5, 5]} size={[3, 0.5, 3]} />
      <Platform position={[-8, 3, 5]} size={[3, 0.5, 3]} />

      {/* 斜坡 */}
      <Ramp position={[0, 0.5, -5]} rotation={[-0.3, 0, 0]} size={[4, 0.3, 6]} />

      {/* 墙壁 */}
      <Wall position={[20, 2.5, 0]} size={[0.5, 5, 10]} />
      <Wall position={[-15, 2.5, 0]} size={[0.5, 5, 10]} />

      {/* 可收集物品 */}
      <Collectible position={[5, 2, 0]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[10, 3.5, 0]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[15, 5, 0]} type="gem" value={50} onCollect={handleCollect} />
      <Collectible position={[-5, 3, 5]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[-8, 4.5, 5]} type="powerup" value={100} onCollect={handleCollect} />

      {/* 可交互箱子 */}
      <InteractableBox position={[3, 1, 3]} color="#ff6b6b" />
      <InteractableBox position={[-3, 1, -3]} color="#4ecdc4" />
      <InteractableBox position={[0, 1, 8]} color="#ffe66d" />
    </Physics>
  )
}

// 场景光照 - 使用 ShadowCaster 实现跟随玩家的动态阴影
function Lighting({ playerRef }: { playerRef?: React.RefObject<RapierRigidBody | null> }) {
  return (
    <>
      <ambientLight intensity={0.4} />
      {/* 动态阴影光：跟随玩家移动，阴影始终在玩家周围 */}
      {playerRef ? (
        <ShadowCaster 
          targetRef={playerRef} 
          positionOffset={[10, 15, 10]} 
          shadowMapSize={1024}
          intensity={1.2}
        />
      ) : (
        // 菜单场景使用静态光
        <directionalLight
          position={[50, 50, 25]}
          intensity={1.2}
          castShadow
          shadow-mapSize-width={1024}
          shadow-mapSize-height={1024}
          shadow-camera-left={-30}
          shadow-camera-right={30}
          shadow-camera-top={30}
          shadow-camera-bottom={-30}
        />
      )}
      <hemisphereLight args={['#87ceeb', '#3d7c3d', 0.3]} />
    </>
  )
}

// 环境背景
function Environment() {
  const gameState = useGameStore((state) => state.gameState)
  
  return (
    <>
      <Sky sunPosition={[100, 50, 100]} />
      {gameState !== GameState.MENU && <Stars radius={100} depth={50} count={500} factor={4} fade />}
      <fog attach="fog" args={['#87ceeb', 30, 100]} />
    </>
  )
}

// 主场景组件
export function MainScene() {
  const gameState = useGameStore((state) => state.gameState)
  const currentScene = useCurrentScene()
  const [assetsReady, setAssetsReady] = useState(false)
  const [minTimeElapsed, setMinTimeElapsed] = useState(false)
  
  // 玩家 ref，用于 ShadowCaster 跟随
  const playerRef = useRef<RapierRigidBody | null>(null)
  
  // 预加载资产
  const { ready } = usePreloader(GAME_ASSETS, HAS_ASSETS_TO_LOAD)
  
  // 最小加载时间
  useEffect(() => {
    const timer = setTimeout(() => {
      setMinTimeElapsed(true)
    }, MIN_LOADING_TIME)
    return () => clearTimeout(timer)
  }, [])
  
  // 资产加载完成 + 最小时间已过 = 准备就绪
  useEffect(() => {
    const assetsOk = HAS_ASSETS_TO_LOAD ? ready : true
    if (assetsOk && minTimeElapsed) {
      setAssetsReady(true)
    }
  }, [ready, minTimeElapsed])

  // 判断是否在游戏中（非菜单状态）
  const isPlaying = gameState !== GameState.MENU

  return (
    <>
      {/* 加载界面 */}
      {!assetsReady && <LoadingScreen />}
      
      {/* 场景过渡遮罩 */}
      <TransitionOverlay />
      <SceneLoadingIndicator />
      
      <Canvas shadows>
        <Bvh firstHitOnly>
          <PerspectiveCamera makeDefault position={[0, 10, 20]} fov={60} />
          
          <Suspense fallback={<CanvasLoader />}>
            <Environment />
            {/* 光照：游戏中使用跟随玩家的动态阴影，菜单使用静态光 */}
            <Lighting playerRef={isPlaying ? playerRef : undefined} />
            
            {/* 粒子特效渲染器 */}
            <EffectsRenderer />
            
            {/* 根据当前场景渲染不同内容 */}
            {assetsReady && currentScene === 'game' && <GameScene playerRef={playerRef} />}
            {assetsReady && currentScene === 'level1' && <Level1Scene playerRef={playerRef} />}
            {assetsReady && currentScene === 'level2' && <Level2Scene playerRef={playerRef} />}
            
            {/* 菜单状态下的轨道控制 */}
            {gameState === GameState.MENU && (
              <OrbitControls
                enablePan={false}
                enableZoom={false}
                autoRotate
                autoRotateSpeed={0.5}
                maxPolarAngle={Math.PI / 2.5}
                minPolarAngle={Math.PI / 4}
              />
            )}
          </Suspense>

          <GameControls />
          
          {/* 后处理效果 */}
          {gameState !== GameState.MENU && <GamePostProcessing />}
        </Bvh>
      </Canvas>

      <GameUI />
    </>
  )
}

// ============ 示例关卡场景 ============

// 关卡1 - 基础关卡
function Level1Scene({ playerRef }: { playerRef: React.RefObject<RapierRigidBody | null> }) {
  const updateStats = useGameStore((state) => state.updateStats)
  
  const handleCollect = useCallback((value: number) => {
    updateStats({ score: useGameStore.getState().stats.score + value })
  }, [updateStats])

  return (
    <Physics gravity={[0, -20, 0]} debug={false}>
      <FollowCamera offset={[0, 8, 12]} smoothness={5} />
      <Player ref={playerRef} position={[0, 3, 0]} />
      <Ground size={[50, 50]} />
      
      {/* 关卡1特有布局 */}
      <Platform position={[0, 1, -5]} size={[6, 0.5, 6]} />
      <Platform position={[8, 2, -5]} size={[4, 0.5, 4]} />
      <Platform position={[16, 3, -5]} size={[4, 0.5, 4]} />
      
      <Collectible position={[0, 2, -5]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[8, 3, -5]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[16, 4, -5]} type="gem" value={50} onCollect={handleCollect} />
    </Physics>
  )
}

// 关卡2 - 进阶关卡
function Level2Scene({ playerRef }: { playerRef: React.RefObject<RapierRigidBody | null> }) {
  const updateStats = useGameStore((state) => state.updateStats)
  
  const handleCollect = useCallback((value: number) => {
    updateStats({ score: useGameStore.getState().stats.score + value })
  }, [updateStats])

  return (
    <Physics gravity={[0, -20, 0]} debug={false}>
      <FollowCamera offset={[0, 10, 15]} smoothness={5} />
      <Player ref={playerRef} position={[0, 3, 0]} />
      <Ground size={[80, 80]} />
      
      {/* 关卡2特有布局 - 更复杂的平台 */}
      <Platform position={[-5, 1, 0]} size={[3, 0.5, 3]} />
      <Platform position={[-5, 2.5, -6]} size={[3, 0.5, 3]} />
      <Platform position={[0, 4, -10]} size={[3, 0.5, 3]} />
      <Platform position={[5, 5.5, -10]} size={[3, 0.5, 3]} />
      <Platform position={[10, 7, -6]} size={[4, 0.5, 4]} color="#5a4a3a" />
      
      <Ramp position={[10, 3.5, 0]} rotation={[-0.4, 0, 0]} size={[4, 0.3, 8]} />
      
      <Wall position={[-10, 3, 0]} size={[0.5, 6, 15]} />
      <Wall position={[15, 3, 0]} size={[0.5, 6, 15]} />
      
      <Collectible position={[-5, 2, 0]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[-5, 3.5, -6]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[0, 5, -10]} type="coin" value={10} onCollect={handleCollect} />
      <Collectible position={[5, 6.5, -10]} type="gem" value={50} onCollect={handleCollect} />
      <Collectible position={[10, 8, -6]} type="powerup" value={100} onCollect={handleCollect} />
    </Physics>
  )
}
