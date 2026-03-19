<!-- Template: 卡通风格-变体9 (Content #1529) - 纯文字·天气预报七日卡片 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #E3F2FD;">
    <!-- 装饰圆 -->
    <div class="absolute -top-16 -right-16 w-[200px] h-[200px] rounded-full" style="background-color: #BBDEFB; opacity: 0.5;"></div>
    <div class="absolute -bottom-12 -left-12 w-[160px] h-[160px] rounded-full" style="background-color: #FFE082; opacity: 0.35;"></div>
    <!-- 太阳装饰 -->
    <div class="absolute top-16 right-32 w-[80px] h-[80px] rounded-full" style="background-color: #FFA726; opacity: 0.25;"></div>
    <!-- 云朵SVG装饰 -->
    <svg class="absolute top-40 left-20 w-20 h-14 opacity-20" viewBox="0 0 80 50"><ellipse cx="30" cy="35" rx="28" ry="15" fill="#90CAF9"/><ellipse cx="50" cy="30" rx="22" ry="18" fill="#90CAF9"/><ellipse cx="18" cy="38" rx="16" ry="10" fill="#90CAF9"/></svg>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题卡片 -->
        <div class="rounded-3xl py-5 px-10 flex items-center justify-between mb-5" style="background-color: #FFFFFF;">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background-color: #42A5F5;">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M6.76 4.84l-1.8-1.79-1.41 1.41 1.79 1.79 1.42-1.41zM4 10.5H1v2h3v-2zm9-9.95h-2V3.5h2V.55zm7.45 3.91l-1.41-1.41-1.79 1.79 1.41 1.41 1.79-1.79zM20 10.5v2h3v-2h-3zm-8-5c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm-1 4h2v2.95h-2V19.5zm-7.45-.96l1.41 1.41 1.79-1.8-1.41-1.41-1.79 1.8z" fill="white"/></svg>
                </div>
                <div>
                    <h1 class="text-[38px] font-bold" style="color: #1565C0;">本周天气预报</h1>
                    <p class="text-[14px] font-medium" style="color: #90A4AE;">Happy Weather Forecast</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[14px] font-medium" style="color: #90A4AE;">2024年1月</p>
                <p class="text-[30px] font-bold" style="color: #42A5F5;">15 - 21日</p>
            </div>
        </div>

        <!-- 七日天气卡片 -->
        <div class="flex-1 grid grid-cols-7 gap-3">
            <!-- 周一 晴 -->
            <div class="rounded-2xl p-4 flex flex-col items-center" style="background-color: #FFA726;">
                <p class="text-[16px] font-bold text-white mb-3">周一</p>
                <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="5" fill="white"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12" stroke="white" stroke-width="2"/></svg>
                <p class="text-[22px] font-bold text-white mb-1">28°</p>
                <p class="text-[14px] font-medium text-white">晴天</p>
            </div>
            <!-- 周二 雨 -->
            <div class="rounded-2xl p-4 flex flex-col items-center" style="background-color: #42A5F5;">
                <p class="text-[16px] font-bold text-white mb-3">周二</p>
                <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none"><path d="M17.5 12c0-1.93-1.03-3.68-2.69-4.52A6.01 6.01 0 009.01 4C5.69 4 3 6.69 3 10c0 .73.13 1.43.37 2.08A4.49 4.49 0 000 16.5C0 18.98 2.02 21 4.5 21h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96A5.89 5.89 0 0017.5 12z" fill="white" opacity="0.7"/><path d="M7 16l-1 3M11 16l-1 3M15 16l-1 3" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
                <p class="text-[22px] font-bold text-white mb-1">22°</p>
                <p class="text-[14px] font-medium text-white">小雨</p>
            </div>
            <!-- 周三 多云 -->
            <div class="rounded-2xl p-4 flex flex-col items-center" style="background-color: #78909C;">
                <p class="text-[16px] font-bold text-white mb-3">周三</p>
                <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none"><path d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z" fill="white"/></svg>
                <p class="text-[22px] font-bold text-white mb-1">24°</p>
                <p class="text-[14px] font-medium text-white">多云</p>
            </div>
            <!-- 周四 雷阵雨 -->
            <div class="rounded-2xl p-4 flex flex-col items-center" style="background-color: #5C6BC0;">
                <p class="text-[16px] font-bold text-white mb-3">周四</p>
                <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none"><path d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z" fill="white" opacity="0.6"/><path d="M13 16l-3 5h4l-3 5" stroke="#FFC107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <p class="text-[22px] font-bold text-white mb-1">20°</p>
                <p class="text-[14px] font-medium text-white">雷阵雨</p>
            </div>
            <!-- 周五 晴转多云 -->
            <div class="rounded-2xl p-4 flex flex-col items-center" style="background-color: #FFC107;">
                <p class="text-[16px] font-bold text-white mb-3">周五</p>
                <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none"><circle cx="8" cy="10" r="4" fill="white"/><path d="M8 4v2M2 10h2M14 10h2M4.22 5.64l1.42 1.42M11.78 5.64l-1.42 1.42" stroke="white" stroke-width="1.5"/><path d="M19 14a3 3 0 00-3-3h-1a4 4 0 00-7.9-.87A3 3 0 005 13h14a3 3 0 000-6" fill="white" opacity="0.7"/></svg>
                <p class="text-[22px] font-bold text-white mb-1">26°</p>
                <p class="text-[14px] font-medium text-white">晴转多云</p>
            </div>
            <!-- 周六 晴热 -->
            <div class="rounded-2xl p-4 flex flex-col items-center" style="background-color: #EF5350;">
                <p class="text-[16px] font-bold text-white mb-3">周六</p>
                <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="5" fill="white"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12" stroke="white" stroke-width="2.5"/></svg>
                <p class="text-[22px] font-bold text-white mb-1">30°</p>
                <p class="text-[14px] font-medium text-white">晴热</p>
            </div>
            <!-- 周日 晴 -->
            <div class="rounded-2xl p-4 flex flex-col items-center" style="background-color: #EC407A;">
                <p class="text-[16px] font-bold text-white mb-3">周日</p>
                <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="white" opacity="0.7"/><circle cx="12" cy="8" r="3.5" fill="white"/><path d="M12 3v1.5M7.5 5l1 1M5 9.5h1.5M16.5 5l-1 1M19 9.5h-1.5" stroke="white" stroke-width="1.5"/></svg>
                <p class="text-[22px] font-bold text-white mb-1">27°</p>
                <p class="text-[14px] font-medium text-white">雨后晴</p>
            </div>
        </div>

        <!-- 底部提示 -->
        <div class="mt-5 rounded-2xl py-4 px-8 flex items-center gap-5" style="background-color: #FFFFFF;">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #42A5F5;">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="white"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-[18px] font-bold mb-1" style="color: #37474F;">温馨提示</h3>
                <p class="text-[14px]" style="color: #78909C;">本周气温变化较大，请注意增减衣物。周二、周四有降雨，出门记得带伞</p>
            </div>
            <div class="flex gap-3">
                <div class="rounded-full px-4 py-2" style="background-color: #E3F2FD;">
                    <p class="text-[13px] font-bold" style="color: #1565C0;">湿度 65%</p>
                </div>
                <div class="rounded-full px-4 py-2" style="background-color: #E8F5E9;">
                    <p class="text-[13px] font-bold" style="color: #2E7D32;">风力 3级</p>
                </div>
            </div>
        </div>
    </div>
</div>
