<!-- 
模板ID: 3555
模板名称: 几何风-立体方块
适用场景: 立体几何风目录页
设计特点: SVG等距立方体装饰+深色背景+立体方块编号+纵向列表,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-slate-900 flex items-center justify-center relative overflow-hidden">
  <!-- 背景立体方块 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 等距立方体群 - 左上 -->
    <g opacity="0.12">
      <path d="M120 180 L170 155 L220 180 L170 205Z" fill="#06b6d4"/>
      <path d="M120 180 L120 230 L170 255 L170 205Z" fill="#0891b2"/>
      <path d="M170 205 L170 255 L220 230 L220 180Z" fill="#0e7490"/>
    </g>
    <g opacity="0.08">
      <path d="M180 140 L230 115 L280 140 L230 165Z" fill="#8b5cf6"/>
      <path d="M180 140 L180 190 L230 215 L230 165Z" fill="#7c3aed"/>
      <path d="M230 165 L230 215 L280 190 L280 140Z" fill="#6d28d9"/>
    </g>
    <!-- 右下立方体群 -->
    <g opacity="0.1">
      <path d="M1250 580 L1310 550 L1370 580 L1310 610Z" fill="#f59e0b"/>
      <path d="M1250 580 L1250 640 L1310 670 L1310 610Z" fill="#d97706"/>
      <path d="M1310 610 L1310 670 L1370 640 L1370 580Z" fill="#b45309"/>
    </g>
    <g opacity="0.06">
      <path d="M1300 530 L1360 500 L1420 530 L1360 560Z" fill="#06b6d4"/>
      <path d="M1300 530 L1300 590 L1360 620 L1360 560Z" fill="#0891b2"/>
      <path d="M1360 560 L1360 620 L1420 590 L1420 530Z" fill="#0e7490"/>
    </g>
    <!-- 散落小方块 -->
    <rect x="800" y="60" width="15" height="15" fill="#06b6d4" opacity="0.1" transform="rotate(20 808 68)"/>
    <rect x="400" y="700" width="12" height="12" fill="#8b5cf6" opacity="0.08" transform="rotate(35 406 706)"/>
  </svg>

  <div class="relative z-10 w-[1100px]">
    <!-- 标题区 -->
    <div class="text-center mb-14">
      <h2 class="text-5xl font-bold text-white tracking-widest mb-3">目录</h2>
      <div class="flex items-center justify-center gap-3">
        <div class="w-12 h-px bg-cyan-500"></div>
        <div class="w-3 h-3 bg-cyan-500 transform rotate-45"></div>
        <div class="w-12 h-px bg-cyan-500"></div>
      </div>
    </div>

    <!-- 纵向列表 -->
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-6 bg-slate-800 bg-opacity-70 rounded-lg px-6 py-4">
        <div class="relative w-14 h-14 flex-shrink-0">
          <svg class="w-full h-full" viewBox="0 0 56 56">
            <path d="M28 8 L48 20 L48 44 L28 56 L8 44 L8 20Z" fill="#06b6d4"/>
            <text x="28" y="36" text-anchor="middle" fill="white" font-weight="bold" font-size="16">01</text>
          </svg>
        </div>
        <div class="text-xl text-white font-medium">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-6 bg-slate-800 bg-opacity-70 rounded-lg px-6 py-4">
        <div class="relative w-14 h-14 flex-shrink-0">
          <svg class="w-full h-full" viewBox="0 0 56 56">
            <path d="M28 8 L48 20 L48 44 L28 56 L8 44 L8 20Z" fill="#8b5cf6"/>
            <text x="28" y="36" text-anchor="middle" fill="white" font-weight="bold" font-size="16">02</text>
          </svg>
        </div>
        <div class="text-xl text-white font-medium">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-6 bg-slate-800 bg-opacity-70 rounded-lg px-6 py-4">
        <div class="relative w-14 h-14 flex-shrink-0">
          <svg class="w-full h-full" viewBox="0 0 56 56">
            <path d="M28 8 L48 20 L48 44 L28 56 L8 44 L8 20Z" fill="#f59e0b"/>
            <text x="28" y="36" text-anchor="middle" fill="white" font-weight="bold" font-size="16">03</text>
          </svg>
        </div>
        <div class="text-xl text-white font-medium">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-6 bg-slate-800 bg-opacity-70 rounded-lg px-6 py-4">
        <div class="relative w-14 h-14 flex-shrink-0">
          <svg class="w-full h-full" viewBox="0 0 56 56">
            <path d="M28 8 L48 20 L48 44 L28 56 L8 44 L8 20Z" fill="#ef4444"/>
            <text x="28" y="36" text-anchor="middle" fill="white" font-weight="bold" font-size="16">04</text>
          </svg>
        </div>
        <div class="text-xl text-white font-medium">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-6 bg-slate-800 bg-opacity-70 rounded-lg px-6 py-4">
        <div class="relative w-14 h-14 flex-shrink-0">
          <svg class="w-full h-full" viewBox="0 0 56 56">
            <path d="M28 8 L48 20 L48 44 L28 56 L8 44 L8 20Z" fill="#10b981"/>
            <text x="28" y="36" text-anchor="middle" fill="white" font-weight="bold" font-size="16">05</text>
          </svg>
        </div>
        <div class="text-xl text-white font-medium">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-6 bg-slate-800 bg-opacity-70 rounded-lg px-6 py-4">
        <div class="relative w-14 h-14 flex-shrink-0">
          <svg class="w-full h-full" viewBox="0 0 56 56">
            <path d="M28 8 L48 20 L48 44 L28 56 L8 44 L8 20Z" fill="#ec4899"/>
            <text x="28" y="36" text-anchor="middle" fill="white" font-weight="bold" font-size="16">06</text>
          </svg>
        </div>
        <div class="text-xl text-white font-medium">总结展望</div>
      </div>
    </div>
  </div>
</div>
