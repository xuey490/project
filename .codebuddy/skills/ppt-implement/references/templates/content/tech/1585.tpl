<!-- Template: 科技风格-变体5 (Content #1585) - 安全架构 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0F172A;">
    <!-- 右侧线 -->
    <div class="absolute top-0 right-0 w-[3px] h-full" style="background-color: #EF4444;"></div>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题 -->
        <div class="flex items-center gap-4 mb-5">
            <div class="w-10 h-10 flex items-center justify-center flex-shrink-0" style="background-color: #EF4444;">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5C17.944 5.328 18 5.661 18 6v4c0 3.314-2.08 6.32-5.2 7.8a.75.75 0 01-.6 0C9.08 16.32 7 13.314 7 10V6c0-.339.056-.672.166-1.001z" clip-rule="evenodd"/></svg>
            </div>
            <div>
                <h1 class="text-[38px] font-bold text-white">安全防护体系</h1>
                <p class="text-[13px] font-mono" style="color: #475569;">SECURITY ARCHITECTURE</p>
            </div>
            <div class="ml-auto flex gap-2 items-center">
                <div class="w-2 h-2 rounded-full" style="background-color: #10B981;"></div>
                <span class="font-mono text-[12px]" style="color: #10B981;">SECURE</span>
            </div>
        </div>
        <!-- 4层防护 -->
        <div class="flex-1 flex gap-4">
            <div class="flex-1 flex flex-col gap-3">
                <!-- 层级1 -->
                <div class="flex-1 flex gap-3">
                    <div class="w-[100px] flex-shrink-0 flex items-center justify-center" style="background-color: #EF4444;">
                        <span class="text-[13px] font-bold font-mono text-white" style="writing-mode: vertical-lr; transform: rotate(180deg);">PERIMETER</span>
                    </div>
                    <div class="flex-1 flex gap-3">
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #EF4444;">WAF</p>
                            <p class="text-[12px]" style="color: #64748B;">Web 应用防火墙，拦截 SQL 注入、XSS</p>
                        </div>
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #EF4444;">DDoS Shield</p>
                            <p class="text-[12px]" style="color: #64748B;">抗 DDoS 攻击，流量清洗</p>
                        </div>
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #EF4444;">IDS/IPS</p>
                            <p class="text-[12px]" style="color: #64748B;">入侵检测与防御系统</p>
                        </div>
                    </div>
                </div>
                <!-- 层级2 -->
                <div class="flex-1 flex gap-3">
                    <div class="w-[100px] flex-shrink-0 flex items-center justify-center" style="background-color: #F59E0B;">
                        <span class="text-[13px] font-bold font-mono" style="color: #451A03; writing-mode: vertical-lr; transform: rotate(180deg);">IDENTITY</span>
                    </div>
                    <div class="flex-1 flex gap-3">
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #F59E0B;">OAuth 2.0</p>
                            <p class="text-[12px]" style="color: #64748B;">标准化授权框架</p>
                        </div>
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #F59E0B;">MFA</p>
                            <p class="text-[12px]" style="color: #64748B;">多因素身份验证</p>
                        </div>
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #F59E0B;">RBAC</p>
                            <p class="text-[12px]" style="color: #64748B;">基于角色的访问控制</p>
                        </div>
                    </div>
                </div>
                <!-- 层级3 -->
                <div class="flex-1 flex gap-3">
                    <div class="w-[100px] flex-shrink-0 flex items-center justify-center" style="background-color: #06B6D4;">
                        <span class="text-[13px] font-bold font-mono" style="color: #0F172A; writing-mode: vertical-lr; transform: rotate(180deg);">DATA</span>
                    </div>
                    <div class="flex-1 flex gap-3">
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #06B6D4;">AES-256</p>
                            <p class="text-[12px]" style="color: #64748B;">数据静态加密</p>
                        </div>
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #06B6D4;">TLS 1.3</p>
                            <p class="text-[12px]" style="color: #64748B;">传输层加密</p>
                        </div>
                        <div class="flex-1 p-4" style="background-color: #1E293B;">
                            <p class="text-[13px] font-bold font-mono mb-1" style="color: #06B6D4;">KMS</p>
                            <p class="text-[12px]" style="color: #64748B;">密钥管理服务</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 右侧监控面板 -->
            <div class="w-[300px] flex-shrink-0 flex flex-col gap-3">
                <div class="p-4" style="background-color: #1E293B; border: 1px solid #EF4444;">
                    <p class="font-mono text-[13px] font-bold mb-3" style="color: #EF4444;">THREAT MONITOR</p>
                    <div class="space-y-2">
                        <div class="flex justify-between"><span class="text-[12px]" style="color: #64748B;">拦截攻击</span><span class="text-[16px] font-bold font-mono" style="color: #EF4444;">12.4K</span></div>
                        <div class="flex justify-between"><span class="text-[12px]" style="color: #64748B;">扫描漏洞</span><span class="text-[16px] font-bold font-mono" style="color: #F59E0B;">3</span></div>
                        <div class="flex justify-between"><span class="text-[12px]" style="color: #64748B;">安全评分</span><span class="text-[16px] font-bold font-mono" style="color: #10B981;">A+</span></div>
                    </div>
                </div>
                <div class="flex-1 p-4" style="background-color: #1E293B;">
                    <p class="font-mono text-[13px] font-bold mb-3" style="color: #06B6D4;">COMPLIANCE</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2"><div class="w-2 h-2" style="background-color: #10B981;"></div><span class="text-[13px]" style="color: #94A3B8;">ISO 27001</span></div>
                        <div class="flex items-center gap-2"><div class="w-2 h-2" style="background-color: #10B981;"></div><span class="text-[13px]" style="color: #94A3B8;">SOC 2 Type II</span></div>
                        <div class="flex items-center gap-2"><div class="w-2 h-2" style="background-color: #10B981;"></div><span class="text-[13px]" style="color: #94A3B8;">GDPR</span></div>
                        <div class="flex items-center gap-2"><div class="w-2 h-2" style="background-color: #10B981;"></div><span class="text-[13px]" style="color: #94A3B8;">等保三级</span></div>
                    </div>
                </div>
                <div class="p-3 font-mono" style="background-color: #1E293B;">
                    <p class="text-[12px]" style="color: #475569;">// Zero Trust Architecture</p>
                </div>
            </div>
        </div>
    </div>
</div>