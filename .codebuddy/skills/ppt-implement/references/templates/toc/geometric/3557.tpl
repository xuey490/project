<!-- 
模板ID: 3557
模板名称: 几何风-三角拼贴
适用场景: 规整几何风目录页
设计特点: SVG三角拼贴底纹+三角形编号+两列布局,翠绿纯色,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-emerald-50 flex items-center justify-center relative overflow-hidden">
  <!-- 背景三角拼贴 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 左上三角群 -->
    <g opacity="0.06">
      <polygon points="0,0 120,0 60,100" fill="#059669"/>
      <polygon points="120,0 240,0 180,100" fill="#10b981"/>
      <polygon points="60,100 120,0 180,100" fill="#047857"/>
      <polygon points="0,0 60,100 0,200" fill="#10b981"/>
    </g>
    <!-- 右下三角群 -->
    <g opacity="0.05">
      <polygon points="1320,810 1440,810 1380,710" fill="#059669"/>
      <polygon points="1200,810 1320,810 1260,710" fill="#10b981"/>
      <polygon points="1260,710 1320,810 1380,710" fill="#047857"/>
      <polygon points="1380,710 1440,810 1440,610" fill="#10b981"/>
    </g>
    <!-- 散落小三角 -->
    <polygon points="1100,80 1120,40 1140,80" fill="#059669" opacity="0.12"/>
    <polygon points="300,680 320,640 340,680" fill="#10b981" opacity="0.1"/>
    <polygon points="700,30 710,10 720,30" fill="#059669" opacity="0.08"/>
  </svg>

  <div class="relative z-10 w-[1200px]">
    <!-- 标题区 -->
    <div class="text-center mb-14">
      <div class="inline-flex items-center gap-4">
        <svg class="w-6 h-6" viewBox="0 0 24 24">
          <polygon points="12,2 22,20 2,20" fill="#059669"/>
        </svg>
        <h2 class="text-4xl font-bold text-emerald-900 tracking-wide">目录</h2>
        <svg class="w-6 h-6" viewBox="0 0 24 24">
          <polygon points="12,22 22,4 2,4" fill="#059669"/>
        </svg>
      </div>
      <p class="text-sm text-emerald-400 mt-2 tracking-widest uppercase">Contents</p>
    </div>

    <!-- 两列布局 -->
    <div class="grid grid-cols-2 gap-x-10 gap-y-5">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 bg-white rounded-lg px-6 py-5 shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <polygon points="24,4 44,40 4,40" fill="#059669"/>
          <text x="24" y="34" text-anchor="middle" fill="white" font-weight="bold" font-size="14">01</text>
        </svg>
        <div class="text-lg text-emerald-900 font-semibold">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 bg-white rounded-lg px-6 py-5 shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <polygon points="24,4 44,40 4,40" fill="#059669"/>
          <text x="24" y="34" text-anchor="middle" fill="white" font-weight="bold" font-size="14">02</text>
        </svg>
        <div class="text-lg text-emerald-900 font-semibold">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 bg-white rounded-lg px-6 py-5 shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <polygon points="24,4 44,40 4,40" fill="#059669"/>
          <text x="24" y="34" text-anchor="middle" fill="white" font-weight="bold" font-size="14">03</text>
        </svg>
        <div class="text-lg text-emerald-900 font-semibold">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 bg-white rounded-lg px-6 py-5 shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <polygon points="24,4 44,40 4,40" fill="#059669"/>
          <text x="24" y="34" text-anchor="middle" fill="white" font-weight="bold" font-size="14">04</text>
        </svg>
        <div class="text-lg text-emerald-900 font-semibold">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 bg-white rounded-lg px-6 py-5 shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <polygon points="24,4 44,40 4,40" fill="#059669"/>
          <text x="24" y="34" text-anchor="middle" fill="white" font-weight="bold" font-size="14">05</text>
        </svg>
        <div class="text-lg text-emerald-900 font-semibold">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 bg-white rounded-lg px-6 py-5 shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <polygon points="24,4 44,40 4,40" fill="#059669"/>
          <text x="24" y="34" text-anchor="middle" fill="white" font-weight="bold" font-size="14">06</text>
        </svg>
        <div class="text-lg text-emerald-900 font-semibold">总结展望</div>
      </div>
    </div>
  </div>
</div>
