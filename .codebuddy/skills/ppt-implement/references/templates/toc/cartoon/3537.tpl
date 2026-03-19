<!-- 
模板ID: 3537
模板名称: 卡通风-彩虹桥
适用场景: 色彩丰富的儿童目录页
设计特点: SVG彩虹拱门,纯色条纹,云朵草地,纵向列表不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #eaf6ff;">

  <!-- SVG 彩虹拱门 —— 纯色条纹无渐变 -->
  <div class="absolute top-[-80px] left-1/2 -translate-x-1/2 pointer-events-none opacity-30">
    <svg viewBox="0 0 700 350" width="700" height="350" fill="none">
      <path d="M50,350 A300,300 0 0,1 650,350" stroke="#ef4444" stroke-width="16"/>
      <path d="M68,350 A282,282 0 0,1 632,350" stroke="#f97316" stroke-width="14"/>
      <path d="M84,350 A266,266 0 0,1 616,350" stroke="#facc15" stroke-width="14"/>
      <path d="M100,350 A250,250 0 0,1 600,350" stroke="#4ade80" stroke-width="14"/>
      <path d="M116,350 A234,234 0 0,1 584,350" stroke="#60a5fa" stroke-width="14"/>
      <path d="M132,350 A218,218 0 0,1 568,350" stroke="#a78bfa" stroke-width="14"/>
      <path d="M148,350 A202,202 0 0,1 552,350" stroke="#f472b6" stroke-width="12"/>
    </svg>
  </div>

  <!-- SVG 左云朵 -->
  <div class="absolute top-[200px] left-[-10px] opacity-30 pointer-events-none">
    <svg viewBox="0 0 160 70" width="140" height="60" fill="#fff">
      <ellipse cx="45" cy="42" rx="40" ry="22"/>
      <ellipse cx="80" cy="30" rx="38" ry="28"/>
      <ellipse cx="120" cy="42" rx="35" ry="20"/>
      <ellipse cx="65" cy="28" rx="22" ry="16"/>
    </svg>
  </div>

  <!-- SVG 右云朵 -->
  <div class="absolute top-[140px] right-4 opacity-20 pointer-events-none">
    <svg viewBox="0 0 130 55" width="110" height="46" fill="#fff">
      <ellipse cx="38" cy="32" rx="32" ry="18"/>
      <ellipse cx="70" cy="22" rx="30" ry="22"/>
      <ellipse cx="98" cy="32" rx="28" ry="16"/>
    </svg>
  </div>

  <!-- 草地 -->
  <div class="absolute bottom-0 left-0 w-full">
    <svg viewBox="0 0 1440 50" class="w-full">
      <path d="M0,50 L1440,50 L1440,20 Q1200,0 960,18 Q720,38 480,12 Q240,0 0,22Z" fill="#7dd87a"/>
      <!-- 小花 -->
      <circle cx="200" cy="18" r="5" fill="#ff6b9d"/>
      <circle cx="200" cy="18" r="2" fill="#ffe066"/>
      <circle cx="600" cy="14" r="4" fill="#a78bfa"/>
      <circle cx="600" cy="14" r="1.5" fill="#ffe066"/>
      <circle cx="1000" cy="20" r="5" fill="#ff6b9d"/>
      <circle cx="1000" cy="20" r="2" fill="#ffe066"/>
      <circle cx="1300" cy="16" r="4" fill="#60a5fa"/>
      <circle cx="1300" cy="16" r="1.5" fill="#fff"/>
    </svg>
  </div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex flex-col items-center justify-center z-10 px-20 py-12">

    <!-- 标题 -->
    <div class="mb-8 flex items-center gap-4">
      <div class="w-4 h-4 rounded-full" style="background: #ef4444;"></div>
      <div class="w-4 h-4 rounded-full" style="background: #f97316;"></div>
      <div class="w-4 h-4 rounded-full" style="background: #facc15;"></div>
      <h1 class="text-5xl font-black mx-4" style="color: #5b8cde; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      <div class="w-4 h-4 rounded-full" style="background: #4ade80;"></div>
      <div class="w-4 h-4 rounded-full" style="background: #60a5fa;"></div>
      <div class="w-4 h-4 rounded-full" style="background: #a78bfa;"></div>
    </div>

    <!-- 两列列表 -->
    <div class="w-full grid grid-cols-2 gap-x-10 gap-y-4">

      <div class="flex items-center gap-4 py-4 px-5 rounded-2xl" style="background: rgba(239,68,68,0.1);">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #ef4444;">1</div>
        <h3 class="text-lg font-bold" style="color: #c03030; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <div class="flex items-center gap-4 py-4 px-5 rounded-2xl" style="background: rgba(249,115,22,0.1);">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #f97316;">2</div>
        <h3 class="text-lg font-bold" style="color: #c05a10; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <div class="flex items-center gap-4 py-4 px-5 rounded-2xl" style="background: rgba(250,204,21,0.1);">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #e6b800;">3</div>
        <h3 class="text-lg font-bold" style="color: #a08000; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <div class="flex items-center gap-4 py-4 px-5 rounded-2xl" style="background: rgba(74,222,128,0.1);">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #3dba5e;">4</div>
        <h3 class="text-lg font-bold" style="color: #2a8040; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <div class="flex items-center gap-4 py-4 px-5 rounded-2xl" style="background: rgba(96,165,250,0.1);">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #60a5fa;">5</div>
        <h3 class="text-lg font-bold" style="color: #3570c0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

      <div class="flex items-center gap-4 py-4 px-5 rounded-2xl" style="background: rgba(167,139,250,0.1);">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #a78bfa;">6</div>
        <h3 class="text-lg font-bold" style="color: #6b50c0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">附录参考</h3>
      </div>

    </div>
  </div>
</div>
