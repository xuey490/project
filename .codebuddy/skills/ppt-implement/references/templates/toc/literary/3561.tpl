<!-- 
模板ID: 3561
模板名称: 文艺风-花卉植物
适用场景: 优雅文艺目录页
设计特点: SVG花枝装饰,暖白底,玫瑰色调,优雅排版
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #faf7f4;">
  <!-- SVG 花枝装饰（左侧） -->
  <svg class="absolute top-0 left-0 h-full opacity-[0.06]" width="300" viewBox="0 0 300 810">
    <path d="M150 810 Q140 700 120 600 Q100 500 130 400 Q160 300 140 200 Q120 100 150 0" stroke="#8b4557" stroke-width="2" fill="none"/>
    <circle cx="120" cy="150" r="20" fill="none" stroke="#8b4557" stroke-width="1"/>
    <circle cx="120" cy="150" r="12" fill="#8b4557" opacity="0.15"/>
    <circle cx="160" cy="300" r="16" fill="none" stroke="#8b4557" stroke-width="1"/>
    <circle cx="160" cy="300" r="9" fill="#8b4557" opacity="0.15"/>
    <circle cx="110" cy="480" r="22" fill="none" stroke="#8b4557" stroke-width="1"/>
    <circle cx="110" cy="480" r="14" fill="#8b4557" opacity="0.15"/>
    <circle cx="155" cy="620" r="14" fill="none" stroke="#8b4557" stroke-width="1"/>
    <circle cx="155" cy="620" r="8" fill="#8b4557" opacity="0.15"/>
    <ellipse cx="90" cy="130" rx="18" ry="10" fill="none" stroke="#8b4557" stroke-width="0.8" transform="rotate(-30 90 130)"/>
    <ellipse cx="150" cy="130" rx="18" ry="10" fill="none" stroke="#8b4557" stroke-width="0.8" transform="rotate(30 150 130)"/>
    <ellipse cx="130" cy="280" rx="16" ry="9" fill="none" stroke="#8b4557" stroke-width="0.8" transform="rotate(-20 130 280)"/>
    <ellipse cx="190" cy="280" rx="16" ry="9" fill="none" stroke="#8b4557" stroke-width="0.8" transform="rotate(25 190 280)"/>
    <ellipse cx="80" cy="460" rx="20" ry="11" fill="none" stroke="#8b4557" stroke-width="0.8" transform="rotate(-35 80 460)"/>
    <ellipse cx="140" cy="460" rx="20" ry="11" fill="none" stroke="#8b4557" stroke-width="0.8" transform="rotate(20 140 460)"/>
  </svg>

  <!-- SVG 花瓣散落（右下角） -->
  <svg class="absolute bottom-0 right-0 opacity-[0.05]" width="250" height="250" viewBox="0 0 250 250">
    <ellipse cx="60" cy="200" rx="25" ry="12" fill="#8b4557" transform="rotate(-45 60 200)"/>
    <ellipse cx="120" cy="180" rx="20" ry="10" fill="#8b4557" transform="rotate(20 120 180)"/>
    <ellipse cx="180" cy="220" rx="22" ry="11" fill="#8b4557" transform="rotate(-15 180 220)"/>
    <ellipse cx="200" cy="160" rx="18" ry="9" fill="#8b4557" transform="rotate(35 200 160)"/>
  </svg>

  <!-- 主体内容 -->
  <div class="absolute top-12 left-[300px] right-16 bottom-12 flex flex-col justify-center">
    <!-- 标题 -->
    <div class="mb-10">
      <h2 class="text-4xl font-serif tracking-[8px]" style="color: #8b4557;">目录</h2>
      <div class="mt-3 flex items-center gap-2">
        <div class="w-12 h-px" style="background-color: #8b4557; opacity: 0.3;"></div>
        <div class="w-2 h-2 rounded-full" style="background-color: #8b4557; opacity: 0.3;"></div>
      </div>
      <p class="mt-2 text-xs tracking-[6px] uppercase" style="color: rgba(139,69,87,0.35);">Contents</p>
    </div>

    <!-- 章节列表 -->
    <div class="space-y-1">
      <!-- 条目1 -->
      <div class="flex items-center py-4 px-5" style="background-color: rgba(139,69,87,0.03);">
        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b4557;">
          <span class="text-sm font-serif" style="color: #faf7f4;">1</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #4a3540;">项目背景</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(139,69,87,0.15);"></div>
      </div>
      <!-- 条目2 -->
      <div class="flex items-center py-4 px-5">
        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b4557;">
          <span class="text-sm font-serif" style="color: #faf7f4;">2</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #4a3540;">核心功能</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(139,69,87,0.15);"></div>
      </div>
      <!-- 条目3 -->
      <div class="flex items-center py-4 px-5" style="background-color: rgba(139,69,87,0.03);">
        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b4557;">
          <span class="text-sm font-serif" style="color: #faf7f4;">3</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #4a3540;">技术架构</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(139,69,87,0.15);"></div>
      </div>
      <!-- 条目4 -->
      <div class="flex items-center py-4 px-5">
        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b4557;">
          <span class="text-sm font-serif" style="color: #faf7f4;">4</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #4a3540;">实施计划</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(139,69,87,0.15);"></div>
      </div>
      <!-- 条目5 -->
      <div class="flex items-center py-4 px-5" style="background-color: rgba(139,69,87,0.03);">
        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b4557;">
          <span class="text-sm font-serif" style="color: #faf7f4;">5</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #4a3540;">成果展示</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(139,69,87,0.15);"></div>
      </div>
      <!-- 条目6 -->
      <div class="flex items-center py-4 px-5">
        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b4557;">
          <span class="text-sm font-serif" style="color: #faf7f4;">6</span>
        </div>
        <span class="ml-5 text-lg font-serif" style="color: #4a3540;">未来展望</span>
        <div class="flex-1 mx-5 border-b border-dotted" style="border-color: rgba(139,69,87,0.15);"></div>
      </div>
    </div>
  </div>
</div>
