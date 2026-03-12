<!-- Template: 科技风格-变体24 (Content #1684) -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #FFFFFF;">
    <!-- 顶部色条 -->
    <div class="absolute top-0 left-0 right-0 h-[4px]" style="background-color: #0F172A;"></div>
    <div class="absolute inset-0 flex flex-col px-16 py-12">
        <!-- 标题 -->
        <div class="text-center mb-6 pb-5" style="border-bottom: 2px solid #E2E8F0;">
            <div class="inline-block px-3 py-1 rounded mb-3" style="background-color: #0F172A;">
                <span class="font-mono text-[14px] font-bold" style="color: #06B6D4;">ARCHITECTURE</span>
            </div>
            <h1 class="text-[38px] font-bold" style="color: #0F172A;">平台整体架构</h1>
            <p class="text-[14px] mt-1" style="color: #94A3B8;">以数据中台为核心的技术架构设计</p>
        </div>

        <!-- 三层架构 -->
        <div class="flex-1 flex flex-col gap-3">
            <!-- 接入层 -->
            <div class="flex gap-3 flex-1">
                <div class="w-[120px] flex-shrink-0 flex items-center justify-center rounded" style="background-color: #0D9488;">
                    <span class="text-[14px] font-bold font-mono text-white">ACCESS</span>
                </div>
                <div class="flex-1 flex gap-3">
                    <div class="flex-1 p-4 rounded" style="background-color: #F0FDFA;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0D9488;">API Gateway</p>
                        <p class="text-[13px]" style="color: #64748B;">统一入口、路由分发</p>
                    </div>
                    <div class="flex-1 p-4 rounded" style="background-color: #F0FDFA;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0D9488;">Load Balancer</p>
                        <p class="text-[13px]" style="color: #64748B;">流量分发、健康检查</p>
                    </div>
                    <div class="flex-1 p-4 rounded" style="background-color: #F0FDFA;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0D9488;">Auth Service</p>
                        <p class="text-[13px]" style="color: #64748B;">身份认证、权限校验</p>
                    </div>
                </div>
            </div>

            <!-- 连接线 -->
            <svg class="w-full h-4 flex-shrink-0" viewBox="0 0 1200 16"><line x1="600" y1="0" x2="600" y2="16" stroke="#CBD5E1" stroke-width="1" stroke-dasharray="4,3"/></svg>

            <!-- 服务层 -->
            <div class="flex gap-3 flex-1">
                <div class="w-[120px] flex-shrink-0 flex items-center justify-center rounded" style="background-color: #0891B2;">
                    <span class="text-[14px] font-bold font-mono text-white">SERVICE</span>
                </div>
                <div class="flex-1 flex gap-3">
                    <div class="flex-1 p-4 rounded" style="background-color: #ECFEFF;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0891B2;">数据采集</p>
                        <p class="text-[13px]" style="color: #64748B;">多源异构接入</p>
                    </div>
                    <div class="flex-1 p-4 rounded" style="background-color: #ECFEFF;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0891B2;">计算引擎</p>
                        <p class="text-[13px]" style="color: #64748B;">批流一体处理</p>
                    </div>
                    <div class="flex-1 p-4 rounded" style="background-color: #ECFEFF;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0891B2;">数据治理</p>
                        <p class="text-[13px]" style="color: #64748B;">质量管控、血缘</p>
                    </div>
                    <div class="flex-1 p-4 rounded" style="background-color: #ECFEFF;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0891B2;">数据服务</p>
                        <p class="text-[13px]" style="color: #64748B;">API、报表、大屏</p>
                    </div>
                </div>
            </div>

            <!-- 连接线 -->
            <svg class="w-full h-4 flex-shrink-0" viewBox="0 0 1200 16"><line x1="600" y1="0" x2="600" y2="16" stroke="#CBD5E1" stroke-width="1" stroke-dasharray="4,3"/></svg>

            <!-- 数据层 -->
            <div class="flex gap-3 flex-1">
                <div class="w-[120px] flex-shrink-0 flex items-center justify-center rounded" style="background-color: #0F172A;">
                    <span class="text-[14px] font-bold font-mono" style="color: #06B6D4;">DATA</span>
                </div>
                <div class="flex-1 flex gap-3">
                    <div class="flex-1 p-4 rounded" style="background-color: #F1F5F9;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0F172A;">OLTP</p>
                        <p class="text-[13px]" style="color: #64748B;">关系型数据存储</p>
                    </div>
                    <div class="flex-1 p-4 rounded" style="background-color: #F1F5F9;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0F172A;">Data Lake</p>
                        <p class="text-[13px]" style="color: #64748B;">海量数据湖存储</p>
                    </div>
                    <div class="flex-1 p-4 rounded" style="background-color: #F1F5F9;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #0F172A;">Cache</p>
                        <p class="text-[13px]" style="color: #64748B;">高速缓存层</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 底部注释 -->
        <div class="mt-4 pt-4 flex items-center justify-between" style="border-top: 1px solid #E2E8F0;">
            <p class="text-[12px] font-mono" style="color: #94A3B8;">// Platform Architecture v3.0 | Cloud Native</p>
            <p class="text-[12px] font-mono" style="color: #94A3B8;">SLA: 99.99%</p>
        </div>
    </div>
</div>