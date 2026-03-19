# 2D Game Application Template

A modern 2D game development template built with Phaser.js 3 and optional Express.js backend.

## Tech Stack

**Game Client:** Phaser.js 3.88+ + JavaScript + Webpack 5  
**Backend (Optional):** Express.js + TypeScript + PostgreSQL + Prisma ORM

## Project Structure

```
game-2d/
├── game-basic/              # Phaser.js game client
│   ├── src/
│   │   ├── main.js          # Game entry point & Phaser config
│   │   ├── config/          # Game configuration
│   │   │   ├── GameConfig.js    # Game settings (physics, scale)
│   │   │   └── StyleConfig.js   # UI style definitions
│   │   ├── scenes/          # Phaser scenes
│   │   │   └── Start.js     # Initial scene
│   │   ├── systems/         # Game systems
│   │   │   ├── AudioManager.js    # Audio control
│   │   │   ├── SaveManager.js     # Save/Load game state
│   │   │   ├── ScoreManager.js    # Score tracking
│   │   │   └── SettingsManager.js # Game settings
│   │   └── utils/           # Utility functions
│   │       ├── audio.js     # Audio helpers & procedural SFX
│   │       ├── layout.js    # Responsive layout helpers
│   │       └── ui.js        # UI component builders
│   ├── assets/              # Game assets
│   │   ├── images/          # Sprites, backgrounds, UI
│   │   └── audio/           # Sound effects, music
│   ├── index.html           # HTML entry point
│   ├── webpack.config.js    # Webpack configuration
│   └── phaser.js            # Phaser library (local)
│
├── backend/                 # Optional Express.js API server
│   ├── src/
│   │   ├── routes/          # API route definitions
│   │   ├── controllers/     # Request handlers
│   │   └── services/        # Business logic (Prisma)
│   └── prisma/
│       └── schema.prisma    # Database models
│
└── docs/
    └── design/              # Game design documents
        ├── features.md           # Game Design Document
        └── todolist.md      # Development task list
```

## Quick Start

### Prerequisites

- Node.js 18+
- (Optional) PostgreSQL database for backend

### Game Client Setup

```bash
cd game-basic
npm install

# Start development server (port 5173)
npm run dev
```

### Backend Setup (Optional)

```bash
cd backend
npm install

# Configure environment variables
cp .env.example .env
# Edit .env file with PostgreSQL connection details

# Setup database
npm run prisma:generate
npm run prisma:migrate -- --name init

# Start development server (port 3000)
npm run dev
```

## Development Commands

### Game Client

```bash
npm run dev              # Start Webpack dev server (hot reload)
npm run build            # Production build to dist/
npm run lint             # Run code checks
```

### Backend

```bash
npm run dev              # Start development server
npm run build            # Compile TypeScript
npm run prisma:studio    # Open Prisma Studio GUI
```

## Core Features

### Game Client (Phaser.js)

- **Phaser 3.88+**: Modern HTML5 game framework
- **Webpack 5**: Module bundling with hot reload
- **Rex Plugins**: Extended UI components (phaser3-rex-plugins)
- **Built-in Systems**:
  - `AudioManager`: Sound effects and music control
  - `SaveManager`: LocalStorage save/load system
  - `ScoreManager`: Score tracking and high scores
  - `SettingsManager`: Game settings (volume, controls)
- **Procedural Audio**: Code-generated SFX via Web Audio API
- **Responsive Layout**: Adaptive UI helpers

### Backend (Optional)

- Express.js + TypeScript
- Prisma ORM for database operations
- RESTful API structure
- Rate limiting & security headers

## Game Architecture

### Scene Flow

```
BootScene → PreloadScene → MainMenuScene → GameScene ⟷ PauseScene
                                              ↓
                                         GameOverScene
```

### Asset Structure

```
game-basic/assets/
├── images/
│   ├── characters/      # Player, enemies, NPCs
│   ├── environments/    # Backgrounds, tiles, platforms
│   ├── ui/              # Buttons, panels, icons
│   ├── effects/         # Particles, VFX sprites
│   └── collectibles/    # Items, power-ups
└── audio/
    ├── bgm/             # Background music
    └── sfx/             # Sound effects
```

## AI Development Guide

This template is designed for AI-assisted game development:

1. **Game Design**: AI generates `docs/product/features.md` with game mechanics, art style, and requirements
2. **Task Planning**: AI creates `docs/design/todolist.md` with role-based tasks
3. **Art Assets**: AI generates sprites and saves to `game-basic/assets/images/`
4. **Code Implementation**: AI implements game logic in `game-basic/src/`

### Key Files for AI

| File | Purpose |
|------|---------|
| `docs/product/features.md` | Game Design Document |
| `docs/design/todolist.md` | Development task checklist |
| `game-basic/src/main.js` | Game entry point |
| `game-basic/src/scenes/` | Game scenes |
| `game-basic/GUIDE.md` | Detailed development guide |

## Environment Configuration

### Game Client

No environment configuration needed. Webpack dev server runs on port 5173.

### Backend `.env`

```env
NODE_ENV=development
PORT=3000
DATABASE_URL="postgresql://user:password@localhost:5432/dbname?schema=public"
CORS_ORIGIN=http://localhost:5173
```

## Performance Tips

- Use object pooling for bullets, particles, enemies
- Prefer procedural audio over audio file loading
- Use texture atlases for sprites
- Implement scene-based asset loading
- Keep physics bodies simple (Arcade physics preferred)

## License

MIT
