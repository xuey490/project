# Game-Basic 项目指南

本文档帮助 AI 工具理解项目结构和各模块职责，以便生成符合规范的代码。

## 项目结构

```
game-basic/
├── assets/              # 游戏资源（图片、音频等）
├── src/
│   ├── main.js          # 入口文件，Phaser 游戏配置
│   ├── config/          # 配置文件
│   │   ├── GameConfig.js    # 游戏常量配置（速度、尺寸等）
│   │   └── StyleConfig.js   # UI 样式配置（颜色、字体、按钮样式）
│   ├── scenes/          # 游戏场景
│   │   └── Start.js         # 示例场景
│   ├── systems/         # 游戏系统（Manager 类）
│   └── utils/           # 工具函数
├── index.html           # HTML 入口
├── phaser.js            # Phaser 引擎（本地引用）
└── webpack.config.js    # 打包配置
```

---

## `src/utils/` - 工具函数

### `layout.js` - 布局工具

| 函数 | 用途 | 参数 | 返回值 |
|------|------|------|--------|
| `calculateGridMetrics(scene, rows, cols, marginTop)` | 计算网格布局（如棋盘、卡牌）的单元格尺寸和起始位置 | `scene`: 场景, `rows/cols`: 行列数, `marginTop`: 顶部边距 | `{ cellSize, startX, startY }` |
| `fitImage(target, maxWidth, maxHeight)` | 将图片等比缩放到指定范围内 | `target`: Image 对象, `maxWidth/maxHeight`: 最大尺寸 | 缩放后的 target |

**使用示例：**
```javascript
import { fitImage, calculateGridMetrics } from '../utils/layout.js';

// 限制图片在 200x200 范围内
fitImage(this.add.image(400, 300, 'hero'), 200, 200);

// 计算 8x8 棋盘布局
const { cellSize, startX, startY } = calculateGridMetrics(this, 8, 8, 100);
```

---

### `audio.js` - 程序化音效系统

`AudioEffects` 类：使用 Web Audio API 生成程序化音效，无需音频文件。

**核心特性：**
- 冷却机制：防止音效过于密集
- 生命周期绑定：场景销毁时自动清理
- 使用 Phaser 时钟：暂停游戏时音效也暂停

**可用音效方法：**

| 方法 | 用途 | 冷却时间 |
|------|------|----------|
| `playButtonClick()` | 按钮点击 | 100ms |
| `playButtonHover()` | 按钮悬停 | 50ms |
| `playTypewriter()` | 打字机（剧情对话） | 80ms |
| `playCollectItem()` | 收集物品 | 50ms |
| `playJump()` | 跳跃 | 200ms |
| `playLanding()` | 着陆 | 300ms |
| `playShoot()` | 射击 | 80ms |
| `playExplosion()` | 爆炸 | - |
| `playVictory()` | 胜利 | - |
| `playLevelUp()` | 升级 | - |
| `playGameOver()` | 游戏结束 | - |
| `playAchievement()` | 成就解锁 | - |
| `playDoorOpen()` | 开门 | - |
| `playDoorClose()` | 关门 | - |
| `playWarning()` | 警告 | - |
| `playMagic()` | 魔法效果 | - |

**注意：** 通常通过 `AudioManager` 调用，而非直接实例化。

---

### `ui.js` - UI 组件工具

基于 **RexUI 插件** 的高级 UI 组件。

**主要导出函数：**

| 函数 | 用途 |
|------|------|
| `createButton(scene, x, y, text, onClick, options)` | 创建 RexUI 按钮，支持图片/纯色背景、悬停/点击状态、防连点 |
| `createDialog(scene, title, content, buttons, options)` | 创建模态对话框 |
| `createStoryTextBox(scene, text, options)` | 创建剧情对话框（打字机效果、头像、自动播放音效） |
| `createFloatingText(scene, x, y, text, options)` | 创建飘字效果（如伤害数字、+100分） |
| `createParticleExplosion(scene, x, y, textureKey, count)` | 创建粒子爆炸效果 |
| `bindRegistryKeys(scene, target, keys, onChange)` | 绑定 registry 数据变化监听，自动解绑 |

**createButton options 常用参数：**
```javascript
{
    fontSize: 24,
    backgroundColor: 0x4CAF50,
    hoverColor: 0x66BB6A,
    pressColor: 0x388E3C,
    cornerRadius: 20,
    padding: { left: 20, right: 20, top: 10, bottom: 10 },
    backgroundImageKey: 'btn_texture',  // 可选：使用图片背景
    clickCooldown: 350,                  // 防连点间隔（ms）
    icon: '🎮'                           // 可选：文字前加图标
}
```

---

## `src/systems/` - 游戏系统

所有 Manager 类都是**单例模式**，在场景 `create()` 中初始化，通过 `scene.registry` 与 UI 同步数据。

### `AudioManager.js` - 音频管理器

**职责：** 统一管理 BGM 和音效的播放、音量、静音状态。

**初始化：**
```javascript
import AudioManager from '../systems/AudioManager.js';

create() {
    this.audioManager = new AudioManager(this);
}
```

**主要方法：**

| 方法 | 用途 |
|------|------|
| `playMusic(key, config)` | 播放 BGM |
| `stopMusic(key)` | 停止 BGM |
| `playButtonClick()` | 播放按钮点击音效 |
| `playButtonHover()` | 播放按钮悬停音效 |
| `playTypewriter()` | 播放打字机音效 |
| `playCollectItem()` | 播放收集音效 |
| `playVictory()` / `playGameOver()` | 播放结算音效 |
| ... | 所有 `AudioEffects` 方法的委托 |

**音量控制：** 由 `SettingsManager` 管理，通过 `scene.registry` 自动同步。

---

### `SettingsManager.js` - 设置管理器

**职责：** 管理游戏设置并持久化到 `localStorage`。

**初始化：**
```javascript
import SettingsManager from '../systems/SettingsManager.js';

create() {
    this.settingsManager = new SettingsManager(this);
}
```

**设置结构：**
```javascript
{
    audio: {
        musicVolume: 0.7,      // 0-1
        sfxVolume: 0.8,        // 0-1
        musicMuted: false,
        sfxMuted: false
    },
    display: {
        fullscreen: false,
        showFPS: false,
        particleEffects: true,
        screenShake: true,
        quality: 'high'        // 'low' | 'medium' | 'high'
    },
    game: {
        difficulty: 'normal',  // 'easy' | 'normal' | 'hard'
        language: 'zh-CN',
        autoSave: true,
        showTutorial: true
    },
    controls: {
        keyboardEnabled: true,
        mouseEnabled: true,
        touchEnabled: true,
        vibration: true
    }
}
```

**主要方法：**

| 方法 | 用途 |
|------|------|
| `get(path)` | 获取设置值，如 `get('audio.musicVolume')` |
| `set(path, value)` | 设置值并自动持久化 |
| `reset()` | 重置为默认值 |

---

### `ScoreManager.js` - 分数管理器

**职责：** 管理分数、等级、连击、成就等统计数据。

**初始化：**
```javascript
import ScoreManager from '../systems/ScoreManager.js';

create() {
    this.scoreManager = new ScoreManager(this);
}
```

**主要方法：**

| 方法 | 用途 |
|------|------|
| `addScore(points)` | 增加分数 |
| `setLevel(level)` | 设置等级 |
| `incrementCombo()` | 增加连击 |
| `resetCombo()` | 重置连击 |
| `reset()` | 重置当前分数（保留最高分） |
| `unlockAchievement(id, name)` | 解锁成就 |

**事件：**
- `score-changed`: 分数变化时触发
- `highscore-changed`: 最高分更新时触发
- `combo-changed`: 连击变化时触发
- `achievement-unlocked`: 成就解锁时触发

---

### `SaveManager.js` - 存档管理器

**职责：** 管理游戏存档的保存和加载（最多 3 个存档槽）。

**初始化：**
```javascript
import SaveManager from '../systems/SaveManager.js';

create() {
    this.saveManager = new SaveManager(this);
}
```

**主要方法：**

| 方法 | 用途 |
|------|------|
| `saveGame(slotId, gameState, saveName)` | 保存到指定槽位 (0-2) |
| `loadGame(slotId)` | 从指定槽位加载 |
| `deleteSave(slotId)` | 删除指定存档 |
| `getAllSaves()` | 获取所有存档信息 |
| `getSaveInfo(slotId)` | 获取单个存档的元信息 |
| `startAutoSave(callback, interval)` | 启动自动保存 |
| `stopAutoSave()` | 停止自动保存 |

**事件：**
- `save-success` / `save-error`
- `load-success` / `load-error`

---

## `src/config/` - 配置文件

### `StyleConfig.js` - UI 样式配置

集中管理所有 UI 样式常量，避免魔法数字。

**主要导出：**

| 常量 | 内容 |
|------|------|
| `COLORS` | 颜色配置（primary, secondary, warning, dialog, story 等） |
| `TYPOGRAPHY` | 字体配置（fontFamily, fontSize, fontWeight, textColor） |
| `BUTTON_STYLES` | 按钮样式（fontSize, cornerRadius, padding, 状态动画） |
| `DIALOG_STYLES` | 对话框样式 |
| `STORY_TEXTBOX_STYLES` | 剧情对话框样式 |
| `FLOATING_TEXT_STYLES` | 飘字样式 |
| `PARTICLE_STYLES` | 粒子效果配置 |

**使用示例：**
```javascript
import { COLORS, BUTTON_STYLES } from '../config/StyleConfig.js';

this.add.rectangle(0, 0, 100, 100, COLORS.primary);
```

### `GameConfig.js` - 游戏常量配置

存放游戏逻辑相关的常量（速度、尺寸、深度层级等）。

**建议内容：**
```javascript
export const DEPTH = {
    BG: 0,
    WORLD: 10,
    PLAYER: 20,
    EFFECTS: 30,
    UI: 50,
    MODAL: 100
};

export const PLAYER = {
    SPEED: 200,
    JUMP_FORCE: -400,
    MAX_HEALTH: 100
};
```

---

## 最佳实践

### 1. 场景初始化模板

```javascript
import AudioManager from '../systems/AudioManager.js';
import SettingsManager from '../systems/SettingsManager.js';
import ScoreManager from '../systems/ScoreManager.js';

export class GameScene extends Phaser.Scene {
    create() {
        // 1. 初始化系统
        this.settingsManager = new SettingsManager(this);
        this.audioManager = new AudioManager(this);
        this.scoreManager = new ScoreManager(this);
        
        // 2. 验证资源
        this.validateAssets();
        
        // 3. 场景淡入
        this.cameras.main.fadeIn(300);
    }
    
    validateAssets() {
        const required = ['player', 'background'];
        const missing = required.filter(key => !this.textures.exists(key));
        if (missing.length > 0) {
            throw new Error(`Missing assets: ${missing.join(', ')}`);
        }
    }
}
```

### 2. 按钮交互标准

```javascript
import { createButton } from '../utils/ui.js';

const btn = createButton(this, 400, 300, '开始游戏', () => {
    this.scene.start('GameScene');
}, {
    backgroundColor: COLORS.primary,
    fontSize: 28
});
```

### 3. 音效播放规范

```javascript
// ✅ 正确：通过 AudioManager
if (this.audioManager) {
    this.audioManager.playButtonClick();
}

// ❌ 错误：直接调用 this.sound
this.sound.play('click');
```

### 4. 数据同步（UI 响应状态变化）

```javascript
import { bindRegistryKeys } from '../utils/ui.js';

// 监听分数变化，自动更新 UI
bindRegistryKeys(this, scoreText, ['score.current'], () => {
    scoreText.setText(`分数: ${this.registry.get('score.current')}`);
});
```
