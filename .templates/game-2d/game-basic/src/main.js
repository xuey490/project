import Phaser from 'phaser';
import UIPlugin from 'phaser3-rex-plugins/templates/ui/ui-plugin.js';
import { Loading } from './scenes/Loading.js';
import { TestPage } from './scenes/TestPage.js';

const config = {
    type: Phaser.AUTO,
    title: 'Genie Game',
    description: 'AI 正在为您创作精彩游戏...',
    parent: 'game-container',
    width: 1280,
    height: 720,
    backgroundColor: '#1a0a2e',
    pixelArt: false,
    scene: [
        Loading,   // 默认加载等待页面
        TestPage   // 测试页面（可通过 scene.start('TestPage') 跳转）
    ],
    plugins: {
        scene: [{
            key: 'rexUI',
            plugin: UIPlugin,
            mapping: 'rexUI'
        }]
    },
    scale: {
        mode: Phaser.Scale.FIT,
        autoCenter: Phaser.Scale.CENTER_BOTH
    }
}

new Phaser.Game(config);
            