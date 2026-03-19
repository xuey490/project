<!-- 
模板ID: 3558
模板名称: 几何风-圆点阵列
适用场景: 炫酷几何风目录页
设计特点: SVG半色调圆点阵列装饰+大号编号+纵向列表,玫红色调纯色,支持多条目
-->
<div class="w-[1440px] h-[810px] bg-rose-50 flex relative overflow-hidden">
  <!-- 背景圆点阵列 SVG -->
  <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1440 810">
    <!-- 右上角半色调圆点 - 由大到小渐变 -->
    <g fill="#fda4af" opacity="0.3">
      <circle cx="1350" cy="60" r="20"/>
      <circle cx="1390" cy="60" r="16"/>
      <circle cx="1420" cy="60" r="12"/>
      <circle cx="1350" cy="100" r="16"/>
      <circle cx="1390" cy="100" r="12"/>
      <circle cx="1420" cy="100" r="8"/>
      <circle cx="1350" cy="135" r="12"/>
      <circle cx="1390" cy="135" r="8"/>
      <circle cx="1420" cy="135" r="5"/>
      <circle cx="1350" cy="165" r="8"/>
      <circle cx="1390" cy="165" r="5"/>
    </g>
    <!-- 左下角半色调圆点 -->
    <g fill="#fda4af" opacity="0.25">
      <circle cx="60" cy="750" r="18"/>
      <circle cx="100" cy="750" r="14"/>
      <circle cx="130" cy="750" r="10"/>
      <circle cx="60" cy="715" r="14"/>
      <circle cx="100" cy="715" r="10"/>
      <circle cx="130" cy="715" r="7"/>
      <circle cx="60" cy="685" r="10"/>
      <circle cx="100" cy="685" r="7"/>
      <circle cx="60" cy="660" r="7"/>
    </g>
    <!-- 散落小圆点 -->
    <circle cx="500" cy="120" r="4" fill="#e11d48" opacity="0.1"/>
    <circle cx="900" cy="700" r="5" fill="#e11d48" opacity="0.08"/>
    <circle cx="300" cy="400" r="3" fill="#f43f5e" opacity="0.12"/>
  </svg>

  <!-- 左侧标题区 -->
  <div class="w-[400px] h-full flex flex-col justify-center items-center relative z-10">
    <div class="text-center">
      <!-- 圆点组合装饰 -->
      <div class="flex items-center justify-center gap-2 mb-6">
        <div class="w-4 h-4 bg-rose-600 rounded-full"></div>
        <div class="w-3 h-3 bg-rose-400 rounded-full"></div>
        <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
      </div>
      <h2 class="text-5xl font-bold text-rose-900 tracking-wider mb-3">目录</h2>
      <p class="text-rose-400 text-sm tracking-[0.3em] uppercase">Contents</p>
      <div class="flex items-center justify-center gap-2 mt-6">
        <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
        <div class="w-3 h-3 bg-rose-400 rounded-full"></div>
        <div class="w-4 h-4 bg-rose-600 rounded-full"></div>
      </div>
    </div>
  </div>

  <!-- 右侧内容区 -->
  <div class="flex-1 flex flex-col justify-center pr-16 pl-6 relative z-10">
    <div class="space-y-3">
      <!-- 条目1 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-rose-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          01
        </div>
        <div class="text-xl text-rose-900 font-semibold">项目背景</div>
        <div class="flex-1"></div>
        <div class="flex gap-1">
          <div class="w-2 h-2 bg-rose-400 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-200 rounded-full"></div>
        </div>
      </div>

      <!-- 条目2 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-rose-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          02
        </div>
        <div class="text-xl text-rose-900 font-semibold">核心功能</div>
        <div class="flex-1"></div>
        <div class="flex gap-1">
          <div class="w-2 h-2 bg-rose-400 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-200 rounded-full"></div>
        </div>
      </div>

      <!-- 条目3 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-rose-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          03
        </div>
        <div class="text-xl text-rose-900 font-semibold">技术架构</div>
        <div class="flex-1"></div>
        <div class="flex gap-1">
          <div class="w-2 h-2 bg-rose-400 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-200 rounded-full"></div>
        </div>
      </div>

      <!-- 条目4 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-rose-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          04
        </div>
        <div class="text-xl text-rose-900 font-semibold">实施计划</div>
        <div class="flex-1"></div>
        <div class="flex gap-1">
          <div class="w-2 h-2 bg-rose-400 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-200 rounded-full"></div>
        </div>
      </div>

      <!-- 条目5 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-rose-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          05
        </div>
        <div class="text-xl text-rose-900 font-semibold">预算分析</div>
        <div class="flex-1"></div>
        <div class="flex gap-1">
          <div class="w-2 h-2 bg-rose-400 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-200 rounded-full"></div>
        </div>
      </div>

      <!-- 条目6 -->
      <div class="flex items-center gap-5 bg-white rounded-xl px-6 py-4 shadow-sm">
        <div class="w-12 h-12 bg-rose-600 text-white rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0">
          06
        </div>
        <div class="text-xl text-rose-900 font-semibold">总结展望</div>
        <div class="flex-1"></div>
        <div class="flex gap-1">
          <div class="w-2 h-2 bg-rose-400 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-300 rounded-full"></div>
          <div class="w-2 h-2 bg-rose-200 rounded-full"></div>
        </div>
      </div>
    </div>
  </div>
</div>
