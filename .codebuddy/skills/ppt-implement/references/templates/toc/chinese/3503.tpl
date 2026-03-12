<!-- 
模板ID: 3503
模板名称: 中国风-朱砂印章
适用场景: 中国风目录页
设计特点: 宣纸底色,SVG印章/祥云/梅花装饰,朱砂红配色,大写汉字编号,楷体衬线
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #F8F3EC;">
    <!-- SVG中国风装饰 -->
    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- 祥云-左上 -->
        <g opacity="0.08" fill="#8B2500">
            <circle cx="60" cy="60" r="25"/>
            <circle cx="90" cy="55" r="20"/>
            <circle cx="45" cy="50" r="15"/>
            <circle cx="75" cy="40" r="18"/>
            <circle cx="105" cy="48" r="14"/>
        </g>
        <!-- 祥云-右下 -->
        <g opacity="0.06" fill="#8B2500">
            <circle cx="1380" cy="750" r="22"/>
            <circle cx="1350" cy="755" r="18"/>
            <circle cx="1395" cy="760" r="14"/>
            <circle cx="1365" cy="765" r="16"/>
            <circle cx="1340" cy="740" r="12"/>
        </g>

        <!-- 梅花枝-右上角 -->
        <g opacity="0.1">
            <path d="M1440 0 Q1380 80 1350 150 Q1340 180 1360 200" stroke="#8B2500" stroke-width="2" fill="none"/>
            <path d="M1380 80 Q1360 70 1340 90" stroke="#8B2500" stroke-width="1.2" fill="none"/>
            <path d="M1360 130 Q1340 120 1320 140" stroke="#8B2500" stroke-width="1" fill="none"/>
            <!-- 梅花朵 -->
            <circle cx="1340" cy="88" r="4" fill="#C0392B" opacity="0.6"/>
            <circle cx="1335" cy="82" r="3.5" fill="#C0392B" opacity="0.5"/>
            <circle cx="1346" cy="84" r="3.5" fill="#C0392B" opacity="0.5"/>
            <circle cx="1338" cy="94" r="3" fill="#C0392B" opacity="0.4"/>
            <circle cx="1340" cy="88" r="1.5" fill="#FFD700" opacity="0.4"/>
            <circle cx="1318" cy="138" r="3.5" fill="#C0392B" opacity="0.5"/>
            <circle cx="1313" cy="133" r="3" fill="#C0392B" opacity="0.4"/>
            <circle cx="1324" cy="134" r="3" fill="#C0392B" opacity="0.4"/>
            <circle cx="1318" cy="138" r="1.5" fill="#FFD700" opacity="0.3"/>
        </g>

        <!-- 梅花枝-左下角 -->
        <g opacity="0.08">
            <path d="M0 810 Q60 740 100 680 Q110 660 90 640" stroke="#8B2500" stroke-width="1.8" fill="none"/>
            <path d="M60 740 Q80 730 70 710" stroke="#8B2500" stroke-width="1" fill="none"/>
            <circle cx="72" cy="708" r="3.5" fill="#C0392B" opacity="0.5"/>
            <circle cx="67" cy="703" r="3" fill="#C0392B" opacity="0.4"/>
            <circle cx="78" cy="704" r="3" fill="#C0392B" opacity="0.4"/>
            <circle cx="72" cy="708" r="1.5" fill="#FFD700" opacity="0.3"/>
        </g>

        <!-- 竖线分隔 -->
        <line x1="400" y1="120" x2="400" y2="690" stroke="#8B2500" stroke-width="0.8" opacity="0.1"/>

        <!-- 散落小圆点 -->
        <circle cx="300" cy="200" r="1.5" fill="#8B2500" opacity="0.06"/>
        <circle cx="1200" cy="300" r="1.5" fill="#8B2500" opacity="0.05"/>
        <circle cx="500" cy="700" r="1" fill="#8B2500" opacity="0.05"/>
        <circle cx="1100" cy="600" r="1.5" fill="#8B2500" opacity="0.04"/>
    </svg>

    <!-- 内容区域 -->
    <div class="absolute inset-0 flex z-10">
        <!-- 左侧标题区 -->
        <div class="w-[400px] flex flex-col items-center justify-center">
            <!-- 印章装饰 -->
            <svg width="80" height="80" viewBox="0 0 80 80" class="mb-5">
                <rect x="4" y="4" width="72" height="72" rx="4" fill="none" stroke="#C0392B" stroke-width="3" opacity="0.7"/>
                <rect x="10" y="10" width="60" height="60" rx="2" fill="none" stroke="#C0392B" stroke-width="1" opacity="0.3"/>
                <text x="40" y="50" text-anchor="middle" fill="#C0392B" font-size="30" font-weight="bold" opacity="0.75" style="font-family: 'KaiTi', 'STKaiti', serif;">目录</text>
            </svg>
            <p class="text-xs tracking-[6px] mt-2" style="color: rgba(139,37,0,0.3); font-family: 'KaiTi', 'STKaiti', serif;">TABLE OF CONTENTS</p>
            <!-- 装饰横线 -->
            <div class="mt-5 flex items-center gap-2">
                <div class="w-6 h-px" style="background-color: rgba(139,37,0,0.15);"></div>
                <svg width="8" height="8" viewBox="0 0 8 8"><rect x="1.5" y="1.5" width="5" height="5" fill="#C0392B" opacity="0.2" transform="rotate(45 4 4)"/></svg>
                <div class="w-6 h-px" style="background-color: rgba(139,37,0,0.15);"></div>
            </div>
        </div>

        <!-- 右侧条目区 -->
        <div class="flex-1 flex items-center justify-center pr-16">
            <div class="grid grid-cols-2 gap-x-10 gap-y-4 w-full max-w-[900px]">
                <!-- 条目1 -->
                <div class="flex items-center gap-4 py-3 px-4 rounded-lg" style="background-color: rgba(139,37,0,0.03);">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded" style="background-color: #C0392B;">
                        <span class="text-white text-base font-bold" style="font-family: 'KaiTi', 'STKaiti', serif;">壹</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #3D2B1F; font-family: 'KaiTi', 'STKaiti', serif;">项目缘起</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #B5A28A;">Project Origin</p>
                    </div>
                </div>
                <!-- 条目2 -->
                <div class="flex items-center gap-4 py-3 px-4 rounded-lg" style="background-color: rgba(139,37,0,0.03);">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded" style="background-color: #C0392B;">
                        <span class="text-white text-base font-bold" style="font-family: 'KaiTi', 'STKaiti', serif;">贰</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #3D2B1F; font-family: 'KaiTi', 'STKaiti', serif;">方案详解</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #B5A28A;">Detailed Plan</p>
                    </div>
                </div>
                <!-- 条目3 -->
                <div class="flex items-center gap-4 py-3 px-4 rounded-lg" style="background-color: rgba(139,37,0,0.03);">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded" style="background-color: #C0392B;">
                        <span class="text-white text-base font-bold" style="font-family: 'KaiTi', 'STKaiti', serif;">叁</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #3D2B1F; font-family: 'KaiTi', 'STKaiti', serif;">实施步骤</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #B5A28A;">Implementation Steps</p>
                    </div>
                </div>
                <!-- 条目4 -->
                <div class="flex items-center gap-4 py-3 px-4 rounded-lg" style="background-color: rgba(139,37,0,0.03);">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded" style="background-color: #C0392B;">
                        <span class="text-white text-base font-bold" style="font-family: 'KaiTi', 'STKaiti', serif;">肆</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #3D2B1F; font-family: 'KaiTi', 'STKaiti', serif;">技术要点</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #B5A28A;">Technical Points</p>
                    </div>
                </div>
                <!-- 条目5 -->
                <div class="flex items-center gap-4 py-3 px-4 rounded-lg" style="background-color: rgba(139,37,0,0.03);">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded" style="background-color: #C0392B;">
                        <span class="text-white text-base font-bold" style="font-family: 'KaiTi', 'STKaiti', serif;">伍</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #3D2B1F; font-family: 'KaiTi', 'STKaiti', serif;">成果展示</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #B5A28A;">Results Display</p>
                    </div>
                </div>
                <!-- 条目6 -->
                <div class="flex items-center gap-4 py-3 px-4 rounded-lg" style="background-color: rgba(139,37,0,0.03);">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded" style="background-color: #C0392B;">
                        <span class="text-white text-base font-bold" style="font-family: 'KaiTi', 'STKaiti', serif;">陆</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #3D2B1F; font-family: 'KaiTi', 'STKaiti', serif;">展望未来</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: #B5A28A;">Future Outlook</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
