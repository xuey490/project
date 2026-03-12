<!-- Template: 教育风格-变体20 (Content #1660) - CSS基础·盒模型 -->
<div class="w-[1440px] h-[810px] relative overflow-hidden" style="background-color: #1E293B;">
    <div class="absolute inset-0 flex flex-col">
        <div class="h-[80px] flex items-center justify-between px-16" style="background-color: #2D3A8C;">
            <div class="flex items-center gap-6">
                <h1 class="text-[32px] font-bold font-mono" style="color: #FFFFFF;">CSS 盒模型</h1>
                <div class="w-[1px] h-8" style="background-color: rgba(255,255,255,0.2);"></div>
                <p class="text-[16px] font-mono" style="color: rgba(255,255,255,0.7);">Box Model</p>
            </div>
            <p class="text-[12px] tracking-[2px]" style="color: rgba(255,255,255,0.5);">CSS FUNDAMENTALS</p>
        </div>

        <div class="flex-1 flex px-16 py-8 gap-10">
            <div class="flex-1 flex items-center justify-center">
                <div class="relative">
                    <div class="relative p-6" style="border: 2px dashed #F97316; background-color: rgba(249,115,22,0.05);">
                        <div class="absolute -top-3 left-6 px-3 py-0.5" style="background-color: #1E293B;">
                            <span class="text-[13px] font-mono font-semibold" style="color: #F97316;">margin: 20px</span>
                        </div>
                        <div class="relative p-5" style="border: 3px solid #4ADE80; background-color: rgba(74,222,128,0.05);">
                            <div class="absolute -top-3 left-6 px-3 py-0.5" style="background-color: #1E293B;">
                                <span class="text-[13px] font-mono font-semibold" style="color: #4ADE80;">border: 3px</span>
                            </div>
                            <div class="relative p-6" style="background-color: rgba(167,139,250,0.1); border: 1px solid rgba(167,139,250,0.3);">
                                <div class="absolute -top-3 left-6 px-3 py-0.5" style="background-color: #1E293B;">
                                    <span class="text-[13px] font-mono font-semibold" style="color: #A78BFA;">padding: 16px</span>
                                </div>
                                <div class="w-[200px] h-[100px] flex items-center justify-center" style="background-color: #0891B2;">
                                    <div class="text-center">
                                        <span class="font-mono font-bold text-[16px]" style="color: #FFFFFF;">content</span>
                                        <br/><span class="font-mono text-[12px]" style="color: rgba(255,255,255,0.7);">200 x 100</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-[520px] space-y-4">
                <div class="flex items-start gap-4 p-4" style="background-color: rgba(8,145,178,0.1); border-left: 3px solid #0891B2;">
                    <div class="w-3 h-3 mt-1.5 flex-shrink-0" style="background-color: #0891B2; border-radius: 50%;"></div>
                    <div>
                        <div class="flex items-baseline gap-3 mb-1">
                            <h3 class="text-[18px] font-bold font-mono" style="color: #22D3EE;">content</h3>
                            <span class="text-[12px]" style="color: #64748B;">内容区域</span>
                        </div>
                        <p class="text-[14px] mb-2" style="color: #94A3B8;">元素的实际内容，如文字、图片等</p>
                        <code class="inline-block px-3 py-1 text-[12px] font-mono" style="background-color: #334155; color: #22D3EE;">width: 200px; height: 100px;</code>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4" style="background-color: rgba(167,139,250,0.1); border-left: 3px solid #A78BFA;">
                    <div class="w-3 h-3 mt-1.5 flex-shrink-0" style="background-color: #A78BFA; border-radius: 50%;"></div>
                    <div>
                        <div class="flex items-baseline gap-3 mb-1">
                            <h3 class="text-[18px] font-bold font-mono" style="color: #A78BFA;">padding</h3>
                            <span class="text-[12px]" style="color: #64748B;">内边距</span>
                        </div>
                        <p class="text-[14px] mb-2" style="color: #94A3B8;">内容与边框之间的透明空间</p>
                        <code class="inline-block px-3 py-1 text-[12px] font-mono" style="background-color: #334155; color: #A78BFA;">padding: 16px;</code>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4" style="background-color: rgba(74,222,128,0.1); border-left: 3px solid #4ADE80;">
                    <div class="w-3 h-3 mt-1.5 flex-shrink-0" style="background-color: #4ADE80; border-radius: 50%;"></div>
                    <div>
                        <div class="flex items-baseline gap-3 mb-1">
                            <h3 class="text-[18px] font-bold font-mono" style="color: #4ADE80;">border</h3>
                            <span class="text-[12px]" style="color: #64748B;">边框</span>
                        </div>
                        <p class="text-[14px] mb-2" style="color: #94A3B8;">围绕 padding 和 content 的边界线</p>
                        <code class="inline-block px-3 py-1 text-[12px] font-mono" style="background-color: #334155; color: #4ADE80;">border: 3px solid;</code>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4" style="background-color: rgba(249,115,22,0.1); border-left: 3px solid #F97316;">
                    <div class="w-3 h-3 mt-1.5 flex-shrink-0" style="background-color: #F97316; border-radius: 50%;"></div>
                    <div>
                        <div class="flex items-baseline gap-3 mb-1">
                            <h3 class="text-[18px] font-bold font-mono" style="color: #F97316;">margin</h3>
                            <span class="text-[12px]" style="color: #64748B;">外边距</span>
                        </div>
                        <p class="text-[14px] mb-2" style="color: #94A3B8;">元素与其他元素之间的透明间隔</p>
                        <code class="inline-block px-3 py-1 text-[12px] font-mono" style="background-color: #334155; color: #F97316;">margin: 20px;</code>
                    </div>
                </div>
            </div>
        </div>

        <div class="h-[50px] flex items-center justify-center px-16" style="background-color: #334155;">
            <div class="flex items-center gap-3 text-[14px] font-mono">
                <span style="color: #94A3B8;">Total Width =</span>
                <span style="color: #F97316;">margin</span><span style="color: #64748B;"> + </span>
                <span style="color: #4ADE80;">border</span><span style="color: #64748B;"> + </span>
                <span style="color: #A78BFA;">padding</span><span style="color: #64748B;"> + </span>
                <span style="color: #22D3EE;">content</span><span style="color: #64748B;"> + </span>
                <span style="color: #A78BFA;">padding</span><span style="color: #64748B;"> + </span>
                <span style="color: #4ADE80;">border</span><span style="color: #64748B;"> + </span>
                <span style="color: #F97316;">margin</span>
            </div>
        </div>
    </div>
</div>