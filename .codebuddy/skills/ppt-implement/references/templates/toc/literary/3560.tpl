<!-- 
模板ID: 3560
模板名称: 文艺风-古典书页
适用场景: 复古文艺目录页
设计特点: 翻开的书本造型,藤蔓SVG装饰,墨绿色调,楷书风格
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #2c3529;">
  <!-- SVG 藤蔓角花装饰 -->
  <svg class="absolute top-0 left-0 opacity-[0.08]" width="200" height="200" viewBox="0 0 200 200">
    <path d="M0 0 Q50 10 60 60 Q65 80 50 100 Q35 80 40 60 Q45 40 0 30 Z" fill="#a8c090"/>
    <path d="M0 0 Q10 50 60 60 Q80 65 100 50 Q80 35 60 40 Q40 45 30 0 Z" fill="#a8c090"/>
    <circle cx="60" cy="60" r="4" fill="#7a9060"/>
  </svg>
  <svg class="absolute bottom-0 right-0 opacity-[0.08] rotate-180" width="200" height="200" viewBox="0 0 200 200">
    <path d="M0 0 Q50 10 60 60 Q65 80 50 100 Q35 80 40 60 Q45 40 0 30 Z" fill="#a8c090"/>
    <path d="M0 0 Q10 50 60 60 Q80 65 100 50 Q80 35 60 40 Q40 45 30 0 Z" fill="#a8c090"/>
    <circle cx="60" cy="60" r="4" fill="#7a9060"/>
  </svg>

  <!-- 书本主体 -->
  <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 flex" style="width: 1100px; height: 660px;">
    <!-- 左页 -->
    <div class="w-1/2 relative" style="background-color: #f7f3ec; box-shadow: inset -8px 0 20px rgba(0,0,0,0.04);">
      <!-- 页面装饰线（上） -->
      <div class="absolute top-5 left-5 right-5 h-px" style="background-color: rgba(74,80,67,0.12);"></div>
      <div class="absolute top-8 left-8 right-8 h-px" style="background-color: rgba(74,80,67,0.06);"></div>

      <!-- 标题区 -->
      <div class="pt-16 px-12 text-center">
        <h2 class="text-4xl tracking-[12px] font-serif" style="color: #3d5a3a;">目 录</h2>
        <div class="mt-4 flex items-center justify-center gap-3">
          <div class="w-8 h-px" style="background-color: #3d5a3a; opacity: 0.4;"></div>
          <svg width="12" height="12" viewBox="0 0 12 12"><path d="M6 0 L7.5 4.5 L12 6 L7.5 7.5 L6 12 L4.5 7.5 L0 6 L4.5 4.5 Z" fill="#3d5a3a" opacity="0.4"/></svg>
          <div class="w-8 h-px" style="background-color: #3d5a3a; opacity: 0.4;"></div>
        </div>
        <p class="mt-3 text-xs tracking-[4px]" style="color: rgba(61,90,58,0.5);">CONTENTS</p>
      </div>

      <!-- 左页条目 -->
      <div class="px-12 mt-10 space-y-6">
        <div class="flex items-baseline gap-4">
          <span class="text-3xl font-serif flex-shrink-0" style="color: rgba(61,90,58,0.15);">01</span>
          <div class="flex-1">
            <span class="text-lg font-serif" style="color: #3d5a3a;">项目背景</span>
            <div class="mt-1 h-px" style="background-color: rgba(61,90,58,0.08);"></div>
          </div>
        </div>
        <div class="flex items-baseline gap-4">
          <span class="text-3xl font-serif flex-shrink-0" style="color: rgba(61,90,58,0.15);">02</span>
          <div class="flex-1">
            <span class="text-lg font-serif" style="color: #3d5a3a;">核心功能</span>
            <div class="mt-1 h-px" style="background-color: rgba(61,90,58,0.08);"></div>
          </div>
        </div>
        <div class="flex items-baseline gap-4">
          <span class="text-3xl font-serif flex-shrink-0" style="color: rgba(61,90,58,0.15);">03</span>
          <div class="flex-1">
            <span class="text-lg font-serif" style="color: #3d5a3a;">技术架构</span>
            <div class="mt-1 h-px" style="background-color: rgba(61,90,58,0.08);"></div>
          </div>
        </div>
      </div>

      <!-- 页码 -->
      <div class="absolute bottom-6 right-10 text-xs font-serif" style="color: rgba(61,90,58,0.3);">- i -</div>
      <!-- 页面装饰线（下） -->
      <div class="absolute bottom-5 left-5 right-5 h-px" style="background-color: rgba(74,80,67,0.12);"></div>
    </div>

    <!-- 书脊 -->
    <div class="w-[6px] flex-shrink-0" style="background-color: #5d7a54; box-shadow: 2px 0 8px rgba(0,0,0,0.1), -2px 0 8px rgba(0,0,0,0.1);"></div>

    <!-- 右页 -->
    <div class="w-1/2 relative" style="background-color: #f7f3ec; box-shadow: inset 8px 0 20px rgba(0,0,0,0.04);">
      <!-- 页面装饰线（上） -->
      <div class="absolute top-5 left-5 right-5 h-px" style="background-color: rgba(74,80,67,0.12);"></div>
      <div class="absolute top-8 left-8 right-8 h-px" style="background-color: rgba(74,80,67,0.06);"></div>

      <!-- 右页条目 -->
      <div class="px-12 pt-16 space-y-6">
        <div class="flex items-baseline gap-4">
          <span class="text-3xl font-serif flex-shrink-0" style="color: rgba(61,90,58,0.15);">04</span>
          <div class="flex-1">
            <span class="text-lg font-serif" style="color: #3d5a3a;">实施计划</span>
            <div class="mt-1 h-px" style="background-color: rgba(61,90,58,0.08);"></div>
          </div>
        </div>
        <div class="flex items-baseline gap-4">
          <span class="text-3xl font-serif flex-shrink-0" style="color: rgba(61,90,58,0.15);">05</span>
          <div class="flex-1">
            <span class="text-lg font-serif" style="color: #3d5a3a;">成果展示</span>
            <div class="mt-1 h-px" style="background-color: rgba(61,90,58,0.08);"></div>
          </div>
        </div>
        <div class="flex items-baseline gap-4">
          <span class="text-3xl font-serif flex-shrink-0" style="color: rgba(61,90,58,0.15);">06</span>
          <div class="flex-1">
            <span class="text-lg font-serif" style="color: #3d5a3a;">未来展望</span>
            <div class="mt-1 h-px" style="background-color: rgba(61,90,58,0.08);"></div>
          </div>
        </div>
      </div>

      <!-- SVG 叶片装饰 -->
      <svg class="absolute bottom-16 right-10 opacity-[0.08]" width="100" height="120" viewBox="0 0 100 120">
        <path d="M50 120 Q50 80 30 50 Q20 35 25 15 Q35 30 40 50 Q45 70 50 80 Q55 70 60 50 Q65 30 75 15 Q80 35 70 50 Q50 80 50 120 Z" fill="#3d5a3a"/>
      </svg>

      <!-- 页码 -->
      <div class="absolute bottom-6 left-10 text-xs font-serif" style="color: rgba(61,90,58,0.3);">- ii -</div>
      <!-- 页面装饰线（下） -->
      <div class="absolute bottom-5 left-5 right-5 h-px" style="background-color: rgba(74,80,67,0.12);"></div>
    </div>
  </div>
</div>
