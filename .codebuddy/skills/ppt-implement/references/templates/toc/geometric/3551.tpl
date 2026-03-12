<!-- 
模板ID: 3551
模板名称: 几何风-菱形拼接
适用场景: 现代几何风目录页
设计特点: SVG菱形拼接装饰+左标题右纵列表,紫色调纯色,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-violet-50 flex relative overflow-hidden">
  <!-- 背景菱形拼接 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 左侧菱形群 -->
    <g fill="none" stroke="#c4b5fd" stroke-width="1" opacity="0.4">
      <path d="M60 200 L120 260 L60 320 L0 260Z"/>
      <path d="M120 260 L180 320 L120 380 L60 320Z"/>
      <path d="M0 260 L60 320 L0 380 L-60 320Z"/>
      <path d="M60 320 L120 380 L60 440 L0 380Z"/>
    </g>
    <!-- 右下菱形群 -->
    <g fill="#c4b5fd" opacity="0.08">
      <path d="M1300 550 L1360 610 L1300 670 L1240 610Z"/>
      <path d="M1360 610 L1420 670 L1360 730 L1300 670Z"/>
      <path d="M1240 610 L1300 670 L1240 730 L1180 670Z"/>
    </g>
    <!-- 散落小菱形 -->
    <path d="M1100 100 L1120 120 L1100 140 L1080 120Z" fill="#8b5cf6" opacity="0.15"/>
    <path d="M200 650 L215 665 L200 680 L185 665Z" fill="#8b5cf6" opacity="0.12"/>
    <path d="M700 50 L712 62 L700 74 L688 62Z" fill="#a78bfa" opacity="0.1"/>
  </svg>

  <!-- 左侧标题区 -->
  <div class="w-[400px] h-full flex flex-col justify-center items-center relative z-10">
    <div class="text-center">
      <!-- 菱形装饰 -->
      <svg class="w-10 h-10 mx-auto mb-6" viewBox="0 0 40 40">
        <path d="M20 2 L38 20 L20 38 L2 20Z" fill="#7c3aed"/>
      </svg>
      <h2 class="text-5xl font-bold text-violet-900 tracking-wider mb-3">目录</h2>
      <p class="text-violet-400 text-sm tracking-[0.3em] uppercase">Contents</p>
      <svg class="w-10 h-10 mx-auto mt-6" viewBox="0 0 40 40">
        <path d="M20 2 L38 20 L20 38 L2 20Z" fill="none" stroke="#7c3aed" stroke-width="2"/>
      </svg>
    </div>
  </div>

  <!-- 右侧内容区 -->
  <div class="flex-1 flex flex-col justify-center pr-16 pl-6 relative z-10">
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-white rounded-lg shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L44 24 L24 44 L4 24Z" fill="#7c3aed"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="16">01</text>
        </svg>
        <div class="text-xl text-violet-900 font-semibold">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-white rounded-lg shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L44 24 L24 44 L4 24Z" fill="#7c3aed"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="16">02</text>
        </svg>
        <div class="text-xl text-violet-900 font-semibold">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-white rounded-lg shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L44 24 L24 44 L4 24Z" fill="#7c3aed"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="16">03</text>
        </svg>
        <div class="text-xl text-violet-900 font-semibold">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-white rounded-lg shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L44 24 L24 44 L4 24Z" fill="#7c3aed"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="16">04</text>
        </svg>
        <div class="text-xl text-violet-900 font-semibold">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-white rounded-lg shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L44 24 L24 44 L4 24Z" fill="#7c3aed"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="16">05</text>
        </svg>
        <div class="text-xl text-violet-900 font-semibold">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 py-4 px-6 bg-white rounded-lg shadow-sm">
        <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48">
          <path d="M24 4 L44 24 L24 44 L4 24Z" fill="#7c3aed"/>
          <text x="24" y="28" text-anchor="middle" fill="white" font-weight="bold" font-size="16">06</text>
        </svg>
        <div class="text-xl text-violet-900 font-semibold">总结展望</div>
      </div>
    </div>
  </div>
</div>
