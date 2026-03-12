<!-- 
模板ID: 3565
模板名称: 文艺风-羽毛钢笔
适用场景: 古典文艺目录页
设计特点: SVG羽毛笔装饰,墨水瓶,深靛蓝底色,古典优雅
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #1c1f35;">
  <!-- SVG 星点底纹 -->
  <svg class="absolute inset-0 w-full h-full opacity-[0.04]">
    <circle cx="100" cy="100" r="1" fill="#fff"/>
    <circle cx="300" cy="50" r="1.5" fill="#fff"/>
    <circle cx="500" cy="150" r="1" fill="#fff"/>
    <circle cx="700" cy="80" r="1.2" fill="#fff"/>
    <circle cx="900" cy="200" r="1" fill="#fff"/>
    <circle cx="1100" cy="120" r="1.5" fill="#fff"/>
    <circle cx="1300" cy="60" r="1" fill="#fff"/>
    <circle cx="200" cy="300" r="1" fill="#fff"/>
    <circle cx="600" cy="350" r="1.2" fill="#fff"/>
    <circle cx="1000" cy="400" r="1" fill="#fff"/>
    <circle cx="1200" cy="500" r="1.5" fill="#fff"/>
    <circle cx="400" cy="600" r="1" fill="#fff"/>
    <circle cx="800" cy="700" r="1.2" fill="#fff"/>
    <circle cx="1400" cy="750" r="1" fill="#fff"/>
    <circle cx="150" cy="550" r="1" fill="#fff"/>
    <circle cx="1050" cy="650" r="1.5" fill="#fff"/>
  </svg>

  <!-- SVG 羽毛笔装饰（左侧） -->
  <svg class="absolute left-16 top-1/2 -translate-y-1/2 opacity-[0.1]" width="120" height="500" viewBox="0 0 120 500">
    <!-- 笔杆 -->
    <line x1="60" y1="80" x2="60" y2="480" stroke="#c4a35a" stroke-width="3"/>
    <!-- 笔尖 -->
    <path d="M60 480 L55 500 L60 495 L65 500 Z" fill="#c4a35a"/>
    <!-- 羽毛 -->
    <path d="M60 80 Q20 40 10 0 Q30 30 60 40 Z" fill="#c4a35a" opacity="0.6"/>
    <path d="M60 80 Q100 40 110 0 Q90 30 60 40 Z" fill="#c4a35a" opacity="0.6"/>
    <path d="M60 100 Q25 70 5 30 Q30 55 60 65 Z" fill="#c4a35a" opacity="0.4"/>
    <path d="M60 100 Q95 70 115 30 Q90 55 60 65 Z" fill="#c4a35a" opacity="0.4"/>
    <path d="M60 120 Q30 100 15 60 Q35 80 60 85 Z" fill="#c4a35a" opacity="0.3"/>
    <path d="M60 120 Q90 100 105 60 Q85 80 60 85 Z" fill="#c4a35a" opacity="0.3"/>
  </svg>

  <!-- 主体内容 -->
  <div class="absolute top-12 left-[220px] right-16 bottom-12 flex flex-col justify-center">
    <!-- 标题区 -->
    <div class="mb-10">
      <div class="flex items-center gap-4">
        <div class="w-1 h-10" style="background-color: #c4a35a;"></div>
        <h2 class="text-4xl font-serif tracking-[6px]" style="color: #e8dcc8;">目录</h2>
      </div>
      <p class="mt-3 ml-5 text-xs tracking-[8px] uppercase" style="color: rgba(196,163,90,0.35);">Contents</p>
    </div>

    <!-- 章节列表 -->
    <div class="space-y-1 ml-5">
      <!-- 条目1 -->
      <div class="flex items-center py-4 px-6 rounded" style="background-color: rgba(196,163,90,0.04);">
        <div class="w-8 h-8 flex items-center justify-center flex-shrink-0" style="border: 1px solid rgba(196,163,90,0.3);">
          <span class="text-sm font-serif" style="color: #c4a35a;">1</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #e8dcc8;">项目背景</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(196,163,90,0.12);"></div>
      </div>
      <!-- 条目2 -->
      <div class="flex items-center py-4 px-6 rounded">
        <div class="w-8 h-8 flex items-center justify-center flex-shrink-0" style="border: 1px solid rgba(196,163,90,0.3);">
          <span class="text-sm font-serif" style="color: #c4a35a;">2</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #e8dcc8;">核心功能</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(196,163,90,0.12);"></div>
      </div>
      <!-- 条目3 -->
      <div class="flex items-center py-4 px-6 rounded" style="background-color: rgba(196,163,90,0.04);">
        <div class="w-8 h-8 flex items-center justify-center flex-shrink-0" style="border: 1px solid rgba(196,163,90,0.3);">
          <span class="text-sm font-serif" style="color: #c4a35a;">3</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #e8dcc8;">技术架构</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(196,163,90,0.12);"></div>
      </div>
      <!-- 条目4 -->
      <div class="flex items-center py-4 px-6 rounded">
        <div class="w-8 h-8 flex items-center justify-center flex-shrink-0" style="border: 1px solid rgba(196,163,90,0.3);">
          <span class="text-sm font-serif" style="color: #c4a35a;">4</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #e8dcc8;">实施计划</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(196,163,90,0.12);"></div>
      </div>
      <!-- 条目5 -->
      <div class="flex items-center py-4 px-6 rounded" style="background-color: rgba(196,163,90,0.04);">
        <div class="w-8 h-8 flex items-center justify-center flex-shrink-0" style="border: 1px solid rgba(196,163,90,0.3);">
          <span class="text-sm font-serif" style="color: #c4a35a;">5</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #e8dcc8;">成果展示</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(196,163,90,0.12);"></div>
      </div>
      <!-- 条目6 -->
      <div class="flex items-center py-4 px-6 rounded">
        <div class="w-8 h-8 flex items-center justify-center flex-shrink-0" style="border: 1px solid rgba(196,163,90,0.3);">
          <span class="text-sm font-serif" style="color: #c4a35a;">6</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #e8dcc8;">未来展望</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(196,163,90,0.12);"></div>
      </div>
    </div>
  </div>

  <!-- 底部墨水瓶 SVG -->
  <svg class="absolute bottom-8 right-12 opacity-[0.08]" width="60" height="70" viewBox="0 0 60 70">
    <rect x="15" y="20" width="30" height="45" rx="3" fill="#c4a35a"/>
    <rect x="10" y="15" width="40" height="8" rx="2" fill="#c4a35a"/>
    <ellipse cx="30" cy="20" rx="18" ry="4" fill="#1c1f35"/>
  </svg>
</div>
