<!-- Template: 文艺风-旧时书信 (Transition #546) -->
            <div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-amber-50">
                <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

                    <!-- 背景纹理：模拟牛皮纸质感 -->
                    <svg class="absolute inset-0 w-full h-full opacity-[0.03]" xmlns="http://www.w3.org/2000/svg">
                        <pattern id="t546-kraft" width="4" height="4" patternUnits="userSpaceOnUse">
                            <rect width="1" height="1" x="0" y="0" fill="#92400e"/>
                            <rect width="1" height="1" x="2" y="2" fill="#92400e"/>
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#t546-kraft)"/>
                    </svg>

                    <!-- 信封三角形翻盖 - 顶部 -->
                    <div class="absolute top-0 left-0 right-0" style="height: 180px;">
                        <svg class="w-full h-full" viewBox="0 0 1350 180" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="0,0 1350,0 675,180" fill="#d6d3d1" opacity="0.3"/>
                            <polyline points="0,0 675,180 1350,0" stroke="#a8a29e" stroke-width="1" fill="none" opacity="0.5"/>
                        </svg>
                    </div>

                    <!-- 左右信封斜纹 -->
                    <div class="absolute bottom-0 left-0" style="width: 200px; height: 300px;">
                        <svg class="w-full h-full" viewBox="0 0 200 300" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="0,300 0,0 200,300" fill="#d6d3d1" opacity="0.15"/>
                            <line x1="0" y1="0" x2="200" y2="300" stroke="#a8a29e" stroke-width="0.8" opacity="0.3"/>
                        </svg>
                    </div>
                    <div class="absolute bottom-0 right-0" style="width: 200px; height: 300px;">
                        <svg class="w-full h-full" viewBox="0 0 200 300" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="200,300 200,0 0,300" fill="#d6d3d1" opacity="0.15"/>
                            <line x1="200" y1="0" x2="0" y2="300" stroke="#a8a29e" stroke-width="0.8" opacity="0.3"/>
                        </svg>
                    </div>

                    <!-- 邮票装饰 - 右上角 -->
                    <div class="absolute top-[24px] right-[40px]">
                        <div class="w-[72px] h-[88px] bg-white border border-stone-300 p-1.5 shadow-sm" style="background-image: repeating-conic-gradient(#d6d3d1 0% 25%, transparent 0% 50%); background-size: 8px 8px; background-position: -4px -4px;">
                            <div class="w-full h-full bg-stone-100 border border-stone-200 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-[10px] text-stone-400 font-serif">No.</div>
                                    <div class="text-xl font-serif font-bold text-stone-600">01</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 信纸横线 - 中央区域背景 -->
                    <div class="absolute top-[220px] left-[200px] right-[200px] bottom-[160px]">
                        <svg class="w-full h-full opacity-[0.15]" xmlns="http://www.w3.org/2000/svg">
                            <pattern id="t546-lines" width="100%" height="32" patternUnits="userSpaceOnUse">
                                <line x1="0" y1="31" x2="100%" y2="31" stroke="#92400e" stroke-width="0.5"/>
                            </pattern>
                            <rect width="100%" height="100%" fill="url(#t546-lines)"/>
                        </svg>
                    </div>

                    <!-- 中央主内容 -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center relative">
                            <!-- 蜡封 -->
                            <div class="relative inline-block mb-10">
                                <div class="w-[80px] h-[80px] rounded-full bg-red-700 flex items-center justify-center shadow-lg relative">
                                    <!-- 蜡封边缘不规则 -->
                                    <div class="absolute -top-1 -left-1 w-4 h-4 rounded-full bg-red-700"></div>
                                    <div class="absolute -top-1.5 right-2 w-3 h-3 rounded-full bg-red-700"></div>
                                    <div class="absolute -bottom-1 left-2 w-3.5 h-3.5 rounded-full bg-red-700"></div>
                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-red-700"></div>
                                    <div class="absolute top-2 -left-1.5 w-3 h-3 rounded-full bg-red-700"></div>
                                    <div class="absolute top-2 -right-1.5 w-3 h-3 rounded-full bg-red-700"></div>
                                    <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3.5 h-3.5 rounded-full bg-red-700"></div>
                                    <!-- 蜡封内圈 -->
                                    <div class="w-[56px] h-[56px] rounded-full border-2 border-red-500 flex items-center justify-center">
                                        <span class="text-xl font-serif font-bold text-red-200 tracking-wider">01</span>
                                    </div>
                                </div>
                                <!-- 丝带 -->
                                <div class="absolute top-[36px] -left-[30px] w-[140px] h-[8px] bg-red-800 opacity-60 -rotate-3"></div>
                            </div>

                            <!-- 标题区 -->
                            <div class="text-sm tracking-[0.5em] text-amber-600 uppercase font-serif mb-3">Chapter One</div>

                            <!-- 装饰线 -->
                            <div class="flex items-center justify-center gap-3 mb-5">
                                <svg class="w-[60px] h-[12px]" viewBox="0 0 60 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 6 Q15 0 30 6 Q45 12 60 6" stroke="#d97706" stroke-width="1" fill="none"/>
                                </svg>
                                <div class="w-2 h-2 border border-amber-500 transform rotate-45"></div>
                                <svg class="w-[60px] h-[12px]" viewBox="0 0 60 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 6 Q15 12 30 6 Q45 0 60 6" stroke="#d97706" stroke-width="1" fill="none"/>
                                </svg>
                            </div>

                            <h1 class="text-[42px] font-serif font-bold text-stone-800 mb-4 tracking-wide" style="font-family: 'Georgia', 'Noto Serif SC', serif;">{{title}}</h1>

                            <p class="text-lg text-amber-700 italic font-serif" style="font-family: 'Georgia', 'Noto Serif SC', serif;">{{subtitle}}</p>

                            <!-- 底部装饰 -->
                            <div class="flex items-center justify-center gap-2 mt-8">
                                <div class="w-12 h-px bg-amber-400"></div>
                                <svg class="w-[20px] h-[20px]" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 2 L12 8 L18 10 L12 12 L10 18 L8 12 L2 10 L8 8 Z" stroke="#d97706" stroke-width="1" fill="none"/>
                                </svg>
                                <div class="w-12 h-px bg-amber-400"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 左下角日期戳 -->
                    <div class="absolute bottom-[40px] left-[60px] text-[10px] font-serif text-stone-300 tracking-widest">A.D. MMXXV</div>

                    <!-- 右下角装饰 -->
                    <div class="absolute bottom-[40px] right-[60px] flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-stone-300"></div>
                        <div class="w-6 h-px bg-stone-300"></div>
                        <div class="text-[10px] font-serif text-stone-300 tracking-widest italic">Correspondence</div>
                    </div>

                </div>
            </div>
