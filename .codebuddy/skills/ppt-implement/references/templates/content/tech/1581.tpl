<!-- Template: 科技风格-变体1 (Content #1581) - 系统架构 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0F172A;">
    <!-- 顶部cyan线 -->
    <div class="absolute top-0 left-0 right-0 h-[3px]" style="background-color: #06B6D4;"></div>
    <!-- 网格装饰 -->
    <div class="absolute top-0 right-0 w-[300px] h-[300px] opacity-5" style="background-image: repeating-linear-gradient(0deg, #06B6D4 0px, #06B6D4 1px, transparent 1px, transparent 40px), repeating-linear-gradient(90deg, #06B6D4 0px, #06B6D4 1px, transparent 1px, transparent 40px);"></div>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题区 -->
        <div class="flex items-center gap-4 mb-5">
            <div class="w-10 h-10 flex items-center justify-center flex-shrink-0" style="border: 2px solid #06B6D4;">
                <span class="font-mono text-[14px] font-bold" style="color: #06B6D4;">&gt;_</span>
            </div>
            <div>
                <h1 class="text-[38px] font-bold text-white">系统架构设计</h1>
                <p class="text-[13px] font-mono tracking-widest" style="color: #475569;">SYSTEM ARCHITECTURE DESIGN</p>
            </div>
        </div>
        <!-- 描述 -->
        <div class="p-4 mb-5 font-mono" style="background-color: #1E293B; border-left: 3px solid #06B6D4;">
            <p class="text-[14px] leading-relaxed" style="color: #94A3B8;"><span style="color: #06B6D4;">// </span>采用微服务架构，通过服务网格实现高可用性与弹性扩展。各服务独立部署、独立扩缩容，确保系统整体稳定性。</p>
        </div>
        <!-- 三层架构 -->
        <div class="flex-1 flex flex-col gap-3">
            <!-- 接入层 -->
            <div class="flex gap-3 flex-1">
                <div class="w-[120px] flex-shrink-0 flex items-center justify-center" style="background-color: #06B6D4;">
                    <span class="text-[14px] font-bold font-mono" style="color: #0F172A;">GATEWAY</span>
                </div>
                <div class="flex-1 flex gap-3">
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #06B6D4;">API Gateway</p>
                        <p class="text-[13px]" style="color: #64748B;">统一入口、路由分发、限流熔断</p>
                    </div>
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #06B6D4;">Load Balancer</p>
                        <p class="text-[13px]" style="color: #64748B;">流量分发、健康检查、会话保持</p>
                    </div>
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #06B6D4;">Auth Service</p>
                        <p class="text-[13px]" style="color: #64748B;">身份认证、权限校验、Token 管理</p>
                    </div>
                </div>
            </div>
            <!-- SVG 连接线 -->
            <svg class="w-full h-4 flex-shrink-0" viewBox="0 0 1200 16"><line x1="600" y1="0" x2="600" y2="16" stroke="#06B6D4" stroke-width="1" stroke-dasharray="4,3"/></svg>
            <!-- 服务层 -->
            <div class="flex gap-3 flex-1">
                <div class="w-[120px] flex-shrink-0 flex items-center justify-center" style="background-color: #3B82F6;">
                    <span class="text-[14px] font-bold font-mono text-white">SERVICE</span>
                </div>
                <div class="flex-1 flex gap-3">
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #3B82F6;">User Service</p>
                        <p class="text-[13px]" style="color: #64748B;">用户管理、资料维护</p>
                    </div>
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #3B82F6;">Order Service</p>
                        <p class="text-[13px]" style="color: #64748B;">订单处理、状态流转</p>
                    </div>
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #3B82F6;">Payment Service</p>
                        <p class="text-[13px]" style="color: #64748B;">支付结算、对账管理</p>
                    </div>
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #3B82F6;">Notify Service</p>
                        <p class="text-[13px]" style="color: #64748B;">消息推送、事件通知</p>
                    </div>
                </div>
            </div>
            <!-- SVG 连接线 -->
            <svg class="w-full h-4 flex-shrink-0" viewBox="0 0 1200 16"><line x1="600" y1="0" x2="600" y2="16" stroke="#3B82F6" stroke-width="1" stroke-dasharray="4,3"/></svg>
            <!-- 数据层 -->
            <div class="flex gap-3 flex-1">
                <div class="w-[120px] flex-shrink-0 flex items-center justify-center" style="background-color: #8B5CF6;">
                    <span class="text-[14px] font-bold font-mono text-white">DATA</span>
                </div>
                <div class="flex-1 flex gap-3">
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #8B5CF6;">PostgreSQL</p>
                        <p class="text-[13px]" style="color: #64748B;">关系型数据持久化</p>
                    </div>
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #8B5CF6;">Redis Cluster</p>
                        <p class="text-[13px]" style="color: #64748B;">缓存、会话、队列</p>
                    </div>
                    <div class="flex-1 p-4" style="background-color: #1E293B;">
                        <p class="text-[14px] font-bold font-mono mb-1" style="color: #8B5CF6;">Elasticsearch</p>
                        <p class="text-[13px]" style="color: #64748B;">全文检索、日志分析</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- 底部注释 -->
        <div class="mt-4 flex items-center justify-between">
            <p class="text-[12px] font-mono" style="color: #475569;">// Architecture v3.2 | Kubernetes + Istio Service Mesh</p>
            <p class="text-[12px] font-mono" style="color: #475569;">SLA: 99.99%</p>
        </div>
    </div>
</div>