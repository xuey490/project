import Phaser from 'phaser';
import { createButton, createDialog, createStoryTextBox, createScrollableList, bindRegistryKeys } from '../utils/ui.js';
import AudioManager from '../systems/AudioManager.js';
import ScoreManager from '../systems/ScoreManager.js';
import SettingsManager from '../systems/SettingsManager.js';
import SaveManager from '../systems/SaveManager.js';

export class TestPage extends Phaser.Scene {

    constructor() {
        super('TestPage');
    }

    preload() {
        this.load.image('background', 'assets/tmp/images/space.png');
        this.load.image('logo', 'assets/tmp/images/phaser.png');

        //  The ship sprite is CC0 from https://ansimuz.itch.io - check out his other work!
        this.load.spritesheet('ship', 'assets/tmp/images/spaceship.png', { frameWidth: 176, frameHeight: 96 });

        // 首页按钮背景图（你已放在 assets/ui）
        this.load.image('panel_brown_damaged', 'assets/tmp/images/ui/panel_brown_damaged.png');
        
        // 加载剧情对话框所需资源
        this.load.image('characterAvatar', 'https://raw.githubusercontent.com/rexrainbow/phaser3-rex-notes/master/assets/images/person.png');
        this.load.image('nextPageIcon', 'https://raw.githubusercontent.com/rexrainbow/phaser3-rex-notes/master/assets/images/arrow-down-left.png');
    }

    create() {
        // 初始化所有系统管理器
        this.initManagers();

        this.background = this.add.tileSprite(640, 360, 1280, 720, 'background');

        const logo = this.add.image(640, 200, 'logo');

        const ship = this.add.sprite(640, 360, 'ship');

        // 安全的动画创建和播放
        if (ship && ship.anims && typeof ship.anims.create === 'function') {
            ship.anims.create({
                key: 'fly',
                frames: this.anims.generateFrameNumbers('ship', { start: 0, end: 2 }),
                frameRate: 15,
                repeat: -1
            });
        }

        if (ship && typeof ship.play === 'function') {
            ship.play('fly');
        }

        this.tweens.add({
            targets: logo,
            y: 180,
            duration: 1500,
            ease: 'Sine.inOut',
            yoyo: true,
            loop: -1
        });

        // 创建按钮
        this.createButtons();
        
        // 创建系统管理器测试面板
        this.createManagerTestPanel();
    }

    initManagers() {
        console.log('🚀 初始化系统管理器...');

        // 设置管理器（作为所有设置的唯一来源，包含 audio 设置）
        this.settingsManager = new SettingsManager(this);
        console.log('✅ SettingsManager 已初始化');

        // 音频管理器（从 settings.audio.* 读取/监听）
        this.audioManager = new AudioManager(this);
        console.log('✅ AudioManager 已初始化');

        // 分数管理器（支持 events + registry）
        this.scoreManager = new ScoreManager(this);

        // 用 registry 监听分数/等级变化（避免依赖各系统自带 events）
        const reg = this.registry;
        const regEvents = reg?.events;

        const onScoreCurrentChanged = (parent, value, previous) => {
            if (typeof previous === 'number') {
                const delta = value - previous;
                console.log(`💰 分数变化: +${delta} = ${value}`);
            } else {
                console.log(`💰 当前分数: ${value}`);
            }
        };

        const onScoreLevelChanged = (parent, value) => {
            console.log(`🎊 升级到 ${value} 级！`);
            this.audioManager.playLevelUp();
        };

        if (regEvents) {
            regEvents.on('changedata-score.current', onScoreCurrentChanged);
            regEvents.on('changedata-score.level', onScoreLevelChanged);
        }

        // 成就目前没有同步到 registry，仍通过 ScoreManager.events 处理（用于音效/日志）
        this.scoreManager.events.on('achievement-unlocked', ({ name }) => {
            console.log(`🏆 解锁成就: ${name}`);
            this.audioManager.playAchievement();
        });

        // 场景结束时解绑 registry 监听，避免重复注册
        if (this.events && typeof this.events.once === 'function') {
            this.events.once('shutdown', () => {
                if (regEvents) {
                    regEvents.off('changedata-score.current', onScoreCurrentChanged);
                    regEvents.off('changedata-score.level', onScoreLevelChanged);
                }
            });
        }

        console.log('✅ ScoreManager 已初始化');

        // 用 registry 监听 settings.* 变化（比打印整个 settings 更轻量）
        const onAnyRegistryChange = (parent, key, value, previous) => {
            if (typeof key === 'string' && key.startsWith('settings.')) {
                console.log(`⚙️ 设置已更新: ${key} = ${value} (prev: ${previous})`);
            }
        };

        if (regEvents) {
            regEvents.on('changedata', onAnyRegistryChange);
        }

        if (this.events && typeof this.events.once === 'function') {
            this.events.once('shutdown', () => {
                if (regEvents) {
                    regEvents.off('changedata', onAnyRegistryChange);
                }
            });
        }

        // 存档管理器（events）
        this.saveManager = new SaveManager(this);
        this.saveManager.events.on('save-success', ({ slotId }) => {
            console.log(`💾 存档 ${slotId} 保存成功！`);
        });
        this.saveManager.events.on('load-success', ({ slotId, gameState }) => {
            console.log(`📂 存档 ${slotId} 加载成功！`);

            // 让"加载存档"影响运行时分数（否则分数测试仍读旧值）
            if (gameState && this.scoreManager && typeof this.scoreManager.applyGameState === 'function') {
                this.scoreManager.applyGameState(gameState);
            }
        });
        console.log('✅ SaveManager 已初始化');

        console.log('🎉 所有系统管理器初始化完成！');
    }

    createButtons() {
        console.log('开始创建按钮...');
        
        // 按钮1: 开始游戏
        const startButton = createButton(
            this, 
            640, 
            500, 
            '开始游戏', 
            () => {
                console.log('开始游戏被点击!');
                this.startGame();
            },
            {
                backgroundImageKey: 'panel_brown_damaged',
                backgroundImageTint: 0xffffff,
                hoverImageTint: 0xffffff,
                pressImageTint: 0xdddddd,

                // 米白字（固定色值，不做自动对比色判断）
                textColor: '#F7E7D2',
                textStrokeColor: '#2a1a10',
                textStrokeThickness: 3,
                textShadowColor: '#000000',

                // 纯色兜底（万一图片未加载成功）
                backgroundColor: 0x4CAF50,
                hoverColor: 0x66BB6A,
                pressColor: 0x388E3C,

                icon: '🎮',
                fontSize: 20,
                padding: { left: 26, right: 26, top: 18, bottom: 18 }
            }
        );

        // 按钮2: 设置
        const settingsButton = createButton(
            this, 
            440, 
            580, 
            '设置', 
            () => {
                console.log('设置被点击!');
                this.showSettings();
            },
            {
                backgroundImageKey: 'panel_brown_damaged',
                backgroundImageTint: 0xffffff,
                hoverImageTint: 0xffffff,
                pressImageTint: 0xdddddd,

                // 米白字（固定色值，不做自动对比色判断）
                textColor: '#F7E7D2',
                textStrokeColor: '#2a1a10',
                textStrokeThickness: 3,
                textShadowColor: '#000000',

                // 纯色兜底（万一图片未加载成功）
                backgroundColor: 0x2196F3,
                hoverColor: 0x42A5F5,
                pressColor: 0x1976D2,

                icon: '⚙️',
                fontSize: 18,
                padding: { left: 22, right: 22, top: 16, bottom: 16 }
            }
        );

        // 按钮3: 关于
        const aboutButton = createButton(
            this, 
            840, 
            580, 
            '关于', 
            () => {
                console.log('关于被点击!');
                this.showAbout();
            },
            {
                backgroundColor: 0xFF9800,
                hoverColor: 0xFFB74D,
                pressColor: 0xF57C00,
                icon: 'ℹ️',
                fontSize: 18
            }
        );

        // 新增：测试对话框按钮
        const testDialogButton = createButton(
            this,
            540,
            650,
            '测试对话框',
            () => {
                console.log('测试对话框被点击!');
                this.showTestDialog();
            },
            {
                backgroundColor: 0x9C27B0,
                hoverColor: 0xBA68C8,
                pressColor: 0x7B1FA2,
                icon: '💬',
                fontSize: 16
            }
        );

        // 新增：测试剧情对话框按钮
        const testStoryButton = createButton(
            this,
            740,
            650,
            '测试剧情',
            () => {
                console.log('测试剧情对话框被点击!');
                this.showTestStory();
            },
            {
                backgroundColor: 0xE91E63,
                hoverColor: 0xF06292,
                pressColor: 0xC2185B,
                icon: '📖',
                fontSize: 16
            }
        );

        // 保存按钮引用
        this.buttons = {
            start: startButton,
            settings: settingsButton,
            about: aboutButton,
            testDialog: testDialogButton,
            testStory: testStoryButton
        };
        
        console.log('按钮创建完成:', this.buttons);
    }

    startGame() {
        // 创建开始游戏的确认对话框
        createDialog(
            this,
            '开始游戏',
            '准备好开始你的太空冒险了吗？',
            [
                {
                    text: '确定',
                    callback: () => {
                        console.log('确认开始游戏');
                        // 这里可以切换到游戏场景
                        // this.scene.start('GamePlay');
                    }
                },
                {
                    text: '取消',
                    callback: () => {
                        console.log('取消开始游戏');
                    }
                }
            ]
        );
    }

    showSettings() {
        // 创建设置对话框
        createDialog(
            this,
            '游戏设置',
            '在这里可以调整音量、画质等设置。\n\n音效音量: 70%\n音乐音量: 50%\n画质: 高',
            [
                {
                    text: '保存',
                    callback: () => {
                        console.log('保存设置');
                    }
                },
                {
                    text: '取消',
                    callback: () => {
                        console.log('取消设置');
                    }
                }
            ]
        );
    }

    showAbout() {
        // 创建关于对话框
        createDialog(
            this,
            '关于游戏',
            '太空冒险游戏 v1.0\n\n使用 Phaser 3 开发\n\n感谢您的游玩！',
            [
                {
                    text: '确定',
                    callback: () => {
                        console.log('关闭关于对话框');
                    }
                }
            ]
        );
    }

    showTestDialog() {
        // 测试 RexUI 对话框的各种特性
        createDialog(
            this,
            '🎉 RexUI 对话框测试',
            '这是使用 Phaser3-Rex-Plugins 创建的对话框！\n\n✨ 自动布局\n🎨 现代设计\n🔊 集成音效\n💫 平滑动画\n\n点击按钮或遮罩关闭',
            [
                {
                    text: '太棒了！',
                    callback: () => {
                        console.log('用户喜欢 RexUI！');
                    }
                },
                {
                    text: '再看看',
                    callback: () => {
                        console.log('用户想再看看');
                    }
                },
                {
                    text: '关闭',
                    callback: () => {
                        console.log('关闭测试对话框');
                    }
                }
            ]
        );
    }

    showTestStory() {
        // 测试剧情对话框（带打字机效果）
        const storyContent = `[b]欢迎来到太空冒险！[/b]

这是一个使用 [color=yellow]RexUI TextBox[/color] 组件实现的剧情对话框。

它支持：
✨ [color=cyan]打字机效果[/color]
📄 [color=lime]自动分页[/color]
🎨 [color=orange]BBCode 富文本[/color]
🖱️ [color=pink]点击加速/跳过[/color]

[i]点击鼠标继续阅读...[/i]

---

这是第二页的内容。
当文字超过显示区域时，会自动分页。
看到右下角的图标闪烁了吗？

那代表可以点击进入下一页！

---

[b][color=red]最后一页[/color][/b]

感谢体验剧情对话系统！

这个系统可以用于：
• RPG 游戏对话
• 视觉小说
• 教程引导
• 剧情演出

[color=yellow]再次点击关闭对话框[/color]`;

        createStoryTextBox(this, {
            characterName: '✨ 系统向导',
            content: storyContent,
            avatarTexture: 'characterAvatar',
            typingSpeed: 50,
            onComplete: () => {
                console.log('剧情演示结束！');
                // 可以在这里触发后续逻辑
            }
        });
    }

    createManagerTestPanel() {
        console.log('创建系统管理器测试面板...');

        const cam = this.cameras.main;
        const panelX = 160;
        const panelY = 240;
        const panelWidth = 260;
        const panelHeight = Math.min(340, cam.height - 140);

        const makeButtonNode = (text, callback, color = 0x9C27B0) => {
            return createButton(this, 0, 0, text, callback, {
                backgroundColor: color,
                hoverColor: color + 0x222222,
                pressColor: color - 0x222222,
                fontSize: 14,
                autoPlayAnim: false
            });
        };

        const items = [
            { type: 'spacer', height: 10 },
            { node: makeButtonNode('🔊 音效测试', () => this.testAudioManager()) },
            { type: 'spacer', height: 10 },
            { node: makeButtonNode('🎵 音量控制', () => this.testVolumeControl()) },
            { type: 'spacer', height: 10 },
            { node: makeButtonNode('💰 分数测试', () => this.testScoreManager()) },
            { type: 'spacer', height: 10 },
            { node: makeButtonNode('🏆 成就测试', () => this.testAchievements()) },
            { type: 'spacer', height: 10 },
            { node: makeButtonNode('⚙️ 设置测试', () => this.testSettingsManager()) },
            { type: 'spacer', height: 10 },
            { node: makeButtonNode('💾 存档测试', () => this.testSaveManager()) },
            { type: 'spacer', height: 10 },
            { node: makeButtonNode('🔄 重置数据', () => this.resetAllData(), 0xFF5252) },
            { type: 'spacer', height: 10 },
        ];

        const list = createScrollableList(this, {
            x: panelX,
            y: panelY,
            width: panelWidth,
            height: panelHeight,
            headerTitle: '系统测试面板',
            items
        });

        if (list) {
            list.setDepth(50);
            list.setChildrenInteractive();
        }
    }
    
    // ==========================================
    //  AudioManager 测试方法
    // ==========================================
    
    testAudioManager() {
        console.log('🔊 测试 AudioManager...');
        
        const effects = [
            { name: '按钮点击', method: 'playButtonClick' },
            { name: '收集物品', method: 'playCollectItem' },
            { name: '跳跃', method: 'playJump' },
            { name: '射击', method: 'playShoot' },
            { name: '爆炸', method: 'playExplosion' },
            { name: '胜利', method: 'playVictory' }
        ];
        
        let delay = 0;
        effects.forEach((effect, index) => {
            this.time.delayedCall(delay, () => {
                console.log(`播放: ${effect.name}`);
                this.audioManager[effect.method]();
            });
            delay += 500;
        });
        
        createDialog(
            this,
            '🔊 音效测试',
            '正在依次播放6种音效：\n\n• 按钮点击\n• 收集物品\n• 跳跃\n• 射击\n• 爆炸\n• 胜利\n\n请注意听！',
            [{ text: '确定', callback: () => {} }]
        );
    }
    
    testVolumeControl() {
        console.log('🎵 测试音量控制...');

        const buildVolumeMessage = () => {
            const currentVol = (this.audioManager.getSFXVolume() * 100).toFixed(0);
            const isMuted = this.audioManager.isSFXMuted();
            return `当前音效音量: ${currentVol}%\n静音状态: ${isMuted ? '开启' : '关闭'}\n\n调整音量后点击"完成"关闭`;
        };

        const dialog = createDialog(
            this,
            '🎵 音量控制',
            buildVolumeMessage(),
            [
                {
                    text: '+ 增加',
                    closeDialog: false,
                    callback: () => {
                        const currentVol = Math.min(100, Math.round(this.audioManager.getSFXVolume() * 100) + 10);
                        this.audioManager.setSFXVolume(currentVol / 100);
                        console.log(`音量增加到 ${currentVol}%`);
                    }
                },
                {
                    text: '- 减少',
                    closeDialog: false,
                    callback: () => {
                        const currentVol = Math.max(0, Math.round(this.audioManager.getSFXVolume() * 100) - 10);
                        this.audioManager.setSFXVolume(currentVol / 100);
                        console.log(`音量减少到 ${currentVol}%`);
                    }
                },
                {
                    text: '静音切换',
                    closeDialog: false,
                    callback: () => {
                        this.audioManager.toggleSFXMute();
                        console.log('切换静音状态');
                    }
                },
                {
                    text: '完成',
                    closeDialog: true,
                    callback: () => {
                        console.log('音量设置完成');
                    }
                }
            ]
        );

        const updateDialog = () => {
            if (dialog && typeof dialog.setContentText === 'function') {
                dialog.setContentText(buildVolumeMessage());
            }
        };

        // 直接监听 registry（唯一来源：settings.audio.*），减少手写 on/off
        bindRegistryKeys(this, dialog, ['settings.audio.sfxVolume', 'settings.audio.sfxMuted'], updateDialog);
    }
    
    // ==========================================
    //  ScoreManager 测试方法
    // ==========================================
    
    testScoreManager() {
        console.log('💰 测试 ScoreManager...');

        // 注意：这里不要自动改动分数/等级；点击弹窗里的按钮再进行测试

        const buildScoreMessage = () => {
            const stats = {
                当前分数: this.scoreManager.getCurrentScore(),
                最高分: this.scoreManager.getHighScore(),
                等级: this.scoreManager.getLevel(),
                连击: this.scoreManager.getCombo(),
                连击倍数: this.scoreManager.getComboMultiplier()
            };

            let message = '分数统计：\n\n';
            Object.entries(stats).forEach(([key, value]) => {
                message += `${key}: ${value}\n`;
            });
            return message;
        };

        const dialog = createDialog(
            this,
            '💰 分数测试结果',
            buildScoreMessage(),
            [
                {
                    text: '+100',
                    closeDialog: false,
                    callback: () => {
                        this.scoreManager.addScore(100);
                    }
                },
                {
                    text: '升级',
                    closeDialog: false,
                    callback: () => {
                        this.scoreManager.levelUp();
                    }
                },
                {
                    text: '重置',
                    closeDialog: false,
                    callback: () => {
                        this.scoreManager.resetScore();
                        console.log('分数已重置');
                    }
                },
                {
                    text: '完成',
                    closeDialog: true,
                    callback: () => {}
                }
            ]
        );

        const updateDialog = () => {
            if (dialog && typeof dialog.setContentText === 'function') {
                dialog.setContentText(buildScoreMessage());
            }
        };

        // 直接监听 registry（ScoreManager 会同步 score.*），减少手写 on/off
        bindRegistryKeys(this, dialog, ['score.current', 'score.high', 'score.level', 'score.combo', 'score.maxCombo'], updateDialog);
    }
    
    testAchievements() {
        console.log('🏆 测试成就系统...');
        
        // 解锁一些测试成就
        this.scoreManager.unlockAchievement('test_1', '测试成就1');
        this.scoreManager.unlockAchievement('test_2', '测试成就2');
        this.scoreManager.unlockAchievement('test_3', '测试成就3');
        
        const stats = this.scoreManager.getStatistics();
        
        createDialog(
            this,
            '🏆 成就系统测试',
            `已解锁成就:\n\n${stats.achievements.join('\n') || '暂无成就'}\n\n总游戏次数: ${stats.totalGamesPlayed}\n总分数: ${stats.totalScore}`,
            [{ text: '确定', callback: () => {} }]
        );
    }
    
    // ==========================================
    //  SettingsManager 测试方法
    // ==========================================
    
    testSettingsManager() {
        console.log('⚙️ 测试 SettingsManager...');
        
        const updateDialogContent = (dialog) => {
            const currentSettings = {
                音乐音量: this.settingsManager.get('audio.musicVolume'),
                音效音量: this.settingsManager.get('audio.sfxVolume'),
                难度: this.settingsManager.get('game.difficulty'),
                语言: this.settingsManager.get('game.language'),
                画质: this.settingsManager.get('display.quality')
            };
            
            let message = '当前设置：\n\n';
            Object.entries(currentSettings).forEach(([key, value]) => {
                message += `${key}: ${value}\n`;
            });
            message += '\n点击按钮修改设置';
            
            if (dialog && typeof dialog.setContentText === 'function') {
                dialog.setContentText(message);
            } else if (dialog && dialog.getElement('content')) {
                dialog.getElement('content').setText(message);
                dialog.layout();
            }
        };
        
        const dialog = createDialog(
            this,
            '⚙️ 设置管理器',
            '', // 初始内容由 updateDialogContent 设置
            [
                {
                    text: '修改难度',
                    closeDialog: false, // 不关闭对话框
                    callback: (dialogInstance) => {
                        const difficulties = ['easy', 'normal', 'hard'];
                        const current = this.settingsManager.get('game.difficulty');
                        const currentIndex = difficulties.indexOf(current);
                        const next = difficulties[(currentIndex + 1) % difficulties.length];
                        this.settingsManager.setDifficulty(next);
                        console.log(`难度改为: ${next}`);
                    }
                },
                {
                    text: '修改画质',
                    closeDialog: false, // 不关闭对话框
                    callback: (dialogInstance) => {
                        const qualities = ['low', 'medium', 'high'];
                        const current = this.settingsManager.get('display.quality');
                        const currentIndex = qualities.indexOf(current);
                        const next = qualities[(currentIndex + 1) % qualities.length];
                        this.settingsManager.setQuality(next);
                        console.log(`画质改为: ${next}`);
                    }
                },
                {
                    text: '重置默认',
                    closeDialog: false, // 不关闭对话框
                    callback: (dialogInstance) => {
                        this.settingsManager.resetToDefault();
                        console.log('设置已重置为默认值');
                    }
                },
                {
                    text: '完成',
                    closeDialog: true, // 关闭对话框
                    callback: () => {
                        console.log('设置完成');
                    }
                }
            ]
        );
        
        // 初始化对话框内容
        updateDialogContent(dialog);

        // 直接监听 registry（SettingsManager 会同步 settings.*），减少手写刷新调用
        bindRegistryKeys(this, dialog, [
            'settings.game.difficulty',
            'settings.game.language',
            'settings.display.quality',
            'settings.audio.musicVolume',
            'settings.audio.sfxVolume',
            'settings.audio.musicMuted',
            'settings.audio.sfxMuted'
        ], () => updateDialogContent(dialog));
    }
    
    // ==========================================
    //  SaveManager 测试方法
    // ==========================================
    
    testSaveManager() {
        console.log('💾 测试 SaveManager...');
        
        // 创建测试存档数据
        const testGameState = {
            score: this.scoreManager.getCurrentScore(),
            level: this.scoreManager.getLevel(),
            timestamp: Date.now(),
            testData: 'This is a test save'
        };
        
        // 获取所有存档信息
        const saveInfo = this.saveManager.getAllSaveInfo();
        
        let message = '存档槽位状态：\n\n';
        saveInfo.forEach((info, index) => {
            if (info) {
                message += `槽位 ${index}: ${info.saveName}\n`;
                message += `  时间: ${info.date}\n\n`;
            } else {
                message += `槽位 ${index}: [空]\n\n`;
            }
        });
        
        const storageInfo = this.saveManager.getStorageInfo();
        message += `\n存储使用: ${storageInfo.usedKB}KB / ${storageInfo.totalMB}MB`;
        
        createDialog(
            this,
            '💾 存档管理器测试',
            message,
            [
                {
                    text: '保存到槽位0',
                    callback: () => {
                        this.saveManager.saveGame(0, testGameState, '测试存档');
                        console.log('已保存到槽位0');
                    }
                },
                {
                    text: '加载槽位0',
                    callback: () => {
                        const loaded = this.saveManager.loadGame(0);
                        if (loaded) {
                            console.log('加载成功:', loaded);
                            createDialog(
                                this,
                                '加载成功',
                                `分数: ${loaded.score}\n等级: ${loaded.level}\n数据: ${loaded.testData}`,
                                [{ text: '确定', callback: () => {} }]
                            );
                        }
                    }
                },
                {
                    text: '删除槽位0',
                    callback: () => {
                        this.saveManager.deleteSave(0);
                        console.log('已删除槽位0');
                    }
                }
            ]
        );
    }
    
    // ==========================================
    //  重置所有数据
    // ==========================================
    
    resetAllData() {
        console.log('🔄 重置所有数据...');
        
        createDialog(
            this,
            '⚠️ 警告',
            '确定要重置所有数据吗？\n\n这将清除：\n• 所有分数和成就\n• 所有设置\n• 所有存档\n\n此操作不可恢复！',
            [
                {
                    text: '确定重置',
                    callback: () => {
                        this.scoreManager.resetAllData();
                        this.settingsManager.resetToDefault();
                        this.saveManager.clearAllSaves();
                        console.log('✅ 所有数据已重置！');
                        
                        createDialog(
                            this,
                            '✅ 重置完成',
                            '所有数据已清除！',
                            [{ text: '确定', callback: () => {} }]
                        );
                    }
                },
                {
                    text: '取消',
                    callback: () => {
                        console.log('取消重置');
                    }
                }
            ]
        );
    }

    update() {
        // 安全的背景滚动
        if (this.background && typeof this.background.tilePositionX !== 'undefined') {
            this.background.tilePositionX += 2;
        }
    }
    
}
