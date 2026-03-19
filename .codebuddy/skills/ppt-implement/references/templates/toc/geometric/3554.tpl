<!-- 
模板ID: 3554
模板名称: 几何风-斜切色块
适用场景: 动感几何风目录页
设计特点: SVG对角线分割背景+斜切编号+纵向列表,橙红纯色,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-orange-50 flex items-center justify-center relative overflow-hidden">
  <!-- 背景斜切 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 大斜切色块 -->
    <polygon points="0,0 500,0 350,810 0,810" fill="#ea580c" opacity="0.06"/>
    <polygon points="1440,0 1440,810 1100,810 1250,0" fill="#ea580c" opacity="0.04"/>
    <!-- 斜线装饰 -->
    <line x1="450" y1="0" x2="300" y2="810" stroke="#fdba74" stroke-width="1" opacity="0.4"/>
    <line x1="470" y1="0" x2="320" y2="810" stroke="#fdba74" stroke-width="1" opacity="0.2"/>
    <line x1="1150" y1="0" x2="1000" y2="810" stroke="#fdba74" stroke-width="1" opacity="0.3"/>
    <!-- 小三角装饰 -->
    <polygon points="1350,60 1380,90 1320,90" fill="#ea580c" opacity="0.12"/>
    <polygon points="100,720 130,750 70,750" fill="#ea580c" opacity="0.1"/>
  </svg>

  <div class="relative z-10 w-[1100px]">
    <!-- 标题区 -->
    <div class="flex items-center gap-5 mb-14">
      <div class="w-3 h-14 bg-orange-600 transform -skew-x-12"></div>
      <h2 class="text-4xl font-bold text-orange-900 tracking-wide">目录</h2>
      <div class="flex-1 h-px bg-orange-200"></div>
      <span class="text-sm text-orange-400 tracking-widest uppercase">Contents</span>
    </div>

    <!-- 纵向列表 -->
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-6 bg-white rounded-lg px-6 py-4 shadow-sm">
        <div class="w-14 h-14 bg-orange-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0 transform -skew-x-6">
          <span class="transform skew-x-6">01</span>
        </div>
        <div class="text-xl text-orange-900 font-semibold">项目背景</div>
        <div class="flex-1"></div>
        <svg class="w-5 h-5 text-orange-300" viewBox="0 0 20 20" fill="currentColor">
          <polygon points="10,2 12.5,7.5 18,8 14,12 15,17.5 10,15 5,17.5 6,12 2,8 7.5,7.5"/>
        </svg>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-6 bg-white rounded-lg px-6 py-4 shadow-sm">
        <div class="w-14 h-14 bg-orange-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0 transform -skew-x-6">
          <span class="transform skew-x-6">02</span>
        </div>
        <div class="text-xl text-orange-900 font-semibold">核心功能</div>
        <div class="flex-1"></div>
        <svg class="w-5 h-5 text-orange-300" viewBox="0 0 20 20" fill="currentColor">
          <polygon points="10,2 12.5,7.5 18,8 14,12 15,17.5 10,15 5,17.5 6,12 2,8 7.5,7.5"/>
        </svg>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-6 bg-white rounded-lg px-6 py-4 shadow-sm">
        <div class="w-14 h-14 bg-orange-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0 transform -skew-x-6">
          <span class="transform skew-x-6">03</span>
        </div>
        <div class="text-xl text-orange-900 font-semibold">技术架构</div>
        <div class="flex-1"></div>
        <svg class="w-5 h-5 text-orange-300" viewBox="0 0 20 20" fill="currentColor">
          <polygon points="10,2 12.5,7.5 18,8 14,12 15,17.5 10,15 5,17.5 6,12 2,8 7.5,7.5"/>
        </svg>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-6 bg-white rounded-lg px-6 py-4 shadow-sm">
        <div class="w-14 h-14 bg-orange-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0 transform -skew-x-6">
          <span class="transform skew-x-6">04</span>
        </div>
        <div class="text-xl text-orange-900 font-semibold">实施计划</div>
        <div class="flex-1"></div>
        <svg class="w-5 h-5 text-orange-300" viewBox="0 0 20 20" fill="currentColor">
          <polygon points="10,2 12.5,7.5 18,8 14,12 15,17.5 10,15 5,17.5 6,12 2,8 7.5,7.5"/>
        </svg>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-6 bg-white rounded-lg px-6 py-4 shadow-sm">
        <div class="w-14 h-14 bg-orange-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0 transform -skew-x-6">
          <span class="transform skew-x-6">05</span>
        </div>
        <div class="text-xl text-orange-900 font-semibold">预算分析</div>
        <div class="flex-1"></div>
        <svg class="w-5 h-5 text-orange-300" viewBox="0 0 20 20" fill="currentColor">
          <polygon points="10,2 12.5,7.5 18,8 14,12 15,17.5 10,15 5,17.5 6,12 2,8 7.5,7.5"/>
        </svg>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-6 bg-white rounded-lg px-6 py-4 shadow-sm">
        <div class="w-14 h-14 bg-orange-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0 transform -skew-x-6">
          <span class="transform skew-x-6">06</span>
        </div>
        <div class="text-xl text-orange-900 font-semibold">总结展望</div>
        <div class="flex-1"></div>
        <svg class="w-5 h-5 text-orange-300" viewBox="0 0 20 20" fill="currentColor">
          <polygon points="10,2 12.5,7.5 18,8 14,12 15,17.5 10,15 5,17.5 6,12 2,8 7.5,7.5"/>
        </svg>
      </div>
    </div>
  </div>
</div>
