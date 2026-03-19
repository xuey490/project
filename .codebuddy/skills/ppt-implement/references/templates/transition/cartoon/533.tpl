<!-- Template: 卡通风-剪贴簿 (Transition #533) -->
<div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-stone-200">
    <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 relative">
        <!-- 牛皮纸质感方格 -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.06]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="craft533" x="0" y="0" width="48" height="48" patternUnits="userSpaceOnUse">
                    <rect x="0" y="0" width="48" height="48" fill="none" stroke="#78716c" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#craft533)"/>
        </svg>

        <!-- 左上角便利贴 -->
        <div class="absolute top-12 left-16 w-28 h-28 bg-pink-200 transform -rotate-6 p-3" style="box-shadow: 2px 2px 4px rgba(0,0,0,0.15);">
            <svg class="w-full h-full" viewBox="0 0 80 80" fill="none">
                <path d="M20 25 L60 25 M20 40 L55 40 M20 55 L45 55" stroke="#db2777" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
            </svg>
        </div>

        <!-- 右上角便利贴 -->
        <div class="absolute top-16 right-20 w-24 h-24 bg-amber-200 transform rotate-3 p-3" style="box-shadow: 2px 2px 4px rgba(0,0,0,0.15);">
            <svg class="w-full h-full" viewBox="0 0 72 72" fill="none">
                <circle cx="36" cy="30" r="12" stroke="#d97706" stroke-width="1.5" fill="none" opacity="0.4"/>
                <path d="M24 52 L48 52" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
            </svg>
        </div>

        <!-- 左下角便利贴 -->
        <div class="absolute bottom-16 left-20 w-26 h-26 bg-emerald-200 transform rotate-4 p-3" style="box-shadow: 2px 2px 4px rgba(0,0,0,0.15);">
            <svg class="w-16 h-16" viewBox="0 0 64 64" fill="none">
                <path d="M12 48 L32 16 L52 48 Z" stroke="#059669" stroke-width="1.5" fill="none" opacity="0.4"/>
            </svg>
        </div>

        <!-- 右下角便利贴 -->
        <div class="absolute bottom-20 right-16 w-24 h-24 bg-blue-200 transform -rotate-3 p-3" style="box-shadow: 2px 2px 4px rgba(0,0,0,0.15);">
            <svg class="w-full h-full" viewBox="0 0 72 72" fill="none">
                <path d="M16 36 L36 16 L56 36 L36 56 Z" stroke="#2563eb" stroke-width="1.5" fill="none" opacity="0.4"/>
            </svg>
        </div>

        <!-- 胶带装饰 -->
        <div class="absolute top-8 left-[38%] w-20 h-5 bg-amber-100/70 transform -rotate-2 border border-amber-300/40"></div>
        <div class="absolute bottom-12 right-[35%] w-16 h-5 bg-sky-100/70 transform rotate-3 border border-sky-300/40"></div>

        <!-- 回形针 -->
        <svg class="absolute top-10 right-[48%] w-5 h-10 opacity-50" viewBox="0 0 20 40" fill="none">
            <path d="M6 2 L6 32 Q6 38, 10 38 Q14 38, 14 32 L14 8 Q14 4, 10 4 Q6 4, 6 8" stroke="#94a3b8" stroke-width="1.5" fill="none" stroke-linecap="round"/>
        </svg>

        <!-- 中央内容 -->
        <div class="relative z-10 flex items-center justify-center h-full">
            <div class="text-center">
                <!-- 编号圆形贴纸 -->
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-rose-400 mb-6 relative transform -rotate-3" style="box-shadow: 3px 3px 0 rgba(0,0,0,0.2);">
                    <span class="text-4xl font-black text-white" style="font-family: 'Comic Sans MS', cursive;">01</span>
                    <!-- 高光 -->
                    <div class="absolute top-2 right-3 w-4 h-2.5 rounded-full bg-white/30 transform -rotate-12"></div>
                </div>

                <!-- 主卡片 - 白色便签风格 -->
                <div class="relative">
                    <!-- 胶带 -->
                    <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 w-16 h-5 bg-amber-100/80 border border-amber-300/40 z-20"></div>

                    <div class="bg-white rounded-sm px-16 py-8 relative transform rotate-1" style="box-shadow: 3px 3px 8px rgba(0,0,0,0.15);">
                        <h1 class="text-4xl font-black text-slate-700 mb-3" style="font-family: 'Comic Sans MS', cursive;">项目背景</h1>

                        <!-- 手绘虚线 -->
                        <svg class="mx-auto mb-3" width="160" height="4" viewBox="0 0 160 4" fill="none">
                            <line x1="4" y1="2" x2="156" y2="2" stroke="#e5e7eb" stroke-width="2" stroke-dasharray="6 4" stroke-linecap="round"/>
                        </svg>

                        <p class="text-lg text-slate-400 font-medium">了解项目的起源与目标</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
