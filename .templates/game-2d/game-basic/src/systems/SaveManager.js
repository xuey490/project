import Phaser from 'phaser';

/**
 * SaveManager - 存档管理系统
 * 管理游戏存档的保存和加载
 *
 * 事件：
 * - save-success: { slotId, saveData }
 * - save-error: { slotId, message, error }
 * - load-success: { slotId, gameState }
 * - load-error: { slotId, message, error }
 */
export default class SaveManager {
    constructor(scene = null) {
        this.scene = scene;
        this.events = new Phaser.Events.EventEmitter();

        this.storageKey = 'gameSaveData';
        this.maxSaveSlots = 3; // 最多保存3个存档
        this.autoSaveEnabled = true;
        this.autoSaveInterval = 60000; // 自动保存间隔（毫秒）
        this.autoSaveTimer = null;
        this._autoSaveCallback = null;

        // 场景关闭/销毁时自动停止自动保存
        if (this.scene && this.scene.events && typeof this.scene.events.once === 'function') {
            this.scene.events.once('shutdown', () => this.stopAutoSave());
            this.scene.events.once('destroy', () => this.stopAutoSave());
        }
    }

    _emit(eventName, payload) {
        if (this.events) {
            this.events.emit(eventName, payload);
        }
    }

    _isValidSlot(slotId) {
        return Number.isInteger(slotId) && slotId >= 0 && slotId < this.maxSaveSlots;
    }

    /**
     * 保存游戏状态
     * @param {number} slotId - 存档槽位ID (0-2)
     * @param {object} gameState - 游戏状态数据
     * @param {string} saveName - 存档名称（可选）
     * @returns {boolean} - 是否保存成功
     */
    saveGame(slotId, gameState, saveName = '') {
        if (!this._isValidSlot(slotId)) {
            const message = 'Invalid save slot ID';
            console.error(message);
            this._emit('save-error', { slotId, message });
            return false;
        }

        try {
            const saveData = {
                slotId,
                saveName: saveName || `存档 ${slotId + 1}`,
                timestamp: Date.now(),
                gameState: this.compressGameState(gameState)
            };

            const allSaves = this.getAllSaves();
            allSaves[slotId] = saveData;

            localStorage.setItem(this.storageKey, JSON.stringify(allSaves));

            this._emit('save-success', { slotId, saveData });
            return true;
        } catch (error) {
            console.error('Failed to save game:', error);
            this._emit('save-error', { slotId, message: error.message, error });
            return false;
        }
    }

    /**
     * 加载游戏状态
     * @param {number} slotId - 存档槽位ID (0-2)
     * @returns {object|null} - 游戏状态数据或null
     */
    loadGame(slotId) {
        if (!this._isValidSlot(slotId)) {
            const message = 'Invalid save slot ID';
            console.error(message);
            this._emit('load-error', { slotId, message });
            return null;
        }

        try {
            const allSaves = this.getAllSaves();
            const saveData = allSaves[slotId];

            if (!saveData) {
                const message = 'No save data found';
                console.warn(`No save data found in slot ${slotId}`);
                this._emit('load-error', { slotId, message });
                return null;
            }

            const gameState = this.decompressGameState(saveData.gameState);
            this._emit('load-success', { slotId, gameState });
            return gameState;
        } catch (error) {
            console.error('Failed to load game:', error);
            this._emit('load-error', { slotId, message: error.message, error });
            return null;
        }
    }

    /**
     * 获取所有存档
     * @returns {Array} - 存档数组
     */
    getAllSaves() {
        try {
            const data = localStorage.getItem(this.storageKey);
            if (data) {
                return JSON.parse(data);
            }
        } catch (error) {
            console.error('Failed to get saves:', error);
        }
        return new Array(this.maxSaveSlots).fill(null);
    }

    /**
     * 获取存档信息（不包含完整游戏状态）
     * @param {number} slotId - 存档槽位ID
     * @returns {object|null} - 存档信息
     */
    getSaveInfo(slotId) {
        const allSaves = this.getAllSaves();
        const saveData = allSaves[slotId];

        if (!saveData) {
            return null;
        }

        return {
            slotId: saveData.slotId,
            saveName: saveData.saveName,
            timestamp: saveData.timestamp,
            date: new Date(saveData.timestamp).toLocaleString()
        };
    }

    /**
     * 获取所有存档信息
     * @returns {Array} - 存档信息数组
     */
    getAllSaveInfo() {
        return Array.from({ length: this.maxSaveSlots }, (_, i) => this.getSaveInfo(i));
    }

    /**
     * 删除存档
     * @param {number} slotId - 存档槽位ID
     * @returns {boolean} - 是否删除成功
     */
    deleteSave(slotId) {
        if (!this._isValidSlot(slotId)) {
            console.error('Invalid save slot ID');
            return false;
        }

        try {
            const allSaves = this.getAllSaves();
            allSaves[slotId] = null;
            localStorage.setItem(this.storageKey, JSON.stringify(allSaves));
            return true;
        } catch (error) {
            console.error('Failed to delete save:', error);
            return false;
        }
    }

    /**
     * 检查存档槽位是否为空
     * @param {number} slotId - 存档槽位ID
     * @returns {boolean}
     */
    isSlotEmpty(slotId) {
        return this.getSaveInfo(slotId) === null;
    }

    /**
     * 压缩游戏状态（可以添加实际压缩逻辑）
     * @param {object} gameState - 游戏状态
     * @returns {object}
     */
    compressGameState(gameState) {
        return gameState;
    }

    /**
     * 解压游戏状态
     * @param {object} compressedState - 压缩的游戏状态
     * @returns {object}
     */
    decompressGameState(compressedState) {
        return compressedState;
    }

    /**
     * 启动自动保存（使用 Phaser 的 scene.time.addEvent，跟随场景生命周期更安全）
     * @param {Function} saveCallback - 自动保存回调函数
     */
    startAutoSave(saveCallback) {
        this._autoSaveCallback = (typeof saveCallback === 'function') ? saveCallback : null;

        if (!this.autoSaveEnabled) {
            return;
        }

        this.stopAutoSave();

        const delay = Math.max(1000, Number(this.autoSaveInterval) || 60000);

        // 优先用 Phaser 的 TimeEvent（场景 pause/shutdown 时更安全）
        if (this.scene?.time && typeof this.scene.time.addEvent === 'function') {
            this.autoSaveTimer = this.scene.time.addEvent({
                delay,
                loop: true,
                callback: () => {
                    if (this._autoSaveCallback) {
                        this._autoSaveCallback();
                    }
                }
            });
            return;
        }

        // 兜底：如果没有 scene/time（例如在纯逻辑环境里使用），退回到 setInterval
        this.autoSaveTimer = setInterval(() => {
            if (this._autoSaveCallback) {
                this._autoSaveCallback();
            }
        }, delay);
    }

    /**
     * 停止自动保存
     */
    stopAutoSave() {
        if (!this.autoSaveTimer) return;

        // Phaser.Time.TimerEvent
        if (typeof this.autoSaveTimer.remove === 'function') {
            this.autoSaveTimer.remove(false);
            this.autoSaveTimer = null;
            return;
        }

        // setInterval id
        clearInterval(this.autoSaveTimer);
        this.autoSaveTimer = null;
    }

    /**
     * 设置自动保存间隔
     * @param {number} interval - 间隔时间（毫秒）
     */
    setAutoSaveInterval(interval) {
        this.autoSaveInterval = interval;

        // 如果正在自动保存，立即用新间隔重启
        if (this.autoSaveEnabled && this.autoSaveTimer) {
            this.startAutoSave(this._autoSaveCallback);
        }
    }

    /**
     * 启用/禁用自动保存
     * @param {boolean} enabled - 是否启用
     */
    setAutoSaveEnabled(enabled) {
        this.autoSaveEnabled = !!enabled;

        if (!this.autoSaveEnabled) {
            this.stopAutoSave();
            return;
        }

        // 重新启用时：如果之前已经注册过回调，则恢复 autosave
        if (this._autoSaveCallback) {
            this.startAutoSave(this._autoSaveCallback);
        }
    }

    /**
     * 清除所有存档
     * @returns {boolean}
     */
    clearAllSaves() {
        try {
            localStorage.removeItem(this.storageKey);
            return true;
        } catch (error) {
            console.error('Failed to clear saves:', error);
            return false;
        }
    }

    /**
     * 导出存档到文件
     * @param {number} slotId - 存档槽位ID
     * @returns {string|null} - JSON字符串
     */
    exportSave(slotId) {
        const saveData = this.getAllSaves()[slotId];
        if (!saveData) {
            return null;
        }
        return JSON.stringify(saveData, null, 2);
    }

    /**
     * 从文件导入存档
     * @param {number} slotId - 存档槽位ID
     * @param {string} jsonString - JSON字符串
     * @returns {boolean}
     */
    importSave(slotId, jsonString) {
        if (!this._isValidSlot(slotId)) {
            console.error('Invalid save slot ID');
            return false;
        }

        try {
            const saveData = JSON.parse(jsonString);
            const allSaves = this.getAllSaves();
            allSaves[slotId] = saveData;
            localStorage.setItem(this.storageKey, JSON.stringify(allSaves));
            return true;
        } catch (error) {
            console.error('Failed to import save:', error);
            return false;
        }
    }

    /**
     * 获取存储使用情况
     * @returns {object}
     */
    getStorageInfo() {
        try {
            const data = localStorage.getItem(this.storageKey);
            const used = data ? new Blob([data]).size : 0;
            const total = 5 * 1024 * 1024; // 假设5MB限制

            return {
                used,
                total,
                usedPercent: (used / total * 100).toFixed(2),
                usedKB: (used / 1024).toFixed(2),
                totalMB: (total / 1024 / 1024).toFixed(2)
            };
        } catch (error) {
            console.error('Failed to get storage info:', error);
            return null;
        }
    }

    destroy() {
        this.stopAutoSave();
        this._autoSaveCallback = null;
        if (this.events) {
            this.events.removeAllListeners();
            this.events = null;
        }
        this.scene = null;
    }
}
