# 3D Game Application Template

A modern 3D game development template built with React Three Fiber and optional Express.js backend.

## Tech Stack

**Game Client:** React Three Fiber + Rapier Physics + Miniplex ECS + Zustand + Vite + TypeScript  
**Backend (Optional):** Express.js + TypeScript + PostgreSQL + Prisma ORM

## Project Structure

```
game-3d/
├── game-basic/              # React Three Fiber game client
│   ├── src/
│   │   ├── main.tsx         # Application entry point
│   │   ├── index.css        # Global styles (Tailwind)
│   │   │
│   │   ├── assets/          # Asset management system
│   │   │   ├── AssetLoader.ts   # GLTF/texture preloader
│   │   │   └── useAssets.ts     # React hooks for assets
│   │   │
│   │   ├── engine/          # Core engine
│   │   │   ├── Engine.ts    # Game state machine (Zustand)
│   │   │   ├── Input.ts     # Input system (keyboard/mouse/touch)
│   │   │   └── World.ts     # ECS world (Miniplex)
│   │   │
│   │   ├── entities/        # Game entities
│   │   │   ├── Player.tsx       # Player character (physics capsule)
│   │   │   ├── Environment.tsx  # Environment objects
│   │   │   ├── Interactables.tsx # Collectibles, triggers
│   │   │   └── FollowCamera.tsx # Follow camera
│   │   │
│   │   ├── audio/           # Audio system
│   │   │   ├── AudioStore.ts    # Audio state (volume, mute)
│   │   │   ├── SoundManager.ts  # Howler.js wrapper
│   │   │   ├── SynthSounds.ts   # Tone.js procedural SFX
│   │   │   └── PositionalAudio.tsx # 3D spatial audio
│   │   │
│   │   ├── effects/         # Visual effects
│   │   │   ├── ParticleSystem.tsx  # Particle effects
│   │   │   ├── DamageFlash.tsx     # Screen flash effects
│   │   │   └── PostProcessing.tsx  # Post-processing (bloom, etc.)
│   │   │
│   │   ├── ui/              # User interface
│   │   │   ├── GameUI.tsx       # Game UI (menus, HUD)
│   │   │   ├── SaveManager.tsx  # Save/load UI
│   │   │   └── LoadingScreen.tsx # Asset loading screen
│   │   │
│   │   ├── components/      # Radix UI components
│   │   │   ├── Button.tsx, Dialog.tsx, Slider.tsx, etc.
│   │   │
│   │   ├── storage/         # Save system
│   │   │   ├── StorageService.ts # IndexedDB service
│   │   │   └── SaveSystem.ts    # Save management
│   │   │
│   │   ├── scenes/          # Scene management
│   │   │   ├── SceneStore.ts    # Scene state
│   │   │   ├── SceneRenderer.tsx # Lazy loading
│   │   │   └── SceneTransition.tsx # Transitions
│   │   │
│   │   └── game/            # Game scenes
│   │       └── MainScene.tsx    # Main 3D scene
│   │
│   ├── assets/              # Static assets
│   │   ├── models/          # GLTF/GLB 3D models
│   │   ├── textures/        # Texture images
│   │   └── music/           # Background music
│   │
│   └── public/              # Public assets
│       └── sounds/          # Sound effects
│
├── backend/                 # Optional Express.js API server
│   ├── src/
│   │   ├── modules/         # API modules
│   │   ├── middleware/      # Express middleware
│   │   └── config/          # Configuration
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
npm run dev              # Start Vite dev server (hot reload)
npm run build            # Production build to dist/
npm run preview          # Preview production build
```

### Backend

```bash
npm run dev              # Start development server
npm run build            # Compile TypeScript
npm run prisma:studio    # Open Prisma Studio GUI
```

## Core Features

### Game Client (React Three Fiber)

| Category | Technology |
|----------|------------|
| Renderer | React Three Fiber + Three.js |
| Helpers | Drei (cameras, skybox, BVH acceleration) |
| Physics | Rapier (@react-three/rapier) |
| ECS | Miniplex (entity-component system) |
| State | Zustand |
| Audio | Tone.js (procedural) + Howler.js (BGM) |
| Effects | @react-three/postprocessing + custom particles |
| UI | React + Tailwind CSS + Framer Motion + Radix UI |
| Icons | Lucide React |
| Storage | IndexedDB (idb-keyval) |
| Build | Vite + TypeScript |

### Backend (Optional)

- Express.js + TypeScript
- Prisma ORM for database operations
- RESTful API structure
- Rate limiting & security headers

## Game Architecture

### Scene Flow

```
LoadingScreen → MainMenu → GameScene ⟷ PauseMenu
     ↓              ↓           ↓
[Asset Preload] [Scene Switch] GameOverScene
```

### Asset Structure

```
game-basic/assets/
├── models/          # GLTF/GLB 3D models
├── textures/        # Texture images
└── music/           # Background music

game-basic/public/
└── sounds/          # Sound effects (Howler.js)
```

## AI Development Guide

This template is designed for AI-assisted game development:

1. **Game Design**: AI generates `docs/product/features.md` with mechanics, art style, and requirements
2. **Task Planning**: AI creates `docs/design/todolist.md` with role-based tasks
3. **3D Assets**: AI generates models/textures to `game-basic/assets/`
4. **Code Implementation**: AI implements game logic in `game-basic/src/`

### Key Files for AI

| File | Purpose |
|------|---------|
| `docs/product/features.md` | Game Design Document |
| `docs/design/todolist.md` | Development task checklist |
| `game-basic/src/main.tsx` | Application entry point |
| `game-basic/src/game/MainScene.tsx` | Main 3D scene |
| `game-basic/README.md` | Detailed development guide |

## Environment Configuration

### Game Client

No environment configuration needed. Vite dev server runs on port 5173.

### Backend `.env`

```env
NODE_ENV=development
PORT=3000
DATABASE_URL="postgresql://user:password@localhost:5432/dbname?schema=public"
CORS_ORIGIN=http://localhost:5173
```

## Performance Tips

- Use `<Bvh>` from Drei for raycasting acceleration
- Use InstancedMesh for repeated objects (coins, enemies)
- Implement object pooling for particles/projectiles
- Prefer procedural audio (Tone.js) over audio file loading
- Use simple collision shapes (Capsule, Box) over Trimesh
- Lower shadow-mapSize to 1024 for mobile
- Enable Draco compression for GLTF models

## License

MIT
