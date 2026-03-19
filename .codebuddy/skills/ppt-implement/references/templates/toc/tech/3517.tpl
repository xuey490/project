<!-- 
模板ID: 3517
模板名称: 科技风-芯片微电路
适用场景: 科技风格目录页
设计特点: 深墨绿底,SVG电路走线/焊点装饰,绿色调,等宽字体,两列紧凑布局
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0B1F14;">
    <!-- SVG电路装饰 -->
    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- 电路走线-左区 -->
        <path d="M0 200 L80 200 L80 300 L150 300" stroke="#22C55E" stroke-width="1" opacity="0.2"/>
        <path d="M0 400 L60 400 L60 350 L130 350" stroke="#22C55E" stroke-width="1" opacity="0.15"/>
        <path d="M0 550 L100 550 L100 480 L170 480" stroke="#22C55E" stroke-width="1" opacity="0.18"/>
        <circle cx="150" cy="300" r="3" fill="#22C55E" opacity="0.3"/>
        <circle cx="130" cy="350" r="2.5" fill="#22C55E" opacity="0.25"/>
        <circle cx="170" cy="480" r="3" fill="#22C55E" opacity="0.25"/>
        <circle cx="80" cy="200" r="2" fill="#22C55E" opacity="0.2"/>
        <circle cx="60" cy="400" r="2" fill="#22C55E" opacity="0.15"/>

        <!-- 电路走线-右区 -->
        <path d="M1440 150 L1350 150 L1350 250 L1280 250" stroke="#22C55E" stroke-width="1" opacity="0.18"/>
        <path d="M1440 380 L1370 380 L1370 320 L1300 320" stroke="#22C55E" stroke-width="1" opacity="0.15"/>
        <path d="M1440 600 L1360 600 L1360 530 L1290 530" stroke="#22C55E" stroke-width="1" opacity="0.12"/>
        <circle cx="1280" cy="250" r="3" fill="#22C55E" opacity="0.25"/>
        <circle cx="1300" cy="320" r="2.5" fill="#22C55E" opacity="0.2"/>
        <circle cx="1290" cy="530" r="2.5" fill="#22C55E" opacity="0.18"/>

        <!-- 芯片座-左上 -->
        <rect x="30" y="40" width="60" height="40" fill="none" stroke="#22C55E" stroke-width="0.6" opacity="0.25"/>
        <line x1="30" y1="50" x2="10" y2="50" stroke="#22C55E" stroke-width="0.6" opacity="0.2"/>
        <line x1="30" y1="60" x2="10" y2="60" stroke="#22C55E" stroke-width="0.6" opacity="0.2"/>
        <line x1="30" y1="70" x2="10" y2="70" stroke="#22C55E" stroke-width="0.6" opacity="0.2"/>
        <line x1="90" y1="50" x2="110" y2="50" stroke="#22C55E" stroke-width="0.6" opacity="0.2"/>
        <line x1="90" y1="60" x2="110" y2="60" stroke="#22C55E" stroke-width="0.6" opacity="0.2"/>
        <line x1="90" y1="70" x2="110" y2="70" stroke="#22C55E" stroke-width="0.6" opacity="0.2"/>

        <!-- 芯片座-右下 -->
        <rect x="1350" y="720" width="50" height="35" fill="none" stroke="#22C55E" stroke-width="0.6" opacity="0.2"/>
        <line x1="1350" y1="730" x2="1335" y2="730" stroke="#22C55E" stroke-width="0.6" opacity="0.15"/>
        <line x1="1350" y1="740" x2="1335" y2="740" stroke="#22C55E" stroke-width="0.6" opacity="0.15"/>
        <line x1="1400" y1="730" x2="1415" y2="730" stroke="#22C55E" stroke-width="0.6" opacity="0.15"/>
        <line x1="1400" y1="740" x2="1415" y2="740" stroke="#22C55E" stroke-width="0.6" opacity="0.15"/>

        <!-- 散布焊点 -->
        <circle cx="250" cy="100" r="2" fill="#22C55E" opacity="0.15"/>
        <circle cx="400" cy="60" r="1.5" fill="#22C55E" opacity="0.12"/>
        <circle cx="700" cy="40" r="2" fill="#22C55E" opacity="0.1"/>
        <circle cx="1100" cy="80" r="1.5" fill="#22C55E" opacity="0.12"/>
        <circle cx="300" cy="750" r="2" fill="#22C55E" opacity="0.1"/>
        <circle cx="600" cy="770" r="1.5" fill="#22C55E" opacity="0.1"/>
        <circle cx="950" cy="760" r="2" fill="#22C55E" opacity="0.12"/>

        <!-- 水平虚线 -->
        <line x1="440" y1="780" x2="1000" y2="780" stroke="#22C55E" stroke-width="0.5" stroke-dasharray="4 4" opacity="0.15"/>
    </svg>

    <!-- 内容区域 -->
    <div class="absolute inset-0 flex z-10">
        <!-- 左侧标题区 -->
        <div class="w-[380px] flex flex-col items-center justify-center px-10">
            <!-- 芯片图标 -->
            <svg width="56" height="56" viewBox="0 0 56 56" class="mb-5">
                <rect x="12" y="12" width="32" height="32" rx="2" fill="none" stroke="#22C55E" stroke-width="1.5" opacity="0.5"/>
                <rect x="18" y="18" width="20" height="20" rx="1" fill="#22C55E" opacity="0.15"/>
                <line x1="12" y1="22" x2="4" y2="22" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="12" y1="28" x2="4" y2="28" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="12" y1="34" x2="4" y2="34" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="44" y1="22" x2="52" y2="22" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="44" y1="28" x2="52" y2="28" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="44" y1="34" x2="52" y2="34" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="22" y1="12" x2="22" y2="4" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="28" y1="12" x2="28" y2="4" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="34" y1="12" x2="34" y2="4" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="22" y1="44" x2="22" y2="52" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="28" y1="44" x2="28" y2="52" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
                <line x1="34" y1="44" x2="34" y2="52" stroke="#22C55E" stroke-width="1" opacity="0.4"/>
            </svg>
            <h1 class="text-3xl font-bold tracking-[4px]" style="color: #4ADE80; font-family: 'Courier New', monospace;">CONTENTS</h1>
            <p class="text-xs mt-2 tracking-[3px]" style="color: rgba(74,222,128,0.5); font-family: 'Courier New', monospace;">// INDEX_MAP</p>
            <div class="mt-4 flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full" style="background-color: #22C55E; opacity: 0.6;"></div>
                <div class="w-6 h-px" style="background-color: #22C55E; opacity: 0.2;"></div>
                <div class="w-1.5 h-1.5 rounded-full" style="background-color: #22C55E; opacity: 0.4;"></div>
                <div class="w-4 h-px" style="background-color: #22C55E; opacity: 0.15;"></div>
                <div class="w-1 h-1 rounded-full" style="background-color: #22C55E; opacity: 0.3;"></div>
            </div>
        </div>

        <!-- 右侧条目区 -->
        <div class="flex-1 flex items-center justify-center pr-14">
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 w-full max-w-[900px]">
                <!-- 条目1 -->
                <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(34,197,94,0.12);">
                    <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(34,197,94,0.3);">
                        <span class="text-sm font-bold" style="color: #4ADE80; font-family: 'Courier New', monospace;">01</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #D4FFEA;">项目背景</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: rgba(74,222,128,0.7); font-family: 'Courier New', monospace;">sys.init()</p>
                    </div>
                    <div class="flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #4ADE80; opacity: 0.7;"></div>
                </div>
                <!-- 条目2 -->
                <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(34,197,94,0.12);">
                    <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(34,197,94,0.3);">
                        <span class="text-sm font-bold" style="color: #4ADE80; font-family: 'Courier New', monospace;">02</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #D4FFEA;">核心功能</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: rgba(74,222,128,0.7); font-family: 'Courier New', monospace;">core.exec()</p>
                    </div>
                    <div class="flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #4ADE80; opacity: 0.7;"></div>
                </div>
                <!-- 条目3 -->
                <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(34,197,94,0.1);">
                    <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(34,197,94,0.25);">
                        <span class="text-sm font-bold" style="color: #4ADE80; font-family: 'Courier New', monospace;">03</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #D4FFEA;">技术架构</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: rgba(74,222,128,0.7); font-family: 'Courier New', monospace;">arch.load()</p>
                    </div>
                    <div class="flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #4ADE80; opacity: 0.6;"></div>
                </div>
                <!-- 条目4 -->
                <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(34,197,94,0.1);">
                    <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(34,197,94,0.25);">
                        <span class="text-sm font-bold" style="color: #4ADE80; font-family: 'Courier New', monospace;">04</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #D4FFEA;">实施计划</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: rgba(74,222,128,0.7); font-family: 'Courier New', monospace;">plan.run()</p>
                    </div>
                    <div class="flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #4ADE80; opacity: 0.6;"></div>
                </div>
                <!-- 条目5 -->
                <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(34,197,94,0.08);">
                    <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(34,197,94,0.2);">
                        <span class="text-sm font-bold" style="color: #4ADE80; font-family: 'Courier New', monospace;">05</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #D4FFEA;">成果展示</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: rgba(74,222,128,0.7); font-family: 'Courier New', monospace;">out.print()</p>
                    </div>
                    <div class="flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #4ADE80; opacity: 0.5;"></div>
                </div>
                <!-- 条目6 -->
                <div class="flex items-center gap-4 py-3.5 px-5 rounded-lg" style="background-color: rgba(34,197,94,0.08);">
                    <div class="flex-shrink-0 w-10 h-10 rounded flex items-center justify-center" style="background-color: rgba(34,197,94,0.2);">
                        <span class="text-sm font-bold" style="color: #4ADE80; font-family: 'Courier New', monospace;">06</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold truncate" style="color: #D4FFEA;">未来展望</h3>
                        <p class="text-xs mt-0.5 truncate" style="color: rgba(74,222,128,0.7); font-family: 'Courier New', monospace;">next.await()</p>
                    </div>
                    <div class="flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #4ADE80; opacity: 0.5;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
