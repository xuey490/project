<div class="cover-wrapper relative w-[1440px] h-[810px]">
  <div class="absolute top-0 left-0 w-[1440px] h-[810px] overflow-hidden">
    <!-- 底层白色背景 -->
    <div class="absolute inset-0 bg-white"></div>

    <!-- 对角分割背景 - 靠上的三角区域 -->
    <div class="absolute inset-0 bg-indigo-500" style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>

    <!-- 三角区域内装饰 -->
    <div class="absolute top-[40px] left-[40px] w-[60px] h-[60px] border-t-2 border-l-2 border-white/20"></div>
    <div class="absolute top-[200px] left-[100px] w-[8px] h-[8px] rounded-full bg-white/20"></div>
    <div class="absolute top-[120px] left-[250px] w-[6px] h-[6px] rounded-full bg-white/15"></div>
    <div class="absolute top-[60px] left-[400px] w-[5px] h-[5px] rounded-full bg-white/10"></div>

    <!-- 白色区域装饰 -->
    <div class="absolute bottom-[40px] right-[40px] w-[60px] h-[60px] border-b-2 border-r-2 border-indigo-200"></div>
    <div class="absolute bottom-[120px] right-[120px] w-[8px] h-[8px] rounded-full bg-indigo-300/30"></div>
    <div class="absolute bottom-[200px] right-[200px] w-[6px] h-[6px] rounded-full bg-indigo-300/20"></div>

    <!-- 对角线装饰 - 平行线 -->
    <div class="absolute top-0 left-0 w-full h-full" style="clip-path: polygon(0 0, 100% 0, 0 100%);">
      <div class="absolute w-[1px] h-[1600px] bg-white/8 origin-top-left rotate-[150deg]" style="top: 0; left: 200px;"></div>
      <div class="absolute w-[1px] h-[1600px] bg-white/5 origin-top-left rotate-[150deg]" style="top: 0; left: 350px;"></div>
    </div>

    <!-- 主内容区 -->
    <div class="absolute inset-0 z-10 flex items-center justify-center px-24">
      <div class="text-center">
        <div class="flex items-center justify-center gap-4 mb-8">
          <div class="w-[40px] h-[2px] bg-indigo-400"></div>
          <span class="text-sm text-indigo-400 tracking-[0.3em] uppercase">Project Management</span>
          <div class="w-[40px] h-[2px] bg-indigo-400"></div>
        </div>
        <h1 class="text-[5rem] font-bold text-gray-900 mb-14 leading-tight">
          项目管理指南
        </h1>
        <p class="text-3xl text-gray-600 mb-16">
          规范流程·控制风险·达成目标
        </p>
        <div class="flex items-center justify-center gap-8 text-white bg-indigo-500 px-12 py-5 rounded-full">
          <span class="text-xl">项目管理办公室</span>
          <div class="w-[6px] h-[6px] rounded-full bg-white/50"></div>
          <span class="text-xl">新人入职培训</span>
        </div>
      </div>
    </div>
  </div>
</div>
