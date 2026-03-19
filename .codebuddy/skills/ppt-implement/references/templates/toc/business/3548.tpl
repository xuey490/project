<!-- 
模板ID: 3548
模板名称: 商务风-圆形徽章编号
适用场景: 友好专业的商务目录页
设计特点: 圆形徽章编号+横排两列紧凑卡片,亲和专业,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-stone-50 flex items-center justify-center relative overflow-hidden">
  <!-- 背景装饰SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 左上角弧线 -->
    <path d="M0 0 Q250 200 0 400" fill="none" stroke="#d6d3d1" stroke-width="1" opacity="0.5"/>
    <!-- 右下角弧线 -->
    <path d="M1440 810 Q1190 610 1440 410" fill="none" stroke="#d6d3d1" stroke-width="1" opacity="0.5"/>
    <!-- 散落的小方块 -->
    <rect x="1300" y="80" width="12" height="12" rx="2" fill="#a8a29e" opacity="0.15" transform="rotate(20 1306 86)"/>
    <rect x="1340" y="120" width="8" height="8" rx="1" fill="#a8a29e" opacity="0.1" transform="rotate(45 1344 124)"/>
    <rect x="100" y="700" width="10" height="10" rx="2" fill="#a8a29e" opacity="0.12" transform="rotate(30 105 705)"/>
  </svg>

  <div class="relative z-10 w-[1200px]">
    <!-- 标题区 -->
    <div class="text-center mb-14">
      <h2 class="text-4xl font-bold text-stone-800 mb-2">目录</h2>
      <div class="flex items-center justify-center gap-4">
        <div class="w-16 h-px bg-amber-600"></div>
        <svg class="w-4 h-4 text-amber-600" viewBox="0 0 16 16" fill="currentColor">
          <path d="M8 0 L10 6 L16 6 L11 10 L13 16 L8 12 L3 16 L5 10 L0 6 L6 6 Z"/>
        </svg>
        <div class="w-16 h-px bg-amber-600"></div>
      </div>
    </div>

    <!-- 两列布局 -->
    <div class="grid grid-cols-2 gap-6">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-amber-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          01
        </div>
        <div class="text-lg text-stone-700 font-semibold">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-amber-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          02
        </div>
        <div class="text-lg text-stone-700 font-semibold">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-amber-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          03
        </div>
        <div class="text-lg text-stone-700 font-semibold">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-amber-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          04
        </div>
        <div class="text-lg text-stone-700 font-semibold">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-amber-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          05
        </div>
        <div class="text-lg text-stone-700 font-semibold">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-amber-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          06
        </div>
        <div class="text-lg text-stone-700 font-semibold">总结展望</div>
      </div>
    </div>
  </div>
</div>
