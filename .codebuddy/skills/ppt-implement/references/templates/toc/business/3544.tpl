<!-- 
模板ID: 3544
模板名称: 商务风-数据仪表盘
适用场景: 专业商务目录页
设计特点: 仪表盘风格编号+两列布局,数据驱动感,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-white flex items-center justify-center relative overflow-hidden">
  <!-- 背景装饰SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 右上角大圆弧 -->
    <path d="M1440 0 Q1440 300 1140 300 L1440 300 Z" fill="#f1f5f9"/>
    <!-- 左下角大圆弧 -->
    <path d="M0 810 Q0 510 300 510 L0 510 Z" fill="#f1f5f9"/>
    <!-- 网格点装饰 -->
    <g fill="#cbd5e1" opacity="0.3">
      <circle cx="100" cy="100" r="2"/><circle cx="140" cy="100" r="2"/><circle cx="180" cy="100" r="2"/><circle cx="220" cy="100" r="2"/>
      <circle cx="100" cy="140" r="2"/><circle cx="140" cy="140" r="2"/><circle cx="180" cy="140" r="2"/><circle cx="220" cy="140" r="2"/>
      <circle cx="100" cy="180" r="2"/><circle cx="140" cy="180" r="2"/><circle cx="180" cy="180" r="2"/><circle cx="220" cy="180" r="2"/>
      <circle cx="1220" cy="630" r="2"/><circle cx="1260" cy="630" r="2"/><circle cx="1300" cy="630" r="2"/><circle cx="1340" cy="630" r="2"/>
      <circle cx="1220" cy="670" r="2"/><circle cx="1260" cy="670" r="2"/><circle cx="1300" cy="670" r="2"/><circle cx="1340" cy="670" r="2"/>
      <circle cx="1220" cy="710" r="2"/><circle cx="1260" cy="710" r="2"/><circle cx="1300" cy="710" r="2"/><circle cx="1340" cy="710" r="2"/>
    </g>
  </svg>

  <div class="relative z-10 w-[1280px]">
    <!-- 标题区 -->
    <div class="flex items-center gap-5 mb-14">
      <div class="w-2 h-12 bg-emerald-600 rounded-full"></div>
      <h2 class="text-4xl font-bold text-slate-800 tracking-wide">目录</h2>
      <div class="flex-1 h-px bg-slate-200"></div>
      <span class="text-sm text-slate-400 tracking-widest uppercase">Contents</span>
    </div>

    <!-- 两列布局 -->
    <div class="grid grid-cols-2 gap-x-16 gap-y-8">
      <!-- 条目1 -->
      <div class="flex items-start gap-5">
        <div class="w-[60px] h-[60px] bg-emerald-600 text-white rounded-lg flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
          01
        </div>
        <div class="flex-1 pt-1">
          <div class="text-lg text-slate-800 font-semibold mb-1">项目背景</div>
          <div class="w-full h-[3px] bg-emerald-100 rounded-full">
            <div class="w-4/5 h-full bg-emerald-500 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-start gap-5">
        <div class="w-[60px] h-[60px] bg-emerald-600 text-white rounded-lg flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
          02
        </div>
        <div class="flex-1 pt-1">
          <div class="text-lg text-slate-800 font-semibold mb-1">核心功能</div>
          <div class="w-full h-[3px] bg-emerald-100 rounded-full">
            <div class="w-3/5 h-full bg-emerald-500 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-start gap-5">
        <div class="w-[60px] h-[60px] bg-emerald-600 text-white rounded-lg flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
          03
        </div>
        <div class="flex-1 pt-1">
          <div class="text-lg text-slate-800 font-semibold mb-1">技术架构</div>
          <div class="w-full h-[3px] bg-emerald-100 rounded-full">
            <div class="w-2/3 h-full bg-emerald-500 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-start gap-5">
        <div class="w-[60px] h-[60px] bg-emerald-600 text-white rounded-lg flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
          04
        </div>
        <div class="flex-1 pt-1">
          <div class="text-lg text-slate-800 font-semibold mb-1">实施计划</div>
          <div class="w-full h-[3px] bg-emerald-100 rounded-full">
            <div class="w-1/2 h-full bg-emerald-500 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-start gap-5">
        <div class="w-[60px] h-[60px] bg-emerald-600 text-white rounded-lg flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
          05
        </div>
        <div class="flex-1 pt-1">
          <div class="text-lg text-slate-800 font-semibold mb-1">预算分析</div>
          <div class="w-full h-[3px] bg-emerald-100 rounded-full">
            <div class="w-2/5 h-full bg-emerald-500 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-start gap-5">
        <div class="w-[60px] h-[60px] bg-emerald-600 text-white rounded-lg flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
          06
        </div>
        <div class="flex-1 pt-1">
          <div class="text-lg text-slate-800 font-semibold mb-1">总结展望</div>
          <div class="w-full h-[3px] bg-emerald-100 rounded-full">
            <div class="w-1/4 h-full bg-emerald-500 rounded-full"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 右下角装饰图标 -->
  <svg class="absolute bottom-8 right-10 w-10 h-10 text-emerald-200" viewBox="0 0 40 40" fill="currentColor">
    <rect x="0" y="22" width="8" height="18" rx="2"/>
    <rect x="11" y="14" width="8" height="26" rx="2"/>
    <rect x="22" y="8" width="8" height="32" rx="2"/>
    <rect x="33" y="2" width="7" height="38" rx="2"/>
  </svg>
</div>
