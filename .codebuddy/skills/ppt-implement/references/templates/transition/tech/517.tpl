<!-- Template: 科技风-雷达脉冲 (Transition #517) -->
<div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 relative">
        <!-- 六边形网格背景 -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.06]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="hex517" x="0" y="0" width="60" height="52" patternUnits="userSpaceOnUse">
                    <polygon points="30,2 56,15 56,37 30,50 4,37 4,15" fill="none" stroke="#67e8f9" stroke-width="0.8"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#hex517)"/>
        </svg>

        <!-- 雷达圆环 -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[520px] h-[520px] rounded-full border border-cyan-500/10"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] rounded-full border border-cyan-400/15"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[280px] h-[280px] rounded-full border border-cyan-400/20"></div>

        <!-- 四角定位框 -->
        <div class="absolute top-12 left-12">
            <div class="w-16 h-16 border-l-2 border-t-2 border-cyan-500/50"></div>
            <div class="text-[10px] font-mono text-cyan-600 mt-1">SYS.ACTIVE</div>
        </div>
        <div class="absolute top-12 right-12">
            <div class="w-16 h-16 border-r-2 border-t-2 border-cyan-500/50 ml-auto"></div>
            <div class="text-[10px] font-mono text-cyan-600 mt-1 text-right">NODE.READY</div>
        </div>
        <div class="absolute bottom-12 left-12">
            <div class="text-[10px] font-mono text-cyan-600 mb-1">SEC.VERIFIED</div>
            <div class="w-16 h-16 border-l-2 border-b-2 border-cyan-500/50"></div>
        </div>
        <div class="absolute bottom-12 right-12">
            <div class="text-[10px] font-mono text-cyan-600 mb-1 text-right">LINK.STABLE</div>
            <div class="w-16 h-16 border-r-2 border-b-2 border-cyan-500/50 ml-auto"></div>
        </div>

        <!-- 水平扫描线 -->
        <div class="absolute top-1/2 left-0 right-0 h-px bg-gradient-to-r from-transparent via-cyan-500/30 to-transparent -translate-y-1/2"></div>
        <!-- 垂直扫描线 -->
        <div class="absolute top-0 bottom-0 left-1/2 w-px bg-gradient-to-b from-transparent via-cyan-500/20 to-transparent -translate-x-1/2"></div>

        <!-- 中央内容区 -->
        <div class="relative z-10 flex items-center justify-center h-full">
            <div class="text-center">
                <!-- 章节编号 -->
                <div class="relative inline-block mb-8">
                    <div class="w-28 h-28 rounded-full border-2 border-cyan-400/60 flex items-center justify-center mx-auto" style="box-shadow: 0 0 40px rgba(34,211,238,0.15), inset 0 0 30px rgba(34,211,238,0.08);">
                        <span class="text-5xl font-mono font-bold text-cyan-400" style="text-shadow: 0 0 12px rgba(34,211,238,0.6);">01</span>
                    </div>
                    <!-- 外部装饰弧线 -->
                    <svg class="absolute -top-3 -left-3 w-[136px] h-[136px]" viewBox="0 0 136 136">
                        <circle cx="68" cy="68" r="64" fill="none" stroke="rgba(34,211,238,0.25)" stroke-width="1" stroke-dasharray="8 12"/>
                    </svg>
                </div>

                <!-- 标题 -->
                <h1 class="text-5xl font-bold text-white mb-4 tracking-wide">项目背景</h1>

                <!-- 分隔线 -->
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div class="w-16 h-px bg-gradient-to-r from-transparent to-cyan-500/70"></div>
                    <div class="w-1.5 h-1.5 bg-cyan-400 rotate-45"></div>
                    <div class="w-16 h-px bg-gradient-to-l from-transparent to-cyan-500/70"></div>
                </div>

                <!-- 副标题 -->
                <p class="text-lg text-cyan-400/80 font-light tracking-widest">了解项目的起源与目标</p>
            </div>
        </div>
    </div>
</div>
