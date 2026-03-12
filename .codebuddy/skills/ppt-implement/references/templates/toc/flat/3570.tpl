<!-- 
模板ID: 3570
模板名称: 扁平风-双色分屏
适用场景: 对比扁平风目录页
设计特点: 左深蓝右浅灰双色分屏,跨区域条目,纯色扁平
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden">
  <!-- 左侧深色区 -->
  <div class="absolute top-0 left-0 w-[480px] h-full" style="background-color: #1e3a5f;"></div>
  <!-- 右侧浅色区 -->
  <div class="absolute top-0 left-[480px] right-0 h-full" style="background-color: #f1f5f9;"></div>

  <!-- 标题（在深色区） -->
  <div class="absolute top-12 left-16 z-10">
    <h2 class="text-5xl font-black text-white">目录</h2>
    <p class="mt-2 text-xs font-medium tracking-[6px] uppercase" style="color: rgba(255,255,255,0.4);">Contents</p>
    <div class="mt-4 flex gap-2">
      <div class="w-8 h-1.5 rounded-full bg-white" style="opacity: 0.5;"></div>
      <div class="w-4 h-1.5 rounded-full bg-white" style="opacity: 0.3;"></div>
    </div>
  </div>

  <!-- 条目列表（跨越两个区域） -->
  <div class="absolute top-[160px] left-0 right-0 bottom-12 flex flex-col justify-center px-16 gap-3 z-10">
    <!-- 条目1 -->
    <div class="flex items-center">
      <div class="w-[400px] flex justify-end pr-8 flex-shrink-0">
        <span class="text-4xl font-black" style="color: rgba(255,255,255,0.12);">01</span>
      </div>
      <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold" style="background-color: #0ea5e9;">1</div>
      <span class="ml-6 text-lg font-semibold text-gray-800">项目背景</span>
    </div>
    <!-- 条目2 -->
    <div class="flex items-center">
      <div class="w-[400px] flex justify-end pr-8 flex-shrink-0">
        <span class="text-4xl font-black" style="color: rgba(255,255,255,0.12);">02</span>
      </div>
      <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold" style="background-color: #8b5cf6;">2</div>
      <span class="ml-6 text-lg font-semibold text-gray-800">核心功能</span>
    </div>
    <!-- 条目3 -->
    <div class="flex items-center">
      <div class="w-[400px] flex justify-end pr-8 flex-shrink-0">
        <span class="text-4xl font-black" style="color: rgba(255,255,255,0.12);">03</span>
      </div>
      <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold" style="background-color: #f43f5e;">3</div>
      <span class="ml-6 text-lg font-semibold text-gray-800">技术架构</span>
    </div>
    <!-- 条目4 -->
    <div class="flex items-center">
      <div class="w-[400px] flex justify-end pr-8 flex-shrink-0">
        <span class="text-4xl font-black" style="color: rgba(255,255,255,0.12);">04</span>
      </div>
      <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold" style="background-color: #10b981;">4</div>
      <span class="ml-6 text-lg font-semibold text-gray-800">实施计划</span>
    </div>
    <!-- 条目5 -->
    <div class="flex items-center">
      <div class="w-[400px] flex justify-end pr-8 flex-shrink-0">
        <span class="text-4xl font-black" style="color: rgba(255,255,255,0.12);">05</span>
      </div>
      <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold" style="background-color: #f59e0b;">5</div>
      <span class="ml-6 text-lg font-semibold text-gray-800">成果展示</span>
    </div>
    <!-- 条目6 -->
    <div class="flex items-center">
      <div class="w-[400px] flex justify-end pr-8 flex-shrink-0">
        <span class="text-4xl font-black" style="color: rgba(255,255,255,0.12);">06</span>
      </div>
      <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold" style="background-color: #06b6d4;">6</div>
      <span class="ml-6 text-lg font-semibold text-gray-800">未来展望</span>
    </div>
  </div>
</div>
