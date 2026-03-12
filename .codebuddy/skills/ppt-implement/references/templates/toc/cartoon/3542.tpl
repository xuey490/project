<!-- 
模板ID: 3542
模板名称: 卡通风-蜡笔涂鸦
适用场景: 手绘风格的儿童目录页
设计特点: 米色画纸底,SVG蜡笔铅笔涂鸦装饰,手绘童真感,列表式布局不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #fdf8ee;">

  <!-- 画纸纹理 -->
  <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: repeating-linear-gradient(0deg, #b0a080 0px, transparent 1px, transparent 6px);"></div>

  <!-- SVG 蜡笔左上 -->
  <div class="absolute top-10 left-10 opacity-25 pointer-events-none">
    <svg viewBox="0 0 120 30" width="100" height="25">
      <!-- 红蜡笔 -->
      <rect x="0" y="5" width="90" height="16" rx="3" fill="#ff6b6b"/>
      <polygon points="90,5 105,13 90,21" fill="#ff4040"/>
      <rect x="5" y="8" width="12" height="10" rx="1" fill="#e05050" opacity="0.5"/>
      <!-- 纸包 -->
      <rect x="25" y="5" width="40" height="16" rx="0" fill="#fff" opacity="0.2"/>
    </svg>
  </div>

  <!-- SVG 蜡笔右上 -->
  <div class="absolute top-6 right-14 opacity-20 pointer-events-none" style="transform: rotate(-8deg);">
    <svg viewBox="0 0 120 30" width="95" height="24">
      <rect x="0" y="5" width="90" height="16" rx="3" fill="#4ecdc4"/>
      <polygon points="90,5 105,13 90,21" fill="#3bb5ad"/>
      <rect x="25" y="5" width="40" height="16" rx="0" fill="#fff" opacity="0.2"/>
    </svg>
  </div>

  <!-- SVG 铅笔底部 -->
  <div class="absolute bottom-10 right-20 opacity-15 pointer-events-none" style="transform: rotate(15deg);">
    <svg viewBox="0 0 150 24" width="130" height="20">
      <rect x="0" y="4" width="110" height="16" rx="2" fill="#ffe066"/>
      <rect x="110" y="4" width="15" height="16" rx="0" fill="#e8c0a0"/>
      <polygon points="125,4 140,12 125,20" fill="#d4a060"/>
      <polygon points="137,10 143,12 137,14" fill="#3a3a3a"/>
      <!-- 橡皮头 -->
      <rect x="0" y="4" width="12" height="16" rx="2" fill="#f8a4c0"/>
      <rect x="12" y="4" width="4" height="16" fill="#c0c0c0"/>
    </svg>
  </div>

  <!-- SVG 涂鸦圆圈装饰 -->
  <div class="absolute top-[55%] left-8 opacity-[0.08] pointer-events-none">
    <svg viewBox="0 0 80 80" width="60" height="60" fill="none">
      <circle cx="40" cy="40" r="30" stroke="#ff6b6b" stroke-width="3" stroke-dasharray="8 4"/>
    </svg>
  </div>

  <!-- SVG 涂鸦波浪线右中 -->
  <div class="absolute top-[40%] right-6 opacity-[0.07] pointer-events-none">
    <svg viewBox="0 0 30 100" width="20" height="80" fill="none">
      <path d="M15,5 Q25,20 15,35 Q5,50 15,65 Q25,80 15,95" stroke="#4ecdc4" stroke-width="3" stroke-linecap="round"/>
    </svg>
  </div>

  <!-- SVG 涂鸦星星 -->
  <div class="absolute bottom-24 left-[35%] opacity-[0.08] pointer-events-none">
    <svg viewBox="0 0 40 40" width="30" height="30" fill="none">
      <path d="M20,4 L24,14 L35,14 L26,21 L29,32 L20,25 L11,32 L14,21 L5,14 L16,14Z" stroke="#ffe066" stroke-width="2.5" stroke-linejoin="round"/>
    </svg>
  </div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex items-center z-10 px-16">

    <!-- 左侧标题区 -->
    <div class="w-[350px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- 手绘风标题装饰 -->
      <div class="relative mb-4">
        <!-- 涂鸦圆圈 -->
        <svg viewBox="0 0 200 100" width="180" height="90" class="absolute inset-0" fill="none">
          <ellipse cx="100" cy="50" rx="90" ry="40" stroke="#ff6b6b" stroke-width="3" opacity="0.15" stroke-dasharray="12 6"/>
        </svg>
        <h1 class="text-5xl font-black tracking-wider px-8 py-4 relative z-10" style="color: #e06050; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      </div>
      <div class="text-sm tracking-[0.3em] font-bold" style="color: #d0a080;">CONTENTS</div>
      <!-- 蜡笔色块 -->
      <div class="mt-5 flex items-center gap-2">
        <div class="w-8 h-3 rounded-sm" style="background: #ff6b6b;"></div>
        <div class="w-8 h-3 rounded-sm" style="background: #4ecdc4;"></div>
        <div class="w-8 h-3 rounded-sm" style="background: #ffe066;"></div>
        <div class="w-8 h-3 rounded-sm" style="background: #a0c8ff;"></div>
        <div class="w-8 h-3 rounded-sm" style="background: #c8a0ff;"></div>
      </div>
    </div>

    <!-- 右侧条目列表 -->
    <div class="flex-1 flex flex-col justify-center gap-4 pl-6">

      <!-- 条目1 - 手绘色块背景 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-xl relative" style="background: rgba(255,107,107,0.08);">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl" style="background: #ff6b6b;"></div>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #ff6b6b;">1</div>
        <h3 class="text-xl font-bold" style="color: #c04040; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-xl relative" style="background: rgba(78,205,196,0.08);">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl" style="background: #4ecdc4;"></div>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #4ecdc4;">2</div>
        <h3 class="text-xl font-bold" style="color: #2a908a; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-xl relative" style="background: rgba(255,224,102,0.1);">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl" style="background: #ffe066;"></div>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black" style="background: #ffe066; color: #806020;">3</div>
        <h3 class="text-xl font-bold" style="color: #a08820; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-xl relative" style="background: rgba(160,200,255,0.08);">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl" style="background: #a0c8ff;"></div>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #a0c8ff;">4</div>
        <h3 class="text-xl font-bold" style="color: #4070b0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-xl relative" style="background: rgba(200,160,255,0.08);">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl" style="background: #c8a0ff;"></div>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #c8a0ff;">5</div>
        <h3 class="text-xl font-bold" style="color: #7050a0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-xl relative" style="background: rgba(255,160,120,0.08);">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl" style="background: #ffa078;"></div>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #ffa078;">6</div>
        <h3 class="text-xl font-bold" style="color: #b06040; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">附录参考</h3>
      </div>

    </div>
  </div>
</div>
