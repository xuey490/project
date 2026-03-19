<!-- Template: 科技风格-变体6 (Content #1586) - 性能监控 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #020617;">
    <!-- 顶部线 -->
    <div class="absolute top-0 left-0 right-0 h-[3px]" style="background-color: #10B981;"></div>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题 -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 flex items-center justify-center flex-shrink-0" style="background-color: #10B981;">
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="#020617"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
                </div>
                <div>
                    <h1 class="text-[38px] font-bold text-white">性能监控中心</h1>
                    <p class="text-[13px] font-mono" style="color: #475569;">PERFORMANCE MONITORING CENTER</p>
                </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-1" style="background-color: #1E293B;">
                <div class="w-2 h-2 rounded-full" style="background-color: #10B981;"></div>
                <span class="font-mono text-[12px]" style="color: #10B981;">LIVE</span>
            </div>
        </div>
        <!-- 4个核心指标 -->
        <div class="grid grid-cols-4 gap-4 mb-5">
            <div class="p-4" style="background-color: #1E293B; border-top: 3px solid #10B981;">
                <p class="text-[12px] font-mono mb-1" style="color: #475569;">Response Time</p>
                <p class="text-[28px] font-bold font-mono text-white">42ms</p>
                <p class="text-[12px] font-mono" style="color: #10B981;">P99: 120ms</p>
            </div>
            <div class="p-4" style="background-color: #1E293B; border-top: 3px solid #06B6D4;">
                <p class="text-[12px] font-mono mb-1" style="color: #475569;">Throughput</p>
                <p class="text-[28px] font-bold font-mono text-white">8.5K</p>
                <p class="text-[12px] font-mono" style="color: #06B6D4;">req/sec</p>
            </div>
            <div class="p-4" style="background-color: #1E293B; border-top: 3px solid #3B82F6;">
                <p class="text-[12px] font-mono mb-1" style="color: #475569;">Error Rate</p>
                <p class="text-[28px] font-bold font-mono text-white">0.02%</p>
                <p class="text-[12px] font-mono" style="color: #3B82F6;">Target: &lt;0.1%</p>
            </div>
            <div class="p-4" style="background-color: #1E293B; border-top: 3px solid #8B5CF6;">
                <p class="text-[12px] font-mono mb-1" style="color: #475569;">Uptime</p>
                <p class="text-[28px] font-bold font-mono text-white">99.99%</p>
                <p class="text-[12px] font-mono" style="color: #8B5CF6;">30-day SLA</p>
            </div>
        </div>
        <!-- 中间区域 -->
        <div class="flex-1 flex gap-5">
            <!-- 左侧：服务健康状态 -->
            <div class="flex-1 flex flex-col gap-4">
                <div class="p-5" style="background-color: #1E293B;">
                    <p class="text-[16px] font-bold mb-3 text-white">服务健康状态</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-2" style="background-color: #0F172A;">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: #10B981;"></div>
                            <span class="text-[13px] font-mono flex-1" style="color: #94A3B8;">api-gateway</span>
                            <span class="text-[12px] font-mono" style="color: #10B981;">healthy</span>
                            <span class="text-[12px] font-mono" style="color: #475569;">12ms</span>
                        </div>
                        <div class="flex items-center gap-3 p-2" style="background-color: #0F172A;">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: #10B981;"></div>
                            <span class="text-[13px] font-mono flex-1" style="color: #94A3B8;">user-service</span>
                            <span class="text-[12px] font-mono" style="color: #10B981;">healthy</span>
                            <span class="text-[12px] font-mono" style="color: #475569;">28ms</span>
                        </div>
                        <div class="flex items-center gap-3 p-2" style="background-color: #0F172A;">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: #F59E0B;"></div>
                            <span class="text-[13px] font-mono flex-1" style="color: #94A3B8;">order-service</span>
                            <span class="text-[12px] font-mono" style="color: #F59E0B;">degraded</span>
                            <span class="text-[12px] font-mono" style="color: #475569;">85ms</span>
                        </div>
                        <div class="flex items-center gap-3 p-2" style="background-color: #0F172A;">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: #10B981;"></div>
                            <span class="text-[13px] font-mono flex-1" style="color: #94A3B8;">payment-service</span>
                            <span class="text-[12px] font-mono" style="color: #10B981;">healthy</span>
                            <span class="text-[12px] font-mono" style="color: #475569;">35ms</span>
                        </div>
                        <div class="flex items-center gap-3 p-2" style="background-color: #0F172A;">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: #10B981;"></div>
                            <span class="text-[13px] font-mono flex-1" style="color: #94A3B8;">notify-service</span>
                            <span class="text-[12px] font-mono" style="color: #10B981;">healthy</span>
                            <span class="text-[12px] font-mono" style="color: #475569;">15ms</span>
                        </div>
                    </div>
                </div>
                <!-- 资源使用率 -->
                <div class="p-4 flex gap-4" style="background-color: #1E293B;">
                    <div class="flex-1">
                        <p class="text-[12px] font-mono mb-1" style="color: #475569;">CPU Usage</p>
                        <div class="h-2 mb-1" style="background-color: #0F172A;"><div class="h-full" style="width: 68%; background-color: #06B6D4;"></div></div>
                        <p class="text-[12px] font-mono" style="color: #06B6D4;">68%</p>
                    </div>
                    <div class="flex-1">
                        <p class="text-[12px] font-mono mb-1" style="color: #475569;">Memory</p>
                        <div class="h-2 mb-1" style="background-color: #0F172A;"><div class="h-full" style="width: 72%; background-color: #3B82F6;"></div></div>
                        <p class="text-[12px] font-mono" style="color: #3B82F6;">72%</p>
                    </div>
                    <div class="flex-1">
                        <p class="text-[12px] font-mono mb-1" style="color: #475569;">Disk I/O</p>
                        <div class="h-2 mb-1" style="background-color: #0F172A;"><div class="h-full" style="width: 45%; background-color: #8B5CF6;"></div></div>
                        <p class="text-[12px] font-mono" style="color: #8B5CF6;">45%</p>
                    </div>
                </div>
            </div>
            <!-- 右侧告警 -->
            <div class="w-[340px] flex-shrink-0 flex flex-col gap-3">
                <div class="p-4" style="background-color: #1E293B; border: 1px solid #F59E0B;">
                    <p class="font-mono text-[13px] font-bold mb-2" style="color: #F59E0B;">ACTIVE ALERTS</p>
                    <div class="space-y-2">
                        <div class="p-2" style="background-color: #0F172A;">
                            <p class="text-[12px] font-mono" style="color: #F59E0B;">[WARN] order-service</p>
                            <p class="text-[12px]" style="color: #475569;">响应延迟超过阈值 80ms</p>
                        </div>
                        <div class="p-2" style="background-color: #0F172A;">
                            <p class="text-[12px] font-mono" style="color: #F59E0B;">[WARN] redis-cluster</p>
                            <p class="text-[12px]" style="color: #475569;">内存使用率达到 85%</p>
                        </div>
                    </div>
                </div>
                <div class="flex-1 p-4" style="background-color: #1E293B;">
                    <p class="font-mono text-[13px] font-bold mb-2" style="color: #10B981;">RECENT EVENTS</p>
                    <div class="space-y-2 text-[12px] font-mono" style="color: #64748B;">
                        <p><span style="color: #475569;">14:32</span> Auto-scale +2 pods</p>
                        <p><span style="color: #475569;">14:15</span> Deploy v3.2.1 成功</p>
                        <p><span style="color: #475569;">13:58</span> SSL 证书自动续期</p>
                        <p><span style="color: #475569;">13:42</span> 缓存预热完成</p>
                    </div>
                </div>
                <div class="p-3 font-mono" style="background-color: #1E293B;">
                    <p class="text-[12px]" style="color: #475569;">// Prometheus + Grafana + PagerDuty</p>
                </div>
            </div>
        </div>
    </div>
</div>