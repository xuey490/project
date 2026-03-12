<!-- 
模板ID: 3530
模板名称: 中国风-水墨长卷式
适用场景: 优雅中国风主题目录页
设计特点: 水墨长卷意境,竖排标题,横向条目列表,不限目录项数量,古典美学
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #f5f0e4;">
  <!-- 宣纸细纹肌理 -->
  <div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: repeating-linear-gradient(0deg, #7a6b52 0px, transparent 1px, transparent 5px), repeating-linear-gradient(90deg, #7a6b52 0px, transparent 1px, transparent 8px);"></div>

  <!-- 右下水墨山峦背景 SVG -->
  <div class="absolute bottom-0 right-0 w-[900px] h-[350px] pointer-events-none opacity-[0.06]">
    <svg viewBox="0 0 900 350" class="w-full h-full" fill="#2c1810">
      <path d="M200,350 Q260,200 340,240 Q400,160 480,210 Q540,120 620,180 Q680,90 760,140 Q820,70 900,110 L900,350Z" opacity="0.5"/>
      <path d="M350,350 Q420,250 500,280 Q580,210 660,250 Q740,180 820,220 Q860,190 900,210 L900,350Z" opacity="0.7"/>
      <path d="M500,350 Q580,290 660,310 Q740,270 820,295 Q870,275 900,290 L900,350Z"/>
      <!-- 远山小亭 -->
      <rect x="685" y="108" width="30" height="2" opacity="0.4"/>
      <line x1="700" y1="108" x2="700" y2="130" stroke="#2c1810" stroke-width="1.5" opacity="0.4"/>
      <path d="M688,108 L700,96 L712,108" fill="none" stroke="#2c1810" stroke-width="1" opacity="0.4"/>
    </svg>
  </div>

  <!-- 左上远山飞鸟 SVG -->
  <div class="absolute top-6 left-[420px] w-[300px] h-[60px] pointer-events-none opacity-[0.08]">
    <svg viewBox="0 0 300 60" class="w-full h-full" fill="none" stroke="#2c1810">
      <path d="M20,35 Q30,25 40,33" stroke-width="1.5" stroke-linecap="round"/>
      <path d="M50,30 Q60,20 70,28" stroke-width="1.5" stroke-linecap="round"/>
      <path d="M85,35 Q95,25 105,33" stroke-width="1.2" stroke-linecap="round" opacity="0.7"/>
      <path d="M120,28 Q130,18 140,26" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
      <path d="M160,32 Q170,22 180,30" stroke-width="1" stroke-linecap="round" opacity="0.4"/>
      <path d="M200,35 Q210,27 220,34" stroke-width="0.8" stroke-linecap="round" opacity="0.3"/>
    </svg>
  </div>

  <!-- 外框细线 -->
  <div class="absolute inset-6 pointer-events-none" style="border: 1px solid rgba(139,69,19,0.1);"></div>
  <!-- 内框细线 -->
  <div class="absolute inset-10 pointer-events-none" style="border: 1px solid rgba(139,69,19,0.06);"></div>

  <!-- 四角回纹装饰 -->
  <div class="absolute top-6 left-6 w-5 h-5" style="border-top: 2px solid #8b4513; border-left: 2px solid #8b4513; opacity: 0.3;"></div>
  <div class="absolute top-6 right-6 w-5 h-5" style="border-top: 2px solid #8b4513; border-right: 2px solid #8b4513; opacity: 0.3;"></div>
  <div class="absolute bottom-6 left-6 w-5 h-5" style="border-bottom: 2px solid #8b4513; border-left: 2px solid #8b4513; opacity: 0.3;"></div>
  <div class="absolute bottom-6 right-6 w-5 h-5" style="border-bottom: 2px solid #8b4513; border-right: 2px solid #8b4513; opacity: 0.3;"></div>

  <!-- 主体布局：左侧标题 | 右侧目录列表 -->
  <div class="absolute inset-0 flex z-10">

    <!-- 左侧标题区 -->
    <div class="w-[320px] h-full flex flex-col items-center justify-center relative" style="border-right: 1px solid rgba(139,69,19,0.12);">
      <!-- 竹枝 SVG 装饰 -->
      <div class="absolute top-16 left-10 w-[80px] h-[200px] pointer-events-none opacity-[0.08]">
        <svg viewBox="0 0 80 200" class="w-full h-full" fill="none" stroke="#2c1810">
          <line x1="35" y1="200" x2="35" y2="10" stroke-width="2.5" stroke-linecap="round"/>
          <line x1="35" y1="140" x2="35" y2="136" stroke-width="5" opacity="0.3"/>
          <line x1="35" y1="80" x2="35" y2="76" stroke-width="5" opacity="0.3"/>
          <path d="M35,80 Q15,65 5,55" stroke-width="1.5" stroke-linecap="round"/>
          <path d="M35,80 Q20,70 12,65" stroke-width="1.5" stroke-linecap="round"/>
          <path d="M35,40 Q55,28 70,20" stroke-width="1.5" stroke-linecap="round"/>
          <path d="M35,40 Q50,32 65,28" stroke-width="1.5" stroke-linecap="round"/>
          <path d="M35,120 Q55,108 68,100" stroke-width="1.2" stroke-linecap="round" opacity="0.7"/>
        </svg>
      </div>

      <!-- 印章 -->
      <div class="w-[56px] h-[56px] flex items-center justify-center mb-8" style="border: 2px solid #8b2500; transform: rotate(-3deg);">
        <span class="text-xl font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">目</span>
      </div>

      <!-- 竖排大标题 -->
      <h1 class="text-[56px] tracking-[0.5em] leading-tight" style="writing-mode: vertical-rl; color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">
        目录
      </h1>

      <!-- 装饰竖线 -->
      <div class="mt-6 w-[1px] h-12" style="background: #8b4513; opacity: 0.2;"></div>

      <!-- CONTENTS -->
      <span class="mt-4 text-[10px] tracking-[0.4em]" style="color: #8b4513; opacity: 0.3;">CONTENTS</span>

      <!-- 底部落款 -->
      <div class="absolute bottom-16 flex flex-col items-center gap-2">
        <div class="w-[1px] h-6" style="background: #8b4513; opacity: 0.15;"></div>
        <span class="text-xs tracking-[0.2em]" style="writing-mode: vertical-rl; color: #8b4513; opacity: 0.25; font-family: 'KaiTi', 'STKaiti', serif;">长卷览胜</span>
      </div>
    </div>

    <!-- 右侧目录列表区 -->
    <div class="flex-1 h-full flex flex-col justify-center px-16 py-20">

      <!-- 条目一 -->
      <div class="flex items-center py-7 relative" style="border-bottom: 1px solid rgba(139,69,19,0.08);">
        <!-- 朱红序号方框 -->
        <div class="w-10 h-10 flex items-center justify-center flex-shrink-0 mr-6" style="border: 2px solid #8b2500;">
          <span class="text-base font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">壹</span>
        </div>
        <!-- 标题 -->
        <h3 class="text-2xl tracking-[0.15em] flex-1" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">
          项目背景
        </h3>
        <!-- 虚点引导线 -->
        <div class="flex-1 mx-6 border-b border-dotted" style="border-color: rgba(139,69,19,0.15);"></div>
        <!-- 小圆装饰 -->
        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: #8b2500; opacity: 0.3;"></div>
      </div>

      <!-- 条目二 -->
      <div class="flex items-center py-7 relative" style="border-bottom: 1px solid rgba(139,69,19,0.08);">
        <div class="w-10 h-10 flex items-center justify-center flex-shrink-0 mr-6" style="border: 2px solid #8b2500;">
          <span class="text-base font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">贰</span>
        </div>
        <h3 class="text-2xl tracking-[0.15em] flex-1" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">
          核心功能
        </h3>
        <div class="flex-1 mx-6 border-b border-dotted" style="border-color: rgba(139,69,19,0.15);"></div>
        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: #8b2500; opacity: 0.3;"></div>
      </div>

      <!-- 条目三 -->
      <div class="flex items-center py-7 relative" style="border-bottom: 1px solid rgba(139,69,19,0.08);">
        <div class="w-10 h-10 flex items-center justify-center flex-shrink-0 mr-6" style="border: 2px solid #8b2500;">
          <span class="text-base font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">叁</span>
        </div>
        <h3 class="text-2xl tracking-[0.15em] flex-1" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">
          技术架构
        </h3>
        <div class="flex-1 mx-6 border-b border-dotted" style="border-color: rgba(139,69,19,0.15);"></div>
        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: #8b2500; opacity: 0.3;"></div>
      </div>

      <!-- 条目四 -->
      <div class="flex items-center py-7 relative" style="border-bottom: 1px solid rgba(139,69,19,0.08);">
        <div class="w-10 h-10 flex items-center justify-center flex-shrink-0 mr-6" style="border: 2px solid #8b2500;">
          <span class="text-base font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">肆</span>
        </div>
        <h3 class="text-2xl tracking-[0.15em] flex-1" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">
          实施计划
        </h3>
        <div class="flex-1 mx-6 border-b border-dotted" style="border-color: rgba(139,69,19,0.15);"></div>
        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: #8b2500; opacity: 0.3;"></div>
      </div>

      <!-- 条目五 -->
      <div class="flex items-center py-7 relative" style="border-bottom: 1px solid rgba(139,69,19,0.08);">
        <div class="w-10 h-10 flex items-center justify-center flex-shrink-0 mr-6" style="border: 2px solid #8b2500;">
          <span class="text-base font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">伍</span>
        </div>
        <h3 class="text-2xl tracking-[0.15em] flex-1" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">
          总结展望
        </h3>
        <div class="flex-1 mx-6 border-b border-dotted" style="border-color: rgba(139,69,19,0.15);"></div>
        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: #8b2500; opacity: 0.3;"></div>
      </div>

      <!-- 条目六 -->
      <div class="flex items-center py-7 relative">
        <div class="w-10 h-10 flex items-center justify-center flex-shrink-0 mr-6" style="border: 2px solid #8b2500;">
          <span class="text-base font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">陆</span>
        </div>
        <h3 class="text-2xl tracking-[0.15em] flex-1" style="color: #3d2512; font-family: 'KaiTi', 'STKaiti', serif;">
          附录参考
        </h3>
        <div class="flex-1 mx-6 border-b border-dotted" style="border-color: rgba(139,69,19,0.15);"></div>
        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: #8b2500; opacity: 0.3;"></div>
      </div>

    </div>
  </div>

  <!-- 右下印章 -->
  <div class="absolute bottom-10 right-14 w-11 h-11 flex items-center justify-center z-20" style="border: 2px solid #8b2500; transform: rotate(5deg); opacity: 0.5;">
    <span class="text-xs font-bold leading-tight text-center" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">墨<br/>香</span>
  </div>

  <!-- 右上祥云小装饰 -->
  <div class="absolute top-10 right-14 opacity-[0.08] pointer-events-none z-20">
    <svg viewBox="0 0 60 30" width="60" height="30" fill="#2c1810">
      <path d="M30,25 Q20,25 15,20 Q8,14 15,8 Q20,3 28,8 Q22,2 30,2 Q38,2 32,8 Q40,3 45,8 Q52,14 45,20 Q40,25 30,25Z"/>
    </svg>
  </div>
</div>
