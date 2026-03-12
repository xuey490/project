<!-- 
模板ID: 3504
模板名称: 中国风-山水意境
适用场景: 中国风目录页
设计特点: 淡墨底色,SVG远山近山层叠,月亮飞鸟,竹叶装饰,水墨灰配色,楷体衬线
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #F5F1EA;">
    <!-- SVG山水装饰 -->
    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- 远山层 -->
        <path d="M0 650 Q200 520 400 580 Q550 480 720 550 Q900 460 1080 530 Q1250 470 1440 560 L1440 810 L0 810 Z" fill="#D5CBBD" opacity="0.15"/>
        <!-- 中山层 -->
        <path d="M0 700 Q180 600 350 660 Q500 580 680 640 Q850 560 1050 620 Q1200 570 1440 640 L1440 810 L0 810 Z" fill="#C4B8A6" opacity="0.12"/>
        <!-- 近山层 -->
        <path d="M0 750 Q160 680 320 720 Q480 670 650 710 Q820 660 1000 700 Q1180 660 1440 720 L1440 810 L0 810 Z" fill="#B5A895" opacity="0.1"/>

        <!-- 月亮-右上 -->
        <circle cx="1250" cy="120" r="45" fill="#E8DFD0" opacity="0.3"/>
        <circle cx="1265" cy="110" r="42" fill="#F5F1EA"/>

        <!-- 飞鸟 -->
        <g opacity="0.12" stroke="#5C5040" stroke-width="1.2" stroke-linecap="round" fill="none">
            <path d="M1100 180 Q1108 172 1116 178 Q1124 172 1132 180"/>
            <path d="M1070 200 Q1076 194 1082 198 Q1088 194 1094 200"/>
            <path d="M1130 210 Q1135 205 1140 209 Q1145 205 1150 210"/>
        </g>

        <!-- 竹叶-左侧 -->
        <g opacity="0.08" fill="#5C5040">
            <!-- 竹竿 -->
            <rect x="55" y="200" width="2" height="400" fill="#5C5040" opacity="0.15"/>
            <rect x="85" y="250" width="1.5" height="350" fill="#5C5040" opacity="0.1"/>
            <!-- 竹叶 -->
            <ellipse cx="40" cy="260" rx="18" ry="4" transform="rotate(-35 40 260)"/>
            <ellipse cx="68" cy="240" rx="16" ry="3.5" transform="rotate(25 68 240)"/>
            <ellipse cx="35" cy="310" rx="15" ry="3.5" transform="rotate(-40 35 310)"/>
            <ellipse cx="75" cy="300" rx="14" ry="3" transform="rotate(30 75 300)"/>
            <ellipse cx="45" cy="370" rx="16" ry="3.5" transform="rotate(-30 45 370)"/>
            <ellipse cx="95" cy="290" rx="13" ry="3" transform="rotate(-25 95 290)"/>
            <ellipse cx="70" cy="350" rx="15" ry="3" transform="rotate(35 70 350)"/>
        </g>

        <!-- 竹叶-右侧 -->
        <g opacity="0.06" fill="#5C5040">
            <rect x="1395" y="300" width="1.5" height="300" fill="#5C5040" opacity="0.12"/>
            <ellipse cx="1410" cy="340" rx="15" ry="3.5" transform="rotate(35 1410 340)"/>
            <ellipse cx="1382" cy="360" rx="14" ry="3" transform="rotate(-30 1382 360)"/>
            <ellipse cx="1405" cy="400" rx="13" ry="3" transform="rotate(25 1405 400)"/>
            <ellipse cx="1385" cy="430" rx="14" ry="3" transform="rotate(-35 1385 430)"/>
        </g>

        <!-- 水纹-底部 -->
        <path d="M200 790 Q240 782 280 790 Q320 798 360 790 Q400 782 440 790" stroke="#B5A895" stroke-width="0.8" fill="none" opacity="0.12"/>
        <path d="M500 795 Q540 787 580 795 Q620 803 660 795 Q700 787 740 795" stroke="#B5A895" stroke-width="0.8" fill="none" opacity="0.1"/>
        <path d="M800 790 Q840 782 880 790 Q920 798 960 790 Q1000 782 1040 790" stroke="#B5A895" stroke-width="0.8" fill="none" opacity="0.08"/>

        <!-- 散落墨点 -->
        <circle cx="200" cy="150" r="1.5" fill="#5C5040" opacity="0.05"/>
        <circle cx="1300" cy="400" r="1" fill="#5C5040" opacity="0.04"/>
        <circle cx="350" cy="500" r="1.5" fill="#5C5040" opacity="0.04"/>
    </svg>

    <!-- 内容区域 -->
    <div class="absolute inset-0 flex flex-col items-center justify-center z-10">
        <!-- 标题区域 -->
        <div class="text-center mb-10">
            <div class="flex items-center justify-center gap-4 mb-3">
                <svg width="50" height="2" viewBox="0 0 50 2"><line x1="0" y1="1" x2="50" y2="1" stroke="#8B7D6B" stroke-width="0.6" opacity="0.25"/></svg>
                <svg width="10" height="10" viewBox="0 0 10 10"><rect x="2" y="2" width="6" height="6" fill="none" stroke="#8B7D6B" stroke-width="0.6" opacity="0.25" transform="rotate(45 5 5)"/></svg>
                <svg width="50" height="2" viewBox="0 0 50 2"><line x1="0" y1="1" x2="50" y2="1" stroke="#8B7D6B" stroke-width="0.6" opacity="0.25"/></svg>
            </div>
            <h1 class="text-5xl tracking-[16px]" style="color: #4A3F35; font-family: 'KaiTi', 'STKaiti', serif; font-weight: 400;">目 录</h1>
            <p class="text-xs tracking-[8px] mt-3" style="color: #B5A895; font-family: 'KaiTi', 'STKaiti', serif;">CONTENTS</p>
        </div>

        <!-- 条目区域 - 两列布局 -->
        <div class="grid grid-cols-2 gap-x-16 gap-y-3 w-full max-w-[1000px] px-8">
            <!-- 条目1 -->
            <div class="flex items-center gap-4 py-3 px-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-sm flex items-center justify-center" style="background-color: rgba(92,80,64,0.08);">
                    <span class="text-sm" style="color: #7A6B58; font-family: 'KaiTi', 'STKaiti', serif; font-weight: bold;">一</span>
                </div>
                <div class="flex-1 min-w-0 flex items-baseline gap-2">
                    <h3 class="text-base truncate flex-shrink-0" style="color: #4A3F35; font-family: 'KaiTi', 'STKaiti', serif;">背景分析</h3>
                    <div class="flex-1 border-b border-dotted opacity-15" style="border-color: #8B7D6B; min-width: 20px;"></div>
                </div>
            </div>
            <!-- 条目2 -->
            <div class="flex items-center gap-4 py-3 px-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-sm flex items-center justify-center" style="background-color: rgba(92,80,64,0.08);">
                    <span class="text-sm" style="color: #7A6B58; font-family: 'KaiTi', 'STKaiti', serif; font-weight: bold;">二</span>
                </div>
                <div class="flex-1 min-w-0 flex items-baseline gap-2">
                    <h3 class="text-base truncate flex-shrink-0" style="color: #4A3F35; font-family: 'KaiTi', 'STKaiti', serif;">解决方案</h3>
                    <div class="flex-1 border-b border-dotted opacity-15" style="border-color: #8B7D6B; min-width: 20px;"></div>
                </div>
            </div>
            <!-- 条目3 -->
            <div class="flex items-center gap-4 py-3 px-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-sm flex items-center justify-center" style="background-color: rgba(92,80,64,0.08);">
                    <span class="text-sm" style="color: #7A6B58; font-family: 'KaiTi', 'STKaiti', serif; font-weight: bold;">三</span>
                </div>
                <div class="flex-1 min-w-0 flex items-baseline gap-2">
                    <h3 class="text-base truncate flex-shrink-0" style="color: #4A3F35; font-family: 'KaiTi', 'STKaiti', serif;">执行计划</h3>
                    <div class="flex-1 border-b border-dotted opacity-15" style="border-color: #8B7D6B; min-width: 20px;"></div>
                </div>
            </div>
            <!-- 条目4 -->
            <div class="flex items-center gap-4 py-3 px-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-sm flex items-center justify-center" style="background-color: rgba(92,80,64,0.08);">
                    <span class="text-sm" style="color: #7A6B58; font-family: 'KaiTi', 'STKaiti', serif; font-weight: bold;">四</span>
                </div>
                <div class="flex-1 min-w-0 flex items-baseline gap-2">
                    <h3 class="text-base truncate flex-shrink-0" style="color: #4A3F35; font-family: 'KaiTi', 'STKaiti', serif;">技术要点</h3>
                    <div class="flex-1 border-b border-dotted opacity-15" style="border-color: #8B7D6B; min-width: 20px;"></div>
                </div>
            </div>
            <!-- 条目5 -->
            <div class="flex items-center gap-4 py-3 px-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-sm flex items-center justify-center" style="background-color: rgba(92,80,64,0.08);">
                    <span class="text-sm" style="color: #7A6B58; font-family: 'KaiTi', 'STKaiti', serif; font-weight: bold;">五</span>
                </div>
                <div class="flex-1 min-w-0 flex items-baseline gap-2">
                    <h3 class="text-base truncate flex-shrink-0" style="color: #4A3F35; font-family: 'KaiTi', 'STKaiti', serif;">成果展示</h3>
                    <div class="flex-1 border-b border-dotted opacity-15" style="border-color: #8B7D6B; min-width: 20px;"></div>
                </div>
            </div>
            <!-- 条目6 -->
            <div class="flex items-center gap-4 py-3 px-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-sm flex items-center justify-center" style="background-color: rgba(92,80,64,0.08);">
                    <span class="text-sm" style="color: #7A6B58; font-family: 'KaiTi', 'STKaiti', serif; font-weight: bold;">六</span>
                </div>
                <div class="flex-1 min-w-0 flex items-baseline gap-2">
                    <h3 class="text-base truncate flex-shrink-0" style="color: #4A3F35; font-family: 'KaiTi', 'STKaiti', serif;">总结回顾</h3>
                    <div class="flex-1 border-b border-dotted opacity-15" style="border-color: #8B7D6B; min-width: 20px;"></div>
                </div>
            </div>
        </div>

        <!-- 底部点缀 -->
        <div class="mt-8 flex items-center gap-3">
            <svg width="40" height="2" viewBox="0 0 40 2"><line x1="0" y1="1" x2="40" y2="1" stroke="#B5A895" stroke-width="0.5" opacity="0.3"/></svg>
            <svg width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="2" fill="#B5A895" opacity="0.2"/></svg>
            <svg width="40" height="2" viewBox="0 0 40 2"><line x1="0" y1="1" x2="40" y2="1" stroke="#B5A895" stroke-width="0.5" opacity="0.3"/></svg>
        </div>
    </div>
</div>
