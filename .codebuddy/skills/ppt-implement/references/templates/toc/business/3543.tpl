<!-- 
模板ID: 3543
模板名称: 商务风-左侧色块导航
适用场景: 商务演示目录页
设计特点: 左侧深色导航面板+右侧内容区,专业简洁,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-slate-50 flex relative overflow-hidden">
  <!-- 左侧深色面板 -->
  <div class="w-[420px] h-full bg-slate-800 flex flex-col justify-center items-center relative">
    <!-- 装饰性SVG几何图形 -->
    <svg class="absolute top-0 right-0 w-full h-full opacity-[0.04]" viewBox="0 0 420 810">
      <circle cx="350" cy="120" r="180" fill="white"/>
      <circle cx="70" cy="650" r="140" fill="white"/>
      <rect x="300" y="500" width="200" height="200" rx="20" fill="white" transform="rotate(25 400 600)"/>
    </svg>
    <!-- 标题区 -->
    <div class="relative z-10 text-center px-10">
      <div class="w-16 h-1 bg-blue-500 mx-auto mb-8"></div>
      <h2 class="text-5xl font-bold text-white tracking-wider mb-4">目录</h2>
      <p class="text-slate-400 text-base tracking-widest uppercase">Contents</p>
      <div class="w-16 h-1 bg-blue-500 mx-auto mt-8"></div>
    </div>
    <!-- 底部装饰 -->
    <div class="absolute bottom-10 left-10 right-10 flex items-center gap-3">
      <svg class="w-5 h-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      <div class="flex-1 h-px bg-slate-600"></div>
    </div>
  </div>

  <!-- 右侧内容区 -->
  <div class="flex-1 flex flex-col justify-center px-16 py-12">
    <!-- 条目列表 -->
    <div class="space-y-4">
      <!-- 条目1 -->
      <div class="flex items-center gap-6 group">
        <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 rounded">
          01
        </div>
        <div class="flex-1 py-4">
          <div class="text-xl text-slate-800 font-semibold">项目背景</div>
        </div>
        <svg class="w-5 h-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </div>
      <div class="h-px bg-slate-200 ml-20"></div>

      <!-- 条目2 -->
      <div class="flex items-center gap-6 group">
        <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 rounded">
          02
        </div>
        <div class="flex-1 py-4">
          <div class="text-xl text-slate-800 font-semibold">核心功能</div>
        </div>
        <svg class="w-5 h-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </div>
      <div class="h-px bg-slate-200 ml-20"></div>

      <!-- 条目3 -->
      <div class="flex items-center gap-6 group">
        <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 rounded">
          03
        </div>
        <div class="flex-1 py-4">
          <div class="text-xl text-slate-800 font-semibold">技术架构</div>
        </div>
        <svg class="w-5 h-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </div>
      <div class="h-px bg-slate-200 ml-20"></div>

      <!-- 条目4 -->
      <div class="flex items-center gap-6 group">
        <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 rounded">
          04
        </div>
        <div class="flex-1 py-4">
          <div class="text-xl text-slate-800 font-semibold">实施计划</div>
        </div>
        <svg class="w-5 h-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </div>
      <div class="h-px bg-slate-200 ml-20"></div>

      <!-- 条目5 -->
      <div class="flex items-center gap-6 group">
        <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 rounded">
          05
        </div>
        <div class="flex-1 py-4">
          <div class="text-xl text-slate-800 font-semibold">预算分析</div>
        </div>
        <svg class="w-5 h-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </div>
      <div class="h-px bg-slate-200 ml-20"></div>

      <!-- 条目6 -->
      <div class="flex items-center gap-6 group">
        <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 rounded">
          06
        </div>
        <div class="flex-1 py-4">
          <div class="text-xl text-slate-800 font-semibold">总结展望</div>
        </div>
        <svg class="w-5 h-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- 右上角装饰 -->
  <svg class="absolute top-6 right-8 w-8 h-8 text-blue-600 opacity-30" viewBox="0 0 32 32" fill="currentColor">
    <rect x="0" y="0" width="14" height="14" rx="2"/>
    <rect x="18" y="0" width="14" height="14" rx="2"/>
    <rect x="0" y="18" width="14" height="14" rx="2"/>
    <rect x="18" y="18" width="14" height="14" rx="2"/>
  </svg>
</div>
