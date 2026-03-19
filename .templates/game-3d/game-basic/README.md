# 3D Game Template

基于 React Three Fiber 的 3D 游戏标准模板工程。

## 技术栈

| 分类 | 技术 |
|------|------|
| 渲染引擎 | React Three Fiber + Three.js |
| 辅助库 | Drei (相机、天空盒、BVH加速等) |
| 物理引擎 | Rapier (@react-three/rapier) |
| ECS架构 | Miniplex |
| 状态管理 | Zustand |
| 音频系统 | Tone.js (程序化合成) + Howler.js (BGM) |
| 特效系统 | @react-three/postprocessing (后处理) + 自定义粒子 |
| UI框架 | React + Tailwind CSS + Framer Motion |
| UI组件库 | Radix UI + class-variance-authority |
| 图标库 | Lucide React |
| 存档系统 | IndexedDB (自动降级 localStorage) |
| 构建工具 | Vite + TypeScript |

## 项目结构

```
src/
├── main.tsx              # 应用入口
├── index.css             # 全局样式
│
├── utils/                # 🔧 通用工具库
│   ├── index.ts          # 统一导出
│   ├── cn.ts             # 类名合并 (clsx)
│   ├── math.ts           # 数学工具 (lerp/clamp/random/distance等)
│   ├── time.ts           # 时间工具 (throttle/debounce/delay等)
│   ├── format.ts         # 格式化工具 (uuid/formatPlaytime等)
│   └── useLightTarget.tsx # 光源跟随工具 (动态阴影)
│
├── assets/               # 📦 资产管理系统
│   ├── AssetLoader.ts    # 资产加载器（GLTF/纹理预加载）
│   ├── useAssets.ts      # React Hooks 封装
│   └── index.ts
│
├── engine/               # ⚙️ 引擎核心
│   ├── Engine.ts         # 游戏引擎（状态机、Zustand Store）
│   ├── Input.ts          # 输入系统（键盘/鼠标/手柄）
│   ├── World.ts          # ECS 世界（Miniplex 实体管理）
│   └── index.ts
│
├── entities/             # 🎮 游戏实体
│   ├── Player.tsx        # 玩家角色（物理胶囊、动画系统）
│   ├── Environment.tsx   # 环境物体（地面、平台、墙壁、斜坡）
│   ├── Interactables.tsx # 可交互物体（收集品、箱子）
│   ├── FollowCamera.tsx  # 跟随相机（鼠标控制视角）
│   └── index.ts
│
├── systems/              # 🔄 游戏系统
│   ├── GameSystems.ts    # 核心系统逻辑
│   ├── Controls.tsx      # 输入控制绑定
│   └── index.ts
│
├── audio/                # 🔊 音频系统
│   ├── AudioStore.ts     # 音频状态（音量、静音）
│   ├── SoundManager.ts   # 音效管理器（Howler.js 封装）
│   ├── SynthSounds.ts    # 程序化音效生成（Tone.js 合成器）
│   ├── PositionalAudio.tsx # 3D空间音效组件
│   └── index.ts
│
├── effects/              # ✨ 特效系统
│   ├── ParticleSystem.tsx # 粒子特效（收集、尘土、冲击）
│   ├── DamageFlash.tsx   # 受伤闪烁效果
│   ├── PostProcessing.tsx # 后处理（泛光、色差、晕影）
│   └── index.ts
│
├── storage/              # 💾 存档系统
│   ├── StorageService.ts # 存储服务（IndexedDB + localStorage）
│   ├── SaveSystem.ts     # 存档管理（自动存档、快速存档）
│   ├── useAutoSave.ts    # 自动存档 Hook
│   └── index.ts
│
├── scenes/               # 🎬 场景管理
│   ├── SceneStore.ts     # 场景状态管理
│   ├── SceneRenderer.tsx # 场景渲染器
│   ├── SceneTransition.tsx # 过渡动画
│   └── index.ts
│
├── ui/                   # 🖥️ 用户界面
│   ├── GameUI.tsx        # 游戏UI（菜单、HUD、设置）
│   ├── SaveManager.tsx   # 存档管理界面
│   ├── LoadingScreen.tsx # 资产加载界面
│   └── index.ts
│
├── components/           # 🧩 通用UI组件
│   ├── Button.tsx        # 按钮（支持变体、音效）
│   ├── Switch.tsx        # 开关
│   ├── Slider.tsx        # 滑块
│   ├── Progress.tsx      # 进度条
│   ├── Dialog.tsx        # 对话框
│   ├── Card.tsx          # 卡片
│   ├── Tooltip.tsx       # 提示框
│   ├── Input.tsx         # 输入框
│   ├── Label.tsx         # 标签
│   ├── Separator.tsx     # 分隔线
│   └── index.ts
│
└── game/                 # 🎯 游戏场景
    ├── MainScene.tsx     # 主场景（Canvas、光照、BVH）
    └── index.ts
```

## 工具库 (utils)

模板提供了丰富的工具函数，可直接在项目中使用：

### 数学工具 (math.ts)

```typescript
import { 
  random, randomInt, randomElement,  // 随机数
  lerp, clamp, mapRange,             // 插值/限制
  degToRad, radToDeg,                // 角度转换
  distance2D, distance3D,            // 距离计算
  smoothDamp,                        // 平滑阻尼
  angleDiff, normalizeAngle,         // 角度处理
} from '@/utils'

// 随机位置
const x = random(-10, 10)
const index = randomInt(0, items.length - 1)
const color = randomElement(['red', 'blue', 'green'])

// 平滑插值
const smoothed = lerp(current, target, 0.1)
const clamped = clamp(value, 0, 100)

// 距离检测
if (distance3D(player.x, player.y, player.z, enemy.x, enemy.y, enemy.z) < 5) {
  // 进入攻击范围
}
```

### 时间工具 (time.ts)

```typescript
import { 
  delay,                    // 异步延迟
  throttle, debounce,       // 节流/防抖
  createInterval,           // 带暂停的定时器
  createTimer,              // 性能计时
  createFrameLimiter,       // 帧率限制
} from '@/utils'

// 异步等待
await delay(1000)

// 节流输入（每100ms最多执行一次）
const handleInput = throttle((value) => {
  console.log(value)
}, 100)

// 防抖搜索（停止输入300ms后执行）
const handleSearch = debounce((query) => {
  search(query)
}, 300)

// 可暂停的定时器
const timer = createInterval(() => {
  console.log('tick')
}, 1000)
timer.start()
timer.stop()

// 性能计时
const perf = createTimer()
perf.start()
// ... 执行代码
console.log(`耗时: ${perf.elapsed()}ms`)
```

### 格式化工具 (format.ts)

```typescript
import { 
  uuid, shortId,                           // ID生成
  formatPlaytime, formatPlaytimeCN,        // 游戏时间
  formatRelativeTime,                      // 相对时间
  formatNumber, formatCompactNumber,       // 数字格式化
  formatPercent, formatFileSize,           // 百分比/文件大小
  truncate, capitalize,                    // 字符串处理
  toKebabCase, toCamelCase,               // 命名转换
} from '@/utils'

// ID 生成
const id = uuid()           // 'a1b2c3d4-...'
const short = shortId()     // 'x7k9m2'

// 游戏时间显示
formatPlaytime(3661)        // '1:01:01'
formatPlaytimeCN(3661)      // '1小时 1分'

// 相对时间
formatRelativeTime(Date.now() - 60000)  // '1分钟前'

// 大数字
formatCompactNumber(1500000)  // '1.5M'

// 文件大小
formatFileSize(1024 * 1024)   // '1.00 MB'
```

### 类名合并 (cn.ts)

```typescript
import { cn } from '@/utils'

// 条件类名
<div className={cn(
  'base-class',
  isActive && 'active',
  { 'hover': isHovered, 'disabled': isDisabled }
)} />
```

### 光源跟随工具 (useLightTarget.tsx)

解决两个常见痛点：
1. **自动添加 target 到场景** - Three.js 要求 `light.target` 必须在 scene graph 中才能生效，AI 经常忘记
2. **性能优化** - 利用 Three.js 父子层级关系，无需 `useFrame` 疯狂更新矩阵

```typescript
import { ShadowCaster } from '@/utils'

// 自动跟随玩家的阴影光
// AI 只需要使用这个组件，完全不需要写 useFrame
<ShadowCaster 
  targetRef={playerRef}           // 玩家 RigidBody 的 ref
  positionOffset={[10, 15, 10]}   // 光源相对玩家的偏移
  shadowMapSize={1024}            // 阴影贴图尺寸
  intensity={1.2}                 // 光照强度
/>

// 底层自动处理：
// 1. 移动整个光源容器 (Group)，而非更新 target
// 2. 光照方向保持不变，阴影范围跟随玩家
// 3. 绝不手动调用 updateMatrixWorld()
```

## 架构设计

### 状态管理 (Zustand)

```typescript
// 游戏状态
const gameState = useGameStore((state) => state.gameState)
const { startGame, pauseGame, resumeGame } = useGameStore()

// 音频状态
const { musicVolume, sfxVolume, muted } = useAudioStore()
```

### ECS 实体组件系统 (Miniplex)

```typescript
// 创建实体
const entity = world.createEntity({
  position: new Vector3(0, 0, 0),
  velocity: new Vector3(),
  health: 100,
})

// 查询实体
const players = world.with('player', 'position', 'velocity')
```

### 物理系统 (Rapier)

```typescript
<Physics gravity={[0, -20, 0]}>
  <RigidBody type="dynamic" colliders="hull">
    <mesh>...</mesh>
  </RigidBody>
</Physics>
```

### 音频系统

```typescript
// 播放音效
const { play } = useGameSound('jump')
play()

// 程序化音效
synthSounds.playJump()
synthSounds.playCollect()
```

### 粒子特效系统

```typescript
import { useEffects, EffectsRenderer } from '@/effects'

const { spawnCollectEffect, spawnDustEffect, spawnImpactEffect } = useEffects()

// 收集物品时触发星星爆发
spawnCollectEffect([x, y, z])

// 跳跃/落地时触发尘土
spawnDustEffect([x, y, z])

// 受伤时触发冲击粒子
spawnImpactEffect([x, y, z])

// 在 Canvas 中添加渲染器
<EffectsRenderer />
```

### 后处理效果

```typescript
import { GamePostProcessing, usePostProcessing } from '@/effects'

const { triggerGlitch, setBloom } = usePostProcessing()

// 受伤时触发故障效果
triggerGlitch(0.3)

// 调整泛光强度
setBloom({ bloomIntensity: 0.8 })

// 在 Canvas 中添加
<GamePostProcessing />
```

### 资产管理系统

```typescript
// 定义资产清单
const GAME_ASSETS: AssetManifest = {
  player: { type: 'gltf', path: '/models/player.glb' },
  enemy: { type: 'gltf', path: '/models/enemy.glb', useDraco: true },
  groundTex: { type: 'texture', path: '/textures/ground.jpg' },
}

// 预加载资产
const { ready } = usePreloader(GAME_ASSETS)

// 使用模型
const { scene, animations } = useModel('player', { clone: true })

// 使用纹理
const texture = useGameTexture('groundTex', { 
  wrapS: THREE.RepeatWrapping,
  repeat: [10, 10]
})
```

### 场景管理系统（多关卡）

```typescript
// 切换场景（支持过渡动画）
const { switchScene, goBack } = useSceneSwitch()

await switchScene('level1', { 
  transition: 'fade',  // fade | slide | zoom | glitch | none
  duration: 500,
  data: { levelIndex: 1 }  // 场景间数据传递
})

// 返回上一场景
await goBack({ transition: 'slide' })

// 获取当前场景
const currentScene = useCurrentScene()
```

### 存档管理系统

```typescript
import { useSaveSystem, storage } from '@/storage'

const { 
  savesList,       // 所有存档列表
  currentSaveId,   // 当前存档ID
  createSave,      // 创建存档
  loadSave,        // 加载存档
  deleteSave,      // 删除存档
  quickSave,       // 快速存档 (F5)
  quickLoad,       // 快速加载 (F9)
} = useSaveSystem()

// 底层存储服务（自动选择 IndexedDB 或 localStorage）
await storage.set('key', { data: 'value' })
const data = await storage.get('key')
```

## 快速开始

```bash
# 安装依赖
npm install

# 开发模式
npm run dev

# 构建
npm run build

# 预览构建结果
npm run preview
```

## 性能优化

- **BVH 加速**: 使用 Drei 的 `<Bvh>` 组件加速光线投射
- **资产预加载**: 支持 GLTF/纹理批量预加载，带进度显示
- **Draco 压缩**: 支持 Draco 压缩模型加载
- **场景懒加载**: 支持按需加载场景，减少初始包体积
- **阴影优化**: 降低 shadow-mapSize 至 1024
- **静态物体**: 移除不必要的 `useFrame` 调用
- **几何体优化**: 减少多边形细分数量
- **程序化音效**: 使用 Tone.js 合成音效，无需加载音频文件
- **合成器池化**: 音效系统使用对象池，避免频繁创建/销毁

## 扩展指南

### 添加新实体

1. 在 `src/entities/` 创建新组件
2. 使用 `RigidBody` 添加物理碰撞
3. 在 `index.ts` 中导出

### 添加新系统

1. 在 `src/systems/` 创建系统逻辑
2. 使用 `useFrame` 进行每帧更新
3. 通过 Zustand 或 Miniplex 管理状态

### 添加新场景/关卡

```typescript
// 1. 在 MainScene.tsx 中添加场景组件
function Level3Scene() {
  return (
    <Physics gravity={[0, -20, 0]}>
      <FollowCamera />
      <Player position={[0, 3, 0]} />
      <Ground size={[100, 100]} />
      {/* 关卡布局 */}
    </Physics>
  )
}

// 2. 在 MainScene 中注册场景渲染
{currentScene === 'level3' && <Level3Scene />}

// 3. 在 GameUI 菜单中添加入口
<CyberButton onClick={() => handleStartGame('level3')}>
  关卡 3
</CyberButton>
```

### 添加新音效

```typescript
// 在 SynthSounds.ts 中添加（使用预创建的合成器池）
playNewSound(volume = 0.5) {
  if (!this.initialized || !this.generalSynths) return

  const synth = this.getFromPool(this.generalSynths)
  synth.set({
    oscillator: { type: 'sine' },
    envelope: { attack: 0.01, decay: 0.2, sustain: 0, release: 0.1 },
  })
  synth.volume.value = Tone.gainToDb(volume * 0.3)
  synth.triggerAttackRelease('C5', '8n')
}
```

## License

MIT
