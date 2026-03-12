<!-- 
模板ID: 3549
模板名称: 商务风-暗色高端
适用场景: 高端商务演示目录页
设计特点: 深色背景+金色点缀+左右分栏,高级沉稳,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-gray-900 flex relative overflow-hidden">
  <!-- 背景装饰 -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 钻石纹理 -->
    <pattern id="p3549" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
      <path d="M30 0 L60 30 L30 60 L0 30Z" fill="none" stroke="#374151" stroke-width="0.5"/>
    </pattern>
    <rect x="0" y="0" width="1440" height="810" fill="url(#p3549)" opacity="0.3"/>
    <!-- 顶部金线 -->
    <line x1="0" y1="0" x2="1440" y2="0" stroke="#d97706" stroke-width="3"/>
    <!-- 底部金线 -->
    <line x1="0" y1="807" x2="1440" y2="807" stroke="#d97706" stroke-width="3"/>
  </svg>

  <!-- 左侧标题区 -->
  <div class="w-[400px] h-full flex flex-col justify-center items-center relative z-10">
    <div class="text-center">
      <!-- 金色菱形装饰 -->
      <svg class="w-8 h-8 mx-auto mb-6 text-amber-500" viewBox="0 0 32 32" fill="currentColor">
        <path d="M16 2 L30 16 L16 30 L2 16Z"/>
      </svg>
      <h2 class="text-5xl font-bold text-white tracking-widest mb-3">目录</h2>
      <p class="text-amber-400 text-xs tracking-[0.5em] uppercase">Contents</p>
      <svg class="w-8 h-8 mx-auto mt-6 text-amber-500" viewBox="0 0 32 32" fill="currentColor">
        <path d="M16 2 L30 16 L16 30 L2 16Z"/>
      </svg>
    </div>
  </div>

  <!-- 右侧内容区 -->
  <div class="flex-1 flex flex-col justify-center pr-16 pl-8 relative z-10">
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-md bg-gray-800 bg-opacity-70">
        <div class="w-12 h-12 bg-amber-600 text-gray-900 flex items-center justify-center text-lg font-bold flex-shrink-0">
          01
        </div>
        <div class="w-8 h-px bg-amber-600 opacity-40"></div>
        <div class="text-xl text-gray-100 font-medium">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-md bg-gray-800 bg-opacity-70">
        <div class="w-12 h-12 bg-amber-600 text-gray-900 flex items-center justify-center text-lg font-bold flex-shrink-0">
          02
        </div>
        <div class="w-8 h-px bg-amber-600 opacity-40"></div>
        <div class="text-xl text-gray-100 font-medium">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-md bg-gray-800 bg-opacity-70">
        <div class="w-12 h-12 bg-amber-600 text-gray-900 flex items-center justify-center text-lg font-bold flex-shrink-0">
          03
        </div>
        <div class="w-8 h-px bg-amber-600 opacity-40"></div>
        <div class="text-xl text-gray-100 font-medium">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-md bg-gray-800 bg-opacity-70">
        <div class="w-12 h-12 bg-amber-600 text-gray-900 flex items-center justify-center text-lg font-bold flex-shrink-0">
          04
        </div>
        <div class="w-8 h-px bg-amber-600 opacity-40"></div>
        <div class="text-xl text-gray-100 font-medium">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-md bg-gray-800 bg-opacity-70">
        <div class="w-12 h-12 bg-amber-600 text-gray-900 flex items-center justify-center text-lg font-bold flex-shrink-0">
          05
        </div>
        <div class="w-8 h-px bg-amber-600 opacity-40"></div>
        <div class="text-xl text-gray-100 font-medium">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-md bg-gray-800 bg-opacity-70">
        <div class="w-12 h-12 bg-amber-600 text-gray-900 flex items-center justify-center text-lg font-bold flex-shrink-0">
          06
        </div>
        <div class="w-8 h-px bg-amber-600 opacity-40"></div>
        <div class="text-xl text-gray-100 font-medium">总结展望</div>
      </div>
    </div>
  </div>
</div>
