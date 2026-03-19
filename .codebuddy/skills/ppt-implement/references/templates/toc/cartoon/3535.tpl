<!-- 
模板ID: 3535
模板名称: 卡通风-糖果色块
适用场景: 活泼可爱的儿童教育类目录页
设计特点: 糖果纯色配色,SVG糖果装饰,圆润造型,列表式布局不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #fff5f9;">
  <!-- 波浪顶部装饰 -->
  <div class="absolute top-0 left-0 w-full">
    <svg viewBox="0 0 1440 80" class="w-full" fill="#ffb6d3">
      <path d="M0,0 L1440,0 L1440,40 Q1260,80 1080,40 Q900,0 720,40 Q540,80 360,40 Q180,0 0,40Z"/>
    </svg>
  </div>

  <!-- 左上 SVG 棒棒糖 -->
  <div class="absolute top-16 left-12 opacity-20 pointer-events-none">
    <svg viewBox="0 0 80 120" width="60" height="90">
      <circle cx="40" cy="30" r="25" fill="#ff6b9d" stroke="#e85588" stroke-width="2"/>
      <path d="M40,5 A25,25 0 0,1 40,55" fill="none" stroke="#fff" stroke-width="4" opacity="0.5"/>
      <rect x="38" y="55" width="4" height="55" rx="2" fill="#e8a87c"/>
    </svg>
  </div>

  <!-- 右上 SVG 糖果 -->
  <div class="absolute top-20 right-16 opacity-15 pointer-events-none">
    <svg viewBox="0 0 100 50" width="90" height="45">
      <ellipse cx="50" cy="25" rx="25" ry="18" fill="#7bc8f6"/>
      <path d="M25,18 L10,8 M25,32 L10,42" stroke="#7bc8f6" stroke-width="3" stroke-linecap="round"/>
      <path d="M75,18 L90,8 M75,32 L90,42" stroke="#7bc8f6" stroke-width="3" stroke-linecap="round"/>
      <path d="M35,20 Q50,12 65,20" fill="none" stroke="#fff" stroke-width="2.5" opacity="0.5"/>
    </svg>
  </div>

  <!-- 底部波浪 -->
  <div class="absolute bottom-0 left-0 w-full">
    <svg viewBox="0 0 1440 70" class="w-full" fill="#c8e6ff">
      <path d="M0,70 L1440,70 L1440,30 Q1260,0 1080,30 Q900,60 720,30 Q540,0 360,30 Q180,60 0,30Z"/>
    </svg>
  </div>

  <!-- 右下小星星装饰 -->
  <div class="absolute bottom-20 right-20 opacity-15 pointer-events-none">
    <svg viewBox="0 0 40 40" width="35" height="35" fill="#ffd166">
      <path d="M20,2 L24,14 L37,14 L26,22 L30,35 L20,27 L10,35 L14,22 L3,14 L16,14Z"/>
    </svg>
  </div>

  <!-- 左下圆点装饰 -->
  <div class="absolute bottom-24 left-16 w-8 h-8 rounded-full opacity-15" style="background: #b388eb;"></div>
  <div class="absolute bottom-16 left-28 w-5 h-5 rounded-full opacity-10" style="background: #ff6b9d;"></div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex items-center px-16 z-10">

    <!-- 左侧标题区 -->
    <div class="w-[340px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- SVG 大糖果装饰 -->
      <div class="mb-6">
        <svg viewBox="0 0 120 120" width="100" height="100">
          <circle cx="60" cy="60" r="50" fill="#ff6b9d"/>
          <path d="M60,10 A50,50 0 0,1 60,110" fill="none" stroke="#fff" stroke-width="8" opacity="0.3"/>
          <path d="M30,30 Q60,20 90,30" fill="none" stroke="#fff" stroke-width="4" opacity="0.4"/>
          <circle cx="45" cy="50" r="5" fill="#fff" opacity="0.4"/>
          <circle cx="70" cy="65" r="4" fill="#fff" opacity="0.3"/>
        </svg>
      </div>
      <h1 class="text-5xl font-black tracking-wider" style="color: #e8528a; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      <div class="mt-3 text-sm tracking-[0.3em] font-bold" style="color: #f0a0c0;">CONTENTS</div>
      <div class="mt-4 flex items-center gap-2">
        <div class="w-3 h-3 rounded-full" style="background: #ff6b9d;"></div>
        <div class="w-3 h-3 rounded-full" style="background: #7bc8f6;"></div>
        <div class="w-3 h-3 rounded-full" style="background: #ffd166;"></div>
        <div class="w-3 h-3 rounded-full" style="background: #b388eb;"></div>
      </div>
    </div>

    <!-- 右侧条目列表 -->
    <div class="flex-1 flex flex-col justify-center gap-4 pl-8">

      <!-- 条目1 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(255,107,157,0.12);">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl font-black text-white" style="background: #ff6b9d;">1</div>
        <h3 class="text-xl font-bold" style="color: #c94070; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(123,200,246,0.12);">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl font-black text-white" style="background: #7bc8f6;">2</div>
        <h3 class="text-xl font-bold" style="color: #3a8ec4; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(255,209,102,0.12);">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl font-black text-white" style="background: #ffd166;">3</div>
        <h3 class="text-xl font-bold" style="color: #c49a30; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(179,136,235,0.12);">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl font-black text-white" style="background: #b388eb;">4</div>
        <h3 class="text-xl font-bold" style="color: #7c52c9; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(102,216,180,0.12);">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl font-black text-white" style="background: #66d8b4;">5</div>
        <h3 class="text-xl font-bold" style="color: #38a882; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

    </div>
  </div>
</div>
