<!-- 
模板ID: 3562
模板名称: 文艺风-竖排诗词
适用场景: 中式雅致目录页
设计特点: 竖排文字布局,书法韵味,SVG印章装饰,深赭色调
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #f0e8d8;">
  <!-- 宣纸底纹 -->
  <div class="absolute inset-0 opacity-[0.02]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%226%22 height=%226%22><rect width=%226%22 height=%226%22 fill=%22none%22/><rect x=%222%22 y=%222%22 width=%222%22 height=%222%22 fill=%22%23000%22 opacity=%220.2%22/></svg>');"></div>

  <!-- SVG 云纹装饰（右上） -->
  <svg class="absolute top-8 right-8 opacity-[0.07]" width="180" height="120" viewBox="0 0 180 120">
    <path d="M20 80 Q20 50 50 50 Q50 20 80 20 Q110 20 110 50 Q140 50 140 80 Q140 100 120 100 L40 100 Q20 100 20 80 Z" fill="none" stroke="#5c3d2e" stroke-width="1.5"/>
    <path d="M60 90 Q60 70 80 70 Q80 50 100 50 Q120 50 120 70 Q135 70 135 85 Q135 95 125 95 L70 95 Q60 95 60 90 Z" fill="none" stroke="#5c3d2e" stroke-width="1"/>
  </svg>

  <!-- SVG 云纹装饰（左下） -->
  <svg class="absolute bottom-8 left-8 opacity-[0.07] rotate-180" width="150" height="100" viewBox="0 0 150 100">
    <path d="M15 70 Q15 40 45 40 Q45 15 70 15 Q95 15 95 40 Q120 40 120 70 Q120 85 105 85 L30 85 Q15 85 15 70 Z" fill="none" stroke="#5c3d2e" stroke-width="1.5"/>
  </svg>

  <!-- 主体：竖排布局 -->
  <div class="absolute inset-0 flex items-center justify-center">
    <div class="flex items-stretch gap-0" style="height: 580px;">
      <!-- 竖排标题列 -->
      <div class="flex flex-col items-center justify-center px-10" style="border-right: 1px solid rgba(92,61,46,0.12);">
        <div class="text-5xl font-serif tracking-[20px]" style="color: #5c3d2e; writing-mode: vertical-rl;">目录</div>
        <!-- SVG 方印 -->
        <svg class="mt-8" width="44" height="44" viewBox="0 0 44 44">
          <rect x="2" y="2" width="40" height="40" fill="none" stroke="#8b3a3a" stroke-width="2"/>
          <text x="22" y="28" text-anchor="middle" font-size="16" font-family="serif" fill="#8b3a3a">雅</text>
        </svg>
      </div>

      <!-- 条目列 — 竖排从右到左 -->
      <div class="flex flex-row-reverse items-stretch">
        <!-- 条目1 -->
        <div class="flex flex-col items-center justify-start pt-8 px-6" style="border-left: 1px solid rgba(92,61,46,0.08);">
          <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b3a3a;">
            <span class="text-sm" style="color: #f0e8d8;">1</span>
          </div>
          <div class="mt-4 text-lg font-serif" style="color: #5c3d2e; writing-mode: vertical-rl;">项目背景</div>
        </div>
        <!-- 条目2 -->
        <div class="flex flex-col items-center justify-start pt-8 px-6" style="border-left: 1px solid rgba(92,61,46,0.08);">
          <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b3a3a;">
            <span class="text-sm" style="color: #f0e8d8;">2</span>
          </div>
          <div class="mt-4 text-lg font-serif" style="color: #5c3d2e; writing-mode: vertical-rl;">核心功能</div>
        </div>
        <!-- 条目3 -->
        <div class="flex flex-col items-center justify-start pt-8 px-6" style="border-left: 1px solid rgba(92,61,46,0.08);">
          <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b3a3a;">
            <span class="text-sm" style="color: #f0e8d8;">3</span>
          </div>
          <div class="mt-4 text-lg font-serif" style="color: #5c3d2e; writing-mode: vertical-rl;">技术架构</div>
        </div>
        <!-- 条目4 -->
        <div class="flex flex-col items-center justify-start pt-8 px-6" style="border-left: 1px solid rgba(92,61,46,0.08);">
          <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b3a3a;">
            <span class="text-sm" style="color: #f0e8d8;">4</span>
          </div>
          <div class="mt-4 text-lg font-serif" style="color: #5c3d2e; writing-mode: vertical-rl;">实施计划</div>
        </div>
        <!-- 条目5 -->
        <div class="flex flex-col items-center justify-start pt-8 px-6" style="border-left: 1px solid rgba(92,61,46,0.08);">
          <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b3a3a;">
            <span class="text-sm" style="color: #f0e8d8;">5</span>
          </div>
          <div class="mt-4 text-lg font-serif" style="color: #5c3d2e; writing-mode: vertical-rl;">成果展示</div>
        </div>
        <!-- 条目6 -->
        <div class="flex flex-col items-center justify-start pt-8 px-6" style="border-left: 1px solid rgba(92,61,46,0.08);">
          <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #8b3a3a;">
            <span class="text-sm" style="color: #f0e8d8;">6</span>
          </div>
          <div class="mt-4 text-lg font-serif" style="color: #5c3d2e; writing-mode: vertical-rl;">未来展望</div>
        </div>
      </div>
    </div>
  </div>
</div>
