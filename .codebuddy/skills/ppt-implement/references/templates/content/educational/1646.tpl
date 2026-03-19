<!-- Template: 教育风格-变体6 (Content #1646) - 计算机编程·Python入门 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #0F172A;">
    <div class="absolute inset-0 flex flex-col">
        <!-- 顶部标题栏 -->
        <div class="h-[70px] flex items-center justify-between px-14" style="background-color: #1E293B; border-bottom: 2px solid #334155;">
            <div class="flex items-center gap-6">
                <h1 class="text-[30px] font-bold font-mono" style="color: #FFFFFF;">Python 入门</h1>
                <div class="w-[1px] h-7" style="background-color: #475569;"></div>
                <p class="text-[14px] font-mono" style="color: #22C55E;">第1课：变量与数据类型</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full" style="background-color: #EF4444;"></div>
                <div class="w-3 h-3 rounded-full" style="background-color: #EAB308;"></div>
                <div class="w-3 h-3 rounded-full" style="background-color: #22C55E;"></div>
            </div>
        </div>

        <!-- 主内容区 -->
        <div class="flex-1 flex">
            <!-- 左侧代码区 -->
            <div class="w-[680px] flex flex-col px-10 py-8 gap-5" style="border-right: 1px solid #334155;">
                <!-- 代码编辑器 -->
                <div class="flex-1 flex flex-col" style="background-color: #1E293B;">
                    <div class="flex items-center justify-between px-4 py-2" style="border-bottom: 1px solid #334155;">
                        <span class="text-[12px] font-mono" style="color: #94A3B8;">example.py</span>
                        <span class="text-[12px] font-mono" style="color: #475569;">Python 3.12</span>
                    </div>
                    <div class="flex-1 p-5 font-mono text-[14px] space-y-1">
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">1</span>
                            <span style="color: #64748B;"># 定义变量</span>
                        </div>
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">2</span>
                            <span><span style="color: #93C5FD;">name</span> <span style="color: #F8FAFC;">=</span> <span style="color: #86EFAC;">"小明"</span></span>
                        </div>
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">3</span>
                            <span><span style="color: #93C5FD;">age</span> <span style="color: #F8FAFC;">=</span> <span style="color: #FDBA74;">10</span></span>
                        </div>
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">4</span>
                            <span><span style="color: #93C5FD;">height</span> <span style="color: #F8FAFC;">=</span> <span style="color: #FDBA74;">1.45</span></span>
                        </div>
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">5</span>
                            <span><span style="color: #93C5FD;">is_student</span> <span style="color: #F8FAFC;">=</span> <span style="color: #C4B5FD;">True</span></span>
                        </div>
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">6</span>
                            <span></span>
                        </div>
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">7</span>
                            <span style="color: #64748B;"># 打印信息</span>
                        </div>
                        <div class="flex">
                            <span class="w-8 text-right mr-4" style="color: #475569;">8</span>
                            <span><span style="color: #F9A8D4;">print</span><span style="color: #F8FAFC;">(</span><span style="color: #86EFAC;">f"我叫</span><span style="color: #93C5FD;">{name}</span><span style="color: #86EFAC;">"</span><span style="color: #F8FAFC;">)</span></span>
                        </div>
                    </div>
                </div>

                <!-- 运行结果 -->
                <div style="background-color: #1E293B; border-left: 3px solid #22C55E;">
                    <div class="px-4 py-2" style="border-bottom: 1px solid #334155;">
                        <span class="text-[12px] font-mono" style="color: #22C55E;">运行结果</span>
                    </div>
                    <div class="p-4 font-mono text-[14px]" style="color: #22C55E;">
                        我叫小明
                    </div>
                </div>
            </div>

            <!-- 右侧知识点 -->
            <div class="flex-1 flex flex-col px-10 py-8 gap-5">
                <h3 class="text-[20px] font-bold" style="color: #FFFFFF;">数据类型</h3>

                <div class="space-y-3">
                    <div class="flex items-center gap-4 p-4" style="background-color: #1E293B; border-left: 3px solid #86EFAC;">
                        <div class="w-[90px] flex-shrink-0">
                            <p class="text-[14px] font-mono font-bold" style="color: #86EFAC;">str</p>
                        </div>
                        <div>
                            <p class="text-[14px] font-semibold" style="color: #F1F5F9;">字符串</p>
                            <p class="text-[13px]" style="color: #94A3B8;">用引号包裹的文本，如 "hello"</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4" style="background-color: #1E293B; border-left: 3px solid #FDBA74;">
                        <div class="w-[90px] flex-shrink-0">
                            <p class="text-[14px] font-mono font-bold" style="color: #FDBA74;">int</p>
                        </div>
                        <div>
                            <p class="text-[14px] font-semibold" style="color: #F1F5F9;">整数</p>
                            <p class="text-[13px]" style="color: #94A3B8;">没有小数点的数字，如 10, -5</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4" style="background-color: #1E293B; border-left: 3px solid #FDE047;">
                        <div class="w-[90px] flex-shrink-0">
                            <p class="text-[14px] font-mono font-bold" style="color: #FDE047;">float</p>
                        </div>
                        <div>
                            <p class="text-[14px] font-semibold" style="color: #F1F5F9;">浮点数</p>
                            <p class="text-[13px]" style="color: #94A3B8;">带小数点的数字，如 3.14</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4" style="background-color: #1E293B; border-left: 3px solid #C4B5FD;">
                        <div class="w-[90px] flex-shrink-0">
                            <p class="text-[14px] font-mono font-bold" style="color: #C4B5FD;">bool</p>
                        </div>
                        <div>
                            <p class="text-[14px] font-semibold" style="color: #F1F5F9;">布尔值</p>
                            <p class="text-[13px]" style="color: #94A3B8;">只有 True 或 False 两个值</p>
                        </div>
                    </div>
                </div>

                <!-- 编程小贴士 -->
                <div class="p-5 mt-auto" style="background-color: #1E293B; border-left: 3px solid #3B82F6;">
                    <h4 class="text-[14px] font-semibold mb-3" style="color: #60A5FA;">编程小贴士</h4>
                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="text-[12px]" style="color: #3B82F6;">></span>
                            <p class="text-[13px]" style="color: #CBD5E1;">变量名要有意义，使用英文或拼音</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[12px]" style="color: #3B82F6;">></span>
                            <p class="text-[13px]" style="color: #CBD5E1;">多个单词用下划线连接：is_student</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[12px]" style="color: #3B82F6;">></span>
                            <p class="text-[13px]" style="color: #CBD5E1;">Python 会自动识别数据类型</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 底部信息条 -->
        <div class="h-[44px] flex items-center justify-between px-14" style="background-color: #2D3A8C;">
            <p class="text-[13px]" style="color: rgba(255,255,255,0.8);">Python 编程入门 · 适合零基础学习者</p>
            <div class="flex items-center gap-6">
                <span class="text-[13px]" style="color: rgba(255,255,255,0.6);">难度：入门</span>
                <span class="text-[13px]" style="color: rgba(255,255,255,0.6);">课时：45分钟</span>
            </div>
        </div>
    </div>
</div>