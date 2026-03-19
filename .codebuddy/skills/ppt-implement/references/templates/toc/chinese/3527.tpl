<!-- 
模板ID: 3527
模板名称: 中国风-青花瓷
适用场景: 中国风主题演示的目录页
设计特点: 青花瓷纹样,蓝白配色,SVG缠枝纹饰,瓷盘构图
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background: #f8f6f0;">
  <!-- 宣纸纹理 -->
  <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: repeating-linear-gradient(0deg, #000 0px, transparent 1px, transparent 3px); background-size: 3px 3px;"></div>

  <!-- 左上角青花纹饰 -->
  <div class="absolute top-0 left-0 w-[300px] h-[300px] opacity-[0.08] pointer-events-none">
    <svg viewBox="0 0 300 300" fill="none">
      <circle cx="0" cy="0" r="280" stroke="#1e3a5f" stroke-width="1"/>
      <circle cx="0" cy="0" r="240" stroke="#1e3a5f" stroke-width="0.5"/>
      <circle cx="0" cy="0" r="200" stroke="#1e3a5f" stroke-width="0.5"/>
      <path d="M0,100 Q50,50 100,100 Q150,150 200,100 Q250,50 300,100" stroke="#1e3a5f" stroke-width="0.8" fill="none"/>
      <path d="M0,160 Q50,110 100,160 Q150,210 200,160 Q250,110 300,160" stroke="#1e3a5f" stroke-width="0.8" fill="none"/>
    </svg>
  </div>

  <!-- 右下角青花纹饰 -->
  <div class="absolute bottom-0 right-0 w-[300px] h-[300px] opacity-[0.08] pointer-events-none" style="transform: rotate(180deg);">
    <svg viewBox="0 0 300 300" fill="none">
      <circle cx="0" cy="0" r="280" stroke="#1e3a5f" stroke-width="1"/>
      <circle cx="0" cy="0" r="240" stroke="#1e3a5f" stroke-width="0.5"/>
      <path d="M0,100 Q50,50 100,100 Q150,150 200,100 Q250,50 300,100" stroke="#1e3a5f" stroke-width="0.8" fill="none"/>
    </svg>
  </div>

  <!-- 中央大瓷盘 -->
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[560px] h-[560px] pointer-events-none">
    <svg viewBox="0 0 560 560" fill="none">
      <!-- 外圈 -->
      <circle cx="280" cy="280" r="275" stroke="#1e3a5f" stroke-width="2" opacity="0.15"/>
      <circle cx="280" cy="280" r="265" stroke="#1e3a5f" stroke-width="1" opacity="0.1"/>
      <!-- 缠枝纹带 -->
      <circle cx="280" cy="280" r="250" stroke="#1e3a5f" stroke-width="0.5" opacity="0.08" stroke-dasharray="8 4"/>
      <circle cx="280" cy="280" r="240" stroke="#1e3a5f" stroke-width="0.5" opacity="0.08" stroke-dasharray="4 8"/>
      <!-- 内圈 -->
      <circle cx="280" cy="280" r="180" stroke="#1e3a5f" stroke-width="1" opacity="0.1"/>
      <circle cx="280" cy="280" r="170" stroke="#1e3a5f" stroke-width="0.5" opacity="0.08"/>
    </svg>
  </div>

  <!-- 顶部横向青花纹带 -->
  <div class="absolute top-0 left-0 w-full h-[6px]" style="background: #1e3a5f;"></div>
  <div class="absolute top-[10px] left-[60px] right-[60px] h-[1px]" style="background: #1e3a5f; opacity: 0.2;"></div>
  
  <!-- 底部横向青花纹带 -->
  <div class="absolute bottom-0 left-0 w-full h-[6px]" style="background: #1e3a5f;"></div>
  <div class="absolute bottom-[10px] left-[60px] right-[60px] h-[1px]" style="background: #1e3a5f; opacity: 0.2;"></div>

  <!-- 主体内容 -->
  <div class="w-full h-full flex relative z-10 px-20 py-16">
    
    <!-- 左侧标题区 -->
    <div class="w-[280px] flex flex-col items-center justify-center" style="border-right: 1px solid rgba(30,58,95,0.15);">
      <!-- 竖排标题 -->
      <h1 class="text-7xl font-bold leading-none tracking-[0.2em]" style="writing-mode: vertical-rl; color: #1e3a5f; font-family: 'KaiTi', 'STKaiti', serif;">
        目录
      </h1>
      
      <!-- 装饰瓷片 -->
      <div class="mt-8 w-12 h-12 flex items-center justify-center" style="border: 2px solid #1e3a5f;">
        <svg viewBox="0 0 24 24" width="20" height="20">
          <path d="M12,2 Q17,7 12,12 Q7,7 12,2Z M12,12 Q17,17 12,22 Q7,17 12,12Z M2,12 Q7,7 12,12 Q7,17 2,12Z M12,12 Q17,7 22,12 Q17,17 12,12Z" fill="#1e3a5f" opacity="0.6"/>
        </svg>
      </div>
      
      <!-- 英文 -->
      <div class="mt-6 text-xs tracking-[0.4em]" style="color: #1e3a5f; opacity: 0.4;">CONTENTS</div>
      
      <!-- 印章 -->
      <div class="mt-10 w-14 h-14 flex items-center justify-center" style="border: 2px solid #8b2500;">
        <span class="text-lg font-bold" style="color: #8b2500; font-family: 'KaiTi', 'STKaiti', serif;">青花</span>
      </div>
    </div>

    <!-- 右侧章节区 -->
    <div class="flex-1 flex items-center justify-center pl-16">
      <div class="w-full max-w-[900px] space-y-2">
        
        <!-- 章节一 -->
        <div class="flex items-center py-7 group" style="border-bottom: 1px solid rgba(30,58,95,0.1);">
          <!-- 序号瓷片 -->
          <div class="w-[56px] h-[56px] flex items-center justify-center flex-shrink-0" style="border: 2px solid #1e3a5f;">
            <span class="text-2xl font-bold" style="color: #1e3a5f; font-family: 'KaiTi', 'STKaiti', serif;">壹</span>
          </div>
          <!-- 连接线 -->
          <div class="w-8 h-[1px] flex-shrink-0" style="background: #1e3a5f; opacity: 0.2;"></div>
          <!-- 标题 -->
          <h3 class="text-2xl flex-shrink-0" style="color: #2c2c2c; font-family: 'KaiTi', 'STKaiti', serif;">项目背景</h3>
          <!-- 点线引导 -->
          <div class="flex-1 mx-6 h-[1px]" style="background-image: repeating-linear-gradient(to right, #1e3a5f 0px, #1e3a5f 2px, transparent 2px, transparent 8px); opacity: 0.15;"></div>
          <!-- 花纹装饰 -->
          <svg viewBox="0 0 32 32" width="28" height="28" class="flex-shrink-0" style="opacity: 0.3;">
            <circle cx="16" cy="16" r="14" stroke="#1e3a5f" stroke-width="1" fill="none"/>
            <path d="M16,4 Q22,10 16,16 Q10,10 16,4Z" fill="#1e3a5f" opacity="0.5"/>
            <path d="M16,16 Q22,22 16,28 Q10,22 16,16Z" fill="#1e3a5f" opacity="0.3"/>
          </svg>
        </div>

        <!-- 章节二 -->
        <div class="flex items-center py-7 group" style="border-bottom: 1px solid rgba(30,58,95,0.1);">
          <div class="w-[56px] h-[56px] flex items-center justify-center flex-shrink-0" style="border: 2px solid #1e3a5f;">
            <span class="text-2xl font-bold" style="color: #1e3a5f; font-family: 'KaiTi', 'STKaiti', serif;">贰</span>
          </div>
          <div class="w-8 h-[1px] flex-shrink-0" style="background: #1e3a5f; opacity: 0.2;"></div>
          <h3 class="text-2xl flex-shrink-0" style="color: #2c2c2c; font-family: 'KaiTi', 'STKaiti', serif;">核心功能</h3>
          <div class="flex-1 mx-6 h-[1px]" style="background-image: repeating-linear-gradient(to right, #1e3a5f 0px, #1e3a5f 2px, transparent 2px, transparent 8px); opacity: 0.15;"></div>
          <svg viewBox="0 0 32 32" width="28" height="28" class="flex-shrink-0" style="opacity: 0.3;">
            <circle cx="16" cy="16" r="14" stroke="#1e3a5f" stroke-width="1" fill="none"/>
            <path d="M4,16 Q10,10 16,16 Q10,22 4,16Z" fill="#1e3a5f" opacity="0.5"/>
            <path d="M16,16 Q22,10 28,16 Q22,22 16,16Z" fill="#1e3a5f" opacity="0.3"/>
          </svg>
        </div>

        <!-- 章节三 -->
        <div class="flex items-center py-7 group" style="border-bottom: 1px solid rgba(30,58,95,0.1);">
          <div class="w-[56px] h-[56px] flex items-center justify-center flex-shrink-0" style="border: 2px solid #1e3a5f;">
            <span class="text-2xl font-bold" style="color: #1e3a5f; font-family: 'KaiTi', 'STKaiti', serif;">叁</span>
          </div>
          <div class="w-8 h-[1px] flex-shrink-0" style="background: #1e3a5f; opacity: 0.2;"></div>
          <h3 class="text-2xl flex-shrink-0" style="color: #2c2c2c; font-family: 'KaiTi', 'STKaiti', serif;">技术架构</h3>
          <div class="flex-1 mx-6 h-[1px]" style="background-image: repeating-linear-gradient(to right, #1e3a5f 0px, #1e3a5f 2px, transparent 2px, transparent 8px); opacity: 0.15;"></div>
          <svg viewBox="0 0 32 32" width="28" height="28" class="flex-shrink-0" style="opacity: 0.3;">
            <circle cx="16" cy="16" r="14" stroke="#1e3a5f" stroke-width="1" fill="none"/>
            <path d="M16,4 Q22,10 16,16 Q10,10 16,4Z" fill="#1e3a5f" opacity="0.4"/>
            <path d="M4,16 Q10,10 16,16 Q10,22 4,16Z" fill="#1e3a5f" opacity="0.3"/>
            <path d="M16,16 Q22,22 16,28 Q10,22 16,16Z" fill="#1e3a5f" opacity="0.2"/>
          </svg>
        </div>

        <!-- 章节四 -->
        <div class="flex items-center py-7">
          <div class="w-[56px] h-[56px] flex items-center justify-center flex-shrink-0" style="border: 2px solid #1e3a5f;">
            <span class="text-2xl font-bold" style="color: #1e3a5f; font-family: 'KaiTi', 'STKaiti', serif;">肆</span>
          </div>
          <div class="w-8 h-[1px] flex-shrink-0" style="background: #1e3a5f; opacity: 0.2;"></div>
          <h3 class="text-2xl flex-shrink-0" style="color: #2c2c2c; font-family: 'KaiTi', 'STKaiti', serif;">实施计划</h3>
          <div class="flex-1 mx-6 h-[1px]" style="background-image: repeating-linear-gradient(to right, #1e3a5f 0px, #1e3a5f 2px, transparent 2px, transparent 8px); opacity: 0.15;"></div>
          <svg viewBox="0 0 32 32" width="28" height="28" class="flex-shrink-0" style="opacity: 0.3;">
            <circle cx="16" cy="16" r="14" stroke="#1e3a5f" stroke-width="1" fill="none"/>
            <path d="M8,8 L24,24 M24,8 L8,24" stroke="#1e3a5f" stroke-width="1" opacity="0.5"/>
            <circle cx="16" cy="16" r="6" stroke="#1e3a5f" stroke-width="0.8" fill="none" opacity="0.4"/>
          </svg>
        </div>

      </div>
    </div>
  </div>

  <!-- 左下角回纹装饰 -->
  <div class="absolute bottom-[20px] left-[20px] w-[60px] h-[60px] pointer-events-none" style="opacity: 0.12;">
    <svg viewBox="0 0 60 60" fill="none">
      <rect x="2" y="2" width="56" height="56" stroke="#1e3a5f" stroke-width="1"/>
      <rect x="8" y="8" width="44" height="44" stroke="#1e3a5f" stroke-width="1"/>
      <rect x="14" y="14" width="32" height="32" stroke="#1e3a5f" stroke-width="1"/>
    </svg>
  </div>

  <!-- 右上角回纹装饰 -->
  <div class="absolute top-[20px] right-[20px] w-[60px] h-[60px] pointer-events-none" style="opacity: 0.12;">
    <svg viewBox="0 0 60 60" fill="none">
      <rect x="2" y="2" width="56" height="56" stroke="#1e3a5f" stroke-width="1"/>
      <rect x="8" y="8" width="44" height="44" stroke="#1e3a5f" stroke-width="1"/>
      <rect x="14" y="14" width="32" height="32" stroke="#1e3a5f" stroke-width="1"/>
    </svg>
  </div>
</div>
