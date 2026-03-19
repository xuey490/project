<!-- Template: 科技风格-变体2 (Content #1582) - DevOps 流水线 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #020617;">
    <!-- 左侧cyan线 -->
    <div class="absolute top-0 left-0 w-[3px] h-full" style="background-color: #06B6D4;"></div>
    <div class="absolute inset-0 flex flex-col pl-16 pr-14 py-10">
        <!-- 标题 -->
        <div class="flex items-center gap-4 mb-5">
            <div class="px-3 py-1" style="background-color: #06B6D4;">
                <span class="font-mono text-[14px] font-bold" style="color: #020617;">CI/CD</span>
            </div>
            <div>
                <h1 class="text-[38px] font-bold text-white">DevOps 流水线</h1>
                <p class="text-[13px] font-mono" style="color: #475569;">CONTINUOUS INTEGRATION & DEPLOYMENT</p>
            </div>
        </div>
        <!-- Pipeline 流程 -->
        <div class="flex gap-2 mb-5 items-stretch">
            <div class="flex-1 p-4" style="background-color: #1E293B; border-top: 3px solid #06B6D4;">
                <p class="text-[13px] font-mono font-bold mb-2" style="color: #06B6D4;">01 CODE</p>
                <p class="text-[13px]" style="color: #64748B;">代码提交</p>
                <p class="text-[12px]" style="color: #475569;">Git Push</p>
            </div>
            <div class="flex items-center flex-shrink-0"><span class="font-mono text-[14px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-4" style="background-color: #1E293B; border-top: 3px solid #06B6D4;">
                <p class="text-[13px] font-mono font-bold mb-2" style="color: #06B6D4;">02 BUILD</p>
                <p class="text-[13px]" style="color: #64748B;">编译构建</p>
                <p class="text-[12px]" style="color: #475569;">Docker Build</p>
            </div>
            <div class="flex items-center flex-shrink-0"><span class="font-mono text-[14px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-4" style="background-color: #1E293B; border-top: 3px solid #3B82F6;">
                <p class="text-[13px] font-mono font-bold mb-2" style="color: #3B82F6;">03 TEST</p>
                <p class="text-[13px]" style="color: #64748B;">自动化测试</p>
                <p class="text-[12px]" style="color: #475569;">Unit + E2E</p>
            </div>
            <div class="flex items-center flex-shrink-0"><span class="font-mono text-[14px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-4" style="background-color: #1E293B; border-top: 3px solid #3B82F6;">
                <p class="text-[13px] font-mono font-bold mb-2" style="color: #3B82F6;">04 STAGE</p>
                <p class="text-[13px]" style="color: #64748B;">预发布环境</p>
                <p class="text-[12px]" style="color: #475569;">Staging Deploy</p>
            </div>
            <div class="flex items-center flex-shrink-0"><span class="font-mono text-[14px]" style="color: #334155;">></span></div>
            <div class="flex-1 p-4" style="background-color: #1E293B; border-top: 3px solid #10B981;">
                <p class="text-[13px] font-mono font-bold mb-2" style="color: #10B981;">05 DEPLOY</p>
                <p class="text-[13px]" style="color: #64748B;">生产发布</p>
                <p class="text-[12px]" style="color: #475569;">K8s Rolling</p>
            </div>
        </div>
        <!-- 中间详情区 -->
        <div class="flex-1 flex gap-5">
            <!-- 左侧工具链 -->
            <div class="flex-1 flex flex-col gap-3">
                <h3 class="text-[20px] font-bold mb-1 text-white">工具链配置</h3>
                <div class="p-4 font-mono" style="background-color: #1E293B;">
                    <p class="text-[13px] mb-1"><span style="color: #06B6D4;">version_control:</span> <span style="color: #94A3B8;">GitLab CE</span></p>
                    <p class="text-[13px] mb-1"><span style="color: #06B6D4;">ci_engine:</span> <span style="color: #94A3B8;">Jenkins Pipeline</span></p>
                    <p class="text-[13px] mb-1"><span style="color: #06B6D4;">container:</span> <span style="color: #94A3B8;">Docker + Harbor</span></p>
                    <p class="text-[13px] mb-1"><span style="color: #06B6D4;">orchestration:</span> <span style="color: #94A3B8;">Kubernetes v1.28</span></p>
                    <p class="text-[13px] mb-1"><span style="color: #06B6D4;">monitoring:</span> <span style="color: #94A3B8;">Prometheus + Grafana</span></p>
                    <p class="text-[13px]"><span style="color: #06B6D4;">logging:</span> <span style="color: #94A3B8;">ELK Stack</span></p>
                </div>
                <div class="p-4" style="background-color: #1E293B;">
                    <p class="text-[14px] font-bold mb-2" style="color: #06B6D4;">部署策略</p>
                    <div class="flex gap-3">
                        <div class="flex-1 p-3" style="background-color: #0F172A;">
                            <p class="text-[13px] font-bold" style="color: #3B82F6;">Rolling</p>
                            <p class="text-[12px]" style="color: #475569;">渐进式更新</p>
                        </div>
                        <div class="flex-1 p-3" style="background-color: #0F172A;">
                            <p class="text-[13px] font-bold" style="color: #3B82F6;">Blue/Green</p>
                            <p class="text-[12px]" style="color: #475569;">双环境切换</p>
                        </div>
                        <div class="flex-1 p-3" style="background-color: #0F172A;">
                            <p class="text-[13px] font-bold" style="color: #3B82F6;">Canary</p>
                            <p class="text-[12px]" style="color: #475569;">灰度发布</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 右侧指标 -->
            <div class="w-[340px] flex-shrink-0 flex flex-col gap-3">
                <div class="p-5" style="background-color: #1E293B; border: 1px solid #06B6D4;">
                    <p class="text-[13px] font-mono mb-1" style="color: #06B6D4;">METRICS</p>
                    <div class="space-y-3 mt-3">
                        <div><p class="text-[12px]" style="color: #475569;">部署频率</p><p class="text-[24px] font-bold text-white">15次/天</p></div>
                        <div class="h-[1px]" style="background-color: #1E293B; border-top: 1px solid #334155;"></div>
                        <div><p class="text-[12px]" style="color: #475569;">构建时间</p><p class="text-[24px] font-bold text-white">4.2min</p></div>
                        <div class="h-[1px]" style="background-color: #1E293B; border-top: 1px solid #334155;"></div>
                        <div><p class="text-[12px]" style="color: #475569;">发布成功率</p><p class="text-[24px] font-bold text-white">99.8%</p></div>
                    </div>
                </div>
                <div class="p-4" style="background-color: #1E293B;">
                    <p class="text-[12px] font-mono" style="color: #475569;">// 自动化覆盖率 95%+ | 零停机部署</p>
                </div>
            </div>
        </div>
    </div>
</div>