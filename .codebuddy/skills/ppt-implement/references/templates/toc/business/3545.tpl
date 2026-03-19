<!-- 
模板ID: 3545
模板名称: 商务风-深蓝专业
适用场景: 企业商务演示目录页
设计特点: 深蓝背景+白色内容,左侧大号编号,专业稳重,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-slate-900 flex relative overflow-hidden">
  <!-- 背景装饰 -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 右侧大圆 -->
    <circle cx="1350" cy="405" r="500" fill="#1e293b" opacity="0.6"/>
    <!-- 左下小圆 -->
    <circle cx="80" cy="730" r="120" fill="#1e293b" opacity="0.5"/>
    <!-- 细横线装饰 -->
    <line x1="0" y1="80" x2="200" y2="80" stroke="#334155" stroke-width="1"/>
    <line x1="1240" y1="730" x2="1440" y2="730" stroke="#334155" stroke-width="1"/>
  </svg>

  <!-- 左侧标题区 -->
  <div class="w-[380px] h-full flex flex-col justify-center items-center relative z-10 px-10">
    <div class="text-center">
      <div class="w-20 h-px bg-blue-400 mx-auto mb-8"></div>
      <h2 class="text-5xl font-bold text-white tracking-widest mb-3">目录</h2>
      <p class="text-blue-300 text-sm tracking-[0.3em] uppercase">Contents</p>
      <div class="w-20 h-px bg-blue-400 mx-auto mt-8"></div>
    </div>
    <!-- 左下角图标 -->
    <div class="absolute bottom-12 left-10">
      <svg class="w-8 h-8 text-blue-400 opacity-40" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M4 28 L4 8 L12 4 L20 8 L28 4 L28 24 L20 28 L12 24 L4 28Z"/>
        <path d="M12 4 L12 24"/>
        <path d="M20 8 L20 28"/>
      </svg>
    </div>
  </div>

  <!-- 右侧内容区 -->
  <div class="flex-1 flex flex-col justify-center pr-16 pl-6 relative z-10">
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-6 py-4 px-6 bg-slate-800 bg-opacity-60 rounded-lg">
        <span class="text-4xl font-bold text-blue-400 w-16 text-right flex-shrink-0">01</span>
        <div class="w-px h-8 bg-blue-400 opacity-50"></div>
        <div class="text-xl text-white font-medium">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-6 py-4 px-6 bg-slate-800 bg-opacity-60 rounded-lg">
        <span class="text-4xl font-bold text-blue-400 w-16 text-right flex-shrink-0">02</span>
        <div class="w-px h-8 bg-blue-400 opacity-50"></div>
        <div class="text-xl text-white font-medium">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-6 py-4 px-6 bg-slate-800 bg-opacity-60 rounded-lg">
        <span class="text-4xl font-bold text-blue-400 w-16 text-right flex-shrink-0">03</span>
        <div class="w-px h-8 bg-blue-400 opacity-50"></div>
        <div class="text-xl text-white font-medium">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-6 py-4 px-6 bg-slate-800 bg-opacity-60 rounded-lg">
        <span class="text-4xl font-bold text-blue-400 w-16 text-right flex-shrink-0">04</span>
        <div class="w-px h-8 bg-blue-400 opacity-50"></div>
        <div class="text-xl text-white font-medium">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-6 py-4 px-6 bg-slate-800 bg-opacity-60 rounded-lg">
        <span class="text-4xl font-bold text-blue-400 w-16 text-right flex-shrink-0">05</span>
        <div class="w-px h-8 bg-blue-400 opacity-50"></div>
        <div class="text-xl text-white font-medium">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-6 py-4 px-6 bg-slate-800 bg-opacity-60 rounded-lg">
        <span class="text-4xl font-bold text-blue-400 w-16 text-right flex-shrink-0">06</span>
        <div class="w-px h-8 bg-blue-400 opacity-50"></div>
        <div class="text-xl text-white font-medium">总结展望</div>
      </div>
    </div>
  </div>
</div>
