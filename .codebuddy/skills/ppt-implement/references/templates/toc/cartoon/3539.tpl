<!-- 
模板ID: 3539
模板名称: 卡通风-积木拼搭
适用场景: 趣味益智类儿童目录页
设计特点: 暖黄底色,SVG积木齿轮装饰,彩色积木序号,列表式布局不溢出
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #fef9ed;">

  <!-- 圆点底纹 -->
  <div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: radial-gradient(circle, #d4a060 1px, transparent 1px); background-size: 28px 28px;"></div>

  <!-- SVG 齿轮左上 -->
  <div class="absolute top-8 left-10 opacity-[0.08] pointer-events-none">
    <svg viewBox="0 0 100 100" width="80" height="80" fill="#c07830">
      <path d="M50,15 L55,15 L57,5 L63,5 L65,15 Q72,18 76,24 L86,20 L90,26 L82,32 Q84,38 84,44 L95,46 L95,54 L84,56 Q84,62 82,68 L90,74 L86,80 L76,76 Q72,82 65,85 L63,95 L57,95 L55,85 Q48,84 44,82 L38,90 L32,86 L36,78 Q30,72 28,66 L18,68 L16,60 L26,56 Q24,50 26,44 L16,40 L18,34 L28,36 Q32,28 38,24 L34,14 L40,10 L44,18 Q48,16 50,15Z"/>
      <circle cx="50" cy="50" r="15" fill="#fef9ed"/>
    </svg>
  </div>

  <!-- SVG 齿轮右下 -->
  <div class="absolute bottom-10 right-12 opacity-[0.06] pointer-events-none">
    <svg viewBox="0 0 80 80" width="65" height="65" fill="#c07830">
      <path d="M40,12 L44,12 L46,4 L50,4 L52,12 Q57,14 60,18 L68,16 L71,20 L65,25 Q67,30 67,35 L76,36 L76,42 L67,43 Q67,48 65,52 L71,57 L68,62 L60,59 Q57,63 52,65 L50,72 L46,72 L44,65 Q39,64 36,62 L30,68 L26,64 L30,58 Q26,54 24,50 L16,52 L14,46 L22,43 Q20,38 22,34 L14,32 L16,26 L24,28 Q28,22 32,18 L28,12 L32,8 L36,14 Q39,12 40,12Z"/>
      <circle cx="40" cy="38" r="12" fill="#fef9ed"/>
    </svg>
  </div>

  <!-- SVG 散落积木块装饰 -->
  <div class="absolute top-20 right-32 opacity-15 pointer-events-none">
    <svg viewBox="0 0 40 40" width="32" height="32">
      <rect x="5" y="5" width="30" height="30" rx="4" fill="#ff6b6b" transform="rotate(15 20 20)"/>
      <circle cx="14" cy="14" r="4" fill="#e05050" transform="rotate(15 20 20)"/>
      <circle cx="26" cy="14" r="4" fill="#e05050" transform="rotate(15 20 20)"/>
    </svg>
  </div>
  <div class="absolute bottom-28 left-32 opacity-12 pointer-events-none">
    <svg viewBox="0 0 40 40" width="28" height="28">
      <rect x="5" y="5" width="30" height="30" rx="4" fill="#4ecdc4" transform="rotate(-10 20 20)"/>
      <circle cx="14" cy="14" r="4" fill="#3bb5ad" transform="rotate(-10 20 20)"/>
      <circle cx="26" cy="26" r="4" fill="#3bb5ad" transform="rotate(-10 20 20)"/>
    </svg>
  </div>
  <div class="absolute top-[55%] right-10 opacity-10 pointer-events-none">
    <svg viewBox="0 0 40 40" width="24" height="24">
      <rect x="5" y="5" width="30" height="30" rx="4" fill="#ffe066" transform="rotate(8 20 20)"/>
      <circle cx="20" cy="20" r="5" fill="#e6c830" transform="rotate(8 20 20)"/>
    </svg>
  </div>

  <!-- 主体布局 -->
  <div class="absolute inset-0 flex items-center z-10 px-16">

    <!-- 左侧标题 -->
    <div class="w-[340px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- 积木搭建标题 -->
      <div class="flex gap-1 mb-4">
        <div class="w-10 h-10 rounded-md flex items-center justify-center" style="background: #ff6b6b;">
          <div class="w-4 h-4 rounded-full" style="background: #e05050;"></div>
        </div>
        <div class="w-10 h-10 rounded-md flex items-center justify-center" style="background: #4ecdc4;">
          <div class="w-4 h-4 rounded-full" style="background: #3bb5ad;"></div>
        </div>
        <div class="w-10 h-10 rounded-md flex items-center justify-center" style="background: #ffe066;">
          <div class="w-4 h-4 rounded-full" style="background: #e6c830;"></div>
        </div>
      </div>
      <h1 class="text-5xl font-black tracking-wider" style="color: #e07830; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">目 录</h1>
      <div class="mt-3 text-sm tracking-[0.3em] font-bold" style="color: #e0a870;">CONTENTS</div>
    </div>

    <!-- 右侧条目列表 -->
    <div class="flex-1 flex flex-col justify-center gap-3 pl-6">

      <div class="flex items-center gap-5 py-4 px-5 rounded-xl" style="background: rgba(255,107,107,0.1);">
        <!-- 积木序号 -->
        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white relative" style="background: #ff6b6b;">
          <div class="absolute top-1 left-1.5 w-3 h-3 rounded-full opacity-40" style="background: #e05050;"></div>
          <span class="relative z-10">1</span>
        </div>
        <h3 class="text-xl font-bold" style="color: #c04040; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">项目背景</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-5 rounded-xl" style="background: rgba(78,205,196,0.1);">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white relative" style="background: #4ecdc4;">
          <div class="absolute top-1 left-1.5 w-3 h-3 rounded-full opacity-40" style="background: #3bb5ad;"></div>
          <span class="relative z-10">2</span>
        </div>
        <h3 class="text-xl font-bold" style="color: #2a908a; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">核心功能</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-5 rounded-xl" style="background: rgba(255,224,102,0.12);">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black relative" style="background: #ffe066; color: #8a7020;">
          <div class="absolute top-1 left-1.5 w-3 h-3 rounded-full opacity-40" style="background: #e6c830;"></div>
          <span class="relative z-10">3</span>
        </div>
        <h3 class="text-xl font-bold" style="color: #a08820; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">技术架构</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-5 rounded-xl" style="background: rgba(100,149,237,0.1);">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white relative" style="background: #6495ed;">
          <div class="absolute top-1 left-1.5 w-3 h-3 rounded-full opacity-40" style="background: #4a78c8;"></div>
          <span class="relative z-10">4</span>
        </div>
        <h3 class="text-xl font-bold" style="color: #3a60b0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">实施计划</h3>
      </div>

      <div class="flex items-center gap-5 py-4 px-5 rounded-xl" style="background: rgba(186,130,230,0.1);">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 text-lg font-black text-white relative" style="background: #ba82e6;">
          <div class="absolute top-1 left-1.5 w-3 h-3 rounded-full opacity-40" style="background: #9a60c8;"></div>
          <span class="relative z-10">5</span>
        </div>
        <h3 class="text-xl font-bold" style="color: #7a50a0; font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;">总结展望</h3>
      </div>

    </div>
  </div>
</div>
