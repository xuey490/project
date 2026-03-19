import Phaser from 'phaser';

/**
 * Loading Scene - AI Generation Waiting Page
 * Loading 场景 - AI 生成等待页面
 * 
 * Displays a cute loading animation with bilingual support (EN/CN)
 * 显示可爱的加载动画，支持双语（中英文）
 */
export class Loading extends Phaser.Scene {
    constructor() {
        super('Loading');
    }

    create() {
        const { width, height } = this.cameras.main;
        const centerX = width / 2;
        const centerY = height / 2;

        // Gradient background / 渐变背景
        this.createGradientBackground(width, height);

        // Twinkling stars / 闪烁星星
        this.createStars(width, height);

        // Loading animation with airplane / 小飞机加载动画
        this.createLoadingAnimation(centerX, centerY);

        // Loading text (bilingual) / 加载文字（双语）
        this.createLoadingText(centerX, centerY);

        // Bottom tips (bilingual) / 底部提示（双语）
        this.createTips(centerX, height);

        // Floating decorations / 浮动装饰
        this.createFloatingDecorations(width, height);
    }

    createGradientBackground(width, height) {
        const graphics = this.add.graphics();
        
        // Gradient from deep purple to deep blue / 深紫到深蓝渐变
        const colors = [
            { y: 0, color: 0x1a0a2e },
            { y: 0.25, color: 0x16213e },
            { y: 0.5, color: 0x0f3460 },
            { y: 0.75, color: 0x1a1a4e },
            { y: 1, color: 0x0a0a20 }
        ];

        for (let i = 0; i < colors.length - 1; i++) {
            const startY = colors[i].y * height;
            const endY = colors[i + 1].y * height;
            const segmentHeight = endY - startY;
            
            graphics.fillStyle(colors[i].color, 1);
            graphics.fillRect(0, startY, width, segmentHeight);
        }
    }

    createStars(width, height) {
        this.stars = [];
        
        for (let i = 0; i < 50; i++) {
            const x = Phaser.Math.Between(0, width);
            const y = Phaser.Math.Between(0, height);
            const size = Phaser.Math.Between(1, 3);
            const alpha = Phaser.Math.FloatBetween(0.3, 1);
            
            const star = this.add.circle(x, y, size, 0xffffff, alpha);
            this.stars.push(star);
            
            // Twinkling animation / 闪烁动画
            this.tweens.add({
                targets: star,
                alpha: { from: alpha, to: alpha * 0.3 },
                duration: Phaser.Math.Between(1000, 3000),
                yoyo: true,
                repeat: -1,
                ease: 'Sine.easeInOut'
            });
        }
    }

    createLoadingAnimation(centerX, centerY) {
        // Outer rotating ring / 外圈旋转光环
        this.outerRing = this.add.graphics();
        this.drawRing(this.outerRing, 0, 0, 80, 4, 0x6c5ce7, 0.8);
        this.outerRing.setPosition(centerX, centerY - 40);

        // Inner rotating ring / 内圈旋转光环
        this.innerRing = this.add.graphics();
        this.drawRing(this.innerRing, 0, 0, 55, 3, 0xa29bfe, 0.6);
        this.innerRing.setPosition(centerX, centerY - 40);

        // Center rocket container / 中心火箭容器
        this.centerIcon = this.add.container(centerX, centerY - 40);
        
        // Create rocket / 创建火箭
        this.createRocket();

        // Ring rotation animations / 光环旋转动画
        this.tweens.add({
            targets: this.outerRing,
            angle: 360,
            duration: 3000,
            repeat: -1,
            ease: 'Linear'
        });

        this.tweens.add({
            targets: this.innerRing,
            angle: -360,
            duration: 2000,
            repeat: -1,
            ease: 'Linear'
        });

        // Energy particles / 能量粒子
        this.createEnergyParticles(centerX, centerY - 40);
    }

    drawRing(graphics, x, y, radius, lineWidth, color, alpha) {
        graphics.lineStyle(lineWidth, color, alpha);
        
        // Dashed circle effect / 虚线圆环效果
        const segments = 8;
        const gap = 0.15;
        
        for (let i = 0; i < segments; i++) {
            const startAngle = (i / segments) * Math.PI * 2;
            const endAngle = startAngle + (1 / segments - gap) * Math.PI * 2;
            
            graphics.beginPath();
            graphics.arc(x, y, radius, startAngle, endAngle, false);
            graphics.strokePath();
        }
    }

    createRocket() {
        const graphics = this.add.graphics();
        
        // Rocket body / 火箭身体
        graphics.fillStyle(0xdfe6e9, 1);
        graphics.beginPath();
        graphics.moveTo(0, -35);     // Nose tip / 火箭尖端
        graphics.lineTo(12, -15);    // Right curve / 右侧曲线
        graphics.lineTo(12, 25);     // Right body / 右侧身体
        graphics.lineTo(-12, 25);    // Left body / 左侧身体
        graphics.lineTo(-12, -15);   // Left curve / 左侧曲线
        graphics.closePath();
        graphics.fillPath();
        
        // Body highlight / 身体高光
        graphics.fillStyle(0xffffff, 0.6);
        graphics.beginPath();
        graphics.moveTo(-2, -30);
        graphics.lineTo(6, -15);
        graphics.lineTo(6, 20);
        graphics.lineTo(-2, 20);
        graphics.closePath();
        graphics.fillPath();
        
        // Nose cone (red) / 火箭头部（红色）
        graphics.fillStyle(0xe74c3c, 1);
        graphics.beginPath();
        graphics.moveTo(0, -35);
        graphics.lineTo(10, -18);
        graphics.lineTo(-10, -18);
        graphics.closePath();
        graphics.fillPath();
        
        // Nose highlight / 头部高光
        graphics.fillStyle(0xec7063, 0.7);
        graphics.beginPath();
        graphics.moveTo(-2, -32);
        graphics.lineTo(4, -20);
        graphics.lineTo(-6, -20);
        graphics.closePath();
        graphics.fillPath();
        
        // Window / 舷窗
        graphics.fillStyle(0x74b9ff, 1);
        graphics.fillCircle(0, -5, 8);
        
        // Window inner / 舷窗内圈
        graphics.fillStyle(0x0984e3, 1);
        graphics.fillCircle(0, -5, 6);
        
        // Window highlight / 舷窗高光
        graphics.fillStyle(0xffffff, 0.7);
        graphics.fillCircle(-2, -7, 2);
        
        // Left fin / 左侧尾翼
        graphics.fillStyle(0xe74c3c, 1);
        graphics.beginPath();
        graphics.moveTo(-12, 15);
        graphics.lineTo(-25, 30);
        graphics.lineTo(-20, 30);
        graphics.lineTo(-12, 22);
        graphics.closePath();
        graphics.fillPath();
        
        // Right fin / 右侧尾翼
        graphics.beginPath();
        graphics.moveTo(12, 15);
        graphics.lineTo(25, 30);
        graphics.lineTo(20, 30);
        graphics.lineTo(12, 22);
        graphics.closePath();
        graphics.fillPath();
        
        // Center fin / 中间尾翼（底部）
        graphics.beginPath();
        graphics.moveTo(0, 25);
        graphics.lineTo(6, 35);
        graphics.lineTo(-6, 35);
        graphics.closePath();
        graphics.fillPath();
        
        // Decorative stripe / 装饰条纹
        graphics.fillStyle(0x3498db, 1);
        graphics.fillRect(-10, 8, 20, 4);
        
        // Engine flame container / 引擎火焰
        this.engineFlame = this.add.graphics();
        this.updateEngineFlame();
        this.centerIcon.add(this.engineFlame);
        
        this.centerIcon.add(graphics);
        
        // Floating animation / 浮动动画
        this.tweens.add({
            targets: this.centerIcon,
            y: this.centerIcon.y - 12,
            duration: 1000,
            yoyo: true,
            repeat: -1,
            ease: 'Sine.easeInOut'
        });

        // Slight wobble / 轻微摇摆
        this.tweens.add({
            targets: this.centerIcon,
            angle: { from: -3, to: 3 },
            duration: 1500,
            yoyo: true,
            repeat: -1,
            ease: 'Sine.easeInOut'
        });

        // Engine flame flicker / 引擎火焰闪烁
        this.time.addEvent({
            delay: 80,
            callback: this.updateEngineFlame,
            callbackScope: this,
            loop: true
        });
    }

    updateEngineFlame() {
        if (!this.engineFlame) return;
        
        this.engineFlame.clear();
        
        // Random flame parameters / 随机火焰参数
        const flameHeight = Phaser.Math.Between(20, 35);
        const flameWidth = Phaser.Math.Between(14, 20);
        const innerFlameHeight = flameHeight * 0.6;
        const innerFlameWidth = flameWidth * 0.5;
        
        // Outer flame (orange-red) / 外层火焰（橙红色）
        this.engineFlame.fillStyle(0xe67e22, 0.9);
        this.engineFlame.beginPath();
        this.engineFlame.moveTo(-flameWidth / 2, 25);
        this.engineFlame.lineTo(0, 25 + flameHeight);
        this.engineFlame.lineTo(flameWidth / 2, 25);
        this.engineFlame.closePath();
        this.engineFlame.fillPath();
        
        // Middle flame (orange) / 中层火焰（橙色）
        this.engineFlame.fillStyle(0xf39c12, 0.9);
        this.engineFlame.beginPath();
        this.engineFlame.moveTo(-flameWidth / 3, 25);
        this.engineFlame.lineTo(0, 25 + flameHeight * 0.8);
        this.engineFlame.lineTo(flameWidth / 3, 25);
        this.engineFlame.closePath();
        this.engineFlame.fillPath();
        
        // Inner flame (yellow) / 内层火焰（黄色）
        this.engineFlame.fillStyle(0xf1c40f, 1);
        this.engineFlame.beginPath();
        this.engineFlame.moveTo(-innerFlameWidth / 2, 25);
        this.engineFlame.lineTo(0, 25 + innerFlameHeight);
        this.engineFlame.lineTo(innerFlameWidth / 2, 25);
        this.engineFlame.closePath();
        this.engineFlame.fillPath();
        
        // Core flame (white-yellow) / 核心火焰（白黄色）
        this.engineFlame.fillStyle(0xffeaa7, 1);
        this.engineFlame.beginPath();
        this.engineFlame.moveTo(-3, 25);
        this.engineFlame.lineTo(0, 25 + innerFlameHeight * 0.5);
        this.engineFlame.lineTo(3, 25);
        this.engineFlame.closePath();
        this.engineFlame.fillPath();
    }

    createEnergyParticles(centerX, centerY) {
        this.particles = [];
        const particleCount = 6;
        
        for (let i = 0; i < particleCount; i++) {
            const angle = (i / particleCount) * Math.PI * 2;
            const particle = this.add.circle(
                centerX + Math.cos(angle) * 100,
                centerY + Math.sin(angle) * 100,
                4,
                [0xfd79a8, 0x74b9ff, 0x55efc4, 0xffeaa7, 0xa29bfe, 0xff7675][i],
                0.8
            );
            
            this.particles.push({
                graphic: particle,
                angle: angle,
                radius: 100,
                speed: 0.02 + Math.random() * 0.01
            });

            // Glow effect / 光晕效果
            this.tweens.add({
                targets: particle,
                scale: { from: 1, to: 1.5 },
                alpha: { from: 0.8, to: 0.4 },
                duration: 1000 + i * 200,
                yoyo: true,
                repeat: -1,
                ease: 'Sine.easeInOut'
            });
        }

        this.particleCenter = { x: centerX, y: centerY };
    }

    createLoadingText(centerX, centerY) {
        // Main title (bilingual) / 主标题（双语）
        this.loadingTitle = this.add.text(centerX, centerY + 80, '✨ AI Creating... / AI 创作中 ✨', {
            fontFamily: 'Arial, sans-serif',
            fontSize: '26px',
            fontStyle: 'bold',
            color: '#ffffff',
            stroke: '#6c5ce7',
            strokeThickness: 2
        }).setOrigin(0.5);

        // Title breathing animation / 标题呼吸动画
        this.tweens.add({
            targets: this.loadingTitle,
            scale: { from: 1, to: 1.05 },
            duration: 1500,
            yoyo: true,
            repeat: -1,
            ease: 'Sine.easeInOut'
        });

        // Subtitle with animated dots (bilingual) / 带动态省略号的副标题（双语）
        this.loadingDots = this.add.text(centerX, centerY + 120, 'Please wait / 请稍候', {
            fontFamily: 'Arial, sans-serif',
            fontSize: '18px',
            color: '#a29bfe'
        }).setOrigin(0.5);

        // Dynamic dots animation / 动态省略号
        this.dotsCount = 0;
        this.time.addEvent({
            delay: 500,
            callback: () => {
                this.dotsCount = (this.dotsCount + 1) % 4;
                const dots = '.'.repeat(this.dotsCount);
                this.loadingDots.setText(`Please wait / 请稍候${dots}`);
            },
            loop: true
        });

        // Progress bar background / 进度条背景
        const progressBg = this.add.graphics();
        progressBg.fillStyle(0x2d3436, 0.5);
        progressBg.fillRoundedRect(centerX - 150, centerY + 150, 300, 12, 6);

        // Progress bar foreground / 进度条前景
        this.progressBar = this.add.graphics();
        this.progressWidth = 0;
        this.progressTargetWidth = 296;

        // Progress bar animation / 进度条动画
        this.tweens.add({
            targets: this,
            progressWidth: this.progressTargetWidth,
            duration: 8000,
            ease: 'Sine.easeInOut',
            repeat: -1,
            yoyo: true,
            onUpdate: () => {
                this.progressBar.clear();
                
                // Gradient progress bar / 渐变进度条
                const gradient = this.progressBar;
                gradient.fillStyle(0x6c5ce7, 1);
                gradient.fillRoundedRect(centerX - 148, centerY + 152, this.progressWidth, 8, 4);
                
                // Highlight effect / 高光效果
                gradient.fillStyle(0xa29bfe, 0.5);
                gradient.fillRoundedRect(centerX - 148, centerY + 152, this.progressWidth, 4, 2);
            }
        });
    }

    createTips(centerX, height) {
        // Bilingual tips / 双语提示
        const tips = [
            '💡 AI is analyzing your creative needs...\n    AI 正在分析您的创意需求...',
            '🎨 Great games take a little patience...\n    优秀的游戏需要一点耐心来酝酿...',
            '🚀 Every pixel is being carefully crafted...\n    每一个像素都在被精心设计...',
            '⭐ Inspiration is flowing from the universe...\n    灵感正在从宇宙中涌来...',
            '🎮 Your unique game world is being built...\n    您的专属游戏世界正在构建中...',
            '✨ Magic takes time to cast...\n    魔法需要时间来施展...'
        ];

        this.currentTipIndex = 0;
        this.tipText = this.add.text(centerX, height - 70, tips[0], {
            fontFamily: 'Arial, sans-serif',
            fontSize: '14px',
            color: '#b2bec3',
            align: 'center'
        }).setOrigin(0.5);

        // Tip rotation animation / 提示切换动画
        this.time.addEvent({
            delay: 4000,
            callback: () => {
                this.currentTipIndex = (this.currentTipIndex + 1) % tips.length;
                
                // Fade out then fade in / 淡出淡入
                this.tweens.add({
                    targets: this.tipText,
                    alpha: 0,
                    duration: 300,
                    onComplete: () => {
                        this.tipText.setText(tips[this.currentTipIndex]);
                        this.tweens.add({
                            targets: this.tipText,
                            alpha: 1,
                            duration: 300
                        });
                    }
                });
            },
            loop: true
        });

        // Footer / 底部信息
        this.add.text(centerX, height - 20, 'Powered by Genie ✨', {
            fontFamily: 'Arial, sans-serif',
            fontSize: '12px',
            color: '#636e72'
        }).setOrigin(0.5);
    }

    createFloatingDecorations(width, height) {
        // Floating decorative elements / 浮动装饰元素
        const decorations = ['⭐', '✨', '💫', '🌟', '🚀', '🎮', '✈️', '🛸'];
        
        for (let i = 0; i < 12; i++) {
            const x = Phaser.Math.Between(50, width - 50);
            const y = Phaser.Math.Between(50, height - 100);
            const emoji = decorations[Phaser.Math.Between(0, decorations.length - 1)];
            
            const decoration = this.add.text(x, y, emoji, {
                fontSize: Phaser.Math.Between(16, 28) + 'px'
            }).setAlpha(Phaser.Math.FloatBetween(0.3, 0.6));

            // Floating animation / 浮动动画
            this.tweens.add({
                targets: decoration,
                y: y + Phaser.Math.Between(-30, 30),
                x: x + Phaser.Math.Between(-20, 20),
                alpha: { from: decoration.alpha, to: decoration.alpha * 0.5 },
                duration: Phaser.Math.Between(3000, 6000),
                yoyo: true,
                repeat: -1,
                ease: 'Sine.easeInOut'
            });

            // Rotation animation / 旋转动画
            this.tweens.add({
                targets: decoration,
                angle: { from: -15, to: 15 },
                duration: Phaser.Math.Between(2000, 4000),
                yoyo: true,
                repeat: -1,
                ease: 'Sine.easeInOut'
            });
        }
    }

    update() {
        // Update orbiting particles / 更新环绕粒子
        if (this.particles && this.particleCenter) {
            this.particles.forEach(p => {
                p.angle += p.speed;
                p.graphic.x = this.particleCenter.x + Math.cos(p.angle) * p.radius;
                p.graphic.y = this.particleCenter.y + Math.sin(p.angle) * p.radius;
            });
        }
    }
}
