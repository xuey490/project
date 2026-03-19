<!-- 
模板ID: 3550
模板名称: 商务风-图标网格
适用场景: 流程化商务目录页
设计特点: SVG图标+三列紧凑网格,视觉丰富,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-slate-50 flex items-center justify-center relative overflow-hidden">
  <!-- 背景装饰 -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 左上色块 -->
    <rect x="0" y="0" width="80" height="810" fill="#1e40af" opacity="0.04"/>
    <!-- 底部色块 -->
    <rect x="0" y="760" width="1440" height="50" fill="#1e40af" opacity="0.03"/>
    <!-- 十字装饰 -->
    <g stroke="#94a3b8" stroke-width="1" opacity="0.15">
      <line x1="1360" y1="60" x2="1360" y2="90"/>
      <line x1="1345" y1="75" x2="1375" y2="75"/>
      <line x1="80" y1="720" x2="80" y2="750"/>
      <line x1="65" y1="735" x2="95" y2="735"/>
    </g>
  </svg>

  <div class="relative z-10 w-[1240px]">
    <!-- 标题区 -->
    <div class="text-center mb-14">
      <div class="inline-flex items-center gap-4">
        <div class="w-12 h-px bg-indigo-600"></div>
        <h2 class="text-4xl font-bold text-slate-800">目录</h2>
        <div class="w-12 h-px bg-indigo-600"></div>
      </div>
      <p class="text-sm text-slate-400 mt-2 tracking-widest uppercase">Contents</p>
    </div>

    <!-- 三列网格 -->
    <div class="grid grid-cols-3 gap-6">
      <!-- 条目1 -->
      <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col items-center text-center">
        <div class="w-14 h-14 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
          </svg>
        </div>
        <span class="text-xs text-indigo-500 font-bold mb-1">01</span>
        <div class="text-lg text-slate-800 font-semibold">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col items-center text-center">
        <div class="w-14 h-14 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
          </svg>
        </div>
        <span class="text-xs text-indigo-500 font-bold mb-1">02</span>
        <div class="text-lg text-slate-800 font-semibold">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col items-center text-center">
        <div class="w-14 h-14 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-16 0H3"/>
            <path d="M9 7h1m-1 4h1m4-4h1m-1 4h1"/>
          </svg>
        </div>
        <span class="text-xs text-indigo-500 font-bold mb-1">03</span>
        <div class="text-lg text-slate-800 font-semibold">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col items-center text-center">
        <div class="w-14 h-14 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
            <path d="M9 14l2 2 4-4"/>
          </svg>
        </div>
        <span class="text-xs text-indigo-500 font-bold mb-1">04</span>
        <div class="text-lg text-slate-800 font-semibold">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col items-center text-center">
        <div class="w-14 h-14 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/>
            <path d="M12 6v2m0 8v2"/>
            <circle cx="12" cy="12" r="10"/>
          </svg>
        </div>
        <span class="text-xs text-indigo-500 font-bold mb-1">05</span>
        <div class="text-lg text-slate-800 font-semibold">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col items-center text-center">
        <div class="w-14 h-14 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <span class="text-xs text-indigo-500 font-bold mb-1">06</span>
        <div class="text-lg text-slate-800 font-semibold">总结展望</div>
      </div>
    </div>
  </div>
</div>
