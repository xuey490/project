<!-- 
模板ID: 3518
模板名称: 科技风-全息面板
适用场景: 科技风格目录页
设计特点: 纯黑底,SVG扫描线/数据流/HUD角标装饰,青蓝紫配色,等宽字体,两列紧凑布局
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #080810;">
    <!-- SVG科技装饰 -->
    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- HUD角标-左上 -->
        <path d="M30 30 L30 80 M30 30 L80 30" stroke="#06B6D4" stroke-width="1.5" opacity="0.4"/>
        <circle cx="30" cy="30" r="2" fill="#06B6D4" opacity="0.5"/>
        <!-- HUD角标-右上 -->
        <path d="M1410 30 L1410 80 M1410 30 L1360 30" stroke="#06B6D4" stroke-width="1.5" opacity="0.4"/>
        <circle cx="1410" cy="30" r="2" fill="#06B6D4" opacity="0.5"/>
        <!-- HUD角标-左下 -->
        <path d="M30 780 L30 730 M30 780 L80 780" stroke="#8B5CF6" stroke-width="1.5" opacity="0.35"/>
        <circle cx="30" cy="780" r="2" fill="#8B5CF6" opacity="0.45"/>
        <!-- HUD角标-右下 -->
        <path d="M1410 780 L1410 730 M1410 780 L1360 780" stroke="#8B5CF6" stroke-width="1.5" opacity="0.35"/>
        <circle cx="1410" cy="780" r="2" fill="#8B5CF6" opacity="0.45"/>

        <!-- 扫描线 -->
        <line x1="0" y1="200" x2="1440" y2="200" stroke="#06B6D4" stroke-width="0.3" opacity="0.12"/>
        <line x1="0" y1="400" x2="1440" y2="400" stroke="#06B6D4" stroke-width="0.3" opacity="0.08"/>
        <line x1="0" y1="600" x2="1440" y2="600" stroke="#8B5CF6" stroke-width="0.3" opacity="0.08"/>

        <!-- 数据流竖线-左 -->
        <line x1="120" y1="0" x2="120" y2="810" stroke="#06B6D4" stroke-width="0.3" opacity="0.1"/>
        <rect x="118" y="150" width="4" height="12" fill="#06B6D4" opacity="0.18"/>
        <rect x="118" y="300" width="4" height="8" fill="#06B6D4" opacity="0.14"/>
        <rect x="118" y="500" width="4" height="10" fill="#06B6D4" opacity="0.16"/>
        <rect x="118" y="680" width="4" height="6" fill="#06B6D4" opacity="0.12"/>

        <!-- 数据流竖线-右 -->
        <line x1="1320" y1="0" x2="1320" y2="810" stroke="#8B5CF6" stroke-width="0.3" opacity="0.1"/>
        <rect x="1318" y="120" width="4" height="10" fill="#8B5CF6" opacity="0.16"/>
        <rect x="1318" y="350" width="4" height="8" fill="#8B5CF6" opacity="0.12"/>
        <rect x="1318" y="550" width="4" height="12" fill="#8B5CF6" opacity="0.14"/>
        <rect x="1318" y="700" width="4" height="6" fill="#8B5CF6" opacity="0.1"/>

        <!-- 信号波形-底部 -->
        <path d="M200 760 L220 760 L225 748 L230 772 L235 752 L240 768 L245 756 L260 760 L400 760" stroke="#06B6D4" stroke-width="0.6" opacity="0.18"/>
        <path d="M1000 760 L1020 760 L1025 750 L1030 770 L1035 754 L1040 766 L1045 758 L1060 760 L1200 760" stroke="#8B5CF6" stroke-width="0.6" opacity="0.14"/>

        <!-- 散布像素点 -->
        <rect x="300" y="80" width="2" height="2" fill="#06B6D4" opacity="0.18"/>
        <rect x="500" y="50" width="2" height="2" fill="#3B82F6" opacity="0.14"/>
        <rect x="900" y="70" width="2" height="2" fill="#8B5CF6" opacity="0.14"/>
        <rect x="1150" y="90" width="2" height="2" fill="#06B6D4" opacity="0.12"/>
        <rect x="400" y="740" width="2" height="2" fill="#8B5CF6" opacity="0.12"/>
        <rect x="800" y="730" width="2" height="2" fill="#06B6D4" opacity="0.14"/>
        <rect x="1100" y="750" width="2" height="2" fill="#3B82F6" opacity="0.1"/>

        <!-- 圆环装饰 -->
        <circle cx="1380" cy="405" r="25" stroke="#8B5CF6" stroke-width="0.5" opacity="0.14"/>
        <circle cx="1380" cy="405" r="18" stroke="#8B5CF6" stroke-width="0.3" opacity="0.1"/>
        <circle cx="60" cy="405" r="20" stroke="#06B6D4" stroke-width="0.5" opacity="0.14"/>
        <circle cx="60" cy="405" r="13" stroke="#06B6D4" stroke-width="0.3" opacity="0.1"/>
    </svg>

    <!-- 内容区域 -->
    <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
        <!-- 标题区域 -->
        <div class="text-center mb-10">
            <div class="flex items-center justify-center gap-2 mb-3">
                <div class="w-2 h-2 rounded-full" style="background-color: #06B6D4; opacity: 0.6;"></div>
                <div class="w-2 h-2 rounded-full" style="background-color: #3B82F6; opacity: 0.5;"></div>
                <div class="w-2 h-2 rounded-full" style="background-color: #8B5CF6; opacity: 0.4;"></div>
            </div>
            <h1 class="text-4xl font-bold tracking-[6px]" style="color: #F1F5F9; font-family: 'Courier New', monospace;">TABLE OF CONTENTS</h1>
            <p class="text-xs mt-2 tracking-[4px]" style="color: rgba(6,182,212,0.6); font-family: 'Courier New', monospace;">// HOLO_PANEL v2.0</p>
            <div class="mt-4 flex items-center justify-center gap-1">
                <div class="w-10 h-px" style="background-color: #06B6D4; opacity: 0.5;"></div>
                <div class="w-1.5 h-1.5" style="background-color: #3B82F6; opacity: 0.6; transform: rotate(45deg);"></div>
                <div class="w-10 h-px" style="background-color: #8B5CF6; opacity: 0.5;"></div>
            </div>
        </div>

        <!-- 条目网格 -->
        <div class="grid grid-cols-2 gap-x-10 gap-y-4 w-full max-w-[1000px]">
            <!-- 条目1 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(6,182,212,0.12);">
                <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(6,182,212,0.25);">
                    <span class="text-sm font-bold" style="color: #22D3EE; font-family: 'Courier New', monospace;">01</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #F1F5F9;">系统初始化</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(34,211,238,0.7); font-family: 'Courier New', monospace;">System Init</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 16 16" class="flex-shrink-0">
                    <polygon points="8,1 15,8 8,15 1,8" fill="#06B6D4" opacity="0.4"/>
                </svg>
            </div>
            <!-- 条目2 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(59,130,246,0.12);">
                <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(59,130,246,0.25);">
                    <span class="text-sm font-bold" style="color: #60A5FA; font-family: 'Courier New', monospace;">02</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #F1F5F9;">核心逻辑</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(96,165,250,0.7); font-family: 'Courier New', monospace;">Core Logic</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 16 16" class="flex-shrink-0">
                    <polygon points="8,1 15,8 8,15 1,8" fill="#3B82F6" opacity="0.4"/>
                </svg>
            </div>
            <!-- 条目3 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(139,92,246,0.12);">
                <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(139,92,246,0.25);">
                    <span class="text-sm font-bold" style="color: #A78BFA; font-family: 'Courier New', monospace;">03</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #F1F5F9;">数据流转</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(167,139,250,0.7); font-family: 'Courier New', monospace;">Data Flow</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 16 16" class="flex-shrink-0">
                    <polygon points="8,1 15,8 8,15 1,8" fill="#8B5CF6" opacity="0.4"/>
                </svg>
            </div>
            <!-- 条目4 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(6,182,212,0.1);">
                <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(6,182,212,0.22);">
                    <span class="text-sm font-bold" style="color: #22D3EE; font-family: 'Courier New', monospace;">04</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #F1F5F9;">架构设计</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(34,211,238,0.65); font-family: 'Courier New', monospace;">Architecture</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 16 16" class="flex-shrink-0">
                    <polygon points="8,1 15,8 8,15 1,8" fill="#06B6D4" opacity="0.35"/>
                </svg>
            </div>
            <!-- 条目5 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(59,130,246,0.1);">
                <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(59,130,246,0.22);">
                    <span class="text-sm font-bold" style="color: #60A5FA; font-family: 'Courier New', monospace;">05</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #F1F5F9;">成果输出</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(96,165,250,0.65); font-family: 'Courier New', monospace;">Output</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 16 16" class="flex-shrink-0">
                    <polygon points="8,1 15,8 8,15 1,8" fill="#3B82F6" opacity="0.35"/>
                </svg>
            </div>
            <!-- 条目6 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(139,92,246,0.1);">
                <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(139,92,246,0.22);">
                    <span class="text-sm font-bold" style="color: #A78BFA; font-family: 'Courier New', monospace;">06</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #F1F5F9;">未来迭代</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(167,139,250,0.65); font-family: 'Courier New', monospace;">Next Iteration</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 16 16" class="flex-shrink-0">
                    <polygon points="8,1 15,8 8,15 1,8" fill="#8B5CF6" opacity="0.35"/>
                </svg>
            </div>
        </div>
    </div>
</div>
