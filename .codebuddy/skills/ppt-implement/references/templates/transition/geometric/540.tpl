<!-- Template: 几何风-棱镜构成 (Transition #540) -->
<div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-slate-950">
    <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

        <!-- 背景网格 -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid540" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                    <line x1="0" y1="60" x2="60" y2="60" stroke="white" stroke-width="0.3"/>
                    <line x1="60" y1="0" x2="60" y2="60" stroke="white" stroke-width="0.3"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid540)"/>
        </svg>

        <!-- 左上角：大三角形线框 -->
        <svg class="absolute top-0 left-0 w-[400px] h-[360px] opacity-[0.07]" viewBox="0 0 400 360" fill="none">
            <polygon points="0,0 400,0 0,360" stroke="white" stroke-width="1" fill="none"/>
            <polygon points="0,0 300,0 0,270" stroke="white" stroke-width="0.6" fill="none"/>
            <polygon points="0,0 200,0 0,180" stroke="white" stroke-width="0.4" fill="none"/>
        </svg>

        <!-- 右下角：大三角形线框（镜像） -->
        <svg class="absolute bottom-0 right-0 w-[400px] h-[360px] opacity-[0.07]" viewBox="0 0 400 360" fill="none">
            <polygon points="400,360 0,360 400,0" stroke="white" stroke-width="1" fill="none"/>
            <polygon points="400,360 100,360 400,90" stroke="white" stroke-width="0.6" fill="none"/>
            <polygon points="400,360 200,360 400,180" stroke="white" stroke-width="0.4" fill="none"/>
        </svg>

        <!-- 左侧竖向色块 -->
        <div class="absolute top-[120px] left-0 w-[5px] h-[200px] bg-violet-500"></div>

        <!-- 中央几何装饰 — 旋转菱形矩阵 -->
        <svg class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] opacity-[0.05]" viewBox="0 0 600 600" fill="none">
            <!-- 外层大菱形 -->
            <rect x="130" y="130" width="340" height="340" rx="4" stroke="white" stroke-width="1" fill="none" transform="rotate(45 300 300)"/>
            <!-- 中层菱形 -->
            <rect x="180" y="180" width="240" height="240" rx="2" stroke="white" stroke-width="0.8" fill="none" transform="rotate(45 300 300)"/>
            <!-- 内层菱形 -->
            <rect x="230" y="230" width="140" height="140" rx="1" stroke="white" stroke-width="0.6" fill="none" transform="rotate(45 300 300)"/>
        </svg>

        <!-- 右上角装饰 — 平行四边形 -->
        <div class="absolute top-[40px] right-[60px] w-[160px] h-[60px] border border-white/[0.08] transform -skew-x-12"></div>
        <div class="absolute top-[52px] right-[72px] w-[160px] h-[60px] border border-white/[0.05] transform -skew-x-12"></div>

        <!-- 中央内容 -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center z-10">

            <!-- 编号 — 几何边框 -->
            <div class="relative inline-block mb-8">
                <svg class="w-[120px] h-[120px]" viewBox="0 0 120 120" fill="none">
                    <!-- 外层旋转方框 -->
                    <rect x="18" y="18" width="84" height="84" stroke="#8b5cf6" stroke-width="2" fill="none" transform="rotate(45 60 60)"/>
                    <!-- 内层正方框 -->
                    <rect x="24" y="24" width="72" height="72" stroke="white" stroke-width="0.6" fill="none" opacity="0.3"/>
                    <!-- 四角标记点 -->
                    <circle cx="60" cy="2" r="2.5" fill="#8b5cf6" opacity="0.6"/>
                    <circle cx="118" cy="60" r="2.5" fill="#8b5cf6" opacity="0.6"/>
                    <circle cx="60" cy="118" r="2.5" fill="#8b5cf6" opacity="0.6"/>
                    <circle cx="2" cy="60" r="2.5" fill="#8b5cf6" opacity="0.6"/>
                </svg>
                <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-5xl font-black text-white tracking-tight">01</span>
            </div>

            <!-- 分隔线 -->
            <div class="flex items-center justify-center gap-4 mb-6">
                <div class="w-16 h-px bg-violet-500/40"></div>
                <div class="w-2 h-2 transform rotate-45 border border-violet-400/50"></div>
                <div class="w-16 h-px bg-violet-500/40"></div>
            </div>

            <!-- 标题 -->
            <h1 class="text-5xl font-bold text-white mb-4 tracking-tight">项目背景</h1>

            <!-- 副标题 -->
            <p class="text-lg text-slate-400 font-light tracking-widest">了解项目的起源与目标</p>
        </div>

        <!-- 左下角坐标标记 -->
        <div class="absolute bottom-[36px] left-[40px] flex items-end gap-2">
            <div class="text-xs font-mono text-white/15 tracking-wider">540</div>
            <div class="w-8 h-px bg-white/10 mb-1"></div>
        </div>

        <!-- 右下角装饰点阵 -->
        <svg class="absolute bottom-[32px] right-[40px] w-[64px] h-[40px] opacity-[0.10]" viewBox="0 0 64 40" fill="none">
            <circle cx="6" cy="6" r="1.5" fill="#8b5cf6"/>
            <circle cx="22" cy="6" r="1.5" fill="#8b5cf6"/>
            <circle cx="38" cy="6" r="1.5" fill="#8b5cf6"/>
            <circle cx="54" cy="6" r="1.5" fill="#8b5cf6"/>
            <circle cx="6" cy="20" r="1.5" fill="#8b5cf6"/>
            <circle cx="22" cy="20" r="1.5" fill="#8b5cf6"/>
            <circle cx="38" cy="20" r="1.5" fill="#8b5cf6"/>
            <circle cx="54" cy="20" r="1.5" fill="#8b5cf6"/>
            <circle cx="6" cy="34" r="1.5" fill="#8b5cf6"/>
            <circle cx="22" cy="34" r="1.5" fill="#8b5cf6"/>
            <circle cx="38" cy="34" r="1.5" fill="#8b5cf6"/>
            <circle cx="54" cy="34" r="1.5" fill="#8b5cf6"/>
        </svg>

        <!-- 顶部中央短线标记 -->
        <div class="absolute top-[28px] left-1/2 -translate-x-1/2 w-12 h-px bg-violet-500/30"></div>

    </div>
</div>
