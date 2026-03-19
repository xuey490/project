<!-- 
模板ID: 3552
模板名称: 几何风-同心圆环
适用场景: 创意几何风目录页
设计特点: SVG同心圆环装饰+圆形编号+两列布局,青蓝纯色,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-cyan-50 flex items-center justify-center relative overflow-hidden">
  <!-- 背景同心圆环 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 左上大同心圆 -->
    <circle cx="150" cy="150" r="200" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <circle cx="150" cy="150" r="160" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <circle cx="150" cy="150" r="120" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <circle cx="150" cy="150" r="80" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <!-- 右下同心圆 -->
    <circle cx="1300" cy="660" r="250" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <circle cx="1300" cy="660" r="200" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <circle cx="1300" cy="660" r="150" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <circle cx="1300" cy="660" r="100" fill="none" stroke="#a5f3fc" stroke-width="1"/>
    <!-- 散落小圆 -->
    <circle cx="900" cy="80" r="6" fill="#06b6d4" opacity="0.2"/>
    <circle cx="600" cy="750" r="4" fill="#06b6d4" opacity="0.15"/>
    <circle cx="1100" cy="200" r="5" fill="#06b6d4" opacity="0.12"/>
  </svg>

  <div class="relative z-10 w-[1200px]">
    <!-- 标题区 -->
    <div class="text-center mb-14">
      <div class="inline-flex items-center gap-4">
        <svg class="w-8 h-8" viewBox="0 0 32 32">
          <circle cx="16" cy="16" r="14" fill="none" stroke="#0891b2" stroke-width="2"/>
          <circle cx="16" cy="16" r="8" fill="none" stroke="#0891b2" stroke-width="2"/>
          <circle cx="16" cy="16" r="3" fill="#0891b2"/>
        </svg>
        <h2 class="text-4xl font-bold text-cyan-900 tracking-wide">目录</h2>
        <svg class="w-8 h-8" viewBox="0 0 32 32">
          <circle cx="16" cy="16" r="14" fill="none" stroke="#0891b2" stroke-width="2"/>
          <circle cx="16" cy="16" r="8" fill="none" stroke="#0891b2" stroke-width="2"/>
          <circle cx="16" cy="16" r="3" fill="#0891b2"/>
        </svg>
      </div>
    </div>

    <!-- 两列布局 -->
    <div class="grid grid-cols-2 gap-x-12 gap-y-5">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 bg-white rounded-2xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-cyan-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          01
        </div>
        <div class="text-lg text-cyan-900 font-semibold">项目背景</div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 bg-white rounded-2xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-cyan-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          02
        </div>
        <div class="text-lg text-cyan-900 font-semibold">核心功能</div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 bg-white rounded-2xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-cyan-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          03
        </div>
        <div class="text-lg text-cyan-900 font-semibold">技术架构</div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 bg-white rounded-2xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-cyan-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          04
        </div>
        <div class="text-lg text-cyan-900 font-semibold">实施计划</div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 bg-white rounded-2xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-cyan-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          05
        </div>
        <div class="text-lg text-cyan-900 font-semibold">预算分析</div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 bg-white rounded-2xl px-6 py-5 shadow-sm">
        <div class="w-14 h-14 bg-cyan-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          06
        </div>
        <div class="text-lg text-cyan-900 font-semibold">总结展望</div>
      </div>
    </div>
  </div>
</div>
