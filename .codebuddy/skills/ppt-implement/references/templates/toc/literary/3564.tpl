<!-- 
模板ID: 3564
模板名称: 文艺风-信笺手写
适用场景: 书信文艺类目录页
设计特点: 信纸横线底纹,邮票SVG装饰,暖琥珀色,手写风格排版
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #f9f3e8;">
  <!-- 信纸横线底纹 -->
  <div class="absolute inset-0 opacity-[0.08]" style="background-image: repeating-linear-gradient(transparent, transparent 47px, #b8860b 47px, #b8860b 48px); background-size: 100% 48px; background-position: 0 24px;"></div>

  <!-- 左侧红线（信纸风格） -->
  <div class="absolute top-0 bottom-0 left-[100px] w-px" style="background-color: #c27c5e; opacity: 0.15;"></div>
  <div class="absolute top-0 bottom-0 left-[104px] w-px" style="background-color: #c27c5e; opacity: 0.1;"></div>

  <!-- SVG 邮票装饰（右上角） -->
  <svg class="absolute top-10 right-12 opacity-[0.12]" width="90" height="110" viewBox="0 0 90 110">
    <rect x="5" y="5" width="80" height="100" fill="#f9f3e8" stroke="#8b6914" stroke-width="1" stroke-dasharray="3 3"/>
    <rect x="10" y="10" width="70" height="90" fill="none" stroke="#8b6914" stroke-width="0.5"/>
    <text x="45" y="55" text-anchor="middle" font-size="10" font-family="serif" fill="#8b6914">MAIL</text>
    <text x="45" y="75" text-anchor="middle" font-size="8" font-family="serif" fill="#8b6914">2024</text>
    <line x1="20" y1="85" x2="70" y2="85" stroke="#8b6914" stroke-width="0.5"/>
  </svg>

  <!-- SVG 蜡封装饰（左下角） -->
  <svg class="absolute bottom-12 left-12 opacity-[0.08]" width="70" height="70" viewBox="0 0 70 70">
    <circle cx="35" cy="35" r="30" fill="#8b3a3a"/>
    <circle cx="35" cy="35" r="22" fill="none" stroke="#f9f3e8" stroke-width="1"/>
    <text x="35" y="40" text-anchor="middle" font-size="14" font-family="serif" fill="#f9f3e8">S</text>
  </svg>

  <!-- 主体内容 -->
  <div class="absolute top-14 left-[140px] right-16 bottom-14 flex flex-col justify-center">
    <!-- 标题 -->
    <div class="mb-8">
      <h2 class="text-4xl font-serif italic" style="color: #8b6914;">目录</h2>
      <div class="mt-2 flex items-center gap-3">
        <div class="w-16 h-px" style="background-color: #8b6914; opacity: 0.25;"></div>
        <span class="text-xs italic font-serif" style="color: rgba(139,105,20,0.35);">Table of Contents</span>
      </div>
    </div>

    <!-- 章节列表 -->
    <div class="space-y-0">
      <!-- 条目1 -->
      <div class="flex items-baseline py-[18px]">
        <span class="text-base font-serif italic flex-shrink-0 w-8" style="color: #8b6914;">1.</span>
        <span class="text-lg font-serif italic" style="color: #5a4510;">项目背景</span>
        <div class="flex-1 mx-4 border-b" style="border-color: rgba(139,105,20,0.1);"></div>
      </div>
      <!-- 条目2 -->
      <div class="flex items-baseline py-[18px]">
        <span class="text-base font-serif italic flex-shrink-0 w-8" style="color: #8b6914;">2.</span>
        <span class="text-lg font-serif italic" style="color: #5a4510;">核心功能</span>
        <div class="flex-1 mx-4 border-b" style="border-color: rgba(139,105,20,0.1);"></div>
      </div>
      <!-- 条目3 -->
      <div class="flex items-baseline py-[18px]">
        <span class="text-base font-serif italic flex-shrink-0 w-8" style="color: #8b6914;">3.</span>
        <span class="text-lg font-serif italic" style="color: #5a4510;">技术架构</span>
        <div class="flex-1 mx-4 border-b" style="border-color: rgba(139,105,20,0.1);"></div>
      </div>
      <!-- 条目4 -->
      <div class="flex items-baseline py-[18px]">
        <span class="text-base font-serif italic flex-shrink-0 w-8" style="color: #8b6914;">4.</span>
        <span class="text-lg font-serif italic" style="color: #5a4510;">实施计划</span>
        <div class="flex-1 mx-4 border-b" style="border-color: rgba(139,105,20,0.1);"></div>
      </div>
      <!-- 条目5 -->
      <div class="flex items-baseline py-[18px]">
        <span class="text-base font-serif italic flex-shrink-0 w-8" style="color: #8b6914;">5.</span>
        <span class="text-lg font-serif italic" style="color: #5a4510;">成果展示</span>
        <div class="flex-1 mx-4 border-b" style="border-color: rgba(139,105,20,0.1);"></div>
      </div>
      <!-- 条目6 -->
      <div class="flex items-baseline py-[18px]">
        <span class="text-base font-serif italic flex-shrink-0 w-8" style="color: #8b6914;">6.</span>
        <span class="text-lg font-serif italic" style="color: #5a4510;">未来展望</span>
        <div class="flex-1 mx-4 border-b" style="border-color: rgba(139,105,20,0.1);"></div>
      </div>
    </div>

    <!-- 底部签名线 -->
    <div class="mt-10 flex items-center gap-4">
      <div class="w-32 h-px" style="background-color: rgba(139,105,20,0.2);"></div>
      <span class="text-xs font-serif italic" style="color: rgba(139,105,20,0.3);">fin.</span>
    </div>
  </div>
</div>
