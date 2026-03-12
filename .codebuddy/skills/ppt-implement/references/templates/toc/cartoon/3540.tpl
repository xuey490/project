<!-- 
模板ID: 3540
模板名称: 卡通风-冰淇淋
适用场景: 甜美可爱的儿童目录页
设计特点: 柔粉底色,SVG冰淇淋甜甜圈蛋糕装饰,甜品配色,纵向列表不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #fff0f3;">

  <!-- 波点底纹 -->
  <div class="absolute inset-0 opacity-[0.05] pointer-events-none" style="background-image: radial-gradient(circle, #e88ca8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

  <!-- SVG 冰淇淋左上 -->
  <div class="absolute top-8 left-14 opacity-20 pointer-events-none">
    <svg viewBox="0 0 60 100" width="50" height="82">
      <!-- 冰淇淋球 -->
      <circle cx="30" cy="25" r="20" fill="#f8a4c0"/>
      <circle cx="30" cy="22" r="16" fill="#ffcce0"/>
      <!-- 华夫筒 -->
      <path d="M14,38 L30,90 L46,38" fill="#d4a060"/>
      <line x1="18" y1="48" x2="42" y2="48" stroke="#c09050" stroke-width="1" opacity="0.5"/>
      <line x1="22" y1="60" x2="38" y2="60" stroke="#c09050" stroke-width="1" opacity="0.5"/>
      <!-- 樱桃 -->
      <circle cx="30" cy="8" r="5" fill="#e04060"/>
      <path d="M30,8 Q34,0 38,4" stroke="#40a040" stroke-width="1.5" fill="none"/>
    </svg>
  </div>

  <!-- SVG 甜甜圈右上 -->
  <div class="absolute top-12 right-16 opacity-15 pointer-events-none">
    <svg viewBox="0 0 80 80" width="65" height="65">
      <circle cx="40" cy="40" r="30" fill="#e8a0c0"/>
      <circle cx="40" cy="40" r="12" fill="#fff0f3"/>
      <!-- 糖霜 -->
      <path d="M12,35 Q18,28 25,32 Q32,22 40,30 Q48,22 55,32 Q62,28 68,35" fill="#ffcce0"/>
      <!-- 彩色糖珠 -->
      <circle cx="25" cy="50" r="2.5" fill="#ffe066"/>
      <circle cx="40" cy="56" r="2.5" fill="#82e6c8"/>
      <circle cx="55" cy="48" r="2.5" fill="#a0c8ff"/>
      <circle cx="32" cy="30" r="2" fill="#ff8080"/>
    </svg>
  </div>

  <!-- SVG 杯子蛋糕右下 -->
  <div class="absolute bottom-12 right-20 opacity-15 pointer-events-none">
    <svg viewBox="0 0 60 70" width="50" height="58">
      <!-- 杯子 -->
      <path d="M12,40 L18,65 L42,65 L48,40" fill="#e8a0c0"/>
      <line x1="20" y1="48" x2="40" y2="48" stroke="#d090b0" stroke-width="1" opacity="0.5"/>
      <!-- 奶油 -->
      <path d="M10,40 Q20,30 30,20 Q40,30 50,40" fill="#fff5f5"/>
      <circle cx="30" cy="18" r="4" fill="#ff6080"/>
    </svg>
  </div>

  <!-- 底部波浪 -->
  <div class="absolute bottom-0 left-0 w-full">
    <svg viewBox="0 0 1440 45" class="w-full" fill="#f8d0e0">
      <path d="M0,45 L1440,45 L1440,18 Q1200,0 960,15 Q720,32 480,10 Q240,0 0,20Z"/>
    </svg>
  </div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex items-center z-10 px-16">

    <!-- 左侧标题区 -->
    <div class="w-[340px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- 冰淇淋标题装饰 -->
      <div class="mb-5">
        <svg viewBox="0 0 80 110" width="70" height="95">
          <circle cx="40" cy="30" r="25" fill="#f8a4c0"/>
          <circle cx="40" cy="26" r="20" fill="#ffcce0"/>
          <path d="M20,45 L40,100 L60,45" fill="#d4a060"/>
          <line x1="25" y1="58" x2="55" y2="58" stroke="#c09050" stroke-width="1.5" opacity="0.5"/>
          <circle cx="40" cy="10" r="6" fill="#e04060"/>
          <path d="M40,10 Q46,0 50,5" stroke="#40a040" stroke-width="2" fill="none"/>
          <!-- 高光 -->
          <circle cx="32" cy="22" r="4" fill="#fff" opacity="0.35"/>
        </svg>
      </div>
      <h1 class="text-5xl font-black tracking-wider" style="color: #d06080; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      <div class="mt-3 text-sm tracking-[0.3em] font-bold" style="color: #e8a0c0;">CONTENTS</div>
    </div>

    <!-- 右侧条目列表 -->
    <div class="flex-1 flex flex-col justify-center gap-3 pl-8">

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(248,164,192,0.12);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #f8a4c0;">1</div>
        <h3 class="text-xl font-bold" style="color: #c06080; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(160,200,255,0.12);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #a0c8ff;">2</div>
        <h3 class="text-xl font-bold" style="color: #5080c0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(130,230,200,0.12);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #82e6c8;">3</div>
        <h3 class="text-xl font-bold" style="color: #40a080; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,224,102,0.12);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black" style="background: #ffe066; color: #8a7020;">4</div>
        <h3 class="text-xl font-bold" style="color: #a08820; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(200,160,235,0.12);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #c8a0eb;">5</div>
        <h3 class="text-xl font-bold" style="color: #8060a8; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,160,120,0.12);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #ffa078;">6</div>
        <h3 class="text-xl font-bold" style="color: #b06040; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">附录参考</h3>
      </div>

    </div>
  </div>
</div>
