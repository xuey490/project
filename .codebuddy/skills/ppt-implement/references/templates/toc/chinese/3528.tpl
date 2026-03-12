<!-- 
模板ID: 3528
模板名称: 中国风-祥云纹
适用场景: 传统中国风主题的目录页
设计特点: SVG祥云图案,朱红金配色,回纹边框,传统吉祥元素
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #f7f0e3;">
  <!-- 纸张纹理 -->
  <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: repeating-linear-gradient(45deg, #8b7355 0px, transparent 1px, transparent 6px); background-size: 6px 6px;"></div>

  <!-- 回纹边框 -->
  <div class="absolute inset-6 pointer-events-none" style="border: 2px solid #c9a96e;"></div>
  <div class="absolute inset-10 pointer-events-none" style="border: 1px solid #c9a96e; opacity: 0.4;"></div>

  <!-- 四角角花 -->
  <div class="absolute top-6 left-6 w-8 h-8" style="border-top: 3px solid #8b2500; border-left: 3px solid #8b2500;"></div>
  <div class="absolute top-6 right-6 w-8 h-8" style="border-top: 3px solid #8b2500; border-right: 3px solid #8b2500;"></div>
  <div class="absolute bottom-6 left-6 w-8 h-8" style="border-bottom: 3px solid #8b2500; border-left: 3px solid #8b2500;"></div>
  <div class="absolute bottom-6 right-6 w-8 h-8" style="border-bottom: 3px solid #8b2500; border-right: 3px solid #8b2500;"></div>

  <!-- 顶部祥云纹带 -->
  <div class="absolute top-[28px] left-[40px] right-[40px] h-[40px] flex items-center justify-center pointer-events-none opacity-[0.12]">
    <svg viewBox="0 0 1360 40" width="100%" height="40">
      <!-- 重复祥云 -->
      <g transform="translate(100,20)">
        <path d="M0,0 Q-8,-12 -4,-18 Q0,-24 8,-18 Q12,-12 8,-4 Q16,-8 20,-4 Q24,0 20,6 Q16,10 8,6 Q4,10 0,6 Q-4,2 0,0Z" fill="#c9a96e"/>
      </g>
      <g transform="translate(300,20)">
        <path d="M0,0 Q-8,-12 -4,-18 Q0,-24 8,-18 Q12,-12 8,-4 Q16,-8 20,-4 Q24,0 20,6 Q16,10 8,6 Q4,10 0,6 Q-4,2 0,0Z" fill="#c9a96e"/>
      </g>
      <g transform="translate(500,20)">
        <path d="M0,0 Q-8,-12 -4,-18 Q0,-24 8,-18 Q12,-12 8,-4 Q16,-8 20,-4 Q24,0 20,6 Q16,10 8,6 Q4,10 0,6 Q-4,2 0,0Z" fill="#c9a96e"/>
      </g>
      <g transform="translate(700,20)">
        <path d="M0,0 Q-8,-12 -4,-18 Q0,-24 8,-18 Q12,-12 8,-4 Q16,-8 20,-4 Q24,0 20,6 Q16,10 8,6 Q4,10 0,6 Q-4,2 0,0Z" fill="#c9a96e"/>
      </g>
      <g transform="translate(900,20)">
        <path d="M0,0 Q-8,-12 -4,-18 Q0,-24 8,-18 Q12,-12 8,-4 Q16,-8 20,-4 Q24,0 20,6 Q16,10 8,6 Q4,10 0,6 Q-4,2 0,0Z" fill="#c9a96e"/>
      </g>
      <g transform="translate(1100,20)">
        <path d="M0,0 Q-8,-12 -4,-18 Q0,-24 8,-18 Q12,-12 8,-4 Q16,-8 20,-4 Q24,0 20,6 Q16,10 8,6 Q4,10 0,6 Q-4,2 0,0Z" fill="#c9a96e"/>
      </g>
    </svg>
  </div>

  <!-- 大祥云背景装饰 - 右上 -->
  <div class="absolute top-[60px] right-[60px] w-[200px] h-[200px] pointer-events-none opacity-[0.06]">
    <svg viewBox="0 0 200 200" fill="#8b4513">
      <path d="M100,40 Q70,10 50,30 Q30,50 50,70 Q30,70 20,90 Q10,110 30,120 Q20,140 40,155 Q60,170 80,155 Q80,175 100,180 Q120,175 120,155 Q140,170 160,155 Q180,140 170,120 Q190,110 180,90 Q170,70 150,70 Q170,50 150,30 Q130,10 100,40Z"/>
    </svg>
  </div>

  <!-- 大祥云背景装饰 - 左下 -->
  <div class="absolute bottom-[60px] left-[60px] w-[160px] h-[160px] pointer-events-none opacity-[0.05]" style="transform: scaleX(-1);">
    <svg viewBox="0 0 200 200" fill="#8b4513">
      <path d="M100,40 Q70,10 50,30 Q30,50 50,70 Q30,70 20,90 Q10,110 30,120 Q20,140 40,155 Q60,170 80,155 Q80,175 100,180 Q120,175 120,155 Q140,170 160,155 Q180,140 170,120 Q190,110 180,90 Q170,70 150,70 Q170,50 150,30 Q130,10 100,40Z"/>
    </svg>
  </div>

  <!-- 主体内容 -->
  <div class="w-full h-full flex flex-col items-center justify-center px-24 py-20 relative z-10">
    
    <!-- 标题区 -->
    <div class="flex flex-col items-center mb-14">
      <!-- 顶部小祥云 -->
      <div class="mb-4 opacity-30">
        <svg viewBox="0 0 60 30" width="60" height="30" fill="#8b2500">
          <path d="M30,5 Q20,-2 12,5 Q5,12 12,18 Q5,18 2,24 Q-1,30 8,30 L52,30 Q61,30 58,24 Q55,18 48,18 Q55,12 48,5 Q40,-2 30,5Z"/>
        </svg>
      </div>
      <h1 class="text-5xl font-bold tracking-[0.3em]" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">目  录</h1>
      <div class="mt-4 flex items-center gap-4">
        <div class="w-20 h-[1px]" style="background: #c9a96e;"></div>
        <div class="w-3 h-3 rotate-45" style="border: 1px solid #c9a96e;"></div>
        <div class="w-20 h-[1px]" style="background: #c9a96e;"></div>
      </div>
      <span class="mt-3 text-xs tracking-[0.5em]" style="color: #c9a96e;">CONTENTS</span>
    </div>

    <!-- 章节区 - 横向排列 -->
    <div class="flex items-start justify-center gap-6 w-full max-w-[1100px]">
      
      <!-- 章节一 -->
      <div class="flex-1 flex flex-col items-center py-8 px-6 relative" style="border: 1px solid #c9a96e;">
        <!-- 角花 -->
        <div class="absolute top-0 left-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute top-0 right-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 left-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 right-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <!-- 小祥云装饰 -->
        <div class="mb-4 opacity-25">
          <svg viewBox="0 0 40 20" width="32" height="16" fill="#8b2500">
            <path d="M20,3 Q14,-1 9,3 Q4,8 9,12 Q4,12 2,16 Q0,20 6,20 L34,20 Q40,20 38,16 Q36,12 31,12 Q36,8 31,3 Q26,-1 20,3Z"/>
          </svg>
        </div>
        <!-- 序号 -->
        <div class="w-12 h-12 flex items-center justify-center mb-4" style="background: #8b2500;">
          <span class="text-xl font-bold text-white" style="font-family: 'KaiTi', 'STKaiti', serif;">壹</span>
        </div>
        <!-- 分隔 -->
        <div class="w-[1px] h-6" style="background: #c9a96e; opacity: 0.5;"></div>
        <!-- 标题 -->
        <h3 class="mt-4 text-xl tracking-[0.2em]" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">项目背景</h3>
      </div>

      <!-- 章节二 -->
      <div class="flex-1 flex flex-col items-center py-8 px-6 relative" style="border: 1px solid #c9a96e;">
        <div class="absolute top-0 left-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute top-0 right-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 left-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 right-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <div class="mb-4 opacity-25">
          <svg viewBox="0 0 40 20" width="32" height="16" fill="#8b2500">
            <path d="M20,3 Q14,-1 9,3 Q4,8 9,12 Q4,12 2,16 Q0,20 6,20 L34,20 Q40,20 38,16 Q36,12 31,12 Q36,8 31,3 Q26,-1 20,3Z"/>
          </svg>
        </div>
        <div class="w-12 h-12 flex items-center justify-center mb-4" style="background: #8b2500;">
          <span class="text-xl font-bold text-white" style="font-family: 'KaiTi', 'STKaiti', serif;">贰</span>
        </div>
        <div class="w-[1px] h-6" style="background: #c9a96e; opacity: 0.5;"></div>
        <h3 class="mt-4 text-xl tracking-[0.2em]" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">核心功能</h3>
      </div>

      <!-- 章节三 -->
      <div class="flex-1 flex flex-col items-center py-8 px-6 relative" style="border: 1px solid #c9a96e;">
        <div class="absolute top-0 left-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute top-0 right-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 left-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 right-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <div class="mb-4 opacity-25">
          <svg viewBox="0 0 40 20" width="32" height="16" fill="#8b2500">
            <path d="M20,3 Q14,-1 9,3 Q4,8 9,12 Q4,12 2,16 Q0,20 6,20 L34,20 Q40,20 38,16 Q36,12 31,12 Q36,8 31,3 Q26,-1 20,3Z"/>
          </svg>
        </div>
        <div class="w-12 h-12 flex items-center justify-center mb-4" style="background: #8b2500;">
          <span class="text-xl font-bold text-white" style="font-family: 'KaiTi', 'STKaiti', serif;">叁</span>
        </div>
        <div class="w-[1px] h-6" style="background: #c9a96e; opacity: 0.5;"></div>
        <h3 class="mt-4 text-xl tracking-[0.2em]" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">技术架构</h3>
      </div>

      <!-- 章节四 -->
      <div class="flex-1 flex flex-col items-center py-8 px-6 relative" style="border: 1px solid #c9a96e;">
        <div class="absolute top-0 left-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute top-0 right-0 w-4 h-4" style="border-top: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 left-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-left: 2px solid #8b2500;"></div>
        <div class="absolute bottom-0 right-0 w-4 h-4" style="border-bottom: 2px solid #8b2500; border-right: 2px solid #8b2500;"></div>
        <div class="mb-4 opacity-25">
          <svg viewBox="0 0 40 20" width="32" height="16" fill="#8b2500">
            <path d="M20,3 Q14,-1 9,3 Q4,8 9,12 Q4,12 2,16 Q0,20 6,20 L34,20 Q40,20 38,16 Q36,12 31,12 Q36,8 31,3 Q26,-1 20,3Z"/>
          </svg>
        </div>
        <div class="w-12 h-12 flex items-center justify-center mb-4" style="background: #8b2500;">
          <span class="text-xl font-bold text-white" style="font-family: 'KaiTi', 'STKaiti', serif;">肆</span>
        </div>
        <div class="w-[1px] h-6" style="background: #c9a96e; opacity: 0.5;"></div>
        <h3 class="mt-4 text-xl tracking-[0.2em]" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">实施计划</h3>
      </div>

    </div>

    <!-- 底部装饰 -->
    <div class="mt-12 flex items-center gap-4">
      <div class="w-16 h-[1px]" style="background: #c9a96e; opacity: 0.4;"></div>
      <div class="opacity-25">
        <svg viewBox="0 0 40 20" width="28" height="14" fill="#8b2500">
          <path d="M20,3 Q14,-1 9,3 Q4,8 9,12 Q4,12 2,16 Q0,20 6,20 L34,20 Q40,20 38,16 Q36,12 31,12 Q36,8 31,3 Q26,-1 20,3Z"/>
        </svg>
      </div>
      <div class="w-16 h-[1px]" style="background: #c9a96e; opacity: 0.4;"></div>
    </div>
  </div>
</div>
