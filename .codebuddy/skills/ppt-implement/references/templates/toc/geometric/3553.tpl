<!-- 
模板ID: 3553
模板名称: 几何风-六边形蜂巢
适用场景: 抽象几何风目录页
设计特点: SVG六边形蜂巢底纹+六边形编号+纵向列表,深靛蓝纯色,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-indigo-950 flex relative overflow-hidden">
  <!-- 背景六边形蜂巢 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <defs>
      <pattern id="hex3553" x="0" y="0" width="80" height="92" patternUnits="userSpaceOnUse">
        <path d="M40 0 L72 16 L72 48 L40 64 L8 48 L8 16Z" fill="none" stroke="#312e81" stroke-width="1"/>
        <path d="M80 46 L112 62 L112 94 L80 110 L48 94 L48 62Z" fill="none" stroke="#312e81" stroke-width="1"/>
      </pattern>
    </defs>
    <rect width="1440" height="810" fill="url(#hex3553)" opacity="0.5"/>
    <!-- 高亮六边形 -->
    <path d="M1200 100 L1240 120 L1240 160 L1200 180 L1160 160 L1160 120Z" fill="#4f46e5" opacity="0.15"/>
    <path d="M200 600 L240 620 L240 660 L200 680 L160 660 L160 620Z" fill="#4f46e5" opacity="0.1"/>
    <path d="M100 200 L130 215 L130 245 L100 260 L70 245 L70 215Z" fill="#6366f1" opacity="0.08"/>
  </svg>

  <!-- 左侧标题区 -->
  <div class="w-[380px] h-full flex flex-col justify-center items-center relative z-10">
    <div class="text-center">
      <svg class="w-16 h-16 mx-auto mb-6" viewBox="0 0 64 64">
        <path d="M32 4 L58 18 L58 46 L32 60 L6 46 L6 18Z" fill="#4f46e5"/>
        <path d="M32 16 L46 24 L46 40 L32 48 L18 40 L18 24Z" fill="#312e81"/>
      </svg>
      <h2 class="text-5xl font-bold text-white tracking-widest mb-3">目录</h2>
      <p class="text-indigo-300 text-sm tracking-[0.3em] uppercase">Contents</p>
      <div class="w-20 h-px bg-indigo-500 mx-auto mt-6"></div>
    </div>
  </div>

  <!-- 右侧内容区 -->
  <div class="flex-1 flex flex-col justify-center pr-16 pl-6 relative z-10">
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-indigo-900 bg-opacity-60 rounded-lg">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L42 14 L42 34 L24 44 L6 34 L6 14Z" fill="#4f46e5"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="14">01</text>
        </svg>
        <div class="text-xl text-white font-medium">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-indigo-900 bg-opacity-60 rounded-lg">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L42 14 L42 34 L24 44 L6 34 L6 14Z" fill="#4f46e5"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="14">02</text>
        </svg>
        <div class="text-xl text-white font-medium">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-indigo-900 bg-opacity-60 rounded-lg">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L42 14 L42 34 L24 44 L6 34 L6 14Z" fill="#4f46e5"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="14">03</text>
        </svg>
        <div class="text-xl text-white font-medium">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-indigo-900 bg-opacity-60 rounded-lg">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L42 14 L42 34 L24 44 L6 34 L6 14Z" fill="#4f46e5"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="14">04</text>
        </svg>
        <div class="text-xl text-white font-medium">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-indigo-900 bg-opacity-60 rounded-lg">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L42 14 L42 34 L24 44 L6 34 L6 14Z" fill="#4f46e5"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="14">05</text>
        </svg>
        <div class="text-xl text-white font-medium">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-indigo-900 bg-opacity-60 rounded-lg">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L42 14 L42 34 L24 44 L6 34 L6 14Z" fill="#4f46e5"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="14">06</text>
        </svg>
        <div class="text-xl text-white font-medium">总结展望</div>
      </div>
    </div>
  </div>
</div>
