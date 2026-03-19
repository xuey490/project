<!-- Template: 商务风-几何框架 (Transition #536) -->
<div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-slate-900">
    <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

        <!-- 右侧大色块 -->
        <div class="absolute top-0 right-0 w-[480px] h-full bg-blue-600"></div>

        <!-- 右侧色块上的竖向细线纹理 -->
        <svg class="absolute top-0 right-0 w-[480px] h-full opacity-[0.06]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="vlines536" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse">
                    <line x1="12" y1="0" x2="12" y2="24" stroke="white" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#vlines536)"/>
        </svg>

        <!-- 左上角 L 形框线 -->
        <div class="absolute top-[40px] left-[40px] w-[80px] h-[2px] bg-white/20"></div>
        <div class="absolute top-[40px] left-[40px] w-[2px] h-[80px] bg-white/20"></div>

        <!-- 右下角 L 形框线 -->
        <div class="absolute bottom-[40px] right-[40px] w-[80px] h-[2px] bg-white/20"></div>
        <div class="absolute bottom-[40px] right-[40px] w-[2px] h-[80px] bg-white/20"></div>

        <!-- 交界处装饰线 -->
        <div class="absolute top-[100px] right-[480px] w-px h-[520px] bg-white/[0.06]"></div>

        <!-- 左半部分内容 -->
        <div class="absolute top-1/2 -translate-y-1/2 left-[80px]">
            <!-- 标签 -->
            <div class="flex items-center gap-4 mb-10">
                <div class="w-12 h-[2px] bg-blue-400"></div>
                <span class="text-xs font-bold tracking-[0.35em] text-blue-400 uppercase">Section</span>
            </div>

            <!-- 大编号 -->
            <div class="text-[120px] font-black text-white leading-none mb-4 -tracking-[0.04em]" style="font-variant-numeric: tabular-nums;">01</div>

            <!-- 标题 -->
            <h1 class="text-4xl font-bold text-white mb-4 tracking-tight">项目背景</h1>

            <!-- 分隔短线 -->
            <div class="w-10 h-[3px] bg-blue-400 mb-4"></div>

            <!-- 副标题 -->
            <p class="text-lg text-slate-400 font-light tracking-wide">了解项目的起源与目标</p>
        </div>

        <!-- 右侧色块中央装饰 — 大空心圆环 -->
        <svg class="absolute top-1/2 -translate-y-1/2 right-[80px] w-[320px] h-[320px] opacity-[0.08]" viewBox="0 0 320 320" fill="none">
            <circle cx="160" cy="160" r="140" stroke="white" stroke-width="1.5"/>
            <circle cx="160" cy="160" r="120" stroke="white" stroke-width="0.8"/>
            <circle cx="160" cy="160" r="100" stroke="white" stroke-width="0.5"/>
        </svg>

        <!-- 右侧色块底部 — 细横线 + 标记 -->
        <div class="absolute bottom-[48px] right-[60px] flex items-center gap-4">
            <div class="flex gap-[6px]">
                <div class="w-8 h-[3px] bg-white/50 rounded-full"></div>
                <div class="w-4 h-[3px] bg-white/20 rounded-full"></div>
                <div class="w-4 h-[3px] bg-white/20 rounded-full"></div>
            </div>
        </div>

        <!-- 左下角装饰点阵 -->
        <svg class="absolute bottom-[48px] left-[40px] w-[48px] h-[48px] opacity-[0.12]" viewBox="0 0 48 48" fill="none">
            <circle cx="6" cy="6" r="1.5" fill="white"/>
            <circle cx="18" cy="6" r="1.5" fill="white"/>
            <circle cx="30" cy="6" r="1.5" fill="white"/>
            <circle cx="42" cy="6" r="1.5" fill="white"/>
            <circle cx="6" cy="18" r="1.5" fill="white"/>
            <circle cx="18" cy="18" r="1.5" fill="white"/>
            <circle cx="30" cy="18" r="1.5" fill="white"/>
            <circle cx="42" cy="18" r="1.5" fill="white"/>
            <circle cx="6" cy="30" r="1.5" fill="white"/>
            <circle cx="18" cy="30" r="1.5" fill="white"/>
            <circle cx="30" cy="30" r="1.5" fill="white"/>
            <circle cx="42" cy="30" r="1.5" fill="white"/>
            <circle cx="6" cy="42" r="1.5" fill="white"/>
            <circle cx="18" cy="42" r="1.5" fill="white"/>
            <circle cx="30" cy="42" r="1.5" fill="white"/>
            <circle cx="42" cy="42" r="1.5" fill="white"/>
        </svg>

    </div>
</div>
