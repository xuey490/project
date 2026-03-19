<!-- 
模板ID: 3546
模板名称: 商务风-杂志排版
适用场景: 简洁商务目录页
设计特点: 杂志式大字体排版,留白优雅,极简专业,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-white flex items-center justify-center relative overflow-hidden">
  <!-- 背景装饰 -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 右侧色块 -->
    <rect x="1300" y="0" width="140" height="810" fill="#f8fafc"/>
    <!-- 顶部细线 -->
    <line x1="80" y1="60" x2="1360" y2="60" stroke="#e2e8f0" stroke-width="1"/>
    <!-- 底部细线 -->
    <line x1="80" y1="750" x2="1360" y2="750" stroke="#e2e8f0" stroke-width="1"/>
  </svg>

  <div class="relative z-10 w-[1200px]">
    <!-- 标题区 -->
    <div class="flex items-baseline gap-6 mb-16">
      <h2 class="text-7xl font-black text-slate-900 leading-none">CONTENTS</h2>
      <div class="w-3 h-3 bg-red-500 rounded-full"></div>
    </div>

    <!-- 条目列表 —— 杂志式大字排版 -->
    <div class="space-y-0">
      <!-- 条目1 -->
      <div class="flex items-baseline py-5 group">
        <span class="text-5xl font-black text-slate-200 w-28 flex-shrink-0 leading-none">01</span>
        <div class="flex-1">
          <div class="text-2xl text-slate-800 font-semibold">项目背景</div>
        </div>
        <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
      </div>
      <div class="h-px bg-slate-100"></div>

      <!-- 条目2 -->
      <div class="flex items-baseline py-5 group">
        <span class="text-5xl font-black text-slate-200 w-28 flex-shrink-0 leading-none">02</span>
        <div class="flex-1">
          <div class="text-2xl text-slate-800 font-semibold">核心功能</div>
        </div>
        <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
      </div>
      <div class="h-px bg-slate-100"></div>

      <!-- 条目3 -->
      <div class="flex items-baseline py-5 group">
        <span class="text-5xl font-black text-slate-200 w-28 flex-shrink-0 leading-none">03</span>
        <div class="flex-1">
          <div class="text-2xl text-slate-800 font-semibold">技术架构</div>
        </div>
        <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
      </div>
      <div class="h-px bg-slate-100"></div>

      <!-- 条目4 -->
      <div class="flex items-baseline py-5 group">
        <span class="text-5xl font-black text-slate-200 w-28 flex-shrink-0 leading-none">04</span>
        <div class="flex-1">
          <div class="text-2xl text-slate-800 font-semibold">实施计划</div>
        </div>
        <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
      </div>
      <div class="h-px bg-slate-100"></div>

      <!-- 条目5 -->
      <div class="flex items-baseline py-5 group">
        <span class="text-5xl font-black text-slate-200 w-28 flex-shrink-0 leading-none">05</span>
        <div class="flex-1">
          <div class="text-2xl text-slate-800 font-semibold">预算分析</div>
        </div>
        <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
      </div>
      <div class="h-px bg-slate-100"></div>

      <!-- 条目6 -->
      <div class="flex items-baseline py-5 group">
        <span class="text-5xl font-black text-slate-200 w-28 flex-shrink-0 leading-none">06</span>
        <div class="flex-1">
          <div class="text-2xl text-slate-800 font-semibold">总结展望</div>
        </div>
        <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
      </div>
    </div>
  </div>

  <!-- 右侧竖向文字 -->
  <div class="absolute right-[1320px] top-1/2 -translate-y-1/2 -rotate-90 origin-center">
    <span class="text-xs text-slate-300 tracking-[0.5em] uppercase whitespace-nowrap">Table of Contents</span>
  </div>
</div>
