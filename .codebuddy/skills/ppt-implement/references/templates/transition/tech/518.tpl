<!-- Template: 科技风-数据节点 (Transition #518) -->
<div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-gradient-to-br from-[#0a0e1a] via-[#0d1529] to-[#0a0e1a]">
    <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 relative">
        <!-- 点阵背景 -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.08]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="dots518" x="0" y="0" width="32" height="32" patternUnits="userSpaceOnUse">
                    <circle cx="16" cy="16" r="1" fill="#818cf8"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dots518)"/>
        </svg>

        <!-- 连接线网络 -->
        <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <!-- 左侧节点连线 -->
            <line x1="120" y1="180" x2="340" y2="280" stroke="rgba(99,102,241,0.15)" stroke-width="1"/>
            <line x1="80" y1="420" x2="340" y2="340" stroke="rgba(99,102,241,0.12)" stroke-width="1"/>
            <line x1="200" y1="600" x2="400" y2="400" stroke="rgba(99,102,241,0.10)" stroke-width="1"/>
            <!-- 右侧节点连线 -->
            <line x1="1010" y1="280" x2="1230" y2="180" stroke="rgba(99,102,241,0.15)" stroke-width="1"/>
            <line x1="1010" y1="340" x2="1270" y2="420" stroke="rgba(99,102,241,0.12)" stroke-width="1"/>
            <line x1="950" y1="400" x2="1150" y2="600" stroke="rgba(99,102,241,0.10)" stroke-width="1"/>
            <!-- 散布节点 -->
            <circle cx="120" cy="180" r="3" fill="rgba(129,140,248,0.4)"/>
            <circle cx="80" cy="420" r="2.5" fill="rgba(129,140,248,0.3)"/>
            <circle cx="200" cy="600" r="2" fill="rgba(129,140,248,0.25)"/>
            <circle cx="1230" cy="180" r="3" fill="rgba(129,140,248,0.4)"/>
            <circle cx="1270" cy="420" r="2.5" fill="rgba(129,140,248,0.3)"/>
            <circle cx="1150" cy="600" r="2" fill="rgba(129,140,248,0.25)"/>
            <circle cx="340" cy="280" r="4" fill="rgba(129,140,248,0.35)"/>
            <circle cx="340" cy="340" r="3" fill="rgba(129,140,248,0.3)"/>
            <circle cx="400" cy="400" r="3" fill="rgba(129,140,248,0.25)"/>
            <circle cx="1010" cy="280" r="4" fill="rgba(129,140,248,0.35)"/>
            <circle cx="1010" cy="340" r="3" fill="rgba(129,140,248,0.3)"/>
            <circle cx="950" cy="400" r="3" fill="rgba(129,140,248,0.25)"/>
        </svg>

        <!-- 顶部状态栏 -->
        <div class="absolute top-6 left-0 right-0 flex justify-between items-center px-4">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-indigo-400" style="box-shadow: 0 0 6px rgba(129,140,248,0.6);"></div>
                <span class="text-[11px] font-mono text-indigo-400/60 tracking-widest">SECTION_TRANSITION</span>
            </div>
            <div class="flex items-center gap-6">
                <span class="text-[11px] font-mono text-indigo-400/40">STATUS: ACTIVE</span>
                <div class="flex gap-1.5">
                    <div class="w-1 h-3 bg-indigo-500/40"></div>
                    <div class="w-1 h-4 bg-indigo-500/50"></div>
                    <div class="w-1 h-5 bg-indigo-500/60"></div>
                    <div class="w-1 h-3 bg-indigo-500/40"></div>
                    <div class="w-1 h-4 bg-indigo-500/50"></div>
                </div>
            </div>
        </div>

        <!-- 中央内容 -->
        <div class="relative z-10 flex items-center justify-center h-full">
            <div class="text-center">
                <!-- 编号区域 -->
                <div class="relative inline-flex items-center gap-5 mb-10">
                    <div class="w-20 h-px bg-gradient-to-r from-transparent to-indigo-500/60"></div>
                    <div class="relative">
                        <div class="text-6xl font-mono font-bold text-transparent bg-clip-text bg-gradient-to-b from-indigo-300 to-indigo-500" style="filter: drop-shadow(0 0 16px rgba(99,102,241,0.3));">01</div>
                    </div>
                    <div class="w-20 h-px bg-gradient-to-l from-transparent to-indigo-500/60"></div>
                </div>

                <!-- 标题 -->
                <h1 class="text-5xl font-bold text-white mb-5 tracking-wider">项目背景</h1>

                <!-- 副标题 -->
                <p class="text-lg text-indigo-300/70 tracking-[0.2em] font-light">了解项目的起源与目标</p>

                <!-- 底部装饰 -->
                <div class="flex items-center justify-center gap-2 mt-10">
                    <div class="w-8 h-px bg-indigo-500/40"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-400/60"></div>
                    <div class="w-24 h-px bg-gradient-to-r from-indigo-500/60 via-indigo-400/80 to-indigo-500/60"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-400/60"></div>
                    <div class="w-8 h-px bg-indigo-500/40"></div>
                </div>
            </div>
        </div>

        <!-- 底部状态栏 -->
        <div class="absolute bottom-6 left-0 right-0 flex justify-between items-center px-4">
            <span class="text-[10px] font-mono text-indigo-500/30 tracking-wider">DATA_NETWORK v2.0</span>
            <span class="text-[10px] font-mono text-indigo-500/30 tracking-wider">NODES: 12 CONNECTED</span>
        </div>
    </div>
</div>
