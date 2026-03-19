# Game Basic

一个基于 Phaser.js 的游戏项目，集成了静态代码分析器用于预防运行时错误。

## 特性

- **Phaser 3.88.2** - 最新稳定版本
- **Webpack** - 现代构建系统，支持热重载
- **静态代码分析器** - 检测常见的JavaScript运行时错误
- **开发工具** - 内置浏览器开发面板，支持代码检查

## 快速开始

```bash
# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build
```

## 项目结构

```
game-basic/                     # Game Basic
├── package.json                # Game dependencies and scripts
├── webpack.config.js           # Webpack configuration
├── index.html                  # Game entry HTML
├── assets/                     # Game assets
│   ├── images/                 # Sprite images, backgrounds, UI elements
│   ├── audio/                  # Sound effects and music files
│   ├── fonts/                  # Custom fonts
│   └── data/                   # JSON data files, level configurations
└── src/
    ├── main.js                 # Game entry point (Phaser.Game initialization)
    ├── config/
    │   ├── gameConfig.js       # Phaser game configuration
    │   └── constants.js        # Game constants and enums
    ├── scenes/
    │   ├── Boot.js             # Boot scene (preload essential assets)
    │   ├── Preloader.js        # Preloader scene (load game assets with progress)
    │   ├── MainMenu.js         # Main menu scene (start, settings, instructions)
    │   ├── Instructions.js     # Tutorial/how-to-play scene
    │   ├── Settings.js         # Settings menu (audio, game options)
    │   ├── GamePlay.js         # Main gameplay scene
    │   ├── Pause.js            # Pause menu scene
    │   ├── GameOver.js         # Game over scene (score, restart options)
    │   └── Victory.js          # Victory/level complete scene (if applicable)
    ├── entities/
    │   ├── Player.js           # Player character class
    │   ├── Enemy.js            # Enemy classes
    │   ├── Collectible.js      # Collectible items
    │   └── Projectile.js       # Bullets, missiles, etc.
    ├── systems/
    │   ├── InputManager.js     # Input handling system
    │   ├── AudioManager.js     # Audio management system (music + SFX volume control)
    │   ├── ScoreManager.js     # Score and statistics system
    │   ├── SettingsManager.js  # Settings persistence and management
    │   └── SaveManager.js      # Save/load game state
    ├── ui/
    │   ├── HUD.js              # Heads-up display
    │   ├── Menu.js             # Menu components
    │   └── Dialog.js           # Dialog and popup systems
    └── utils/
        ├── audio.js            # Audio utility functions
        ├── helpers.js          # Utility functions
        ├── physics.js          # Physics helpers
        ├── animations.js       # Animation utilities
        ├── ui.js               # UI components
        └── layout.js           # Layout tools
        
```

## 开发指南

- 访问属性前总是检查对象：`if (obj && obj.prop)`
- 使用物理体前进行验证：`if (sprite && sprite.body)`
- 使用数组边界检查：`if (arr && arr.length > index)`
- 优先使用可选链：`obj?.prop?.method?.()`
