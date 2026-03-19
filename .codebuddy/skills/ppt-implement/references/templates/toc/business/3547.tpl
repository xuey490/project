<!-- 
模板ID: 3547
模板名称: 商务风-交替色块
适用场景: 现代商务目录页
设计特点: 交替色块行+左侧色带指示器,视觉层次分明,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-slate-50 flex items-center justify-center relative overflow-hidden">
  <!-- 背景装饰 -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 顶部装饰三角 -->
    <polygon points="1440,0 1440,160 1280,0" fill="#dbeafe" opacity="0.5"/>
    <!-- 底部装饰三角 -->
    <polygon points="0,810 0,650 160,810" fill="#dbeafe" opacity="0.5"/>
    <!-- 圆点阵列 -->
    <g fill="#94a3b8" opacity="0.15">
      <circle cx="1350" cy="700" r="3"/><circle cx="1370" cy="700" r="3"/><circle cx="1390" cy="700" r="3"/>
      <circle cx="1350" cy="720" r="3"/><circle cx="1370" cy="720" r="3"/><circle cx="1390" cy="720" r="3"/>
      <circle cx="1350" cy="740" r="3"/><circle cx="1370" cy="740" r="3"/><circle cx="1390" cy="740" r="3"/>
    </g>
  </svg>

  <div class="relative z-10 w-[1200px]">
    <!-- 标题区 -->
    <div class="mb-12 flex items-center gap-4">
      <svg class="w-8 h-8 text-blue-600" viewBox="0 0 32 32" fill="currentColor">
        <rect x="2" y="4" width="12" height="4" rx="1"/>
        <rect x="2" y="12" width="20" height="4" rx="1"/>
        <rect x="2" y="20" width="16" height="4" rx="1"/>
        <rect x="2" y="28" width="24" height="4" rx="1" opacity="0.5"/>
      </svg>
      <h2 class="text-4xl font-bold text-slate-800">目录</h2>
    </div>

    <!-- 条目列表 -->
    <div class="space-y-2">
      <!-- 条目1 -->
      <div class="flex items-center bg-white rounded-lg overflow-hidden shadow-sm">
        <div class="w-2 h-16 bg-blue-600 flex-shrink-0"></div>
        <div class="px-6 py-4 flex items-center gap-5 flex-1">
          <span class="text-3xl font-bold text-blue-600 w-14 flex-shrink-0">01</span>
          <div class="text-xl text-slate-700 font-medium">项目背景</div>
        </div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center bg-blue-50 rounded-lg overflow-hidden">
        <div class="w-2 h-16 bg-blue-600 flex-shrink-0"></div>
        <div class="px-6 py-4 flex items-center gap-5 flex-1">
          <span class="text-3xl font-bold text-blue-600 w-14 flex-shrink-0">02</span>
          <div class="text-xl text-slate-700 font-medium">核心功能</div>
        </div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center bg-white rounded-lg overflow-hidden shadow-sm">
        <div class="w-2 h-16 bg-blue-600 flex-shrink-0"></div>
        <div class="px-6 py-4 flex items-center gap-5 flex-1">
          <span class="text-3xl font-bold text-blue-600 w-14 flex-shrink-0">03</span>
          <div class="text-xl text-slate-700 font-medium">技术架构</div>
        </div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center bg-blue-50 rounded-lg overflow-hidden">
        <div class="w-2 h-16 bg-blue-600 flex-shrink-0"></div>
        <div class="px-6 py-4 flex items-center gap-5 flex-1">
          <span class="text-3xl font-bold text-blue-600 w-14 flex-shrink-0">04</span>
          <div class="text-xl text-slate-700 font-medium">实施计划</div>
        </div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center bg-white rounded-lg overflow-hidden shadow-sm">
        <div class="w-2 h-16 bg-blue-600 flex-shrink-0"></div>
        <div class="px-6 py-4 flex items-center gap-5 flex-1">
          <span class="text-3xl font-bold text-blue-600 w-14 flex-shrink-0">05</span>
          <div class="text-xl text-slate-700 font-medium">预算分析</div>
        </div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center bg-blue-50 rounded-lg overflow-hidden">
        <div class="w-2 h-16 bg-blue-600 flex-shrink-0"></div>
        <div class="px-6 py-4 flex items-center gap-5 flex-1">
          <span class="text-3xl font-bold text-blue-600 w-14 flex-shrink-0">06</span>
          <div class="text-xl text-slate-700 font-medium">总结展望</div>
        </div>
      </div>
    </div>
  </div>
</div>
