<!-- 
模板ID: 3563
模板名称: 文艺风-水墨泼洒
适用场景: 前卫文艺目录页
设计特点: SVG墨迹泼洒底纹,灰白素色,错落布局,随性气质
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #f8f6f3;">
  <!-- SVG 墨迹泼洒装饰 -->
  <svg class="absolute top-0 right-0 opacity-[0.06]" width="500" height="400" viewBox="0 0 500 400">
    <ellipse cx="300" cy="100" rx="120" ry="60" fill="#1a1a1a" transform="rotate(-15 300 100)"/>
    <ellipse cx="350" cy="150" rx="80" ry="30" fill="#1a1a1a" transform="rotate(25 350 150)"/>
    <circle cx="250" cy="80" r="25" fill="#1a1a1a"/>
    <circle cx="400" cy="120" r="15" fill="#1a1a1a"/>
    <circle cx="420" cy="180" r="8" fill="#1a1a1a"/>
    <circle cx="200" cy="130" r="6" fill="#1a1a1a"/>
    <circle cx="380" cy="200" r="4" fill="#1a1a1a"/>
    <path d="M300 130 Q340 160 360 220 Q370 250 355 280" stroke="#1a1a1a" stroke-width="3" fill="none"/>
  </svg>

  <svg class="absolute bottom-0 left-0 opacity-[0.04]" width="400" height="300" viewBox="0 0 400 300">
    <ellipse cx="100" cy="200" rx="100" ry="50" fill="#1a1a1a" transform="rotate(10 100 200)"/>
    <circle cx="180" cy="250" r="20" fill="#1a1a1a"/>
    <circle cx="50" cy="230" r="12" fill="#1a1a1a"/>
    <circle cx="220" cy="270" r="6" fill="#1a1a1a"/>
  </svg>

  <!-- 主体内容 -->
  <div class="absolute top-12 left-20 right-20 bottom-12 flex">
    <!-- 左侧标题区 -->
    <div class="w-[280px] flex flex-col justify-center items-start flex-shrink-0">
      <div class="relative">
        <!-- 大字标题 -->
        <h2 class="text-6xl font-serif" style="color: #1a1a1a;">目录</h2>
        <!-- 墨点装饰 -->
        <div class="absolute -right-4 -top-2 w-3 h-3 rounded-full" style="background-color: #1a1a1a; opacity: 0.15;"></div>
        <div class="absolute -right-8 top-2 w-2 h-2 rounded-full" style="background-color: #1a1a1a; opacity: 0.1;"></div>
      </div>
      <div class="mt-3 w-20 h-0.5" style="background-color: #1a1a1a; opacity: 0.15;"></div>
      <p class="mt-4 text-xs tracking-[8px] uppercase" style="color: rgba(26,26,26,0.25);">Contents</p>

      <!-- SVG 墨滴 -->
      <svg class="mt-16 opacity-[0.08]" width="60" height="80" viewBox="0 0 60 80">
        <path d="M30 0 Q10 30 10 50 Q10 70 30 75 Q50 70 50 50 Q50 30 30 0 Z" fill="#1a1a1a"/>
      </svg>
    </div>

    <!-- 右侧章节列表 -->
    <div class="flex-1 flex flex-col justify-center pl-12 space-y-2">
      <!-- 条目1 -->
      <div class="flex items-center py-4 pl-6" style="border-left: 3px solid rgba(26,26,26,0.12);">
        <span class="text-4xl font-serif flex-shrink-0 w-16" style="color: rgba(26,26,26,0.08);">01</span>
        <span class="text-xl font-serif" style="color: #2a2a2a;">项目背景</span>
      </div>
      <!-- 条目2 -->
      <div class="flex items-center py-4 pl-6" style="border-left: 3px solid rgba(26,26,26,0.12);">
        <span class="text-4xl font-serif flex-shrink-0 w-16" style="color: rgba(26,26,26,0.08);">02</span>
        <span class="text-xl font-serif" style="color: #2a2a2a;">核心功能</span>
      </div>
      <!-- 条目3 -->
      <div class="flex items-center py-4 pl-6" style="border-left: 3px solid rgba(26,26,26,0.12);">
        <span class="text-4xl font-serif flex-shrink-0 w-16" style="color: rgba(26,26,26,0.08);">03</span>
        <span class="text-xl font-serif" style="color: #2a2a2a;">技术架构</span>
      </div>
      <!-- 条目4 -->
      <div class="flex items-center py-4 pl-6" style="border-left: 3px solid rgba(26,26,26,0.12);">
        <span class="text-4xl font-serif flex-shrink-0 w-16" style="color: rgba(26,26,26,0.08);">04</span>
        <span class="text-xl font-serif" style="color: #2a2a2a;">实施计划</span>
      </div>
      <!-- 条目5 -->
      <div class="flex items-center py-4 pl-6" style="border-left: 3px solid rgba(26,26,26,0.12);">
        <span class="text-4xl font-serif flex-shrink-0 w-16" style="color: rgba(26,26,26,0.08);">05</span>
        <span class="text-xl font-serif" style="color: #2a2a2a;">成果展示</span>
      </div>
      <!-- 条目6 -->
      <div class="flex items-center py-4 pl-6" style="border-left: 3px solid rgba(26,26,26,0.12);">
        <span class="text-4xl font-serif flex-shrink-0 w-16" style="color: rgba(26,26,26,0.08);">06</span>
        <span class="text-xl font-serif" style="color: #2a2a2a;">未来展望</span>
      </div>
    </div>
  </div>
</div>
