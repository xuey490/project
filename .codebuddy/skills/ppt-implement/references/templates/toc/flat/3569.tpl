<!-- 
模板ID: 3569
模板名称: 扁平风-色块拼接
适用场景: 扁平风格目录页
设计特点: 纯白底,多彩扁平色块装饰,SVG几何图形,色块编号,两列紧凑布局
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #FFFFFF;">
    <!-- SVG扁平几何装饰 -->
    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- 左上大色块 -->
        <rect x="0" y="0" width="380" height="810" fill="#F0F4F8"/>
        <!-- 左侧竖色条 -->
        <rect x="380" y="0" width="6" height="810" fill="#4A90D9" opacity="0.15"/>

        <!-- 右上角装饰三角 -->
        <polygon points="1440,0 1440,160 1280,0" fill="#FF6B6B" opacity="0.08"/>
        <!-- 右下角装饰圆 -->
        <circle cx="1400" cy="770" r="80" fill="#4ECDC4" opacity="0.06"/>
        <!-- 左下角装饰半圆 -->
        <path d="M0 810 A120 120 0 0 1 120 810 Z" fill="#FFD93D" opacity="0.08"/>

        <!-- 散布几何形状 -->
        <!-- 小十字 -->
        <g transform="translate(1350, 250)" opacity="0.12">
            <rect x="-1.5" y="-8" width="3" height="16" rx="1.5" fill="#4A90D9"/>
            <rect x="-8" y="-1.5" width="16" height="3" rx="1.5" fill="#4A90D9"/>
        </g>
        <g transform="translate(480, 60)" opacity="0.1">
            <rect x="-1" y="-6" width="2" height="12" rx="1" fill="#FF6B6B"/>
            <rect x="-6" y="-1" width="12" height="2" rx="1" fill="#FF6B6B"/>
        </g>

        <!-- 小圆点 -->
        <circle cx="1300" cy="400" r="4" fill="#FFD93D" opacity="0.15"/>
        <circle cx="1250" cy="150" r="3" fill="#4ECDC4" opacity="0.12"/>
        <circle cx="500" cy="750" r="3.5" fill="#FF6B6B" opacity="0.1"/>
        <circle cx="1100" cy="700" r="3" fill="#4A90D9" opacity="0.1"/>

        <!-- 小方块 -->
        <rect x="1380" cy="500" y="500" width="8" height="8" fill="#FF6B6B" opacity="0.1" transform="rotate(45 1384 504)"/>
        <rect x="600" y="30" width="6" height="6" fill="#4ECDC4" opacity="0.1" transform="rotate(45 603 33)"/>

        <!-- 虚线横条 -->
        <line x1="500" y1="780" x2="1350" y2="780" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="6 4"/>
    </svg>

    <!-- 内容区域 -->
    <div class="absolute inset-0 flex z-10">
        <!-- 左侧标题区 -->
        <div class="w-[380px] flex flex-col items-center justify-center px-12">
            <!-- 装饰图标 -->
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="mb-5">
                <rect x="4" y="4" width="16" height="16" rx="3" fill="#4A90D9"/>
                <rect x="24" y="4" width="16" height="16" rx="3" fill="#4ECDC4" opacity="0.6"/>
                <rect x="4" y="24" width="16" height="16" rx="3" fill="#FFD93D" opacity="0.6"/>
                <rect x="24" y="24" width="16" height="16" rx="3" fill="#FF6B6B" opacity="0.4"/>
            </svg>
            <h1 class="text-5xl font-black tracking-wider" style="color: #2D3748;">目录</h1>
            <p class="mt-2 text-xs font-bold tracking-[6px] uppercase" style="color: #A0AEC0;">CONTENTS</p>
            <div class="mt-5 flex items-center gap-1.5">
                <div class="w-8 h-1 rounded-full" style="background-color: #4A90D9;"></div>
                <div class="w-3 h-1 rounded-full" style="background-color: #4ECDC4;"></div>
                <div class="w-1.5 h-1 rounded-full" style="background-color: #FFD93D;"></div>
            </div>
            <!-- 大装饰数字 -->
            <div class="mt-10 text-8xl font-black leading-none" style="color: rgba(74,144,217,0.06);">TOC</div>
        </div>

        <!-- 右侧条目区 -->
        <div class="flex-1 flex items-center justify-center pl-10 pr-16">
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 w-full max-w-[900px]">
                <!-- 条目1 -->
                <div class="flex items-center gap-4 py-3.5 px-4 rounded-xl" style="background-color: #F7FAFC;">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #4A90D9;">
                        <span class="text-white text-sm font-black">01</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #2D3748;">项目背景</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #A0AEC0;">Project Background</p>
                    </div>
                </div>
                <!-- 条目2 -->
                <div class="flex items-center gap-4 py-3.5 px-4 rounded-xl" style="background-color: #F7FAFC;">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #FF6B6B;">
                        <span class="text-white text-sm font-black">02</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #2D3748;">核心功能</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #A0AEC0;">Core Features</p>
                    </div>
                </div>
                <!-- 条目3 -->
                <div class="flex items-center gap-4 py-3.5 px-4 rounded-xl" style="background-color: #F7FAFC;">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #4ECDC4;">
                        <span class="text-white text-sm font-black">03</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #2D3748;">技术架构</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #A0AEC0;">Technical Architecture</p>
                    </div>
                </div>
                <!-- 条目4 -->
                <div class="flex items-center gap-4 py-3.5 px-4 rounded-xl" style="background-color: #F7FAFC;">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #FFD93D;">
                        <span class="text-white text-sm font-black">04</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #2D3748;">实施计划</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #A0AEC0;">Implementation Plan</p>
                    </div>
                </div>
                <!-- 条目5 -->
                <div class="flex items-center gap-4 py-3.5 px-4 rounded-xl" style="background-color: #F7FAFC;">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #9B7DD4;">
                        <span class="text-white text-sm font-black">05</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #2D3748;">成果展示</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #A0AEC0;">Results Display</p>
                    </div>
                </div>
                <!-- 条目6 -->
                <div class="flex items-center gap-4 py-3.5 px-4 rounded-xl" style="background-color: #F7FAFC;">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #F093A0;">
                        <span class="text-white text-sm font-black">06</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #2D3748;">未来展望</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #A0AEC0;">Future Outlook</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
