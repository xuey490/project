<!-- 
模板ID: 3538
模板名称: 卡通风-星星点点
适用场景: 梦幻可爱的儿童目录页
设计特点: 深蓝夜空,SVG月亮星星萤火虫,梦幻童话感,纵向列表不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #1a1a3e;">

  <!-- SVG 弯月 -->
  <div class="absolute top-10 right-20 pointer-events-none">
    <svg viewBox="0 0 100 100" width="90" height="90">
      <circle cx="50" cy="50" r="35" fill="#ffe87a"/>
      <circle cx="38" cy="42" r="30" fill="#1a1a3e"/>
    </svg>
  </div>

  <!-- SVG 星星散布 -->
  <div class="absolute inset-0 pointer-events-none">
    <svg viewBox="0 0 1440 810" class="w-full h-full">
      <!-- 大星星 -->
      <path d="M200,80 L206,96 L222,96 L210,106 L214,122 L200,114 L186,122 L190,106 L178,96 L194,96Z" fill="#ffe87a" opacity="0.7"/>
      <path d="M1200,120 L1205,132 L1218,132 L1208,140 L1211,152 L1200,145 L1189,152 L1192,140 L1182,132 L1195,132Z" fill="#ffe87a" opacity="0.5"/>
      <path d="M400,180 L404,190 L415,190 L407,196 L409,207 L400,201 L391,207 L393,196 L385,190 L396,190Z" fill="#ffe87a" opacity="0.4"/>
      <!-- 小星星点 -->
      <circle cx="100" cy="150" r="2" fill="#fff" opacity="0.5"/>
      <circle cx="350" cy="60" r="1.5" fill="#fff" opacity="0.4"/>
      <circle cx="550" cy="100" r="2" fill="#fff" opacity="0.6"/>
      <circle cx="750" cy="50" r="1.5" fill="#fff" opacity="0.3"/>
      <circle cx="950" cy="180" r="2" fill="#fff" opacity="0.5"/>
      <circle cx="1100" cy="70" r="1.5" fill="#fff" opacity="0.4"/>
      <circle cx="1350" cy="200" r="2" fill="#fff" opacity="0.3"/>
      <circle cx="680" cy="160" r="1" fill="#fff" opacity="0.5"/>
      <circle cx="1250" cy="250" r="1.5" fill="#fff" opacity="0.35"/>
      <circle cx="150" cy="300" r="1" fill="#fff" opacity="0.3"/>
      <circle cx="800" cy="220" r="1.5" fill="#fff" opacity="0.25"/>
      <!-- 流星 -->
      <line x1="600" y1="30" x2="560" y2="70" stroke="#ffe87a" stroke-width="1.5" opacity="0.4" stroke-linecap="round"/>
      <circle cx="600" cy="30" r="2" fill="#ffe87a" opacity="0.5"/>
    </svg>
  </div>

  <!-- 地平线城堡剪影 -->
  <div class="absolute bottom-0 left-0 w-full pointer-events-none">
    <svg viewBox="0 0 1440 120" class="w-full" fill="#12122e">
      <!-- 地面 -->
      <path d="M0,120 L1440,120 L1440,80 Q1200,60 960,75 Q720,90 480,65 Q240,50 0,70Z"/>
      <!-- 树木剪影 -->
      <ellipse cx="150" cy="68" rx="25" ry="30" opacity="0.7"/>
      <rect x="147" y="68" width="6" height="20" opacity="0.7"/>
      <ellipse cx="350" cy="62" rx="20" ry="25" opacity="0.5"/>
      <ellipse cx="900" cy="72" rx="22" ry="28" opacity="0.6"/>
      <rect x="897" y="72" width="6" height="18" opacity="0.6"/>
      <ellipse cx="1250" cy="66" rx="18" ry="22" opacity="0.5"/>
      <!-- 小房子 -->
      <rect x="580" y="55" width="30" height="25" opacity="0.6"/>
      <path d="M575,55 L595,35 L615,55" fill="#12122e" opacity="0.6"/>
      <rect x="590" y="65" width="8" height="15" fill="#ffe87a" opacity="0.15"/>
    </svg>
  </div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex items-center z-10 px-16">

    <!-- 左侧标题 -->
    <div class="w-[320px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- 星星顶饰 -->
      <svg viewBox="0 0 40 40" width="32" height="32" class="mb-4">
        <path d="M20,2 L24,14 L37,14 L26,22 L30,35 L20,27 L10,35 L14,22 L3,14 L16,14Z" fill="#ffe87a"/>
      </svg>
      <h1 class="text-5xl font-black tracking-wider" style="color: #ffe87a; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      <div class="mt-3 text-sm tracking-[0.3em] font-bold" style="color: rgba(255,232,122,0.4);">CONTENTS</div>
      <div class="mt-5 w-16 h-[1px]" style="background: rgba(255,232,122,0.2);"></div>
    </div>

    <!-- 右侧条目列表 -->
    <div class="flex-1 flex flex-col justify-center gap-3 pl-8 pr-4">

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(255,232,122,0.08);">
        <svg viewBox="0 0 30 30" width="28" height="28" class="flex-shrink-0"><path d="M15,2 L18,11 L28,11 L20,17 L23,27 L15,21 L7,27 L10,17 L2,11 L12,11Z" fill="#ffe87a"/></svg>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-base font-black" style="background: #ffe87a; color: #1a1a3e;">1</div>
        <h3 class="text-xl font-bold" style="color: #ffe87a; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(160,200,255,0.08);">
        <svg viewBox="0 0 30 30" width="28" height="28" class="flex-shrink-0"><path d="M15,2 L18,11 L28,11 L20,17 L23,27 L15,21 L7,27 L10,17 L2,11 L12,11Z" fill="#a0c8ff"/></svg>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-base font-black" style="background: #a0c8ff; color: #1a1a3e;">2</div>
        <h3 class="text-xl font-bold" style="color: #a0c8ff; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(200,160,255,0.08);">
        <svg viewBox="0 0 30 30" width="28" height="28" class="flex-shrink-0"><path d="M15,2 L18,11 L28,11 L20,17 L23,27 L15,21 L7,27 L10,17 L2,11 L12,11Z" fill="#c8a0ff"/></svg>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-base font-black" style="background: #c8a0ff; color: #1a1a3e;">3</div>
        <h3 class="text-xl font-bold" style="color: #c8a0ff; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(255,160,180,0.08);">
        <svg viewBox="0 0 30 30" width="28" height="28" class="flex-shrink-0"><path d="M15,2 L18,11 L28,11 L20,17 L23,27 L15,21 L7,27 L10,17 L2,11 L12,11Z" fill="#ffa0b4"/></svg>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-base font-black" style="background: #ffa0b4; color: #1a1a3e;">4</div>
        <h3 class="text-xl font-bold" style="color: #ffa0b4; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-6 rounded-2xl" style="background: rgba(130,230,200,0.08);">
        <svg viewBox="0 0 30 30" width="28" height="28" class="flex-shrink-0"><path d="M15,2 L18,11 L28,11 L20,17 L23,27 L15,21 L7,27 L10,17 L2,11 L12,11Z" fill="#82e6c8"/></svg>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-base font-black" style="background: #82e6c8; color: #1a1a3e;">5</div>
        <h3 class="text-xl font-bold" style="color: #82e6c8; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

    </div>
  </div>
</div>
