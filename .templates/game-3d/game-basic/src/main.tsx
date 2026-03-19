import React from 'react'
import ReactDOM from 'react-dom/client'
import { MainScene } from './game'
import { useGameStore } from './engine'
import { useSceneStore } from './scenes'
import { onSaveLoaded, onBeforeSave, useSaveSystem, type GameSave } from './storage'
import './index.css'

// 注册保存前同步回调 - 从游戏状态同步到存档
onBeforeSave(() => {
  const gameStore = useGameStore.getState()
  const sceneStore = useSceneStore.getState()
  const saveSystem = useSaveSystem.getState()
  
  // 同步统计数据到存档
  saveSystem.updateStats({
    score: gameStore.stats.score,
    kills: gameStore.stats.kills,
    deaths: gameStore.stats.deaths,
    totalPlaytime: gameStore.stats.playtime,
  })
  
  // 同步当前场景到存档
  saveSystem.updateWorld({
    currentScene: sceneStore.currentScene,
  })
  
  console.log('[SaveSystem] 保存前同步完成, score:', gameStore.stats.score)
})

// 注册存档加载回调 - 同步存档数据到游戏状态
onSaveLoaded((save: GameSave) => {
  const gameStore = useGameStore.getState()
  const sceneStore = useSceneStore.getState()
  
  // 同步统计数据
  gameStore.updateStats({
    score: save.stats.score,
    kills: save.stats.kills,
    deaths: save.stats.deaths,
    playtime: save.stats.totalPlaytime,
  })
  
  // 同步场景
  if (save.world.currentScene) {
    sceneStore.switchScene(save.world.currentScene as any, { transition: 'fade', duration: 300 })
  }
  
  console.log('[SaveSystem] 存档已加载并同步:', save.name, 'score:', save.stats.score)
})

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <MainScene />
  </React.StrictMode>
)
