<!-- Template: 科技风格-变体3 (Content #1583) - API 设计规范 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0F172A;">
    <!-- 底部cyan线 -->
    <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background-color: #06B6D4;"></div>
    <div class="absolute inset-0 flex flex-col px-14 py-10">
        <!-- 标题 -->
        <div class="flex items-center gap-4 mb-5">
            <div class="px-3 py-1" style="background-color: #3B82F6;">
                <span class="font-mono text-[14px] font-bold text-white">API</span>
            </div>
            <div>
                <h1 class="text-[38px] font-bold text-white">RESTful API 设计规范</h1>
                <p class="text-[13px] font-mono" style="color: #475569;">API DESIGN SPECIFICATION v2.0</p>
            </div>
        </div>
        <!-- 内容 -->
        <div class="flex-1 flex gap-5">
            <!-- 左侧：请求示例 -->
            <div class="flex-1 flex flex-col gap-4">
                <!-- 端点列表 -->
                <div class="p-5" style="background-color: #1E293B;">
                    <p class="text-[16px] font-bold mb-3 text-white">端点设计</p>
                    <div class="space-y-2 font-mono">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 text-[12px] font-bold" style="background-color: #10B981; color: #022C22;">GET</span>
                            <span class="text-[13px]" style="color: #94A3B8;">/api/v2/users</span>
                            <span class="text-[12px] ml-auto" style="color: #475569;">获取用户列表</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 text-[12px] font-bold" style="background-color: #3B82F6; color: white;">POST</span>
                            <span class="text-[13px]" style="color: #94A3B8;">/api/v2/users</span>
                            <span class="text-[12px] ml-auto" style="color: #475569;">创建新用户</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 text-[12px] font-bold" style="background-color: #F59E0B; color: #451A03;">PUT</span>
                            <span class="text-[13px]" style="color: #94A3B8;">/api/v2/users/:id</span>
                            <span class="text-[12px] ml-auto" style="color: #475569;">更新用户信息</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 text-[12px] font-bold" style="background-color: #EF4444; color: white;">DEL</span>
                            <span class="text-[13px]" style="color: #94A3B8;">/api/v2/users/:id</span>
                            <span class="text-[12px] ml-auto" style="color: #475569;">删除用户</span>
                        </div>
                    </div>
                </div>
                <!-- 响应示例 -->
                <div class="flex-1 p-5 font-mono" style="background-color: #1E293B;">
                    <p class="text-[14px] font-bold mb-3" style="color: #06B6D4;">// Response Example</p>
                    <div class="text-[13px] leading-relaxed" style="color: #94A3B8;">
                        <p>{</p>
                        <p class="pl-4"><span style="color: #06B6D4;">"code"</span>: <span style="color: #10B981;">200</span>,</p>
                        <p class="pl-4"><span style="color: #06B6D4;">"message"</span>: <span style="color: #F59E0B;">"success"</span>,</p>
                        <p class="pl-4"><span style="color: #06B6D4;">"data"</span>: {</p>
                        <p class="pl-8"><span style="color: #06B6D4;">"id"</span>: <span style="color: #10B981;">1024</span>,</p>
                        <p class="pl-8"><span style="color: #06B6D4;">"name"</span>: <span style="color: #F59E0B;">"John"</span>,</p>
                        <p class="pl-8"><span style="color: #06B6D4;">"role"</span>: <span style="color: #F59E0B;">"admin"</span></p>
                        <p class="pl-4">},</p>
                        <p class="pl-4"><span style="color: #06B6D4;">"timestamp"</span>: <span style="color: #10B981;">1706745600</span></p>
                        <p>}</p>
                    </div>
                </div>
            </div>
            <!-- 右侧：规范要点 -->
            <div class="w-[420px] flex-shrink-0 flex flex-col gap-4">
                <div class="p-5" style="background-color: #1E293B; border-left: 3px solid #06B6D4;">
                    <h3 class="text-[18px] font-bold mb-3 text-white">设计原则</h3>
                    <div class="space-y-3">
                        <div class="flex gap-3 items-start">
                            <span class="text-[12px] font-mono font-bold px-2 py-0.5 flex-shrink-0" style="background-color: #06B6D4; color: #0F172A;">01</span>
                            <div><p class="text-[14px] font-bold text-white">资源导向</p><p class="text-[12px]" style="color: #64748B;">URL 表示资源，HTTP 方法表示操作</p></div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="text-[12px] font-mono font-bold px-2 py-0.5 flex-shrink-0" style="background-color: #06B6D4; color: #0F172A;">02</span>
                            <div><p class="text-[14px] font-bold text-white">版本控制</p><p class="text-[12px]" style="color: #64748B;">URI 路径中包含版本号 /v2/</p></div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="text-[12px] font-mono font-bold px-2 py-0.5 flex-shrink-0" style="background-color: #06B6D4; color: #0F172A;">03</span>
                            <div><p class="text-[14px] font-bold text-white">统一响应</p><p class="text-[12px]" style="color: #64748B;">标准化 JSON 响应格式</p></div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="text-[12px] font-mono font-bold px-2 py-0.5 flex-shrink-0" style="background-color: #06B6D4; color: #0F172A;">04</span>
                            <div><p class="text-[14px] font-bold text-white">错误处理</p><p class="text-[12px]" style="color: #64748B;">语义化 HTTP 状态码 + 错误详情</p></div>
                        </div>
                    </div>
                </div>
                <!-- 状态码参考 -->
                <div class="flex-1 p-5" style="background-color: #1E293B;">
                    <p class="text-[14px] font-bold mb-3" style="color: #06B6D4;">HTTP Status Codes</p>
                    <div class="space-y-2 font-mono text-[13px]">
                        <div class="flex justify-between"><span style="color: #10B981;">200 OK</span><span style="color: #475569;">请求成功</span></div>
                        <div class="flex justify-between"><span style="color: #10B981;">201 Created</span><span style="color: #475569;">资源创建</span></div>
                        <div class="flex justify-between"><span style="color: #F59E0B;">400 Bad Request</span><span style="color: #475569;">参数错误</span></div>
                        <div class="flex justify-between"><span style="color: #F59E0B;">401 Unauthorized</span><span style="color: #475569;">未认证</span></div>
                        <div class="flex justify-between"><span style="color: #F59E0B;">403 Forbidden</span><span style="color: #475569;">无权限</span></div>
                        <div class="flex justify-between"><span style="color: #EF4444;">404 Not Found</span><span style="color: #475569;">资源不存在</span></div>
                        <div class="flex justify-between"><span style="color: #EF4444;">500 Internal</span><span style="color: #475569;">服务器异常</span></div>
                    </div>
                </div>
                <div class="p-3 font-mono" style="background-color: #1E293B;">
                    <p class="text-[12px]" style="color: #475569;">// OpenAPI 3.0 Spec | JWT Auth</p>
                </div>
            </div>
        </div>
    </div>
</div>