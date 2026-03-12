<!-- 
模板ID: 3536
模板名称: 卡通风-云朵漂浮
适用场景: 轻松愉快的儿童主题目录页
设计特点: 天蓝纯色天空,SVG云朵太阳热气球,轻盈漂浮感,纵向列表不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #e8f4fd;">

  <!-- SVG 太阳 -->
  <div class="absolute top-8 right-16 pointer-events-none">
    <svg viewBox="0 0 100 100" width="80" height="80">
      <circle cx="50" cy="50" r="22" fill="#ffe066"/>
      <!-- 光芒 -->
      <line x1="50" y1="8" x2="50" y2="20" stroke="#ffe066" stroke-width="4" stroke-linecap="round"/>
      <line x1="50" y1="80" x2="50" y2="92" stroke="#ffe066" stroke-width="4" stroke-linecap="round"/>
      <line x1="8" y1="50" x2="20" y2="50" stroke="#ffe066" stroke-width="4" stroke-linecap="round"/>
      <line x1="80" y1="50" x2="92" y2="50" stroke="#ffe066" stroke-width="4" stroke-linecap="round"/>
      <line x1="20" y1="20" x2="28" y2="28" stroke="#ffe066" stroke-width="3" stroke-linecap="round"/>
      <line x1="72" y1="20" x2="80" y2="28" stroke="#ffe066" stroke-width="3" stroke-linecap="round" transform="rotate(90 76 24)"/>
      <line x1="20" y1="72" x2="28" y2="80" stroke="#ffe066" stroke-width="3" stroke-linecap="round" transform="rotate(-90 24 76)"/>
      <line x1="72" y1="72" x2="80" y2="80" stroke="#ffe066" stroke-width="3" stroke-linecap="round"/>
    </svg>
  </div>

  <!-- SVG 大云朵左上 -->
  <div class="absolute top-12 left-10 opacity-25 pointer-events-none">
    <svg viewBox="0 0 200 90" width="180" height="80" fill="#fff">
      <ellipse cx="60" cy="55" rx="50" ry="30"/>
      <ellipse cx="100" cy="40" rx="45" ry="35"/>
      <ellipse cx="145" cy="55" rx="45" ry="28"/>
      <ellipse cx="80" cy="35" rx="30" ry="22"/>
      <ellipse cx="125" cy="32" rx="28" ry="20"/>
    </svg>
  </div>

  <!-- SVG 小云朵右中 -->
  <div class="absolute top-[45%] right-8 opacity-15 pointer-events-none">
    <svg viewBox="0 0 120 55" width="100" height="45" fill="#fff">
      <ellipse cx="35" cy="32" rx="30" ry="18"/>
      <ellipse cx="65" cy="24" rx="28" ry="22"/>
      <ellipse cx="90" cy="32" rx="25" ry="16"/>
    </svg>
  </div>

  <!-- SVG 热气球左下 -->
  <div class="absolute bottom-16 left-12 opacity-20 pointer-events-none">
    <svg viewBox="0 0 70 110" width="55" height="85">
      <ellipse cx="35" cy="38" rx="28" ry="35" fill="#ff8a80"/>
      <path d="M15,60 Q25,80 35,85 Q45,80 55,60" fill="#ff8a80"/>
      <!-- 条纹 -->
      <path d="M22,15 Q35,5 48,15" fill="none" stroke="#fff" stroke-width="2.5" opacity="0.4"/>
      <path d="M18,28 Q35,18 52,28" fill="none" stroke="#fff" stroke-width="2" opacity="0.3"/>
      <!-- 吊篮 -->
      <line x1="28" y1="85" x2="25" y2="95" stroke="#a0845e" stroke-width="1.5"/>
      <line x1="42" y1="85" x2="45" y2="95" stroke="#a0845e" stroke-width="1.5"/>
      <rect x="22" y="95" width="26" height="12" rx="3" fill="#c4a36e"/>
    </svg>
  </div>

  <!-- 草地底部 -->
  <div class="absolute bottom-0 left-0 w-full">
    <svg viewBox="0 0 1440 60" class="w-full" fill="#8dd68a">
      <path d="M0,60 L1440,60 L1440,25 Q1300,5 1150,20 Q1000,40 850,18 Q700,0 550,22 Q400,42 250,15 Q100,0 0,20Z"/>
    </svg>
  </div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex z-10">

    <!-- 左侧标题区 -->
    <div class="w-[350px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- 云朵标题框 -->
      <div class="relative mb-4">
        <svg viewBox="0 0 220 110" width="200" height="100" fill="#fff">
          <ellipse cx="65" cy="68" rx="55" ry="32"/>
          <ellipse cx="110" cy="50" rx="55" ry="42"/>
          <ellipse cx="155" cy="68" rx="50" ry="30"/>
          <ellipse cx="90" cy="42" rx="35" ry="25"/>
          <ellipse cx="135" cy="40" rx="32" ry="24"/>
        </svg>
        <h1 class="absolute inset-0 flex items-center justify-center text-4xl font-black pt-2" style="color: #4a9bd9; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      </div>
      <div class="text-xs tracking-[0.35em] font-bold" style="color: #8cc4e8;">CONTENTS</div>
    </div>

    <!-- 右侧条目列表 -->
    <div class="flex-1 flex flex-col justify-center gap-3 pr-16 py-16">

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,255,255,0.7);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #4a9bd9;">1</div>
        <h3 class="text-xl font-bold" style="color: #357aa8; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,255,255,0.7);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #ff8a80;">2</div>
        <h3 class="text-xl font-bold" style="color: #c05050; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,255,255,0.7);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #ffe066;">3</div>
        <h3 class="text-xl font-bold" style="color: #b8941a; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,255,255,0.7);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #8dd68a;">4</div>
        <h3 class="text-xl font-bold" style="color: #4a9050; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,255,255,0.7);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #c8a0e8;">5</div>
        <h3 class="text-xl font-bold" style="color: #7a52a0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-full" style="background: rgba(255,255,255,0.7);">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 text-lg font-black text-white" style="background: #ffb060;">6</div>
        <h3 class="text-xl font-bold" style="color: #b07020; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">附录参考</h3>
      </div>

    </div>
  </div>
</div>
