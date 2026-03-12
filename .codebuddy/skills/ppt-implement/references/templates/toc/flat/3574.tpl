<!-- 
模板ID: 3574
模板名称: 扁平风-Material卡片
适用场景: Material Design扁平风目录页
设计特点: 浅灰底,白色浮动卡片,靛蓝FAB按钮编号,层次阴影,纯色扁平
-->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #f5f5f5;">
  <!-- 顶部应用栏 -->
  <div class="absolute top-0 left-0 w-full h-[72px] flex items-center px-8 z-20" style="background-color: #4338ca; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
    <h2 class="text-2xl font-medium text-white tracking-wide">目录</h2>
    <span class="ml-4 text-sm" style="color: rgba(255,255,255,0.6);">CONTENTS</span>
  </div>

  <!-- FAB按钮（右下装饰） -->
  <div class="absolute bottom-8 right-8 w-14 h-14 rounded-full flex items-center justify-center z-20" style="background-color: #4338ca; box-shadow: 0 3px 6px rgba(67,56,202,0.3);">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
      <path d="M5 12h14M12 5v14" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
  </div>

  <!-- 主体卡片区 -->
  <div class="absolute top-[92px] left-0 right-0 bottom-0 flex flex-col justify-center px-16">
    <div class="grid grid-cols-2 gap-5 max-w-[1100px] mx-auto w-full">
      <!-- 条目1 -->
      <div class="bg-white rounded-lg px-6 py-5 flex items-center gap-5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-white font-medium text-lg" style="background-color: #4338ca;">1</div>
        <div>
          <span class="text-base font-medium text-gray-900">项目背景</span>
          <p class="text-xs mt-0.5" style="color: #9ca3af;">了解项目的起源与发展目标</p>
        </div>
      </div>
      <!-- 条目2 -->
      <div class="bg-white rounded-lg px-6 py-5 flex items-center gap-5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-white font-medium text-lg" style="background-color: #7c3aed;">2</div>
        <div>
          <span class="text-base font-medium text-gray-900">核心功能</span>
          <p class="text-xs mt-0.5" style="color: #9ca3af;">产品的主要功能与特性介绍</p>
        </div>
      </div>
      <!-- 条目3 -->
      <div class="bg-white rounded-lg px-6 py-5 flex items-center gap-5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-white font-medium text-lg" style="background-color: #0891b2;">3</div>
        <div>
          <span class="text-base font-medium text-gray-900">技术架构</span>
          <p class="text-xs mt-0.5" style="color: #9ca3af;">系统设计与技术选型方案</p>
        </div>
      </div>
      <!-- 条目4 -->
      <div class="bg-white rounded-lg px-6 py-5 flex items-center gap-5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-white font-medium text-lg" style="background-color: #059669;">4</div>
        <div>
          <span class="text-base font-medium text-gray-900">实施计划</span>
          <p class="text-xs mt-0.5" style="color: #9ca3af;">项目推进时间与里程碑</p>
        </div>
      </div>
      <!-- 条目5 -->
      <div class="bg-white rounded-lg px-6 py-5 flex items-center gap-5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-white font-medium text-lg" style="background-color: #d97706;">5</div>
        <div>
          <span class="text-base font-medium text-gray-900">成果展示</span>
          <p class="text-xs mt-0.5" style="color: #9ca3af;">项目交付成果与效果展示</p>
        </div>
      </div>
      <!-- 条目6 -->
      <div class="bg-white rounded-lg px-6 py-5 flex items-center gap-5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-white font-medium text-lg" style="background-color: #dc2626;">6</div>
        <div>
          <span class="text-base font-medium text-gray-900">未来展望</span>
          <p class="text-xs mt-0.5" style="color: #9ca3af;">未来发展方向与规划蓝图</p>
        </div>
      </div>
    </div>
  </div>
</div>
