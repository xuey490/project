<!-- 
模板ID: 3556
模板名称: 几何风-波浪曲线
适用场景: 流动几何风目录页
设计特点: SVG波浪曲线装饰+圆角编号+纵向列表,蓝紫纯色,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-slate-50 flex items-center justify-center relative overflow-hidden">
  <!-- 背景波浪 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 波浪线组 -->
    <path d="M0 150 Q180 80 360 150 T720 150 T1080 150 T1440 150" fill="none" stroke="#c4b5fd" stroke-width="2" opacity="0.3"/>
    <path d="M0 200 Q180 130 360 200 T720 200 T1080 200 T1440 200" fill="none" stroke="#a78bfa" stroke-width="1.5" opacity="0.2"/>
    <path d="M0 610 Q180 680 360 610 T720 610 T1080 610 T1440 610" fill="none" stroke="#c4b5fd" stroke-width="2" opacity="0.3"/>
    <path d="M0 660 Q180 730 360 660 T720 660 T1080 660 T1440 660" fill="none" stroke="#a78bfa" stroke-width="1.5" opacity="0.2"/>
    <!-- 底部波浪填充 -->
    <path d="M0 750 Q360 700 720 750 T1440 750 L1440 810 L0 810Z" fill="#ede9fe" opacity="0.5"/>
    <path d="M0 770 Q360 720 720 770 T1440 770 L1440 810 L0 810Z" fill="#ddd6fe" opacity="0.3"/>
    <!-- 小圆点装饰 -->
    <circle cx="200" cy="400" r="4" fill="#8b5cf6" opacity="0.15"/>
    <circle cx="1250" cy="350" r="5" fill="#8b5cf6" opacity="0.12"/>
    <circle cx="700" cy="100" r="3" fill="#a78bfa" opacity="0.18"/>
  </svg>

  <div class="relative z-10 w-[1100px]">
    <!-- 标题区 -->
    <div class="text-center mb-14">
      <h2 class="text-4xl font-bold text-violet-900 mb-2">目录</h2>
      <svg class="mx-auto w-48 h-6" viewBox="0 0 192 24">
        <path d="M0 12 Q24 0 48 12 T96 12 T144 12 T192 12" fill="none" stroke="#7c3aed" stroke-width="2"/>
      </svg>
    </div>

    <!-- 纵向列表 -->
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-violet-600 text-white rounded-lg flex items-center justify-center text-lg font-bold flex-shrink-0">
          01
        </div>
        <div class="text-xl text-violet-900 font-semibold">项目背景</div>
        <div class="flex-1 mx-4">
          <svg class="w-full h-4" viewBox="0 0 400 16" preserveAspectRatio="none">
            <path d="M0 8 Q50 0 100 8 T200 8 T300 8 T400 8" fill="none" stroke="#ddd6fe" stroke-width="1.5"/>
          </svg>
        </div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-violet-600 text-white rounded-lg flex items-center justify-center text-lg font-bold flex-shrink-0">
          02
        </div>
        <div class="text-xl text-violet-900 font-semibold">核心功能</div>
        <div class="flex-1 mx-4">
          <svg class="w-full h-4" viewBox="0 0 400 16" preserveAspectRatio="none">
            <path d="M0 8 Q50 0 100 8 T200 8 T300 8 T400 8" fill="none" stroke="#ddd6fe" stroke-width="1.5"/>
          </svg>
        </div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-violet-600 text-white rounded-lg flex items-center justify-center text-lg font-bold flex-shrink-0">
          03
        </div>
        <div class="text-xl text-violet-900 font-semibold">技术架构</div>
        <div class="flex-1 mx-4">
          <svg class="w-full h-4" viewBox="0 0 400 16" preserveAspectRatio="none">
            <path d="M0 8 Q50 0 100 8 T200 8 T300 8 T400 8" fill="none" stroke="#ddd6fe" stroke-width="1.5"/>
          </svg>
        </div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-violet-600 text-white rounded-lg flex items-center justify-center text-lg font-bold flex-shrink-0">
          04
        </div>
        <div class="text-xl text-violet-900 font-semibold">实施计划</div>
        <div class="flex-1 mx-4">
          <svg class="w-full h-4" viewBox="0 0 400 16" preserveAspectRatio="none">
            <path d="M0 8 Q50 0 100 8 T200 8 T300 8 T400 8" fill="none" stroke="#ddd6fe" stroke-width="1.5"/>
          </svg>
        </div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-violet-600 text-white rounded-lg flex items-center justify-center text-lg font-bold flex-shrink-0">
          05
        </div>
        <div class="text-xl text-violet-900 font-semibold">预算分析</div>
        <div class="flex-1 mx-4">
          <svg class="w-full h-4" viewBox="0 0 400 16" preserveAspectRatio="none">
            <path d="M0 8 Q50 0 100 8 T200 8 T300 8 T400 8" fill="none" stroke="#ddd6fe" stroke-width="1.5"/>
          </svg>
        </div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-violet-600 text-white rounded-lg flex items-center justify-center text-lg font-bold flex-shrink-0">
          06
        </div>
        <div class="text-xl text-violet-900 font-semibold">总结展望</div>
        <div class="flex-1 mx-4">
          <svg class="w-full h-4" viewBox="0 0 400 16" preserveAspectRatio="none">
            <path d="M0 8 Q50 0 100 8 T200 8 T300 8 T400 8" fill="none" stroke="#ddd6fe" stroke-width="1.5"/>
          </svg>
        </div>
      </div>
    </div>
  </div>
</div>
