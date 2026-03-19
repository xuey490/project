/**
 * Style Configuration
 * 统一管理所有 UI 组件的样式常量
 * 避免硬编码的"魔法数字"和"魔法字符串"
 */

// ============================================
// 颜色配置 (Colors)
// ============================================
export const COLORS = {
    // 主要颜色
    primary: 0x4CAF50,
    light: 0x66BB6A,
    dark: 0x388E3C,
    white: 0xffffff,
    black: 0x000000,
    
    // 次要颜色
    secondary: 0x2196F3,
    secondaryLight: 0x42A5F5,
    secondaryDark: 0x1976D2,
    
    // 警告颜色
    warning: 0xFF9800,
    warningLight: 0xFFB74D,
    warningDark: 0xF57C00,
    
    // 对话框颜色
    dialogBg: 0x1976D2,
    dialogStroke: 0xffffff,
    
    // 剧情对话框颜色
    storyPrimary: 0x4e342e,
    storyLight: 0x7b5e57,
    storyDark: 0x260e04,
    
    // 粒子效果颜色
    particleRed: 0xff0000,
    particleOrange: 0xff9800,
    particleYellow: 0xffeb3b
};

// ============================================
// 字体配置 (Typography)
// ============================================
export const TYPOGRAPHY = {
    fontFamily: {
        default: 'Arial, sans-serif',
        pixel: '"Press Start 2P"',
        monospace: 'Courier New, monospace'
    },
    
    fontSize: {
        xs: '14px',
        sm: '16px',
        md: '18px',
        lg: '20px',
        xl: '24px',
        xxl: '32px'
    },
    
    fontWeight: {
        normal: 'normal',
        bold: 'bold',
        bolder: 'bolder'
    },
    
    textColor: {
        primary: '#ffffff',
        secondary: '#cccccc',
        dark: '#000000',
        disabled: '#666666'
    }
};

// ============================================
// 按钮配置 (Button Styles)
// ============================================
export const BUTTON_STYLES = {
    // 默认按钮尺寸
    fontSize: 20,
    cornerRadius: 15,
    strokeWidth: 2,
    
    // 内边距
    padding: {
        left: 20,
        right: 20,
        top: 15,
        bottom: 15
    },
    
    // 交互动画参数
    hover: {
        scale: 1.05,
        duration: 100,
        ease: 'Back.easeOut'
    },
    
    normal: {
        scale: 1,
        duration: 100,
        ease: 'Back.easeIn'
    },
    
    press: {
        scale: 0.95,
        duration: 50,
        ease: 'Sine.easeInOut'
    },
    
    release: {
        scale: 1.05,
        duration: 50,
        ease: 'Sine.easeInOut'
    },
    
    // 入场动画
    entrance: {
        duration: 300,
        ease: 'Back.easeOut',
        maxDelay: 200
    }
};

// ============================================
// 对话框配置 (Dialog Styles)
// ============================================
export const DIALOG_STYLES = {
    // 基础尺寸
    width: 400,
    cornerRadius: 20,
    strokeWidth: 3,
    
    // 标题样式
    title: {
        fontSize: '24px',
        fontWeight: 'bold'
    },
    
    // 内容样式
    content: {
        fontSize: '18px',
        wrapWidth: 340
    },
    
    // 按钮样式
    button: {
        fontSize: 18,
        cornerRadius: 10
    },
    
    // 内边距
    padding: {
        left: 20,
        right: 20,
        top: 20,
        bottom: 20,
        title: 25,
        content: 30,
        action: 15
    },
    
    // 深度层级
    depth: {
        base: 100,
        button: 101
    },
    
    // 模态遮罩
    modal: {
        coverColor: 0x000000,
        coverAlpha: 0.7,
        durationIn: 500,
        durationOut: 300
    },
    
    // 动画
    animation: {
        transitInEase: 'Back.easeOut',
        transitOutEase: 'Back.easeIn'
    }
};

// ============================================
// 剧情对话框配置 (Story TextBox Styles)
// ============================================
export const STORY_TEXTBOX_STYLES = {
    // 名字标签样式
    nameLabel: {
        fontSize: '18px',
        cornerRadius: 10,
        strokeWidth: 2,
        padding: {
            left: 15,
            right: 15,
            top: 8,
            bottom: 8
        }
    },
    
    // 主对话框样式
    textBox: {
        wrapWidth: 500,
        fixedWidth: 520,
        fixedHeight: 80,
        cornerRadius: 20,
        strokeWidth: 2,
        fontSize: '20px',
        maxLines: 3
    },
    
    // 头像样式
    avatar: {
        displayWidth: 60,
        displayHeight: 60
    },
    
    // 继续图标样式
    actionIcon: {
        displayWidth: 24,
        displayHeight: 24
    },
    
    // 内边距
    padding: {
        left: 20,
        right: 20,
        top: 20,
        bottom: 20,
        icon: 15,
        text: 10,
        nameBottom: 5,
        nameLeft: 25
    },
    
    // 深度层级
    depth: 200,
    
    // 打字机效果
    typing: {
        defaultSpeed: 50, // 毫秒/字符
        iconBreath: {
            scale: 1.3,
            duration: 500,
            ease: 'Sine.easeInOut'
        }
    },
    
    // 动画
    animation: {
        entrance: {
            offsetY: 50,
            duration: 400,
            ease: 'Cubic.easeOut'
        },
        exit: {
            offsetY: 50,
            duration: 300,
            ease: 'Cubic.easeIn'
        }
    },
    
    // 默认位置
    defaultPosition: {
        yOffset: -150 // 距离屏幕底部的偏移
    }
};

// ============================================
// 浮动文本配置 (Floating Text Styles)
// ============================================
export const FLOATING_TEXT_STYLES = {
    fontSize: '24px',
    strokeColor: '#000000',
    strokeThickness: 4,
    
    animation: {
        offsetY: -100,
        duration: 1500,
        ease: 'Cubic.easeOut'
    }
};

// ============================================
// 粒子效果配置 (Particle Effect Styles)
// ============================================
export const PARTICLE_STYLES = {
    speed: {
        min: -200,
        max: 200
    },
    
    angle: {
        min: 0,
        max: 360
    },
    
    scale: {
        start: 0.1,
        end: 0
    },
    
    lifespan: 600,
    blendMode: 'ADD',
    
    // 颜色组合
    tint: [COLORS.particleRed, COLORS.particleOrange, COLORS.particleYellow],
    
    // 自动销毁延迟
    destroyDelay: 1000
};

// ============================================
// 圆角矩形默认配置 (RoundRectangle Defaults)
// ============================================
export const ROUND_RECTANGLE_DEFAULTS = {
    cornerRadius: 15,
    strokeWidth: 0,
    strokeColor: null,
    fallbackSize: {
        width: 100,
        height: 50
    }
};

// ============================================
// 导出所有配置的组合对象（可选）
// ============================================
export const UI_STYLES = {
    colors: COLORS,
    typography: TYPOGRAPHY,
    button: BUTTON_STYLES,
    dialog: DIALOG_STYLES,
    storyTextBox: STORY_TEXTBOX_STYLES,
    floatingText: FLOATING_TEXT_STYLES,
    particle: PARTICLE_STYLES,
    roundRect: ROUND_RECTANGLE_DEFAULTS
};

// ============================================
// 辅助函数：获取字体样式字符串
// ============================================
export function getFontStyle(size = 'md', family = 'default', weight = 'normal') {
    return {
        fontSize: TYPOGRAPHY.fontSize[size] || TYPOGRAPHY.fontSize.md,
        fontFamily: TYPOGRAPHY.fontFamily[family] || TYPOGRAPHY.fontFamily.default,
        fontWeight: TYPOGRAPHY.fontWeight[weight] || TYPOGRAPHY.fontWeight.normal
    };
}

// ============================================
// 辅助函数：获取颜色值
// ============================================
export function getColor(colorName) {
    return COLORS[colorName] || COLORS.white;
}
