<!-- 
模板ID: 3559
模板名称: 文艺风-水墨山水
适用场景: 中式文艺目录页
设计特点: 水墨山水SVG,宣纸质感,留白意境,serif字体
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #f5f0e8;">
  <!-- 宣纸纹理 -->
  <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%224%22 height=%224%22><rect width=%224%22 height=%224%22 fill=%22none%22/><rect width=%221%22 height=%221%22 fill=%22%23000%22 opacity=%220.3%22/></svg>');"></div>

  <!-- SVG 远山底纹 -->
  <svg class="absolute bottom-0 left-0 w-full" height="320" viewBox="0 0 1440 320" preserveAspectRatio="none">
    <path d="M0 320 L0 240 Q120 160 240 200 Q360 140 480 180 Q600 100 720 160 Q840 80 960 140 Q1080 60 1200 120 Q1320 80 1440 100 L1440 320 Z" fill="#d4cbbe" opacity="0.25"/>
    <path d="M0 320 L0 270 Q180 200 360 240 Q540 180 720 220 Q900 160 1080 200 Q1260 170 1440 190 L1440 320 Z" fill="#c8bfb0" opacity="0.2"/>
    <path d="M0 320 L0 290 Q200 250 400 270 Q600 230 800 260 Q1000 240 1200 260 Q1350 250 1440 260 L1440 320 Z" fill="#bdb4a4" opacity="0.15"/>
  </svg>

  <!-- SVG 竹枝装饰（右上角） -->
  <svg class="absolute top-8 right-12 opacity-[0.12]" width="160" height="400" viewBox="0 0 160 400">
    <line x1="80" y1="0" x2="80" y2="400" stroke="#4a5043" stroke-width="3"/>
    <line x1="80" y1="60" x2="80" y2="62" stroke="#4a5043" stroke-width="6"/>
    <line x1="80" y1="150" x2="80" y2="152" stroke="#4a5043" stroke-width="6"/>
    <line x1="80" y1="240" x2="80" y2="242" stroke="#4a5043" stroke-width="6"/>
    <path d="M80 50 Q110 30 140 15" stroke="#4a5043" stroke-width="1.5" fill="none"/>
    <path d="M80 50 Q115 45 150 40" stroke="#4a5043" stroke-width="1.5" fill="none"/>
    <path d="M80 140 Q50 120 20 105" stroke="#4a5043" stroke-width="1.5" fill="none"/>
    <path d="M80 140 Q45 135 10 130" stroke="#4a5043" stroke-width="1.5" fill="none"/>
    <path d="M80 230 Q110 210 145 200" stroke="#4a5043" stroke-width="1.5" fill="none"/>
    <path d="M80 230 Q115 225 150 222" stroke="#4a5043" stroke-width="1.5" fill="none"/>
    <path d="M80 320 Q50 300 15 290" stroke="#4a5043" stroke-width="1.5" fill="none"/>
    <path d="M80 320 Q45 315 10 312" stroke="#4a5043" stroke-width="1.5" fill="none"/>
  </svg>

  <!-- 主体内容区 -->
  <div class="absolute top-12 left-16 right-[220px] bottom-12 flex">
    <!-- 左侧标题区 -->
    <div class="w-[200px] flex flex-col items-center justify-center flex-shrink-0">
      <!-- 竖排标题 -->
      <div class="writing-vertical-rl text-5xl tracking-[16px] font-serif" style="color: #4a5043; writing-mode: vertical-rl;">目录</div>
      <!-- 水墨圆印章 -->
      <div class="mt-8 w-14 h-14 rounded-full flex items-center justify-center" style="background-color: #8b3a3a;">
        <span class="text-sm font-serif" style="color: #f5f0e8;">文</span>
      </div>
      <!-- 装饰线 -->
      <div class="mt-6 w-px h-24" style="background-color: #4a5043; opacity: 0.2;"></div>
    </div>

    <!-- 右侧章节列表 -->
    <div class="flex-1 flex flex-col justify-center pl-8 space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center py-3 group">
        <span class="text-2xl font-serif flex-shrink-0 w-10 text-right" style="color: #8b3a3a;">壹</span>
        <div class="mx-4 flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #8b3a3a;"></div>
        <span class="text-lg font-serif" style="color: #4a5043;">项目背景</span>
        <div class="flex-1 mx-4 border-b border-dotted" style="border-color: rgba(74,80,67,0.2);"></div>
      </div>
      <!-- 条目2 -->
      <div class="flex items-center py-3 group">
        <span class="text-2xl font-serif flex-shrink-0 w-10 text-right" style="color: #8b3a3a;">贰</span>
        <div class="mx-4 flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #8b3a3a;"></div>
        <span class="text-lg font-serif" style="color: #4a5043;">核心功能</span>
        <div class="flex-1 mx-4 border-b border-dotted" style="border-color: rgba(74,80,67,0.2);"></div>
      </div>
      <!-- 条目3 -->
      <div class="flex items-center py-3 group">
        <span class="text-2xl font-serif flex-shrink-0 w-10 text-right" style="color: #8b3a3a;">叁</span>
        <div class="mx-4 flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #8b3a3a;"></div>
        <span class="text-lg font-serif" style="color: #4a5043;">技术架构</span>
        <div class="flex-1 mx-4 border-b border-dotted" style="border-color: rgba(74,80,67,0.2);"></div>
      </div>
      <!-- 条目4 -->
      <div class="flex items-center py-3 group">
        <span class="text-2xl font-serif flex-shrink-0 w-10 text-right" style="color: #8b3a3a;">肆</span>
        <div class="mx-4 flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #8b3a3a;"></div>
        <span class="text-lg font-serif" style="color: #4a5043;">实施计划</span>
        <div class="flex-1 mx-4 border-b border-dotted" style="border-color: rgba(74,80,67,0.2);"></div>
      </div>
      <!-- 条目5 -->
      <div class="flex items-center py-3 group">
        <span class="text-2xl font-serif flex-shrink-0 w-10 text-right" style="color: #8b3a3a;">伍</span>
        <div class="mx-4 flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #8b3a3a;"></div>
        <span class="text-lg font-serif" style="color: #4a5043;">成果展示</span>
        <div class="flex-1 mx-4 border-b border-dotted" style="border-color: rgba(74,80,67,0.2);"></div>
      </div>
      <!-- 条目6 -->
      <div class="flex items-center py-3 group">
        <span class="text-2xl font-serif flex-shrink-0 w-10 text-right" style="color: #8b3a3a;">陆</span>
        <div class="mx-4 flex-shrink-0 w-1.5 h-1.5 rounded-full" style="background-color: #8b3a3a;"></div>
        <span class="text-lg font-serif" style="color: #4a5043;">未来展望</span>
        <div class="flex-1 mx-4 border-b border-dotted" style="border-color: rgba(74,80,67,0.2);"></div>
      </div>
    </div>
  </div>
</div>
