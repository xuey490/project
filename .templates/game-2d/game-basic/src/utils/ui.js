/**
 * UI Utility Functions - RexUI Enhanced
 * 使用 Phaser3-Rex-Plugins 创建高质量UI组件
 */

import {
    COLORS,
    TYPOGRAPHY,
    BUTTON_STYLES,
    DIALOG_STYLES,
    STORY_TEXTBOX_STYLES,
    FLOATING_TEXT_STYLES,
    PARTICLE_STYLES,
    ROUND_RECTANGLE_DEFAULTS
} from '../config/StyleConfig.js';

/**
 * 创建圆角矩形背景（RexUI Label 需要）
 * @param {Phaser.Scene} scene
 * @param {number} color
 * @param {number} radius
 * @param {number} strokeColor
 * @param {number} strokeWidth
 */
function createRoundRectangle(
    scene, 
    color, 
    radius = ROUND_RECTANGLE_DEFAULTS.cornerRadius, 
    strokeColor = ROUND_RECTANGLE_DEFAULTS.strokeColor, 
    strokeWidth = ROUND_RECTANGLE_DEFAULTS.strokeWidth
) {
    if (!scene.rexUI) {
        console.error('RexUI plugin not loaded!');
        const { width, height } = ROUND_RECTANGLE_DEFAULTS.fallbackSize;
        return scene.add.graphics().fillStyle(color).fillRoundedRect(0, 0, width, height, radius);
    }
    
    const bg = scene.rexUI.add.roundRectangle(0, 0, 0, 0, radius, color);
    if (strokeColor !== null) {
        bg.setStrokeStyle(strokeWidth, strokeColor);
    }
    return bg;
}


/**
 * 绑定 scene.registry 的 changedata 事件，并在 target 销毁时自动解绑
 * @param {Phaser.Scene} scene
 * @param {any} target - 通常是 dialog/scrollable 等，有 once('destroy') 即可
 * @param {string[]} keys - registry key 列表，例如 ['score.current', 'settings.audio.sfxVolume']
 * @param {Function} onChange - 任意 key 变化时触发
 * @returns {Function} cleanup
 */
export function bindRegistryKeys(scene, target, keys = [], onChange) {
    const registry = scene?.registry;
    const emitter = registry?.events;

    if (!emitter || !Array.isArray(keys) || keys.length === 0) {
        return () => {};
    }

    const handler = (...args) => {
        if (typeof onChange === 'function') {
            onChange(...args);
        }
    };

    const eventNames = keys.map((k) => `changedata-${k}`);
    eventNames.forEach((eventName) => emitter.on(eventName, handler));

    const cleanup = () => {
        eventNames.forEach((eventName) => emitter.off(eventName, handler));
    };

    // 自动解绑：优先跟随 target 生命周期
    if (target && typeof target.once === 'function') {
        target.once('destroy', cleanup);
    } else if (scene?.events && typeof scene.events.once === 'function') {
        scene.events.once('shutdown', cleanup);
    }

    return cleanup;
}

/**
 * 创建 RexUI 按钮（增强版 - 支持被 Dialog 复用）
 * @param {Phaser.Scene} scene - 当前场景
 * @param {number} x - X坐标
 * @param {number} y - Y坐标
 * @param {string} text - 按钮文字
 * @param {Function} onClick - 点击回调
 * @param {Object} options - 可选配置
 * @returns {RexUI.Label}
 */
export function createButton(scene, x, y, text, onClick, options = {}) {
    if (!scene.rexUI) {
        console.error('RexUI plugin not loaded! Cannot create button.');
        return null;
    }

    const config = {
        fontSize: options.fontSize || BUTTON_STYLES.fontSize,
        textColor: options.textColor || TYPOGRAPHY.textColor.primary,
        icon: options.icon || null,

        // 默认是纯色圆角矩形；如果传了 backgroundImageKey 则使用图片背景
        backgroundImageKey: options.backgroundImageKey || null,
        backgroundImageFrame: options.backgroundImageFrame,
        backgroundImageTint: (typeof options.backgroundImageTint === 'number') ? options.backgroundImageTint : 0xffffff,
        hoverImageTint: (typeof options.hoverImageTint === 'number') ? options.hoverImageTint : null,
        pressImageTint: (typeof options.pressImageTint === 'number') ? options.pressImageTint : 0xdddddd,
        backgroundAlpha: (typeof options.backgroundAlpha === 'number') ? options.backgroundAlpha : 1,
        resizeBackgroundToLabel: options.resizeBackgroundToLabel !== false,

        // 文本描边/阴影（固定色值；不做自动判断）
        textStrokeThickness: options.textStrokeThickness,

        // 手动文本描边/阴影
        textStrokeColor: options.textStrokeColor,
        textShadowColor: options.textShadowColor,
        textShadowOffsetX: Number.isFinite(options.textShadowOffsetX) ? options.textShadowOffsetX : 1,
        textShadowOffsetY: Number.isFinite(options.textShadowOffsetY) ? options.textShadowOffsetY : 1,
        textShadowBlur: Number.isFinite(options.textShadowBlur) ? options.textShadowBlur : 2,

        // 纯色背景（图片未配置/未加载时兜底）
        backgroundColor: options.backgroundColor || COLORS.primary,
        hoverColor: options.hoverColor || COLORS.light,
        pressColor: options.pressColor || COLORS.dark,

        cornerRadius: options.cornerRadius || BUTTON_STYLES.cornerRadius,
        padding: options.padding || BUTTON_STYLES.padding,
        autoPlayAnim: options.autoPlayAnim !== undefined ? options.autoPlayAnim : true,

        // 点击防连点（ms）。0 表示不节流
        clickCooldown: options.clickCooldown !== undefined ? options.clickCooldown : 350,

        // 如果 onClick 返回带 once('destroy') 的对象（例如 dialog），默认锁到它销毁再恢复
        lockWhileReturnedAlive: options.lockWhileReturnedAlive !== undefined ? options.lockWhileReturnedAlive : true,

        ...options
    };

    const buttonText = config.icon ? `${config.icon} ${text}` : text;

    // 背景：默认用圆角矩形；传 backgroundImageKey 则改为图片背景
    const backgroundGO = config.backgroundImageKey
        ? scene.add.image(0, 0, config.backgroundImageKey, config.backgroundImageFrame)
        : createRoundRectangle(
            scene,
            config.backgroundColor,
            config.cornerRadius,
            COLORS.white,
            BUTTON_STYLES.strokeWidth
        );

    // 创建 Label 组件
    const button = scene.rexUI.add.label({
        background: backgroundGO,

        text: scene.add.text(0, 0, buttonText, {
            fontSize: `${config.fontSize}px`,
            color: config.textColor,
            fontFamily: TYPOGRAPHY.fontFamily.default,
            fontWeight: TYPOGRAPHY.fontWeight.bold
        }),

        space: config.padding,

        align: 'center',
        name: text
    });

    button.setPosition(x, y);
    button.layout();

    // 文本效果：通过参数加描边/阴影增强可读性（不做自动对比色判断）
    const textElement = button.getElement('text');
    if (textElement) {
        if (typeof textElement.setStroke === 'function' && (config.textStrokeColor != null || Number.isFinite(config.textStrokeThickness))) {
            const strokeColor = (config.textStrokeColor != null) ? config.textStrokeColor : '#000000';
            const strokeThickness = Number.isFinite(config.textStrokeThickness) ? config.textStrokeThickness : 3;
            textElement.setStroke(strokeColor, strokeThickness);
        }

        if (typeof textElement.setShadow === 'function' && config.textShadowColor != null) {
            textElement.setShadow(
                config.textShadowOffsetX,
                config.textShadowOffsetY,
                config.textShadowColor,
                config.textShadowBlur,
                false,
                true
            );
        }
    }

    const bgElement = button.getElement('background');

    const setBgNormal = () => {
        if (!bgElement) return;
        if (typeof bgElement.setFillStyle === 'function') {
            bgElement.setFillStyle(config.backgroundColor);
            return;
        }
        if (typeof bgElement.setTint === 'function') {
            bgElement.setTint(config.backgroundImageTint);
        }
        if (typeof bgElement.setAlpha === 'function') {
            bgElement.setAlpha(config.backgroundAlpha);
        }
    };

    const setBgHover = () => {
        if (!bgElement) return;
        if (typeof bgElement.setFillStyle === 'function') {
            bgElement.setFillStyle(config.hoverColor);
            return;
        }
        if (typeof bgElement.setTint === 'function') {
            const tint = (typeof config.hoverImageTint === 'number') ? config.hoverImageTint : config.backgroundImageTint;
            bgElement.setTint(tint);
        }
        if (typeof bgElement.setAlpha === 'function') {
            bgElement.setAlpha(config.backgroundAlpha);
        }
    };

    const setBgPress = () => {
        if (!bgElement) return;
        if (typeof bgElement.setFillStyle === 'function') {
            bgElement.setFillStyle(config.pressColor);
            return;
        }
        if (typeof bgElement.setTint === 'function') {
            bgElement.setTint(config.pressImageTint);
        }
        if (typeof bgElement.setAlpha === 'function') {
            bgElement.setAlpha(config.backgroundAlpha);
        }
    };

    const resetVisual = () => {
        if (!button || !button.scene) return;
        if (scene?.tweens && typeof scene.tweens.killTweensOf === 'function') {
            scene.tweens.killTweensOf(button);
        }
        setBgNormal();
        if (typeof button.setScale === 'function') {
            button.setScale(BUTTON_STYLES.normal.scale);
        }
    };

    // 供外部（例如弹窗打开时）强制复位按钮视觉，避免 hover/pressed 卡住
    button.__uiResetVisual = resetVisual;

    // 如果背景是图片：让它跟随 label 的最终尺寸
    if (config.backgroundImageKey && config.resizeBackgroundToLabel && bgElement && typeof bgElement.setDisplaySize === 'function') {
        const bounds = (typeof button.getBounds === 'function') ? button.getBounds() : null;
        const w = bounds?.width || button.width;
        const h = bounds?.height || button.height;
        if (w && h) {
            bgElement.setDisplaySize(w, h);
        }
        setBgNormal();
    } else {
        setBgNormal();
    }


    const clickCooldownMs = Math.max(0, Number(config.clickCooldown) || 0);
    let lastClickAt = -Infinity;

    let unlockTimer = null;
    const clearUnlockTimer = () => {
        if (unlockTimer && typeof unlockTimer.remove === 'function') {
            unlockTimer.remove(false);
        } else if (unlockTimer && scene?.time && typeof scene.time.removeEvent === 'function') {
            scene.time.removeEvent(unlockTimer);
        }
        unlockTimer = null;
    };

    const enableButtonInput = () => {
        if (!button || !button.scene) return;
        if (typeof button.setInteractive === 'function') {
            button.setInteractive({ useHandCursor: true });
        }
    };

    const disableButtonInput = () => {
        if (!button || !button.scene) return;
        if (typeof button.disableInteractive === 'function') {
            button.disableInteractive();
        }
    };

    if (button && typeof button.once === 'function') {
        button.once('destroy', clearUnlockTimer);
    }

    button.setInteractive({ useHandCursor: true });

    // 交互效果 - 使用配置中的动画参数
    button.on('pointerover', function () {
        setBgHover();
        scene.tweens.add({
            targets: button,
            scaleX: BUTTON_STYLES.hover.scale,
            scaleY: BUTTON_STYLES.hover.scale,
            duration: BUTTON_STYLES.hover.duration,
            ease: BUTTON_STYLES.hover.ease
        });
        if (scene.audioManager && typeof scene.audioManager.playButtonHover === 'function') {
            scene.audioManager.playButtonHover();
        }
    });

    button.on('pointerout', function () {
        setBgNormal();
        scene.tweens.add({
            targets: button,
            scaleX: BUTTON_STYLES.normal.scale,
            scaleY: BUTTON_STYLES.normal.scale,
            duration: BUTTON_STYLES.normal.duration,
            ease: BUTTON_STYLES.normal.ease
        });
    });

    button.on('pointerdown', function () {
        setBgPress();
        scene.tweens.add({
            targets: button,
            scaleX: BUTTON_STYLES.press.scale,
            scaleY: BUTTON_STYLES.press.scale,
            duration: BUTTON_STYLES.press.duration,
            ease: BUTTON_STYLES.press.ease
        });
    });

    button.on('pointerup', function () {
        const now = (scene?.time && typeof scene.time.now === 'number') ? scene.time.now : Date.now();

        // 防连点：在 cooldown 期间直接忽略，但要强制把视觉复位（避免弹窗遮罩吃掉 pointerout 导致卡态）
        if (clickCooldownMs > 0 && now - lastClickAt < clickCooldownMs) {
            resetVisual();
            return;
        }

        lastClickAt = now;
        clearUnlockTimer();

        // 点击处理期间先禁用交互，避免 tween 未结束时重复触发
        disableButtonInput();

        // 关键：不要停留在 hover/press；弹窗出现后可能收不到 pointerout
        if (scene?.tweens && typeof scene.tweens.killTweensOf === 'function') {
            scene.tweens.killTweensOf(button);
        }
        setBgNormal();

        scene.tweens.add({
            targets: button,
            scaleX: BUTTON_STYLES.release.scale,
            scaleY: BUTTON_STYLES.release.scale,
            duration: BUTTON_STYLES.release.duration,
            ease: BUTTON_STYLES.release.ease,
            onComplete: () => {
                // 先把视觉彻底归位，再执行业务逻辑，避免“按钮一直像被按住”
                resetVisual();

                let clickResult;
                try {
                    if (onClick && typeof onClick === 'function') {
                        clickResult = onClick();
                    }
                } finally {
                    // 如果 onClick 返回了“可 destroy 的对象”（例如 dialog），锁到它销毁再恢复
                    if (
                        config.lockWhileReturnedAlive &&
                        clickResult &&
                        clickResult !== button &&
                        typeof clickResult.once === 'function'
                    ) {
                        clickResult.once('destroy', () => {
                            enableButtonInput();
                            resetVisual();
                        });
                        return;
                    }

                    // 否则按 cooldown 解锁（0 则立即解锁）
                    if (clickCooldownMs > 0 && scene?.time && typeof scene.time.delayedCall === 'function') {
                        unlockTimer = scene.time.delayedCall(clickCooldownMs, () => {
                            enableButtonInput();
                            resetVisual();
                        });
                    } else if (clickCooldownMs > 0) {
                        unlockTimer = setTimeout(() => {
                            unlockTimer = null;
                            enableButtonInput();
                            resetVisual();
                        }, clickCooldownMs);
                    } else {
                        enableButtonInput();
                        resetVisual();
                    }
                }
            }
        });

        if (scene.audioManager && typeof scene.audioManager.playButtonClick === 'function') {
            scene.audioManager.playButtonClick();
        }
    });

    // 入场动画控制
    if (config.autoPlayAnim) {
        button.setScale(0);
        scene.tweens.add({
            targets: button,
            scaleX: 1,
            scaleY: 1,
            duration: BUTTON_STYLES.entrance.duration,
            ease: BUTTON_STYLES.entrance.ease,
            delay: Math.random() * BUTTON_STYLES.entrance.maxDelay
        });
    }

    return button;
}

/**
 * 创建 RexUI 对话框（支持按钮控制是否关闭对话框）
 * @param {Phaser.Scene} scene
 * @param {string} title
 * @param {string} content
 * @param {Array} buttons
 * @param {string} buttons[].text
 * @param {Function} buttons[].callback
 * @param {boolean} buttons[].closeDialog
 * @returns {RexUI.Dialog}
 */
export function createDialog(scene, title, content, buttons = [], options = {}) {
    if (!scene.rexUI) {
        console.error('RexUI plugin not loaded! Cannot create dialog.');
        return null;
    }

    const opts = options || {};
    const singleton = opts.singleton !== false;
    const singletonKey = opts.singletonKey || `dialog:${title}`;

    const getSceneMaxDepth = () => {
        const list = scene?.children?.list || [];
        let max = 0;
        for (let i = 0; i < list.length; i++) {
            const go = list[i];
            if (!go) continue;
            const d = (typeof go.depth === 'number') ? go.depth : 0;
            if (d > max) max = d;
        }
        return max;
    };

    // 让弹窗/蒙层永远处在“当前场景最高层之上”，避免 scrollablePanel / UI 自抬 depth 后穿透
    const depthPadding = Number.isFinite(opts.depthPadding) ? opts.depthPadding : 20;
    const baseDepth = Number.isFinite(opts.baseDepth)
        ? opts.baseDepth
        : Math.max(DIALOG_STYLES.depth.base, getSceneMaxDepth() + depthPadding);

    const coverDepth = baseDepth;
    const dialogDepth = baseDepth + 1;
    const buttonDepth = baseDepth + 2;

    const coverColor = (typeof opts.coverColor === 'number') ? opts.coverColor : DIALOG_STYLES.modal.coverColor;
    const coverAlpha = (typeof opts.coverAlpha === 'number') ? opts.coverAlpha : DIALOG_STYLES.modal.coverAlpha;
    const closeOnBlocker = opts.closeOnBlocker !== undefined ? !!opts.closeOnBlocker : true;

    const resetSceneButtonVisuals = () => {
        const list = scene?.children?.list || [];
        for (let i = 0; i < list.length; i++) {
            const go = list[i];
            if (go && typeof go.__uiResetVisual === 'function') {
                go.__uiResetVisual();
            }
        }
    };

    const ensureModalBlocker = (targetDialog) => {
        if (targetDialog && targetDialog.__uiModalBlocker && targetDialog.__uiModalBlocker.scene) {
            const blk = targetDialog.__uiModalBlocker;
            blk.setDepth(coverDepth);
            blk.setVisible(true);
            return blk;
        }

        const cam = scene.cameras.main;
        const blocker = scene.add.rectangle(0, 0, cam.width, cam.height, coverColor, coverAlpha)
            .setOrigin(0, 0)
            .setScrollFactor(0)
            .setDepth(coverDepth);

        if (typeof blocker.setInteractive === 'function') {
            blocker.setInteractive();
        }

        if (closeOnBlocker && typeof blocker.on === 'function') {
            blocker.on('pointerup', () => {
                if (targetDialog && typeof targetDialog.closeDialog === 'function') {
                    targetDialog.closeDialog();
                } else if (targetDialog && typeof targetDialog.modalClose === 'function') {
                    targetDialog.modalClose();
                }
            });
        }

        if (targetDialog) {
            targetDialog.__uiModalBlocker = blocker;
            if (typeof targetDialog.once === 'function') {
                targetDialog.once('destroy', () => {
                    if (blocker && blocker.scene && typeof blocker.destroy === 'function') {
                        blocker.destroy();
                    }
                });
            }
        }

        return blocker;
    };

    if (singleton) {
        if (!scene.__uiSingletonDialogs) scene.__uiSingletonDialogs = new Map();
        const existing = scene.__uiSingletonDialogs.get(singletonKey);
        if (existing && existing.scene && existing.active !== false) {
            // 弹窗出现时，底层按钮可能因遮罩吃掉 pointerout 而卡在 hover/pressed，先强制复位
            resetSceneButtonVisuals();
            ensureModalBlocker(existing);

            if (typeof existing.setDepth === 'function') existing.setDepth(dialogDepth);
            if (typeof existing.setVisible === 'function') existing.setVisible(true);
            if (typeof existing.layout === 'function') existing.layout();

            // 尽量把 actions 也抬上去（部分 rexUI 版本 actions 不是 dialog 的 children）
            const actions = existing.getElement ? existing.getElement('actions') : null;
            if (actions && typeof actions.setDepth === 'function') actions.setDepth(buttonDepth);

            return existing;
        }
    }

    let dialog;

    // 复用 createButton 生成按钮数组
    const actionButtons = buttons.map((btnConfig, index) => {
        const isPrimary = index === 0;
        const shouldCloseDialog = btnConfig.closeDialog !== false;

        const wrappedCallback = () => {
            if (btnConfig.callback && typeof btnConfig.callback === 'function') {
                btnConfig.callback(dialog);
            }

            if (shouldCloseDialog && dialog) {
                actionButtons.forEach(btn => {
                    btn.disableInteractive();
                    scene.tweens.killTweensOf(btn);
                    btn.setScale(1);
                });
                dialog.modalClose();
            }
        };

        return createButton(scene, 0, 0, btnConfig.text, wrappedCallback, {
            backgroundColor: isPrimary ? COLORS.primary : COLORS.secondary,
            hoverColor: isPrimary ? COLORS.light : COLORS.secondaryLight,
            pressColor: isPrimary ? COLORS.dark : COLORS.secondaryDark,
            fontSize: DIALOG_STYLES.button.fontSize,
            cornerRadius: DIALOG_STYLES.button.cornerRadius,
            autoPlayAnim: false,
            clickCooldown: 0
        });
    });

    // 创建 Dialog 组件
    dialog = scene.rexUI.add.dialog({
        x: scene.cameras.main.centerX,
        y: scene.cameras.main.centerY,
        width: DIALOG_STYLES.width,

        background: createRoundRectangle(
            scene,
            COLORS.dialogBg,
            DIALOG_STYLES.cornerRadius,
            COLORS.dialogStroke,
            DIALOG_STYLES.strokeWidth
        ),

        title: scene.add.text(0, 0, title, {
            fontSize: DIALOG_STYLES.title.fontSize,
            fontWeight: DIALOG_STYLES.title.fontWeight,
            color: TYPOGRAPHY.textColor.primary,
            fontFamily: TYPOGRAPHY.fontFamily.default
        }),

        content: scene.rexUI.add.BBCodeText(0, 0, content, {
            fontSize: DIALOG_STYLES.content.fontSize,
            color: TYPOGRAPHY.textColor.primary,
            align: 'center',
            fontFamily: TYPOGRAPHY.fontFamily.default,
            wrap: { mode: 'word', width: DIALOG_STYLES.content.wrapWidth }
        }),

        actions: actionButtons,

        space: DIALOG_STYLES.padding,

        align: {
            title: 'center',
            content: 'center',
            actions: 'center'
        },

        expand: {
            title: false,
            content: false,
            actions: false
        }
    });

    // 先复位底层按钮视觉（避免卡 hover/pressed），再放置可见且可交互的蒙层
    resetSceneButtonVisuals();
    ensureModalBlocker(dialog);

    dialog.setDepth(dialogDepth);
    dialog.layout();
    dialog.setVisible(true);
    dialog.setScale(1);
    actionButtons.forEach(btn => btn.setDepth(buttonDepth));

    if (singleton) {
        scene.__uiSingletonDialogs.set(singletonKey, dialog);
        if (dialog && typeof dialog.once === 'function') {
            dialog.once('destroy', () => {
                if (scene.__uiSingletonDialogs) {
                    scene.__uiSingletonDialogs.delete(singletonKey);
                }
            });
        }
    }

    // 模态弹出：RexUI 自带 cover 设为透明，避免与我们自建 blocker 叠加造成层级/视觉不一致
    dialog.modalPromise({
        manualClose: true,
        defaultBehavior: false,
        duration: {
            in: DIALOG_STYLES.modal.durationIn,
            out: DIALOG_STYLES.modal.durationOut
        },
        cover: {
            color: coverColor,
            alpha: 0,
            depth: coverDepth
        },
        transitIn: function (gameObject, duration) {
            gameObject.setVisible(true);
            gameObject.setScale(1);
            return scene.tweens.add({
                targets: gameObject,
                scaleX: { from: 0.1, to: 1 },
                scaleY: { from: 0.1, to: 1 },
                duration: duration,
                ease: DIALOG_STYLES.animation.transitInEase
            });
        },
        transitOut: function (gameObject, duration) {
            return scene.tweens.add({
                targets: gameObject,
                scaleX: 0,
                scaleY: 0,
                duration: duration,
                ease: DIALOG_STYLES.animation.transitOutEase
            });
        }
    }).then(() => {
        dialog.destroy();
    });

    // 提供统一的内容刷新方法
    dialog.setContentText = function (text) {
        const contentElement = dialog.getElement('content');
        if (contentElement) {
            contentElement.setText(text);
            dialog.layout();
        }
    };

    // 添加手动关闭方法
    dialog.closeDialog = function () {
        actionButtons.forEach(btn => {
            btn.disableInteractive();
            scene.tweens.killTweensOf(btn);
            btn.setScale(1);
        });
        dialog.modalClose();
    };

    return dialog;
}

/**
 * 创建粒子爆炸效果
 * @param {Phaser.Scene} scene
 * @param {number} x
 * @param {number} y
 * @param {string} texture - 粒子纹理键
 * @param {number} count - 粒子数量
 */
export function createParticleExplosion(scene, x, y, texture = 'particle', count = 20) {
    const emitter = scene.add.particles(x, y, texture, {
        speed: PARTICLE_STYLES.speed,
        angle: PARTICLE_STYLES.angle,
        scale: PARTICLE_STYLES.scale,
        lifespan: PARTICLE_STYLES.lifespan,
        blendMode: PARTICLE_STYLES.blendMode,
        tint: PARTICLE_STYLES.tint
    });

    if (emitter && typeof emitter.explode === 'function') {
        emitter.explode(count);
    }

    // 自动销毁
    scene.time.delayedCall(PARTICLE_STYLES.destroyDelay, () => {
        if (emitter && typeof emitter.destroy === 'function') {
            emitter.destroy();
        }
    });

    return emitter;
}

/**
 * 创建浮动文本（伤害数字/得分提示）
 * @param {Phaser.Scene} scene
 * @param {number} x
 * @param {number} y
 * @param {string} text
 * @param {string} color
 */
export function createFloatingText(scene, x, y, text, color = TYPOGRAPHY.textColor.primary) {
    const floatText = scene.add.text(x, y, text, {
        fontFamily: TYPOGRAPHY.fontFamily.pixel,
        fontSize: FLOATING_TEXT_STYLES.fontSize,
        color: color,
        stroke: FLOATING_TEXT_STYLES.strokeColor,
        strokeThickness: FLOATING_TEXT_STYLES.strokeThickness
    }).setOrigin(0.5);

    scene.tweens.add({
        targets: floatText,
        y: y + FLOATING_TEXT_STYLES.animation.offsetY,
        alpha: 0,
        duration: FLOATING_TEXT_STYLES.animation.duration,
        ease: FLOATING_TEXT_STYLES.animation.ease,
        onComplete: () => {
            if (floatText && typeof floatText.destroy === 'function') {
                floatText.destroy();
            }
        }
    });

    return floatText;
}

/**
 * 创建自适应缩放的图片
 * @param {Phaser.Scene} scene
 * @param {number} x
 * @param {number} y
 * @param {string} texture
 * @param {number} maxWidth - 最大宽度
 * @param {number} maxHeight - 最大高度(可选)
 */
export function createScaledImage(scene, x, y, texture, maxWidth, maxHeight = null) {
    const image = scene.add.image(x, y, texture);
    
    // 如果没有指定高度，使用宽度
    if (!maxHeight) {
        maxHeight = maxWidth;
    }
    
    // 计算缩放比例
    const scaleX = maxWidth / image.width;
    const scaleY = maxHeight / image.height;
    const scale = Math.min(scaleX, scaleY);
    
    if (image && typeof image.setScale === 'function') {
        image.setScale(scale);
    }
    
    return image;
}

/**
 * 创建 BBCodeText 富文本对象（用于剧情对话框）
 * @param {Phaser.Scene} scene
 * @param {number} wrapWidth - 文本换行宽度
 * @param {number} fixedWidth - 固定宽度
 * @param {number} fixedHeight - 固定高度
 * @returns {RexUI.BBCodeText}
 */
function createBBCodeText(scene, wrapWidth, fixedWidth, fixedHeight) {
    return scene.rexUI.add.BBCodeText(0, 0, '', {
        fixedWidth: fixedWidth,
        fixedHeight: fixedHeight,
        fontSize: STORY_TEXTBOX_STYLES.textBox.fontSize,
        color: TYPOGRAPHY.textColor.primary,
        fontFamily: TYPOGRAPHY.fontFamily.default,
        wrap: {
            mode: 'word',
            width: wrapWidth
        },
        maxLines: STORY_TEXTBOX_STYLES.textBox.maxLines
    });
}

/**
 * 创建剧情对话框（带打字机效果）
 * @param {Phaser.Scene} scene
 * @param {Object} config - 配置对象
 * @param {string} config.characterName - 角色名称
 * @param {string} config.content - 对话内容（支持BBCode标签）
 * @param {string} config.avatarTexture - 头像纹理键（可选）
 * @param {number} config.typingSpeed - 打字速度（毫秒/字符，默认50）
 * @param {Function} config.onComplete - 剧情结束回调（可选）
 * @returns {Object} - 返回包含 textBox 和 nameLabel 的对象
 */
export function createStoryTextBox(scene, config = {}) {
    if (!scene.rexUI) {
        console.error('RexUI plugin not loaded! Cannot create story textbox.');
        return null;
    }

    const {
        characterName = '旁白',
        content = '',
        avatarTexture = null,
        typingSpeed = STORY_TEXTBOX_STYLES.typing.defaultSpeed,
        onComplete = null,
        x = scene.cameras.main.centerX,
        y = scene.cameras.main.height + STORY_TEXTBOX_STYLES.defaultPosition.yOffset
    } = config;

    // 1. 创建名字标签
    const nameLabel = scene.rexUI.add.label({
        background: createRoundRectangle(
            scene, 
            COLORS.storyPrimary, 
            STORY_TEXTBOX_STYLES.nameLabel.cornerRadius, 
            COLORS.storyLight, 
            STORY_TEXTBOX_STYLES.nameLabel.strokeWidth
        ),
        text: scene.add.text(0, 0, characterName, {
            fontSize: STORY_TEXTBOX_STYLES.nameLabel.fontSize,
            color: TYPOGRAPHY.textColor.primary,
            fontFamily: TYPOGRAPHY.fontFamily.default,
            fontWeight: TYPOGRAPHY.fontWeight.bold
        }),
        space: STORY_TEXTBOX_STYLES.nameLabel.padding
    });

    // 2. 创建主对话框容器
    const { wrapWidth, fixedWidth, fixedHeight } = STORY_TEXTBOX_STYLES.textBox;

    const textBox = scene.rexUI.add.textBox({
        background: createRoundRectangle(
            scene, 
            COLORS.storyPrimary, 
            STORY_TEXTBOX_STYLES.textBox.cornerRadius, 
            COLORS.storyLight, 
            STORY_TEXTBOX_STYLES.textBox.strokeWidth
        ),

        icon: avatarTexture ? 
            scene.add.image(0, 0, avatarTexture).setDisplaySize(
                STORY_TEXTBOX_STYLES.avatar.displayWidth, 
                STORY_TEXTBOX_STYLES.avatar.displayHeight
            ) : undefined,

        text: createBBCodeText(scene, wrapWidth, fixedWidth, fixedHeight),

        action: scene.add.image(0, 0, 'nextPageIcon')
            .setTint(COLORS.storyLight)
            .setVisible(false)
            .setDisplaySize(
                STORY_TEXTBOX_STYLES.actionIcon.displayWidth, 
                STORY_TEXTBOX_STYLES.actionIcon.displayHeight
            ),

        space: {
            left: STORY_TEXTBOX_STYLES.padding.left,
            right: STORY_TEXTBOX_STYLES.padding.right,
            top: STORY_TEXTBOX_STYLES.padding.top,
            bottom: STORY_TEXTBOX_STYLES.padding.bottom,
            icon: avatarTexture ? STORY_TEXTBOX_STYLES.padding.icon : 0,
            text: STORY_TEXTBOX_STYLES.padding.text
        }
    });

    // 3. 组合名字栏和对话框
    const storyDialog = scene.rexUI.add.sizer({
        orientation: 'y',
        x: x,
        y: y
    })
    .add(nameLabel, { 
        align: 'left', 
        padding: { 
            bottom: STORY_TEXTBOX_STYLES.padding.nameBottom, 
            left: STORY_TEXTBOX_STYLES.padding.nameLeft 
        } 
    })
    .add(textBox)
    .layout();

    // 4. 设置层级
    storyDialog.setDepth(STORY_TEXTBOX_STYLES.depth);

    // 5. 添加"继续"图标呼吸动画
    const actionIcon = textBox.getElement('action');
    if (actionIcon) {
        scene.tweens.add({
            targets: actionIcon,
            scaleX: STORY_TEXTBOX_STYLES.typing.iconBreath.scale,
            scaleY: STORY_TEXTBOX_STYLES.typing.iconBreath.scale,
            duration: STORY_TEXTBOX_STYLES.typing.iconBreath.duration,
            yoyo: true,
            repeat: -1,
            ease: STORY_TEXTBOX_STYLES.typing.iconBreath.ease
        });
    }

    // 6. 启动打字机效果
    textBox.start(content, typingSpeed);

    // 7. 交互逻辑
    let isCurrentlyTyping = false;

    textBox.on('type', function () {
        isCurrentlyTyping = true;
        if (actionIcon) actionIcon.setVisible(false);
        // 播放打字机音效
        if (scene.audioManager && typeof scene.audioManager.playTypewriter === 'function') {
            scene.audioManager.playTypewriter();
        }
    });

    textBox.on('pageend', function () {
        isCurrentlyTyping = false;
        if (actionIcon) actionIcon.setVisible(true);
    });

    // 8. 全局点击交互
    const clickHandler = function () {
        if (isCurrentlyTyping) {
            textBox.stop(true);
            isCurrentlyTyping = false;
            if (actionIcon) actionIcon.setVisible(true);
        } else {
            if (!textBox.isLastPage) {
                textBox.typeNextPage();
                if (actionIcon) actionIcon.setVisible(false);
            } else {
                console.log('剧情对话结束');
                scene.input.off('pointerdown', clickHandler);
                
                scene.tweens.add({
                    targets: storyDialog,
                    alpha: 0,
                    y: y + STORY_TEXTBOX_STYLES.animation.exit.offsetY,
                    duration: STORY_TEXTBOX_STYLES.animation.exit.duration,
                    ease: STORY_TEXTBOX_STYLES.animation.exit.ease,
                    onComplete: () => {
                        storyDialog.destroy();
                        if (onComplete && typeof onComplete === 'function') {
                            onComplete();
                        }
                    }
                });
            }
        }
    };

    scene.input.on('pointerdown', clickHandler);

    // 9. 入场动画
    storyDialog.setAlpha(0);
    storyDialog.y += STORY_TEXTBOX_STYLES.animation.entrance.offsetY;
    scene.tweens.add({
        targets: storyDialog,
        alpha: 1,
        y: y,
        duration: STORY_TEXTBOX_STYLES.animation.entrance.duration,
        ease: STORY_TEXTBOX_STYLES.animation.entrance.ease
    });

    // 10. 返回对话框对象
    return {
        textBox: textBox,
        nameLabel: nameLabel,
        container: storyDialog,
        destroy: () => {
            scene.input.off('pointerdown', clickHandler);
            storyDialog.destroy();
        }
    };
}

export function createScrollableList(scene, config = {}) {
    if (!scene.rexUI) return null;

    const {
        x = 0, y = 0, width = 400, height = 500,
        headerTitle = 'Inventory',
        items = [],
        depth = 0
    } = config;

    const STYLES = {
        bg: 0x2d2d2d, stroke: 0x5e92f3,
        track: 0x1a1a1a, thumb: 0x5e92f3,
        // space.panel -> rexScrollablePanel 内部会映射为 space.child
        // - panel.right: 给可滚动内容留出右侧内边距，避免贴着滚动条
        // - slider: 控制 panel 与滚动条之间的间距
        space: {
            left: 10,
            right: 10,
            top: 10,
            bottom: 10,
            item: 5,
            panel: { left: 0, right: 12 },
            slider: 10
        }
    };

    // 1. 内容容器
    const contentPanel = scene.rexUI.add.sizer({
        orientation: 'y',
        space: { item: STYLES.space.item }
    });

    // 用于处理“先创建 item 再创建面板”导致的遮挡：统一把 item 深度跟随面板
    const itemNodes = [];

    // 2. 主面板
    const scrollable = scene.rexUI.add.scrollablePanel({
        x: x, y: y,
        width: width, height: height,
        scrollMode: 0,

        background: scene.rexUI.add.roundRectangle(0, 0, 2, 2, 10, STYLES.bg)
            .setStrokeStyle(2, STYLES.stroke),

        panel: {
            child: contentPanel,
            mask: { padding: 1 }
        },

        slider: {
            // 竖向滚动条：把 thumb 做成“细长胶囊条”，避免显示成圆点
            track: scene.rexUI.add.roundRectangle(0, 0, 12, 10, 6, STYLES.track),
            thumb: scene.rexUI.add.roundRectangle(0, 0, 12, 60, 6, STYLES.thumb),
            input: 'drag',

            // === CRITICAL FIXES ===
            hideUnscrollableSlider: true, // 不需要滚动时隐藏，打断部分版本的死循环
            minThumbSize: 48,             // 保底最小高度，让它始终像“长条”
            // ======================
        },

        scroller: {
            threshold: 10,
            slidingDeceleration: 5000,
            backDeceleration: 2000,
        },

        header: scene.rexUI.add.label({
            // 标题区域稍微加高，避免文字上沿被裁切
            height: 52,
            orientation: 'x',
            background: scene.rexUI.add.roundRectangle(0, 0, 2, 2, { tl: 10, tr: 10 }, 0x444444),
            text: scene.add.text(0, 0, headerTitle, {
                fontSize: '20px',
                fontStyle: 'bold',
                padding: { x: 0, y: 2 }
            }).setOrigin(0, 0.5),
            space: { left: 12, top: 8, bottom: 8 }
        }),

        space: STYLES.space
    });

    const originalSetDepth = scrollable.setDepth ? scrollable.setDepth.bind(scrollable) : null;
    scrollable.setDepthAll = (d) => {
        if (originalSetDepth) originalSetDepth(d);
        // 确保列表 item 不会被 panel 背景盖住
        itemNodes.forEach((n) => {
            if (n && typeof n.setDepth === 'function') n.setDepth(d + 1);
        });
        return scrollable;
    };
    // 兼容外部继续调用 list.setDepth(...)
    if (originalSetDepth) scrollable.setDepth = scrollable.setDepthAll;

    // 3. 挂载方法
    // item 支持：
    // - string: 简单文本项
    // - { text, onClick?, backgroundColor? }
    // - { type: 'spacer', height }
    // - { node: RexUI/GameObject, expand? }
    scrollable.addItem = (item) => {
        // spacer
        if (item && typeof item === 'object' && item.type === 'spacer') {
            const h = Math.max(1, item.height || STYLES.space.item);
            const zone = scene.add.zone(0, 0, 1, h);
            contentPanel.add(zone, 0, 'center', 0, true);
            itemNodes.push(zone);
            return;
        }

        // custom node
        if (item && typeof item === 'object' && item.node) {
            const node = item.node;
            // 这里用 RexUI 更通用的签名：add(child, proportion, align, padding, expand)
            contentPanel.add(node, 0, 'center', 0, item.expand !== false);
            itemNodes.push(node);
            // 尽量立即同步一次深度（外部也可能在之后再 setDepth）
            if (typeof scrollable.depth === 'number' && typeof node.setDepth === 'function') {
                node.setDepth(scrollable.depth + 1);
            }
            return;
        }

        // normalize text item
        const text = typeof item === 'string' ? item : (item?.text ?? '');
        const bgColor = (typeof item === 'object' && item.backgroundColor != null) ? item.backgroundColor : 0x3e3e3e;
        const hoverColor = 0x555555;

        const bg = scene.rexUI.add.roundRectangle(0, 0, 20, 20, 8, bgColor);
        const label = scene.rexUI.add.label({
            height: 52,
            orientation: 'x',
            background: bg,
            text: scene.add.text(0, 0, text, { fontSize: '16px' }),
            space: { left: 12, right: 12, top: 10, bottom: 10 },
        });

        label.setInteractive({ useHandCursor: true })
            .on('pointerover', () => bg.setFillStyle(hoverColor))
            .on('pointerout', () => bg.setFillStyle(bgColor));

        if (item && typeof item === 'object' && typeof item.onClick === 'function') {
            label.on('pointerup', () => item.onClick(item));
        }

        contentPanel.add(label, 0, 'center', 0, true);
        itemNodes.push(label);
    };

    // 4. 填充数据
    if (items.length > 0) {
        items.forEach((item) => scrollable.addItem(item));
    } else {
        // 防止空列表导致的高度计算错误
        const zone = scene.add.zone(0, 0, 1, 1);
        contentPanel.add(zone, 0, 'center', 0, true);
        itemNodes.push(zone);
    }

    scrollable.layout();
    // 初始深度（如果外部之后会 setDepth，这里也会被覆盖并自动同步到 items）
    scrollable.setDepthAll(depth);

    return scrollable;
}