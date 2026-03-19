<!-- 
模板ID: 3510
模板名称: 几何风-同心圆环
适用场景: 几何风格目录页
设计特点: 深蓝藏青底,SVG同心圆环/六边形/菱形装饰,六边形编号,白色文字,两列紧凑布局
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #1A1A2E;">
    <!-- SVG几何装饰 -->
    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- 大同心圆-左侧 -->
        <circle cx="200" cy="405" r="280" stroke="#E94560" stroke-width="0.8" opacity="0.08"/>
        <circle cx="200" cy="405" r="220" stroke="#E94560" stroke-width="0.6" opacity="0.06"/>
        <circle cx="200" cy="405" r="160" stroke="#E94560" stroke-width="0.5" opacity="0.05"/>
        <circle cx="200" cy="405" r="100" stroke="#E94560" stroke-width="0.4" opacity="0.04"/>

        <!-- 大同心圆-右下 -->
        <circle cx="1300" cy="650" r="240" stroke="#0F3460" stroke-width="0.8" opacity="0.12"/>
        <circle cx="1300" cy="650" r="180" stroke="#0F3460" stroke-width="0.6" opacity="0.1"/>
        <circle cx="1300" cy="650" r="120" stroke="#0F3460" stroke-width="0.5" opacity="0.08"/>

        <!-- 六边形网格-右上 -->
        <g opacity="0.06" stroke="#E94560" stroke-width="0.8" fill="none">
            <polygon points="1300,80 1340,57 1380,80 1380,126 1340,149 1300,126"/>
            <polygon points="1380,80 1420,57 1440,70 1440,126 1420,149 1380,126"/>
            <polygon points="1340,149 1380,126 1420,149 1420,195 1380,218 1340,195"/>
        </g>

        <!-- 六边形网格-左下 -->
        <g opacity="0.05" stroke="#16C79A" stroke-width="0.8" fill="none">
            <polygon points="40,620 80,597 120,620 120,666 80,689 40,666"/>
            <polygon points="120,620 160,597 200,620 200,666 160,689 120,666"/>
            <polygon points="80,689 120,666 160,689 160,735 120,758 80,735"/>
        </g>

        <!-- 散布菱形 -->
        <rect x="1100" y="100" width="16" height="16" fill="#E94560" opacity="0.08" transform="rotate(45 1108 108)"/>
        <rect x="350" y="700" width="12" height="12" fill="#16C79A" opacity="0.07" transform="rotate(45 356 706)"/>
        <rect x="1350" y="400" width="10" height="10" fill="#0F3460" opacity="0.1" transform="rotate(45 1355 405)"/>

        <!-- 散布小圆点 -->
        <circle cx="500" cy="100" r="3" fill="#E94560" opacity="0.1"/>
        <circle cx="900" cy="60" r="2.5" fill="#16C79A" opacity="0.08"/>
        <circle cx="650" cy="750" r="3" fill="#E94560" opacity="0.06"/>
        <circle cx="1050" cy="750" r="2.5" fill="#16C79A" opacity="0.06"/>
        <circle cx="1200" cy="200" r="2" fill="#0F3460" opacity="0.15"/>

        <!-- 横虚线 -->
        <line x1="450" y1="770" x2="990" y2="770" stroke="#E94560" stroke-width="0.6" stroke-dasharray="4 4" opacity="0.08"/>

        <!-- 小三角 -->
        <polygon points="800,50 812,75 788,75" fill="#16C79A" opacity="0.06"/>
        <polygon points="1400,500 1410,520 1390,520" fill="#E94560" opacity="0.06"/>
    </svg>

    <!-- 内容区域 -->
    <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
        <!-- 标题区域 -->
        <div class="text-center mb-10">
            <div class="flex items-center justify-center gap-2 mb-3">
                <svg width="12" height="12" viewBox="0 0 12 12">
                    <polygon points="6,0 12,3 12,9 6,12 0,9 0,3" fill="#E94560" opacity="0.5"/>
                </svg>
                <svg width="10" height="10" viewBox="0 0 10 10">
                    <polygon points="5,0 10,2.5 10,7.5 5,10 0,7.5 0,2.5" fill="#16C79A" opacity="0.4"/>
                </svg>
                <svg width="8" height="8" viewBox="0 0 8 8">
                    <polygon points="4,0 8,2 8,6 4,8 0,6 0,2" fill="#E94560" opacity="0.3"/>
                </svg>
            </div>
            <h1 class="text-4xl font-black tracking-[6px]" style="color: #EAEAEA;">目录</h1>
            <p class="text-xs font-bold tracking-[8px] mt-2 uppercase" style="color: rgba(234,234,234,0.3);">TABLE OF CONTENTS</p>
            <div class="mt-4 flex items-center justify-center gap-1.5">
                <div class="w-8 h-0.5 rounded-full" style="background-color: #E94560; opacity: 0.5;"></div>
                <div class="w-3 h-0.5 rounded-full" style="background-color: #16C79A; opacity: 0.4;"></div>
                <div class="w-1.5 h-0.5 rounded-full" style="background-color: #E94560; opacity: 0.3;"></div>
            </div>
        </div>

        <!-- 条目网格 -->
        <div class="grid grid-cols-2 gap-x-10 gap-y-4 w-full max-w-[1000px]">
            <!-- 条目1 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(233,69,96,0.08);">
                <div class="flex-shrink-0 relative w-11 h-11 flex items-center justify-center">
                    <svg class="absolute inset-0" width="44" height="44" viewBox="0 0 44 44">
                        <polygon points="22,2 42,13 42,31 22,42 2,31 2,13" fill="#E94560"/>
                    </svg>
                    <span class="relative text-white text-xs font-black">01</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #EAEAEA;">研究背景</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(234,234,234,0.4);">Research Background</p>
                </div>
            </div>
            <!-- 条目2 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(22,199,154,0.08);">
                <div class="flex-shrink-0 relative w-11 h-11 flex items-center justify-center">
                    <svg class="absolute inset-0" width="44" height="44" viewBox="0 0 44 44">
                        <polygon points="22,2 42,13 42,31 22,42 2,31 2,13" fill="#16C79A"/>
                    </svg>
                    <span class="relative text-white text-xs font-black">02</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #EAEAEA;">方法论</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(234,234,234,0.4);">Methodology</p>
                </div>
            </div>
            <!-- 条目3 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(233,69,96,0.06);">
                <div class="flex-shrink-0 relative w-11 h-11 flex items-center justify-center">
                    <svg class="absolute inset-0" width="44" height="44" viewBox="0 0 44 44">
                        <polygon points="22,2 42,13 42,31 22,42 2,31 2,13" fill="#E94560" opacity="0.8"/>
                    </svg>
                    <span class="relative text-white text-xs font-black">03</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #EAEAEA;">数据分析</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(234,234,234,0.4);">Data Analysis</p>
                </div>
            </div>
            <!-- 条目4 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(22,199,154,0.06);">
                <div class="flex-shrink-0 relative w-11 h-11 flex items-center justify-center">
                    <svg class="absolute inset-0" width="44" height="44" viewBox="0 0 44 44">
                        <polygon points="22,2 42,13 42,31 22,42 2,31 2,13" fill="#16C79A" opacity="0.8"/>
                    </svg>
                    <span class="relative text-white text-xs font-black">04</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #EAEAEA;">实施方案</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(234,234,234,0.4);">Implementation</p>
                </div>
            </div>
            <!-- 条目5 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(233,69,96,0.04);">
                <div class="flex-shrink-0 relative w-11 h-11 flex items-center justify-center">
                    <svg class="absolute inset-0" width="44" height="44" viewBox="0 0 44 44">
                        <polygon points="22,2 42,13 42,31 22,42 2,31 2,13" fill="#E94560" opacity="0.6"/>
                    </svg>
                    <span class="relative text-white text-xs font-black">05</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #EAEAEA;">成果展示</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(234,234,234,0.4);">Results Display</p>
                </div>
            </div>
            <!-- 条目6 -->
            <div class="flex items-center gap-4 py-3.5 px-5 rounded-xl" style="background-color: rgba(22,199,154,0.04);">
                <div class="flex-shrink-0 relative w-11 h-11 flex items-center justify-center">
                    <svg class="absolute inset-0" width="44" height="44" viewBox="0 0 44 44">
                        <polygon points="22,2 42,13 42,31 22,42 2,31 2,13" fill="#16C79A" opacity="0.6"/>
                    </svg>
                    <span class="relative text-white text-xs font-black">06</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold truncate" style="color: #EAEAEA;">研究结论</h3>
                    <p class="text-xs mt-0.5 truncate" style="color: rgba(234,234,234,0.4);">Conclusions</p>
                </div>
            </div>
        </div>
    </div>
</div>
