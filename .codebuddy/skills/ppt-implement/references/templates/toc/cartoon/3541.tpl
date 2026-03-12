<!-- 
模板ID: 3541
模板名称: 卡通风-动物朋友
适用场景: 动物主题的儿童目录页
设计特点: 浅绿草地,SVG动物脚印树木蝴蝶装饰,森林探险感,列表式布局不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #f0f8e8;">

  <!-- SVG 大树左侧 -->
  <div class="absolute top-0 left-[-20px] opacity-[0.1] pointer-events-none">
    <svg viewBox="0 0 200 500" width="160" height="400">
      <!-- 树干 -->
      <rect x="80" y="200" width="40" height="250" rx="8" fill="#8B6914"/>
      <!-- 树冠 -->
      <ellipse cx="100" cy="150" rx="80" ry="100" fill="#5a9e3a"/>
      <ellipse cx="60" cy="180" rx="50" ry="60" fill="#4a8e2a"/>
      <ellipse cx="140" cy="180" rx="50" ry="60" fill="#4a8e2a"/>
      <ellipse cx="100" cy="120" rx="55" ry="65" fill="#6ab04c"/>
    </svg>
  </div>

  <!-- SVG 小树右侧 -->
  <div class="absolute top-10 right-6 opacity-[0.08] pointer-events-none">
    <svg viewBox="0 0 100 250" width="80" height="200">
      <rect x="42" y="100" width="16" height="120" rx="4" fill="#8B6914"/>
      <ellipse cx="50" cy="70" rx="40" ry="55" fill="#5a9e3a"/>
      <ellipse cx="50" cy="55" rx="28" ry="35" fill="#6ab04c"/>
    </svg>
  </div>

  <!-- SVG 蝴蝶 -->
  <div class="absolute top-24 right-[30%] opacity-20 pointer-events-none">
    <svg viewBox="0 0 50 40" width="40" height="32">
      <ellipse cx="18" cy="15" rx="12" ry="10" fill="#f8a4c0" transform="rotate(-15 18 15)"/>
      <ellipse cx="32" cy="15" rx="12" ry="10" fill="#f8a4c0" transform="rotate(15 32 15)"/>
      <ellipse cx="20" cy="26" rx="8" ry="7" fill="#ffcce0" transform="rotate(-10 20 26)"/>
      <ellipse cx="30" cy="26" rx="8" ry="7" fill="#ffcce0" transform="rotate(10 30 26)"/>
      <ellipse cx="25" cy="20" rx="2" ry="10" fill="#604020"/>
      <line x1="24" y1="10" x2="20" y2="3" stroke="#604020" stroke-width="1" stroke-linecap="round"/>
      <line x1="26" y1="10" x2="30" y2="3" stroke="#604020" stroke-width="1" stroke-linecap="round"/>
      <circle cx="20" cy="2" r="1.5" fill="#604020"/>
      <circle cx="30" cy="2" r="1.5" fill="#604020"/>
    </svg>
  </div>

  <!-- SVG 动物脚印散布 -->
  <div class="absolute inset-0 pointer-events-none opacity-[0.06]">
    <svg viewBox="0 0 1440 810" class="w-full h-full" fill="#5a4020">
      <!-- 猫爪 -->
      <ellipse cx="1100" cy="700" rx="8" ry="10"/>
      <circle cx="1088" cy="688" r="4"/>
      <circle cx="1100" cy="684" r="4"/>
      <circle cx="1112" cy="688" r="4"/>
      <!-- 猫爪2 -->
      <ellipse cx="1200" cy="660" rx="6" ry="8" transform="rotate(-15 1200 660)"/>
      <circle cx="1190" cy="648" r="3" transform="rotate(-15 1200 660)"/>
      <circle cx="1200" cy="646" r="3" transform="rotate(-15 1200 660)"/>
      <circle cx="1210" cy="648" r="3" transform="rotate(-15 1200 660)"/>
      <!-- 猫爪3 -->
      <ellipse cx="300" cy="740" rx="7" ry="9"/>
      <circle cx="289" cy="728" r="3.5"/>
      <circle cx="300" cy="725" r="3.5"/>
      <circle cx="311" cy="728" r="3.5"/>
    </svg>
  </div>

  <!-- 草地底部 -->
  <div class="absolute bottom-0 left-0 w-full">
    <svg viewBox="0 0 1440 70" class="w-full">
      <path d="M0,70 L1440,70 L1440,30 Q1200,8 960,25 Q720,45 480,15 Q240,0 0,28Z" fill="#7cc86a"/>
      <!-- 小草 -->
      <path d="M100,28 Q105,10 108,28" stroke="#5aa04a" stroke-width="2" fill="none"/>
      <path d="M400,18 Q405,2 408,18" stroke="#5aa04a" stroke-width="2" fill="none"/>
      <path d="M700,30 Q705,14 708,30" stroke="#5aa04a" stroke-width="2" fill="none"/>
      <path d="M1000,22 Q1005,6 1008,22" stroke="#5aa04a" stroke-width="2" fill="none"/>
      <path d="M1300,26 Q1305,10 1308,26" stroke="#5aa04a" stroke-width="2" fill="none"/>
      <!-- 小花 -->
      <circle cx="250" cy="24" r="4" fill="#ff8080"/>
      <circle cx="250" cy="24" r="1.5" fill="#ffe066"/>
      <circle cx="850" cy="20" r="3.5" fill="#c8a0ff"/>
      <circle cx="850" cy="20" r="1.2" fill="#ffe066"/>
    </svg>
  </div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex items-center z-10 px-16">

    <!-- 左侧标题 -->
    <div class="w-[340px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- SVG 小动物头像 -->
      <div class="mb-5">
        <svg viewBox="0 0 100 90" width="85" height="76">
          <!-- 熊脸 -->
          <circle cx="50" cy="50" r="35" fill="#c09060"/>
          <!-- 耳朵 -->
          <circle cx="22" cy="22" r="14" fill="#c09060"/>
          <circle cx="22" cy="22" r="8" fill="#e8c0a0"/>
          <circle cx="78" cy="22" r="14" fill="#c09060"/>
          <circle cx="78" cy="22" r="8" fill="#e8c0a0"/>
          <!-- 脸部 -->
          <ellipse cx="50" cy="58" rx="18" ry="14" fill="#e8c0a0"/>
          <!-- 眼睛 -->
          <circle cx="38" cy="45" r="4" fill="#3a2010"/>
          <circle cx="36" cy="43" r="1.5" fill="#fff"/>
          <circle cx="62" cy="45" r="4" fill="#3a2010"/>
          <circle cx="60" cy="43" r="1.5" fill="#fff"/>
          <!-- 鼻子 -->
          <ellipse cx="50" cy="55" rx="5" ry="3.5" fill="#3a2010"/>
          <!-- 嘴巴 -->
          <path d="M46,60 Q50,65 54,60" stroke="#3a2010" stroke-width="1.5" fill="none"/>
        </svg>
      </div>
      <h1 class="text-5xl font-black tracking-wider" style="color: #5a9040; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      <div class="mt-3 text-sm tracking-[0.3em] font-bold" style="color: #8cc070;">CONTENTS</div>
    </div>

    <!-- 右侧条目列表 -->
    <div class="flex-1 flex flex-col justify-center gap-3 pl-6">

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(108,180,76,0.1);">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #6cb44c;">1</div>
        <h3 class="text-xl font-bold" style="color: #4a8030; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(192,144,96,0.1);">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #c09060;">2</div>
        <h3 class="text-xl font-bold" style="color: #906838; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(248,164,192,0.1);">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #f8a4c0;">3</div>
        <h3 class="text-xl font-bold" style="color: #c06080; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(100,165,240,0.1);">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #64a5f0;">4</div>
        <h3 class="text-xl font-bold" style="color: #3a70b8; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(255,200,80,0.1);">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-lg font-black" style="background: #ffc850; color: #806020;">5</div>
        <h3 class="text-xl font-bold" style="color: #a08020; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

    </div>
  </div>
</div>
