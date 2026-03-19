<!-- Template: 几何风格-变体4 (Content #1544) - 层次结构 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #F8FAFC;">
    <div class="absolute top-0 left-0 right-0 h-[6px]" style="background-color: #4338CA;"></div>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题 -->
        <div class="text-center mb-6">
            <h1 class="text-[38px] font-bold" style="color: #1E1B4B;">层次结构</h1>
            <p class="text-[14px]" style="color: #94A3B8;">从核心向外辐射的同心圆设计原理</p>
        </div>
        <!-- 同心圆 - 使用flex居中 -->
        <div class="flex-1 flex items-center justify-center">
            <div class="relative" style="width: 520px; height: 520px;">
                <!-- 最外圈 -->
                <div class="absolute inset-0 rounded-full" style="border: 3px solid #E0E7FF;"></div>
                <!-- 外圈4个节点 -->
                <div class="absolute rounded-lg p-3 text-center" style="left: 50%; top: -8px; transform: translateX(-50%); background-color: #4338CA; width: 110px;">
                    <p class="text-[14px] font-bold" style="color: #FFFFFF;">展示层</p>
                    <p class="text-[11px]" style="color: #C7D2FE;">Layer 6</p>
                </div>
                <div class="absolute rounded-lg p-3 text-center" style="right: -12px; top: 50%; transform: translateY(-50%); background-color: #3730A3; width: 110px;">
                    <p class="text-[14px] font-bold" style="color: #FFFFFF;">交互层</p>
                    <p class="text-[11px]" style="color: #C7D2FE;">Layer 5</p>
                </div>
                <div class="absolute rounded-lg p-3 text-center" style="left: 50%; bottom: -8px; transform: translateX(-50%); background-color: #312E81; width: 110px;">
                    <p class="text-[14px] font-bold" style="color: #FFFFFF;">逻辑层</p>
                    <p class="text-[11px]" style="color: #C7D2FE;">Layer 4</p>
                </div>
                <div class="absolute rounded-lg p-3 text-center" style="left: -12px; top: 50%; transform: translateY(-50%); background-color: #1E1B4B; width: 110px;">
                    <p class="text-[14px] font-bold" style="color: #FFFFFF;">数据层</p>
                    <p class="text-[11px]" style="color: #C7D2FE;">Layer 3</p>
                </div>
                <!-- 中圈 -->
                <div class="absolute rounded-full" style="top: 25%; left: 25%; width: 50%; height: 50%; border: 3px solid #C7D2FE;"></div>
                <!-- 中圈2个节点 -->
                <div class="absolute rounded-lg p-2 text-center" style="right: 18%; top: 18%; background-color: #4338CA; opacity: 0.8; width: 90px;">
                    <p class="text-[13px] font-bold" style="color: #FFFFFF;">服务层</p>
                    <p class="text-[11px]" style="color: #C7D2FE;">Layer 2</p>
                </div>
                <div class="absolute rounded-lg p-2 text-center" style="left: 18%; bottom: 18%; background-color: #3730A3; opacity: 0.8; width: 90px;">
                    <p class="text-[13px] font-bold" style="color: #FFFFFF;">基础层</p>
                    <p class="text-[11px]" style="color: #C7D2FE;">Layer 1</p>
                </div>
                <!-- 核心圆 -->
                <div class="absolute rounded-full flex items-center justify-center" style="top: 37%; left: 37%; width: 26%; height: 26%; background-color: #4338CA;">
                    <div class="text-center">
                        <p class="text-[20px] font-bold" style="color: #FFFFFF;">核心</p>
                        <p class="text-[11px]" style="color: #C7D2FE;">Core</p>
                    </div>
                </div>
                <!-- 连接虚线 -->
                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 520">
                    <line x1="260" y1="260" x2="260" y2="40" stroke="#6366F1" stroke-width="1.5" stroke-dasharray="4,4" opacity="0.3"/>
                    <line x1="260" y1="260" x2="480" y2="260" stroke="#6366F1" stroke-width="1.5" stroke-dasharray="4,4" opacity="0.3"/>
                    <line x1="260" y1="260" x2="260" y2="480" stroke="#6366F1" stroke-width="1.5" stroke-dasharray="4,4" opacity="0.3"/>
                    <line x1="260" y1="260" x2="40" y2="260" stroke="#6366F1" stroke-width="1.5" stroke-dasharray="4,4" opacity="0.3"/>
                </svg>
            </div>
        </div>
    </div>
</div>
