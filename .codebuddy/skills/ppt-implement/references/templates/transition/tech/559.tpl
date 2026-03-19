<!-- Template: 科技风-电路板 (Transition #559) -->
            <div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden bg-emerald-950">
                <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

                    <!-- PCB基板纹理 -->
                    <svg class="absolute inset-0 w-full h-full opacity-[0.06]" xmlns="http://www.w3.org/2000/svg">
                        <pattern id="t559-pcb" width="24" height="24" patternUnits="userSpaceOnUse">
                            <rect width="24" height="24" fill="none" stroke="#34d399" stroke-width="0.3"/>
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#t559-pcb)"/>
                    </svg>

                    <!-- 电路走线网络 - SVG精绘 -->
                    <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <!-- 左侧走线 -->
                        <path d="M0 180 H120 V240 H200 V240" stroke="#10b981" stroke-width="2" fill="none" opacity="0.25"/>
                        <path d="M0 300 H80 V360 H180" stroke="#10b981" stroke-width="1.5" fill="none" opacity="0.2"/>
                        <path d="M0 460 H160 V400 H240" stroke="#10b981" stroke-width="1.5" fill="none" opacity="0.18"/>
                        <path d="M0 560 H100 V500 H200 V480" stroke="#10b981" stroke-width="2" fill="none" opacity="0.22"/>

                        <!-- 右侧走线 -->
                        <path d="M1350 200 H1220 V260 H1140 V260" stroke="#10b981" stroke-width="2" fill="none" opacity="0.25"/>
                        <path d="M1350 340 H1260 V400 H1160" stroke="#10b981" stroke-width="1.5" fill="none" opacity="0.2"/>
                        <path d="M1350 480 H1200 V420 H1120" stroke="#10b981" stroke-width="1.5" fill="none" opacity="0.18"/>
                        <path d="M1350 580 H1240 V520 H1160 V500" stroke="#10b981" stroke-width="2" fill="none" opacity="0.22"/>

                        <!-- 过孔 (via) - 同心圆 -->
                        <circle cx="120" cy="180" r="6" fill="none" stroke="#34d399" stroke-width="1.5" opacity="0.35"/>
                        <circle cx="120" cy="180" r="2.5" fill="#34d399" opacity="0.4"/>

                        <circle cx="200" cy="240" r="5" fill="none" stroke="#34d399" stroke-width="1.2" opacity="0.3"/>
                        <circle cx="200" cy="240" r="2" fill="#34d399" opacity="0.35"/>

                        <circle cx="180" cy="360" r="5" fill="none" stroke="#34d399" stroke-width="1.2" opacity="0.3"/>
                        <circle cx="180" cy="360" r="2" fill="#34d399" opacity="0.35"/>

                        <circle cx="240" cy="400" r="6" fill="none" stroke="#34d399" stroke-width="1.5" opacity="0.35"/>
                        <circle cx="240" cy="400" r="2.5" fill="#34d399" opacity="0.4"/>

                        <circle cx="200" cy="480" r="5" fill="none" stroke="#34d399" stroke-width="1.2" opacity="0.3"/>
                        <circle cx="200" cy="480" r="2" fill="#34d399" opacity="0.35"/>

                        <circle cx="1220" cy="200" r="6" fill="none" stroke="#34d399" stroke-width="1.5" opacity="0.35"/>
                        <circle cx="1220" cy="200" r="2.5" fill="#34d399" opacity="0.4"/>

                        <circle cx="1140" cy="260" r="5" fill="none" stroke="#34d399" stroke-width="1.2" opacity="0.3"/>
                        <circle cx="1140" cy="260" r="2" fill="#34d399" opacity="0.35"/>

                        <circle cx="1160" cy="400" r="5" fill="none" stroke="#34d399" stroke-width="1.2" opacity="0.3"/>
                        <circle cx="1160" cy="400" r="2" fill="#34d399" opacity="0.35"/>

                        <circle cx="1200" cy="480" r="6" fill="none" stroke="#34d399" stroke-width="1.5" opacity="0.35"/>
                        <circle cx="1200" cy="480" r="2.5" fill="#34d399" opacity="0.4"/>

                        <circle cx="1160" cy="500" r="5" fill="none" stroke="#34d399" stroke-width="1.2" opacity="0.3"/>
                        <circle cx="1160" cy="500" r="2" fill="#34d399" opacity="0.35"/>

                        <!-- 焊盘 (pads) - 方形 -->
                        <rect x="76" y="296" width="8" height="8" fill="#34d399" opacity="0.3"/>
                        <rect x="96" y="296" width="8" height="8" fill="none" stroke="#34d399" stroke-width="1" opacity="0.25"/>
                        <rect x="156" y="456" width="8" height="8" fill="#34d399" opacity="0.3"/>
                        <rect x="176" y="456" width="8" height="8" fill="none" stroke="#34d399" stroke-width="1" opacity="0.25"/>

                        <rect x="1256" y="336" width="8" height="8" fill="#34d399" opacity="0.3"/>
                        <rect x="1276" y="336" width="8" height="8" fill="none" stroke="#34d399" stroke-width="1" opacity="0.25"/>
                        <rect x="1236" y="576" width="8" height="8" fill="#34d399" opacity="0.3"/>
                        <rect x="1256" y="576" width="8" height="8" fill="none" stroke="#34d399" stroke-width="1" opacity="0.25"/>
                    </svg>

                    <!-- 中央芯片区域 -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <!-- 芯片外壳 -->
                        <div class="relative w-[400px] h-[320px] border-2 border-emerald-500 bg-emerald-950 flex items-center justify-center" style="box-shadow: inset 0 0 60px rgba(16,185,129,0.06);">
                            <!-- 引脚 - 顶部 -->
                            <div class="absolute -top-[14px] left-[60px] flex gap-[16px]">
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                            </div>
                            <!-- 引脚 - 底部 -->
                            <div class="absolute -bottom-[14px] left-[60px] flex gap-[16px]">
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[2px] h-[14px] bg-emerald-500 opacity-50"></div>
                            </div>
                            <!-- 引脚 - 左侧 -->
                            <div class="absolute -left-[14px] top-[40px] flex flex-col gap-[14px]">
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                            </div>
                            <!-- 引脚 - 右侧 -->
                            <div class="absolute -right-[14px] top-[40px] flex flex-col gap-[14px]">
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                                <div class="w-[14px] h-[2px] bg-emerald-500 opacity-50"></div>
                            </div>

                            <!-- Pin 1 标记 - 左上角缺口 -->
                            <div class="absolute top-3 left-3 w-4 h-4 rounded-full border border-emerald-500 opacity-40"></div>

                            <!-- 芯片内容 -->
                            <div class="text-center relative z-10">
                                <div class="text-[10px] font-mono text-emerald-600 tracking-[0.3em] mb-3">SECTION MODULE</div>
                                <div class="text-[72px] font-mono font-bold text-emerald-400 leading-none mb-4">01</div>
                                <div class="w-[200px] h-px bg-emerald-600 mx-auto mb-4 opacity-40"></div>
                                <h1 class="text-[32px] font-bold text-emerald-100 mb-3 tracking-wide">{{title}}</h1>
                                <p class="text-sm text-emerald-500 tracking-wider">{{subtitle}}</p>
                            </div>

                            <!-- 芯片编号标记 - 右下 -->
                            <div class="absolute bottom-3 right-4 text-[9px] font-mono text-emerald-700">IC-559</div>
                        </div>
                    </div>

                    <!-- 顶部状态 -->
                    <div class="absolute top-4 left-6 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 opacity-60"></div>
                        <span class="text-[10px] font-mono text-emerald-700 tracking-widest">PCB_LAYER_01</span>
                    </div>
                    <div class="absolute top-4 right-6 text-[10px] font-mono text-emerald-700 tracking-widest">REV 2.0</div>

                    <!-- 底部信息 -->
                    <div class="absolute bottom-4 left-6 text-[10px] font-mono text-emerald-700 tracking-wider">COPPER_TRACE: ACTIVE</div>
                    <div class="absolute bottom-4 right-6 flex items-center gap-3">
                        <div class="flex gap-1">
                            <div class="w-2 h-2 bg-emerald-500 opacity-40"></div>
                            <div class="w-2 h-2 bg-emerald-500 opacity-60"></div>
                            <div class="w-2 h-2 bg-emerald-400 opacity-80"></div>
                        </div>
                        <span class="text-[10px] font-mono text-emerald-700">SIGNAL OK</span>
                    </div>

                </div>
            </div>
