
# 1. 游戏概述 (Game Overview)
-   **游戏名称**：(Name)
-   **核心概念**：(High Concept / Logline)
-   **游戏类型**：(Genre - e.g., 3D Platformer, First-Person, Third-Person, Racing, Puzzle)
-   **独特的吸引点 (Unique Hook)**：(是什么让游戏令人难忘？)
-   **目标平台**：Web (WebGL / WebGPU)
-   **视角模式**：(First-Person / Third-Person / Isometric / Free Camera)

# 2. 核心机制与手感 (Core Mechanics & Game Feel)
-   **游戏循环图示**：(Start -> Play -> Reward -> Loop)
-   **操作控制表 (Control Scheme)**：
    | 动作 | 键位/操作 | 逻辑描述 (Physics/Logic) |
    | :--- | :--- | :--- |
    | 移动 | WASD / 左摇杆 | Rapier KinematicCharacterController / Dynamic Body |
    | 视角 | 鼠标移动 / 右摇杆 | OrbitControls / PointerLockControls |
    | 跳跃 | Space | applyImpulse({ y: jumpForce }) |
    | 交互 | E / 点击 | Raycaster hit detection |
    | ... | ... | ... |
-   **游戏反馈清单 (Game Juice Checklist)**：
    | 触发事件 | 视觉反馈 (Visual) | 音效反馈 (Audio) | 相机效果 (Camera) |
    | :--- | :--- | :--- | :--- |
    | 着地 | 尘土粒子、挤压变形 | 落地音效 | 轻微震动 |
    | 受伤 | 红色闪烁、屏幕泛红 | 受击音效 | 短促抖动 |
    | 收集 | 物品飞向UI、光效 | 收集音效 | 无 |
    | ... | ... | ... | ... |
-   **核心规则**：胜利/失败/计分。

# 3. 游戏对象系统 (Game Objects System)
-   **玩家角色 (Player)**：
    | 属性 | 值 | 说明 |
    | :--- | :--- | :--- |
    | 碰撞体 | Capsule (radius: 0.5, height: 1.8) | 角色胶囊碰撞体 |
    | 质量 | 70 kg | 物理质量 |
    | 移动速度 | 5 m/s | 最大移动速度 |
    | 跳跃力 | 8 | 垂直冲量 |
    | 重力倍率 | 2.5 | 下落加速度倍率 |

-   **障碍物/敌人图鉴 (Enemies & Obstacles)**：
    | 名称 | 3D模型/几何体 | 碰撞体 | 行为模式 (AI) |
    | :--- | :--- | :--- | :--- |
    | 巡逻敌人 | enemy.glb | Capsule | 路径巡逻 + 视野追踪 |
    | 移动平台 | BoxGeometry | Cuboid (Kinematic) | 往返运动 |
    | 旋转障碍 | CylinderGeometry | Cylinder | 恒速旋转 |
    | ... | ... | ... | ... |

-   **道具/收集品 (Collectibles)**：
    | 名称 | 几何体/模型 | 碰撞检测 | 效果 |
    | :--- | :--- | :--- | :--- |
    | 金币 | SphereGeometry | Sensor (触发器) | +10 分 |
    | 生命药水 | potion.glb | Sensor | +1 生命 |
    | ... | ... | ... | ... |

# 4. 3D 环境与关卡 (Environment & Level Design)
-   **场景结构**：
    -   天空盒/环境贴图 (Skybox / Environment Map)
    -   主光源配置 (DirectionalLight / Shadows)
    -   环境光 (AmbientLight / HemisphereLight)
    -   雾效 (Fog / FogExp2)
-   **关卡几何体**：
    | 元素 | 实现方式 | 碰撞体 |
    | :--- | :--- | :--- |
    | 地面 | PlaneGeometry / 模型 | Trimesh / Heightfield |
    | 墙壁 | BoxGeometry | Cuboid |
    | 斜坡 | 自定义几何 | Trimesh |
    | ... | ... | ... |
-   **难度曲线**：
-   **存档系统**：(localStorage / IndexedDB)

# 5. 美术与音效需求 (Art & Audio Assets)
-   **美术风格支柱 (Art Pillar)**：(Low-Poly / Stylized / Realistic / Voxel / Toon Shader)
-   **光照氛围**：(明亮欢快 / 阴暗诡异 / 黄昏温暖)
-   **材质风格**：(MeshToonMaterial / MeshStandardMaterial / Custom Shader)
-   **资源清单 (Asset List)**：

    **3D 模型 (优先使用 GLTF/GLB)**:
    | 资源名 | 格式 | 多边形预算 | 动画 |
    | :--- | :--- | :--- | :--- |
    | player.glb | GLTF | < 5K tris | idle, run, jump |
    | ... | ... | ... | ... |

    **纹理贴图 (优先程序化生成)**:
    | 资源名 | 分辨率 | 类型 | 实现方式 | 备注 |
    | :--- | :--- | :--- | :--- | :--- |
    | ground_pattern | 1024x1024 | Diffuse | **程序化** | 噪声函数生成 |
    | brick_texture.jpg | 1024x1024 | Diffuse | **图片** | 复杂砖纹，需真实感 |
    | ... | ... | ... | ... | ... |
    
    > **Note**: 纹理优先使用 **CanvasTexture / Shader / 噪声函数** 程序化生成。仅复杂有机纹理（如照片级材质）才请求图片文件。

    **2D UI 素材 (仅当 CSS 无法实现时)**:
    | 资源名 | 分辨率 | 用途 | 为何需要图片 |
    | :--- | :--- | :--- | :--- |
    | bg_mainmenu.png | 1920x1080 | **开始页背景图 (REQUIRED)** | 营造游戏氛围，CSS 无法实现 |
    | icon_item_*.png | 64x64 | 道具/物品图标 | 复杂手绘风格，需视觉辨识度 |
    | ... | ... | ... | ... |
    
    > **Note**: 
    > - **道具/物品图标**：建议生成图片，确保视觉辨识度和游戏风格一致
    > - **UI 元素（金币、生命值、按钮等）**：应用 **CSS/SVG/React 组件** 绘制
    > - **开始页背景图 (bg_mainmenu.png)**：必需资源，用于营造游戏第一印象

    **音效 (优先使用程序化生成)**:
    | 音效 | 实现方式 | 备注 |
    | :--- | :--- | :--- |
    | UI 点击 | Tone.js (程序化) | 短促正弦波 |
    | 脚步声 | Howler.js (音频文件) | 需要真实感 |
    | BGM | Howler.js (音频文件) | 循环播放 |

# 6. 场景与关卡设计 (Scene & Level Design)
-   **场景列表**：
    | 场景ID | 名称 | 类型 | 预加载 |
    | :--- | :--- | :--- | :--- |
    | menu | 主菜单 | UI场景 | 是 |
    | game | 主游戏场景 | 游戏场景 | 是 |
    | level1 | 关卡1 | 游戏场景 | 否 |
    | ... | ... | ... | ... |
-   **场景切换动画**：(fade / slide / zoom / glitch / none)
-   **场景间数据传递**：(分数、玩家状态、解锁进度)

# 7. 存档系统设计 (Save System Design)
-   **存储方案**：IndexedDB (idb-keyval)
-   **存档槽位数量**：(3-5 个手动存档 + 1 自动存档 + 1 快速存档)
-   **存档数据结构**：
    ```typescript
    interface SaveData {
      playerPosition: [number, number, number]
      health: number
      score: number
      level: string
      inventory: string[]
      timestamp: number
    }
    ```
-   **自动存档触发点**：(关卡切换、检查点、定时)

# 8. UI/UX 设计 (Interface)
-   **UI 技术栈**：React + Tailwind CSS + Framer Motion + Radix UI
-   **HUD 布局**：
    ```
    ┌─────────────────────────────────────┐
    │ [生命值]              [得分: 0000] │
    │                                     │
    │                                     │
    │              [准星]                 │
    │                                     │
    │                                     │
    │ [物品栏]              [小地图]     │
    └─────────────────────────────────────┘
    ```
-   **场景流转**：
    ```
    LoadingScreen → MainMenu → GameScene ⟷ PauseMenu
         ↓              ↓           ↓
    [资产预加载]   [场景切换]   GameOverScene
    ```
-   **加载界面设计**：
    -   进度条样式与游戏风格一致
    -   加载提示文字（加载模型、加载纹理...）
    -   可选：加载动画、游戏提示

# 9. 技术实现方案 (Technical Implementation Scheme)
-   **渲染框架**：React Three Fiber (@react-three/fiber) + Three.js
-   **辅助库**：Drei (@react-three/drei) - 相机、天空盒、BVH 加速等
-   **物理引擎**：Rapier (@react-three/rapier)
-   **ECS 架构**：Miniplex (实体组件系统)
-   **状态管理**：Zustand (游戏状态 & 音频状态)
-   **音频系统**：Tone.js (程序化合成) + Howler.js (BGM)
-   **特效系统**：@react-three/postprocessing (后处理) + 自定义粒子
-   **UI 方案**：React + Tailwind CSS + Framer Motion + Radix UI
-   **存档系统**：IndexedDB (idb-keyval)
-   **构建工具**：Vite + TypeScript
-   **性能预算**：
    | 指标 | 目标值 |
    | :--- | :--- |
    | 帧率 | 60 FPS (桌面) / 30 FPS (移动) |
    | Draw Calls | < 100 |
    | 总多边形 | < 100K tris |
    | 纹理内存 | < 256 MB |
