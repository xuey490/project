<!-- Template: 教育风格-变体16 (Content #1656) - JavaScript基础·变量与数据类型 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #1E293B;">
    <div class="absolute inset-0 flex flex-col">
        <!-- 顶部标题栏 -->
        <div class="h-[80px] flex items-center justify-between px-16" style="background-color: #2D3A8C;">
            <div class="flex items-center gap-6">
                <h1 class="text-[32px] font-bold font-mono" style="color: #FFFFFF;">JavaScript 基础</h1>
                <div class="w-[1px] h-8" style="background-color: rgba(255,255,255,0.2);"></div>
                <p class="text-[16px] font-mono" style="color: rgba(255,255,255,0.7);">变量声明 let, const, var</p>
            </div>
            <p class="text-[12px] tracking-[2px]" style="color: rgba(255,255,255,0.5);">JS FUNDAMENTALS</p>
        </div>

        <!-- 三列对比 -->
        <div class="flex-1 flex flex-col px-16 py-8 gap-6">
            <div class="grid grid-cols-3 gap-6">
                <!-- let -->
                <div class="p-5" style="background-color: #334155;">
                    <h3 class="text-[22px] font-bold font-mono mb-4" style="color: #93C5FD;">let</h3>
                    <div class="p-3 font-mono text-[14px] space-y-1 mb-4" style="background-color: #1E293B;">
                        <div style="color: #64748B;">// 可以重新赋值</div>
                        <div><span style="color: #F472B6;">let</span> <span style="color: #93C5FD;">age</span> = <span style="color: #FBBF24;">10</span>;</div>
                        <div><span style="color: #93C5FD;">age</span> = <span style="color: #FBBF24;">11</span>; <span style="color: #86EFAC;">// OK</span></div>
                    </div>
                    <div class="space-y-2 text-[14px] mb-4" style="color: #CBD5E1;">
                        <p>- 块级作用域</p>
                        <p>- 可以修改值</p>
                        <p>- 不能重复声明</p>
                    </div>
                    <div class="p-3" style="background-color: rgba(134,239,172,0.1); border-left: 3px solid #86EFAC;">
                        <p class="text-[13px] font-semibold" style="color: #86EFAC;">推荐：需要改变的变量</p>
                    </div>
                </div>

                <!-- const -->
                <div class="p-5" style="background-color: #334155;">
                    <h3 class="text-[22px] font-bold font-mono mb-4" style="color: #C4B5FD;">const</h3>
                    <div class="p-3 font-mono text-[14px] space-y-1 mb-4" style="background-color: #1E293B;">
                        <div style="color: #64748B;">// 不能重新赋值</div>
                        <div><span style="color: #F472B6;">const</span> <span style="color: #C4B5FD;">PI</span> = <span style="color: #FBBF24;">3.14</span>;</div>
                        <div><span style="color: #C4B5FD;">PI</span> = <span style="color: #FBBF24;">3</span>; <span style="color: #FCA5A5;">// Error</span></div>
                    </div>
                    <div class="space-y-2 text-[14px] mb-4" style="color: #CBD5E1;">
                        <p>- 块级作用域</p>
                        <p>- 不能修改值</p>
                        <p>- 不能重复声明</p>
                    </div>
                    <div class="p-3" style="background-color: rgba(134,239,172,0.1); border-left: 3px solid #86EFAC;">
                        <p class="text-[13px] font-semibold" style="color: #86EFAC;">最推荐：不变的常量</p>
                    </div>
                </div>

                <!-- var -->
                <div class="p-5" style="background-color: #334155;">
                    <h3 class="text-[22px] font-bold font-mono mb-4" style="color: #94A3B8;">var</h3>
                    <div class="p-3 font-mono text-[14px] space-y-1 mb-4" style="background-color: #1E293B;">
                        <div style="color: #64748B;">// 旧语法</div>
                        <div><span style="color: #F472B6;">var</span> <span style="color: #94A3B8;">name</span> = <span style="color: #86EFAC;">"Tom"</span>;</div>
                        <div><span style="color: #94A3B8;">name</span> = <span style="color: #86EFAC;">"Jerry"</span>; <span style="color: #86EFAC;">// OK</span></div>
                    </div>
                    <div class="space-y-2 text-[14px] mb-4" style="color: #CBD5E1;">
                        <p>- 函数作用域</p>
                        <p>- 可以修改值</p>
                        <p>- 可以重复声明</p>
                    </div>
                    <div class="p-3" style="background-color: rgba(252,165,165,0.1); border-left: 3px solid #FCA5A5;">
                        <p class="text-[13px] font-semibold" style="color: #FCA5A5;">不推荐：容易产生问题</p>
                    </div>
                </div>
            </div>

            <!-- 使用建议 + 实战示例 -->
            <div class="grid grid-cols-2 gap-6 flex-1">
                <div class="p-6" style="background-color: #EEF2F7;">
                    <h3 class="text-[20px] font-bold mb-4 font-mono" style="color: #1E293B;">使用建议</h3>
                    <div class="space-y-3 text-[16px]" style="color: #374151;">
                        <div class="flex items-start gap-3">
                            <div class="w-[3px] h-5 mt-0.5 flex-shrink-0" style="background-color: #2D3A8C;"></div>
                            <p>默认使用 <span class="font-mono font-bold" style="color: #2D3A8C;">const</span></p>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-[3px] h-5 mt-0.5 flex-shrink-0" style="background-color: #2D3A8C;"></div>
                            <p>需要改变值时用 <span class="font-mono font-bold" style="color: #2D3A8C;">let</span></p>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-[3px] h-5 mt-0.5 flex-shrink-0" style="background-color: #E8573A;"></div>
                            <p>不要使用 <span class="font-mono font-bold line-through" style="color: #94A3B8;">var</span></p>
                        </div>
                    </div>
                </div>

                <div class="p-6" style="background-color: #334155;">
                    <h3 class="text-[20px] font-bold mb-4 font-mono" style="color: #FBBF24;">实战示例</h3>
                    <div class="p-4 font-mono text-[14px] space-y-1" style="background-color: #1E293B;">
                        <div><span style="color: #F472B6;">const</span> <span style="color: #C4B5FD;">studentName</span> = <span style="color: #86EFAC;">"小明"</span>;</div>
                        <div><span style="color: #F472B6;">let</span> <span style="color: #93C5FD;">score</span> = <span style="color: #FBBF24;">90</span>;</div>
                        <div><span style="color: #93C5FD;">score</span> = <span style="color: #93C5FD;">score</span> + <span style="color: #FBBF24;">5</span>;</div>
                        <div style="color: #64748B;">// score 现在是 95</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 底部信息条 -->
        <div class="h-[44px] flex items-center justify-center" style="background-color: #E8573A;">
            <p class="text-[13px]" style="color: #FFFFFF;">参考：MDN Web Docs · JavaScript变量声明</p>
        </div>
    </div>
</div>