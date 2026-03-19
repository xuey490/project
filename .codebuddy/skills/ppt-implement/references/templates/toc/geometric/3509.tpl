<!-- 
模板ID: 3509
模板名称: 几何风-棱镜拼贴
适用场景: 几何风格目录页
设计特点: 纯白底,SVG大面积几何色块装饰,三角形编号,多彩纯色,两列紧凑布局
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #FAFAFA;">
    <!-- SVG几何装饰 -->
    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- 左上大三角 -->
        <polygon points="0,0 320,0 0,280" fill="#6C5CE7" opacity="0.08"/>
        <!-- 左上小三角叠层 -->
        <polygon points="0,0 200,0 0,170" fill="#6C5CE7" opacity="0.05"/>

        <!-- 右下大三角 -->
        <polygon points="1440,810 1120,810 1440,530" fill="#00CEC9" opacity="0.07"/>
        <!-- 右下小三角叠层 -->
        <polygon points="1440,810 1240,810 1440,630" fill="#00CEC9" opacity="0.04"/>

        <!-- 右上菱形 -->
        <rect x="1300" y="40" width="80" height="80" fill="#FDCB6E" opacity="0.1" transform="rotate(45 1340 80)"/>
        <rect x="1310" y="50" width="50" height="50" fill="#FDCB6E" opacity="0.06" transform="rotate(45 1335 75)"/>

        <!-- 左下圆 -->
        <circle cx="100" cy="720" r="60" fill="#FF7675" opacity="0.06"/>
        <circle cx="100" cy="720" r="35" fill="#FF7675" opacity="0.04"/>

        <!-- 中部装饰几何 -->
        <polygon points="720,0 760,0 740,35" fill="#6C5CE7" opacity="0.06"/>
        <rect x="680" y="790" width="20" height="20" fill="#00CEC9" opacity="0.06" transform="rotate(45 690 800)"/>

        <!-- 散布小三角 -->
        <polygon points="1350,300 1365,330 1335,330" fill="#FF7675" opacity="0.08"/>
        <polygon points="180,450 195,475 165,475" fill="#FDCB6E" opacity="0.07"/>
        <polygon points="1200,650 1212,675 1188,675" fill="#6C5CE7" opacity="0.06"/>

        <!-- 散布小圆 -->
        <circle cx="400" cy="100" r="4" fill="#00CEC9" opacity="0.1"/>
        <circle cx="1100" cy="200" r="3" fill="#FF7675" opacity="0.08"/>
        <circle cx="550" cy="700" r="3.5" fill="#6C5CE7" opacity="0.07"/>
        <circle cx="950" cy="80" r="3" fill="#FDCB6E" opacity="0.08"/>

        <!-- 小十字 -->
        <g transform="translate(1380, 450)" opacity="0.1">
            <rect x="-1.5" y="-7" width="3" height="14" rx="1.5" fill="#6C5CE7"/>
            <rect x="-7" y="-1.5" width="14" height="3" rx="1.5" fill="#6C5CE7"/>
        </g>
        <g transform="translate(60, 500)" opacity="0.08">
            <rect x="-1" y="-5" width="2" height="10" rx="1" fill="#00CEC9"/>
            <rect x="-5" y="-1" width="10" height="2" rx="1" fill="#00CEC9"/>
        </g>

        <!-- 虚线装饰 -->
        <line x1="440" y1="780" x2="1000" y2="780" stroke="#E0E0E0" stroke-width="1" stroke-dasharray="5 4"/>
    </svg>

    <!-- 内容区域 -->
    <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
        <!-- 标题区域 -->
        <div class="text-center mb-10">
            <div class="flex items-center justify-center gap-3 mb-3">
                <svg width="28" height="24" viewBox="0 0 28 24">
                    <polygon points="14,0 28,24 0,24" fill="#6C5CE7" opacity="0.6"/>
                </svg>
                <svg width="28" height="24" viewBox="0 0 28 24">
                    <polygon points="14,0 28,24 0,24" fill="#00CEC9" opacity="0.5"/>
                </svg>
                <svg width="28" height="24" viewBox="0 0 28 24">
                    <polygon points="14,0 28,24 0,24" fill="#FDCB6E" opacity="0.5"/>
                </svg>
            </div>
            <h1 class="text-4xl font-black tracking-widest" style="color: #2D3436;">目录</h1>
            <p class="text-xs font-bold tracking-[8px] mt-2 uppercase" style="color: #B2BEC3;">CONTENTS</p>
        </div>

        <!-- 条目网格 -->
        <div class="grid grid-cols-2 gap-x-10 gap-y-4 w-full max-w-[1000px]">
            <!-- 条目1 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(108,92,231,0.06);">
                <div class="flex-shrink-0 relative w-10 h-10 flex items-center justify-center">
                    <svg class="absolute inset-0" width="40" height="40" viewBox="0 0 40 40">
                        <polygon points="20,2 38,38 2,38" fill="#6C5CE7"/>
                    </svg>
                    <span class="relative text-white text-xs font-black mt-1.5">01</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #2D3436;">研究背景</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: #B2BEC3;">Research Background</p>
                </div>
            </div>
            <!-- 条目2 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(0,206,201,0.06);">
                <div class="flex-shrink-0 relative w-10 h-10 flex items-center justify-center">
                    <svg class="absolute inset-0" width="40" height="40" viewBox="0 0 40 40">
                        <polygon points="20,2 38,38 2,38" fill="#00CEC9"/>
                    </svg>
                    <span class="relative text-white text-xs font-black mt-1.5">02</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #2D3436;">方法论</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: #B2BEC3;">Methodology</p>
                </div>
            </div>
            <!-- 条目3 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(253,203,110,0.08);">
                <div class="flex-shrink-0 relative w-10 h-10 flex items-center justify-center">
                    <svg class="absolute inset-0" width="40" height="40" viewBox="0 0 40 40">
                        <polygon points="20,2 38,38 2,38" fill="#FDCB6E"/>
                    </svg>
                    <span class="relative text-white text-xs font-black mt-1.5">03</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #2D3436;">数据分析</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: #B2BEC3;">Data Analysis</p>
                </div>
            </div>
            <!-- 条目4 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(255,118,117,0.06);">
                <div class="flex-shrink-0 relative w-10 h-10 flex items-center justify-center">
                    <svg class="absolute inset-0" width="40" height="40" viewBox="0 0 40 40">
                        <polygon points="20,2 38,38 2,38" fill="#FF7675"/>
                    </svg>
                    <span class="relative text-white text-xs font-black mt-1.5">04</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #2D3436;">实施方案</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: #B2BEC3;">Implementation</p>
                </div>
            </div>
            <!-- 条目5 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(108,92,231,0.04);">
                <div class="flex-shrink-0 relative w-10 h-10 flex items-center justify-center">
                    <svg class="absolute inset-0" width="40" height="40" viewBox="0 0 40 40">
                        <polygon points="20,2 38,38 2,38" fill="#A29BFE"/>
                    </svg>
                    <span class="relative text-white text-xs font-black mt-1.5">05</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #2D3436;">成果展示</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: #B2BEC3;">Results Display</p>
                </div>
            </div>
            <!-- 条目6 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(0,206,201,0.04);">
                <div class="flex-shrink-0 relative w-10 h-10 flex items-center justify-center">
                    <svg class="absolute inset-0" width="40" height="40" viewBox="0 0 40 40">
                        <polygon points="20,2 38,38 2,38" fill="#55EFC4"/>
                    </svg>
                    <span class="relative text-white text-xs font-black mt-1.5">06</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #2D3436;">研究结论</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: #B2BEC3;">Conclusions</p>
                </div>
            </div>
        </div>
    </div>
</div>
