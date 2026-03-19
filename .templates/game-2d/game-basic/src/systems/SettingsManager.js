import Phaser from 'phaser';

/**
 * SettingsManager - 设置持久化和管理系统
 * 管理游戏设置（语言、难度、显示选项等）
 *
 * 事件：
 * - setting-changed: { path, value, previous }
 * - settings-changed: { settings }
 */
export default class SettingsManager {
    constructor(scene = null) {
        this.scene = scene;
        this.registry = scene?.registry ?? null;
        this.events = new Phaser.Events.EventEmitter();

        // 本地持久化节流：避免频繁 localStorage 写入导致卡顿
        this._saveTimer = null;
        this._saveDelayMs = 400;
        this._boundFlushSave = () => this.flushSaveSettings();

        if (this.scene?.events && typeof this.scene.events.once === 'function') {
            this.scene.events.once('shutdown', this._boundFlushSave);
        }
        if (typeof window !== 'undefined' && window?.addEventListener) {
            window.addEventListener('pagehide', this._boundFlushSave);
        }

        // 默认设置
        this.settings = {
            // 音频设置
            audio: {
                musicVolume: 0.7,
                sfxVolume: 0.8,
                musicMuted: false,
                sfxMuted: false
            },

            // 显示设置
            display: {
                fullscreen: false,
                showFPS: false,
                particleEffects: true,
                screenShake: true,
                quality: 'high' // 'low', 'medium', 'high'
            },

            // 游戏设置
            game: {
                difficulty: 'normal', // 'easy', 'normal', 'hard'
                language: 'zh-CN', // 'zh-CN', 'en-US'
                autoSave: true,
                showTutorial: true
            },

            // 控制设置
            controls: {
                keyboardEnabled: true,
                mouseEnabled: true,
                touchEnabled: true,
                vibration: true
            }
        };

        this.loadSettings();
        this._syncRegistryAll();
    }

    _emit(eventName, payload) {
        if (this.events) {
            this.events.emit(eventName, payload);
        }
    }

    _syncRegistryAll() {
        if (!this.registry) return;
        // 只同步常用项（避免 registry 塞太大对象）
        this.registry.set('settings.game.difficulty', this.get('game.difficulty'));
        this.registry.set('settings.display.quality', this.get('display.quality'));
        this.registry.set('settings.game.language', this.get('game.language'));
        this.registry.set('settings.game.autoSave', this.get('game.autoSave'));
        this.registry.set('settings.display.fullscreen', this.get('display.fullscreen'));
        this.registry.set('settings.display.showFPS', this.get('display.showFPS'));
        this.registry.set('settings.audio.musicVolume', this.get('audio.musicVolume'));
        this.registry.set('settings.audio.sfxVolume', this.get('audio.sfxVolume'));
        this.registry.set('settings.audio.musicMuted', this.get('audio.musicMuted'));
        this.registry.set('settings.audio.sfxMuted', this.get('audio.sfxMuted'));
    }

    _syncRegistryPath(path, value) {
        if (!this.registry) return;
        this.registry.set(`settings.${path}`, value);
    }

    /**
     * 从本地存储加载设置
     */
    loadSettings() {
        let parsedGameSettings = null;

        try {
            const data = localStorage.getItem('gameSettings');
            if (data) {
                parsedGameSettings = JSON.parse(data);
                this.settings = this.mergeDeep(this.settings, parsedGameSettings);
            }
        } catch (error) {
            console.error('Failed to load settings:', error);
        }

        // 兼容迁移：历史版本 AudioManager 使用 audioSettings 单独存储
        // 统一迁移到 gameSettings.audio，避免“两个来源漂移”
        try {
            const legacyAudio = localStorage.getItem('audioSettings');
            const shouldMigrate = legacyAudio && (!parsedGameSettings || !parsedGameSettings.audio);

            if (shouldMigrate) {
                const parsedLegacy = JSON.parse(legacyAudio);
                this.settings.audio = this.mergeDeep(this.settings.audio, {
                    musicVolume: parsedLegacy.musicVolume,
                    sfxVolume: parsedLegacy.sfxVolume,
                    musicMuted: parsedLegacy.musicMuted,
                    sfxMuted: parsedLegacy.sfxMuted
                });

                localStorage.removeItem('audioSettings');
                // 直接落盘到 gameSettings（不额外触发 events，避免初始化时刷屏）
                localStorage.setItem('gameSettings', JSON.stringify(this.settings));
            } else if (legacyAudio) {
                // 已有新设置源时，清理旧 key，避免后续误用
                localStorage.removeItem('audioSettings');
            }
        } catch (error) {
            console.error('Failed to migrate legacy audio settings:', error);
        }
    }

    _scheduleSaveSettings() {
        if (this._saveTimer) return;
        this._saveTimer = setTimeout(() => {
            this._saveTimer = null;
            this._persistNow();
        }, this._saveDelayMs);
    }

    _persistNow() {
        try {
            localStorage.setItem('gameSettings', JSON.stringify(this.settings));
        } catch (error) {
            console.error('Failed to save settings:', error);
        }
    }

    /**
     * 保存设置到本地存储（已节流）
     */
    saveSettings() {
        // 维持旧语义：调用者认为“设置已变更”，事件立即发；但真正落盘延迟合并
        this._emit('settings-changed', { settings: this.settings });
        this._scheduleSaveSettings();
    }

    /**
     * 立即落盘（用于切场景/关闭页面）
     */
    flushSaveSettings() {
        if (this._saveTimer) {
            clearTimeout(this._saveTimer);
            this._saveTimer = null;
        }
        this._persistNow();
    }

    /**
     * 深度合并对象
     * @param {object} target - 目标对象
     * @param {object} source - 源对象
     * @returns {object}
     */
    mergeDeep(target, source) {
        const output = { ...target };
        if (this.isObject(target) && this.isObject(source)) {
            Object.keys(source).forEach(key => {
                if (this.isObject(source[key])) {
                    if (!(key in target)) {
                        output[key] = source[key];
                    } else {
                        output[key] = this.mergeDeep(target[key], source[key]);
                    }
                } else {
                    output[key] = source[key];
                }
            });
        }
        return output;
    }

    /**
     * 检查是否为对象
     * @param {any} item - 要检查的项
     * @returns {boolean}
     */
    isObject(item) {
        return item && typeof item === 'object' && !Array.isArray(item);
    }

    /**
     * 获取设置值
     * @param {string} path - 设置路径（例如: 'audio.musicVolume'）
     * @returns {any}
     */
    get(path) {
        const keys = path.split('.');
        let value = this.settings;

        for (const key of keys) {
            if (value && typeof value === 'object' && key in value) {
                value = value[key];
            } else {
                return undefined;
            }
        }

        return value;
    }

    /**
     * 设置值
     * @param {string} path - 设置路径（例如: 'audio.musicVolume'）
     * @param {any} value - 要设置的值
     */
    set(path, value) {
        const previous = this.get(path);

        const keys = path.split('.');
        const lastKey = keys.pop();
        let target = this.settings;

        for (const key of keys) {
            if (!(key in target)) {
                target[key] = {};
            }
            target = target[key];
        }

        target[lastKey] = value;
        this._syncRegistryPath(path, value);
        this.saveSettings();
        this._emit('setting-changed', { path, value, previous });
    }

    /**
     * 获取所有设置
     * @returns {object}
     */
    getAll() {
        return { ...this.settings };
    }

    /**
     * 批量更新设置
     * @param {object} newSettings - 新设置对象
     */
    updateSettings(newSettings) {
        this.settings = this.mergeDeep(this.settings, newSettings);
        this._syncRegistryAll();
        this.saveSettings();
    }

    /**
     * 重置为默认设置
     */
    resetToDefault() {
        this.settings = {
            audio: {
                musicVolume: 0.7,
                sfxVolume: 0.8,
                musicMuted: false,
                sfxMuted: false
            },
            display: {
                fullscreen: false,
                showFPS: false,
                particleEffects: true,
                screenShake: true,
                quality: 'high'
            },
            game: {
                difficulty: 'normal',
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
        };
        this._syncRegistryAll();
        this.saveSettings();
    }

    /**
     * 切换全屏（这里只改设置值；真正的全屏切换建议由 Scene 执行）
     */
    toggleFullscreen() {
        this.set('display.fullscreen', !this.get('display.fullscreen'));
    }

    /**
     * 设置难度
     * @param {string} difficulty - 难度等级
     */
    setDifficulty(difficulty) {
        const validDifficulties = ['easy', 'normal', 'hard'];
        if (validDifficulties.includes(difficulty)) {
            this.set('game.difficulty', difficulty);
        }
    }

    /**
     * 设置语言
     * @param {string} language - 语言代码
     */
    setLanguage(language) {
        this.set('game.language', language);
    }

    /**
     * 设置画质
     * @param {string} quality - 画质等级
     */
    setQuality(quality) {
        const validQualities = ['low', 'medium', 'high'];
        if (validQualities.includes(quality)) {
            this.set('display.quality', quality);
        }
    }

    /**
     * 导出设置为 JSON
     * @returns {string}
     */
    exportSettings() {
        return JSON.stringify(this.settings, null, 2);
    }

    /**
     * 从 JSON 导入设置
     * @param {string} jsonString - JSON 字符串
     * @returns {boolean} - 是否成功
     */
    importSettings(jsonString) {
        try {
            const parsed = JSON.parse(jsonString);
            this.updateSettings(parsed);
            return true;
        } catch (error) {
            console.error('Failed to import settings:', error);
            return false;
        }
    }

    destroy() {
        // 尽量保证最后一次写入落盘
        this.flushSaveSettings();

        if (typeof window !== 'undefined' && window?.removeEventListener && this._boundFlushSave) {
            window.removeEventListener('pagehide', this._boundFlushSave);
        }

        if (this.events) {
            this.events.removeAllListeners();
            this.events = null;
        }
        this.scene = null;
        this.registry = null;
        this._boundFlushSave = null;
    }
}
