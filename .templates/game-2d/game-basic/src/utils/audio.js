/**
 * 游戏音效工具类 - Pro Version
 * 优化了 CPU 性能（缓存噪音）、时间调度（使用游戏时钟）和生命周期管理
 */
export class AudioEffects {
    constructor(scene) {
        this.scene = scene;
        this.context = scene.sound.context;
        this.sfxVolume = 1.0;
        
        // 状态管理
        this.lastPlayTime = new Map();
        this.cooldownTime = new Map();
        
        // 【优化1】缓存白噪音 Buffer，避免每次 createBuffer 重复计算随机数
        this.sharedNoiseBuffer = null; 

        // 初始化配置
        this.setupEffectLimits();

        // 【优化2】生命周期绑定：场景销毁时自动清理
        this.scene.events.once(Phaser.Scenes.Events.SHUTDOWN, this.destroy, this);
    }

    setupEffectLimits() {
        // 设置冷却时间（毫秒）- 防止过于频繁播放
        this.cooldownTime.set('buttonClick', 100);     // 按钮点击100ms冷却
        this.cooldownTime.set('buttonHover', 50);      // 按钮悬停50ms冷却
        this.cooldownTime.set('collect', 50);          // 收集音效50ms冷却
        this.cooldownTime.set('shoot', 80);            // 射击音效80ms冷却
        this.cooldownTime.set('jump', 200);            // 跳跃音效200ms冷却
        this.cooldownTime.set('landing', 300);         // 着陆音效300ms冷却
        this.cooldownTime.set('typewriter', 30);       // 打字机音效30ms冷却
    }

    _ensureNoiseBuffer() {
        // 如果已有缓存，直接跳过
        if (this.sharedNoiseBuffer) return;
        if (!this.context) return;

        // 创建 0.5秒 的单声道白噪音 (足够用了，实际只播几十毫秒)
        const bufferSize = this.context.sampleRate * 0.5;
        const buffer = this.context.createBuffer(1, bufferSize, this.context.sampleRate);
        const data = buffer.getChannelData(0);

        // 填充随机噪音
        for (let i = 0; i < bufferSize; i++) {
            // Math.random() * 2 - 1 生成 -1 到 1 之间的数值
            data[i] = Math.random() * 2 - 1;
        }

        this.sharedNoiseBuffer = buffer;
    }


    destroy() {
        // 清理缓存（可选，如果想跨场景复用可以存到全局）
        this.sharedNoiseBuffer = null;
        this.scene = null;
        this.context = null;
    }

    canPlayEffect(effectName) {
        const now = this.scene.time.now; // 【建议】使用游戏时间而不是系统时间 Date.now()
        const lastTime = this.lastPlayTime.get(effectName) || 0;
        const cooldown = this.cooldownTime.get(effectName) || 0;
        
        if (now - lastTime >= cooldown) {
            this.lastPlayTime.set(effectName, now);
            return true;
        }
        return false;
    }

    // ==========================================
    //  通用辅助方法 (Timer Helper)
    // ==========================================
    /**
     * 【优化2】使用 Phaser 时钟的延时调用
     * 确保游戏暂停时声音序列也暂停，场景销毁时自动取消
     */
    delay(ms, callback) {
        if (!this.scene) return;
        this.scene.time.delayedCall(ms, callback, [], this);
    }

    // ==========================================
    //  具体音效实现 (应用了 delay 优化)
    // ==========================================

    /**
     * 1. 按钮点击音效
     * 适用场景: UI按钮点击、菜单选择、确认操作
     */
    playButtonClick() {
        if (!this.canPlayEffect('buttonClick')) return;
        
        // 主音调：400Hz 三角波，更温和柔软
        // this.generateTone(400, 0.08, 0.25, 'triangle');
        this.generateTone(600, 0.05, 0.2, 'sine');

        
        // // 低频成分：200Hz 正弦波，添加温暖的低频让音效更饱满
        // this.delay(5, () => this.generateTone(200, 0.08, 0.25, 'sine'));
    }

    /**
     * 2. 按钮悬停音效
     * 适用场景: 鼠标悬停在按钮上、菜单项高亮
     */
    playButtonHover() {
        if (!this.canPlayEffect('buttonHover')) return;
        this.generateTone(600, 0.05, 0.2, 'sine');
    }

    /**
     * 3. 打字机音效
     * 适用场景: 剧情对话框文字逐字显示
     */
    playTypewriter() {
        if (!this.canPlayEffect('typewriter')) return;
        if (!this.context) return;

        this._ensureNoiseBuffer();
        if (!this.sharedNoiseBuffer) return;

        const ctx = this.context;
        const t = ctx.currentTime;

        const source = ctx.createBufferSource();
        source.buffer = this.sharedNoiseBuffer;

        const filter = ctx.createBiquadFilter();
        const gain = ctx.createGain();

        // --- 修改点 1：把 Highpass (高通) 改为 Lowpass (低通) ---
        // Highpass 会保留刺耳的“嘶嘶”声，导致“噼里啪啦”。
        // Lowpass 会切掉高频，只保留低沉的“笃笃”声，听起来像墨水打在纸上。
        filter.type = 'lowpass';
        filter.frequency.value = 1500 + Math.random() * 200; // 800Hz 左右，比较闷、不刺耳

        // --- 修改点 2：大幅降低音量 ---
        // 白噪音能量很大，0.4 确实太响了，降到 0.1 - 0.15 比较合适
        // 如果还是响，可以降到 0.05
        const volume = 0.15 * this.sfxVolume; 

        // 连接节点
        source.connect(filter);
        filter.connect(gain);
        gain.connect(ctx.destination);

        // 随机微调播放速度 (0.8 ~ 1.2)
        source.playbackRate.setValueAtTime(0.8 + Math.random() * 0.4, t);

        // --- 修改点 3：缩短时长 ---
        // 从 0.04 缩短到 0.025，让声音极短，更干净利落
        gain.gain.setValueAtTime(volume, t);
        gain.gain.exponentialRampToValueAtTime(0.001, t + 0.025); 

        source.start(t);
        source.stop(t + 0.03); // 稍微多给一点 buffer 防止截断爆音
    }

    /**
     * 4. 收集物品音效
     * 适用场景: 拾取金币、道具、能量球、经验值
     */
    playCollectItem() {
        if (!this.canPlayEffect('collect')) return;
        const frequencies = [523, 659, 784]; // C-E-G 和弦
        frequencies.forEach((freq, index) => {
            this.delay(index * 50, () => this.generateTone(freq, 0.1, 0.4, 'sine'));
        });
    }

    /**
     * 4. 跳跃音效
     * 适用场景: 角色跳跃、弹跳、蹦板效果
     */
    playJump() {
        if (!this.canPlayEffect('jump')) return;
        this.generateSweep(200, 600, 0.2, 0.5, 'square');
    }

    /**
     * 5. 着陆音效
     * 适用场景: 角色落地、物体掉落、重击地面
     */
    playLanding() {
        if (!this.canPlayEffect('landing')) return;
        // 超低频重击 - 更沉闷的基础音
        this.generateTone(40, 0.25, 0.9, 'square');
        
        // 低频冲击噪音 - 更厚重的撞击感
        this.generateNoise(0.3, 0.7, 80);
        
        // 次低频层 - 增加厚度
        this.delay(80, () => this.generateTone(55, 0.15, 0.4, 'triangle'));
        
        // 轻微高频 - 保持一点清晰度但很轻
        this.delay(100, () => this.generateTone(120, 0.03, 0.15, 'sine'));
        
        // 深层余震 - 延长的低频震动
        this.delay(150, () => this.generateTone(35, 0.2, 0.5, 'sine'));
    }

    /**
     * 6. 射击音效
     * 适用场景: 发射子弹、激光武器、弓箭射击
     */
    playShoot() {
        if (!this.canPlayEffect('shoot')) return;
        // 【优化3】增加微小的音调随机性 (detune)，让连射听起来更自然
        const detune = Phaser.Math.Between(-100, 100);
        
        this.generateTone(150, 0.05, 0.6, 'sawtooth', detune);
        this.delay(20, () => this.generateNoise(0.02, 0.4, 200));
    }

    /**
     * 7. 爆炸音效
     * 适用场景: 炸弹爆炸、火箭爆炸、破坏效果
     */
    playExplosion() {
        // 低频冲击波
        this.generateTone(30, 0.4, 0.8, 'square');
        
        // 中频爆裂音
        this.delay(50, () => this.generateNoise(0.3, 0.9, 500));
        
        // 高频碎片音
        this.delay(100, () => {
            for (let i = 0; i < 8; i++) {
                this.delay(i * 20, () => {
                    const freq = 800 + Math.random() * 1200;
                    this.generateTone(freq, 0.05, 0.3, 'sawtooth');
                });
            }
        });
    }

    /**
     * 8. 胜利音效
     * 适用场景: 关卡完成、任务成功、获得成就
     */
    playVictory() {
        const frequencies = [440, 554, 659, 880]; // A-C#-E-A 和弦
        frequencies.forEach((freq, index) => {
            this.delay(index * 80, () => this.generateTone(freq, 0.15, 0.3, 'sine'));
        });
    }

    /**
     * 9. 升级音效
     * 适用场景: 角色升级、技能解锁、属性提升
     */
    playLevelUp() {
        const melody = [523, 659, 784, 1047]; // C-E-G-C 上升音阶
        melody.forEach((freq, index) => {
            this.delay(index * 100, () => this.generateTone(freq, 0.2, 0.4, 'sine'));
        });
    }

    /**
     * 10. 失败音效
     * 适用场景: 游戏结束、任务失败、生命值耗尽
     */
    playGameOver() {
        const melody = [440, 415, 392, 349]; // A-G#-G-F 下降音阶
        melody.forEach((freq, index) => {
            this.delay(index * 200, () => this.generateTone(freq, 0.3, 0.5, 'triangle'));
        });
    }

    /**
     * 11. 成就解锁音效
     * 适用场景: 获得成就、解锁新内容、特殊奖励
     */
    playAchievement() {
        const fanfare = [523, 659, 784, 1047, 784, 1047]; // 胜利号角
        fanfare.forEach((freq, index) => {
            this.delay(index * 120, () => this.generateTone(freq, 0.15, 0.6, 'square'));
        });
    }

    /**
     * 12. 门开启音效
     */
    playDoorOpen() {
        this.generateSweep(100, 300, 0.4, 0.4, 'sine');
        this.delay(200, () => this.generateTone(400, 0.1, 0.3, 'triangle'));
    }

    /**
     * 13. 门关闭音效
     */
    playDoorClose() {
        this.generateSweep(300, 100, 0.3, 0.4, 'sine');
        this.delay(150, () => this.generateTone(80, 0.2, 0.5, 'square'));
    }

    /**
     * 14. 警告音效
     */
    playWarning() {
        for (let i = 0; i < 3; i++) {
            this.delay(i * 300, () => {
                this.generateTone(800, 0.1, 0.6, 'square');
                this.delay(100, () => this.generateTone(600, 0.1, 0.6, 'square'));
            });
        }
    }

    /**
     * 15. 魔法音效
     */
    playMagic() {
        // 魔法粒子音效
        for (let i = 0; i < 5; i++) {
            this.delay(i * 30, () => {
                const freq = 800 + Math.random() * 400;
                this.generateTone(freq, 0.05, 0.3, 'sine');
            });
        }
        // 主魔法音调
        this.delay(100, () => this.generateSweep(400, 1200, 0.3, 0.4, 'triangle'));
    }

    // ==========================================
    //  核心生成器 (Core Generators)
    // ==========================================

    /**
     * 生成基础音调
     * @param {number} frequency - 频率 (Hz)
     * @param {number} duration - 持续时间 (秒)
     * @param {number} volume - 音量 (0-1)
     * @param {string} waveType - 波形类型 ('sine', 'square', 'sawtooth', 'triangle')
     * @param {number} detune - 音调微调 (cents), 默认0
     */
    generateTone(frequency, duration, volume = 0.5, waveType = 'sine', detune = 0) {
        // 如果 Phaser 的音频系统被锁住（未发生交互）或被禁用，则不播放
        if (this.scene.sound.locked || !this.context) return;

        const oscillator = this.context.createOscillator();
        const gainNode = this.context.createGain();

        oscillator.connect(gainNode);

        // ====================================================
        // 关键点：连接到 Phaser 的 Master Volume 节点
        // 而不是直接 connect(this.context.destination)
        // ====================================================
        gainNode.connect(this.scene.sound.masterVolumeNode);

        oscillator.frequency.setValueAtTime(frequency, this.context.currentTime);
        oscillator.type = waveType;
        
        // 应用音调微调
        if (detune !== 0) {
            oscillator.detune.setValueAtTime(detune, this.context.currentTime);
        }

        // 音量包络
        const now = this.context.currentTime;
        
        // 这里的 volume 只需要乘你自己设定的 sfxVolume
        // 不需要乘 masterVolume，因为 connect 到了 masterVolumeNode，Phaser 会自动处理全局音量
        const finalVolume = volume * this.sfxVolume;

        // 预防爆音：快速淡入 (0.005s) 而不是直接设为 0
        gainNode.gain.setValueAtTime(0, now);
        gainNode.gain.linearRampToValueAtTime(finalVolume, now + 0.005);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + duration);

        oscillator.start(now);
        oscillator.stop(now + duration);
        
        // 良好的习惯：播放完断开连接，帮助垃圾回收
        oscillator.onended = () => {
            oscillator.disconnect();
            gainNode.disconnect();
        };
    }

    /**
     * 生成频率扫描音效
     * @param {number} startFreq - 起始频率
     * @param {number} endFreq - 结束频率
     * @param {number} duration - 持续时间
     * @param {number} volume - 音量
     * @param {string} waveType - 波形类型
     * @param {number} detune - 音调微调 (cents), 默认0
     */
    generateSweep(startFreq, endFreq, duration, volume = 0.5, waveType = 'sine', detune = 0) {
        if (this.scene.sound.locked || !this.context) return;

        const oscillator = this.context.createOscillator();
        const gainNode = this.context.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(this.scene.sound.masterVolumeNode);

        oscillator.type = waveType;
        
        // 应用音调微调
        if (detune !== 0) {
            oscillator.detune.setValueAtTime(detune, this.context.currentTime);
        }
        
        const now = this.context.currentTime;
        oscillator.frequency.setValueAtTime(startFreq, now);
        oscillator.frequency.exponentialRampToValueAtTime(endFreq, now + duration);

        const finalVolume = volume * this.sfxVolume;
        
        // 预防爆音：快速淡入
        gainNode.gain.setValueAtTime(0, now);
        gainNode.gain.linearRampToValueAtTime(finalVolume, now + 0.005);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + duration);

        oscillator.start(now);
        oscillator.stop(now + duration);

        oscillator.onended = () => {
            oscillator.disconnect();
            gainNode.disconnect();
        };
    }

    /**
     * 生成噪音效果
     * @param {number} duration - 持续时间
     * @param {number} volume - 音量
     * @param {number} filterFreq - 滤波器频率
     * @param {boolean} highpass - 是否使用高通滤波器
     */
    generateNoise(duration, volume = 0.5, filterFreq = 1000, highpass = false) {
        if (this.scene.sound.locked || !this.context) return;

        // 【优化1 实现】懒加载单例模式：如果已经生成过噪音 Buffer，直接用
        if (!this.sharedNoiseBuffer) {
            // 创建 2秒 的噪音 buffer 足够用了，播放时循环或截取
            const bufferSize = this.context.sampleRate * 2;
            this.sharedNoiseBuffer = this.context.createBuffer(1, bufferSize, this.context.sampleRate);
            const data = this.sharedNoiseBuffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = Math.random() * 2 - 1;
            }
        }

        const bufferSource = this.context.createBufferSource();
        bufferSource.buffer = this.sharedNoiseBuffer;
        
        // 如果需要的时长超过了 buffer 长度，开启循环
        if (duration > bufferSource.buffer.duration) {
            bufferSource.loop = true;
        }

        const gainNode = this.context.createGain();
        const filter = this.context.createBiquadFilter();

        filter.type = highpass ? 'highpass' : 'lowpass';
        filter.frequency.setValueAtTime(filterFreq, this.context.currentTime);

        bufferSource.connect(filter);
        filter.connect(gainNode);
        gainNode.connect(this.scene.sound.masterVolumeNode);

        const now = this.context.currentTime;
        const finalVolume = volume * this.sfxVolume;

        // 预防爆音：快速淡入
        gainNode.gain.setValueAtTime(0, now);
        gainNode.gain.linearRampToValueAtTime(finalVolume, now + 0.005);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + duration);

        bufferSource.start(now);
        bufferSource.stop(now + duration);

        bufferSource.onended = () => {
            bufferSource.disconnect();
            filter.disconnect();
            gainNode.disconnect();
        };
    }

    // ==========================================
    //  工具方法 (Utility Methods)
    // ==========================================

    /**
     * 设置音效音量
     */
    setSfxVolume(volume) {
        this.sfxVolume = Math.max(0, Math.min(1, volume));
    }

    /**
     * 获取音效音量
     */
    getSfxVolume() {
        return this.sfxVolume;
    }

    /**
     * 检查音频上下文状态
     */
    getAudioContextState() {
        return this.context ? this.context.state : 'unavailable';
    }

    /**
     * 播放随机音效（用于测试）
     */
    playRandomEffect() {
        const effects = [
            'playButtonClick', 'playButtonHover', 'playCollectItem', 'playJump', 'playLanding',
            'playShoot', 'playExplosion', 'playVictory', 'playLevelUp', 'playGameOver'
        ];
        
        const randomEffect = effects[Math.floor(Math.random() * effects.length)];
        if (this[randomEffect] && typeof this[randomEffect] === 'function') {
            this[randomEffect]();
        }
    }
}

/**
 * 测试函数 - 可以在控制台中调用
 */
export function testAudioEffects(scene) {
    const audioEffects = new AudioEffects(scene);
    
    console.log('🎵 音效系统测试开始...');
    console.log('AudioContext 状态:', audioEffects.getAudioContextState());
    console.log('Phaser Sound 锁定状态:', scene.sound.locked);
    
    // 测试按钮音效
    console.log('测试按钮音效...');
    audioEffects.playButtonClick();        // 按钮点击
    
    audioEffects.delay(500, () => {
        audioEffects.playButtonHover();    // 按钮悬停
    });
    
    // 测试游戏音效
    audioEffects.delay(1000, () => {
        console.log('测试游戏音效...');
        audioEffects.playCollectItem();   // 收集物品
    });
    
    audioEffects.delay(1500, () => {
        audioEffects.playJump();          // 跳跃
    });
    
    audioEffects.delay(2000, () => {
        audioEffects.playLanding();       // 着陆
    });
    
    // 测试战斗音效
    audioEffects.delay(2500, () => {
        console.log('测试战斗音效...');
        audioEffects.playShoot();         // 射击
    });
    
    audioEffects.delay(3000, () => {
        audioEffects.playExplosion();     // 爆炸
    });
    
    // 测试反馈音效
    audioEffects.delay(3500, () => {
        console.log('测试反馈音效...');
        audioEffects.playVictory();       // 胜利
    });
    
    audioEffects.delay(4500, () => {
        console.log('🎵 音效系统测试完成！');
    });
}
