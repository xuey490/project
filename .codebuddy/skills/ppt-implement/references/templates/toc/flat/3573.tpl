<!-- 
模板ID: 3573
模板名称: 扁平风-彩色标签
适用场景: 多彩扁平风目录页
设计特点: 顶部多彩色块标识+纵向列表,每条目对应色块,扁平纯色
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden bg-white">
  <!-- 顶部色块标识条 -->
  <div class="absolute top-0 left-0 w-full h-[100px] flex">
    <div class="flex-1 flex items-end justify-center pb-3" style="background-color: #ef4444;">
      <span class="text-xl font-black text-white">01</span>
    </div>
    <div class="flex-1 flex items-end justify-center pb-3" style="background-color: #f59e0b;">
      <span class="text-xl font-black text-white">02</span>
    </div>
    <div class="flex-1 flex items-end justify-center pb-3" style="background-color: #10b981;">
      <span class="text-xl font-black text-white">03</span>
    </div>
    <div class="flex-1 flex items-end justify-center pb-3" style="background-color: #3b82f6;">
      <span class="text-xl font-black text-white">04</span>
    </div>
    <div class="flex-1 flex items-end justify-center pb-3" style="background-color: #8b5cf6;">
      <span class="text-xl font-black text-white">05</span>
    </div>
    <div class="flex-1 flex items-end justify-center pb-3" style="background-color: #ec4899;">
      <span class="text-xl font-black text-white">06</span>
    </div>
  </div>

  <!-- 主体内容 -->
  <div class="absolute top-[100px] left-0 right-0 bottom-0 flex flex-col justify-center px-20">
    <!-- 标题 -->
    <div class="mb-8 text-center">
      <h2 class="text-4xl font-black text-gray-900">目录</h2>
      <p class="mt-1 text-xs font-medium tracking-[6px] text-gray-400 uppercase">Contents</p>
    </div>

    <!-- 条目列表（两列） -->
    <div class="grid grid-cols-2 gap-x-16 gap-y-4 max-w-[1000px] mx-auto w-full">
      <!-- 条目1 -->
      <div class="flex items-center gap-4 py-4">
        <div class="w-3 h-10 rounded-sm flex-shrink-0" style="background-color: #ef4444;"></div>
        <div>
          <span class="text-xs font-bold" style="color: #ef4444;">CHAPTER 01</span>
          <p class="text-lg font-semibold text-gray-800 mt-0.5">项目背景</p>
        </div>
      </div>
      <!-- 条目2 -->
      <div class="flex items-center gap-4 py-4">
        <div class="w-3 h-10 rounded-sm flex-shrink-0" style="background-color: #f59e0b;"></div>
        <div>
          <span class="text-xs font-bold" style="color: #f59e0b;">CHAPTER 02</span>
          <p class="text-lg font-semibold text-gray-800 mt-0.5">核心功能</p>
        </div>
      </div>
      <!-- 条目3 -->
      <div class="flex items-center gap-4 py-4">
        <div class="w-3 h-10 rounded-sm flex-shrink-0" style="background-color: #10b981;"></div>
        <div>
          <span class="text-xs font-bold" style="color: #10b981;">CHAPTER 03</span>
          <p class="text-lg font-semibold text-gray-800 mt-0.5">技术架构</p>
        </div>
      </div>
      <!-- 条目4 -->
      <div class="flex items-center gap-4 py-4">
        <div class="w-3 h-10 rounded-sm flex-shrink-0" style="background-color: #3b82f6;"></div>
        <div>
          <span class="text-xs font-bold" style="color: #3b82f6;">CHAPTER 04</span>
          <p class="text-lg font-semibold text-gray-800 mt-0.5">实施计划</p>
        </div>
      </div>
      <!-- 条目5 -->
      <div class="flex items-center gap-4 py-4">
        <div class="w-3 h-10 rounded-sm flex-shrink-0" style="background-color: #8b5cf6;"></div>
        <div>
          <span class="text-xs font-bold" style="color: #8b5cf6;">CHAPTER 05</span>
          <p class="text-lg font-semibold text-gray-800 mt-0.5">成果展示</p>
        </div>
      </div>
      <!-- 条目6 -->
      <div class="flex items-center gap-4 py-4">
        <div class="w-3 h-10 rounded-sm flex-shrink-0" style="background-color: #ec4899;"></div>
        <div>
          <span class="text-xs font-bold" style="color: #ec4899;">CHAPTER 06</span>
          <p class="text-lg font-semibold text-gray-800 mt-0.5">未来展望</p>
        </div>
      </div>
    </div>
  </div>
</div>
