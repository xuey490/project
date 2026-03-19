<!-- Template: 教育风格-变体24 (Content #1664) - Python循环·for语句 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #1E293B;">
    <div class="absolute inset-0 flex flex-col">
        <div class="h-[80px] flex items-center justify-between px-16" style="background-color: #2D3A8C;">
            <div class="flex items-center gap-6">
                <h1 class="text-[32px] font-bold font-mono" style="color: #FFFFFF;">for 循环</h1>
                <div class="w-[1px] h-8" style="background-color: rgba(255,255,255,0.2);"></div>
                <p class="text-[16px] font-mono" style="color: rgba(255,255,255,0.7);">重复执行代码的魔法</p>
            </div>
            <p class="text-[12px] tracking-[2px]" style="color: rgba(255,255,255,0.5);">PYTHON BASICS</p>
        </div>

        <div class="flex-1 flex px-16 py-8 gap-8">
            <div class="flex-1 space-y-5">
                <div style="background-color: #334155;">
                    <div class="px-4 py-2" style="background-color: #475569; border-bottom: 1px solid #64748B;">
                        <span class="text-[12px] font-mono" style="color: #E8573A;">basic_syntax.py</span>
                    </div>
                    <div class="p-5 font-mono text-[14px] space-y-1">
                        <div class="flex"><span class="w-8" style="color: #64748B;">1</span><span><span style="color: #F472B6;">for</span> <span style="color: #7DD3FC;">i</span> <span style="color: #F472B6;">in</span> <span style="color: #A78BFA;">range</span>(<span style="color: #FBBF24;">5</span>):</span></div>
                        <div class="flex"><span class="w-8" style="color: #64748B;">2</span><span class="ml-8"><span style="color: #86EFAC;">print</span>(<span style="color: #7DD3FC;">i</span>)</span></div>
                    </div>
                </div>

                <div class="p-5" style="background-color: rgba(134,239,172,0.1); border-left: 3px solid #86EFAC;">
                    <h3 class="text-[18px] font-bold font-mono mb-3" style="color: #86EFAC;">运行结果</h3>
                    <div class="p-4 font-mono text-[14px] space-y-0.5" style="background-color: #334155; color: #86EFAC;">
                        <div>0</div><div>1</div><div>2</div><div>3</div><div>4</div>
                    </div>
                </div>
            </div>

            <div class="flex-1 space-y-5">
                <div class="p-5" style="background-color: #334155;">
                    <h3 class="text-[20px] font-bold mb-4" style="color: #FFFFFF;">理解 range() 函数</h3>
                    <div class="space-y-3 text-[14px]">
                        <div class="p-3" style="background-color: #1E293B;">
                            <p class="font-mono mb-1" style="color: #7DD3FC;">range(5)</p>
                            <p style="color: #94A3B8;">生成 0,1,2,3,4 (从0开始，不包括5)</p>
                        </div>
                        <div class="p-3" style="background-color: #1E293B;">
                            <p class="font-mono mb-1" style="color: #7DD3FC;">range(1, 6)</p>
                            <p style="color: #94A3B8;">生成 1,2,3,4,5 (从1到5)</p>
                        </div>
                        <div class="p-3" style="background-color: #1E293B;">
                            <p class="font-mono mb-1" style="color: #7DD3FC;">range(0, 10, 2)</p>
                            <p style="color: #94A3B8;">生成 0,2,4,6,8 (步长为2)</p>
                        </div>
                    </div>
                </div>

                <div class="p-5" style="background-color: rgba(232,87,58,0.1); border-left: 3px solid #E8573A;">
                    <h3 class="text-[18px] font-bold mb-3 font-mono" style="color: #E8573A;">实战案例</h3>
                    <div class="p-4 font-mono text-[13px] space-y-1" style="background-color: #334155;">
                        <div style="color: #64748B;"># 打印9的乘法表</div>
                        <div><span style="color: #F472B6;">for</span> <span style="color: #7DD3FC;">i</span> <span style="color: #F472B6;">in</span> <span style="color: #A78BFA;">range</span>(<span style="color: #FBBF24;">1</span>, <span style="color: #FBBF24;">10</span>):</div>
                        <div class="ml-4"><span style="color: #86EFAC;">print</span>(<span style="color: #86EFAC;">f"9 x {i} = {9*i}"</span>)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="h-[44px] flex items-center justify-center" style="background-color: #E8573A;">
            <p class="text-[13px]" style="color: #FFFFFF;">参考：Python官方文档 · Control Flow</p>
        </div>
    </div>
</div>