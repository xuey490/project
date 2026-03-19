<!-- Template: 科技风-雷达HUD (TOC #3576) -->
<div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden" style="background: #060d1f;">
    <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 relative">
        <!-- SVG 雷达背景 -->
        <svg style="position: absolute; width: 500px; height: 500px; left: 50%; top: 50%; transform: translate(-50%, -50%); opacity: 0.12;" viewBox="0 0 500 500">
            <circle cx="250" cy="250" r="60" fill="none" stroke="#00d4ff" stroke-width="1"/>
            <circle cx="250" cy="250" r="120" fill="none" stroke="#00d4ff" stroke-width="1"/>
            <circle cx="250" cy="250" r="180" fill="none" stroke="#00d4ff" stroke-width="1"/>
            <circle cx="250" cy="250" r="240" fill="none" stroke="#00d4ff" stroke-width="0.5"/>
            <line x1="250" y1="10" x2="250" y2="490" stroke="#00d4ff" stroke-width="0.5"/>
            <line x1="10" y1="250" x2="490" y2="250" stroke="#00d4ff" stroke-width="0.5"/>
            <line x1="80" y1="80" x2="420" y2="420" stroke="#00d4ff" stroke-width="0.3"/>
            <line x1="420" y1="80" x2="80" y2="420" stroke="#00d4ff" stroke-width="0.3"/>
        </svg>

        <!-- HUD角落标记 -->
        <svg style="position: absolute; top: 0; left: 0; width: 80px; height: 80px;" viewBox="0 0 80 80">
            <path d="M0 30 L0 0 L30 0" fill="none" stroke="#00d4ff" stroke-width="2"/>
        </svg>
        <svg style="position: absolute; top: 0; right: 0; width: 80px; height: 80px;" viewBox="0 0 80 80">
            <path d="M50 0 L80 0 L80 30" fill="none" stroke="#00d4ff" stroke-width="2"/>
        </svg>
        <svg style="position: absolute; bottom: 0; left: 0; width: 80px; height: 80px;" viewBox="0 0 80 80">
            <path d="M0 50 L0 80 L30 80" fill="none" stroke="#00d4ff" stroke-width="2"/>
        </svg>
        <svg style="position: absolute; bottom: 0; right: 0; width: 80px; height: 80px;" viewBox="0 0 80 80">
            <path d="M50 80 L80 80 L80 50" fill="none" stroke="#00d4ff" stroke-width="2"/>
        </svg>

        <!-- 标题区域 -->
        <div style="text-align: center; padding-top: 40px; margin-bottom: 30px;">
            <div style="color: rgba(0, 212, 255, 0.4); font-size: 11px; font-family: 'Courier New', monospace; letter-spacing: 6px; margin-bottom: 8px;">TARGET ACQUISITION</div>
            <h1 style="font-size: 48px; font-weight: 800; color: #00d4ff; letter-spacing: 6px;">SCAN TARGETS</h1>
            <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 12px;">
                <div style="width: 50px; height: 2px; background: #00d4ff; opacity: 0.6;"></div>
                <div style="width: 6px; height: 6px; background: #00d4ff; transform: rotate(45deg);"></div>
                <div style="width: 50px; height: 2px; background: #00d4ff; opacity: 0.6;"></div>
            </div>
        </div>

        <!-- 条目列表 - 两列HUD风格 -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px 40px; padding: 0 60px;">

            <div style="display: flex; align-items: center; gap: 14px; padding: 16px 20px; background: rgba(0, 212, 255, 0.04);">
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <polygon points="20,2 38,20 20,38 2,20" fill="none" stroke="#00d4ff" stroke-width="1.5"/>
                    <text x="20" y="25" text-anchor="middle" fill="#00d4ff" font-size="16" font-weight="700" font-family="'Courier New', monospace">1</text>
                </svg>
                <div style="flex: 1;">
                    <h3 style="color: #00d4ff; font-size: 17px; font-weight: 700; margin-bottom: 2px;">项目背景</h3>
                    <p style="color: rgba(0, 212, 255, 0.45); font-size: 12px;">了解项目的起源与目标</p>
                </div>
                <div style="width: 6px; height: 6px; background: #00d4ff; border-radius: 50;"></div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; padding: 16px 20px; background: rgba(0, 212, 255, 0.04);">
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <polygon points="20,2 38,20 20,38 2,20" fill="none" stroke="#00d4ff" stroke-width="1.5"/>
                    <text x="20" y="25" text-anchor="middle" fill="#00d4ff" font-size="16" font-weight="700" font-family="'Courier New', monospace">2</text>
                </svg>
                <div style="flex: 1;">
                    <h3 style="color: #00d4ff; font-size: 17px; font-weight: 700; margin-bottom: 2px;">核心功能</h3>
                    <p style="color: rgba(0, 212, 255, 0.45); font-size: 12px;">产品的主要功能介绍</p>
                </div>
                <div style="width: 6px; height: 6px; background: #00d4ff; border-radius: 50;"></div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; padding: 16px 20px; background: rgba(0, 212, 255, 0.04);">
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <polygon points="20,2 38,20 20,38 2,20" fill="none" stroke="#00d4ff" stroke-width="1.5"/>
                    <text x="20" y="25" text-anchor="middle" fill="#00d4ff" font-size="16" font-weight="700" font-family="'Courier New', monospace">3</text>
                </svg>
                <div style="flex: 1;">
                    <h3 style="color: #00d4ff; font-size: 17px; font-weight: 700; margin-bottom: 2px;">技术架构</h3>
                    <p style="color: rgba(0, 212, 255, 0.45); font-size: 12px;">系统设计与技术选型</p>
                </div>
                <div style="width: 6px; height: 6px; background: #00d4ff; border-radius: 50;"></div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; padding: 16px 20px; background: rgba(0, 212, 255, 0.04);">
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <polygon points="20,2 38,20 20,38 2,20" fill="none" stroke="#00d4ff" stroke-width="1.5"/>
                    <text x="20" y="25" text-anchor="middle" fill="#00d4ff" font-size="16" font-weight="700" font-family="'Courier New', monospace">4</text>
                </svg>
                <div style="flex: 1;">
                    <h3 style="color: #00d4ff; font-size: 17px; font-weight: 700; margin-bottom: 2px;">实施计划</h3>
                    <p style="color: rgba(0, 212, 255, 0.45); font-size: 12px;">项目的推进与时间安排</p>
                </div>
                <div style="width: 6px; height: 6px; background: #00d4ff; border-radius: 50;"></div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; padding: 16px 20px; background: rgba(0, 212, 255, 0.04);">
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <polygon points="20,2 38,20 20,38 2,20" fill="none" stroke="#00d4ff" stroke-width="1.5"/>
                    <text x="20" y="25" text-anchor="middle" fill="#00d4ff" font-size="16" font-weight="700" font-family="'Courier New', monospace">5</text>
                </svg>
                <div style="flex: 1;">
                    <h3 style="color: #00d4ff; font-size: 17px; font-weight: 700; margin-bottom: 2px;">数据分析</h3>
                    <p style="color: rgba(0, 212, 255, 0.45); font-size: 12px;">关键数据与洞察分析</p>
                </div>
                <div style="width: 6px; height: 6px; background: #00d4ff; border-radius: 50;"></div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; padding: 16px 20px; background: rgba(0, 212, 255, 0.04);">
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <polygon points="20,2 38,20 20,38 2,20" fill="none" stroke="#00d4ff" stroke-width="1.5"/>
                    <text x="20" y="25" text-anchor="middle" fill="#00d4ff" font-size="16" font-weight="700" font-family="'Courier New', monospace">6</text>
                </svg>
                <div style="flex: 1;">
                    <h3 style="color: #00d4ff; font-size: 17px; font-weight: 700; margin-bottom: 2px;">总结展望</h3>
                    <p style="color: rgba(0, 212, 255, 0.45); font-size: 12px;">未来规划与发展方向</p>
                </div>
                <div style="width: 6px; height: 6px; background: #00d4ff; border-radius: 50;"></div>
            </div>

        </div>

        <!-- 底部状态条 -->
        <div style="position: absolute; bottom: 20px; left: 60px; right: 60px; display: flex; justify-content: space-between; font-family: 'Courier New', monospace; font-size: 10px; color: rgba(0, 212, 255, 0.3);">
            <span>FREQ: 2.4GHz</span>
            <span>RANGE: 360°</span>
            <span>SIGNAL: STRONG</span>
        </div>
    </div>
</div>